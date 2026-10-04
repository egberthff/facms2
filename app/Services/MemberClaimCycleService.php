<?php

namespace App\Services;

use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\Member;
use App\Models\MemberClaimCycle;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Illuminate\Validation\ValidationException;
use LogicException;

/**
 * Single source of truth for member claim eligibility cycles.
 *
 * - A cycle starts at the month of the member's first qualifying (non-rejected) claim
 *   when they have no cycle covering the claim date.
 * - It lasts the campus `claim_cycle_years` (inclusive months) and snapshots the campus
 *   `annual_claim_limit`; later setting changes only affect cycles created afterwards.
 * - Unused allowance is forfeited when the cycle ends. No cycle exists again until the
 *   next qualifying claim, so gaps between cycles are expected.
 *
 * The campus-wide `claims_cycles` periods (CycleService) are a treasury reporting
 * period only and play no part in eligibility.
 */
class MemberClaimCycleService
{
    public const SELECTOR_CURRENT = 'current';
    public const SELECTOR_PREVIOUS = 'previous';
    public const SELECTOR_ALL = 'all';

    /**
     * Last day of an inclusive cycle: Aug 2026 + 2 years => 2028-07-31.
     */
    public function calculateEnd(Carbon $start, int $years): Carbon
    {
        return $start->copy()->startOfMonth()->addYears($years)->subMonthNoOverflow()->endOfMonth()->startOfDay();
    }

    public function getCycleForDate(Member $member, Carbon $date): ?MemberClaimCycle
    {
        return MemberClaimCycle::query()
            ->where('member_id', $member->id)
            ->covering($date)
            ->first();
    }

    public function getActiveCycleForMember(Member $member, ?Carbon $date = null): ?MemberClaimCycle
    {
        return $this->getCycleForDate($member, $date ?? now());
    }

    /**
     * The cycle a claim belongs to: its stored link, or the member cycle covering its submission date.
     */
    public function getCycleForClaim(Claim $claim): ?MemberClaimCycle
    {
        if ($claim->member_claim_cycle_id) {
            return $claim->memberClaimCycle;
        }

        $member = Member::withoutGlobalScopes()->find($claim->member_id);

        return $member && $claim->submitted_at
            ? $this->getCycleForDate($member, $claim->submitted_at)
            : null;
    }

    public function startNewCycleForMember(Member $member, Carbon $date, ?float $claimLimit = null): MemberClaimCycle
    {
        $years = SystemSetting::getCycleYears($member->campus_id);
        $start = $date->copy()->startOfMonth();
        $end = $this->calculateEnd($start, $years);

        $overlaps = MemberClaimCycle::query()
            ->where('member_id', $member->id)
            ->where('cycle_start', '<=', $end->copy()->endOfDay())
            ->where('cycle_end', '>=', $start)
            ->exists();

        if ($overlaps) {
            throw new LogicException("Member [{$member->id}] already has a claim cycle overlapping {$start->format('F Y')}.");
        }

        return MemberClaimCycle::create([
            'member_id' => $member->id,
            'cycle_start' => $start,
            'cycle_end' => $end,
            'duration_years' => $years,
            'claim_limit' => $claimLimit ?? SystemSetting::getGlobalLimit($member->campus_id),
        ]);
    }

    /**
     * Active cycle for the claim date, or a new one when the member has none (first claim or previous expired).
     * Callers must hold a lock on the member row (see ClaimService::createClaim).
     */
    public function resolveCycleForClaim(Member $member, Carbon $date): MemberClaimCycle
    {
        return $this->getCycleForDate($member, $date) ?? $this->startNewCycleForMember($member, $date);
    }

    /**
     * Allowance consumed inside a cycle: every non-rejected claim counts at its approved amount,
     * or its requested amount while not yet approved, so in-flight claims reserve allowance.
     */
    public function consumedAmount(MemberClaimCycle $cycle, ?string $exceptClaimId = null): float
    {
        return (float) Claim::query()
            ->where('member_claim_cycle_id', $cycle->id)
            ->where('status', '!=', ClaimStatus::Rejected->value)
            ->when($exceptClaimId, fn ($query) => $query->whereKeyNot($exceptClaimId))
            ->sum(DB::raw('COALESCE(approved_amount, requested_amount)'));
    }

    public function remainingForCycle(MemberClaimCycle $cycle): float
    {
        return max(0.0, (float) $cycle->claim_limit - $this->consumedAmount($cycle));
    }

    /**
     * Statuses reached once the campus President has approved the claim.
     */
    public static function approvedStatuses(): array
    {
        return [
            ClaimStatus::Approved->value,
            ClaimStatus::ForReviewFp->value,
            ClaimStatus::Processing->value,
            ClaimStatus::ForDisbursement->value,
            ClaimStatus::Disbursed->value,
        ];
    }

    /**
     * Per-cycle aggregates in one query, keyed by cycle id: consumed (eligibility rule),
     * approved (approved-stage amounts) and disbursed.
     *
     * @return Collection<string, object{consumed: float, approved: float, disbursed: float}>
     */
    public function totalsForCycles(iterable $cycleIds): Collection
    {
        $cycleIds = collect($cycleIds)->filter()->unique()->values();

        if ($cycleIds->isEmpty()) {
            return collect();
        }

        $approved = self::approvedStatuses();
        $placeholders = implode(',', array_fill(0, count($approved), '?'));

        return Claim::query()
            ->whereIn('member_claim_cycle_id', $cycleIds->all())
            ->groupBy('member_claim_cycle_id')
            ->selectRaw(
                "member_claim_cycle_id,
                SUM(CASE WHEN status != ? THEN COALESCE(approved_amount, requested_amount) ELSE 0 END) as consumed,
                SUM(CASE WHEN status IN ({$placeholders}) THEN COALESCE(approved_amount, 0) ELSE 0 END) as approved,
                SUM(CASE WHEN status = ? THEN COALESCE(approved_amount, 0) ELSE 0 END) as disbursed",
                [ClaimStatus::Rejected->value, ...$approved, ClaimStatus::Disbursed->value]
            )
            ->get()
            ->mapWithKeys(fn ($row) => [$row->member_claim_cycle_id => (object) [
                'consumed' => (float) $row->consumed,
                'approved' => (float) $row->approved,
                'disbursed' => (float) $row->disbursed,
            ]]);
    }

    /**
     * Remaining allowance as of a date. Without an active cycle the next claim starts a fresh one,
     * so the full current campus limit is available (previous unused balances are forfeited).
     */
    public function getRemainingClaimAllowance(Member $member, ?Carbon $date = null): float
    {
        $cycle = $this->getActiveCycleForMember($member, $date);

        return $cycle
            ? $this->remainingForCycle($cycle)
            : (float) SystemSetting::getGlobalLimit($member->campus_id);
    }

    public function isMemberEligibleForClaim(Member $member, float $amount, ?Carbon $date = null): bool
    {
        try {
            $this->assertEligible($member, $amount, $date);
            return true;
        } catch (ValidationException) {
            return false;
        }
    }

    /**
     * @throws ValidationException on the `amount` field when the claim would exceed the cycle allowance.
     */
    public function assertEligible(Member $member, float $amount, ?Carbon $date = null): void
    {
        $date ??= now();
        $cycle = $this->getActiveCycleForMember($member, $date);
        $remaining = $this->getRemainingClaimAllowance($member, $date);

        if ($cycle && $remaining <= 0) {
            throw ValidationException::withMessages([
                'amount' => sprintf(
                    'Your claim allowance for the cycle %s is fully used. You may submit a new claim from %s.',
                    $cycle->label(),
                    $cycle->cycle_end->copy()->addDay()->format('F d, Y')
                ),
            ]);
        }

        if ($amount > $remaining) {
            throw ValidationException::withMessages([
                'amount' => 'The requested amount exceeds your remaining cycle allowance balance (₱' . number_format($remaining, 2) . ').',
            ]);
        }
    }

    /**
     * Attach a display-only `cycle_summary` to each claim (dates, limit, remaining allowance of the
     * claimant's cycle) with one aggregate query. Stored as a relation so it serialises with the
     * claim but is never written back to the claims table.
     */
    public function attachCycleSummaries(iterable $claims, ?Carbon $now = null): void
    {
        $claims = collect($claims);
        $cycleIds = $claims->pluck('member_claim_cycle_id')->filter()->unique();
        $cycles = MemberClaimCycle::query()->whereIn('id', $cycleIds->all())->get()->keyBy('id');
        $totals = $this->totalsForCycles($cycleIds);

        foreach ($claims as $claim) {
            $cycle = $cycles->get($claim->member_claim_cycle_id);

            $claim->setRelation('cycle_summary', $cycle ? new Fluent([
                'label' => $cycle->label(),
                'start' => $cycle->cycle_start->toDateString(),
                'end' => $cycle->cycle_end->toDateString(),
                'is_active' => $cycle->isActive($now),
                'limit' => (float) $cycle->claim_limit,
                'consumed' => $totals->get($cycle->id)->consumed ?? 0.0,
                'remaining' => max(0.0, (float) $cycle->claim_limit - ($totals->get($cycle->id)->consumed ?? 0.0)),
            ]) : null);
        }
    }

    /**
     * Drop a cycle that no longer holds any qualifying claim (e.g. its only claim was rejected),
     * so the member's next claim starts a fresh cycle.
     */
    public function releaseIfEmpty(?MemberClaimCycle $cycle): void
    {
        if (!$cycle) {
            return;
        }

        $hasQualifyingClaim = Claim::query()
            ->where('member_claim_cycle_id', $cycle->id)
            ->where('status', '!=', ClaimStatus::Rejected->value)
            ->exists();

        if (!$hasQualifyingClaim) {
            $cycle->delete();
        }
    }

    /**
     * Reconstruct a member's cycles chronologically from their claims (used for historical backfill).
     * Rejected claims never start a cycle; they are linked to a covering cycle when one exists.
     */
    public function rebuildForMember(Member $member): Collection
    {
        return DB::transaction(function () use ($member) {
            DB::table('claims')->where('member_id', $member->id)->update(['member_claim_cycle_id' => null]);
            MemberClaimCycle::where('member_id', $member->id)->delete();

            $claims = Claim::query()
                ->where('member_id', $member->id)
                ->orderBy('submitted_at')
                ->orderBy('created_at')
                ->get();

            $cycles = collect();
            $current = null;

            foreach ($claims->where('status', '!=', ClaimStatus::Rejected->value) as $claim) {
                if (!$current || !$current->covers($claim->submitted_at)) {
                    $current = $this->startNewCycleForMember(
                        $member,
                        $claim->submitted_at,
                        $claim->annual_limit !== null ? (float) $claim->annual_limit : null
                    );
                    $cycles->push($current);
                }

                $this->linkClaim($claim, $current);
            }

            foreach ($claims->where('status', ClaimStatus::Rejected->value) as $claim) {
                $covering = $cycles->first(fn (MemberClaimCycle $cycle) => $cycle->covers($claim->submitted_at));
                if ($covering) {
                    $this->linkClaim($claim, $covering);
                }
            }

            return $cycles;
        });
    }

    /**
     * Link without touching updated_at, so historical claims keep their audit timestamps.
     */
    private function linkClaim(Claim $claim, MemberClaimCycle $cycle): void
    {
        DB::table('claims')->where('id', $claim->id)->update(['member_claim_cycle_id' => $cycle->id]);
        $claim->member_claim_cycle_id = $cycle->id;
    }

    /**
     * Report selector: `current` = cycles active now, `previous` = each member's latest expired cycle,
     * `all` = every cycle. With $expiredOnly, `current`/`previous` both mean the latest expired cycle
     * and `all` means every expired cycle.
     *
     * @param  iterable<string>|null  $memberIds  null for all members
     */
    public function cyclesForSelector(?string $selector, ?iterable $memberIds = null, ?Carbon $now = null, bool $expiredOnly = false): Collection
    {
        $selector = $this->normalizeSelector($selector);
        $today = ($now ?? now())->copy()->startOfDay();

        $query = MemberClaimCycle::query()
            ->when($memberIds !== null, fn ($query) => $query->whereIn('member_id', collect($memberIds)->all()))
            ->orderBy('member_id')
            ->orderByDesc('cycle_start');

        if ($expiredOnly) {
            $query->where('cycle_end', '<', $today);

            return $selector === self::SELECTOR_ALL
                ? $query->get()
                : $query->get()->unique('member_id')->values();
        }

        return match ($selector) {
            self::SELECTOR_ALL => $query->get(),
            self::SELECTOR_PREVIOUS => $query->where('cycle_end', '<', $today)->get()->unique('member_id')->values(),
            default => $query->covering($today)->get(),
        };
    }

    public function normalizeSelector(?string $selector): string
    {
        return in_array($selector, [self::SELECTOR_CURRENT, self::SELECTOR_PREVIOUS, self::SELECTOR_ALL], true)
            ? $selector
            : self::SELECTOR_CURRENT;
    }

    public function selectorOptions(): array
    {
        return [
            ['value' => self::SELECTOR_CURRENT, 'label' => 'Current member cycle'],
            ['value' => self::SELECTOR_PREVIOUS, 'label' => 'Previous (latest expired) cycle'],
            ['value' => self::SELECTOR_ALL, 'label' => 'All member cycles'],
        ];
    }
}

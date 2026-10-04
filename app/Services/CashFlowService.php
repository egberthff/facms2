<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\ClaimPayment;
use App\Models\CollectionSummaryItem;
use App\Models\CollectionSummaryPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Campus Cash Flow: a saved, per-campus period whose forwarded balance is the previous
 * finalized period's cash on hand.
 *
 *   Add Funds  = approved FFA disbursements to the campus (snapshot) + manual cash-ins
 *   Less       = manual campus expenses + medical assistance released to campus members (snapshot)
 *   Total      = forwarded balance + Add Funds
 *   Cash on Hand = Total - Less
 */
class CashFlowService
{
    public function __construct(
        private CollectionSummaryService $collectionSummaryService,
        private TreasuryLedgerService $treasuryLedgerService,
        private ClaimFormService $claimFormService,
    ) {
    }

    /**
     * Latest finalized period of the campus that ends before the given date.
     */
    public function previousPeriod(string $campusId, string $before): ?CollectionSummaryPeriod
    {
        return CollectionSummaryPeriod::query()
            ->cashFlow()
            ->where('campus_id', $campusId)
            ->where('status', CollectionSummaryPeriod::STATUS_FINALIZED)
            ->whereDate('period_to', '<', Carbon::parse($before)->toDateString())
            ->latest('period_to')
            ->first();
    }

    /**
     * The campus has no finalized period yet, so the next one needs a manual opening balance.
     */
    public function needsOpeningBalance(string $campusId): bool
    {
        return !CollectionSummaryPeriod::query()
            ->cashFlow()
            ->where('campus_id', $campusId)
            ->where('status', CollectionSummaryPeriod::STATUS_FINALIZED)
            ->exists();
    }

    public function createDraft(array $data, User $user): CollectionSummaryPeriod
    {
        $campusId = $data['campus_id'];
        $from = Carbon::parse($data['period_from'])->toDateString();
        $to = Carbon::parse($data['period_to'])->toDateString();

        $existing = CollectionSummaryPeriod::query()->cashFlow()->where('campus_id', $campusId);

        if ((clone $existing)->whereDate('period_from', '<=', $to)->whereDate('period_to', '>=', $from)->exists()) {
            throw ValidationException::withMessages(['period_from' => 'This date range overlaps an existing cash flow period of the campus.']);
        }

        if ((clone $existing)->whereDate('period_from', '>', $to)->exists()) {
            throw ValidationException::withMessages(['period_from' => 'Cash flow periods must be created in order. A later period already exists for this campus.']);
        }

        if ((clone $existing)->where('status', CollectionSummaryPeriod::STATUS_DRAFT)->exists()) {
            throw ValidationException::withMessages(['period_from' => 'Finalize the campus\'s current draft period before starting a new one.']);
        }

        $previous = $this->previousPeriod($campusId, $from);

        if (!$previous && !isset($data['opening_balance'])) {
            throw ValidationException::withMessages(['opening_balance' => 'Enter the opening (forwarded) balance for the campus\'s first cash flow period.']);
        }

        return DB::transaction(function () use ($data, $campusId, $from, $to, $previous, $user) {
            $period = CollectionSummaryPeriod::create([
                'campus_id' => $campusId,
                'report_type' => CollectionSummaryPeriod::TYPE_CASH_FLOW,
                'period_label' => 'As of ' . Carbon::parse($to)->format('F d, Y'),
                'title' => 'Campus Cash Flow',
                'period_from' => $from,
                'period_to' => $to,
                'forwarded_label' => $previous
                    ? $previous->period_to->format('F Y')
                    : (($data['opening_label'] ?? null) ?: Carbon::parse($from)->subDay()->format('F Y')),
                'forwarded_balance' => $previous ? $previous->cash_on_hand : $data['opening_balance'],
                'prepared_by' => $user->user_id,
                'status' => CollectionSummaryPeriod::STATUS_DRAFT,
            ]);

            $this->syncSourceItems($period);

            return $this->recalculate($period);
        });
    }

    /**
     * Re-snapshot the system-sourced lines (FFA transfers, claim releases); manual lines are kept.
     */
    public function refresh(CollectionSummaryPeriod $period): CollectionSummaryPeriod
    {
        $this->assertDraft($period);

        return DB::transaction(function () use ($period) {
            $previous = $this->previousPeriod($period->campus_id, $period->period_from->toDateString());

            if ($previous) {
                $period->update([
                    'forwarded_label' => $previous->period_to->format('F Y'),
                    'forwarded_balance' => $previous->cash_on_hand,
                ]);
            }

            $this->syncSourceItems($period);

            return $this->recalculate($period);
        });
    }

    /**
     * Only the first period of a campus has a manually entered forwarded balance.
     */
    public function updateOpeningBalance(CollectionSummaryPeriod $period, float $balance, ?string $label): CollectionSummaryPeriod
    {
        $this->assertDraft($period);

        if ($this->previousPeriod($period->campus_id, $period->period_from->toDateString())) {
            throw ValidationException::withMessages(['opening_balance' => 'The forwarded balance comes from the previous finalized period and cannot be edited.']);
        }

        $period->update([
            'forwarded_balance' => $balance,
            'forwarded_label' => $label ?: $period->forwarded_label,
        ]);

        return $this->recalculate($period);
    }

    public function addManualItem(CollectionSummaryPeriod $period, array $data): CollectionSummaryItem
    {
        $this->assertDraft($period);

        $item = $period->items()->create([
            'section' => $data['section'],
            'label' => $data['label'],
            'amount' => $data['amount'],
            'entry_date' => $data['entry_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_manual' => true,
            'source_type' => CollectionSummaryItem::SOURCE_MANUAL,
            'sort_order' => (int) $period->items()->where('section', $data['section'])->max('sort_order') + 1,
        ]);

        $this->recalculate($period);

        return $item;
    }

    /**
     * Manual lines are fully editable; system lines only take a label/notes change
     * (e.g. appending "with P10 transaction fee") so their amounts stay tied to the source record.
     */
    public function updateItem(CollectionSummaryItem $item, array $data): CollectionSummaryItem
    {
        $period = $item->summary;
        $this->assertDraft($period);

        $item->update($item->is_manual
            ? collect($data)->only(['section', 'label', 'amount', 'entry_date', 'notes'])->all()
            : collect($data)->only(['label', 'notes'])->all());

        $this->recalculate($period);

        return $item;
    }

    public function deleteItem(CollectionSummaryItem $item): void
    {
        $period = $item->summary;
        $this->assertDraft($period);

        if (!$item->is_manual) {
            throw ValidationException::withMessages(['item' => 'System lines come from approved transfers and released claims and cannot be removed.']);
        }

        $item->delete();
        $this->recalculate($period);
    }

    public function deleteDraft(CollectionSummaryPeriod $period): void
    {
        $this->assertDraft($period);

        $period->delete();
    }

    /**
     * Lock the period, freeze the signatories and post the manual lines to the campus treasury ledger.
     * System lines are already in the ledger (transfer approval, claim disbursement).
     */
    public function finalize(CollectionSummaryPeriod $period, User $user): CollectionSummaryPeriod
    {
        return DB::transaction(function () use ($period, $user) {
            $period = CollectionSummaryPeriod::query()->lockForUpdate()->findOrFail($period->id);
            $this->assertDraft($period);
            $this->recalculate($period);

            $period->items()
                ->where('is_manual', true)
                ->orderBy('section')
                ->orderBy('sort_order')
                ->get()
                ->each(fn (CollectionSummaryItem $item) => $this->treasuryLedgerService->recordTransaction([
                    'campus_id' => $period->campus_id,
                    'sourceable_type' => CollectionSummaryItem::class,
                    'sourceable_id' => $item->id,
                    'reference_number' => 'CF-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6)),
                    'description' => $item->label,
                    'type' => $item->section === CollectionSummaryItem::SECTION_FUNDS ? 'Credit' : 'Debit',
                    'amount' => $item->amount,
                ]));

            $signatories = $this->signatories($period->campus_id);

            $period->update([
                'status' => CollectionSummaryPeriod::STATUS_FINALIZED,
                'finalized_at' => now(),
                'approved_by' => $user->user_id,
                'prepared_by_name' => $signatories['prepared_by'],
                'checked_by_name' => $signatories['checked_by'],
            ]);

            return $period;
        });
    }

    /**
     * Report signatories: Settings assignments, with Prepared by falling back to the campus Treasurer.
     */
    public function signatories(string $campusId): array
    {
        $preparedBy = $this->claimFormService->assignedMember('cash_flow_prepared_by', $campusId)
            ?: $this->claimFormService->officer('Treasurer', $campusId)['name'];

        return [
            'prepared_by' => $preparedBy,
            'checked_by' => $this->claimFormService->assignedMember('cash_flow_checked_by', $campusId),
        ];
    }

    /**
     * Everything the printable report needs, grouped the way the treasurer's report is laid out.
     */
    public function printableData(CollectionSummaryPeriod $period): array
    {
        $period->loadMissing('items');
        $campus = Campus::withoutGlobalScopes()->find($period->campus_id);

        $ordered = fn (Collection $items) => $items
            ->sortBy([['entry_date', 'asc'], ['sort_order', 'asc']])
            ->values();

        $funds = $ordered($period->items->where('section', CollectionSummaryItem::SECTION_FUNDS));
        $disbursements = $period->items->where('section', CollectionSummaryItem::SECTION_DISBURSEMENTS);
        $medical = $ordered($disbursements->where('source_type', CollectionSummaryItem::SOURCE_CLAIM_PAYMENT));

        $signatories = $period->isFinalized()
            ? ['prepared_by' => $period->prepared_by_name ?? '', 'checked_by' => $period->checked_by_name ?? '']
            : $this->signatories($period->campus_id);

        return [
            'period' => $period,
            'campus' => $campus,
            'associationName' => 'BISU ' . strtoupper($this->claimFormService->campusShortName($campus?->campus_name)) . ' FACULTY ASSOCIATION',
            'asOf' => $period->period_to->format('F d, Y'),
            'funds' => $funds,
            'expenses' => $ordered($disbursements->where('source_type', '!=', CollectionSummaryItem::SOURCE_CLAIM_PAYMENT)),
            'medicalTotal' => (float) $medical->sum('amount'),
            'medicalByMonth' => $medical->groupBy('group_label'),
            'preparedBy' => $signatories['prepared_by'],
            'checkedBy' => $signatories['checked_by'],
        ];
    }

    public function recalculate(CollectionSummaryPeriod $period): CollectionSummaryPeriod
    {
        $sums = $period->items()
            ->selectRaw('section, SUM(amount) as total')
            ->groupBy('section')
            ->pluck('total', 'section');

        $totalFunds = round((float) $period->forwarded_balance + (float) ($sums[CollectionSummaryItem::SECTION_FUNDS] ?? 0), 2);
        $totalDisbursements = round((float) ($sums[CollectionSummaryItem::SECTION_DISBURSEMENTS] ?? 0), 2);

        $period->update([
            'total_funds' => $totalFunds,
            'total_disbursements' => $totalDisbursements,
            'cash_on_hand' => round($totalFunds - $totalDisbursements, 2),
        ]);

        return $period->refresh();
    }

    private function syncSourceItems(CollectionSummaryPeriod $period): void
    {
        $from = $period->period_from->toDateString();
        $to = $period->period_to->toDateString();

        // Keep label/notes edits made on system lines across a refresh
        $edits = $period->items()->where('is_manual', false)->get()->keyBy('source_reference');
        $period->items()->where('is_manual', false)->delete();

        $transfers = $this->collectionSummaryService->ffaDisbursementsForCampus($period->campus_id, $from, $to, 'approved_at');

        foreach ($transfers->values() as $index => $transfer) {
            $period->items()->create([
                'section' => CollectionSummaryItem::SECTION_FUNDS,
                'label' => $edits->get($transfer->id)?->label ?? $transfer->description,
                'notes' => $edits->get($transfer->id)?->notes,
                'amount' => $transfer->amount,
                'entry_date' => $transfer->approved_at,
                'is_manual' => false,
                'source_type' => CollectionSummaryItem::SOURCE_FUND_TRANSFER,
                'source_reference' => $transfer->id,
                'sort_order' => $index,
            ]);
        }

        // Members (and the claim's member) are campus-scoped; bypass so federated users see the campus's releases.
        $payments = ClaimPayment::query()
            ->whereNotNull('released_at')
            ->whereBetween('released_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])
            ->whereHas('claim', fn ($claim) => $claim->withoutGlobalScopes()->whereHas(
                'member',
                fn ($member) => $member->withoutGlobalScopes()->where('campus_id', $period->campus_id)
            ))
            ->with(['claim' => fn ($claim) => $claim->withoutGlobalScopes()->with(['member' => fn ($member) => $member->withoutGlobalScopes()])])
            ->oldest('released_at')
            ->get();

        foreach ($payments->values() as $index => $payment) {
            $memberName = Str::squish($payment->claim?->member?->full_name ?? '') ?: 'Claim #' . $payment->claim?->claim_number;

            $period->items()->create([
                'section' => CollectionSummaryItem::SECTION_DISBURSEMENTS,
                'label' => $edits->get($payment->id)?->label ?? $memberName,
                'notes' => $edits->get($payment->id)?->notes,
                'amount' => (float) ($payment->amount ?: $payment->claim?->approved_amount),
                'entry_date' => $payment->released_at,
                'group_label' => $payment->released_at->format('F Y'),
                'is_manual' => false,
                'source_type' => CollectionSummaryItem::SOURCE_CLAIM_PAYMENT,
                'source_reference' => $payment->id,
                'sort_order' => $index,
            ]);
        }
    }

    private function assertDraft(CollectionSummaryPeriod $period): void
    {
        if ($period->isFinalized()) {
            throw ValidationException::withMessages(['period' => 'This cash flow period is finalized and can no longer be changed.']);
        }
    }
}

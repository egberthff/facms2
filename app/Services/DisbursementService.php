<?php
namespace App\Services;

use App\Models\Claim;
use App\Models\ClaimPayment;
use App\Models\ClaimType;
use App\Models\Scopes\CampusMemberScope;
use App\Enums\ClaimStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class DisbursementService
{
    public function getDisbursmentAll(array $filters = []): Collection
    {
        $campusFilter = $filters['campus_filter'] ?? null;
        $isPresident = Auth::user()?->can('view all campus data') || Auth::user()?->hasAnyRole(['President', 'Treasurer']);

        $query = ClaimPayment::query()->with([
            'paymentMethod',
            'claim.claimType',
            'claim.member' => function ($query) use ($isPresident) {
                if ($isPresident) {
                    $query->withoutGlobalScope(CampusMemberScope::class);
                }
            },
        ]);

        if (!empty($filters['year'])) {
            $year = (int) $filters['year'];
            $query->where(function ($query) use ($year) {
                $query->whereYear('released_at', $year)
                    ->orWhere(function ($q) use ($year) {
                        $q->whereNull('released_at')->whereYear('created_at', $year);
                    });
            });
        }

        if (!empty($filters['claim_type'])) {
            $claimType = trim($filters['claim_type']);
            $query->whereHas('claim.claimType', function ($query) use ($claimType) {
                // $query->where('name', 'like', '%' . $claimType . '%');
                $query->where('claim_type_id', $claimType);
            });
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'released') {
                $query->whereNotNull('released_at');
            } elseif ($filters['status'] === 'pending') {
                $query->whereNull('released_at');
            }
        }

        if (!empty($campusFilter)) {
            $query->whereHas('claim.member', function ($query) use ($campusFilter, $isPresident) {
                if ($isPresident) {
                    $query->withoutGlobalScope(CampusMemberScope::class);
                }

                $query->where('campus_id', $campusFilter);
            });
        }

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($query) use ($search) {
                $query->whereHas('claim', function ($query) use ($search) {
                    $query->where('claim_number', 'like', $search)
                        ->orWhere('remarks', 'like', $search)
                        ->orWhere('description', 'like', $search);
                })
                ->orWhereHas('claim.member', function ($query) use ($search) {
                    $query->where('first_name', 'like', $search)
                        ->orWhere('middle_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('gcash_number', 'like', $search);
                })
                ->orWhereHas('claim.claimType', function ($query) use ($search) {
                    $query->where('name', 'like', $search);
                })
                ->orWhereHas('paymentMethod', function ($query) use ($search) {
                    $query->where('name', 'like', $search);
                })
                ->orWhere('reference_number', 'like', $search);
            });
        }

        return $query->latest('created_at')->get();
    }

    public function getYearOptions(): array
    {
        $driver = ClaimPayment::query()->getConnection()->getDriverName();

        $yearExpression = match ($driver) {
            'sqlite' => "CAST(strftime('%Y', COALESCE(released_at, created_at)) AS INTEGER)",
            'pgsql' => "DATE_PART('year', COALESCE(released_at, created_at))",
            default => "YEAR(COALESCE(released_at, created_at))",
        };

        return ClaimPayment::query()
            ->selectRaw("{$yearExpression} as year")
            ->groupByRaw($yearExpression)
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->map(fn ($year) => (int) $year)
            ->values()
            ->all();
    }

    public function getClaimTypeOptions(): array
    {
        // return Claim::query()
        //     ->with('claimType')
        //     ->get()
        //     ->pluck('claimType.name')
        //     ->filter()
        //     ->unique()
        //     ->sort()
        //     ->values()
        //     ->all();
        // return ClaimTypes::cases();
       return ClaimType::where('is_active', true)->get()->toArray();
    }

    public function getDisbursementSummary(?string $selectedCampusId = null): array
    {
        $isPresident = Auth::user()?->can('view all campus data') || Auth::user()?->hasAnyRole(['President', 'Treasurer']);

        $approvedClaims = Claim::when($selectedCampusId, function ($query) use ($selectedCampusId, $isPresident) {
                $query->whereHas('member', function ($query) use ($selectedCampusId, $isPresident) {
                    if ($isPresident) {
                        $query->withoutGlobalScope(CampusMemberScope::class);
                    }

                    $query->where('campus_id', $selectedCampusId);
                });
            })
            ->whereIn('status', [ClaimStatus::Approved->value, ClaimStatus::Disbursed->value])
            ->get();

        $approvedAmount = (float) $approvedClaims->sum('approved_amount');

        $releasedAmountQuery = ClaimPayment::join('claims', 'claim_payments.claim_id', '=', 'claims.id')
            ->join('members', 'claims.member_id', '=', 'members.id');

        if ($selectedCampusId) {
            $releasedAmountQuery->where('members.campus_id', $selectedCampusId);
        }

        $releasedAmount = (float) $releasedAmountQuery->where('status', 'Disbursed')->sum('claim_payments.amount');
        $pendingAmount = max(0, $approvedAmount - $releasedAmount);

        $releasedCountQuery = ClaimPayment::query();

        if ($selectedCampusId) {
            $releasedCountQuery->join('claims', 'claim_payments.claim_id', '=', 'claims.id')
                ->join('members', 'claims.member_id', '=', 'members.id')
                ->where('members.campus_id', $selectedCampusId);
        }

        return [
            'approved_claims' => $approvedClaims->count(),
            'approved_amount' => $approvedAmount,
            'released_amount' => $releasedAmount,
            'pending_amount' => $pendingAmount,
            'released_count' => $releasedCountQuery->whereNotNull('released_at')->count(),
        ];
    }

     /**
     * Get the total released claim payments sum, filtered by optional campus.
     */
    public function getTotalDisbursements(?string $selectedCampusId = null, ?string $cycle = null): float
    {
        $query = ClaimPayment::join('claims', 'claim_payments.claim_id', '=', 'claims.id')
            ->join('members', 'claims.member_id', '=', 'members.id');

        if ($selectedCampusId) {
            $query->where('members.campus_id', $selectedCampusId);
        }

        if ($cycle && $selectedCampusId) {
            [$cycleStart, $cycleEnd] = app(\App\Services\CycleService::class)->resolve($cycle, $selectedCampusId);
            $query->whereBetween('claim_payments.released_at', [$cycleStart, $cycleEnd]);
        }

        return (float) $query->sum('claim_payments.amount');
    }
}
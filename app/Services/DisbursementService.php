<?php
namespace App\Services;

use App\Models\Claim;
use App\Models\ClaimPayment;
use App\Enums\ClaimStatus;
use Illuminate\Database\Eloquent\Collection;

class DisbursementService
{
    public function getDisbursmentAll(array $filters = []): Collection
    {
        $query = ClaimPayment::query()->with(['paymentMethod', 'claim.claimType', 'claim.member']);

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
                $query->where('name', 'like', '%' . $claimType . '%');
            });
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'released') {
                $query->whereNotNull('released_at');
            } elseif ($filters['status'] === 'pending') {
                $query->whereNull('released_at');
            }
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
        return ClaimPayment::query()
            ->selectRaw('YEAR(COALESCE(released_at, created_at)) as year')
            ->groupByRaw('YEAR(COALESCE(released_at, created_at))')
            ->orderByDesc('year')
            ->pluck('year')
            ->filter()
            ->map(fn ($year) => (int) $year)
            ->values()
            ->all();
    }

    public function getClaimTypeOptions(): array
    {
        return Claim::query()
            ->with('claimType')
            ->get()
            ->pluck('claimType.name')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function getDisbursementSummary(): array
    {
        $approvedClaims = Claim::whereIn('status', [ClaimStatus::Approved->value, ClaimStatus::Disbursed->value])
            ->get();

        $approvedAmount = (float) $approvedClaims->sum('approved_amount');
        $releasedAmount = (float) ClaimPayment::sum('amount');
        $pendingAmount = max(0, $approvedAmount - $releasedAmount);

        return [
            'approved_claims' => $approvedClaims->count(),
            'approved_amount' => $approvedAmount,
            'released_amount' => $releasedAmount,
            'pending_amount' => $pendingAmount,
            'released_count' => ClaimPayment::whereNotNull('released_at')->count(),
        ];
    }

     /**
     * Get the total released claim payments sum, filtered by optional campus.
     */
    public function getTotalDisbursements(?string $selectedCampusId = null): float
    {
        $query = ClaimPayment::join('claims', 'claim_payments.claim_id', '=', 'claims.id')
            ->join('members', 'claims.member_id', '=', 'members.id');

        if ($selectedCampusId) {
            $query->where('members.campus_id', $selectedCampusId);
        }

        return (float) $query->sum('claim_payments.amount');
    }
}
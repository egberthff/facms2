<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\FundAccount;
use App\Models\FundTransfer;
use App\Models\User;
use App\Services\MemberService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class CollectionSummaryService
{
    private MemberService $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }
    public function createTransfer(array $data, User $user): FundTransfer
    {
        $account = FundAccount::query()->where('code', 'FFA')->where('is_active', true)->first();

        if (!$account) {
            throw ValidationException::withMessages(['fund_account' => 'The FFA fund account is not configured.']);
        }

        $isRemittance = $data['type'] === 'campus_remittance';

        return FundTransfer::create([
            'fund_account_id' => $account->id,
            'type' => $data['type'],
            'source_campus_id' => $isRemittance ? $data['campus_id'] : null,
            'destination_campus_id' => $isRemittance ? null : $data['campus_id'],
            'reference_number' => $data['reference_number'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'created_by' => $user->user_id,
            'status' => 'pending',
        ]);
    }

    public function approveTransfer(FundTransfer $transfer, User $user): FundTransfer
    {
        return app(\App\Services\TreasuryLedgerService::class)->postApprovedTransfer($transfer, $user->user_id);
    }

    public function fundAccount(): ?FundAccount
    {
        return FundAccount::query()->where('code', 'FFA')->where('is_active', true)->first();
    }

    public function transfersForCampus(?string $campusId, ?string $from = null, ?string $to = null): array
    {
        $query = FundTransfer::query()->approved()->with(['sourceCampus', 'destinationCampus']);

        if ($from) {
            $query->where('created_at', '>=', Carbon::parse($from)->startOfDay());
        }

        if ($to) {
            $query->where('created_at', '<=', Carbon::parse($to)->endOfDay());
        }

        $collections = (clone $query)
            ->with('sourceCampus')
            ->where('type', 'campus_remittance')
            ->when($campusId, fn ($query) => $query->where('source_campus_id', $campusId))
            ->oldest('created_at')
            ->get()
            ->map(fn (FundTransfer $transfer) => (object) [
                'label' => $transfer->description,
                'amount' => $transfer->amount,
                'date' => $transfer->created_at,
                'reference_number' => $transfer->reference_number,
                'campus' => $transfer->source_campus_id,
            ]);

        $disbursements = (clone $query)
            ->with('destinationCampus')
            ->where('type', 'ffa_disbursement')
            ->when($campusId, fn ($query) => $query->where('destination_campus_id', $campusId))
            ->oldest('created_at')
            ->get()
            ->map(fn (FundTransfer $transfer) => (object) [
                'label' => $transfer->description,
                'amount' => $transfer->amount,
                'date' => $transfer->created_at,
                'reference_number' => $transfer->reference_number,
                'campus' => $transfer->destination_campus_id,
            ]);

        $campusTreasuer  = $this->memberService->getTreasurer($campusId);
        // $campusAuditor   = $this->memberService->getAuditor($campusId);
        $campusAuditor   = '';
        $campusPresident = $this->memberService->getPresident($campusId);

        return [
            'collections' => $collections,
            'disbursements' => $disbursements,
            'collection_total' => (float) $collections->sum('amount'),
            'disbursement_total' => (float) $disbursements->sum('amount'),
            'campus_treasurer' => $campusTreasuer?->name ?? '',
            // 'campus_auditor' => $campusAuditor?->name ?? '',
            'campus_auditor' =>  '',
            'campus_president' => $campusPresident?->name ?? '',
        ];
    }

    public function buildPrintableData(?string $campusId = null, ?string $from = null, ?string $to = null): array
    {
        $campus = $campusId ? Campus::find($campusId) : null;
        $data = $this->transfersForCampus($campusId, $from, $to);

        return [
            'campus' => $campus,
            ...$data,
            'period_label' => $from && $to
                ? Carbon::parse($from)->format('F Y') . ' - ' . Carbon::parse($to)->format('F Y')
                : 'All approved transfers',
        ];
    }

    public function store(array $data, User $user): never
    {
        throw ValidationException::withMessages([
            'items' => 'Manual collection summary rows are no longer supported. Record a fund transfer instead.',
        ]);
    }
}

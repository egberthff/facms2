<?php
namespace App\Services;

use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\ClaimType;
use App\Models\Member;
use App\Models\PaymentMethod;
use App\Models\ContributionBatch;
use App\Models\ContributionDetail;
use App\Models\ClaimPayment;
use App\Models\Scopes\CampusMemberScope;
use App\Enums\ClaimStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use App\Services\TreasuryLedgerService;
use Exception;

class ClaimService
{
    private TreasuryLedgerService $ledgerService;
    private MemberClaimCycleService $cycleService;
    public function __construct(TreasuryLedgerService $ledgerService, MemberClaimCycleService $cycleService){
        $this->ledgerService = $ledgerService;
        $this->cycleService = $cycleService;
    }
    /**
     * Handle the full claim creation business logic.
     */
    public function createClaim(array $data, array $files): Claim
    {

        try {
            DB::beginTransaction();
            $claimNumber = 'CLM-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            $submittedAt = now();

            // Lock the member row so concurrent submissions resolve the same cycle and balance in turn
            $member = Member::withoutGlobalScopes()
                ->whereKey(Auth::user()->member->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Authoritative eligibility check: the form request check ran without the lock
            $this->cycleService->assertEligible($member, (float) $data['amount'], $submittedAt);
            $cycle = $this->cycleService->resolveCycleForClaim($member, $submittedAt);

            // 1. Create the Claim record
            $claim = Claim::create([
                'claim_number'      => $claimNumber,
                'member_id'         => $member->id,
                'member_claim_cycle_id' => $cycle->id,
                'claim_type_id'     => $data['claimType'],
                'payment_method_id' => $data['paymentMethod'],
                'requested_amount'  => $data['amount'],
                'approved_amount'   => null,
                'annual_limit'      => $this->toMysqlDecimal($cycle->claim_limit),
                'status'            => ClaimStatus::Pending->value,
                'description'       => $data['description'] ?? null,
                'hospital_name'     => $data['hospital'] ?? null,
                'treatment_start_date' => $data['incidentDate'] ?? null,
                'treatment_end_date'   => $data['treatmentEndDate'] ?? null,
                'submitted_at'      => $submittedAt,
            ]);

            // 2. Handle File Uploads if present
            if (!empty($files)) {
                $this->uploadClaimDocuments($claim, $files);
            }

            DB::commit();
            return $claim;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e; // Rethrow to let the controller handle the HTTP error response
        }
    }

    /**
     * Process and store claim documents.
     */
    private function uploadClaimDocuments(Claim $claim, array $files): void
    {
        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $storedFilename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            
            $path = $file->storeAs('documents/claims', $storedFilename, 'public');

            ClaimDocument::create([
                'claim_id'          => $claim->id,
                'document_type'     => pathinfo($originalName, PATHINFO_EXTENSION),
                'original_filename' => $originalName,
                'stored_filename'   => $storedFilename,
                'file_path'         => $path,
                'mime_type'         => $file->getMimeType(),
                'file_size'         => $file->getSize(),
            ]);
        }
    }

    /**
     * Helper to format value into MySQL decimal.
     */
     private function toMysqlDecimal($number, int $decimals = 2): string 
    {
        if (is_string($number)) {
            $number = str_replace(',', '.', $number);
            $number = preg_replace('/[^0-9.-]/', '', $number);
        }

        return number_format((float)$number, $decimals, '.', '');
    }

    /**
     * Fetch all claims belonging to a specific user's member profile.
     */
    public function getClaimsForUser(User $user): Collection
    {
        $member = $user->member;
        // $member->load('campus');
        // Return empty Eloquent collection instead of crashing if member doesn't exist
        if (!$member) {
            return new Collection();
        }

        $claims = Claim::forMember($member->id)
            ->with(['claimType', 'paymentMethod', 'reviewer', 'documents', 'member.campus', 'memberClaimCycle'])
            ->latest('submitted_at')
            ->get();

        $this->cycleService->attachCycleSummaries($claims);

        return $claims;
    }

    /**
     * Get all reference data required to build the claim submission form.
     */
    public function getFormData(): array
    {
        return [
            'title'              => 'Submit Claim',
            'claimTypes'         => ClaimType::all(),
            'paymentMethods'     => PaymentMethod::all(),
            'paymentMethodBanks' => ['BDO', 'BPI', 'PNB', 'LandBank', 'Metrobank'], // Fixed typo: Mentrobank -> Metrobank
        ];
    }

    public function getAllClaims( array $userRoles = [], ?string $selectedCampusId = null, ?array $cycle = []): Collection
    {
        $query = Claim::with(['claimType', 'paymentMethod', 'reviewer', 'documents', 'member', 'memberClaimCycle'])
            ->withoutGlobalScope(CampusMemberScope::class);
    
        // 1. Filter by Campus if selected
        if ($selectedCampusId) {
            $query->whereHas('member', function ($query) use ($selectedCampusId) {
                $query->withoutGlobalScope(CampusMemberScope::class)
                    ->where('campus_id', $selectedCampusId);
            });
        }

        // 2. Filter strictly by the bounds of the active Cycle Date Range
        if ($cycle && count($cycle) >= 2) {
            [$startDate, $endDate] = $cycle;
            $query->whereBetween('submitted_at', [$startDate, $endDate]);
        }
    
    //    if( !empty($userRoles)) {
    //         $query->where(function ($query) use ($userRoles) {

    //             if (in_array('President', $userRoles)) {
    //                 $query->orWhereIn('status', ['Pending', 'Processing', 'Approved', 'Disbursed', 'Rejected']);
    //             }

    //             if (in_array('Treasurer', $userRoles)) {
    //                 $query->orWhereIn('status', ['For Review FP', 'Processing', 'Approved', 'Disbursed']);
    //             }
    
    //             if (in_array('Federated President', $userRoles)) {
    //                 $query->orWhereIn('status', ['Pending', 'Rejected']);
    //             }
    
    //             if (in_array('Federated Treasurer', $userRoles)) {
    //                 $query->orWhereIn('status', ['For Review FP', 'Processing', 'Approved', 'Disbursed']);
    //             }
    //         });
    //     }
    
        $claims = $query->latest('submitted_at')->get();
        $this->cycleService->attachCycleSummaries($claims);

        return $claims;
    }

    public function updateStatus(Claim $claim, array $data): Claim
    {
        $rawStatus = $data['status'] ?? $data['_status'] ?? null;
        
        // Convert the raw string input safely into your Type-Safe Enum instance
        $statusEnum = $rawStatus instanceof ClaimStatus 
            ? $rawStatus 
            : ClaimStatus::tryFrom(trim((string) $rawStatus));
    
        if (!$statusEnum) {
            throw new Exception("Unsupported claim status update transition value provided: [{$rawStatus}].");
        }
    
        try {
            DB::beginTransaction();
    
            $now = now();
            $userId = Auth::user()?->user_id ?? Auth::id();
            $remarks = $data['remarks'] ?? $data['payment_remarks'] ?? null;
    
            $claimUpdateData = [
                'status'     => $statusEnum->value, // Saves 'For Disbursement', 'For Review FP', etc.
                'remarks'    => $remarks,
                'updated_at' => $now,
            ];
    
            // Clean, type-safe switch matching against the Enum cases directly!
            switch ($statusEnum) {
                case ClaimStatus::Approved:
                    $claimUpdateData = $this->handleApprovedStatus($claim, $data, $claimUpdateData, $userId, $now, $remarks);
                    break;
    
                case ClaimStatus::ForReviewFp:
                    $claimUpdateData = $this->handleForReviewFpStatus($claim, $claimUpdateData, $userId, $now);
                    break;
    
                case ClaimStatus::Processing:
                    $claimUpdateData = $this->handleProcessingStatus($claim, $claimUpdateData, $userId, $now);
                    break;
    
                case ClaimStatus::ForDisbursement:
                    $claimUpdateData = $this->handleForDisbursementStatus($claim, $claimUpdateData, $userId, $now);
                    break;
    
                case ClaimStatus::Disbursed:
                    $claimUpdateData = $this->handleDisbursedStatus($claim, $data, $claimUpdateData, $userId, $now, $remarks);
                    break;
    
                case ClaimStatus::Rejected:
                    $claimUpdateData = $this->handleRejectedStatus($claim, $claimUpdateData, $now);
                    break;
    
                case ClaimStatus::Pending:
                    // No specific transactional mutations required for initial submission states
                    break;
            }
    
            $claim->update($claimUpdateData);

            // A cycle that only held rejected claims is released so the next claim starts a fresh one
            if ($statusEnum === ClaimStatus::Rejected) {
                $this->cycleService->releaseIfEmpty($claim->memberClaimCycle);
            }

            DB::commit();
            return $claim;
    
        } catch (Exception $e) {
            DB::rollBack();
            throw $e; 
        }
    }
    

    /**
     * STEP 2 to 3: Campus President Approves.
     * Generates Local Contribution Batches & Initial Payment record placeholders.
     */
    private function handleApprovedStatus(Claim $claim, array $data, array $updateData, $userId, $now, $remarks): array
    {
        $claim->loadMissing(['claimType', 'member']);

        if (!$claim->claimType || !$claim->claimType->is_active) {
            throw new Exception('The associated claim type is invalid or inactive.');
        }

        $amountPerMember = (float) $claim->claimType->required_amount;

        // Extract active members strictly belonging to the claimant's local campus
        $members = Member::where('membership_status', 'Active')
            ->where('campus_id', $claim->member->campus_id)
            ->get();
                
        $totalMembersCount = $members->count();

        if ($totalMembersCount > 0) {
            $totalAmount = $totalMembersCount * $amountPerMember;
            $batchReference = 'BTCH-' . $now->format('Ymd') . '-' . strtoupper(Str::random(6));

            $contributionBatch = ContributionBatch::create([
                'claim_id'           => $claim->id,
                'batch_reference'    => $batchReference,
                'amount_per_member'  => $amountPerMember,
                'total_members'      => $totalMembersCount,
                'total_amount'       => $totalAmount,
                'generated_at'       => $now,
                'generated_by'       => $userId
            ]);

            $batchData = [];
            foreach ($members as $member) {
                $batchData[] = [
                    'id'                    => (string) Str::uuid(),
                    'contribution_batch_id' => $contributionBatch->id,
                    'member_id'             => $member->id,
                    'amount'                => $amountPerMember,
                    'status'                => 'Unpaid',
                    'paid_at'               => null, 
                    'created_at'            => $now,
                    'updated_at'            => $now
                ];
            }

            DB::table('contribution_details')->insert($batchData);

            // Initialize base payment snapshot payload
            $claim->payment()->updateOrCreate(
                ['claim_id' => $claim->id],
                [
                    'id'                => (string) Str::uuid(),
                    'payment_method_id' => $claim->payment_method_id,
                    'reference_number'  => null,
                    'amount'            => $this->toMysqlDecimal($claim->requested_amount),
                    'processed_by'      => $userId,
                    'released_at'       => null,
                    'remarks'           => $remarks,
                ]
            );
        }

        $updateData['approved_amount'] = $claim->requested_amount ?? 0.00;
        $updateData['approved_at']     = $now;

        return $updateData;
    }

    /**
     * STEP 3 to 4: Passed to Federated President for Official Record Filing.
     */
    private function handleForReviewFpStatus(Claim $claim, array $updateData, $userId, $now): array
    {
        $updateData['reviewed_by'] = $userId;
        // Hook additional logging or docket verification models here if needed.
        return $updateData;
    }

    /**
     * STEP 4 to 5 & 6: Passed to Federated Treasurer for Final Verification.
     */
    private function handleProcessingStatus(Claim $claim, array $updateData, $userId, $now): array
    {
        $updateData['reviewed_by'] = $userId;
        // Hook final auditing metrics or document tracking updates here if needed.
        return $updateData;
    }

    /**
     * STEP 6 to 7: Federated Treasurer issues "Go Signal" back to Campus Level.
     */
    private function handleForDisbursementStatus(Claim $claim, array $updateData, $userId, $now): array
    {
        // Records that the official authorization threshold has been crossed
        // $updateData['authorized_at'] = $now;
        // $updateData['authorized_by'] = $userId;
        return $updateData;
    }

    /**
     * STEP 7 to 8: Campus Treasurer physically issues Payout & updates Ledger balances.
     */
    private function handleDisbursedStatus(Claim $claim, array $data, array $updateData, $userId, $now, $remarks): array
    {
        $amount = $data['amount'] ?? $claim->approved_amount ?? $claim->requested_amount;
        $paymentMethodId = $data['payment_method_id'] ?? $claim->payment_method_id;
        $referenceNumber = $data['reference_number'] ?? null;

        if (blank($amount)) {
            throw new Exception('A disbursement amount is required.');
        }

        if (blank($paymentMethodId)) {
            throw new Exception('A payment method is required for disbursement.');
        }

        if (!empty($data['proof_file'])) {
            $storedFilename = Str::uuid() . '.' . $data['proof_file']->getClientOriginalExtension();
            $path = $data['proof_file']->storeAs('documents/disbursements', $storedFilename, 'public');
        }

        // Finalize payment distribution logs
        $claim->payment()->updateOrCreate(
            ['claim_id' => $claim->id],
            [
                'id'                => (string) Str::uuid(),
                'payment_method_id' => $paymentMethodId,
                'reference_number'  => $referenceNumber,
                'amount'            => $this->toMysqlDecimal($amount),
                'processed_by'      => $userId,
                'receipt_path'      => $path ?? null,
                'released_at'       => $now,
                'remarks'           => $remarks,
            ]
        );

        // Commit ledger ledger transaction debiting funds
        $this->ledgerService->recordTransaction([
            'campus_id'        => $claim->member->campus_id,
            'sourceable_type'  => get_class($claim),
            'sourceable_id'    => $claim->id,
            'reference_number' => $referenceNumber ?? 'GL-' . time(),
            'description'      => "Disbursement for Claim #: " . $claim->claim_number,
            'type'             => 'Debit',
            'amount'           => $amount,
        ]);

        $updateData['approved_amount'] = $this->toMysqlDecimal($amount);
        $updateData['disbursed_at']    = $now;

        return $updateData;
    }

    /**
     * EXCEPTION PATH: Claim drops out of queue and returns to standard member.
     */
    private function handleRejectedStatus(Claim $claim, array $updateData, $now): array
    {
        // $updateData['rejected_at'] = $now;
        return $updateData;
    }

    

     /**
     * Update the claim status securely. - working
     */
    // public function updateStatus(Claim $claim, array $data): Claim
    // {
    //     $rawStatus = $data['status'] ?? $data['_status'] ?? null;

    //     // Standardize text check against Schema Enums ('Approved')
    //     $statusInput = trim((string) $rawStatus);
    //     $isDisbursed = strtolower($statusInput) === 'disbursed';

    //     // Put ALL transactional logic inside the try block so rollbacks work reliably
    //     try {
    //         DB::beginTransaction();


    //         $amount = $data['amount'] ?? $claim->approved_amount ?? $claim->requested_amount;
    //         if ($isDisbursed) {
    //             $referenceNumber = $data['reference_number'] ?? null;
    //             $remarks = $data['payment_remarks'] ?? $data['remarks'] ?? null;
    //             $paymentMethodId = $data['payment_method_id'] ?? $claim->payment_method_id;

    //             if (blank($amount)) {
    //                 throw new Exception('A disbursement amount is required.');
    //             }

    //             if (blank($paymentMethodId)) {
    //                 throw new Exception('A payment method is required for disbursement.');
    //             }

    //             if (!empty($data['proof_file'])) {
    //                 $originalName = $data['proof_file']->getClientOriginalName();
    //                 $storedFilename = Str::uuid() . '.' . $data['proof_file']->getClientOriginalExtension();

    //                 $path = $data['proof_file']->storeAs('documents/disbursements', $storedFilename, 'public');
    //             }

    //             //Insert claim payments
    //             $claim->payment()->updateOrCreate(
    //                 ['claim_id' => $claim->id],
    //                 [
    //                     'id' => (string) Str::uuid(),
    //                     'payment_method_id' => $paymentMethodId,
    //                     'reference_number' => $referenceNumber,
    //                     'amount' => $this->toMysqlDecimal($amount),
    //                     'processed_by' => Auth::user()?->user_id ?? Auth::id(),
    //                     'receipt_path' => $path ?? null,
    //                     'released_at' => now(),
    //                     'remarks' => $remarks,
    //                 ]
    //             );
    //                 //Insert ledger record
    //                 $this->ledgerService->recordTransaction([
    //                 'campus_id'        => $claim->member->campus_id,
    //                 'sourceable_type'  => get_class($claim),
    //                 'sourceable_id'    => $claim->id,
    //                 'reference_number' => $data['reference_number'] ?? 'GL-' . time(),
    //                 'description'      => "Disbursement for Claim #: " . $claim->claim_number,
    //                 'type'             => 'Debit',
    //                 'amount'           => $data['amount'],
    //         ]);
    //         }

    //         if (strtolower($statusInput) === 'approved') {

    //             $claim->loadMissing('claimType');

    //             if (!$claim->claimType || !$claim->claimType->is_active) {
    //                throw new Exception('The associated claim type is invalid or inactive.');
    //             }

    //             $amountPerMember = (float) $claim->claimType->required_amount;

    //             // Extract dynamically matching active members
    //             $members = Member::where('membership_status', 'Active')->get();
    //             $totalMembersCount = $members->count();

    //             // Guard check: Avoid division by zero or empty processing
    //             if ($totalMembersCount > 0) {
    //                 $totalAmount = $totalMembersCount * $amountPerMember;
    //                 $batch_reference = 'BTCH -' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    //                 // Correctly save model and extract its raw primary key string
    //                 $contributionBatch = ContributionBatch::create([
    //                     'claim_id'           => $claim->id,
    //                     'batch_reference'    => $batch_reference,
    //                     'amount_per_member'  => $amountPerMember,
    //                     'total_members'      => $totalMembersCount,
    //                     'total_amount'      => $totalAmount,
    //                     'generated_at'       => now(),
    //                     'generated_by'       => Auth::user()->user_id 
    //                 ]);

    //                 // Structure data into a batch array for a single database payload
    //                 $batchData = [];
    //                 $now = now();

    //                 foreach ($members as $member) {
    //                     $batchData[] = [
    //                         'id'                     => (string) Str::uuid(), // Required since schema uses UUID primary key
    //                         'contribution_batch_id'  => $contributionBatch->id,
    //                         'member_id'              => $member->id,
    //                         'amount'                 => $amountPerMember,
    //                         'status'                 => 'Unpaid',
    //                         'paid_at'                => null, 
    //                         'created_at'             => $now,
    //                         'updated_at'             => $now
    //                     ];
    //                 }

    //                 // Execute highly optimized query payload 
    //                 DB::table('contribution_details')->insert($batchData);

    //                 $claim->payment()->updateOrCreate(
    //                 ['claim_id' => $claim->id],
    //                 [
    //                     'id' => (string) Str::uuid(),
    //                     'payment_method_id' => $claim->payment_method_id,
    //                     'reference_number' => null,
    //                     'amount' => $this->toMysqlDecimal($claim->requested_amount),
    //                     'processed_by' => Auth::user()?->user_id ?? Auth::id(),
    //                     'released_at' => null,
    //                     'remarks' => $data['remarks'] ?? null,
    //                 ]
    //             );
    //             }
    //         }

    //         $claimUpdateData = array();
    //         switch (strtolower($statusInput)) {
    //             case "processing" : {
    //                 $claimUpdateData['status'] = $statusInput;
    //                 $claimUpdateData['reviewed_by'] = Auth::user()->id;
    //                 $claimUpdateData['updated_at'] = now();
    //                 break;
    //             }
    //             case 'approved' : {
    //                 $claimUpdateData['status'] = $statusInput;
    //                 $claimUpdateData['remarks'] = $data['remarks'] ?? null;
    //                 $claimUpdateData['approved_amount'] = $claim->requested_amount ?? 0.00;
    //                 $claimUpdateData['approved_at'] = now();
    //                 $claimUpdateData['updated_at'] = now();
    //                 break;
    //             }
    //             case 'disbursed' : {
    //                 $claimUpdateData['status'] = $statusInput;
    //                 $claimUpdateData['remarks'] = $data['remarks'] ?? null;
    //                 $claimUpdateData['approved_amount'] =  $this->toMysqlDecimal($amount);
    //                 $claimUpdateData['disbursed_at'] = now();
    //                 $claimUpdateData['updated_at'] = now();
    //                 break;
    //             }
    //             case 'rejected' : {
    //                 $claimUpdateData['status'] = $statusInput;
    //                 $claimUpdateData['remarks'] = $data['remarks'] ?? null;
    //                 $claimUpdateData['updated_at'] = now();
    //             }

    //         }

    //         // Complete the parent workflow update
    //         $claim->update($claimUpdateData);

    //         DB::commit();
    //         return $claim;

    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         throw $e; 
    //     }
    // }

    public function getClaimType(?string $claimTypeId = null): Collection|ClaimType|null
    {
        if ($claimTypeId) {
            return ClaimType::find($claimTypeId); 
        }

        return ClaimType::all()->where('is_active', TRUE);
    }

}

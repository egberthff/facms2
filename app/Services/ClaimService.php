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
use App\Enums\ClaimStatus;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class ClaimService
{
    /**
     * Handle the full claim creation business logic.
     */
    public function createClaim(array $data, array $files): Claim
    {
        DB::beginTransaction();

        try {
            $claimNumber = 'CLM-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            $annualLimit = SystemSetting::annualLimit()->first();

            // 1. Create the Claim record
            $claim = Claim::create([
                'claim_number'      => $claimNumber,
                'member_id'         => Auth::user()->member->id,
                'claim_type_id'     => $data['claimType'],
                'payment_method_id' => $data['paymentMethod'],
                'requested_amount'  => $data['amount'],
                'approved_amount'   => null,
                'annual_limit'      => $this->toMysqlDecimal($annualLimit->setting_value),
                'status'            => ClaimStatus::Pending->value,
                'description'       => $data['description'] ?? null,
                'submitted_at'      => now(),
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

        // Return empty Eloquent collection instead of crashing if member doesn't exist
        if (!$member) {
            return new Collection();
        }

        return Claim::forMember($member->id)
            ->with(['claimType', 'paymentMethod', 'reviewer', 'documents'])
            ->latest('submitted_at')
            ->get();
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

    public function getAllClaims(): Collection
    {
       return Claim::with(['claimType', 'paymentMethod', 'reviewer', 'documents'])
            ->latest('submitted_at')
            ->get();
        
    }

     /**
     * Update the claim status securely.
     */
    public function updateStatus(Claim $claim, array $data): Claim
    {
        // \Log::error(print_r($data, true));
        $rawStatus = $data['status'] ?? $data['_status'] ?? null;

        // if (blank($rawStatus)) {
        //     throw new Exception('The status field is required.');
        // }

        // Standardize text check against Schema Enums ('Approved')
        $statusInput = trim((string) $rawStatus);
        $isDisbursed = strtolower($statusInput) === 'disbursed';

        // Put ALL transactional logic inside the try block so rollbacks work reliably
        try {
            DB::beginTransaction();

            $amount = null;

            if ($isDisbursed) {
                $amount = $data['amount'] ?? $claim->approved_amount ?? $claim->requested_amount;
                $referenceNumber = $data['reference_number'] ?? null;
                $remarks = $data['payment_remarks'] ?? $data['remarks'] ?? null;
                $paymentMethodId = $data['payment_method_id'] ?? $claim->payment_method_id;

                if (blank($amount)) {
                    throw new Exception('A disbursement amount is required.');
                }

                if (blank($paymentMethodId)) {
                    throw new Exception('A payment method is required for disbursement.');
                }

                $claim->payment()->updateOrCreate(
                    ['claim_id' => $claim->id],
                    [
                        'id' => (string) Str::uuid(),
                        'payment_method_id' => $paymentMethodId,
                        'reference_number' => $referenceNumber,
                        'amount' => $this->toMysqlDecimal($amount),
                        'processed_by' => Auth::user()?->user_id ?? Auth::id(),
                        'released_at' => now(),
                        'remarks' => $remarks,
                    ]
                );

                if (!empty($data['proof_file'])) {
                    $this->uploadClaimDocuments($claim, [$data['proof_file']]);
                }
            }

            if (strtolower($statusInput) === 'approved') {

                $claim->loadMissing('claimType');

                if (!$claim->claimType || !$claim->claimType->is_active) {
                   throw new Exception('The associated claim type is invalid or inactive.');
                }

                $amountPerMember = (float) $claim->claimType->required_amount;

                // Extract dynamically matching active members
                $members = Member::where('membership_status', 'Active')->get();
                $totalMembersCount = $members->count();

                // Guard check: Avoid division by zero or empty processing
                if ($totalMembersCount > 0) {
                    $totalAmount = $totalMembersCount * $amountPerMember;

                    // Correctly save model and extract its raw primary key string
                    $contributionBatch = ContributionBatch::create([
                        'claim_id'           => $claim->id,
                        'amount_per_member'  => $amountPerMember,
                        'total_members'      => $totalMembersCount,
                        'total_amount'      => $totalAmount,
                        'generated_at'       => now(),
                        'generated_by'       => Auth::user()->user_id 
                    ]);

                    // Structure data into a batch array for a single database payload
                    $batchData = [];
                    $now = now();

                    foreach ($members as $member) {
                        $batchData[] = [
                            'id'                     => (string) Str::uuid(), // Required since schema uses UUID primary key
                            'contribution_batch_id'  => $contributionBatch->id,
                            'member_id'              => $member->id,
                            'amount'                 => $amountPerMember,
                            'status'                 => 'Unpaid',
                            'paid_at'                => null, 
                            'created_at'             => $now,
                            'updated_at'             => $now
                        ];
                    }

                    // Execute highly optimized query payload 
                    DB::table('contribution_details')->insert($batchData);
                }
            }

            $claimUpdateData = array();
            switch (strtolower($statusInput)) {
                case "processing" : {
                    $claimUpdateData['status'] = $statusInput;
                    $claimUpdateData['reviewed_by'] = Auth::user()->id;
                    $claimUpdateData['updated_at'] = now();
                    break;
                }
                case 'approved' : {
                    $claimUpdateData['status'] = $statusInput;
                    $claimUpdateData['remarks'] = $data['remarks'] ?? null;
                    $claimUpdateData['approved_at'] = now();
                    $claimUpdateData['updated_at'] = now();
                    break;
                }
                case 'disbursed' : {
                    $claimUpdateData['status'] = $statusInput;
                    $claimUpdateData['remarks'] = $data['remarks'] ?? null;
                    $claimUpdateData['approved_amount'] =  $this->toMysqlDecimal($amount);
                    $claimUpdateData['disbursed_at'] = now();
                    $claimUpdateData['updated_at'] = now();
                    break;
                }
                case 'rejected' : {
                    $claimUpdateData['status'] = $statusInput;
                    $claimUpdateData['remarks'] = $data['remarks'] ?? null;
                    $claimUpdateData['updated_at'] = now();
                }

            }

            // Complete the parent workflow update
            $claim->update($claimUpdateData);

            DB::commit();
            return $claim;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e; 
        }
    }

}

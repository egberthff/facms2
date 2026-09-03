<?php

namespace App\Services;

use App\Mail\WelcomeEMail;
use App\Mail\ClaimSubmittedAlert;
use App\Mail\ClaimStatusUpdated;
use App\Models\User;
use App\Models\Member;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Exception;

class EmailService
{
    /**
     * Send welcome emails to one or multiple registered members.
     *
     * @param mixed $members Can be a single Member model, an array, or a Collection.
     * @return void
     */
    public function sendWelcomeEmails($members): void
    {
        // 1. Normalize input into a standard Laravel collection wrapper
        $memberCollection = wrap_into_collection($members);

        foreach ($memberCollection as $member) {
            try {
                // 2. Traverse the relationship to fetch user data
                $user = $member->user;

                if ($user && $user->email) {
                    Mail::to($user->email)->send(new WelcomeEMail($user->name, $user->email));
                }
            } catch (Exception $e) {
                // 3. Log failures individually so one bad email address doesn't halt the rest
                Log::error("Failed to send welcome email to Member ID {$member->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Alert notification for presidents
     */
    public function newSubmittedClaimAlert($claim): void
    {
        // 1. Wrap into a base support collection
        $claimCollection = wrap_into_collection($claim);
    
        // 2. Safely eager-load relationships if the collection isn't empty
        if ($claimCollection->isNotEmpty()) {
            // If it's a standard Eloquent Collection, this loads it perfectly.
            // If it's a Support Collection holding Eloquent models, this still triggers Eloquent's loader safely.
            $claimCollection->first()->newCollection($claimCollection->all())->loadMissing(['member.user', 'claimType']);
        }
    
        foreach ($claimCollection as $singleClaim) {
            try {
                // Get the claimant's member record
                $member = $singleClaim->member;
    
                if (!$member) {
                    continue; // Skip if no member record is attached to this claim
                }
    
                // Find the member who has the 'President' or 'Federated President' role and matching campus_id
                $presidentMember = Member::with('user')
                    ->where('campus_id', $member->campus_id)
                    ->whereHas('user', function ($query) {
                        $query->role(['President', 'Federated President']); 
                    })
                    ->first();
    
                // Send the email if a president is found
                if ($presidentMember && $presidentMember->user && $presidentMember->user->email) {
                    $emailData = [
                        "memberName"     => $member->user ? $member->user->name : 'Unknown Member', 
                        "claimType"      => $singleClaim->claimType ? $singleClaim->claimType->name : 'N/A', 
                        "claimNumber"    => $singleClaim->claim_number,
                        "amount"         => $singleClaim->requested_amount,
                        "submissionDate" => $singleClaim->submitted_at->format('F j, Y, g:i a'), 
                        "url"            => url('/claims/list') 
                    ];
                
                    Mail::to($presidentMember->user->email)
                        ->send(new ClaimSubmittedAlert($emailData));
                } else {
                    Log::warning("No president user account found for campus_id: {$member->campus_id}");
                }
    
            } catch (\Exception $e) {
                Log::error('Failed to send claim alert: ' . $e->getMessage());
            }
        }
    }
 

    /**
     * Notification email for Members claim status changed
     */
    public function sendClaimStatusUpdate($claim): void
    {
        $claimCollection = wrap_into_collection($claim);

        // Ensure relationships are loaded on the collection safely
        if ($claimCollection->isNotEmpty()) {
            $claimCollection->first()->newCollection($claimCollection->all())->loadMissing(['member.user', 'claimType']);
        }

        foreach ($claimCollection as $singleClaim) {
            try {
                $member = $singleClaim->member;     

                // 1. Structural Check: Ensure the claimant exists and has a valid user email
                if (!$member || !$member->user || !$member->user->email) {
                    Log::warning("Cannot send status update. Claimant user account or email missing for claim ID: {$singleClaim->id}");
                    continue; 
                }       

                // --- SECTION A: SEND MEMBER NOTIFICATION ---
                $memberEmailData = [
                    "recipientName" => $member->user->name,
                    "claimNumber"   => $singleClaim->claim_number,
                    "claimType"     => $singleClaim->claimType ? $singleClaim->claimType->name : 'N/A',
                    "status"        => strtoupper($singleClaim->status), 
                    "amount"        => $singleClaim->requested_amount ?? 0.00,
                    "remarks"       => $singleClaim->status_remarks ?? 'No additional comments provided.',
                    "updatedAt"     => $singleClaim->updated_at->format('F j, Y, g:i a'),
                    "url"           => url('claims/my-list/' . $member->user->user_id)
                ];      

                Mail::to($member->user->email)
                    ->send(new ClaimStatusUpdated('member', $memberEmailData));     


                // --- SECTION B: SEND TREASURER NOTIFICATION (ONLY IF APPROVED) ---
                if (strtoupper(trim($singleClaim->status)) === 'APPROVED') {

                    // Query for the specific regional treasurer
                    $treasurerMember = Member::with('user')
                        ->where('campus_id', $member->campus_id)
                        ->whereHas('user', function ($query) {
                            // Tip: Ensure Spatie Laravel-Permission is configured correctly for array arguments
                            $query->role(['Treasurer', 'Federated Treasurer']); 
                        })
                        ->first();      

                    // CRITICAL FIX: Verify the treasurer was actually found before pulling variables
                    if ($treasurerMember && $treasurerMember->user && $treasurerMember->user->email) {

                        $treasurerEmailData = [
                            "recipientName" => $treasurerMember->user->name,
                            "claimNumber"   => $singleClaim->claim_number,
                            "claimType"     => $singleClaim->claimType ? $singleClaim->claimType->name : 'N/A',
                            "status"        => strtoupper($singleClaim->status), 
                            "amount"        => $singleClaim->approved_amount ?? 0.00, // Uses approved amount for treasurer
                            "remarks"       => $singleClaim->status_remarks ?? 'No additional comments provided.', 
                            "updatedAt"     => $singleClaim->updated_at->format('F j, Y, g:i a'),
                            "url"           => url('claims/list') 
                        ];      

                        Mail::to($treasurerMember->user->email)
                            ->send(new ClaimStatusUpdated('treasurer', $treasurerEmailData));

                    } else {
                        Log::warning("Treasurer alert skipped. No active 'Treasurer' or 'Federated Treasurer' role found linked to campus_id: {$member->campus_id}");
                    }
                }       

            } catch (\Exception $e) {
                Log::error('Failed to send claim status update email for claim ID ' . ($singleClaim->id ?? 'unknown') . ': ' . $e->getMessage());
            }
        }

    }

}

    /**
     * Notification for the treasurer to disburse the claim
     */
    // public function sendDisbursementAlertToTresurer($claim): void
    // {
    //     $claimCollection = wrap_into_collection($claim);


    // } 

/**
 * Helper utility to normalize mixed types into a singular Collection instance.
 */
function wrap_into_collection($items): Collection 
{
    if ($items instanceof Collection) {
        return $items;
    }
    if (is_array($items)) {
        return collect($items);
    }
    return collect([$items]);
}

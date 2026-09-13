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
    
                $status = strtolower(trim($singleClaim->status));

                // Always notify the member
                $this->sendMemberNotification($singleClaim, $member);
                
                // Dynamically route notifications based on status changes
                switch ($status) {
                    case 'rejected':
                        // 1. Notify Member with their specific view layout instructions
                        $this->sendMemberNotification($singleClaim, $member);
                        
                        // 2. Notify Local President so they see the audit trail closure
                        $this->notifyOfficer($singleClaim, $member, 'President', 'president', 'claims/list', true);
                        break;
                    case 'approved':
                    case 'for disbursement':
                        // Parameters: $claim, $member, SpatieRole, MailType, RedirectPath, IsRegional?
                        $this->notifyOfficer($singleClaim, $member, 'Treasurer', 'treasurer', 'claims/list', true);
                        break;
                
                    case 'for review fp':
                        $this->notifyOfficer($singleClaim, $member, 'Federated President', 'federated president', 'disbursed/list', false);
                        break;
                
                    case 'processing':
                        $this->notifyOfficer($singleClaim, $member, 'Federated Treasurer', 'federated treasurer', 'claims/list', false);
                        break;
                
                    case 'disbursed':
                        $this->notifyOfficer($singleClaim, $member, 'Treasurer', 'treasurer', 'claims/list', true);
                        $this->notifyOfficer($singleClaim, $member, 'President', 'president', 'disbbursements/list', true);
                        $this->notifyOfficer($singleClaim, $member, 'Federated President', 'federated president', 'disbbursements/list', false);
                        $this->notifyOfficer($singleClaim, $member, 'Federated Treasurer', 'federated treasurer', 'claims/list', false);
                        break;
                }
    
            } catch (\Exception $e) {
                Log::error('Failed to send claim status update email for claim ID ' . ($singleClaim->id ?? 'unknown') . ': ' . $e->getMessage());
            }
        }
    }
    
    /*
     * Handles sending notifications to the claim owner (member).
     */
    private function sendMemberNotification($singleClaim, $member): void
    {
        $emailData = [
            "recipientName" => $member->user->name,
            "claimNumber"   => $singleClaim->claim_number,
            "claimType"     => $singleClaim->claimType ? $singleClaim->claimType->name : 'N/A',
            "status"        => strtoupper($singleClaim->status), 
            "amount"        => $singleClaim->requested_amount ?? 0.00, // Members see requested amount
            "remarks"       => $singleClaim->status_remarks ?? 'No additional comments provided.',
            "updatedAt"     => $singleClaim->updated_at->format('F j, Y, g:i a'),
            "url"           => url('claims/my-list/' . $member->user->user_id)
        ];          

        Mail::to($member->user->email)->send(new ClaimStatusUpdated('member', $emailData));     
    }   

    /**
     * Reusable helper to dynamically fetch and notify an organizational officer.
     *
     * @param mixed $singleClaim The current claim model instance
     * @param mixed $member The claim owner's member profile (used for campus tracking)
     * @param string $role The exact target Spatie permission role (e.g., 'Treasurer')
     * @param string $mailType The internal slug identifier expected by your Mailable class
     * @param string $redirectPath The target application subpath for the notification link
     * @param bool $isRegional Set to true if the role must belong to the member's specific campus
     */
    private function notifyOfficer($singleClaim, $member, string $role, string $mailType, string $redirectPath, bool $isRegional = false): void
    {
        $query = Member::with('user')->whereHas('user', function ($q) use ($role) {
            $q->role([$role]); 
        }); 

        if ($isRegional) {
            $query->where('campus_id', $member->campus_id);
        }   

        $officer = $query->first();         

        if (!$officer || !$officer->user || !$officer->user->email) {
            $scopeInfo = $isRegional ? " linked to campus_id: {$member->campus_id}" : "";
            Log::warning("Officer alert skipped. No active '{$role}' role found{$scopeInfo}.");
            return;
        }   

        // Determine correct date format fallback based on status lifecycle
        $statusLower = strtolower(trim($singleClaim->status));

        if ($statusLower === 'disbursed') {
            $updatedAt = optional($singleClaim->disbursed_at)->format('F j, Y, g:i a') ?? 'N/A';
        } elseif ($statusLower === 'rejected') {
            $updatedAt = optional($singleClaim->rejected_at)->format('F j, Y, g:i a') ?? $singleClaim->updated_at->format('F j, Y, g:i a');
        } else {
            $updatedAt = $singleClaim->updated_at->format('F j, Y, g:i a');
        }


        $emailData = [
            "recipientName" => $officer->user->name,
            "claimNumber"   => $singleClaim->claim_number,
            "claimType"     => $singleClaim->claimType ? $singleClaim->claimType->name : 'N/A',
            "status"        => strtoupper($singleClaim->status), 
            "amount"        => $singleClaim->approved_amount ?? 0.00, // Officers see approved amount
            "remarks"       => $singleClaim->status_remarks ?? 'No additional comments provided.', 
            "updatedAt"     => $updatedAt,
            "url"           => url($redirectPath) 
        ];          

        Mail::to($officer->user->email)->send(new ClaimStatusUpdated($mailType, $emailData));
    }
    
}

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

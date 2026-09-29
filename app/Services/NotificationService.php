<?php 
namespace App\Services;

use App\Models\Member;
use App\Models\User;
use App\Notifications\ClaimNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    /**
     * Handle notifications for newly submitted claims (bell dropdown equivalent)
     */
    public function newSubmittedClaimAlert($claim): void
    {
        // 1. Wrap into a base support collection
        $claimCollection = wrap_into_collection($claim);
    
        // 2. Safely eager-load relationships if the collection isn't empty
        if ($claimCollection->isNotEmpty()) {
            $claimCollection->first()->newCollection($claimCollection->all())->loadMissing(['member.user', 'claimType']);
        }
    
        foreach ($claimCollection as $singleClaim) {
            try {
                $member = $singleClaim->member;
    
                if (!$member) {
                    continue; 
                }
    
                // Find the member who has the 'President' or 'Federated President' role and matching campus_id
                $presidentMember = Member::with('user')
                    ->where('campus_id', $member->campus_id)
                    ->whereHas('user', function ($query) {
                        $query->role(['President', 'Federated President']); 
                    })
                    ->first();
    
                // Send Database Notification if a president user is found
                if ($presidentMember && $presidentMember->user) {
                    $memberName = $member->user ? $member->user->name : 'Unknown Member';
                    $claimTypeName = $singleClaim->claimType ? $singleClaim->claimType->name : 'N/A';

                    $notificationData = [
                        'user_name'  => $memberName,
                        'user_image' => $member->user->profile_photo_url ?? null,
                        'type'       => 'New Submission',
                        'message'    => "A new {$claimTypeName} claim has been submitted by {$memberName}. Claim ID: {$singleClaim->claim_number}.",
                        'url'        => '/claims/list',
                    ];
                
                    // Send to the president's user model instance
                    Notification::send($presidentMember->user, new ClaimNotification($notificationData));
                } else {
                    Log::warning("No president user account found for database notification setup for campus_id: {$member->campus_id}");
                }
    
            } catch (\Exception $e) {
                Log::error('Failed to send database claim alert: ' . $e->getMessage());
            }
        }
    }

    /**
     * Main route orchestrator for changing member claim statuses
     */
    public function sendClaimStatusUpdate($claim): void
    {
        $claimCollection = wrap_into_collection($claim);
    
        if ($claimCollection->isNotEmpty()) {
            $claimCollection->first()->newCollection($claimCollection->all())->loadMissing(['member.user', 'claimType']);
        }
    
        foreach ($claimCollection as $singleClaim) {
            try {
                $member = $singleClaim->member;     
    
                if (!$member || !$member->user) {
                    Log::warning("Cannot send database status update. Claimant user account missing for claim ID: {$singleClaim->id}");
                    continue; 
                }       
    
                $status = strtolower(trim($singleClaim->status));

                // Always notify the original claimant member
                $this->sendMemberDatabaseNotification($singleClaim, $member);
                
                // Dynamically route additional role alerts matching your mailable setup logic
                switch ($status) {
                    case 'rejected':
                        $this->notifyOfficer($singleClaim, $member, 'President', 'claims/list', true);
                        break;
                    case 'approved':
                    case 'for disbursement':
                        $this->notifyOfficer($singleClaim, $member, 'Treasurer', 'claims/list', true);
                        break;
                
                    case 'for review fp':
                        $this->notifyOfficer($singleClaim, $member, 'Federated President', 'disbursed/list', false);
                        break;
                
                    case 'processing':
                        $this->notifyOfficer($singleClaim, $member, 'Federated Treasurer', 'claims/list', false);
                        break;
                
                    case 'disbursed':
                        $this->notifyOfficer($singleClaim, $member, 'Treasurer', 'claims/list', true);
                        $this->notifyOfficer($singleClaim, $member, 'President', 'disbbursements/list', true);
                        $this->notifyOfficer($singleClaim, $member, 'Federated President', 'disbbursements/list', false);
                        $this->notifyOfficer($singleClaim, $member, 'Federated Treasurer', 'claims/list', false);
                        break;
                }
    
            } catch (\Exception $e) {
                Log::error('Failed to send database claim status update for claim ID ' . ($singleClaim->id ?? 'unknown') . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Helper Method: Send database row payload to the claimant member directly
     */
    private function sendMemberDatabaseNotification($singleClaim, $member): void
    {
        $actor = auth()->user() ?? User::where('role', 'System')->first();
        $statusDisplay = strtoupper($singleClaim->status);

        $notificationData = [
            'user_name'  => $actor->name ?? 'System Admin',
            'user_image' => $actor->profile_photo_url ?? null,
            'type'       => 'Status Update',
            'message'    => "Your Claim (Number: {$singleClaim->claim_number}) status has been updated to: {$statusDisplay}.",
            'url'        => '/claims/my-claims', // Adjust view link location for standard members
        ];

        Notification::send($member->user, new ClaimNotification($notificationData));
    }

    /**
     * Helper Method: Reusable officer querying matching your local vs regional structural checks
     */
    private function notifyOfficer($singleClaim, $member, string $roleName, string $redirectUrl, bool $isCampusRestricted): void
    {
        $actor = auth()->user() ?? User::where('role', 'System')->first();

        // 1. Build the base query checking roles using your Spatie implementation
        $query = Member::with('user')->whereHas('user', function ($q) use ($roleName) {
            $q->role($roleName);
        });

        // 2. If it's a campus level role (like Local President/Treasurer), filter by current campus scope
        if ($isCampusRestricted) {
            $query->where('campus_id', $member->campus_id);
        }

        $officerMembers = $query->get();

        foreach ($officerMembers as $officerMember) {
            if ($officerMember->user) {
                $notificationData = [
                    'user_name'  => $actor->name ?? 'System Admin',
                    'user_image' => $actor->profile_photo_url ?? null,
                    'type'       => $roleName . ' Action',
                    'message'    => "Claim (Number: {$singleClaim->claim_number}) belongs to {$member->user->name} is now moving to: " . strtoupper($singleClaim->status),
                    'url'        => url($redirectUrl),
                ];

                Notification::send($officerMember->user, new ClaimNotification($notificationData));
            }
        }
    }
}

// function wrap_into_collection($items): Collection 
// {
//     if ($items instanceof Collection) {
//         return $items;
//     }
//     if (is_array($items)) {
//         return collect($items);
//     }
//     return collect([$items]);
// }

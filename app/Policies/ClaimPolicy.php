<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class ClaimPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
       // Allows Presidents and Treasurers to view the entire list of claims
        return $user->hasAnyRole([
            'President',
            'Federated President',
            'Treasurer',
            'Federated Treasurer'
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Claim $claim): bool
    {
        return $user->hasAnyRole([
            'President',
            'Federated President',
            'Treasurer',
            'Federated Treasurer'
        ]);
    }

    /**
     * Determine whether the user can print the claim forms (claimant or claim officers).
     */
    public function printForms(User $user, Claim $claim): bool
    {
        $ownerUserId = Member::withoutGlobalScopes()
            ->whereKey($claim->member_id)
            ->value('user_id');

        return $ownerUserId === $user->user_id || $this->view($user, $claim);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // return $user->hasRole('Member');
        return $user->hasAnyRole([
            'Member',
            'President',
            'Federated President',
            'Treasurer',
            'Federated Treasurer'
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Claim $claim): bool
    {
        return $user->hasAnyRole([
            'President',
            'Federated President',
            'Treasurer',
            'Federated Treasurer'
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Claim $claim): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Claim $claim): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Claim $claim): bool
    {
        return false;
    }
    
    /**
     * Determine whether the user can view their own claims.
     */
     public function viewMyClaims(User $user): bool
     {
         // 1. Super Admins, Presidents, and Treasurers can always view claims
         if ($user->hasAnyRole([
             'President',
             'Federated President',
             'Treasurer',
             'Federated Treasurer'
         ])) {
             return true;
         }
     
         // 2. If they are a basic member, they can see this view
         return $user->hasRole('Member');
     }
     
    /**
     * Determine whether the user can view summary dashboard statistics.
     */
    public function viewDashboardDashboardMetrics(User $user, User $targetMember): bool
    {
        // Users can view their own stats; leadership or permission holders can view others'
        return $user->user_id === $targetMember->user_id || $user->can('view all metrics') || $user->hasAnyRole(['President', 'Treasurer']);
    }
}

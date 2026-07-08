<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClaimPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Claim $claim): bool
    {
        return $user->hasAnyRole([
            'President',
            'Treasurer',
        ]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole([
            'Member',
        ]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Claim $claim): bool
    {
        return $user->hasAnyRole([
            'President',
            'Treasurer',
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
    public function viewMyClaims(User $user, User $targetUser = null): bool
    {
        // Presidents and Treasurers can see anyone's claim list
        if ($user->hasAnyRole(['President', 'Treasurer'])) {
            return true;
        }
    
        // Members can only see their own claim list
        return $user->hasRole('Member') && $user->id === $targetUser?->id;
    }
}

<?php

namespace App\Policies;

use App\Models\ContributionDetails;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\Response;

class ContributionDetailsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, ): bool
    {
        // return false;

        if ($user->hasRole([
            'President',
            'Treasurer'
        ])){
            return true;
        } else {
            // Member check: compare against the configured primary key
            return $user->hasRole('Member') && $user->user_id === Auth::id();
        }
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ContributionDetails $contributionDetails): bool
    {
        return false;
        // return $user->

    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ContributionDetails $contributionDetails): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ContributionDetails $contributionDetails): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ContributionDetails $contributionDetails): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ContributionDetails $contributionDetails): bool
    {
        return false;
    }
}

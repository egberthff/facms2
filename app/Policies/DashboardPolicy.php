<?php

namespace App\Policies;

use App\Models\User;

class DashboardPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the user can view the main dashboard.
     */
    public function view(User $user): bool
    {
        // Allow users with the 'view all metrics' permission or leadership roles
        return $user->can('view all metrics') || $user->hasAnyRole(['President', 'Treasurer']);
    }

    /**
     * Determine if the user can access their personal financial dashboard overview metrics.
     */
    public function viewMetrics(User $user, User $targetUser): bool
    {
        // Enforces unique cross-profile evaluation match using user_id
        return $user->user_id === $targetUser->user_id;
    }
    // public function viewMetrics(User $user, User $targetUser): bool
    // {
    //     // Users can only view their own dashboard data, unless they have administrative access
    //     return $user->user_id === $targetUser->user_id || $user->hasAnyRole(['President', 'Treasurer']);
    // }
}

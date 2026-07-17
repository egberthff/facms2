<?php 
namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CampusMemberScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Skip filtering when running terminal commands or for unauthenticated visitors
        if (app()->runningInConsole() || !auth()->check()) {
            return;
        }

        $authUser = auth()->user();

        // 1. President / Super-Admin Bypass
        // Check if the user has a president role or an all-access permission string
        if ($authUser->hasRole('president') || $authUser->can('view all campus data')) {
            return;
        }

        // 2. Campus Restriction Logic (e.g., for Treasurers)
        // Access the relationship cleanly using the custom user_id mapping
        $campusId = $authUser->member?->campus_id;

        if (!$campusId) {
            $builder->whereRaw('1 = 0'); // Secure fallback: returns empty dataset if unassigned
            return;
        }

        // High performance direct column filter (No subqueries needed!)
        $builder->where($model->getTable() . '.campus_id', $campusId);
    }
}

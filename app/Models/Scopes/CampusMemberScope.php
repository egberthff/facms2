<?php 

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Models\Member;

class CampusMemberScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // Skip filtering when running terminal commands or for unauthenticated visitors
        if (app()->runningInConsole() || !Auth::check()) {
            return;
        }

        // Fix: Use withoutGlobalScopes() to break the infinite recursion loop
        $memberRecord = Member::withoutGlobalScopes()
            ->where('user_id', Auth::id())
            ->first();

        // Safety fallback if the user is authenticated but has no member profile yet
        if (!$memberRecord) {
            return;
        }

        // 1. President / Super-Admin Bypass
        // Check roles or permissions using the authenticated User model
        if (Auth::user()->hasRole('president') || Auth::user()->can('view all campus data')) {
            return;
        }

        // 2. Campus Restriction Logic
        $campusId = $memberRecord->campus_id;

        if (!$campusId) {
            $builder->whereRaw('1 = 0'); // Secure fallback: returns empty dataset if unassigned
            return;
        }

        // High performance direct column filter
        $builder->where($model->getTable() . '.campus_id', $campusId);
    }
}

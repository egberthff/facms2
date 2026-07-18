<?php 

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class CampusMemberScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        // 1. Skip filtering for terminal/cron tasks or unlogged guests
        if (app()->runningInConsole() || !Auth::check()) {
            return;
        }

        $user = Auth::user();

        // 2. President/Admin Global Bypass
        // Note: Ensure your role package works directly with the User model
        if ($user->hasRole('president') || $user->can('view all campus data')) {
            return;
        }

        // 3. Get the cached Campus ID from the user session
        $campusId = $user->getCachedCampusId();

        if (!$campusId) {
            $builder->whereRaw('1 = 0'); // Secure fallback: returns empty dataset if unassigned
            return;
        }

        // 4. Inject direct column filter using the active model's table name
        $builder->where($model->getTable() . '.campus_id', $campusId);
    }
}

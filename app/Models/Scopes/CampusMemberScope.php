<?php 

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
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

        // 2. Permission-driven global bypass
        // Users granted the `view all campus data` permission may see all campuses
        if ($user->can('view all campus data')) {
            return;
        }

        // 3. Get the cached Campus ID from the user session
        $campusId = $user->getCachedCampusId();
        if (!$campusId) {
            $builder->whereRaw('1 = 0'); // Secure fallback: returns empty dataset if unassigned
            return;
        }

        $tableName = $model->getTable();

        // If the query is running directly on the Campuses table itself, 
        // filter by its primary key column (id).
        if ($tableName === 'campuses') {
            $builder->where('campuses.id', $campusId);
            return;
        }

        // If it's running on members (or any other table), verify it has a 'campus_id' column
        if (Schema::hasColumn($tableName, 'campus_id')) {
            $builder->where($tableName . '.campus_id', $campusId);
        }
    }
}

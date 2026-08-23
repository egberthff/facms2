<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Blade; 
use Illuminate\Support\Facades\Gate;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
          Blade::anonymousComponentPath(resource_path('views/claims'), 'claims');

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       Event::listen(Login::class, function ($event) {

            $event->user->update([
                'last_login_at' => now(),
            ]);
        
        });
          Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
          Blade::anonymousComponentPath(resource_path('views/claims'), 'claims');

         $policies = [
            // Registers the Dashboard Policy manually
            'dashboard' => \App\Policies\DashboardPolicy::class,
        ];

        // Automatically grant all permissions to a Federated President
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Federated President')) {
                return true;
            }
        });

        // 2. Fallback check: If a user has a specific permission string, 
        // bypass policy model checks for that permission.
        Gate::after(function ($user, $ability, $result) {
            if ($result === false || $result === null) {
                return $user->hasPermissionTo($ability) ? true : $result;
            }
        });
    }
}

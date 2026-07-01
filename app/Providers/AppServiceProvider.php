<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Blade; 


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
        //  Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
    }
}

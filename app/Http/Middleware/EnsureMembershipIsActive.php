<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Accounts whose membership is Inactive (e.g. self-signups awaiting officer
 * activation) may only reach the dashboard and their own profile.
 */
class EnsureMembershipIsActive
{
    /**
     * Route names an inactive member may still access.
     */
    private const ALLOWED_ROUTES = [
        'dashboard',
        'members.profile',
        'notifications.read',
        'notifications.markAllRead',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasInactiveMembership() || $request->routeIs(...self::ALLOWED_ROUTES)) {
            return $next($request);
        }

        $message = 'Your membership is not yet active. Please wait for your campus officers to activate your account.';

        if ($request->expectsJson()) {
            abort(403, $message);
        }

        return redirect()->route('dashboard')->with('error', $message);
    }
}

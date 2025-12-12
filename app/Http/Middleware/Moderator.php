<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Moderator
{

    private static $allowedRoles = ['admin', 'moderator'];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Check if any of the user's roles is in allowedRoles
        $hasAllowedRole = !empty(array_intersect($user->roles ?? [], self::$allowedRoles));

        if (!$hasAllowedRole) {
            abort(403, 'Role "moderator" required');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route guard: only lets users with the given role through.
 * Usage in routes: ->middleware('role:parent') or ->middleware('role:child')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if ($request->user()?->role !== UserRole::from($role)) {
            abort(403, 'This page is not available for your account type.');
        }

        return $next($request);
    }
}
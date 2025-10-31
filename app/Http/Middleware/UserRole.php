<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class UserRole
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('role:admin') or ->middleware('role:user')
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if (! method_exists($user, 'hasRole') || ! $user->hasRole($role)) {
            abort(403);
        }

        return $next($request);
    }
}

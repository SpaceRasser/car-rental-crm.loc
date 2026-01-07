<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (property_exists($user, 'is_active') && !$user->is_active) {
            abort(403, 'User is inactive');
        }

        if (!empty($roles) && !in_array($user->role, $roles, true)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    /**
     * Gate access by user_type.
     * SuperAdmin always passes. Pass allowed roles as middleware arguments:
     *   role:Admin,Agent
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // SuperAdmin can always pass
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (! in_array($user->user_type, $roles, true)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}

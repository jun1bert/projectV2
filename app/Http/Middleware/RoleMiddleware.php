<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
{
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    $userRole = strtolower($user->role);

    // normalize allowed roles
    $allowedRoles = array_map('strtolower', $roles);

    // admin always allowed (system override)
    if ($userRole === 'admin') {
        return $next($request);
    }

    // if no roles defined → block
    if (empty($allowedRoles)) {
        abort(403, 'No access roles defined.');
    }

    // check access
    if (!in_array($userRole, $allowedRoles)) {
        abort(403, 'Unauthorized access.');
    }

    return $next($request);
}
}

<?php

namespace App\Http\Middleware;

use App\Support\Rbac;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRoleLevel
{
    /**
     * Ensure the authenticated user has at least the configured role level.
     */
    public function handle(Request $request, Closure $next, int $minimumLevel): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! Rbac::hasMinimumLevel($user, $minimumLevel)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
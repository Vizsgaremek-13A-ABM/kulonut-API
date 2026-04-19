<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateOptionalSanctum
{
    /**
     * Resolve Sanctum user when a valid bearer token is present, without forcing authentication.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->bearerToken()) {
            $guard = Auth::guard('sanctum');
            $user = $guard->user();
            if ($user) {
                Auth::setUser($user);
            }
        }

        return $next($request);
    }
}
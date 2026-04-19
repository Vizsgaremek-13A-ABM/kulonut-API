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
            if (Auth::guard('sanctum')->check()) {
                Auth::setUser(Auth::guard('sanctum')->user());
            }
        }

        return $next($request);
    }
}

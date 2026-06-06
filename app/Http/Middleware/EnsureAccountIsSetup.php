<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAccountIsSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip if not authenticated
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Bypass account setup if the user is not a 'karyawan' (e.g., admin, super admin)
        if (!$user->hasRole('karyawan')) {
            return $next($request);
        }

        // If email_verified_at is null, the account is not set up
        if (is_null($user->email_verified_at)) {
            // Check if current route is part of setup or logout to avoid redirect loop
            if (!$request->routeIs('account.setup.*') && !$request->routeIs('logout')) {
                return redirect()->route('account.setup.form');
            }
        }

        return $next($request);
    }
}

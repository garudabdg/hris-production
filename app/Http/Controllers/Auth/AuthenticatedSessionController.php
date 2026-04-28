<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\AuditLog;
use App\Http\Controllers\Auth\TwoFactorController;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Cek apakah role admin (bukan karyawan) → wajib 2FA
        $isAdminRole = !$user->hasRole('karyawan');

        if ($isAdminRole && !empty($user->email)) {
            // Cek apakah perangkat ini sudah dipercaya
            if (TwoFactorController::isTrustedDevice($request, $user)) {
                // Perangkat dikenal → skip 2FA, langsung login
                $request->session()->regenerate();
                try { AuditLog::logLogin(); } catch (\Exception $e) {}
                return redirect()->intended(RouteServiceProvider::HOME);
            }

            // Logout sementara — simpan di session untuk 2FA
            Auth::logout();
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_remember', $request->boolean('remember'));

            // Generate & kirim kode
            TwoFactorController::sendCode($user);

            return redirect()->route('two-factor.show');
        }

        $request->session()->regenerate();

        // Log login activity
        try {
            AuditLog::logLogin();
        } catch (\Exception $e) {
            \Log::error('Failed to log login: ' . $e->getMessage());
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log logout activity sebelum logout
        try {
            AuditLog::logLogout();
        } catch (\Exception $e) {
            \Log::error('Failed to log logout: ' . $e->getMessage());
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

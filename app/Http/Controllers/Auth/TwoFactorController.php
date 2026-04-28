<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use App\Models\AuditLog;
use App\Models\TrustedDevice;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class TwoFactorController extends Controller
{
    const COOKIE_NAME   = '2fa_trusted_device';
    const COOKIE_DAYS   = 30; // hari

    /**
     * Tampilkan form input kode 2FA.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('2fa_verified')) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        $user        = \App\Models\User::find($request->session()->get('2fa_user_id'));
        $maskedEmail = $user ? self::maskEmail($user->email) : '';

        return view('auth.two-factor', compact('maskedEmail'));
    }

    /**
     * Verifikasi kode 2FA yang diinput user.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'two_factor_code' => 'required|string',
        ]);

        $userId = $request->session()->get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['two_factor_code' => 'Sesi login tidak valid. Silakan login kembali.']);
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login')->withErrors(['two_factor_code' => 'User tidak ditemukan.']);
        }

        // Cek expired
        if (!$user->two_factor_expires_at || now()->isAfter($user->two_factor_expires_at)) {
            return back()->withErrors(['two_factor_code' => 'Kode verifikasi telah kedaluwarsa. Silakan minta kode baru.']);
        }

        // Cek kode
        if ($request->two_factor_code !== $user->two_factor_code) {
            return back()->withErrors(['two_factor_code' => 'Kode verifikasi tidak valid.']);
        }

        // Kode benar — bersihkan kode & login
        $user->update([
            'two_factor_code'       => null,
            'two_factor_expires_at' => null,
        ]);

        $request->session()->forget('2fa_user_id');
        $request->session()->put('2fa_verified', true);

        Auth::login($user, $request->session()->get('2fa_remember', false));
        $request->session()->regenerate();

        // Log login activity
        try {
            AuditLog::logLogin();
        } catch (\Exception $e) {
            \Log::error('Failed to log login: ' . $e->getMessage());
        }

        // Percaya perangkat ini jika dicentang
        $response = redirect()->intended(RouteServiceProvider::HOME);
        if ($request->boolean('trust_device')) {
            $token  = self::generateDeviceToken();
            $hashed = hash('sha256', $token);

            TrustedDevice::create([
                'user_id'      => $user->id,
                'token'        => $hashed,
                'device_name'  => self::guessDeviceName($request),
                'ip_address'   => $request->ip(),
                'last_used_at' => now(),
            ]);

            $cookie = Cookie::make(
                self::COOKIE_NAME,
                $token,
                self::COOKIE_DAYS * 24 * 60, // menit
                '/',
                null,
                true,  // secure
                true,  // httpOnly
                false,
                'Lax'
            );

            $response->withCookie($cookie);
        }

        return $response;
    }

    /**
     * Kirim ulang kode 2FA.
     */
    public function resend(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);
        if (!$user || empty($user->email)) {
            return back()->withErrors(['two_factor_code' => 'Tidak dapat mengirim ulang kode. Email tidak ditemukan.']);
        }

        self::sendCode($user);

        return back()->with('status', 'Kode verifikasi baru telah dikirim ke email Anda.');
    }

    /**
     * Cek apakah request berasal dari perangkat tepercaya milik user.
     */
    public static function isTrustedDevice(Request $request, \App\Models\User $user): bool
    {
        $token = $request->cookie(self::COOKIE_NAME);
        if (empty($token)) {
            return false;
        }

        $hashed = hash('sha256', $token);
        $device = TrustedDevice::where('user_id', $user->id)
            ->where('token', $hashed)
            ->first();

        if (!$device) {
            return false;
        }

        // Perbarui last_used_at
        $device->update(['last_used_at' => now(), 'ip_address' => $request->ip()]);
        return true;
    }

    /**
     * Generate & kirim kode ke email user.
     */
    public static function sendCode(\App\Models\User $user): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'two_factor_code'       => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new TwoFactorCodeMail($code, $user->name));
    }

    /**
     * Mask email: j***@g***.com
     */
    public static function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $maskedLocal  = substr($local, 0, 1) . str_repeat('*', max(strlen($local) - 1, 3));
        [$domainName, $tld] = array_pad(explode('.', $domain, 2), 2, '');
        $maskedDomain = substr($domainName, 0, 1) . str_repeat('*', max(strlen($domainName) - 1, 2));
        return $maskedLocal . '@' . $maskedDomain . ($tld ? '.' . $tld : '');
    }

    private static function generateDeviceToken(): string
    {
        return bin2hex(random_bytes(32)); // 64 hex chars
    }

    private static function guessDeviceName(Request $request): string
    {
        $ua = $request->userAgent() ?? '';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) return 'iOS';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Macintosh')) return 'Mac';
        if (str_contains($ua, 'Linux')) return 'Linux';
        return 'Browser';
    }
}

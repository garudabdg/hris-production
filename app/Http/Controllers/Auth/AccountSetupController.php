<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use App\Mail\AccountSetupOtpMail;
use Illuminate\Support\Str;

class AccountSetupController extends Controller
{
    /**
     * Show the account setup form.
     */
    public function showSetupForm(Request $request)
    {
        $user = Auth::user();

        // If already verified, redirect to dashboard
        if (!is_null($user->email_verified_at)) {
            return redirect()->route('dashboard.index');
        }

        $cooldownLeft = 0;
        if ($user->two_factor_expires_at && now()->isBefore($user->two_factor_expires_at)) {
            $cooldownLeft = now()->diffInSeconds($user->two_factor_expires_at);
        }

        // If they already submitted the form and have a pending OTP, they can still access this page to fix typos
        return view('auth.account_setup', compact('user', 'cooldownLeft'));
    }

    /**
     * Process the account setup form.
     */
    public function processSetup(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!is_null($user->email_verified_at)) {
            return redirect()->route('dashboard.index');
        }

        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => \App\Helpers\PasswordHelper::getRules($user, null, false, true),
        ]);

        // Cek cooldown secara global (mencegah spam ganti-ganti email)
        if ($user->two_factor_expires_at && now()->isBefore($user->two_factor_expires_at)) {
            $timeLeft = now()->diffInSeconds($user->two_factor_expires_at);
            $minutes = floor($timeLeft / 60);
            $seconds = $timeLeft % 60;
            return back()->withErrors(['email' => "Terlalu banyak permintaan. Harap tunggu {$minutes} menit {$seconds} detik lagi sebelum mengirim OTP baru."]);
        }

        // Update user data
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        // Cegah auto-logout dengan meregenerasi session dengan hash password baru
        Auth::login($user);

        // Catat aktivitas di AuditLog
        try {
            \App\Models\AuditLog::log('update', 'Account Setup', 'User (' . $user->username . ') mengubah data login pada proses setup akun awal.');
        } catch (\Exception $e) {
            \Log::error('Gagal mencatat audit log account setup: ' . $e->getMessage());
        }

        // Generate and send OTP
        $this->generateAndSendOtp($user);

        return redirect()->route('account.setup.otp')->with('status', 'Kode OTP telah dikirim ke email Anda.');
    }

    public function showOtpForm()
    {
        $user = Auth::user();

        if (!is_null($user->email_verified_at)) {
            return redirect()->route('dashboard.index');
        }

        // If they haven't set an email yet (still using dummy email)
        if (Str::endsWith($user->email, '@belum.diset')) {
            return redirect()->route('account.setup.form');
        }

        $cooldownLeft = 0;
        if ($user->two_factor_expires_at && now()->isBefore($user->two_factor_expires_at)) {
            $cooldownLeft = now()->diffInSeconds($user->two_factor_expires_at);
        }

        return view('auth.account_setup_otp', compact('user', 'cooldownLeft'));
    }

    /**
     * Verify the OTP.
     */
    public function verifyOtp(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!is_null($user->email_verified_at)) {
            return redirect()->route('dashboard.index');
        }

        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        // Check if OTP is valid and not expired
        if ($user->two_factor_code !== $request->otp) {
            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        if (now()->isAfter($user->two_factor_expires_at)) {
            return back()->withErrors(['otp' => 'Kode OTP telah kadaluarsa. Silakan minta kode baru.']);
        }

        // Verify email
        $user->email_verified_at = now();
        $user->two_factor_code = null;
        $user->two_factor_expires_at = null;
        $user->save();

        return redirect()->route('dashboard.index')->with('success', 'Akun berhasil disetup! Selamat datang di HRIS.');
    }

    /**
     * Resend the OTP.
     */
    public function resendOtp(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!is_null($user->email_verified_at)) {
            return redirect()->route('dashboard.index');
        }

        if (Str::endsWith($user->email, '@belum.diset')) {
            return redirect()->route('account.setup.form');
        }

        if ($user->two_factor_expires_at && now()->isBefore($user->two_factor_expires_at)) {
            $timeLeft = now()->diffInSeconds($user->two_factor_expires_at);
            $minutes = floor($timeLeft / 60);
            $seconds = $timeLeft % 60;
            return back()->withErrors(['otp' => "Harap tunggu {$minutes} menit {$seconds} detik lagi sebelum mengirim ulang."]);
        }

        $this->generateAndSendOtp($user);

        return back()->with('status', 'Kode OTP baru telah dikirim ke email Anda.');
    }

    /**
     * Generate 6-digit OTP and send via email.
     */
    private function generateAndSendOtp(User $user)
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->two_factor_code = $otp;
        $user->two_factor_expires_at = now()->addMinutes(2);
        $user->save();

        Mail::to($user->email)->send(new AccountSetupOtpMail($otp, $user->name));
    }
}

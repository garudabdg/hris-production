<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Pengaturanumum;
use App\Jobs\SendWaMessage;
use Carbon\Carbon;

class PasswordOtpController extends Controller
{
    /**
     * Send OTP to email for password reset
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);
        
        $email = $request->email;
        
        // Generate OTP (6 digits)
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = Str::random(64);
        
        // Expires in 10 minutes
        $expiresAt = Carbon::now()->addMinutes(10);
        
        // Delete any existing OTP for this email
        DB::table('password_reset_otps')->where('email', $email)->delete();
        
        // Store OTP
        DB::table('password_reset_otps')->insert([
            'email' => $email,
            'otp_code' => $otp,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
        
        // Get General Setting
        $setting = Pengaturanumum::first();
        $otpMethod = $setting->otp_method ?? 'email';
        
        $user = User::where('email', $email)->first();
        $isAdmin = $user && !$user->hasRole('karyawan');
        
        $noHp = null;
        if ($otpMethod == 'whatsapp') {
            if ($user && $user->userkaryawan && $user->userkaryawan->karyawan) {
                $noHp = $user->userkaryawan->karyawan->no_hp;
            }
            
            // If phone number is invalid or empty, fallback to email
            if (empty($noHp)) {
                $otpMethod = 'email';
            }
        }
        
        // Send OTP
        try {
            if ($otpMethod == 'whatsapp') {
                $messageText = "*OTP RESET PASSWORD*\n\nKode OTP Anda adalah: *$otp*\n\nBerlaku hingga: " . $expiresAt->format('H:i') . " WIB\n\n_Mohon jangan berikan kode ini kepada siapapun._";
                SendWaMessage::dispatch($noHp, $messageText, false, true, 'otp');
                $methodSent = 'WhatsApp';
            } else {
                Mail::send('emails.password-otp', [
                    'otp' => $otp,
                    'expires_at' => $expiresAt->format('H:i'),
                    'email' => $email
                ], function($message) use ($email) {
                    $message->to($email)
                            ->subject('Password Reset OTP - HRIS DIDIMAX')
                            ->from(env('MAIL_FROM_ADDRESS', 'hrd@didimax.online'), env('MAIL_FROM_NAME', 'HRIS DIDIMAX'));
                });
                $methodSent = 'email';
            }
            
            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your ' . $methodSent,
                'token' => $token,
                'isAdmin' => $isAdmin
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'otp' => 'required|digits:6'
        ]);
        
        $otpRecord = DB::table('password_reset_otps')
            ->where('token', $request->token)
            ->where('otp_code', $request->otp)
            ->first();
            
        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 400);
        }
        
        if (Carbon::now()->gt(Carbon::parse($otpRecord->expires_at))) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired'
            ], 400);
        }
        
        // OTP is valid
        return response()->json([
            'success' => true,
            'message' => 'OTP verified',
            'email' => $otpRecord->email
        ]);
    }
    
    /**
     * Reset password with OTP verification
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'otp' => 'required|digits:6',
        ]);
        
        // Verify OTP first
        $otpRecord = DB::table('password_reset_otps')
            ->where('token', $request->token)
            ->where('otp_code', $request->otp)
            ->first();
            
        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 400);
        }
        
        if (Carbon::now()->gt(Carbon::parse($otpRecord->expires_at))) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired'
            ], 400);
        }
        
        // Update password
        $user = User::where('email', $otpRecord->email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Validate password based on user role
        $request->validate([
            'password' => \App\Helpers\PasswordHelper::getRules($user, null, false, true)
        ]);
        
        $user->password = Hash::make($request->password);
        $user->save();
        
        // Delete OTP record
        DB::table('password_reset_otps')->where('token', $request->token)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    }
}
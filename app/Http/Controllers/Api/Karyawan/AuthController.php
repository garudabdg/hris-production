<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\User;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use KaryawanApiHelper;

    public function login(Request $request)
    {
        $request->validate([
            'id_user'     => 'required|string',
            'password'    => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->id_user) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . ceil($seconds / 60) . ' menit.',
            ], 429);
        }

        $field = filter_var($request->id_user, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $request->id_user)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey);
            return response()->json([
                'success' => false,
                'message' => 'Username/email atau password salah.',
            ], 401);
        }

        RateLimiter::clear($throttleKey);

        if (!$user->hasRole('karyawan')) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini bukan akun karyawan.',
            ], 403);
        }

        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userkaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        $karyawan = Karyawan::where('nik', $userkaryawan->nik)
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();

        $deviceName = $request->device_name ?? 'mobile';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token'     => $token,
                'user'      => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'username' => $user->username,
                    'email'    => $user->email,
                ],
                'karyawan' => $this->formatKaryawan($karyawan),
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }
}

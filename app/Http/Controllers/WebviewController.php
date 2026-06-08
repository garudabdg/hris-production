<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class WebviewController extends Controller
{
    /**
     * Endpoint untuk auto-login dari aplikasi mobile (Flutter) ke WebView (PWA).
     * Menerima token API (Sanctum) via URL, memvalidasinya, dan menciptakan
     * session cookie standard Laravel untuk WebView.
     */
    public function autoLogin(Request $request)
    {
        $token = $request->query('token');
        $redirect = $request->query('redirect', '/dashboard');

        if (!$token) {
            return redirect('/')->with('error', 'Token login tidak ditemukan.');
        }

        // 1. Cari token di tabel Sanctum
        $accessToken = PersonalAccessToken::findToken($token);

        // Validasi apakah token ada dan belum expired (opsional tergantung config sanctum)
        if (!$accessToken || !$accessToken->tokenable) {
            return redirect('/')->with('error', 'Token tidak valid atau sudah expired.');
        }

        // 2. Login otomatis user ke session WEB (Guard default/web)
        Auth::login($accessToken->tokenable);

        // 3. Arahkan WebView ke halaman yang dituju (misal: /presensi/create)
        return redirect($redirect);
    }
}

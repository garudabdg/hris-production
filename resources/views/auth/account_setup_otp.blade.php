<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Verifikasi OTP - HRIS Didimax</title>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <style>
        :root {
            --theme-color-1: {{ $general_setting->theme_color_1 ?? '#106f62' }};
            --theme-color-2: {{ $general_setting->theme_color_2 ?? '#0b5247' }};
            --theme-color-light: color-mix(in srgb, var(--theme-color-1) 15%, #ffffff);
            --theme-color-fade: color-mix(in srgb, var(--theme-color-1) 30%, #ffffff);
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/css/auth_mobile.css') }}?v={{ time() }}">
</head>
<body>
    <div class="top-section">
        <div class="blob-1"></div>
        

        <div class="plant-wrapper">
            <div class="leaf-1"></div>
            <div class="leaf-2"></div>
            <div class="plant-pot"></div>
            <div class="pot-shadow"></div>
        </div>
    </div>
    
    <div class="bottom-section">
        <div class="container">
            <div style="margin-top: 5px;">
                <div class="login-title" style="margin-bottom: 8px; font-size: 30px;">Verifikasi OTP</div>
                <div style="font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 25px;">Masukkan 6 digit kode OTP yang telah dikirimkan ke <strong style="color: #475569;">{{ $user->email }}</strong>.</div>
            </div>

            @if (session('status'))
                <div class="alert alert-success">
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif
            
            <form action="{{ route('account.setup.verify') }}" method="POST" onsubmit="disableButton('btn-verify', 'Memverifikasi...')">
                @csrf
                
                <div class="form-group">
                    <ion-icon name="keypad-outline"></ion-icon>
                    <input type="text" id="otp" name="otp" class="form-control" placeholder="••••••" maxlength="6" autocomplete="one-time-code" required autofocus>
                </div>
                
                <button type="submit" id="btn-verify" class="btn-login">Verifikasi Akun</button>
            </form>

            <div style="text-align: center; margin-bottom: 10px;">
                <form method="POST" action="{{ route('account.setup.resend') }}" onsubmit="disableButton('btn-resend', 'Mengirim...')">
                    @csrf
                    <button type="submit" id="btn-resend" class="btn-link">Kirim Ulang Kode OTP</button>
                </form>
            </div>

            <div class="bottom-actions">
                <a href="{{ route('account.setup.form') }}" class="btn-link" style="color: #8eaba5;">&larr; Ganti Email</a>
                
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function disableButton(btnId, loadingText) {
            const btn = document.getElementById(btnId);
            if(btn) {
                // Biarkan form tersubmit, tapi ubah visual tombol
                setTimeout(() => {
                    btn.disabled = true;
                    btn.innerHTML = '<ion-icon name="sync-outline" class="animate-spin"></ion-icon> ' + loadingText;
                    btn.style.opacity = '0.7';
                    btn.style.cursor = 'not-allowed';
                }, 10); // sedikit delay agar submit form tidak terblokir
            }
        }

        // Timer Cooldown untuk Resend OTP (5 Menit)
        let cooldownSeconds = {{ $cooldownLeft ?? 0 }};
        if (cooldownSeconds > 0) {
            const resendBtn = document.getElementById('btn-resend');
            if (resendBtn) {
                resendBtn.disabled = true;
                resendBtn.style.opacity = '0.5';
                resendBtn.style.cursor = 'not-allowed';
                
                const timerInterval = setInterval(() => {
                    let m = Math.floor(cooldownSeconds / 60);
                    let s = cooldownSeconds % 60;
                    resendBtn.innerHTML = `Tunggu (${m}:${s.toString().padStart(2, '0')})`;
                    cooldownSeconds--;
                    
                    if (cooldownSeconds < 0) {
                        clearInterval(timerInterval);
                        resendBtn.disabled = false;
                        resendBtn.style.opacity = '1';
                        resendBtn.style.cursor = 'pointer';
                        resendBtn.innerHTML = 'Kirim Ulang Kode OTP';
                    }
                }, 1000);
            }
        }
    </script>
</body>
</html>

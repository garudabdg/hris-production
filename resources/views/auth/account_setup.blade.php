<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Setup Akun - HRIS Didimax</title>

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
                <div class="login-title" style="margin-bottom: 8px; font-size: 30px;">Setup Akun</div>
                <div style="font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 25px;">Lengkapi profil Anda untuk mengamankan akun.</div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger" id="error-alert">
                    <span id="error-text">
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </span>
                </div>
            @endif
            
            <form action="{{ route('account.setup.process') }}" method="POST" onsubmit="disableButton('btn-submit', 'Mengirim OTP...')">
                @csrf
                
                <div>
                    <label class="input-label">Username Baru</label>
                    <div class="form-group">
                        <ion-icon name="person-outline"></ion-icon>
                        <input type="text" name="username" class="form-control" placeholder="Username" autocomplete="off" required value="{{ old('username', $user->username) }}">
                    </div>
                </div>

                <div>
                    <label class="input-label">Email Aktif (untuk OTP)</label>
                    <div class="form-group">
                        <ion-icon name="mail-outline"></ion-icon>
                        <input type="email" name="email" class="form-control" placeholder="Email Asli" autocomplete="off" required value="{{ old('email', str_contains($user->email, '@belum.diset') ? '' : $user->email) }}">
                    </div>
                </div>
                
                <div>
                    <label class="input-label">Password Baru <span style="font-size: 10px; color: #a9b5b2; font-weight: normal;">(Min. 8 karakter, kombinasi huruf & angka)</span></label>
                    <div class="form-group">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Password Baru" required>
                    </div>
                </div>

                <div>
                    <label class="input-label">Konfirmasi Password Baru</label>
                    <div class="form-group" style="margin-bottom: 5px;">
                        <ion-icon name="lock-closed-outline"></ion-icon>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi Password Baru" required>
                    </div>
                    <div id="password-match-error" style="color: #c62828; font-size: 11px; margin-left: 5px; margin-bottom: 15px; display: none; font-weight: 500;">
                        <ion-icon name="alert-circle-outline" style="vertical-align: middle;"></ion-icon> Password tidak sama!
                    </div>
                </div>
                
                <button type="submit" id="btn-submit" class="btn-login" style="margin-top: 10px;">Simpan & Kirim OTP</button>
            </form>

            <form method="POST" action="{{ route('logout') }}" style="margin-top: 10px;">
                @csrf
                <button type="submit" class="logout-btn">Batal & Logout</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('password_confirmation');
            const errorMsg = document.getElementById('password-match-error');
            const submitBtn = document.getElementById('btn-submit');

            function checkPasswordMatch() {
                if (confirmInput.value === '') {
                    errorMsg.style.display = 'none';
                    confirmInput.style.borderColor = 'transparent';
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                    return;
                }

                if (passwordInput.value !== confirmInput.value) {
                    errorMsg.style.display = 'block';
                    confirmInput.style.borderColor = '#c62828';
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.5';
                } else {
                    errorMsg.style.display = 'none';
                    confirmInput.style.borderColor = '#2e7d32'; // green when matched
                    submitBtn.disabled = false;
                    submitBtn.style.opacity = '1';
                }
            }

            passwordInput.addEventListener('input', checkPasswordMatch);
            confirmInput.addEventListener('input', checkPasswordMatch);
        });

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

        // Timer Cooldown untuk Form Setup (5 Menit)
        let cooldownSeconds = {{ $cooldownLeft ?? 0 }};
        if (cooldownSeconds > 0) {
            const btn = document.getElementById('btn-submit');
            const errorAlert = document.getElementById('error-alert');
            const errorText = document.getElementById('error-text');

            if (btn) {
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.style.cursor = 'not-allowed';
                
                const timerInterval = setInterval(() => {
                    let m = Math.floor(cooldownSeconds / 60);
                    let s = cooldownSeconds % 60;
                    btn.innerHTML = `Tunggu (${m}:${s.toString().padStart(2, '0')})`;
                    
                    if (errorAlert && errorText && errorAlert.innerText.includes('Terlalu banyak permintaan')) {
                        errorText.innerHTML = `Terlalu banyak permintaan. Harap tunggu ${m} menit ${s} detik lagi sebelum mengirim OTP baru.`;
                    }
                    
                    cooldownSeconds--;
                    
                    if (cooldownSeconds < 0) {
                        clearInterval(timerInterval);
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        btn.style.cursor = 'pointer';
                        btn.innerHTML = 'Simpan & Kirim OTP';
                        if (errorAlert) errorAlert.style.display = 'none';
                    }
                }, 1000);
            }
        }
    </script>
</body>
</html>

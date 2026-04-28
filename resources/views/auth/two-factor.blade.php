<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verifikasi 2FA - HRIS</title>
    <link rel="stylesheet" href="{{ asset('assets/login/css/style.css') }}" />
    <style>
        :root {
            --theme-color-1: {{ optional(\App\Models\Pengaturanumum::first())->theme_color_1 ?? '#053b22' }};
            --theme-color-2: {{ optional(\App\Models\Pengaturanumum::first())->theme_color_2 ?? '#0b6a3a' }};
        }
        .sign-btn { background-color: var(--theme-color-1) !important; cursor:pointer; }
        .sign-btn:hover { background-color: var(--theme-color-2) !important; }
        .bullets span.active { background-color: var(--theme-color-1) !important; }
        .carousel { background: var(--theme-color-1) !important; }
        .text-group h2 { color: #ffffff !important; }

        /* Alert */
        .alert { padding: 12px 16px; margin-bottom: 16px; border: 1px solid transparent; border-radius: 8px; font-size: 13px; animation: slideIn .4s ease; }
        .alert-danger  { color: #842029; background: #f8d7da; border-color: #f5c2c7; }
        .alert-success { color: #0f5132; background: #d1e7dd; border-color: #badbcc; }
        @keyframes slideIn { from { transform:translateY(-8px); opacity:0; } to { transform:translateY(0); opacity:1; } }

        /* Email hint */
        .email-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f0f7f3; border: 1px solid #b7dfc8;
            border-radius: 20px; padding: 6px 14px;
            font-size: 13px; color: #1a5c38; font-weight: 600;
            margin-bottom: 18px;
        }

        /* OTP boxes */
        .otp-group {
            display: flex; gap: 8px; justify-content: center; margin-bottom: 20px;
        }
        .otp-digit {
            width: 44px; height: 52px;
            border: 2px solid #d1d5db; border-radius: 10px;
            font-size: 22px; font-weight: 700; text-align: center;
            color: var(--theme-color-1); background: #fff;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .otp-digit:focus {
            border-color: var(--theme-color-1);
            box-shadow: 0 0 0 3px rgba(5,59,34,.12);
        }
        .otp-digit.filled { border-color: var(--theme-color-1); background: #f0f7f3; }

        /* Hidden input */
        #two_factor_code { display:none; }

        /* Trust checkbox */
        .trust-row {
            display: flex; align-items: center; gap: 9px;
            background: #f8f9fa; border-radius: 8px;
            padding: 10px 12px; margin-bottom: 16px;
            border: 1px solid #e9ecef;
        }
        .trust-row input[type=checkbox] {
            width: 17px; height: 17px; cursor: pointer;
            accent-color: var(--theme-color-1); flex-shrink: 0;
        }
        .trust-row label { font-size: 13px; color: #555; cursor: pointer; margin: 0; position: static; transform: none; }

        /* Resend */
        .resend-btn {
            background: none; border: none;
            color: var(--theme-color-1); text-decoration: underline;
            cursor: pointer; font-size: 13px; padding: 0; font-weight: 600;
        }
        .resend-btn:hover { color: var(--theme-color-2); }
        #resendTimer { font-size: 13px; color: #888; font-style: italic; }

        /* Timer ring (visual) */
        .otp-timer-bar {
            height: 3px; border-radius: 3px;
            background: #e0e0e0; margin-bottom: 16px; overflow: hidden;
        }
        .otp-timer-bar-fill {
            height: 100%; width: 100%;
            background: var(--theme-color-1);
            transition: width 1s linear;
        }

        .divider { display:flex; align-items:center; gap:10px; margin:10px 0; }
        .divider::before,.divider::after { content:''; flex:1; height:1px; background:#e5e5e5; }
        .divider span { font-size:12px; color:#aaa; }

        /* Override login CSS agar muat 6 OTP box */
        form#formTwoFactor {
            max-width: 320px;
        }
        form#formTwoFactor .heading h2 {
            font-size: 1.55rem;
        }
        /* forms-wrap perlu lebih lebar sedikit */
        .forms-wrap {
            width: 48%;
        }
    </style>
</head>
<body>
    <main>
        <div class="box">
            <div class="inner-box">
                <div class="forms-wrap">

                    <form id="formTwoFactor" action="{{ route('two-factor.verify') }}" method="POST" autocomplete="off">
                        @csrf

                        {{-- Logo --}}
                        <div class="logo">
                            @php $gs = \App\Models\Pengaturanumum::first(); @endphp
                            @if (!empty($gs->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists('logo/' . $gs->logo))
                                <img src="{{ asset('storage/logo/' . $gs->logo) }}" alt="Logo" style="height:auto;width:75px;margin-bottom:16px;" />
                            @else
                                <img src="{{ asset('assets/login/images/logoweb-1.png') }}" alt="HRIS" />
                            @endif
                            <h4>{{ $gs->nama_perusahaan ?? 'HRIS' }}</h4>
                        </div>

                        {{-- Heading --}}
                        <div class="heading">
                            <h2>Verifikasi Dua Langkah</h2>
                        </div>

                        {{-- Alert --}}
                        @if (session('status'))
                            <div class="alert alert-success">✅ {{ session('status') }}</div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error) {{ $error }}<br> @endforeach
                            </div>
                        @endif

                        {{-- Email hint --}}
                        <div style="text-align:center; margin-bottom:6px;">
                            <span class="email-badge">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                {{ $maskedEmail }}
                            </span>
                        </div>
                        <p style="font-size:12px;color:#888;text-align:center;margin-bottom:20px;">
                            Masukkan kode 6 digit yang dikirim ke email Anda. Kode berlaku <strong>10 menit</strong>.
                        </p>

                        {{-- Timer bar --}}
                        <div class="otp-timer-bar"><div class="otp-timer-bar-fill" id="timerBar"></div></div>

                        <div class="actual-form">
                            {{-- OTP 6 kotak --}}
                            <div class="otp-group">
                                <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="0">
                                <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="1">
                                <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="2">
                                <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="3">
                                <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="4">
                                <input type="text" class="otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="5">
                            </div>
                            {{-- Hidden input gabungan --}}
                            <input type="hidden" name="two_factor_code" id="two_factor_code">

                            {{-- Trust device --}}
                            <div class="trust-row">
                                <input type="checkbox" id="trust_device" name="trust_device" value="1">
                                <label for="trust_device">
                                    Percaya perangkat ini selama <strong>{{ \App\Http\Controllers\Auth\TwoFactorController::COOKIE_DAYS }} hari</strong>
                                </label>
                            </div>

                            {{-- Submit --}}
                            <input type="submit" value="Verifikasi" class="sign-btn" id="btnVerify">

                            <div class="divider"><span>atau</span></div>

                            {{-- Resend --}}
                            <p class="text" style="text-align:center; margin:0 0 8px;">
                                Tidak menerima kode?
                                <button type="button" id="btnResend" class="resend-btn">Kirim ulang</button>
                                <span id="resendTimer" style="display:none;"></span>
                            </p>

                            <p style="text-align:center; margin:0;">
                                <a href="{{ route('login') }}" style="font-size:12px;color:#aaa;text-decoration:none;">
                                    ← Kembali ke halaman login
                                </a>
                            </p>
                        </div>
                    </form>

                    {{-- Form resend (terpisah, tidak nested) --}}
                    <form id="formResend" action="{{ route('two-factor.resend') }}" method="POST" style="display:none;">
                        @csrf
                    </form>

                </div>

                <div class="carousel">
                    <div class="images-wrapper">
                        <img src="{{ asset('assets/login/img/image1.png') }}" class="image img-1 show" alt="" />
                        <img src="{{ asset('assets/login/img/image2.png') }}" class="image img-2" alt="" />
                        <img src="{{ asset('assets/login/img/image3.png') }}" class="image img-3" alt="" />
                    </div>
                    <div class="text-slider">
                        <div class="text-wrap">
                            <div class="text-group">
                                <h2>Keamanan Akun Terjaga!</h2>
                                <h2>Login Aman dengan 2FA!</h2>
                                <h2>Verifikasi Dua Langkah Aktif!</h2>
                            </div>
                        </div>
                        <div class="bullets">
                            <span class="active" data-value="1"></span>
                            <span data-value="2"></span>
                            <span data-value="3"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('assets/login/script/app.js') }}"></script>
    <script>
    (function () {
        const digits   = document.querySelectorAll('.otp-digit');
        const hidden   = document.getElementById('two_factor_code');
        const btnResend  = document.getElementById('btnResend');
        const timerEl    = document.getElementById('resendTimer');
        const formResend = document.getElementById('formResend');
        const timerBar   = document.getElementById('timerBar');

        // ── OTP digit navigation ──────────────────────────────────────────
        digits.forEach((el, i) => {
            el.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace') {
                    el.value = '';
                    el.classList.remove('filled');
                    syncHidden();
                    if (i > 0) digits[i - 1].focus();
                }
            });

            el.addEventListener('input', () => {
                // only digits
                el.value = el.value.replace(/\D/g, '').slice(-1);
                el.classList.toggle('filled', el.value !== '');
                syncHidden();
                if (el.value && i < 5) digits[i + 1].focus();
            });

            el.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                pasted.split('').slice(0, 6).forEach((ch, j) => {
                    if (digits[j]) {
                        digits[j].value = ch;
                        digits[j].classList.add('filled');
                    }
                });
                syncHidden();
                digits[Math.min(pasted.length, 5)].focus();
            });
        });

        function syncHidden() {
            hidden.value = Array.from(digits).map(d => d.value).join('');
        }

        // Auto-focus first digit
        digits[0].focus();

        // ── Timer bar (10 menit = 600 detik) ─────────────────────────────
        let totalSecs = 600;
        let elapsed   = 0;
        const barInterval = setInterval(() => {
            elapsed++;
            const pct = Math.max(0, 100 - (elapsed / totalSecs * 100));
            timerBar.style.width = pct + '%';
            timerBar.style.background = pct > 30 ? 'var(--theme-color-1)' : '#dc3545';
            if (elapsed >= totalSecs) clearInterval(barInterval);
        }, 1000);

        // ── Resend cooldown ───────────────────────────────────────────────
        function startCooldown(seconds) {
            let countdown = seconds;
            btnResend.style.display = 'none';
            timerEl.style.display   = 'inline';
            timerEl.textContent     = 'Kirim ulang dalam ' + countdown + 's';
            const iv = setInterval(() => {
                countdown--;
                timerEl.textContent = 'Kirim ulang dalam ' + countdown + 's';
                if (countdown <= 0) {
                    clearInterval(iv);
                    timerEl.style.display   = 'none';
                    btnResend.style.display = 'inline';
                }
            }, 1000);
        }

        btnResend.addEventListener('click', () => formResend.submit());

        @if(session('status'))
            startCooldown(60);
        @endif
    })();
    </script>
</body>
</html>

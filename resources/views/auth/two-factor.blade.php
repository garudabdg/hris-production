<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verifikasi 2FA - HRIS DIDIMAX V3</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/two_factor.css') }}">
    
    <style>
        :root {
            /* Dynamic Theme Colors */
            --theme-color-1: {{ optional(\App\Models\Pengaturanumum::first())->theme_color_1 ?? '#053b22' }};
            --theme-color-2: {{ optional(\App\Models\Pengaturanumum::first())->theme_color_2 ?? '#0b6a3a' }};
        }
    </style>
</head>

<body class="antialiased min-h-screen flex items-center justify-center relative overflow-hidden text-gray-800">
    <!-- Background Gradient & Animated Shapes -->
    <div class="absolute inset-0 z-0 bg-gradient"></div>
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <!-- Main Content -->
    <main class="w-full max-w-md p-6 z-10 relative mx-auto">
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden login-card p-8 border border-white/20">
            <div class="text-center mb-6">
                <div class="inline-block p-3 rounded-2xl bg-gray-50 shadow-sm mb-4 border border-gray-100">
                    @php $gs = \App\Models\Pengaturanumum::first(); @endphp
                    @if (!empty($gs->logo) && \Illuminate\Support\Facades\Storage::disk('public')->exists('logo/' . $gs->logo))
                        <img src="{{ asset('storage/logo/' . $gs->logo) }}" alt="Company Logo" class="h-14 mx-auto object-contain" />
                    @else
                        <img src="{{ asset('assets/login/images/logoweb-1.png') }}" alt="HRIS Logo" class="h-14 mx-auto object-contain" />
                    @endif
                </div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Verifikasi Dua Langkah</h1>
                <p class="text-sm text-gray-500 mt-2">Amankan akun Anda dengan 2FA.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success mb-5 p-4 rounded-xl bg-green-50 text-green-700 border border-green-100 text-sm flex items-start shadow-sm">
                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-5 p-4 rounded-xl bg-red-50 text-red-700 border border-red-100 text-sm flex items-start shadow-sm">
                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                    <div class="font-medium">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="text-center mb-6">
                <span class="inline-flex items-center gap-2 bg-green-50/50 border border-green-200 text-green-800 rounded-full px-4 py-1.5 text-sm font-medium shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    {{ $maskedEmail ?? 'email@example.com' }}
                </span>
                <p class="text-xs text-gray-500 mt-3 leading-relaxed">Masukkan 6 digit kode yang dikirim ke email Anda.<br/>Kode berlaku <strong>10 menit</strong>.</p>
            </div>

            <!-- Timer Bar -->
            <div class="h-1 bg-gray-100 rounded-full overflow-hidden mb-6">
                <div id="timerBar" class="h-full w-full bg-gradient-to-r from-green-400 to-emerald-500 transition-all duration-1000 ease-linear"></div>
            </div>

            <form id="formTwoFactor" action="{{ route('two-factor.verify') }}" method="POST" autocomplete="off" class="space-y-6">
                @csrf
                <div class="flex gap-2 justify-center" id="otp-group">
                    <input type="text" class="w-11 h-14 text-center text-2xl font-bold text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:outline-none transition-all duration-200 otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="0">
                    <input type="text" class="w-11 h-14 text-center text-2xl font-bold text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:outline-none transition-all duration-200 otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="1">
                    <input type="text" class="w-11 h-14 text-center text-2xl font-bold text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:outline-none transition-all duration-200 otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="2">
                    <input type="text" class="w-11 h-14 text-center text-2xl font-bold text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:outline-none transition-all duration-200 otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="3">
                    <input type="text" class="w-11 h-14 text-center text-2xl font-bold text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:outline-none transition-all duration-200 otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="4">
                    <input type="text" class="w-11 h-14 text-center text-2xl font-bold text-gray-800 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:outline-none transition-all duration-200 otp-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" data-index="5">
                </div>
                <input type="hidden" name="two_factor_code" id="two_factor_code">

                <div class="flex items-center justify-center pt-2">
                    <input type="checkbox" id="trust_device" name="trust_device" value="1" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer transition-colors accent-green-600" />
                    <label for="trust_device" class="ml-2 block text-sm text-gray-600 cursor-pointer select-none">Percayai perangkat ini selama <strong>{{ \App\Http\Controllers\Auth\TwoFactorController::COOKIE_DAYS ?? 30 }} hari</strong></label>
                </div>

                <button type="submit" id="btnVerify" class="w-full py-3.5 px-4 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-xl btn-primary">
                    Verifikasi Kode
                </button>
            </form>

            <div class="relative flex py-5 items-center">
                <div class="flex-grow border-t border-gray-100"></div>
                <span class="flex-shrink-0 mx-4 text-gray-400 text-xs uppercase tracking-wider">Atau</span>
                <div class="flex-grow border-t border-gray-100"></div>
            </div>

            <div class="text-center space-y-4">
                <p class="text-sm text-gray-600">
                    Tidak menerima kode?
                    <button type="button" id="btnResend" class="font-bold theme-text hover:underline transition-colors ml-1 focus:outline-none">Kirim ulang</button>
                    <span id="resendTimer" class="font-medium text-gray-400 hidden ml-1"></span>
                </p>

                <p>
                    <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-gray-600 transition-colors inline-flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Kembali ke halaman login
                    </a>
                </p>
            </div>
        </div>
    </main>

    <form id="formResend" action="{{ route('two-factor.resend') }}" method="POST" class="hidden">
        @csrf
    </form>

    <script>
        window.TwoFactorConfig = {
            hasStatus: {{ session('status') ? 'true' : 'false' }}
        };
    </script>
    <!-- Custom Javascript -->
    <script src="{{ asset('assets/js/two_factor.js') }}"></script>
</body>
</html>

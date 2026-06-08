<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In - HRIS DIDIMAX V3</title>

    <!-- PWA Meta Tags -->
    <meta name="application-name" content="E-Presensi GPS V2">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="E-Presensi">
    <meta name="description" content="Aplikasi Presensi GPS untuk Karyawan">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#696cff">

    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="/assets/img/icons/pwa/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/assets/img/icons/pwa/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/assets/img/icons/pwa/icon-512x512.png">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/loginuser.css') }}">
    
    <style>
        :root {
            /* Dynamic Theme Colors from General Setting */
            --theme-color-1: {{ $general_setting->theme_color_1 ?? '#053b22' }};
            --theme-color-2: {{ $general_setting->theme_color_2 ?? '#0b6a3a' }};
        }
    </style>
    
    <script>
        window.Config = {
            csrfToken: "{{ csrf_token() }}",
            routes: {
                sendOtp: "{{ route('password.send-otp') }}",
                verifyOtp: "{{ route('password.verify-otp') }}",
                resetPassword: "{{ route('password.reset-with-otp') }}"
            }
        };
    </script>
</head>

<body class="antialiased min-h-screen flex items-center justify-center relative overflow-hidden text-gray-800">
    <!-- Background Gradient & Animated Shapes -->
    <div id="vanta-bg" class="absolute inset-0 z-0"></div>

    <!-- Main Content -->
    <main class="w-full max-w-md p-6 z-10 relative mx-auto">
        <div class="bg-white/60 backdrop-blur-md rounded-3xl shadow-2xl overflow-hidden login-card p-8 border border-white/40">
            <div class="text-center mb-8">
                    @if (!empty($general_setting->logo) && Storage::disk('public')->exists('logo/' . $general_setting->logo))
                        <img src="{{ asset('storage/logo/' . $general_setting->logo) }}" alt="Company Logo" class="h-16 mx-auto object-contain mb-4" />
                    @else
                        <img src="{{ asset('assets/login/images/logoweb-1.png') }}" alt="HRIS Logo" class="h-16 mx-auto object-contain mb-4" />
                    @endif
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">HRIS DIDIMAX V3</h1>
                <p class="text-sm text-gray-500 mt-2">Welcome back! Please enter your details.</p>
            </div>

            <!-- Alerts for Error -->
            @if (session('error'))
                <div class="alert mb-5 p-4 rounded-xl bg-red-50 text-red-700 border border-red-100 text-sm flex items-start shadow-sm">
                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert mb-5 p-4 rounded-xl bg-red-50 text-red-700 border border-red-100 text-sm flex items-start shadow-sm">
                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                    <div class="font-medium">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form id="formAuthentication" action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="id_user" class="block text-sm font-semibold text-gray-700 mb-1.5">Username or Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" id="id_user" name="id_user" value="{{ old('id_user') }}" 
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-0 transition-all duration-200 bg-gray-50/50 hover:bg-white @error('id_user') border-red-500 @enderror input-field"
                               placeholder="Enter your username or email" required autocomplete="off" />
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                        <a href="#" onclick="showForgotPasswordModal(); return false;" class="text-xs font-semibold theme-text hover:underline transition-colors">Forgot password?</a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" id="password" name="password" 
                               class="w-full pl-10 pr-10 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-0 transition-all duration-200 bg-gray-50/50 hover:bg-white @error('password') border-red-500 @enderror input-field"
                               placeholder="••••••••" required autocomplete="off" />
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                            <!-- Eye icon SVG -->
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="eyeIcon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center pt-1">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer transition-colors accent-blue-600" />
                    <label for="remember" class="ml-2 block text-sm text-gray-600 cursor-pointer select-none">Remember me</label>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-xl btn-primary mt-6">
                    Sign In
                </button>
            </form>
            
            
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center text-white/80 text-sm font-medium tracking-wide">
            Copyright by IT DIDIMAX &copy; 2026
        </div>
    </main>

    <!-- Forgot Password Modal - Step 1: Email Input -->
    <div id="forgotPasswordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative">
            <button onclick="hideForgotPasswordModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="p-6 sm:p-8">
                <div class="w-12 h-12 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Reset Password</h2>
                <p class="text-sm text-gray-500 mb-6">Enter your email address and we'll send you an OTP to reset your password.</p>
                
                <div id="forgotPasswordMessage" class="hidden p-4 rounded-xl text-sm mb-5 font-medium border flex items-start"></div>
                
                <form id="forgotPasswordForm" class="space-y-4">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email address</label>
                        <input type="email" id="resetEmail" name="email" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-0 transition-all duration-200 bg-gray-50/50 hover:bg-white input-field" placeholder="nama@email.com">
                    </div>
                    <button type="submit" class="w-full py-3.5 px-4 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 btn-primary mt-2">Send OTP</button>
                </form>
            </div>
        </div>
    </div>

    <!-- OTP Verification Modal - Step 2: OTP Input -->
    <div id="otpVerificationModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative">
            <button onclick="hideOtpModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="p-6 sm:p-8 text-center">
                <div class="w-12 h-12 bg-green-50 text-green-500 rounded-full flex items-center justify-center mb-4 mx-auto">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Verify OTP</h2>
                <p class="text-sm text-gray-500 mb-1">We sent a 6-digit OTP to:</p>
                <p id="otpEmailDisplay" class="font-bold text-gray-800 mb-6"></p>
                
                <div id="otpMessage" class="hidden p-4 rounded-xl text-sm mb-5 font-medium border text-left flex items-start"></div>
                
                <form id="otpVerificationForm" class="space-y-6">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" id="resetToken" name="token">
                    <input type="hidden" id="fullOtp" name="otp">
                    
                    <div class="flex justify-center gap-2 sm:gap-3">
                        <input type="text" maxlength="1" class="otp-input w-10 sm:w-12 h-12 sm:h-14 text-center text-2xl font-bold rounded-xl border border-gray-200 focus:outline-none focus:ring-0 transition-all duration-200 bg-gray-50/50 hover:bg-white input-field" oninput="moveToNext(this, 2)" autofocus>
                        <input type="text" maxlength="1" class="otp-input w-10 sm:w-12 h-12 sm:h-14 text-center text-2xl font-bold rounded-xl border border-gray-200 focus:outline-none focus:ring-0 transition-all duration-200 bg-gray-50/50 hover:bg-white input-field" oninput="moveToNext(this, 3)">
                        <input type="text" maxlength="1" class="otp-input w-10 sm:w-12 h-12 sm:h-14 text-center text-2xl font-bold rounded-xl border border-gray-200 focus:outline-none focus:ring-0 transition-all duration-200 bg-gray-50/50 hover:bg-white input-field" oninput="moveToNext(this, 4)">
                        <input type="text" maxlength="1" class="otp-input w-10 sm:w-12 h-12 sm:h-14 text-center text-2xl font-bold rounded-xl border border-gray-200 focus:outline-none focus:ring-0 transition-all duration-200 bg-gray-50/50 hover:bg-white input-field" oninput="moveToNext(this, 5)">
                        <input type="text" maxlength="1" class="otp-input w-10 sm:w-12 h-12 sm:h-14 text-center text-2xl font-bold rounded-xl border border-gray-200 focus:outline-none focus:ring-0 transition-all duration-200 bg-gray-50/50 hover:bg-white input-field" oninput="moveToNext(this, 6)">
                        <input type="text" maxlength="1" class="otp-input w-10 sm:w-12 h-12 sm:h-14 text-center text-2xl font-bold rounded-xl border border-gray-200 focus:outline-none focus:ring-0 transition-all duration-200 bg-gray-50/50 hover:bg-white input-field" oninput="moveToNext(this, null)">
                    </div>
                    
                    <button type="submit" class="w-full py-3.5 px-4 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 btn-primary">Verify OTP</button>
                    
                    <div class="text-sm">
                        <a href="#" onclick="resendOtp(); return false;" class="font-medium theme-text hover:underline transition-colors">Resend OTP</a>
                        <div id="resendTimer" class="text-gray-500 mt-1"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- New Password Modal - Step 3: Set New Password -->
    <div id="newPasswordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative">
            <button onclick="hideNewPasswordModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="p-6 sm:p-8">
                <div class="w-12 h-12 bg-purple-50 text-purple-500 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Set New Password</h2>
                <p class="text-sm text-gray-500 mb-6">Please enter your new password below.</p>
                
                <div id="newPasswordMessage" class="hidden p-4 rounded-xl text-sm mb-5 font-medium border flex items-start"></div>
                
                <form id="newPasswordForm" class="space-y-4">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" id="passwordResetToken" name="token">
                    <input type="hidden" id="passwordResetOtp" name="otp">
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">New Password</label>
                        <input type="password" id="newPassword" name="password" required minlength="8" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-0 transition-all duration-200 bg-gray-50/50 hover:bg-white input-field" placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password</label>
                        <input type="password" name="password_confirmation" required minlength="8" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-0 transition-all duration-200 bg-gray-50/50 hover:bg-white input-field" placeholder="••••••••">
                    </div>
                    <p class="text-xs text-gray-500 hidden" id="karyawanPasswordHint">Password minimal 6 karakter.</p>
                    <p class="text-xs text-red-500 font-semibold hidden mt-2" id="adminPasswordHint">
                        <svg class="w-4 h-4 inline-block mr-0.5 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        {{ \App\Helpers\PasswordHelper::getRequirementMessage(true) }}
                    </p>
                    <button type="submit" class="w-full py-3.5 px-4 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 btn-primary mt-4">Reset Password</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Custom Javascript -->
    <script src="{{ asset('assets/js/loginuser.js') }}?v={{ time() }}"></script>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('ServiceWorker registration successful');
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
    
    <!-- Vanta JS Background -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanta/0.5.24/vanta.waves.min.js"></script>
    <script>
    VANTA.WAVES({
      el: "#vanta-bg",
      mouseControls: true,
      touchControls: true,
      gyroControls: false,
      minHeight: 200.00,
      minWidth: 200.00,
      scale: 1.00,
      scaleMobile: 1.00,
      color: 0x340f88
    })
    </script>

    <!-- PWA Install Prompt - Only on Login Page -->
    @include('components.pwa-install-prompt')
</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign In - HRIS DIDIMAX V3</title>

    <!-- PWA Meta Tags -->
    <meta name="application-name" content="HRIS-DIDIMAX V3">
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
    <main class="w-full max-w-5xl p-4 md:p-6 z-10 relative mx-auto flex items-center justify-center min-h-screen">
        <div class="flex flex-col md:flex-row w-full bg-white rounded-2xl shadow-2xl overflow-hidden login-card h-auto md:h-[600px]">
            
            <!-- Left Side (Theme Color) -->
            <div class="w-full md:w-5/12 p-8 md:p-12 text-white flex flex-col justify-between relative overflow-hidden" style="background-color: var(--theme-color-1);">
                <!-- Abstract BG Shapes -->
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-black opacity-10 rounded-full blur-2xl"></div>
                
                <!-- Logo -->
                <div class="flex items-center gap-3 mb-6 relative z-10">
                    @if (!empty($general_setting->logo) && Storage::disk('public')->exists('logo/' . $general_setting->logo))
                        <img src="{{ asset('storage/logo/' . $general_setting->logo) }}" alt="Company Logo" class="h-8 object-contain" />
                    @else
                        <!-- Default Icon -->
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    @endif
                    <span class="text-xl font-bold tracking-tight">HRIS DIDIMAX</span>
                </div>

                <!-- Illustration (Clipboard/Checklist matching the design) -->
                <div class="flex-grow flex items-center justify-center py-6 relative z-10">
                    <svg viewBox="0 0 200 200" class="w-full max-w-[220px] drop-shadow-2xl" xmlns="http://www.w3.org/2000/svg">
                        <!-- Background Blob -->
                        <path fill="rgba(255,255,255,0.1)" d="M45.7,-76.4C58.9,-69.1,69.1,-56,76.5,-42.1C83.9,-28.2,88.5,-14.1,87.6,-0.5C86.7,13.1,80.3,26.2,72.7,38.1C65.1,50,56.3,60.8,44.5,68.9C32.7,77,18.1,82.4,3.2,77.2C-11.7,72,-27.1,56.2,-40.4,43.2C-53.7,30.2,-64.9,20,-69.2,7.2C-73.5,-5.6,-70.9,-21,-63.4,-33.2C-55.9,-45.4,-43.5,-54.4,-30.9,-61.9C-18.3,-69.4,-5.5,-75.4,8.5,-79.8C22.5,-84.2,32.5,-83.7,45.7,-76.4Z" transform="translate(100 100) rotate(15)" />
                        <!-- Shadow -->
                        <rect x="50" y="45" width="100" height="130" rx="8" fill="rgba(0,0,0,0.15)" transform="rotate(-10 100 100)" />
                        <!-- Clipboard Board -->
                        <rect x="40" y="35" width="100" height="130" rx="8" fill="#ffffff" transform="rotate(-15 100 100)" />
                        <rect x="45" y="40" width="90" height="120" rx="4" fill="#f0f4f8" transform="rotate(-15 100 100)" />
                        <!-- Clip -->
                        <path d="M75 15 h30 v15 h-30 z" fill="#1e293b" transform="rotate(-15 100 100)" rx="3" />
                        <rect x="85" y="10" width="10" height="10" rx="5" fill="#cbd5e1" transform="rotate(-15 100 100)" />
                        <!-- Lines & Checkmarks -->
                        <g transform="rotate(-15 100 100)">
                            <!-- Item 1 -->
                            <rect x="55" y="55" width="15" height="15" rx="3" fill="var(--theme-color-1)" opacity="0.2" />
                            <path d="M58 62 l4 4 l8 -8" fill="none" stroke="var(--theme-color-1)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <rect x="78" y="60" width="45" height="4" rx="2" fill="var(--theme-color-1)" opacity="0.6" />
                            <!-- Item 2 -->
                            <rect x="55" y="80" width="15" height="15" rx="3" fill="var(--theme-color-1)" opacity="0.2" />
                            <path d="M58 83 l10 10 M68 83 l-10 10" fill="none" stroke="var(--theme-color-1)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <rect x="78" y="85" width="35" height="4" rx="2" fill="var(--theme-color-1)" opacity="0.6" />
                            <!-- Item 3 -->
                            <rect x="55" y="105" width="15" height="15" rx="3" fill="var(--theme-color-1)" opacity="0.2" />
                            <path d="M58 112 l4 4 l8 -8" fill="none" stroke="var(--theme-color-1)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <rect x="78" y="110" width="40" height="4" rx="2" fill="var(--theme-color-1)" opacity="0.6" />
                        </g>
                        <!-- Floating Pen/Marker -->
                        <g transform="translate(30, 110) rotate(-45)">
                            <rect x="0" y="0" width="12" height="50" rx="3" fill="var(--theme-color-2)" />
                            <rect x="2" y="2" width="8" height="46" rx="2" fill="var(--theme-color-1)" />
                            <path d="M0 50 L6 60 L12 50 Z" fill="#1e293b" />
                            <path d="M0 10 L12 10" stroke="rgba(255,255,255,0.3)" stroke-width="2" />
                        </g>
                    </svg>
                </div>

                <!-- Welcome Text -->
                <div class="relative z-10 mt-4">
                    <h2 class="text-3xl font-bold mb-3 tracking-tight">Welcome!</h2>
                    <p class="text-white/80 text-sm leading-relaxed mb-8">
                        Get a real centralized portal on top of your HR environment, with HRIS DIDIMAX.
                    </p>
                    
                    <!-- Carousel Indicator Dots -->
                    <div class="flex gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-white"></div>
                        <div class="w-2.5 h-2.5 rounded-full border border-white opacity-70"></div>
                        <div class="w-2.5 h-2.5 rounded-full border border-white opacity-70"></div>
                    </div>
                </div>
            </div>

            <!-- Right Side (White) -->
            <div class="w-full md:w-7/12 p-8 md:p-12 lg:p-16 bg-white flex flex-col justify-center">
                
                <h2 class="text-3xl font-bold text-gray-800 mb-2">Log In</h2>
                <div class="flex flex-wrap items-center text-sm text-gray-500 mb-1">
                    <span class="mr-1">Don't have an account?</span>
                    <a href="javascript:void(0)" onclick="alert('Silakan hubungi HRD untuk pembuatan akun.')" class="font-semibold transition-colors" style="color: var(--theme-color-1);">Create an account</a>
                </div>
                <p class="text-xs text-gray-400 mb-8">It will take less than a minute.</p>

                <!-- Alerts -->
                @if (session('error'))
                    <div class="mb-5 p-3 rounded-lg bg-red-50 text-red-600 border border-red-100 text-xs flex items-center">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-5 p-3 rounded-lg bg-red-50 text-red-600 border border-red-100 text-xs">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-center mt-1 first:mt-0">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form id="formAuthentication" action="{{ route('login') }}" method="POST" class="w-full">
                    @csrf
                    
                    <!-- Username Field -->
                    <div class="relative mb-6">
                        <input type="text" id="id_user" name="id_user" value="{{ old('id_user') }}" 
                               class="w-full border-0 border-b-2 border-gray-200 px-0 py-2.5 focus:ring-0 focus:border-[var(--theme-color-1)] bg-transparent text-sm text-gray-700 transition-colors placeholder-gray-400 @error('id_user') border-red-500 @enderror"
                               placeholder="Username" required autocomplete="off" />
                        <div class="absolute right-0 top-2 text-gray-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="relative mb-8">
                        <input type="password" id="password" name="password" 
                               class="w-full border-0 border-b-2 border-gray-200 px-0 py-2.5 focus:ring-0 focus:border-[var(--theme-color-1)] bg-transparent text-sm text-gray-700 transition-colors placeholder-gray-400 pr-10 @error('password') border-red-500 @enderror"
                               placeholder="Password" required autocomplete="off" />
                        <button type="button" id="togglePassword" class="absolute right-0 top-2 text-gray-400 hover:text-gray-600 focus:outline-none">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" id="eyeIcon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </button>
                    </div>

                    <!-- Sign in & Remember -->
                    <div class="flex items-center gap-6 mb-6">
                        <button type="submit" class="text-white px-8 py-2.5 rounded shadow-md font-medium transition-all hover:shadow-lg transform hover:-translate-y-0.5" style="background-color: var(--theme-color-1);">
                            Sign in
                        </button>
                        <label for="remember" class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer select-none">
                            <input type="checkbox" id="remember" name="remember" class="w-4 h-4 rounded border-gray-300 focus:ring-[var(--theme-color-1)] cursor-pointer accent-[var(--theme-color-1)]" />
                            Remember password
                        </label>
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="text-center sm:text-left mt-8">
                        <a href="#" onclick="showForgotPasswordModal(); return false;" class="text-xs font-semibold hover:underline transition-colors" style="color: var(--theme-color-1);">
                            Forget your password?
                        </a>
                    </div>
                </form>
            </div>
            
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vanta/0.5.24/vanta.clouds.min.js"></script>
    <script>
    VANTA.CLOUDS({
      el: "#vanta-bg",
      mouseControls: true,
      touchControls: true,
      gyroControls: false,
      minHeight: 200.00,
      minWidth: 200.00,
      backgroundColor: 0x1e16ca,
      skyColor: 0x461be0
    })
    </script>

    <!-- Force reload if page is loaded from BFCache to prevent stale CSRF tokens after logout -->
    <script>
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

    <!-- PWA Install Prompt - Only on Login Page -->
    @include('components.pwa-install-prompt')
</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up - HRIS DIDIMAX</title>

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

    <link rel="stylesheet" href="{{ asset('assets/login/css/style.css') }}" />
    <style>
        :root {
            /* Dynamic Theme Colors */
            --theme-color-1: {{ $general_setting->theme_color_1 ?? '#053b22' }};
            --theme-color-2: {{ $general_setting->theme_color_2 ?? '#0b6a3a' }};
        }

        .sign-btn {
            background-color: var(--theme-color-1) !important;
        }

        .sign-btn:hover {
            background-color: var(--theme-color-2) !important;
        }

        .bullets span.active {
            background-color: var(--theme-color-1) !important;
        }

        .carousel {
            background: var(--theme-color-1) !important;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            animation: slideIn 0.5s ease-out;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .text-group h2 {
            color: #ffffff !important;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .register-link a {
            color: var(--theme-color-1);
            font-weight: 600;
            text-decoration: none;
        }
        
        .register-link a:hover {
            color: var(--theme-color-2);
            text-decoration: underline;
        }
    </style>

</head>

<body>
    <main>
        <div class="box">
            <div class="inner-box">
                <div class="forms-wrap">
                    <form id="formRegister" class="mb-3" action="{{ route('register') }}" method="POST">
                        @csrf
                        <div class="logo">
                            @if (!empty($general_setting->logo) && Storage::disk('public')->exists('logo/' . $general_setting->logo))
                                <img src="{{ asset('storage/logo/' . $general_setting->logo) }}" alt="Company Logo" style="height: auto; width: 80px; margin-bottom: 20px;" />
                            @else
                                <img src="{{ asset('assets/login/images/logoweb-1.png') }}" alt="easyclass" />
                            @endif
                            <h4>HRIS DIDIMAX V3</h4>
                            <p style="color: #666; font-size: 14px; margin-top: 5px;">Create New Account</p>
                        </div>

                        <div class="heading">
                            <h2>Join Our Team</h2>
                            <p style="color: #666; font-size: 14px; margin-top: 5px; font-weight: normal;">
                                After registration, please check your email for verification link
                            </p>
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    {{ $error }}<br>
                                @endforeach
                            </div>
                        @endif

                        <div class="actual-form">
                            <!-- Name -->
                            <div class="input-wrap">
                                <input type="text" class="input-field @error('name') is-invalid @enderror" 
                                    name="name" value="{{ old('name') }}" 
                                    placeholder="Full Name" required autofocus />
                            </div>

                            <!-- Username -->
                            <div class="input-wrap">
                                <input type="text" class="input-field @error('username') is-invalid @enderror" 
                                    name="username" value="{{ old('username') }}" 
                                    placeholder="Username (max 10 characters)" maxlength="10" required />
                            </div>

                            <!-- Email -->
                            <div class="input-wrap">
                                <input type="email" class="input-field @error('email') is-invalid @enderror" 
                                    name="email" value="{{ old('email') }}" 
                                    placeholder="Email Address" required />
                            </div>

                            <!-- Password -->
                            <div class="input-wrap">
                                <input type="password" class="input-field @error('password') is-invalid @enderror" 
                                    name="password" placeholder="Password" required />
                            </div>

                            <!-- Confirm Password -->
                            <div class="input-wrap">
                                <input type="password" class="input-field" 
                                    name="password_confirmation" placeholder="Confirm Password" required />
                            </div>

                            <div class="checkbox-wrap">
                                <input type="checkbox" id="terms" name="terms" style="margin-right: 8px; width: 16px; height: 16px;">
                                <label for="terms" style="color: #666; font-size: 14px; cursor: pointer; margin-left: 20px;">
                                    I agree to the <a href="#" style="color: var(--theme-color-1);">Terms & Conditions</a>
                                </label>
                            </div>

                            <input type="submit" value="Create Account" class="sign-btn" />

                            <div class="register-link">
                                Already have an account? <a href="{{ route('login') }}">Sign In</a>
                            </div>

                        </div>
                    </form>

                </div>

                <div class="carousel">
                    <div class="images-wrapper">
                        <img src="{{ asset('assets/login/images/image1.png') }}" class="image img-1 show" alt="" />
                        <img src="{{ asset('assets/login/images/image2.png') }}" class="image img-2" alt="" />
                        <img src="{{ asset('assets/login/images/image3.png') }}" class="image img-3" alt="" />
                    </div>

                    <div class="text-slider">
                        <div class="text-wrap">
                            <div class="text-group">
                                <h2>Start Your Journey With Us!</h2>
                                <h2>Join Our Growing Team!</h2>
                                <h2>Build Your Career Here!</h2>
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

    <!-- Javascript file -->
    <script src="{{ asset('assets/login/script/app.js') }}"></script>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful with scope: ', registration.scope);
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>

    <!-- PWA Install Prompt -->
    @include('components.pwa-install-prompt')
</body>

</html>
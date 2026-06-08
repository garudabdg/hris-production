<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Lupa Password - HRIS DIDIMAX V3</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS (reuse two_factor.css) -->
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
                <div class="inline-block p-4 rounded-full bg-blue-50 text-blue-500 shadow-sm mb-4 border border-blue-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">{{ __('Lupa Password?') }}</h1>
                <p class="text-sm text-gray-500 mt-3 leading-relaxed">
                    {{ __('Masukkan alamat email Anda yang terdaftar, dan kami akan mengirimkan tautan untuk mengatur ulang password Anda.') }}
                </p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="alert mb-6 p-4 rounded-xl bg-green-50 text-green-700 border border-green-100 text-sm flex items-start shadow-sm">
                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-medium">{{ session('status') }}</span>
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

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5 mt-6">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">{{ __('Email') }}</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-0 transition-all duration-200 bg-gray-50/50 hover:bg-white @error('email') border-red-500 @enderror input-field"
                               placeholder="nama@email.com" />
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 px-4 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-xl btn-primary">
                        {{ __('Kirim Tautan Reset Password') }}
                    </button>
                </div>
                
                <div class="text-center mt-6 pt-4 border-t border-gray-100">
                    <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium transition-colors inline-flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        Kembali ke halaman login
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>

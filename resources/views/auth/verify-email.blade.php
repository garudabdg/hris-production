<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verify Email - HRIS DIDIMAX V3</title>

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
                <div class="inline-block p-4 rounded-full bg-blue-50 text-blue-500 shadow-sm mb-4 border border-blue-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">{{ __('Verifikasi Email') }}</h1>
                <p class="text-sm text-gray-500 mt-3 leading-relaxed">
                    {{ __('Terima kasih telah mendaftar! Sebelum memulai, mohon verifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan ke email Anda. Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkan ulang.') }}
                </p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="alert mb-6 p-4 rounded-xl bg-green-50 text-green-700 border border-green-100 text-sm flex items-start shadow-sm">
                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium">{{ __('Tautan verifikasi baru telah dikirimkan ke alamat email yang Anda berikan saat pendaftaran.') }}</span>
                </div>
            @endif

            <div class="flex flex-col gap-3 mt-6">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full py-3.5 px-4 text-white font-semibold rounded-xl shadow-lg transition-all duration-300 transform hover:-translate-y-0.5 hover:shadow-xl btn-primary">
                        {{ __('Kirim Ulang Tautan Verifikasi') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-3.5 px-4 text-gray-700 font-semibold rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition-all duration-300 shadow-sm hover:shadow">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>

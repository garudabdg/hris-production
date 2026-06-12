<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --theme-color-1: {{ $general_setting->theme_color_1 ?? '#32745e' }};
            --theme-color-2: {{ $general_setting->theme_color_2 ?? '#1a4a3a' }};
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--theme-color-1) 0%, var(--theme-color-2) 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .floating {
            animation: floating 3.5s ease-in-out infinite;
        }

        @keyframes floating {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        .number-404 {
            font-size: 8rem;
            font-weight: 800;
            line-height: 1;
            background: linear-gradient(135deg, var(--theme-color-1) 0%, var(--theme-color-2) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body class="flex items-center justify-center p-4 bg-pattern">
    <!-- Decorative elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute top-10 left-10 w-24 h-24 bg-white opacity-20 rounded-full blur-xl floating"></div>
        <div class="absolute bottom-10 right-10 w-40 h-40 bg-white opacity-20 rounded-full blur-xl floating" style="animation-delay: 1.5s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-white opacity-5 rounded-full blur-2xl pulse"></div>
    </div>

    <div class="w-full max-w-lg relative z-10">
        <div class="glass-effect rounded-3xl p-10 shadow-2xl text-center" data-aos="zoom-in" data-aos-duration="1000">
            
            <div class="number-404 floating" data-aos="fade-down" data-aos-delay="200">
                404
            </div>

            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-4" data-aos="fade-up" data-aos-delay="400">
                Halaman Tidak Ditemukan
            </h1>

            <p class="text-gray-500 mb-8 px-4" data-aos="fade-up" data-aos-delay="600">
                Maaf, halaman yang Anda cari mungkin telah dihapus, namanya diubah, atau sementara tidak tersedia.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4" data-aos="fade-up" data-aos-delay="800">
                <button onclick="window.history.back()"
                    class="inline-flex justify-center items-center px-6 py-3 bg-white text-gray-700 font-medium rounded-xl shadow-sm border border-gray-200 hover:bg-gray-50 hover:text-gray-900 transition duration-300 ease-in-out transform hover:-translate-y-1">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </button>

                <a href="{{ url('/') }}"
                    class="inline-flex justify-center items-center px-6 py-3 text-white font-medium rounded-xl shadow-lg transition duration-300 ease-in-out transform hover:-translate-y-1 hover:shadow-xl"
                    style="background: linear-gradient(135deg, var(--theme-color-1) 0%, var(--theme-color-2) 100%);">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Beranda Utama
                </a>
            </div>
        </div>
    </div>

    <script>
        AOS.init({
            once: true,
            offset: 50
        });
    </script>
</body>

</html>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Sistem Presensi QR Code & Face Recognition</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistem Presensi Face Recognition" name="description" />
    <meta content="Coderthemes" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon/favicon.ico') }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/iconfont/tabler-icons.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4 font-sans text-gray-800">
    <div class="glass-card w-full max-w-4xl rounded-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-blue-700 text-white p-8 text-center">
            <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ti ti-qrcode text-5xl"></i>
            </div>
            <h1 class="text-3xl font-bold mb-2">Portal Presensi Karyawan</h1>
            <p class="text-blue-100 text-lg">Pilih metode presensi yang ingin Anda gunakan</p>
        </div>

        <!-- Content -->
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Generate QR Code Option -->
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 hover-lift flex flex-col justify-between">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="ti ti-qrcode text-3xl text-indigo-600"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Generate QR Code</h2>
                        <p class="text-gray-500">Buat QR Code unik untuk absen masuk dan pulang karyawan.</p>
                    </div>

                    <form id="qrForm" class="mt-auto">
                        <div class="mb-4">
                            <label for="nik" class="block text-sm font-medium text-gray-700 mb-1">NIK Karyawan</label>
                            <input type="text" id="nik" name="nik" placeholder="Masukkan NIK 9 Digit" maxlength="9" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all outline-none">
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                            <i class="ti ti-qrcode mr-2"></i> Buat QR Code
                        </button>
                    </form>
                </div>

                <!-- Face Recognition Option -->
                <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 hover-lift flex flex-col justify-between">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="ti ti-user-check text-3xl text-emerald-600"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">Face Recognition</h2>
                        <p class="text-gray-500">Gunakan deteksi wajah dan verifikasi liveness untuk absensi yang aman.</p>
                    </div>

                    <div class="mt-auto">
                        <a href="{{ route('facerecognition-presensi.scan_any') }}" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center">
                            <i class="ti ti-camera mr-2"></i> Mulai Scan Wajah
                        </a>
                    </div>
                </div>
            </div>

            <!-- Result Section for QR Code -->
            <div id="errorMessage" class="hidden mt-6 bg-red-50 text-red-600 p-4 rounded-lg text-center font-medium border border-red-200"></div>

            <div id="qrResult" class="hidden mt-8 text-center bg-gray-50 rounded-xl p-8 border border-gray-200 shadow-inner">
                <h3 class="text-xl font-bold text-gray-800 mb-4">QR Code Anda Sudah Siap</h3>
                <div id="qrCode" class="bg-white p-4 inline-block rounded-xl shadow-sm mb-4 border border-gray-200"></div>
                <div id="employeeInfo" class="text-gray-700 bg-white inline-block px-6 py-3 rounded-lg shadow-sm border border-gray-100 text-left"></div>
                <p class="text-gray-500 mt-4 text-sm"><i class="ti ti-info-circle mr-1"></i> Scan QR code ini pada mesin absensi atau kios untuk melakukan presensi</p>
            </div>
        </div>
    </div>

    <!-- Configuration for external JS -->
    <script>
        window.FaceRecogConfig = {
            routes: {
                generate: '{{ route("facerecognition-presensi.generate", ["nik" => ":nik"]) }}'
            }
        };
    </script>
    
    <!-- Custom JS for this page -->
    <script src="{{ asset('assets/js/facerecognition_presensi.js') }}?v=2"></script>
</body>

</html>

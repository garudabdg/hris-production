<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lamaran Terkirim</title>
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">
    <style>
        body { background: #f4f5fb; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .success-icon { font-size: 80px; color: #28c76f; animation: bounceIn 0.6s; }
        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            60% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="text-center p-5">
        <div class="success-icon mb-3">✓</div>
        <h2 class="fw-bold text-success mb-2">Lamaran Berhasil Dikirim!</h2>
        <p class="text-muted mb-4">
            Terima kasih telah melamar. Tim HR kami akan menghubungi Anda dalam waktu dekat.<br>
            Pastikan nomor HP dan email Anda aktif.
        </p>
        <a href="{{ route('recruitment.form') }}" class="btn btn-outline-primary">
            <i class="ti ti-arrow-left me-1"></i> Kembali ke Form
        </a>
    </div>
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
</body>
</html>

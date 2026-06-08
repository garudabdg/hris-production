@extends('layouts.mobile.app')

@push('mystyle')
    <!-- Tabler Icons & Local CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/iconfont/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/create_karyawan.css') }}">
@endpush

@section('content')
    <!-- Camera View -->
    <div class="camera-container">
        <video id="webcam-video" autoplay playsinline muted></video>
    </div>

    <!-- Interface Overlays -->
    <div class="overlay-container">
        <!-- Top Status & Control Row -->
        <div class="top-row">
            <a href="javascript:history.back()" class="btn-back-circle">
                <i class="ti ti-chevron-left"></i>
            </a>
            <div class="status-badge">
                <span class="status-dot" id="statusDot"></span>
                <span id="statusText">Menunggu kamera...</span>
            </div>
            <div style="width: 44px;"></div> <!-- Spacing alignment helper -->
        </div>

        <!-- Warning Toast -->
        <div class="warning-toast" id="warningToast">
            <i class="ti ti-alert-circle"></i>
            <span id="warningMessage">Peringatan</span>
        </div>

        <!-- Center Face Frame Guide -->
        <div class="face-frame" id="faceFrame">
            <!-- Progress shown below frame -->
            <div class="scan-progress-container" id="scanProgress">
                <div class="scan-text">Merekam Wajah...</div>
                <div class="progress-bar-wrapper">
                    <div class="progress-bar-fill" id="progressBarFill"></div>
                </div>
                <div style="font-size: 12px; color: rgba(255,255,255,0.7); margin-top: 5px;">Tahan posisi... <span id="progressPercent">0%</span></div>
            </div>
        </div>
    </div>

    <!-- Bottom Action Triggers -->
    <div class="action-area" id="actionArea">
        <button type="button" class="btn-modern-start" id="btnStart" style="position: relative; z-index: 999999; cursor: pointer; pointer-events: auto;">
            <div class="loading-spinner" id="btnSpinner"></div>
            <i class="ti ti-face-id" id="btnIcon"></i>
            <span id="btnText">Mulai Scan Wajah</span>
        </button>
    </div>

    <!-- Success Screen Overlay -->
    <div class="success-overlay" id="successScreen">
        <i class="ti ti-circle-check-filled success-icon"></i>
        <h2 class="mb-2">Berhasil!</h2>
        <p class="text-white-50 text-center px-4">Data wajah berhasil didaftarkan.<br>Mengalihkan anda kembali...</p>
    </div>
@endsection

@push('myscript')
    <!-- Local Face API Dependencies & Scripts -->
    <script src="{{ asset('assets/vendor/face-api.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script>
        window.CreateKaryawanConfig = {
            modelsUrl: '{{ asset("models") }}',
            csrfToken: '{{ csrf_token() }}',
            nik: '{{ $nik }}',
            storeRoute: '{{ route("facerecognition.store") }}',
            dashboardRoute: '{{ route("dashboard.index") }}'
        };
    </script>
    <script src="{{ asset('assets/js/create_karyawan.js') }}?v=1"></script>
@endpush

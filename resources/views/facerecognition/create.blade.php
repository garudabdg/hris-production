<link rel="stylesheet" href="{{ asset('assets/css/create.css') }}">

<!-- Camera View -->
<div class="camera-container">
    <video id="webcam-video" autoplay playsinline muted></video>
</div>

<!-- Interface Overlays -->
<div class="overlay-container">
    <!-- Top Status -->
    <div class="status-badge">
        <span class="status-dot" id="statusDot"></span>
        <span id="statusText">Menunggu kamera...</span>
    </div>

    <!-- Warning Toast -->
    <div class="warning-toast" id="warningToast">
        <i class="ti ti-alert-circle"></i>
        <span id="warningMessage">Peringatan</span>
    </div>

    <!-- Center Face Frame -->
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

<!-- Bottom Actions -->
<div class="action-area" id="actionArea">
    <button type="button" class="btn-modern-start" id="btnStart" style="position: relative; z-index: 999999; cursor: pointer; pointer-events: auto;">
        <div class="loading-spinner" id="btnSpinner"></div>
        <i class="ti ti-face-id" id="btnIcon"></i>
        <span id="btnText">Mulai Scan Wajah</span>
    </button>
</div>

<!-- Success Screen -->
<div class="success-overlay" id="successScreen">
    <i class="ti ti-circle-check-filled success-icon"></i>
    <h2 class="mb-2">Berhasil!</h2>
    <p class="text-white-50 text-center px-4">Data wajah berhasil didaftarkan.<br>Menutup halaman...</p>
</div>

<!-- Scripts -->
<script src="{{ asset('assets/vendor/face-api.min.js') }}"></script>
<!-- Assuming jQuery is already loaded by the admin layout. If not, uncomment next line -->
<!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> -->

<script>
    window.CreateConfig = {
        modelsUrl: '{{ asset("models") }}',
        csrfToken: '{{ csrf_token() }}',
        nik: '{{ isset($nik) ? $nik : "" }}',
        storeRoute: '{{ route("facerecognition.store") }}'
    };
</script>
<script src="{{ asset('assets/js/create.js') }}?v=1"></script>

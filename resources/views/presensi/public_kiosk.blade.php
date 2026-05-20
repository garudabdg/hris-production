@extends('layouts.kiosk')

@push('mystyle')
<style>
    /* ============================================
       KIOSK PRESENSI — Semi-Formal Modern
       ============================================ */

    .kiosk-page {
        display: flex;
        height: 100vh;
        overflow: hidden;
    }

    /* ---- Left Panel (Branding + Camera) ---- */
    .kiosk-left {
        flex: 0 0 55%;
        background: linear-gradient(160deg, color-mix(in srgb, var(--primary) 90%, #000 10%), var(--primary));
        color: #fff;
        display: flex;
        flex-direction: column;
        padding: 3rem 3.5rem;
        position: relative;
        overflow: hidden;
    }

    .kiosk-left::after {
        content: '';
        position: absolute;
        width: 600px;
        height: 600px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
        bottom: -200px;
        right: -150px;
        pointer-events: none;
    }

    .brand-area {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2.5rem;
    }

    .brand-logo {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(255,255,255,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(8px);
    }

    .brand-logo img {
        width: 32px;
        height: 32px;
        object-fit: contain;
    }

    .brand-text h2 {
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .brand-text span {
        font-size: 0.8rem;
        opacity: 0.7;
        font-weight: 400;
    }

    .camera-section {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 1.5rem;
    }

    .camera-frame {
        position: relative;
        border-radius: 1.25rem;
        overflow: hidden;
        background: #000;
        aspect-ratio: 16/10;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        border: 3px solid rgba(255,255,255,0.15);
    }

    .camera-frame .webcam-capture,
    .camera-frame .webcam-capture video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover;
        display: block;
    }

    .camera-label {
        position: absolute;
        bottom: 1rem;
        left: 1rem;
        background: rgba(0,0,0,0.55);
        backdrop-filter: blur(8px);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.4rem 0.85rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        letter-spacing: 0.03em;
    }

    .camera-label .dot {
        width: 7px;
        height: 7px;
        background: #ef4444;
        border-radius: 50%;
        animation: blink 1.5s infinite;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    .camera-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.85rem;
        opacity: 0.6;
    }

    .loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 20;
    }

    .loading-overlay.active { display: flex; }

    .spinner {
        width: 44px;
        height: 44px;
        border: 4px solid rgba(255,255,255,0.2);
        border-left-color: #fff;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    /* ---- Right Panel (Clock + Status) ---- */
    .kiosk-right {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 3rem 3.5rem;
        background: #f8f9fb;
        overflow-y: auto;
    }

    .clock-section {
        text-align: center;
        padding: 2.5rem 0 2rem;
    }

    .clock-time {
        font-family: 'JetBrains Mono', monospace;
        font-size: 4.5rem;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.04em;
        line-height: 1;
    }

    .clock-date {
        font-size: 1rem;
        color: #64748b;
        margin-top: 0.6rem;
        font-weight: 500;
    }

    .divider {
        height: 1px;
        background: #e2e8f0;
        margin: 1.5rem 0;
    }

    /* ---- Instruction Card ---- */
    .instruction-card {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: 1.25rem;
        padding: 2rem;
        animation: fadeIn 0.5s ease;
    }

    .rfid-icon-wrapper {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: rgba(var(--primary-rgb), 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulseRing 2.5s infinite ease-in-out;
    }

    .rfid-icon-wrapper ion-icon {
        font-size: 3rem;
        color: var(--primary);
    }

    @keyframes pulseRing {
        0% { box-shadow: 0 0 0 0 rgba(var(--primary-rgb), 0.3); }
        70% { box-shadow: 0 0 0 20px rgba(var(--primary-rgb), 0); }
        100% { box-shadow: 0 0 0 0 rgba(var(--primary-rgb), 0); }
    }

    .instruction-card h3 {
        font-size: 1.5rem;
        font-weight: 700;
        color: #0f172a;
    }

    .instruction-card p {
        color: #64748b;
        font-size: 0.95rem;
        max-width: 320px;
        line-height: 1.6;
    }

    /* ---- Employee Card ---- */
    .employee-card {
        display: none;
        background: #fff;
        border-radius: 1.25rem;
        padding: 2rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .employee-card.error {
        border-color: #fca5a5;
        box-shadow: 0 4px 20px rgba(239, 68, 68, 0.1);
    }

    .employee-card .emp-header {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
    }

    .emp-avatar {
        width: 64px;
        height: 64px;
        border-radius: 1rem;
        background: rgba(var(--primary-rgb), 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1.75rem;
        flex-shrink: 0;
    }

    .emp-meta h2 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.2rem;
    }

    .emp-meta p {
        font-size: 0.85rem;
        color: #64748b;
        font-family: 'JetBrains Mono', monospace;
    }

    .emp-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 1rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        margin-top: 0.75rem;
    }

    .emp-badge.masuk {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .emp-badge.pulang {
        background: rgba(59, 130, 246, 0.1);
        color: #2563eb;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }

    .emp-badge.error {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .emp-detail-rows {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px solid #e2e8f0;
    }

    .emp-detail-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .emp-detail-row ion-icon {
        font-size: 1.15rem;
        color: var(--primary);
        flex-shrink: 0;
    }

    .emp-detail-row .detail-label {
        display: block;
        font-size: 0.7rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }

    .emp-detail-row .detail-value {
        display: block;
        font-size: 0.9rem;
        color: #1e293b;
        font-weight: 600;
    }

    .emp-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 1rem;
    }

    /* ---- Bottom Info ---- */
    .kiosk-footer {
        margin-top: auto;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.8rem;
        color: #94a3b8;
    }

    .kiosk-footer .status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #22c55e;
        margin-right: 0.4rem;
        vertical-align: middle;
    }

    /* ---- Toast Notification ---- */
    .toast-notif {
        position: fixed;
        top: 2rem;
        right: 2rem;
        min-width: 360px;
        padding: 1.25rem 1.5rem;
        border-radius: 1rem;
        display: none;
        z-index: 999;
        animation: slideInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 12px 40px rgba(0,0,0,0.12);
    }

    .toast-notif.success {
        background: #fff;
        border-left: 5px solid #10b981;
        color: #064e3b;
    }

    .toast-notif.error {
        background: #fff;
        border-left: 5px solid #ef4444;
        color: #7f1d1d;
    }

    .toast-notif .toast-body {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .toast-notif .toast-body ion-icon {
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .toast-notif .toast-body .toast-text strong {
        display: block;
        font-size: 0.95rem;
    }

    .toast-notif .toast-body .toast-text span {
        font-size: 0.82rem;
        color: #64748b;
    }

    /* ---- Hidden Input ---- */
    #rfid-input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    /* ---- Face Detection Indicator ---- */
    .face-status {
        position: absolute;
        top: 1rem;
        right: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(8px);
        padding: 0.4rem 0.85rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #fff;
        z-index: 10;
        transition: all 0.3s;
        letter-spacing: 0.03em;
    }

    .face-status .face-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ef4444;
        transition: background 0.3s;
    }

    .face-status.detected .face-dot {
        background: #22c55e;
        box-shadow: 0 0 6px #22c55e;
    }

    .camera-frame.face-ok {
        border-color: #22c55e;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3), 0 0 0 2px rgba(34, 197, 94, 0.3);
    }

    /* ---- Animations ---- */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(40px); }
        to { opacity: 1; transform: translateX(0); }
    }
</style>
@endpush

@section('content')
<input type="text" id="rfid-input" autofocus>

<div class="kiosk-page">
    {{-- ====== LEFT PANEL ====== --}}
    <div class="kiosk-left">
        <div class="brand-area">
            <div class="brand-logo">
                @if (!empty($generalsetting->logo) && Storage::disk('public')->exists('logo/' . $generalsetting->logo))
                    <img src="{{ asset('storage/logo/' . $generalsetting->logo) }}" alt="Logo">
                @else
                    <ion-icon name="business-outline" style="font-size: 1.75rem; color: #fff;"></ion-icon>
                @endif
            </div>
            <div class="brand-text">
                <h2>{{ $generalsetting->nama_perusahaan ?? 'Perusahaan' }}</h2>
                <span>Sistem Presensi Digital</span>
            </div>
        </div>

        <div class="camera-section">
            <div class="camera-frame" id="camera-frame">
                <div class="webcam-capture" id="webcam-capture"></div>
                <canvas id="face-canvas" style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:5;"></canvas>
                <div class="face-status" id="face-status">
                    <span class="face-dot"></span>
                    <span id="face-label">NO FACE</span>
                </div>
                <div class="camera-label">
                    <span class="dot"></span>
                    LIVE CAMERA
                </div>
                <div class="loading-overlay" id="loading-overlay">
                    <div class="spinner"></div>
                </div>
            </div>
            <div class="camera-footer">
                <span>Kamera aktif untuk verifikasi wajah</span>
                <span>Resolusi: 640×480</span>
            </div>
        </div>
    </div>

    {{-- ====== RIGHT PANEL ====== --}}
    <div class="kiosk-right">
        <div class="clock-section">
            <div class="clock-time" id="clock-time">00:00:00</div>
            <div class="clock-date" id="clock-date">Memuat tanggal...</div>
        </div>

        <div class="divider"></div>

        {{-- Instruction --}}
        <div class="instruction-card" id="instruction-card">
            <div class="rfid-icon-wrapper" id="instruction-icon-wrapper">
                <ion-icon name="sync-outline" id="instruction-icon" style="animation: spin 1.5s linear infinite;"></ion-icon>
            </div>
            <h3 id="instruction-title">Memuat Sistem...</h3>
            <p id="instruction-desc">Sedang memuat model pengenalan wajah, harap tunggu sebentar...</p>
            <div id="face-load-info" style="width:100%;max-width:300px;text-align:center;">
                <div style="background:#e2e8f0;border-radius:4px;height:6px;overflow:hidden;">
                    <div id="face-load-bar" style="height:100%;width:0%;background:var(--primary);border-radius:4px;transition:width 0.4s ease;"></div>
                </div>
                <small class="text-muted mt-2 d-block" id="face-load-status">Inisialisasi...</small>
            </div>
        </div>

        {{-- Employee Info (hidden by default) --}}
        <div class="employee-card" id="employee-card">
            <div class="emp-header">
                <div class="emp-avatar" id="emp-avatar">
                    <ion-icon name="person"></ion-icon>
                </div>
                <div class="emp-meta">
                    <h2 id="emp-name">-</h2>
                    <p id="emp-nik" style="font-family: 'JetBrains Mono', monospace; color: #64748b; font-size: 0.85rem;">-</p>
                </div>
            </div>
            <div class="emp-detail-rows">
                <div class="emp-detail-row">
                    <ion-icon name="briefcase-outline"></ion-icon>
                    <div>
                        <span class="detail-label">Jabatan</span>
                        <span class="detail-value" id="emp-jabatan">-</span>
                    </div>
                </div>
                <div class="emp-detail-row">
                    <ion-icon name="people-outline"></ion-icon>
                    <div>
                        <span class="detail-label">Departemen</span>
                        <span class="detail-value" id="emp-dept">-</span>
                    </div>
                </div>
                <div class="emp-detail-row">
                    <ion-icon name="time-outline"></ion-icon>
                    <div>
                        <span class="detail-label">Jadwal Kerja</span>
                        <span class="detail-value" id="emp-jadwal">-</span>
                    </div>
                </div>
            </div>
            <div id="emp-badge-wrapper" style="margin-top: 1rem;">
                <span class="emp-badge masuk" id="emp-status">
                    <ion-icon name="log-in-outline"></ion-icon>
                    ABSEN MASUK
                </span>
            </div>
        </div>

        <div class="kiosk-footer">
            <span><span class="status-dot"></span> Sistem Online</span>
            <span id="fr-status-label" style="font-size:0.75rem; color:#94a3b8;">
                <ion-icon name="scan-circle-outline" style="vertical-align:middle;"></ion-icon>
                Face Recognition
            </span>
            <span>v2.1</span>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="toast-notif success" id="toast-success">
    <div class="toast-body">
        <ion-icon name="checkmark-circle" style="color: #10b981;"></ion-icon>
        <div class="toast-text">
            <strong>Presensi Berhasil</strong>
            <span id="toast-msg">Kehadiran sudah dicatat</span>
        </div>
    </div>
</div>

<div class="toast-notif error" id="toast-error">
    <div class="toast-body">
        <ion-icon name="close-circle" style="color: #ef4444;"></ion-icon>
        <div class="toast-text">
            <strong>Gagal</strong>
            <span id="toast-err-msg">Terjadi kesalahan</span>
        </div>
    </div>
</div>

<audio id="audio-success">
    <source src="{{ asset('assets/sound/absenmasuk.wav') }}" type="audio/wav">
</audio>
<audio id="audio-error">
    <source src="{{ asset('assets/sound/radius.mp3') }}" type="audio/mpeg">
</audio>
@endsection

@push('myscript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
<script src="{{ asset('assets/vendor/face-api.min.js') }}"></script>
<script>
(function() {
    'use strict';

    // ── Config ──────────────────────────────────────────────────────────────
    const MODEL_URL     = '{{ asset("models") }}';
    const CSRF_TOKEN    = '{{ csrf_token() }}';
    const CHECK_NIK_URL = '{{ route("public.presensi.check-nik") }}';
    const CHECK_RFID_URL= '{{ route("public.presensi.check-rfid") }}';
    const STORE_URL     = '{{ route("public.presensi.store") }}';
    const GETALLWAJAH_URL = '/facerecognition/getallwajah';

    // Face recognition threshold – lower = stricter (0.45 recommended)
    const MATCH_THRESHOLD    = 0.45;
    // How many consecutive frames must match before triggering presensi
    const CONFIRM_FRAMES     = 2;
    // Interval between recognition checks (ms)
    const RECOG_INTERVAL_MS  = 1200;
    // Cooldown after a successful presensi (ms) – prevents double-fire
    const COOLDOWN_MS        = 8000;

    // ── State ────────────────────────────────────────────────────────────────
    let faceMatcher     = null;
    let modelsReady     = false;
    let isProcessing    = false;
    let lastMatchNik    = null;
    let matchFrameCount = 0;
    let lastSuccessTime = 0;

    // ── Clock ────────────────────────────────────────────────────────────────
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2,'0');
        const m = String(now.getMinutes()).padStart(2,'0');
        const s = String(now.getSeconds()).padStart(2,'0');
        document.getElementById('clock-time').textContent = `${h}:${m}:${s}`;
        const opts = { weekday:'long', day:'numeric', month:'long', year:'numeric' };
        document.getElementById('clock-date').textContent = now.toLocaleDateString('id-ID', opts);
    }
    setInterval(updateClock, 1000);
    updateClock();

    // ── Webcam ───────────────────────────────────────────────────────────────
    Webcam.set({ width:640, height:480, image_format:'jpeg', jpeg_quality:90 });
    Webcam.attach('.webcam-capture');

    // ── UI Helpers ───────────────────────────────────────────────────────────
    const audioSuccess = document.getElementById('audio-success');
    const audioError   = document.getElementById('audio-error');

    function setLoadProgress(pct, label) {
        document.getElementById('face-load-bar').style.width = pct + '%';
        document.getElementById('face-load-status').textContent = label;
    }

    function setInstructionReady() {
        const wrapper = document.getElementById('instruction-icon-wrapper');
        wrapper.innerHTML = '<ion-icon name="scan-circle-outline" style="font-size:3rem;color:var(--primary);animation:pulseRing 2.5s infinite ease-in-out;"></ion-icon>';
        document.getElementById('instruction-title').textContent = 'Arahkan Wajah ke Kamera';
        document.getElementById('instruction-desc').textContent = 'Sistem akan mengenali wajah Anda secara otomatis dan mencatat kehadiran.';
        document.getElementById('face-load-info').style.display = 'none';
    }

    function setInstructionFallback() {
        const wrapper = document.getElementById('instruction-icon-wrapper');
        wrapper.innerHTML = '<ion-icon name="card-outline" style="font-size:3rem;color:var(--primary);"></ion-icon>';
        document.getElementById('instruction-title').textContent = 'Tap Kartu RFID';
        document.getElementById('instruction-desc').textContent = 'Data wajah belum tersedia. Gunakan kartu RFID untuk presensi.';
        document.getElementById('face-load-info').style.display = 'none';
    }

    function showLoadingOverlay(show) {
        const el = document.getElementById('loading-overlay');
        if (show) el.classList.add('active');
        else el.classList.remove('active');
    }

    function showToast(type, msg) {
        if (type === 'success') {
            document.getElementById('toast-msg').textContent = msg;
            $('#toast-success').fadeIn(300);
            setTimeout(() => $('#toast-success').fadeOut(300), 4500);
        } else {
            document.getElementById('toast-err-msg').textContent = msg;
            $('#toast-error').fadeIn(300);
            setTimeout(() => $('#toast-error').fadeOut(300), 3500);
        }
    }

    function showError(msg) {
        showLoadingOverlay(false);
        audioError.currentTime = 0; audioError.play().catch(()=>{});
        if ($('#employee-card').is(':visible')) {
            $('#employee-card').addClass('error');
            $('#emp-status').removeClass('masuk pulang').addClass('error')
                .html('<ion-icon name="close-circle-outline"></ion-icon> GAGAL');
        }
        showToast('error', msg);
        setTimeout(resetKiosk, 3000);
    }

    function resetKiosk() {
        showLoadingOverlay(false);
        $('#employee-card').removeClass('error').fadeOut(200, function() {
            $('#instruction-card').fadeIn(300);
            ['emp-name','emp-nik','emp-jabatan','emp-dept','emp-jadwal'].forEach(id => document.getElementById(id).textContent = '-');
            document.getElementById('emp-avatar').innerHTML = '<ion-icon name="person"></ion-icon>';
            $('#emp-status').removeClass('error pulang').addClass('masuk')
                .html('<ion-icon name="log-in-outline"></ion-icon> ABSEN MASUK');
        });
        rfidBuffer  = '';
        isProcessing = false;
        matchFrameCount = 0;
        lastMatchNik    = null;
    }

    function showEmployeeCard(res) {
        $('#emp-name').text(res.nama);
        $('#emp-nik').text(res.nik);
        $('#emp-jabatan').text(res.jabatan);
        $('#emp-dept').text(res.departemen);
        $('#emp-jadwal').text(res.jam_kerja);

        if (res.foto) {
            $('#emp-avatar').html('<img src="' + res.foto + '" alt="Foto">');
        } else {
            $('#emp-avatar').html('<ion-icon name="person"></ion-icon>');
        }

        const badge = $('#emp-status');
        if (res.type === 'in') {
            badge.removeClass('pulang error').addClass('masuk')
                 .html('<ion-icon name="log-in-outline"></ion-icon> ABSEN MASUK');
        } else {
            badge.removeClass('masuk error').addClass('pulang')
                 .html('<ion-icon name="log-out-outline"></ion-icon> ABSEN PULANG');
        }

        $('#instruction-card').fadeOut(200, () => $('#employee-card').fadeIn(300));
    }

    // ── Load face-api Models ─────────────────────────────────────────────────
    async function loadModels() {
        setLoadProgress(5, 'Memuat model SSD MobileNet...');
        try {
            await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
            setLoadProgress(35, 'Memuat model landmark wajah...');
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            setLoadProgress(65, 'Memuat model pengenalan wajah...');
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            setLoadProgress(85, 'Mengambil data wajah karyawan...');
            return true;
        } catch (err) {
            console.error('Model load failed:', err);
            return false;
        }
    }

    // ── Build FaceMatcher from all employee face images ──────────────────────
    async function buildFaceMatcher() {
        let employees;
        try {
            const res = await fetch(GETALLWAJAH_URL + '?t=' + Date.now());
            employees = await res.json();
        } catch (err) {
            console.error('Failed to fetch face data:', err);
            return null;
        }

        if (!Array.isArray(employees) || employees.length === 0) return null;

        const labeled = [];
        let processed = 0;
        const total = employees.filter(e => e.wajah_data && e.wajah_data.length > 0).length;

        for (const emp of employees) {
            if (!emp.wajah_data || emp.wajah_data.length === 0) continue;

            const descriptors = [];
            const folderName = emp.nik + '-' + emp.nama_karyawan.trim().split(/\s+/)[0].toLowerCase();
            const folderEnc  = encodeURIComponent(folderName);

            for (const face of emp.wajah_data) {
                const imgUrl = `/storage/uploads/facerecognition/${folderEnc}/${encodeURIComponent(face.wajah)}`;
                try {
                    const img = await faceapi.fetchImage(imgUrl);
                    const det = await faceapi.detectSingleFace(img, new faceapi.SsdMobilenetv1Options({ minConfidence: 0.4 }))
                        .withFaceLandmarks()
                        .withFaceDescriptor();
                    if (det) descriptors.push(det.descriptor);
                } catch (e) {
                    // skip unprocessable image
                }
            }

            if (descriptors.length > 0) {
                labeled.push(new faceapi.LabeledFaceDescriptors(emp.nik, descriptors));
            }

            processed++;
            const pct = 85 + Math.round((processed / total) * 14);
            setLoadProgress(pct, `Memproses wajah: ${processed}/${total} karyawan...`);
        }

        if (labeled.length === 0) return null;
        return new faceapi.FaceMatcher(labeled, MATCH_THRESHOLD);
    }

    // ── Recognition Loop ─────────────────────────────────────────────────────
    function startRecognitionLoop() {
        const video = document.querySelector('.webcam-capture video');
        if (!video) { setTimeout(startRecognitionLoop, 600); return; }

        const canvas = document.getElementById('face-canvas');

        setInterval(async () => {
            if (isProcessing || !video || video.readyState < 2 || !faceMatcher) return;

            try {
                const options = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 });
                const detections = await faceapi.detectAllFaces(video, options)
                    .withFaceLandmarks()
                    .withFaceDescriptors();

                // Draw bounding boxes
                const displaySize = { width: video.videoWidth, height: video.videoHeight };
                faceapi.matchDimensions(canvas, displaySize);
                const resized = faceapi.resizeResults(detections, displaySize);
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);

                let bestMatch = null;
                let bestDistance = 1;

                resized.forEach(det => {
                    const match = faceMatcher.findBestMatch(det.descriptor);
                    const distance = match.distance;
                    const isKnown = match.label !== 'unknown';

                    // Draw box
                    const box = det.detection.box;
                    ctx.strokeStyle = isKnown ? '#22c55e' : '#ef4444';
                    ctx.lineWidth = 2;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);

                    if (isKnown) {
                        ctx.fillStyle = 'rgba(34,197,94,0.8)';
                        ctx.fillRect(box.x, box.y - 22, box.width, 22);
                        ctx.fillStyle = '#fff';
                        ctx.font = '12px monospace';
                        ctx.fillText(match.label + ' (' + (100 - Math.round(distance * 100)) + '%)', box.x + 4, box.y - 6);
                    }

                    if (isKnown && distance < bestDistance) {
                        bestDistance = distance;
                        bestMatch = match.label; // NIK
                    }
                });

                // Update face indicator
                if (detections.length > 0) {
                    document.getElementById('face-status').classList.add('detected');
                    document.getElementById('face-label').textContent = bestMatch ? 'WAJAH DIKENALI' : 'WAJAH TERDETEKSI';
                    document.getElementById('camera-frame').classList.add('face-ok');
                } else {
                    document.getElementById('face-status').classList.remove('detected');
                    document.getElementById('face-label').textContent = 'NO FACE';
                    document.getElementById('camera-frame').classList.remove('face-ok');
                    matchFrameCount = 0;
                    lastMatchNik = null;
                }

                // Confirm match across consecutive frames
                if (bestMatch) {
                    if (bestMatch === lastMatchNik) {
                        matchFrameCount++;
                    } else {
                        lastMatchNik    = bestMatch;
                        matchFrameCount = 1;
                    }

                    if (matchFrameCount >= CONFIRM_FRAMES) {
                        const now = Date.now();
                        if (now - lastSuccessTime < COOLDOWN_MS) return; // still in cooldown
                        triggerFacePresensi(bestMatch);
                    }
                } else {
                    lastMatchNik    = null;
                    matchFrameCount = 0;
                }

            } catch (err) {
                // silent fail in detection loop
            }
        }, RECOG_INTERVAL_MS);
    }

    // ── Trigger presensi via Face Recognition ───────────────────────────────
    function triggerFacePresensi(nik) {
        if (isProcessing) return;
        isProcessing = true;
        matchFrameCount = 0;

        // 1. Check employee info by NIK
        $.ajax({
            type: 'POST',
            url: CHECK_NIK_URL,
            data: { _token: CSRF_TOKEN, nik: nik },
            success: function(res) {
                if (res.status === 'success') {
                    showEmployeeCard(res);
                    showLoadingOverlay(true);
                    // 2. Short delay then snap + store
                    setTimeout(() => takeFaceSnapshot(nik), 1200);
                } else {
                    showError(res.message);
                }
            },
            error: function() { showError('Gagal terhubung ke server'); }
        });
    }

    function takeFaceSnapshot(nik) {
        Webcam.snap(function(dataUri) {
            $.ajax({
                type: 'POST',
                url: STORE_URL,
                data: { _token: CSRF_TOKEN, nik: nik, image: dataUri },
                success: function(res) {
                    showLoadingOverlay(false);
                    if (res.status === 'success') {
                        lastSuccessTime = Date.now();
                        audioSuccess.currentTime = 0; audioSuccess.play().catch(()=>{});
                        showToast('success', res.message);
                        setTimeout(resetKiosk, 4500);
                    } else {
                        showError(res.message);
                    }
                },
                error: function() { showError('Gagal menyimpan data presensi'); }
            });
        });
    }

    // ── RFID Fallback ────────────────────────────────────────────────────────
    let rfidBuffer  = '';
    let rfidTimeout = null;

    document.addEventListener('click', () => document.getElementById('rfid-input').focus());
    setInterval(() => {
        if (document.activeElement !== document.getElementById('rfid-input'))
            document.getElementById('rfid-input').focus();
    }, 1500);

    window.addEventListener('keydown', function(e) {
        if (isProcessing) return;
        if (e.key === 'Enter') {
            clearTimeout(rfidTimeout);
            if (rfidBuffer.length > 3) processRfid(rfidBuffer);
            rfidBuffer = '';
            document.getElementById('rfid-input').value = '';
            return;
        }
        if (e.key.length === 1 && /[a-zA-Z0-9]/.test(e.key)) {
            rfidBuffer += e.key;
            clearTimeout(rfidTimeout);
            rfidTimeout = setTimeout(() => { rfidBuffer = ''; }, 300);
        }
    });

    function processRfid(uid) {
        if (isProcessing) return;
        isProcessing = true;
        showLoadingOverlay(true);
        $('#instruction-card').fadeOut(200, () => $('#employee-card').fadeIn(300));

        $.ajax({
            type: 'POST',
            url: CHECK_RFID_URL,
            data: { _token: CSRF_TOKEN, rfid_uid: uid },
            success: function(res) {
                if (res.status === 'success') {
                    showEmployeeCard(res);
                    setTimeout(() => takeRfidSnapshot(uid), 1200);
                } else {
                    showError(res.message);
                }
            },
            error: function() { showError('Gagal terhubung ke server'); }
        });
    }

    function takeRfidSnapshot(uid) {
        Webcam.snap(function(dataUri) {
            $.ajax({
                type: 'POST',
                url: STORE_URL,
                data: { _token: CSRF_TOKEN, rfid_uid: uid, image: dataUri },
                success: function(res) {
                    showLoadingOverlay(false);
                    if (res.status === 'success') {
                        audioSuccess.currentTime = 0; audioSuccess.play().catch(()=>{});
                        showToast('success', res.message);
                        setTimeout(resetKiosk, 4000);
                    } else {
                        showError(res.message);
                    }
                },
                error: function() { showError('Gagal menyimpan data presensi'); }
            });
        });
    }

    // ── Boot Sequence ────────────────────────────────────────────────────────
    async function boot() {
        // Wait for webcam to be ready
        await new Promise(r => setTimeout(r, 2000));

        const modelOk = await loadModels();
        if (!modelOk) {
            setLoadProgress(100, 'Gagal memuat model');
            setInstructionFallback();
            return;
        }

        const matcher = await buildFaceMatcher();
        setLoadProgress(100, 'Siap!');

        if (!matcher) {
            console.warn('No face data found – RFID fallback only');
            setInstructionFallback();
            document.getElementById('fr-status-label').innerHTML =
                '<ion-icon name="card-outline" style="vertical-align:middle;"></ion-icon> Mode RFID';
        } else {
            faceMatcher = matcher;
            modelsReady = true;
            setInstructionReady();
            document.getElementById('fr-status-label').innerHTML =
                '<ion-icon name="scan-circle-outline" style="vertical-align:middle;"></ion-icon> Face Recognition Aktif';
            startRecognitionLoop();
        }
    }

    boot();
})();
</script>
@endpush

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Scan QR Code - {{ $karyawan->nama_karyawan }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Sistem Presensi QR Code" name="description" />
    <meta content="Coderthemes" name="author" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon/favicon.ico') }}">

    <!-- App css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="light-style" />
    <link href="{{ asset('assets/css/app-dark.min.css') }}" rel="stylesheet" type="text/css" id="dark-style" />

    <!-- QR Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <!-- Face API -->
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .scan-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .employee-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .employee-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 15px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }

        .employee-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .employee-nik {
            font-size: 16px;
            opacity: 0.9;
        }

        .time-display {
            font-size: 48px;
            font-weight: bold;
            color: #333;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
        }

        .date-display {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }

        .scan-section {
            margin: 30px 0;
        }

        .qr-reader {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            border-radius: 15px;
            overflow: hidden;
        }

        .manual-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .btn-absen {
            flex: 1;
            min-width: 150px;
            padding: 20px;
            border: none;
            border-radius: 15px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }

        .btn-masuk {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .btn-pulang {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            color: white;
        }

        .btn-absen:hover {
            opacity: 0.8;
        }

        .btn-absen:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .camera-container {
            margin: 20px 0;
            border-radius: 15px;
            overflow: hidden;
            display: none;
        }

        #video {
            width: 100%;
            max-width: 600px;
            border-radius: 15px;
            transform: scaleX(-1); /* Mirror — tampilan seperti cermin */
        }

        .canvas-container {
            margin: 20px 0;
            display: none;
        }

        #canvas {
            width: 100%;
            max-width: 600px;
            height: auto;
            border-radius: 15px;
        }

        .status-message {
            margin: 20px 0;
            padding: 15px;
            border-radius: 10px;
            font-weight: bold;
            display: none;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .loading {
            display: none;
            margin: 20px 0;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Remove unnecessary animations */
        .btn-absen {
            transition: opacity 0.2s;
        }

        .back-button {
            transition: background 0.2s;
        }

        .scan-instructions {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            color: #666;
        }

        .scan-instructions h5 {
            color: #333;
            margin-bottom: 10px;
        }

        .scan-instructions ul {
            text-align: left;
            margin: 0;
            padding-left: 20px;
        }

        .scan-instructions li {
            margin-bottom: 5px;
        }

        #qr-reader {
            border: 2px solid #ddd;
            border-radius: 15px;
        }

        /* Force camera to be active */
        #qr-reader video {
            width: 100% !important;
            height: auto !important;
        }

        /* Hide unnecessary elements */
        #qr-reader__scan_region {
            display: none !important;
        }

        #qr-reader__scan_region>img {
            display: none !important;
        }

        /* Ensure QR scanner is always active */
        #qr-reader__status_span {
            display: none !important;
        }

        #qr-reader__camera_selection {
            margin-bottom: 10px;
        }

        #qr-reader__dashboard_section {
            margin-bottom: 10px;
        }

        .qr-reader-container {
            position: relative;
        }

        .scan-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 250px;
            height: 250px;
            border: 3px solid #667eea;
            border-radius: 10px;
            pointer-events: none;
            z-index: 10;
        }

        /* Face Verification */
        .face-verify-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.85);
            z-index: 9999;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        .face-verify-overlay.active { display: flex; }
        .face-verify-video-wrap {
            position: relative;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid rgba(255,255,255,0.3);
            margin-bottom: 20px;
        }
        .face-verify-video-wrap video {
            width: 100%; height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }
        .face-verify-video-wrap.detecting { border-color: #fbbf24; }
        .face-verify-video-wrap.matched   { border-color: #22c55e; box-shadow: 0 0 30px rgba(34,197,94,0.6); }
        .face-verify-video-wrap.failed    { border-color: #ef4444; box-shadow: 0 0 30px rgba(239,68,68,0.5); }
        .face-verify-status {
            font-size: 16px; font-weight: 600; margin-bottom: 8px;
        }
        .face-verify-sub {
            font-size: 13px; color: rgba(255,255,255,0.6); margin-bottom: 20px;
        }
        .face-verify-progress {
            width: 200px; height: 6px;
            background: rgba(255,255,255,0.2);
            border-radius: 3px; overflow: hidden; margin-bottom: 20px;
        }
        .face-verify-progress-fill {
            height: 100%; width: 0%;
            background: #667eea;
            transition: width 0.3s;
        }
        .face-verify-cancel {
            background: rgba(255,255,255,0.15);
            border: none; color: #fff;
            padding: 10px 24px; border-radius: 8px;
            cursor: pointer; font-size: 14px;
        }
    </style>
</head>

<body>
    <button class="back-button" id="btnBack">
        <i class="ti ti-arrow-left"></i> Kembali
    </button>

    <div class="scan-container">
        <div class="employee-card">
            <div class="employee-avatar">
                <i class="ti ti-user"></i>
            </div>
            <div class="employee-name">{{ $karyawan->nama_karyawan }}</div>
            <div class="employee-nik">NIK: {{ $karyawan->nik }}</div>
        </div>

        <div class="time-display" id="timeDisplay"></div>
        <div class="date-display" id="dateDisplay"></div>

        <div class="scan-section">
            <h4>Scan QR Code untuk Absen</h4>

            <div class="scan-instructions">
                <h5>Cara menggunakan:</h5>
                <ul>
                    <li>Arahkan kamera ke QR Code</li>
                    <li>Pastikan QR Code berada dalam kotak scan</li>
                    <li>Tunggu hingga QR Code terdeteksi otomatis</li>
                    <li>Atau gunakan tombol manual di bawah</li>
                </ul>
            </div>

            <div class="qr-reader-container">
                <div id="qr-reader" class="qr-reader"></div>
                <div class="scan-overlay"></div>
            </div>
        </div>

        <div class="manual-buttons">
            <button class="btn-absen btn-masuk" id="btnAbsenMasuk">
                <i class="ti ti-login me-2"></i>Absen Masuk Manual
            </button>
            <button class="btn-absen btn-pulang" id="btnAbsenPulang">
                <i class="ti ti-logout me-2"></i>Absen Pulang Manual
            </button>
        </div>

        <div class="camera-container" id="cameraContainer">
            <video id="video" autoplay></video>
        </div>

        <div class="canvas-container" id="canvasContainer">
            <canvas id="canvas"></canvas>
        </div>

        <div class="loading" id="loading">
            <div class="spinner"></div>
            <p>Memproses absen...</p>
        </div>

        <div class="status-message" id="statusMessage"></div>
    </div>

    <!-- Face Verification Overlay -->
    <div class="face-verify-overlay" id="faceVerifyOverlay">
        <p style="font-size:13px;color:rgba(255,255,255,0.5);margin-bottom:12px;text-transform:uppercase;letter-spacing:1px;">Verifikasi Wajah</p>
        <div class="face-verify-video-wrap" id="faceVerifyWrap">
            <video id="faceVerifyVideo" autoplay playsinline muted></video>
        </div>
        <div class="face-verify-status" id="faceVerifyStatus">Memuat model AI...</div>
        <div class="face-verify-sub" id="faceVerifySub">Mohon tunggu sebentar</div>
        <div class="face-verify-progress">
            <div class="face-verify-progress-fill" id="faceVerifyProgress"></div>
        </div>
        <button class="face-verify-cancel" id="btnCancelFaceVerify">Batalkan</button>
    </div>

    <!-- Vendor js -->
    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.min.js') }}"></script>

    <script>
        let stream = null;
        let currentStatus = null;
        let html5QrcodeScanner = null;
        const karyawan = @json($karyawan);

        // ── Face API state ──
        let faceModelsLoaded = false;
        let faceVerifyStream = null;
        let faceVerifyAborted = false;
        const FACE_MATCH_THRESHOLD = 0.6; // jarak euclidean, default face-api 0.6
        const FACE_API_MODELS_URL = '{{ asset("models") }}';
        const FACE_IMAGES_URL = '{{ route("facerecognition.face-images", $karyawan->nik) }}';

        // ── Event Listeners ──
        document.addEventListener('DOMContentLoaded', () => {
            const btnBack = document.getElementById('btnBack');
            if (btnBack) btnBack.addEventListener('click', () => window.location.href='{{ route('facerecognition-presensi.index') }}');
            
            const btnAbsenMasuk = document.getElementById('btnAbsenMasuk');
            if (btnAbsenMasuk) btnAbsenMasuk.addEventListener('click', () => manualAbsen(1));
            
            const btnAbsenPulang = document.getElementById('btnAbsenPulang');
            if (btnAbsenPulang) btnAbsenPulang.addEventListener('click', () => manualAbsen(0));
            
            const btnCancelFaceVerify = document.getElementById('btnCancelFaceVerify');
            if (btnCancelFaceVerify) btnCancelFaceVerify.addEventListener('click', () => cancelFaceVerify());
            
            initQRScanner();
        });

        // ── Deteksi masker: cek apakah area mulut/hidung terhalang ──
        // Landmark 27-30: hidung bridge→tip, 48-67: area mulut
        // Jika spread vertikal mulut < 3% tinggi wajah → kemungkinan pakai masker
        function isMaskDetected(landmarks, detection) {
            try {
                const pts = landmarks.positions; // array of {x, y}
                const box = detection.box;

                // Ambil titik mulut (48–67)
                const mouthPts = pts.slice(48, 68);
                const mouthYMin = Math.min(...mouthPts.map(p => p.y));
                const mouthYMax = Math.max(...mouthPts.map(p => p.y));
                const mouthSpread = mouthYMax - mouthYMin;

                // Ambil nose tip (landmark 30) dan chin (landmark 8)
                const noseTip = pts[30];
                const chin    = pts[8];
                const noseToChin = chin.y - noseTip.y;

                // Jika spread mulut < 15% jarak hidung-dagu → terhalang
                if (noseToChin > 0 && (mouthSpread / noseToChin) < 0.15) {
                    return true;
                }

                // Jika nose tip lebih rendah dari rata-rata mulut → hidung tertutup masker
                const mouthAvgY = mouthPts.reduce((s, p) => s + p.y, 0) / mouthPts.length;
                if (noseTip.y > mouthAvgY - 5) {
                    return true;
                }

                return false;
            } catch (e) {
                return false;
            }
        }

        // ── Load face-api models di background saat halaman dimuat ──
        async function loadFaceModels() {
            try {
                await Promise.all([
                    faceapi.nets.tinyFaceDetector.loadFromUri(FACE_API_MODELS_URL),
                    faceapi.nets.faceLandmark68Net.loadFromUri(FACE_API_MODELS_URL),
                    faceapi.nets.faceRecognitionNet.loadFromUri(FACE_API_MODELS_URL),
                ]);
                faceModelsLoaded = true;
                console.log('Face API models loaded');
            } catch (e) {
                console.error('Gagal load face models:', e);
            }
        }

        // ── Ambil descriptor dari array URL foto referensi ──
        async function loadReferenceDescriptors(imageUrls) {
            const descriptors = [];
            for (const url of imageUrls) {
                try {
                    const img = await faceapi.fetchImage(url);
                    const detection = await faceapi
                        .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }))
                        .withFaceLandmarks()
                        .withFaceDescriptor();
                    if (detection) descriptors.push(detection.descriptor);
                } catch (e) {
                    console.warn('Skip referensi:', url, e.message);
                }
            }
            return descriptors;
        }

        // ── Main: verifikasi wajah sebelum absen ──
        async function verifyFaceThenAbsen(status) {
            faceVerifyAborted = false;
            currentStatus = status;

            // Tampilkan overlay
            const overlay   = document.getElementById('faceVerifyOverlay');
            const wrap      = document.getElementById('faceVerifyWrap');
            const statusEl  = document.getElementById('faceVerifyStatus');
            const subEl     = document.getElementById('faceVerifySub');
            const progressEl = document.getElementById('faceVerifyProgress');

            overlay.classList.add('active');
            wrap.className = 'face-verify-video-wrap detecting';
            statusEl.textContent = 'Memuat model AI...';
            subEl.textContent = 'Mohon tunggu sebentar';
            progressEl.style.width = '0%';

            // 1. Pastikan model sudah load
            if (!faceModelsLoaded) {
                statusEl.textContent = 'Memuat model AI...';
                await loadFaceModels();
                if (!faceModelsLoaded) {
                    statusEl.textContent = 'Gagal memuat model AI';
                    subEl.textContent = 'Silakan refresh halaman';
                    return;
                }
            }
            progressEl.style.width = '20%';
            if (faceVerifyAborted) return;

            // 2. Ambil foto referensi dari server
            statusEl.textContent = 'Mengambil data wajah...';
            subEl.textContent = 'Menghubungi server';
            let referenceUrls = [];
            try {
                const res = await fetch(FACE_IMAGES_URL);
                const json = await res.json();
                if (!json.success || !json.images.length) {
                    overlay.classList.remove('active');
                    showStatus('Data wajah belum terdaftar. Daftarkan wajah terlebih dahulu.', 'error');
                    enableButtons();
                    return;
                }
                referenceUrls = json.images;
            } catch (e) {
                overlay.classList.remove('active');
                showStatus('Gagal mengambil data wajah referensi', 'error');
                enableButtons();
                return;
            }
            progressEl.style.width = '40%';
            if (faceVerifyAborted) return;

            // 3. Ekstrak descriptors dari referensi
            statusEl.textContent = 'Memproses wajah referensi...';
            subEl.textContent = referenceUrls.length + ' foto referensi';
            const refDescriptors = await loadReferenceDescriptors(referenceUrls);
            if (!refDescriptors.length) {
                overlay.classList.remove('active');
                showStatus('Wajah tidak terdeteksi pada foto referensi. Daftar ulang wajah Anda.', 'error');
                enableButtons();
                return;
            }
            const faceMatcher = new faceapi.FaceMatcher(
                refDescriptors.map(d => new faceapi.LabeledFaceDescriptors('ref', [d])),
                FACE_MATCH_THRESHOLD
            );
            progressEl.style.width = '60%';
            if (faceVerifyAborted) return;

            // 4. Buka kamera untuk verifikasi live
            statusEl.textContent = 'Posisikan wajah Anda';
            subEl.textContent = 'Pastikan wajah terlihat jelas';
            try {
                faceVerifyStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } }
                });
                document.getElementById('faceVerifyVideo').srcObject = faceVerifyStream;
            } catch (e) {
                overlay.classList.remove('active');
                showStatus('Gagal akses kamera untuk verifikasi', 'error');
                enableButtons();
                return;
            }

            progressEl.style.width = '70%';

            // 5. Loop deteksi: coba hingga 20 frame (10 detik) sebelum menyerah
            const videoEl = document.getElementById('faceVerifyVideo');
            let matched = false;
            let attempts = 0;
            const MAX_ATTEMPTS = 20;

            while (!faceVerifyAborted && attempts < MAX_ATTEMPTS) {
                attempts++;
                progressEl.style.width = (70 + Math.round((attempts / MAX_ATTEMPTS) * 25)) + '%';

                // Tunggu video siap
                if (videoEl.readyState < 2) {
                    await new Promise(r => setTimeout(r, 500));
                    continue;
                }

                const detection = await faceapi
                    .detectSingleFace(videoEl, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                if (!detection) {
                    statusEl.textContent = 'Wajah tidak terdeteksi...';
                    subEl.textContent = 'Hadapkan wajah ke kamera';
                    wrap.className = 'face-verify-video-wrap detecting';
                    await new Promise(r => setTimeout(r, 500));
                    continue;
                }

                // Cek masker sebelum pencocokan
                if (isMaskDetected(detection.landmarks, detection.detection)) {
                    statusEl.textContent = '⚠ Masker terdeteksi!';
                    subEl.textContent = 'Harap lepaskan masker / helm terlebih dahulu';
                    wrap.className = 'face-verify-video-wrap failed';
                    await new Promise(r => setTimeout(r, 1000));
                    continue;
                }

                statusEl.textContent = 'Wajah terdeteksi, mencocokkan...';
                subEl.textContent = 'Harap diam sebentar';
                wrap.className = 'face-verify-video-wrap detecting';

                const bestMatch = faceMatcher.findBestMatch(detection.descriptor);
                console.log('Face match distance:', bestMatch.distance, 'label:', bestMatch.label);

                if (bestMatch.label !== 'unknown') {
                    matched = true;
                    break;
                } else {
                    // Bedakan: jarak sangat jauh → kemungkinan masker tipis/buff
                    if (bestMatch.distance > 0.65) {
                        statusEl.textContent = '⚠ Wajah terhalang?';
                        subEl.textContent = 'Harap lepaskan masker / helm';
                    } else {
                        statusEl.textContent = '✗ Wajah tidak dikenali';
                        subEl.textContent = 'Pastikan pencahayaan cukup & wajah jelas';
                    }
                    wrap.className = 'face-verify-video-wrap failed';
                }
                await new Promise(r => setTimeout(r, 500));
            }

            // 6. Hentikan stream verifikasi
            if (faceVerifyStream) {
                faceVerifyStream.getTracks().forEach(t => t.stop());
                faceVerifyStream = null;
            }
            if (faceVerifyAborted) return;

            progressEl.style.width = '100%';

            if (matched) {
                wrap.className = 'face-verify-video-wrap matched';
                statusEl.textContent = '✓ Wajah Terverifikasi!';
                subEl.textContent = 'Memproses absen...';
                await new Promise(r => setTimeout(r, 1000));
                overlay.classList.remove('active');
                // Lanjut ambil foto & kirim presensi
                startCamera();
            } else {
                wrap.className = 'face-verify-video-wrap failed';
                statusEl.textContent = '✗ Verifikasi Gagal';
                subEl.textContent = 'Pastikan wajah tidak terhalang masker / helm, lalu coba lagi';
                await new Promise(r => setTimeout(r, 2500));
                overlay.classList.remove('active');
                showStatus('Verifikasi wajah gagal. Pastikan wajah tidak terhalang masker atau helm.', 'error');
                enableButtons();
            }
        }

        function cancelFaceVerify() {
            faceVerifyAborted = true;
            if (faceVerifyStream) {
                faceVerifyStream.getTracks().forEach(t => t.stop());
                faceVerifyStream = null;
            }
            document.getElementById('faceVerifyOverlay').classList.remove('active');
            enableButtons();
        }

        // Update waktu real-time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            const dateString = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            document.getElementById('timeDisplay').textContent = timeString;
            document.getElementById('dateDisplay').textContent = dateString;
        }

        // Update waktu setiap detik
        setInterval(updateTime, 1000);
        updateTime();

        // Initialize QR Scanner
        function initQRScanner() {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "qr-reader", {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0,
                    showTorchButtonIfSupported: true,
                    showZoomSliderIfSupported: true,
                    rememberLastUsedCamera: true,
                    supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
                },
                false
            );
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }

        // QR Scan Success
        function onScanSuccess(decodedText, decodedResult) {
            if (html5QrcodeScanner) html5QrcodeScanner.clear();

            try {
                const url = new URL(decodedText);
                const pathParts = url.pathname.split('/');
                const scannedNik = pathParts[pathParts.length - 1];

                if (scannedNik === karyawan.nik) {
                    showStatus('QR Code terdeteksi! Memulai verifikasi wajah...', 'success');
                    const currentHour = new Date().getHours();
                    const status = currentHour < 12 ? 1 : 0;
                    setTimeout(() => { startAbsenProcess(status); }, 800);
                } else {
                    showStatus('QR Code tidak valid untuk karyawan ini', 'error');
                    setTimeout(() => { initQRScanner(); }, 2000);
                }
            } catch (error) {
                showStatus('QR Code tidak valid', 'error');
                setTimeout(() => { initQRScanner(); }, 2000);
            }
        }

        function onScanFailure(error) {
            console.log(`QR scan failure: ${error}`);
        }

        // Manual absen — sekarang lewat verifikasi wajah dulu
        function manualAbsen(status) {
            document.querySelectorAll('.btn-absen').forEach(btn => btn.disabled = true);
            verifyFaceThenAbsen(status);
        }

        // Start absen process — sekarang lewat verifikasi wajah dulu
        function startAbsenProcess(status) {
            document.querySelectorAll('.btn-absen').forEach(btn => btn.disabled = true);
            verifyFaceThenAbsen(status);
        }

        // Start camera (dipanggil setelah verifikasi berhasil)
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 720 } }
                });

                const video = document.getElementById('video');
                video.srcObject = stream;
                document.getElementById('cameraContainer').style.display = 'block';

                setTimeout(() => { capturePhoto(); }, 3000);

            } catch (error) {
                console.error('Error accessing camera:', error);
                showStatus('Tidak dapat mengakses kamera. Silakan izinkan akses kamera.', 'error');
                enableButtons();
            }
        }

        // Capture photo
        function capturePhoto() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.style.width = '100%';
            canvas.style.height = 'auto';

            context.save();
            context.translate(canvas.width, 0);
            context.scale(-1, 1);
            context.drawImage(video, 0, 0);
            context.restore();

            if (stream) stream.getTracks().forEach(track => track.stop());

            document.getElementById('cameraContainer').style.display = 'none';
            document.getElementById('canvasContainer').style.display = 'block';

            processAbsen();
        }

        // Process absen
        async function processAbsen() {
            const canvas = document.getElementById('canvas');
            const imageData = canvas.toDataURL('image/png');

            let location = '';
            if (navigator.geolocation) {
                try {
                    const position = await getCurrentPosition();
                    location = `${position.coords.latitude},${position.coords.longitude}`;
                } catch (error) {
                    location = '0,0';
                }
            } else {
                location = '0,0';
            }

            const cabangLocation = '{{ $cabang->lokasi_cabang ?? '0,0' }}';
            document.getElementById('loading').style.display = 'block';

            try {
                const response = await fetch('{{ route('facerecognition-presensi.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        nik: karyawan.nik,
                        status: currentStatus,
                        image: imageData,
                        lokasi: location,
                        lokasi_cabang: cabangLocation,
                        kode_jam_kerja: '{{ $karyawan->kode_jadwal ?? '0001' }}'
                    })
                });

                const result = await response.json();
                if (result.status) {
                    showStatus(result.message, 'success');
                    playNotificationSound('success');
                } else {
                    showStatus(result.message, 'error');
                    playNotificationSound('error');
                }
            } catch (error) {
                showStatus('Terjadi kesalahan saat mengirim data', 'error');
            }

            document.getElementById('loading').style.display = 'none';
            enableButtons();

            setTimeout(() => {
                document.getElementById('canvasContainer').style.display = 'none';
            }, 3000);
        }

        function getCurrentPosition() {
            return new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                });
            });
        }

        function showStatus(message, type) {
            const statusElement = document.getElementById('statusMessage');
            statusElement.textContent = message;
            statusElement.className = `status-message status-${type}`;
            statusElement.style.display = 'block';
            setTimeout(() => { statusElement.style.display = 'none'; }, 5000);
        }

        function enableButtons() {
            document.querySelectorAll('.btn-absen').forEach(btn => btn.disabled = false);
        }

        function playNotificationSound(type) {
            const audio = new Audio();
            audio.src = type === 'success'
                ? '{{ asset('assets/sound/absenmasuk.wav') }}'
                : '{{ asset('assets/sound/akhirabsen.wav') }}';
            audio.play().catch(e => console.log('Audio play failed:', e));
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Load face models di background
            loadFaceModels();

            // Init QR scanner
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(s) {
                    initQRScanner();
                    s.getTracks().forEach(t => t.stop());
                })
                .catch(function() {
                    initQRScanner();
                });
        });
    </script>
</body>

</html>

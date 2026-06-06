window.onerror = function(message, source, lineno, colno, error) {
    alert('JavaScript Error: ' + message + ' at line ' + lineno);
};

// Configuration
const TOTAL_IMAGES_NEEDED = 5;
const CAPTURE_INTERVAL = 300; // ms between captures
const CONFIDENCE_THRESHOLD = 0.5;

// State
let modelLoaded = false;
let isScanning = false;
let imagesCaptured = [];
let videoEl = document.getElementById('webcam-video');
let stream = null;

// UI Elements
const statusText = document.getElementById('statusText');
const statusDot = document.getElementById('statusDot');
const faceFrame = document.getElementById('faceFrame');
const warningToast = document.getElementById('warningToast');
const btnStart = document.getElementById('btnStart');
const actionArea = document.getElementById('actionArea');
const scanProgress = document.getElementById('scanProgress');
const progressBarFill = document.getElementById('progressBarFill');
const progressPercent = document.getElementById('progressPercent');

// Initialize
// Using simple immediate execution or checking readiness
(async function init() {
    // Bind button click using EventListener
    const btn = document.getElementById('btnStart');
    if(btn) {
        btn.addEventListener('click', function() {
            startScanning();
        });
        btn.addEventListener('touchstart', function(e) {
            e.preventDefault(); // Prevent double firing
            startScanning();
        }, {passive: false});
    }

    await startCamera();
    await loadModels();
})();

// 1. Start Camera
async function startCamera() {
    try {
        // Constraints for optimal face/portrait mode
        const constraints = {
            video: {
                facingMode: 'user',
                width: { ideal: 1280 },
                height: { ideal: 720 }
            }
        };
        
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        videoEl.srcObject = stream;
        
        // Wait for video to play to confirm dimensions
        await new Promise(resolve => videoEl.onloadedmetadata = resolve);
        videoEl.play();
        
        updateStatus('ready', 'Kamera Siap. Klik Mulai.');
        btnStart.disabled = false;
        
    } catch (err) {
        console.error("Camera Error:", err);
        updateStatus('error', 'Gagal akses kamera. Izinkan akses.');
        showWarning('Gagal mengakses kamera.', true);
    }
}

// 2. Load Face API Models
async function loadModels() {
    updateStatus('loading', 'Memuat model AI...');
    btnStart.disabled = true;
    
    try {
        await faceapi.nets.tinyFaceDetector.loadFromUri(window.CreateConfig.modelsUrl);
        // Optional: load landmarks if we want strict checks, but tinyDetector is enough for simple presence
        // await faceapi.nets.faceLandmark68Net.loadFromUri('/models'); 
        
        modelLoaded = true;
        updateStatus('ready', 'Kamera Siap. Klik tombol Mulai.');
        btnStart.disabled = false;
        console.log("Models loaded");
    } catch (err) {
        console.error("Model Load Error:", err);
        updateStatus('error', 'Gagal memuat model AI.');
    }
}

// 3. User Clicks Start
async function startScanning() {
    // alert('Debug: Tombol berhasil diklik!'); // Uncomment to debug if needed
    if (!modelLoaded) {
        alert('Model AI sedang dimuat, mohon tunggu sebentar...');
        return;
    }
    if (!stream) {
        alert('Kamera belum siap atau izin kamera ditolak. Pastikan Anda memberikan akses kamera pada browser.');
        return;
    }
    
    isScanning = true;
    imagesCaptured = []; // Reset
    
    // UI Updates
    btnStart.style.display = 'none'; // Hide button to declutter
    faceFrame.classList.add('scanning');
    scanProgress.classList.add('show');
    updateProgress(0);
    
    updateStatus('loading', 'Mencari wajah...');
    
    // Start Detection Loop
    detectLoop();
}

// 4. Detection Loop
async function detectLoop() {
    if (!isScanning) return;

    // Detect face using TinyFaceDetector (fastest)
    // We use inputSize 224 or 320 for speed on mobile
    const detection = await faceapi.detectSingleFace(videoEl, new faceapi.TinyFaceDetectorOptions({ inputSize: 320 }));

    if (detection && detection.score > CONFIDENCE_THRESHOLD) {
        // Face detected!
        const box = detection.box;
        
        // Simple centering check (optional, but good UX)
        // We define a "safe zone" in the center
        const videoWidth = videoEl.videoWidth;
        const videoHeight = videoEl.videoHeight;
        const centerX = box.x + (box.width / 2);
        const centerY = box.y + (box.height / 2);
        
        // Check if face is roughly centered (within middle 60%)
        const isCentered = (centerX > videoWidth * 0.2 && centerX < videoWidth * 0.8) &&
                           (centerY > videoHeight * 0.2 && centerY < videoHeight * 0.8);
        
        // Check if face is big enough
        const isCloseEnough = box.width > videoWidth * 0.15; // Face width > 15% of screen

        if (isCentered && isCloseEnough) {
            faceFrame.classList.add('active'); // Green border
            updateStatus('success', 'Wajah terdeteksi. Tahan...');
            hideWarning();
            
            // Capture Frame!
            await captureFrame();
            
        } else {
            faceFrame.classList.remove('active');
            if (!isCloseEnough) {
                updateStatus('warning', 'Mendekat ke kamera');
            } else {
                updateStatus('warning', 'Posisikan wajah di tengah');
            }
        }
    } else {
        faceFrame.classList.remove('active');
        updateStatus('loading', 'Wajah tidak terdeteksi...');
    }

    // Continue loop if not done
    if (imagesCaptured.length < TOTAL_IMAGES_NEEDED) {
        requestAnimationFrame(detectLoop);
    } else {
        finishScanning();
    }
}

// 5. Capture Frame Logic
let lastCaptureTime = 0;
async function captureFrame() {
    const now = Date.now();
    if (now - lastCaptureTime < CAPTURE_INTERVAL) return; // Debounce
    
    lastCaptureTime = now;
    
    // Draw video frame to canvas
    const canvas = document.createElement('canvas');
    canvas.width = videoEl.videoWidth;
    canvas.height = videoEl.videoHeight;
    const ctx = canvas.getContext('2d');
    
    // Mirror flip if using front camera usually mirrors, but we want the raw image?
    // Actually, for recognition, standard orientation is best. 
    // The video preview is css mirrored. We draw raw.
    ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);
    
    // Convert to base64
    const dataUrl = canvas.toDataURL('image/jpeg', 0.8); // 80% quality
    
    imagesCaptured.push(dataUrl);
    console.log(`Captured ${imagesCaptured.length}/${TOTAL_IMAGES_NEEDED}`);
    
    // Update Progress UI
    const pct = Math.round((imagesCaptured.length / TOTAL_IMAGES_NEEDED) * 100);
    updateProgress(pct);
}

function updateProgress(percent) {
    progressBarFill.style.width = percent + '%';
    progressPercent.innerText = percent + '%';
}

// 6. Finish & Upload
async function finishScanning() {
    isScanning = false;
    faceFrame.classList.remove('scanning');
    faceFrame.classList.add('active'); // Stay green
    
    updateStatus('success', 'Perekaman Selesai! Mengunggah...');
    scanProgress.classList.remove('show');
    
    // Show Spinner on Start Button (if we wanted to reuse it, but we hid it)
    // Let's create an upload form data
    
    try {
        const formData = new FormData();
        formData.append('_token', window.CreateConfig.csrfToken);
        formData.append('nik', window.CreateConfig.nik);
        
        // Convert captured base64 images to Blobs and append
        for (let i = 0; i < imagesCaptured.length; i++) {
            const blob = await (await fetch(imagesCaptured[i])).blob();
            formData.append('files[]', blob, `capture_${i+1}.jpg`);
        }
        
        // Add dummy metadata to satisfy backend requirement structure
        // Backend expects metadata json with direction keys
        const metadata = imagesCaptured.map(() => ({ direction: 'front' }));
        formData.append('metadata', JSON.stringify(metadata));

        // Send AJAX
        updateStatus('loading', 'Mengeirim data ke server...');
        
        $.ajax({
            type: 'POST',
            url: window.CreateConfig.storeRoute,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showSuccessScreen();
                } else {
                    handleUploadError(response.message);
                }
            },
            error: function(err) {
                console.error("Upload Error", err);
                handleUploadError('Terjadi kesalahan koneksi.');
            }
        });

    } catch (err) {
        console.error("Processing Error", err);
        handleUploadError('Gagal memproses gambar.');
    }
}

function showSuccessScreen() {
    const successScreen = document.getElementById('successScreen');
    successScreen.style.display = 'flex';
    setTimeout(() => {
        location.reload(); // Reload the page/close modal context
    }, 2000);
}

function handleUploadError(msg) {
    alert("Error: " + msg);
    // Reset to allow retry
    isScanning = false;
    imagesCaptured = [];
    btnStart.style.display = 'flex';
    faceFrame.classList.remove('active', 'scanning');
    updateProgress(0);
    updateStatus('error', 'Gagal. Silakan coba lagi.');
}

// Helpers
function updateStatus(type, text) {
    statusText.innerText = text;
    statusDot.className = 'status-dot'; // reset
    if (type === 'ready' || type === 'success') statusDot.classList.add('ready');
    if (type === 'loading') statusDot.style.background = '#fbbf24'; // yellow
    if (type === 'error') statusDot.style.background = '#ef4444'; // red
}

let warningTimeout;
function showWarning(msg) {
    const warningEl = document.getElementById('warningMessage');
    warningEl.innerText = msg;
    warningToast.classList.add('show');
    
    clearTimeout(warningTimeout);
    warningTimeout = setTimeout(() => {
        warningToast.classList.remove('show');
    }, 3000);
}

function hideWarning() {
    warningToast.classList.remove('show');
}

@extends('layouts.mobile.modern')

@section('title', 'Tambah Aktivitas')

@section('header_left')
    <a href="{{ route('aktivitaskaryawan.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/15 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        body {
            background: #e6fcf5 !important;
        }

        /* Premium Camera UI (No outer card) */
        .webcam-capture {
            width: 100%;
            height: 380px;
            border-radius: 24px;
            overflow: hidden;
            background: #1e293b;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 30px rgba(50, 116, 94, 0.12);
            margin-bottom: 25px;
            border: 2px solid #ffffff;
        }

        .webcam-capture video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
            transform: scaleX(-1); /* Mirror for front camera */
        }

        /* Glassmorphism Overlays */
        .glass-overlay {
            position: absolute;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 12px;
            padding: 6px 12px;
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            z-index: 20;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        .overlay-date { top: 15px; left: 15px; }
        .overlay-time { top: 15px; right: 15px; }

        /* Camera Controls */
        .camera-controls {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 12px;
            z-index: 30;
            padding: 0 20px;
        }

        .btn-camera-action {
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s;
            border: none;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .btn-capture {
            background: #32745e;
            color: white;
            flex: 1;
        }

        .btn-switch {
            background: #ffffff;
            color: #32745e;
            width: 50px;
            flex-shrink: 0;
        }

        .btn-camera-action:active {
            transform: scale(0.95);
        }

        /* Image Preview Overlay */
        .preview-overlay {
            position: absolute;
            top: 60px;
            right: 15px;
            width: 80px;
            height: 80px;
            border-radius: 14px;
            border: 3px solid #fff;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            z-index: 40;
            overflow: hidden;
            background: #eee;
            display: none;
            animation: popIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes popIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .preview-overlay img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Form Styling */
        .form-label-group {
            position: relative;
            margin-bottom: 15px;
            background: #ffffff;
            border: 1px solid #32745e;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .form-label-group .input-icon {
            position: absolute;
            left: 15px;
            top: 15px;
            font-size: 24px;
            color: #32745e;
            z-index: 10;
            pointer-events: none;
        }

        .form-label-group textarea {
            width: 100% !important;
            min-height: 120px !important;
            padding: 30px 15px 5px 52px !important;
            font-size: 16px;
            font-weight: 500;
            color: #616eb5;
            background: transparent !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            display: block !important;
            resize: none;
            line-height: 1.5;
        }

        .form-label-group label {
            position: absolute;
            top: 15px;
            left: 52px;
            font-size: 16px;
            color: #32745e;
            opacity: 0.8;
            pointer-events: none;
            transition: all 0.2s ease-in-out;
            margin-bottom: 0;
            z-index: 5;
        }

        .form-label-group textarea:focus ~ label,
        .form-label-group textarea:not(:placeholder-shown) ~ label {
            top: 5px;
            left: 52px;
            font-size: 11px;
            font-weight: 600;
            color: #32745e;
        }

        /* Modern Submit Button */
        .btn-submit-premium {
            width: 100%;
            height: 54px;
            background: #32745e;
            color: #ffffff;
            border: none;
            border-radius: 18px;
            font-size: 16px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 25px rgba(50, 116, 94, 0.25);
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-submit-premium:active {
            transform: scale(0.97);
            background: #616eb5;
        }

        .error-hint {
            color: #ef4444;
            font-size: 12px;
            font-weight: 600;
            padding-left: 10px;
            margin-top: -15px;
            margin-bottom: 15px;
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="fade-up" style="padding: 10px 5px 100px 5px;">
        <form method="POST" action="{{ route('aktivitaskaryawan.store') }}" id="formAktivitas" enctype="multipart/form-data">
            @csrf
            
            <input type="hidden" name="image" id="image-data">
            <input type="hidden" name="lokasi" id="lokasi">

            <!-- Camera Section (Floating Style) -->
            <div class="relative">
                <div class="webcam-capture">
                    {{-- Video Holder --}}
                    <div id="video-holder" class="absolute inset-0 z-0"></div>

                    {{-- Overlays --}}
                    <div class="glass-overlay overlay-date">{{ DateToIndo(date('Y-m-d')) }}</div>
                    <div class="glass-overlay overlay-time"><span id="jam-inner" class="jam-display">00:00:00</span></div>
                    
                    {{-- Image Preview Overlay --}}
                    <div id="imagePreview" class="preview-overlay">
                        <img id="previewImg" src="" alt="Preview">
                    </div>

                    {{-- Camera Placeholder --}}
                    <div id="cameraPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center text-white/20 z-0">
                        <ion-icon name="camera-outline" class="text-6xl mb-2"></ion-icon>
                        <span class="text-xs font-semibold uppercase tracking-widest">Initializing...</span>
                    </div>

                    {{-- Action Buttons Overlay --}}
                    <div class="camera-controls">
                        <button type="button" class="btn-camera-action btn-capture" id="btnScan">
                            <ion-icon name="camera-outline" class="text-xl"></ion-icon>
                            <span>Ambil Foto</span>
                        </button>
                        <button type="button" class="btn-camera-action btn-switch" id="btnSwitch">
                            <ion-icon name="camera-reverse-outline" class="text-xl"></ion-icon>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dashboard Style Form -->
            <div class="form-label-group shadow-sm">
                <ion-icon name="document-text-outline" class="input-icon"></ion-icon>
                <textarea name="aktivitas" id="aktivitas" placeholder=" " required>{{ old('aktivitas') }}</textarea>
                <label for="aktivitas">Apa aktivitas harian Anda hari ini?</label>
            </div>
            @error('aktivitas')
                <span class="error-hint">{{ $message }}</span>
            @enderror

            <button type="submit" class="btn-submit-premium" id="btnSubmit">
                <ion-icon name="cloud-upload-outline" class="text-xl"></ion-icon>
                <span>Simpan Aktivitas</span>
            </button>
        </form>
    </div>

    <canvas id="canvas" style="display: none;"></canvas>

@endsection

@push('myscript')
    <script>
        $(document).ready(function() {
            let video = null;
            let stream = null;
            let currentFacingMode = 'environment';
            let capturedImage = null;

            // Clock Synchronization
            function updateClock() {
                const now = new Date();
                const timeStr = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit', minute: '2-digit', second: '2-digit'
                });
                $('.jam-display').text(timeStr);
            }
            setInterval(updateClock, 1000);
            updateClock();

            // Camera Engine
            async function startCamera(facingMode) {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }

                const constraints = {
                    video: { 
                        facingMode: facingMode,
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                };

                try {
                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                    const videoTag = document.createElement('video');
                    videoTag.srcObject = stream;
                    videoTag.autoplay = true;
                    videoTag.playsInline = true;
                    
                    // Style fitting for standardized container
                    videoTag.style.width = '100%';
                    videoTag.style.height = '100%';
                    videoTag.style.objectFit = 'cover';
                    videoTag.style.transform = facingMode === 'user' ? 'scaleX(-1)' : 'none';

                    $('#video-holder').html(videoTag);
                    video = videoTag;
                    $('#cameraPlaceholder').fadeOut();
                    currentFacingMode = facingMode;
                } catch (err) {
                    console.error("Camera Access Error:", err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal mengakses kamera.',
                    });
                }
            }

            // Capture Logic
            $('#btnScan').on('click', function() {
                if (!video) return;

                const canvas = document.getElementById('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');

                // Mirror correction for capture
                if (currentFacingMode === 'user') {
                    ctx.translate(canvas.width, 0);
                    ctx.scale(-1, 1);
                }
                
                ctx.drawImage(video, 0, 0);
                capturedImage = canvas.toDataURL('image/jpeg', 0.82);

                $('#previewImg').attr('src', capturedImage);
                $('#imagePreview').show();
                $('#image-data').val(capturedImage);

                Swal.fire({
                    icon: 'success',
                    title: 'Tersimpan!',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            // Switch Camera
            $('#btnSwitch').on('click', function() {
                currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
                startCamera(currentFacingMode);
            });

            // Geolocation
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    $('#lokasi').val(position.coords.latitude + "," + position.coords.longitude);
                }, function(error) {
                    console.warn('Geolocation Error:', error.message);
                }, { enableHighAccuracy: true });
            }

            // Init
            startCamera(currentFacingMode);

            // Form Interceptor
            $('#formAktivitas').on('submit', function(e) {
                const foto = $('#image-data').val();
                if (!foto) {
                    e.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'Belum Ada Foto', text: 'Silakan ambil foto aktivitas Anda.' });
                    return false;
                }
                
                const loc = $('#lokasi').val();
                if (!loc) {
                    e.preventDefault();
                    Swal.fire({ icon: 'warning', title: 'Lokasi Belum Terdeteksi', text: 'Tunggu sejenak agar lokasi berhasil dideteksi.' });
                    return false;
                }

                $('#btnSubmit').addClass('opacity-50').attr('disabled', 'disabled').html('<ion-icon name="sync-outline" class="animate-spin text-xl mr-2"></ion-icon><span>Menyimpan...</span>');
            });

            // Auto-resize textarea
            $('#aktivitas').on('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });

            // Cleanup
            $(window).on('beforeunload', function() {
                if (stream) stream.getTracks().forEach(track => track.stop());
            });
        });
    </script>
    <style>
        .animate-spin { animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    </style>
@endpush

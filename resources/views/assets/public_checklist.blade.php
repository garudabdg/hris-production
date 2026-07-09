<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checklist Perawatan Aset - {{ $asset->kode_asset }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .webcam-capture {
            width: 100%;
            height: 300px;
            border-radius: 12px;
            overflow: hidden;
            background: #1e293b;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 10px;
        }
        .webcam-capture video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover;
        }
        .camera-controls {
            position: absolute;
            bottom: 15px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 12px;
            z-index: 30;
        }
        .btn-camera-action {
            height: 40px;
            border-radius: 20px;
            border: none;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            padding: 0 15px;
        }
        .btn-capture {
            background: #ef4444;
            color: white;
        }
        .btn-switch {
            background: rgba(255,255,255,0.2);
            color: white;
            backdrop-filter: blur(5px);
            width: 40px;
            padding: 0;
        }
        .preview-overlay {
            position: absolute;
            inset: 0;
            background: #000;
            z-index: 25;
            display: none;
        }
        .preview-overlay img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen pb-10">

    <div class="bg-blue-600 text-white shadow-md">
        <div class="max-w-md mx-auto px-4 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold leading-tight">{{ config('app.name', 'HRIS') }}</h1>
                <p class="text-xs text-blue-200">Asset Maintenance</p>
            </div>
            <a href="{{ route('assets.public_scan') }}" class="bg-white/20 p-2 rounded-lg hover:bg-white/30 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
            </a>
        </div>
    </div>

    <div class="max-w-md mx-auto mt-4 px-4">
        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: '{{ session("success") }}',
                        icon: 'success',
                        confirmButtonText: 'Lanjut Scan',
                        confirmButtonColor: '#2563eb',
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('assets.public_scan') }}";
                        }
                    });
                });
            </script>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Terjadi Kesalahan!</strong>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-5">
            <div class="bg-slate-50 px-4 py-3 border-b border-slate-100 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Informasi Aset</span>
                <span class="px-2 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded-md">{{ $asset->kode_asset }}</span>
            </div>
            <div class="p-4 space-y-4">
                <div class="flex gap-4 items-start">
                    @if ($asset->foto)
                        <img src="{{ asset('storage/assets/' . $asset->foto) }}" alt="{{ $asset->nama_asset }}" class="w-20 h-20 rounded-lg object-cover border border-slate-200 shadow-sm">
                    @else
                        <div class="w-20 h-20 rounded-lg bg-slate-100 flex items-center justify-center border border-slate-200 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    @endif
                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-slate-800 leading-tight mb-1">{{ $asset->nama_asset }}</h2>
                        <p class="text-sm text-slate-500">{{ $asset->category->nama_kategori ?? '-' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                        <p class="text-xs text-slate-500 font-medium mb-1">Merk</p>
                        <p class="text-sm font-semibold text-slate-700 truncate">{{ $asset->merk ?? '-' }}</p>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                        <p class="text-xs text-slate-500 font-medium mb-1">No. Seri</p>
                        <p class="text-sm font-semibold text-slate-700 truncate">{{ $asset->no_seri ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('assets.public_checklist.update', $asset->kode_asset) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            @csrf
            <div class="bg-slate-50 px-4 py-3 border-b border-slate-100">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Laporan Perawatan</span>
            </div>
            
            <div class="p-4 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Update Nama Aset</label>
                        <input type="text" name="nama_asset" value="{{ $asset->nama_asset }}" class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Nama Aset">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Update Merk</label>
                        <input type="text" name="merk" value="{{ $asset->merk }}" class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Merk Aset">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Update Foto Aset <span class="text-xs text-slate-500 font-normal">(opsional)</span></label>
                    <input type="file" name="foto" onchange="checkFileSize(this)" class="w-full border border-slate-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p id="file-name-preview" class="text-xs text-blue-600 mt-2 font-medium"></p>

                    <div class="flex items-center my-4">
                        <div class="flex-grow border-t border-slate-200"></div>
                        <span class="mx-3 text-xs text-slate-400 font-bold uppercase tracking-wider">Atau Foto Langsung</span>
                        <div class="flex-grow border-t border-slate-200"></div>
                    </div>

                    <input type="hidden" name="foto_base64" id="foto_base64">
                    <div class="webcam-capture relative">
                        <div id="video-holder" class="absolute inset-0 z-0"></div>
                        <div id="imagePreview" class="preview-overlay">
                            <img id="previewImg" src="" alt="Preview">
                        </div>
                        <div id="cameraPlaceholder" class="absolute inset-0 flex flex-col items-center justify-center text-white/50 z-0">
                            <span class="text-xs font-semibold uppercase tracking-widest">Memuat Kamera...</span>
                        </div>
                        <div class="camera-controls">
                            <button type="button" class="btn-camera-action btn-capture" id="btnScan">
                                Ambil Foto
                            </button>
                            <button type="button" class="btn-camera-action btn-switch" id="btnSwitch">
                                <span class="text-lg">↻</span>
                            </button>
                            <button type="button" class="btn-camera-action bg-slate-600 text-white" id="btnRetake" style="display:none;">
                                Ulangi
                            </button>
                        </div>
                    </div>
                    <canvas id="canvas" style="display:none;"></canvas>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Petugas / Pelapor <span class="text-red-500">*</span></label>
                    <input type="text" name="petugas" value="{{ auth()->user()->karyawan->nama_karyawan ?? auth()->user()->name ?? '' }}" required readonly class="w-full bg-slate-100 border border-slate-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Masukkan nama Anda">
                </div>

                <div class="border-t border-slate-200 pt-4">
                    <h3 class="text-sm font-bold text-slate-800 mb-4">Poin Checklist</h3>
                    
                    @if(empty($checklistItems))
                        <div class="bg-yellow-50 text-yellow-700 p-3 rounded-lg text-sm mb-4">
                            Kategori aset ini belum memiliki daftar checklist khusus. Anda dapat menambahkan catatan di bawah.
                        </div>
                    @endif

                    <div class="space-y-5">
                        @foreach($checklistItems as $index => $item)
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <input type="hidden" name="items[{{ $index }}][item_name]" value="{{ $item }}">
                                <p class="font-semibold text-slate-800 mb-3 text-sm">{{ $loop->iteration }}. {{ $item }}</p>
                                
                                <div class="grid grid-cols-3 gap-2 mb-3">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="items[{{ $index }}][klasifikasi]" value="baik" required class="peer sr-only">
                                        <div class="text-center px-1 py-2 border border-slate-200 bg-white rounded-lg peer-checked:bg-green-50 peer-checked:border-green-500 peer-checked:text-green-700 hover:bg-slate-50 transition-all">
                                            <span class="text-xs font-bold">Baik</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="items[{{ $index }}][klasifikasi]" value="cukup_baik" required class="peer sr-only">
                                        <div class="text-center px-1 py-2 border border-slate-200 bg-white rounded-lg peer-checked:bg-yellow-50 peer-checked:border-yellow-500 peer-checked:text-yellow-700 hover:bg-slate-50 transition-all">
                                            <span class="text-xs font-bold">Cukup</span>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="items[{{ $index }}][klasifikasi]" value="rusak" required class="peer sr-only">
                                        <div class="text-center px-1 py-2 border border-slate-200 bg-white rounded-lg peer-checked:bg-red-50 peer-checked:border-red-500 peer-checked:text-red-700 hover:bg-slate-50 transition-all">
                                            <span class="text-xs font-bold">Rusak</span>
                                        </div>
                                    </label>
                                </div>
                                <input type="text" name="items[{{ $index }}][keterangan]" placeholder="Keterangan tambahan (opsional)" class="w-full border border-slate-200 rounded-lg p-2 text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Catatan Keseluruhan</label>
                    <textarea name="catatan" rows="3" class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" placeholder="Tambahkan catatan umum jika ada..."></textarea>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition-all flex justify-center items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Simpan Hasil Perawatan
                </button>
            </div>
        </form>
    </div>

    <script>
        function checkFileSize(input) {
            const preview = document.getElementById('file-name-preview');
            if (input.files && input.files[0]) {
                const maxAllowedSize = 30 * 1024 * 1024; // 30MB
                if (input.files[0].size > maxAllowedSize) {
                    alert("Maaf, ukuran foto melebihi batas maksimal (30MB). Silakan pilih foto dengan ukuran lebih kecil.");
                    input.value = ""; // Clear the selected file
                    preview.textContent = "";
                } else {
                    preview.textContent = "File terpilih: " + input.files[0].name + " (" + (input.files[0].size / 1024 / 1024).toFixed(2) + " MB)";
                    
                    // Clear webcam if file is selected
                    document.getElementById('imagePreview').style.display = 'none';
                    document.getElementById('foto_base64').value = '';
                    document.getElementById('btnRetake').style.display = 'none';
                    document.getElementById('btnScan').style.display = 'flex';
                    document.getElementById('btnSwitch').style.display = 'flex';
                }
            } else {
                preview.textContent = "";
            }
        }

        // Web Camera Logic
        let stream = null;
        let video = null;
        let currentFacingMode = 'environment';

        function startCamera(facingMode = 'environment') {
            if (stream) stream.getTracks().forEach(track => track.stop());
            
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                    video: { facingMode: facingMode }
                }).then(function(s) {
                    stream = s;
                    const videoTag = document.createElement('video');
                    videoTag.srcObject = stream;
                    videoTag.setAttribute('playsinline', true);
                    videoTag.autoplay = true;
                    
                    if (facingMode === 'user') {
                        videoTag.style.transform = 'scaleX(-1)';
                    } else {
                        videoTag.style.transform = 'none';
                    }

                    document.getElementById('video-holder').innerHTML = '';
                    document.getElementById('video-holder').appendChild(videoTag);
                    video = videoTag;
                    document.getElementById('cameraPlaceholder').style.display = 'none';
                    currentFacingMode = facingMode;
                }).catch(function(err) {
                    console.error("Camera Access Error:", err);
                    document.getElementById('cameraPlaceholder').innerHTML = '<span class="text-xs text-red-400 text-center px-4">Gagal mengakses kamera. Gunakan tombol pilih file di atas.</span>';
                });
            } else {
                document.getElementById('cameraPlaceholder').innerHTML = '<span class="text-xs text-red-400 text-center px-4">Browser tidak mendukung kamera langsung.</span>';
            }
        }

        document.getElementById('btnScan').addEventListener('click', function() {
            if (!video) return;

            const canvas = document.getElementById('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');

            if (currentFacingMode === 'user') {
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
            }
            
            ctx.drawImage(video, 0, 0);
            const capturedImage = canvas.toDataURL('image/jpeg', 0.8);

            document.getElementById('previewImg').src = capturedImage;
            document.getElementById('imagePreview').style.display = 'block';
            document.getElementById('foto_base64').value = capturedImage;

            this.style.display = 'none';
            document.getElementById('btnSwitch').style.display = 'none';
            document.getElementById('btnRetake').style.display = 'flex';
            
            // Clear file input if webcam is used
            document.querySelector('input[name="foto"]').value = "";
            document.getElementById('file-name-preview').textContent = "";
        });

        document.getElementById('btnRetake').addEventListener('click', function() {
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('foto_base64').value = '';
            
            this.style.display = 'none';
            document.getElementById('btnScan').style.display = 'flex';
            document.getElementById('btnSwitch').style.display = 'flex';
        });

        document.getElementById('btnSwitch').addEventListener('click', function() {
            currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
            startCamera(currentFacingMode);
        });

        // Initialize camera on page load
        startCamera('environment');

        // Stop camera on form submit
        document.querySelector('form').addEventListener('submit', function() {
            if (stream) stream.getTracks().forEach(track => track.stop());
        });
    </script>
</body>
</html>

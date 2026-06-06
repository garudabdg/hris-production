@extends('layouts.mobile.modern')

@section('title', 'E-Presensi')

@section('header_left')
    <a href="javascript:;" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/15 text-white active:scale-90 transition-transform" onclick="window.history.back()">
        <ion-icon name="chevron-back-outline" class="text-base"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        /* Override modern layout main padding for camera view */
        main { padding-left: 0 !important; padding-right: 0 !important; padding-top: calc(3.5rem + env(safe-area-inset-top)) !important; }
    </style>
@endpush

@section('content')
    {{-- <style>
        :root {
            --bg-body: #dff9fb;
            --bg-nav: #ffffff;
            --color-nav: #32745e;
            --color-nav-active: #58907D;
            --bg-indicator: #32745e;
            --color-nav-hover: #283ebe;
        }
    </style> --}}
    @push('mystyle')
        <link rel="stylesheet" href="{{ asset('assets/css/presensi.css') }}">
    @endpush
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <!-- Import Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <div id="content-section">
        <!-- SKELETON LOADER -->
        <div id="skeleton-loader" class="presensi-content-modern" style="background: transparent; box-shadow: none;">
            <!-- Camera Skeleton -->
            <div class="skeleton skeleton-camera"></div>
            
            <!-- Info Skeleton -->
            <div class="info-section" style="background: white; margin-bottom: 15px;">
                <div class="skeleton-row">
                    <div class="skeleton skeleton-col"></div>
                    <div class="skeleton skeleton-col"></div>
                    <div class="skeleton skeleton-col"></div>
                </div>
            </div>

            <!-- Button Skeleton -->
            <div class="action-section">
                <div class="skeleton skeleton-btn"></div>
                <div class="skeleton skeleton-btn"></div>
            </div>
        </div>

        <div id="real-content" class="presensi-content-modern content-hide">
            <div class="camera-section" style="position:relative;">
                <div class="row" style="margin-top: 0;">
                    <div class="col" id="facedetection" style="position:relative;">
                        <!-- Absolute Tanggal & Jam -->
                        <div class="abs-tanggal-modern">{{ DateToIndo(date('Y-m-d')) }}</div>
                        <div class="abs-jam-modern"><span id="jam"></span></div>
                        <div style="position:relative;">
                            <div class="webcam-capture"></div>
                            <!-- Debug overlay hidden for production -->
                            <div id="debug-overlay" style="display:none; position:absolute; top:10px; left:10px; z-index:9999; color:lime; font-size:11px; pointer-events:none; text-shadow: 1px 1px 2px black; text-align:left; max-height:80%; overflow:hidden;"></div>
                        </div>
                        <!-- MAPS ABSOLUTE -->
                        <div class="map-absolute-section">
                            <div id="map">
                                <div id="map-loading">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                    <div class="mt-2">Memuat peta...</div>
                                </div>
                            </div>
                        </div>
                        @if ($general_setting->multi_lokasi)
                            <div id="listcabang">
                                <div class="select-wrapper">
                                    <select name="cabang" id="cabang" class="form-control">
                                        @foreach ($cabang as $item)
                                            <option {{ $item->kode_cabang == $karyawan->kode_cabang ? 'selected' : '' }}
                                                value="{{ $item->lokasi_cabang }}">
                                                {{ $item->nama_cabang }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <!-- Info jam digital dipindah ke info-section -->
                    </div>
                </div>
            </div>
            <div class="info-section">
                <div class="row jadwalkerja-row">
                    <div class="col text-center jadwalkerja-col jadwalkerja-col-shift">

                        <ion-icon name="person-outline" class="jadwalkerja-icon"></ion-icon>
                        <div class="jadwalkerja-label">Shift</div>
                        <div class="jadwalkerja-value">{{ $jam_kerja->nama_jam_kerja }}</div>
                    </div>
                    <div class="col text-center jadwalkerja-col">
                        <ion-icon name="log-in-outline" class="jadwalkerja-icon"></ion-icon>
                        <div class="jadwalkerja-label">Jam Masuk</div>
                        <div class="jadwalkerja-value">{{ date('H:i', strtotime($jam_kerja->jam_masuk)) }}</div>
                    </div>
                    <div class="col text-center jadwalkerja-col">
                        <ion-icon name="log-out-outline" class="jadwalkerja-icon"></ion-icon>
                        <div class="jadwalkerja-label">Jam Pulang</div>
                        <div class="jadwalkerja-value">{{ date('H:i', strtotime($jam_kerja->jam_pulang)) }}</div>
                    </div>
                </div>
            </div>
            <!-- <div class="map-section"> ... </div> -->
            <div class="action-section">
                <button class="btn btn-success bg-primary scan-button" id="absenmasuk" statuspresensi="masuk">
                    <ion-icon name="finger-print-outline" style="font-size: 24px !important"></ion-icon>
                    <span style="font-size:14px">Masuk</span>
                </button>
                <button class="btn btn-danger scan-button" id="absenpulang" statuspresensi="pulang">
                    <ion-icon name="finger-print-outline" style="font-size: 24px !important"></ion-icon>
                    <span style="font-size:14px">Pulang</span>
                </button>
            </div>
        </div>

    <audio id="notifikasi_radius">
        <source src="{{ asset('assets/sound/radius.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="notifikasi_mulaiabsen">
        <source src="{{ asset('assets/sound/mulaiabsen.wav') }}" type="audio/mpeg">
    </audio>
    <audio id="notifikasi_akhirabsen">
        <source src="{{ asset('assets/sound/akhirabsen.wav') }}" type="audio/mpeg">
    </audio>
    <audio id="notifikasi_sudahabsen">
        <source src="{{ asset('assets/sound/sudahabsen.wav') }}" type="audio/mpeg">
    </audio>
    <audio id="notifikasi_absenmasuk">
        <source src="{{ asset('assets/sound/absenmasuk.wav') }}" type="audio/mpeg">
    </audio>


    <!--Pulang-->
    <audio id="notifikasi_sudahabsenpulang">
        <source src="{{ asset('assets/sound/sudahabsenpulang.mp3') }}" type="audio/mpeg">
    </audio>
    <audio id="notifikasi_absenpulang">
        <source src="{{ asset('assets/sound/absenpulang.mp3') }}" type="audio/mpeg">
    </audio>
@endsection
@push('myscript')
    <!-- Face Recognition dengan Caching -->
    <script src="{{ asset('assets/vendor/face-api.min.js') }}"></script>
    <script src="{{ asset('assets/external/js/face-model-cache.js') }}"></script>
    <script type="text/javascript">
        // Fungsi yang dijalankan ketika halaman selesai dimuat
        // Menggunakan DOMContentLoaded untuk memastikan DOM sudah siap
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                jam();
            });
        } else {
            // DOM sudah dimuat, langsung panggil jam()
            jam();
        }

        // Fungsi untuk menampilkan waktu secara real-time
        function jam() {
            // Mengambil elemen HTML dengan id 'jam'
            var e = document.getElementById('jam');

            // Cek apakah elemen ada sebelum mengatur innerHTML
            if (!e) {
                // Jika elemen belum tersedia, coba lagi setelah 100ms
                setTimeout(jam, 100);
                return;
            }

            // Membuat objek Date untuk mendapatkan waktu saat ini
            var d = new Date(),
                // Variabel untuk menampung jam, menit, dan detik
                h, m, s;
            // Mengambil jam dari objek Date
            h = d.getHours();
            // Mengambil menit dari objek Date dan menambahkan '0' di depan jika kurang dari 10
            m = set(d.getMinutes());
            // Mengambil detik dari objek Date dan menambahkan '0' di depan jika kurang dari 10
            s = set(d.getSeconds());

            // Menampilkan waktu dalam format HH:MM:SS
            e.innerHTML = h + ':' + m + ':' + s;

            // Mengatur waktu untuk memanggil fungsi jam() lagi setelah 1 detik
            setTimeout(jam, 1000);
        }

        // Fungsi untuk menambahkan '0' di depan angka jika kurang dari 10
        function set(e) {
            // Jika angka kurang dari 10, tambahkan '0' di depan
            e = e < 10 ? '0' + e : e;
            // Mengembalikan angka yang telah ditambahkan '0' di depan jika perlu
            return e;
        }
    </script>
    <script>
        window.PresensiConfig = {
            multi_lokasi: {{ $general_setting->multi_lokasi }},
            lokasi_cabang: "{{ $lokasi_kantor->lokasi_cabang }}",
            face_recognition: "{{ $general_setting->face_recognition }}",
            radius_cabang: "{{ $lokasi_kantor->radius_cabang }}",
            nik: "{{ $karyawan->nik }}",
            nama_depan: "{{ getNamaDepan(strtolower($karyawan->nama_karyawan)) }}",
            nama_karyawan: "{{ $karyawan->nama_karyawan }}",
            wajah: parseInt("{{ $wajah }}") || 0,
            jmlwajah: "{{ $wajah == 0 ? 1 : $wajah }}",
            csrf_token: "{{ csrf_token() }}",
            kode_jam_kerja: "{{ $jam_kerja->kode_jam_kerja ?? '' }}",
            store_route: "{{ route('presensi.store') }}"
        };
    </script>
    <script src="{{ asset('storage/presensi.js') }}?v=1"></script>
@endpush
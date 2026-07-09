@extends('layouts.mobile.modern')

@section('title', 'Aktivitas Saya')

@section('header_left')
    <a href="{{ route('dashboard.index') }}"
        class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@section('header_right')
    <a href="{{ route('aktivitaskaryawan.export.pdf', request()->query()) }}"
        class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-white active:scale-95 transition-all"
        target="_blank" title="Export PDF">
        <ion-icon name="document-text-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://cdn.jsdelivr.net/npm/air-datepicker@3.5.0/air-datepicker.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ asset('assets/css/aktivitaskaryawan.css') }}">
    <style>
        :root {
            --primary-color: {{ $t['primary'] }};
            --primary-color-15: {{ $t['primary'] }}15;
        }
    </style>
@endpush

@section('content')
    {{-- ===== FILTER SYNC WITH HISTORI ===== --}}
    <form method="GET" action="{{ route('aktivitaskaryawan.index') }}" id="formAktivitas">
        <div class="mt-1 mb-2 rounded-xl overflow-hidden border"
            style="background: #fff; border-color: #e2e8f0; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
            {{-- Filter Header --}}
            <div class="flex items-center gap-2 px-3 py-2" style="border-bottom: 1px solid #f1f5f9;">
                <div class="w-6 h-6 rounded flex items-center justify-center" style="background: {{ $t['primary'] }}15;">
                    <ion-icon name="calendar-outline" class="text-[12px]" style="color: {{ $t['primary'] }};"></ion-icon>
                </div>
                <span class="text-[12px] font-semibold" style="color: #475569;">Pilih Rentang Tanggal</span>
            </div>
            {{-- Filter Inputs --}}
            <div class="px-3 py-2.5">
                <div class="flex items-center gap-2">
                    {{-- Dari --}}
                    <div class="flex-1">
                        <input type="text" name="tanggal_awal" id="tanggal_awal"
                            class="w-full rounded-lg py-1.5 px-2 text-[12px] font-medium text-center focus:outline-none transition-all datepicker"
                            style="background: #f8fafc; border: 1px solid #e2e8f0; color: #334155;" placeholder="Dari"
                            value="{{ Request('tanggal_awal', date('Y-m-d')) }}" autocomplete="off" required readonly>
                    </div>
                    <div class="flex-shrink-0 w-4 flex items-center justify-center">
                        <div class="w-3 h-[1px]" style="background: #cbd5e1;"></div>
                    </div>
                    {{-- Sampai --}}
                    <div class="flex-1">
                        <input type="text" name="tanggal_akhir" id="tanggal_akhir"
                            class="w-full rounded-lg py-1.5 px-2 text-[12px] font-medium text-center focus:outline-none transition-all datepicker"
                            style="background: #f8fafc; border: 1px solid #e2e8f0; color: #334155;" placeholder="Sampai"
                            value="{{ Request('tanggal_akhir', date('Y-m-d')) }}" autocomplete="off" required readonly>
                    </div>
                    {{-- Button --}}
                    <button type="submit" id="btnCari"
                        class="flex-shrink-0 w-9 h-8 rounded-lg text-white flex items-center justify-center active:scale-90 transition-transform"
                        style="background: {{ $t['primary'] }};">
                        <ion-icon name="search-outline" class="text-base"></ion-icon>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Activities List (Width Sync: Removing transactions custom padding) -->
    <div class="transactions space-y-2 mt-2">
        @if ($aktivitas->count() > 0)
            @php
                $namahari = [
                    'Sun' => 'Min',
                    'Mon' => 'Sen',
                    'Tue' => 'Sel',
                    'Wed' => 'Rab',
                    'Thu' => 'Kam',
                    'Fri' => 'Jum',
                    'Sat' => 'Sab',
                ];
            @endphp
            @foreach ($aktivitas as $index => $item)
                @php
                    $day_eng = $item->created_at->format('D');
                    $day_short = $namahari[$day_eng] ?? $day_eng;
                @endphp
                <div class="fade-up item press mb-0" style="animation-delay: {{ $index * 0.04 }}s;"
                    onclick="showDetailModal({{ $item->id }}, '{{ addslashes($item->aktivitas) }}', '{{ $item->created_at->format('d M Y') }}', '{{ $item->created_at->format('H:i') }}', '{{ $item->lokasi }}', '{{ $item->foto }}')">
                    <div class="detail">
                        {{-- Date Badge Sync --}}
                        <div class="date-badge-modern">
                            <span class="day-short">{{ strtoupper($day_short) }}</span>
                            <span class="date-num">{{ $item->created_at->format('d') }}</span>
                        </div>

                        <div class="info">
                            <div class="meta-row">
                                <strong>{{ DateToIndo($item->created_at->format('Y-m-d')) }}</strong>
                                <span class="timestamp">
                                    <ion-icon name="time-outline"></ion-icon>
                                    {{ $item->created_at->format('H:i') }}
                                </span>
                            </div>
                            <p class="truncate" style="color: #334155; font-weight: 600; margin-bottom: 2px;">
                                {{ Str::limit($item->aktivitas, 35) }}
                            </p>
                            @if ($item->lokasi)
                                <p style="font-size: 11px;">
                                    <ion-icon name="location-outline" style="color: {{ $t['primary'] }};"></ion-icon>
                                    <a href="https://www.google.com/maps?q={{ $item->lokasi }}" target="_blank"
                                        onclick="event.stopPropagation();"
                                        style="color: {{ $t['primary'] }}; font-weight: 600;">
                                        Lihat di Peta
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="price" onclick="event.stopPropagation();">
                        <form method="POST" name="deleteform" class="deleteform d-inline"
                            action="{{ route('aktivitaskaryawan.destroy', $item) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm text-danger p-0 delete-confirm"
                                style="background: transparent; border: none;">
                                <ion-icon name="trash-outline" style="font-size: 20px;"></ion-icon>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center" style="padding: 100px 40px;">
                <div style="font-size: 80px; color: #e2e8f0; margin-bottom: 20px;">
                    <ion-icon name="calendar-clear-outline"></ion-icon>
                </div>
                <h4 style="color: #1e293b; font-weight: 800; margin-bottom: 8px;">Belum Ada Aktivitas</h4>
                <p style="color: #64748b; font-size: 14px;">Mulai catat aktivitas harian Anda untuk melacak produktivitas.
                </p>
                <button type="button" onclick="openActionSheet()" class="btn-search-modern mt-4"
                    style="text-decoration: none; width: 200px; margin: 0 auto;">
                    <ion-icon name="add-outline"></ion-icon> Tambah Aktivitas
                </button>
            </div>
        @endif

        {{-- Pagination --}}
        <div class="p-3">
            {{ $aktivitas->links() }}
        </div>
    </div>

    {{-- FAB Button --}}
    <button type="button" onclick="openActionSheet()" class="fab-modern">
        <ion-icon name="add-outline"></ion-icon>
    </button>

    {{-- Modern Detail Modal (Synced with Kunjungan Style) --}}
    <div id="detailModal" class="fixed inset-0 z-[10000] bg-black/70 backdrop-blur-sm opacity-0 pointer-events-none">
        <div class="modal-card">
            <div class="modal-header-modern">
                <h3 class="modal-title-modern">Detail Aktivitas</h3>
                <ion-icon name="close-outline" class="modal-close-icon text-2xl text-slate-400 cursor-pointer"
                    onclick="closeDetailModal()"></ion-icon>
            </div>

            <div class="modal-body-modern">
                {{-- Live Map integration (Leaflet) --}}
                <div class="modal-map-container" id="mapWrapper">
                    <div id="activityMap"></div>
                    <button type="button" class="map-refresh-btn shadow-sm active:scale-95 transition-all"
                        onclick="refreshMap(event)">
                        <ion-icon name="refresh-outline"></ion-icon>
                    </button>
                </div>

                <div class="modal-img-container">
                    <img id="modalImg" src="" class="modal-img-full hidden" alt="Foto">
                    <div id="modalIconWrapper" class="flex flex-col items-center gap-2 py-4">
                        <ion-icon name="image-outline" class="text-5xl text-slate-200"></ion-icon>
                        <span class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Tidak ada foto</span>
                    </div>
                </div>

                <div class="modal-info-grid">
                    <div class="info-item">
                        <div class="info-icon-box">
                            <ion-icon name="calendar-outline"></ion-icon>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Tanggal</div>
                            <div id="modalDate" class="info-value text-[13px] font-semibold"></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon-box">
                            <ion-icon name="time-outline"></ion-icon>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Waktu Catat</div>
                            <div id="modalTime" class="info-value text-[13px] font-semibold"></div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon-box">
                            <ion-icon name="map-outline"></ion-icon>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Lokasi / Koordinat</div>
                            <div id="modalLocationWrapper">
                                <a id="modalLocationLink" href="" target="_blank"
                                    class="info-value flex items-center gap-1 text-teal-600 underline">
                                    <span id="modalLocation"></span>
                                    <ion-icon name="open-outline" class="text-[10px]"></ion-icon>
                                </a>
                                <div id="modalLocationEmpty" class="info-value text-slate-400 hidden">Lokasi tidak
                                    tersedia</div>
                            </div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon-box">
                            <ion-icon name="chatbox-ellipses-outline"></ion-icon>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Deskripsi Aktivitas</div>
                            <div id="modalDescription" class="info-value"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer-modern">
                <button onclick="closeDetailModal()" class="btn-close-modern">Tutup Detail</button>
            </div>
        </div>
    </div>

    {{-- Action Sheet --}}
    <div id="actionSheet" class="fixed inset-0 z-[10000] bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl transform translate-y-full transition-transform duration-300" id="actionSheetContent">
            <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800 text-lg">Pilih Jenis Aktivitas</h3>
                <button type="button" onclick="closeActionSheet()" class="text-gray-400 hover:text-gray-600 bg-gray-50 rounded-full w-8 h-8 flex items-center justify-center transition-colors">
                    <ion-icon name="close-outline" class="text-xl"></ion-icon>
                </button>
            </div>
            <div class="p-5 space-y-4 pb-8">
                <a href="{{ route('aktivitaskaryawan.create') }}" class="flex items-center gap-4 p-4 rounded-2xl border border-gray-100 hover:bg-emerald-50 active:scale-[0.98] transition-all shadow-sm">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center bg-emerald-100 text-emerald-600 flex-shrink-0">
                        <ion-icon name="document-text-outline" class="text-3xl"></ion-icon>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-base mb-1">Aktivitas Harian</h4>
                        <p class="text-xs text-gray-500 leading-relaxed">Catat log aktivitas beserta titik lokasi dan bukti foto</p>
                    </div>
                </a>
                <a href="{{ route('dailyreportbu.create') }}" class="flex items-center gap-4 p-4 rounded-2xl border border-gray-100 hover:bg-blue-50 active:scale-[0.98] transition-all shadow-sm">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center bg-blue-100 text-blue-600 flex-shrink-0">
                        <ion-icon name="briefcase-outline" class="text-3xl"></ion-icon>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-800 text-base mb-1">Daily Report Business</h4>
                        <p class="text-xs text-gray-500 leading-relaxed">Laporan detail untuk aktivitas online, offline & prospek nasabah</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection

@push('myscript')
    <script src="https://cdn.jsdelivr.net/npm/air-datepicker@3.5.0/air-datepicker.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        window.Config = {
            assetAktivitas: "{{ asset('storage/uploads/aktivitas') }}"
        };

        function openActionSheet() {
            $('#actionSheet').removeClass('opacity-0 pointer-events-none');
            setTimeout(() => {
                $('#actionSheetContent').removeClass('translate-y-full');
            }, 50);
        }
        
        function closeActionSheet() {
            $('#actionSheetContent').addClass('translate-y-full');
            setTimeout(() => {
                $('#actionSheet').addClass('opacity-0 pointer-events-none');
            }, 300);
        }
        
        $(document).on('click', function(e) {
            if ($(e.target).is('#actionSheet')) {
                closeActionSheet();
            }
        });
    </script>
    <script src="{{ asset('assets/js/aktivitaskaryawan.js') }}"></script>
@endpush

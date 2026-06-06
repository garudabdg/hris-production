@extends('layouts.app')
@section('titlepage', 'Monitoring Presensi')

@section('content')
@section('navigasi')
    <span>Monitoring Presensi</span>
@endsection

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                {{-- ============================================
                     Section: Filter Form
                     Filter berdasarkan tanggal, cabang, dan nama
                     ============================================ --}}
                <form action="{{ route('presensi.index') }}" class="mb-4">
                    <div class="row g-2 align-items-end">
                        <div class="col-lg-3 col-md-6 col-12">
                            <x-input-with-icon label="" value="{{ Request('tanggal') }}" name="tanggal"
                                icon="ti ti-calendar" datepicker="flatpickr-date" placeholder="Tanggal" />
                        </div>
                        <div class="col-lg-3 col-md-6 col-12">
                            <x-select label="" name="kode_cabang" :data="$cabang" key="kode_cabang"
                                textShow="nama_cabang" selected="{{ Request('kode_cabang') }}" upperCase="true"
                                select2="select2Kodecabangsearch" placeholder="Cabang" />
                        </div>
                        <div class="col-lg-5 col-md-10 col-10">
                            <x-input-with-icon label="" value="{{ Request('nama_karyawan') }}" name="nama_karyawan"
                                icon="ti ti-search" placeholder="Cari Nama Karyawan" />
                        </div>
                        <div class="col-lg-1 col-md-2 col-2">
                            <div class="mb-3">
                                <button class="btn btn-primary w-100">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- ============================================
                     Section: Daftar Presensi Karyawan
                     Satu baris per karyawan: foto, nama, info, aksi
                     ============================================ --}}
                @foreach ($karyawan as $d)
                    @php
                        // Hitung data terlambat dan denda untuk karyawan ini
                        $tanggal_presensi      = !empty(Request('tanggal')) ? Request('tanggal') : date('Y-m-d');
                        $jam_masuk             = $tanggal_presensi . ' ' . $d->jam_masuk;
                        $terlambat             = hitungjamterlambat($d->jam_in, $jam_masuk);
                        $potongan_tidak_hadir  = $d->status == 'a' ? $d->total_jam : 0;
                        $pulangcepat           = hitungpulangcepat(
                            $tanggal_presensi, $d->jam_out, $d->jam_pulang,
                            $d->istirahat, $d->jam_awal_istirahat, $d->jam_akhir_istirahat, $d->lintashari,
                        );

                        // Prioritas: gunakan denda dari tabel jika laporan sudah dikunci
                        if ($d->denda !== null) {
                            $denda = $d->denda;
                            $potongan_jam_terlambat = $terlambat ? ($terlambat['desimal_terlambat'] >= 1 ? $terlambat['desimal_terlambat'] : 0) : 0;
                        } else {
                            if ($terlambat != null) {
                                if ($terlambat['desimal_terlambat'] < 1) {
                                    $potongan_jam_terlambat = 0;
                                    $denda = hitungdenda($denda_list, $terlambat['menitterlambat']);
                                } else {
                                    $potongan_jam_terlambat = $terlambat['desimal_terlambat'];
                                    $denda = 0;
                                }
                            } else {
                                $potongan_jam_terlambat = 0;
                                $denda = 0;
                            }
                        }

                        $total_potongan_jam = $pulangcepat + $potongan_jam_terlambat + $potongan_tidak_hadir;
                    @endphp

                    {{-- Card 1 baris per karyawan, responsif --}}
                    <div class="presensi-card" style="border-radius:10px;border:1px solid #e9ecef;background:#fff;margin-bottom:8px;overflow:hidden;">
                        <div class="presensi-row-inner" style="display:flex;align-items:center;gap:8px;padding:8px 12px;flex-wrap:nowrap;min-width:0;overflow:hidden;">

                            {{-- Foto Avatar --}}
                            @php $path = Storage::url('karyawan/'.$d->foto); @endphp
                            @if (!empty($d->foto) && Storage::disk('public')->exists('/karyawan/' . $d->foto))
                                <img src="{{ url($path) }}" alt="Avatar" class="presensi-avatar"
                                    style="width:36px;height:36px;object-fit:cover;border-radius:50%;border:2px solid #e9ecef;flex-shrink:0;">
                            @else
                                <img src="{{ asset('assets/img/avatars/No_Image_Available.jpg') }}" alt="No Image" class="presensi-avatar"
                                    style="width:36px;height:36px;object-fit:cover;border-radius:50%;border:2px solid #e9ecef;flex-shrink:0;">
                            @endif

                            {{-- Nama & Meta (selalu tampil, bisa menyusut) --}}
                            <div class="presensi-info">
                                <div class="presensi-name">{{ $d->nama_karyawan }}</div>
                                <div class="presensi-meta">
                                    <span class="text-primary fw-semibold">{{ $d->nik_show ?? $d->nik }}</span>
                                    <span>·</span><i class="ti ti-building" style="font-size:10px"></i>{{ $d->kode_dept }}
                                    <span>·</span><i class="ti ti-map-pin" style="font-size:10px"></i>{{ $d->kode_cabang }}
                                </div>
                            </div>

                            {{-- Jadwal (hilang di < 1200px) --}}
                            <div class="presensi-divider presensi-divider-jadwal"></div>
                            <div class="presensi-col presensi-col-jadwal" style="width:90px;">
                                <span class="presensi-label">Jadwal</span>
                                <span class="presensi-value">
                                    <i class="ti ti-clock text-secondary" style="font-size:10px"></i>
                                    @if ($d->kode_jam_kerja)
                                        {{ date('H:i', strtotime($d->jam_masuk)) }}-{{ date('H:i', strtotime($d->jam_pulang)) }}
                                    @else -
                                    @endif
                                </span>
                            </div>

                            {{-- Jam Masuk (selalu tampil) --}}
                            <div class="presensi-divider"></div>
                            <div class="presensi-col" style="width:72px;">
                                <span class="presensi-label">Masuk</span>
                                <span class="presensi-value">
                                    @if ($d->jam_in)
                                        <a href="#" class="btnShowpresensi_in text-success fw-bold text-decoration-none"
                                            id="{{ $d->id }}" status="in">
                                            <i class="ti ti-login" style="font-size:10px"></i>
                                            {{ date('H:i', strtotime($d->jam_in)) }}
                                        </a>
                                        @if (!empty($d->foto_in))
                                            <i class="ti ti-photo text-success" style="font-size:9px" title="Ada Foto"></i>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </span>
                            </div>

                            {{-- Jam Pulang (selalu tampil) --}}
                            <div class="presensi-divider"></div>
                            <div class="presensi-col" style="width:72px;">
                                <span class="presensi-label">Pulang</span>
                                <span class="presensi-value">
                                    @if ($d->jam_out)
                                        <a href="#" class="btnShowpresensi_out text-danger fw-bold text-decoration-none"
                                            id="{{ $d->id }}" status="out">
                                            <i class="ti ti-logout" style="font-size:10px"></i>
                                            {{ date('H:i', strtotime($d->jam_out)) }}
                                        </a>
                                        @if (!empty($d->foto_out))
                                            <i class="ti ti-photo text-danger" style="font-size:9px" title="Ada Foto"></i>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </span>
                            </div>

                            {{-- Terlambat (hilang di < 992px) --}}
                            <div class="presensi-divider presensi-divider-terlambat"></div>
                            <div class="presensi-col presensi-col-terlambat" style="width:90px;">
                                <span class="presensi-label">Terlambat</span>
                                <span class="presensi-value">
                                    @if ($terlambat)
                                        <span class="text-warning fw-semibold">
                                            <i class="ti ti-clock-exclamation" style="font-size:10px"></i>
                                            {!! strip_tags($terlambat['show']) !!}
                                        </span>
                                    @else
                                        <span class="text-success fw-semibold">
                                            <i class="ti ti-check" style="font-size:10px"></i> Tepat
                                        </span>
                                    @endif
                                </span>
                            </div>

                            {{-- Denda (hilang di < 1400px) --}}
                            <div class="presensi-divider presensi-divider-denda"></div>
                            <div class="presensi-col presensi-col-denda" style="width:65px;">
                                <span class="presensi-label">Denda</span>
                                <span class="presensi-value text-danger fw-semibold">
                                    <i class="ti ti-coin" style="font-size:10px"></i>
                                    {{ empty($denda) ? '0' : formatAngka($denda) }}
                                </span>
                            </div>

                            {{-- Potongan (hilang di < 1400px) --}}
                            <div class="presensi-divider presensi-divider-potongan"></div>
                            <div class="presensi-col presensi-col-potongan" style="width:65px;">
                                <span class="presensi-label">Potongan</span>
                                <span class="presensi-value">
                                    @if ($total_potongan_jam > 0)
                                        <span class="text-danger fw-semibold">
                                            <i class="ti ti-cut" style="font-size:10px"></i>
                                            {{ formatAngkaDesimal($total_potongan_jam) }}j
                                        </span>
                                    @else
                                        <span class="text-success fw-semibold">
                                            <i class="ti ti-cut" style="font-size:10px"></i> 0j
                                        </span>
                                    @endif
                                </span>
                            </div>

                            {{-- Status Badge + Aksi (selalu di kanan) --}}
                            <div class="presensi-actions">

                                {{-- Badge Status --}}
                                @if ($d->status == 'h')
                                    <span class="presensi-status-badge bg-success-subtle text-success">
                                        <i class="ti ti-check"></i> Hadir
                                    </span>
                                @elseif ($d->status == 'i')
                                    <span class="presensi-status-badge bg-info-subtle text-info">
                                        <i class="ti ti-file-info"></i> Izin
                                    </span>
                                @elseif ($d->status == 's')
                                    <span class="presensi-status-badge bg-warning-subtle text-warning">
                                        <i class="ti ti-ambulance"></i> Sakit
                                    </span>
                                @elseif ($d->status == 'a')
                                    <span class="presensi-status-badge bg-danger-subtle text-danger">
                                        <i class="ti ti-x"></i> Alpa
                                    </span>
                                @elseif ($d->status == 'c')
                                    <span class="presensi-status-badge bg-primary-subtle text-primary">
                                        <i class="ti ti-calendar-event"></i> Cuti
                                    </span>
                                @else
                                    <span class="presensi-status-badge bg-secondary-subtle text-secondary">
                                        <i class="ti ti-minus"></i> Belum
                                    </span>
                                @endif

                                {{-- Tombol Aksi --}}
                                <div class="d-flex gap-1">
                                    @if (isset($d->status_potongan))
                                        <button class="presensi-action-btn btn btn-light text-muted" disabled title="Terkunci">
                                            <i class="ti ti-lock" style="font-size:12px"></i>
                                        </button>
                                    @else
                                        <a href="#" class="presensi-action-btn btn btn-light text-success koreksiPresensi"
                                            nik="{{ Crypt::encrypt($d->nik) }}" tanggal="{{ $tanggal_presensi }}" title="Koreksi">
                                            <i class="ti ti-edit" style="font-size:12px"></i>
                                        </a>
                                        @if (!empty($d->id))
                                            <form action="{{ route('presensi.delete', $d->id) }}" method="POST"
                                                style="display:inline-block;" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="presensi-action-btn btn btn-light text-danger delete-confirm" title="Hapus">
                                                    <i class="ti ti-trash" style="font-size:12px"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif

                                    <a href="#" class="presensi-action-btn btn btn-light text-primary btngetDatamesin"
                                        pin="{{ $d->pin }}"
                                        tanggal="{{ !empty(Request('tanggal')) ? Request('tanggal') : date('Y-m-d') }}"
                                        title="Log Mesin">
                                        <i class="ti ti-device-desktop" style="font-size:12px"></i>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>{{-- end presensi-card --}}

                @endforeach

                {{-- Pagination --}}
                <div class="d-flex justify-content-end mt-3">
                    {{ $karyawan->links() }}
                </div>

            </div>
        </div>
    </div>
</div>

<x-modal-form id="modal" size="modal-lg" show="loadmodal" title="" />

@endsection

@push('myscript')
    {{-- CSS khusus halaman Monitoring Presensi --}}
    {{-- Embed langsung sebagai style tag agar pasti dimuat sebelum render --}}
    <style>
        /* Card wrapper */
        .presensi-card { border-radius:10px; border:1px solid #e9ecef; background:#fff; margin-bottom:8px; overflow:hidden; transition:box-shadow .2s,transform .2s; }
        .presensi-card:hover { box-shadow:0 3px 16px rgba(0,0,0,.08); transform:translateY(-1px); }

        /* Row utama: 1 baris flex, tidak wrap */
        .presensi-row-inner { display:flex; align-items:center; gap:8px; padding:8px 12px; flex-wrap:nowrap; min-width:0; overflow:hidden; }

        /* Avatar */
        .presensi-avatar { width:36px; height:36px; object-fit:cover; flex-shrink:0; border-radius:50%; border:2px solid #e9ecef; }

        /* Nama & meta */
        .presensi-info { min-width:0; flex-shrink:1; flex-basis:150px; }
        .presensi-name { font-size:.82rem; font-weight:600; color:#1a1a2e; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .presensi-meta { font-size:.68rem; color:#6c757d; display:flex; align-items:center; gap:4px; white-space:nowrap; overflow:hidden; }

        /* Divider vertikal */
        .presensi-divider { width:1px; height:28px; background:#e9ecef; flex-shrink:0; }

        /* Kolom info */
        .presensi-col { flex-shrink:0; min-width:0; }
        .presensi-label { font-size:.6rem; letter-spacing:.5px; text-transform:uppercase; color:#adb5bd; display:block; margin-bottom:2px; font-weight:600; white-space:nowrap; }
        .presensi-value { font-size:.78rem; font-weight:500; color:#343a40; white-space:nowrap; display:block; }

        /* Aksi: rata kanan */
        .presensi-actions { flex-shrink:0; margin-left:auto; display:flex; align-items:center; gap:6px; }

        /* Badge status */
        .presensi-status-badge { font-size:.68rem; padding:2px 8px; border-radius:20px; font-weight:600; white-space:nowrap; }

        /* Tombol aksi */
        .presensi-action-btn { width:28px; height:28px; padding:0; display:flex; align-items:center; justify-content:center; border-radius:7px; font-size:.75rem; transition:all .15s; flex-shrink:0; }
        .presensi-action-btn:hover { transform:scale(1.1); }

        /* Breakpoint: < 1400px — sembunyikan Denda & Potongan */
        @media (max-width:1399px) {
            .presensi-col-denda, .presensi-col-potongan,
            .presensi-divider-denda, .presensi-divider-potongan { display:none !important; }
        }
        /* Breakpoint: < 1200px — sembunyikan juga Jadwal */
        @media (max-width:1199px) {
            .presensi-col-jadwal, .presensi-divider-jadwal { display:none !important; }
        }
        /* Breakpoint: < 992px — sembunyikan Terlambat */
        @media (max-width:991px) {
            .presensi-col-terlambat, .presensi-divider-terlambat { display:none !important; }
        }
        /* Breakpoint: < 576px — sembunyikan badge status & meta */
        @media (max-width:575px) {
            .presensi-status-badge, .presensi-meta { display:none !important; }
        }
    </style>

    {{-- Konfigurasi variabel Blade untuk JS eksternal --}}
    <script>
        window.PresensiConfig = {
            csrfToken : '{{ csrf_token() }}',
            routes    : {
                edit : '{{ route('presensi.edit') }}'
            }
        };
    </script>
    <script src="{{ asset('assets/js/presensi_index.js') }}"></script>
@endpush

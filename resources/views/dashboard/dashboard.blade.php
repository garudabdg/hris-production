@extends('layouts.app')
@section('titlepage', 'Dashboard')


@push('mystyle')
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
@endpush


@section('content')
@section('navigasi')
    <span>Dashboard</span>
@endsection

<div class="d-flex justify-content-end mt-3">
    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterDashboardModal">
        <i class="ti ti-filter me-1"></i> Filter
    </button>
</div>

<!-- Modal Filter -->
<div class="modal fade" id="filterDashboardModal" tabindex="-1" aria-labelledby="filterDashboardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterDashboardModalLabel">Filter Kehadiran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="">
                <div class="modal-body">
                    <div class="row">
                        <x-input-with-icon label="Tanggal" icon="ti ti-calendar" name="tanggal" datepicker="flatpickr-date"
                            value="{{ Request('tanggal') }}" />
                        <x-select label="Cabang" name="kode_cabang" :data="$cabang" key="kode_cabang" textShow="nama_cabang"
                            selected="{{ Request('kode_cabang') }}" />
                        <x-select label="Departemen" name="kode_dept" :data="$departemen" key="kode_dept" textShow="nama_dept"
                            selected="{{ Request('kode_dept') }}" upperCase="true" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                    <button class="btn btn-primary"><i class="ti ti-search me-1"></i> Terapkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
    $authUser = auth()->user();
    $fullName = $authUser->name ?? 'Pengguna';
    $userName = explode(' ', $fullName)[0]; // Ambil nama depan saja
    $currentHour = (int) date('H');

    if ($currentHour >= 5 && $currentHour < 12) {
        $greeting = 'Selamat Pagi';
    } elseif ($currentHour >= 12 && $currentHour < 15) {
        $greeting = 'Selamat Siang';
    } elseif ($currentHour >= 15 && $currentHour < 19) {
        $greeting = 'Selamat Sore';
    } else {
        $greeting = 'Selamat Malam';
    }

    $tanggalHariIni = getnamaHari(date('D')) . ', ' . DateToIndo(date('Y-m-d'));
@endphp

<!-- Welcome Card -->
<div class="welcome-card">
    <div class="welcome-card__content">
        <div class="welcome-card__greeting">{{ $greeting }},</div>
        <div class="welcome-card__name">{{ $userName }} 👋</div>
        <div class="welcome-card__date">
            <i class="ti ti-calendar"></i>
            <span>{{ $tanggalHariIni }}</span>
        </div>
    </div>
    <div class="welcome-card__icon">
        <i class="ti ti-user"></i>
    </div>
</div>

@php
    $presenceStats = [
        [
            'title' => 'Total Hadir',
            'value' => $rekappresensi->hadir ?? 0,
            'meta' => 'Karyawan hadir hari ini',
            'trend' => 'Live update',
            'icon' => 'ti ti-user-check',
            'class' => 'stat-card--highlight ',
        ],
        [
            'title' => 'Izin',
            'value' => $rekappresensi->izin ?? 0,
            'meta' => 'Sedang izin resmi',
            'trend' => 'Terverifikasi',
            'icon' => 'ti ti-file-description',
            'accent' => '#2563eb',
        ],
        [
            'title' => 'Sakit',
            'value' => $rekappresensi->sakit ?? 0,
            'meta' => 'Sedang sakit',
            'trend' => 'Realtime update',
            'icon' => 'ti ti-ambulance',
            'accent' => '#d97706',
        ],
        [
            'title' => 'Cuti',
            'value' => $rekappresensi->cuti ?? 0,
            'meta' => 'Sedang cuti ',
            'trend' => 'Terjadwal',
            'icon' => 'ti ti-briefcase',
            'accent' => '#7c3aed',
        ],
    ];
@endphp

<div class="stat-grid">
    @foreach ($presenceStats as $stat)
        <div class="stat-card {{ $stat['class'] ?? '' }}" style="--stat-accent: {{ $stat['accent'] ?? 'var(--theme-color-1)' }};">
            <div class="stat-card__top">
                <div>
                    <p class="stat-card__title">{{ $stat['title'] }}</p>
                    <h3 class="stat-card__value">{{ $stat['value'] }}</h3>
                </div>
                <div class="stat-card__icon">
                    <i class="{{ $stat['icon'] }}"></i>
                </div>
            </div>
            <div>
                <p class="stat-card__meta mb-1">
                    <i class="ti ti-broadcast me-1"></i>
                    {{ $stat['meta'] }}
                </p>
                {{-- <span class="stat-card__trend">
                    <i class="ti ti-arrow-up-right"></i>
                    {{ $stat['trend'] }}
                </span> --}}
            </div>
        </div>
    @endforeach
</div>

<div class="row mt-3">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <div class="card mb-6">
            <div class="card-widget-separator-wrapper">
                <div class="card-body card-widget-separator">
                    <div class="row gy-4 gy-sm-1">
                        <div class="col-sm-6 col-lg-3">
                            <div class="d-flex justify-content-between align-items-start card-widget-1 border-end pb-4 pb-sm-0">

                                <div>
                                    <p class="mb-1">Karyawan Aktif</p>
                                    <h4 class="mb-1">{{ $status_karyawan->jml_aktif }}</h4>
                                </div>
                                <div class="avatar me-sm-4">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="ti ti-users fs-4"></i>
                                    </span>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="d-flex justify-content-between align-items-start card-widget-2 border-end pb-4 pb-sm-0">
                                <div>
                                    <p class="mb-1">Karyawan Tetap</p>
                                    <h4 class="mb-1">{{ $status_karyawan->jml_tetap }}</h4>
                                </div>
                                <div class="avatar me-sm-4">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="ti ti-user-check fs-4"></i>
                                    </span>
                                </div>
                            </div>

                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="d-flex justify-content-between align-items-start border-end pb-4 pb-sm-0 card-widget-3">
                                <div>
                                    <p class="mb-1">Karyawan Kontrak</p>
                                    <h4 class="mb-1">{{ $status_karyawan->jml_kontrak }}</h4>
                                </div>
                                <div class="avatar me-sm-4">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="ti ti-user-exclamation fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="mb-1">Outsourcing</p>
                                    <h4 class="mb-1">{{ $status_karyawan->jml_outsourcing }}</h4>
                                </div>
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="ti ti-user-share fs-4"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<div class="row mt-3">
    <div class="col-lg-8 col-md-6 col-sm-12">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="ti ti-cake fs-4"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="mb-0">Karyawan Ulang Tahun</h4>
                                <small class="text-muted">Selamat ulang tahun untuk karyawan yang berulang tahun hari ini</small>
                            </div>
                        </div>
                        <span class="badge bg-label-warning rounded-pill">{{ count($birthday) }} Karyawan</span>
                    </div>
                    <div class="card-body">
                        @if (count($birthday) > 0)
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                <div>
                                    <h6 class="mb-0">Kirim Ucapan Ulang Tahun</h6>
                                    <small class="text-muted">Kirim ucapan ulang tahun ke semua karyawan yang berulang tahun hari ini</small>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-success btn-sm" id="btnKirimUcapan" onclick="kirimUcapanSemua()">
                                        <i class="ti ti-brand-whatsapp me-1"></i>
                                        <span id="btnText">Kirim ke Semua</span>
                                        <span id="btnLoading" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="row g-3">
                                @foreach ($birthday as $d)
                                    @php
                                        $umur = \Carbon\Carbon::parse($d->tanggal_lahir)->age;
                                        $colors = ['primary', 'success', 'info', 'warning', 'danger'];
                                        $colorIndex = $loop->index % count($colors);
                                        $color = $colors[$colorIndex];
                                    @endphp
                                    <div class="col-12">
                                        <div class="card card-border-shadow-{{ $color }} birthday-card"
                                            style="transition: all 0.3s ease; cursor: pointer;"
                                            onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.15)';"
                                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar me-3" style="width: 80px; height: 80px; position: relative;">
                                                        @if (!empty($d->foto))
                                                            @if (Storage::disk('public')->exists('/karyawan/' . $d->foto))
                                                                <img src="{{ getfotoKaryawan($d->foto) }}" alt="{{ $d->nama_karyawan }}"
                                                                    class="rounded-circle border border-{{ $color }} border-3"
                                                                    style="width: 80px; height: 80px; object-fit: cover; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                                            @else
                                                                <div class="avatar-initial rounded-circle bg-label-{{ $color }} d-flex align-items-center justify-content-center border border-{{ $color }} border-3"
                                                                    style="width: 80px; height: 80px; font-size: 32px;">
                                                                    <i class="ti ti-user"></i>
                                                                </div>
                                                            @endif
                                                        @else
                                                            <div class="avatar-initial rounded-circle bg-label-{{ $color }} d-flex align-items-center justify-content-center border border-{{ $color }} border-3"
                                                                style="width: 80px; height: 80px; font-size: 32px;">
                                                                <i class="ti ti-user"></i>
                                                            </div>
                                                        @endif
                                                        <div class="position-absolute bottom-0 end-0 bg-{{ $color }} text-white rounded-circle d-flex align-items-center justify-content-center border border-white border-2"
                                                            style="width: 28px; height: 28px; font-size: 14px;">
                                                            <i class="ti ti-cake"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <h5 class="mb-0">{{ $d->nama_karyawan }}</h5>
                                                            <span class="badge bg-label-{{ $color }} rounded-pill">{{ $umur }}
                                                                Tahun</span>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col-md-6">
                                                                <div class="d-flex align-items-center mb-1">
                                                                    <i class="ti ti-id me-2 text-{{ $color }}"></i>
                                                                    <small class="text-muted">NIK:</small>
                                                                    <strong class="ms-2">{{ $d->nik_show }}</strong>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="d-flex align-items-center mb-1">
                                                                    <i class="ti ti-calendar me-2 text-{{ $color }}"></i>
                                                                    <small class="text-muted">Tanggal Lahir:</small>
                                                                    <strong
                                                                        class="ms-2">{{ date('d-m-Y', strtotime($d->tanggal_lahir)) }}</strong>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="d-flex align-items-center mb-1">
                                                                    <i class="ti ti-briefcase me-2 text-{{ $color }}"></i>
                                                                    <small class="text-muted">Jabatan:</small>
                                                                    <strong class="ms-2">{{ $d->nama_jabatan }}</strong>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="d-flex align-items-center mb-1">
                                                                    <i class="ti ti-building me-2 text-{{ $color }}"></i>
                                                                    <small class="text-muted">Dept:</small>
                                                                    <strong class="ms-2">{{ $d->kode_dept }}</strong>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="d-flex align-items-center mb-2">
                                                                    <i class="ti ti-map-pin me-2 text-{{ $color }}"></i>
                                                                    <small class="text-muted">Cabang:</small>
                                                                    <strong class="ms-2">{{ $d->nama_cabang }}</strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="avatar mb-3" style="width: 100px; height: 100px; margin: 0 auto;">
                                    <span class="avatar-initial rounded-circle bg-label-secondary d-flex align-items-center justify-content-center"
                                        style="font-size: 48px;">
                                        <i class="ti ti-cake-off"></i>
                                    </span>
                                </div>
                                <h5 class="text-muted">Tidak ada karyawan yang ulang tahun hari ini</h5>
                                <p class="text-muted mb-0">Semua karyawan akan menunggu hari ulang tahun mereka!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @php
            $contractTabs = [
                [
                    'id' => 'lewatjatuhtempo',
                    'label' => 'Lewat Jatuh Tempo',
                    'badge' => 'bg-label-danger',
                    'icon' => 'ti ti-alert-octagon',
                    'items' => $kontrak_lewat,
                    'showRemaining' => false,
                    'accent' => '#dc2626',
                    'active' => false,
                ],
                [
                    'id' => 'bulanini',
                    'label' => 'Bulan Ini',
                    'badge' => 'bg-label-danger',
                    'icon' => 'ti ti-calendar-event',
                    'items' => $kontrak_bulanini,
                    'showRemaining' => true,
                    'accent' => '#f97316',
                    'active' => true,
                ],
                [
                    'id' => 'bulandepan',
                    'label' => 'Bulan Depan',
                    'badge' => 'bg-label-warning',
                    'icon' => 'ti ti-calendar-stats',
                    'items' => $kontrak_bulandepan,
                    'showRemaining' => true,
                    'accent' => '#facc15',
                    'active' => false,
                ],
                [
                    'id' => 'duabulan',
                    'label' => '2 Bulan Lagi',
                    'badge' => 'bg-label-success',
                    'icon' => 'ti ti-calendar-time',
                    'items' => $kontrak_duabulan,
                    'showRemaining' => true,
                    'accent' => '#22c55e',
                    'active' => false,
                ],
            ];

            $contractSummary = [
                [
                    'label' => 'Lewat Tempo',
                    'count' => count($kontrak_lewat),
                    'icon' => 'ti ti-alert-triangle',
                    'accent' => 'linear-gradient(120deg,#f43f5e,#b91c1c)',
                ],
                [
                    'label' => 'Bulan Ini',
                    'count' => count($kontrak_bulanini),
                    'icon' => 'ti ti-calendar-event',
                    'accent' => 'linear-gradient(120deg,#f97316,#ea580c)',
                ],
                [
                    'label' => 'Bulan Depan',
                    'count' => count($kontrak_bulandepan),
                    'icon' => 'ti ti-calendar-stats',
                    'accent' => 'linear-gradient(120deg,#facc15,#eab308)',
                ],
                [
                    'label' => '2 Bulan',
                    'count' => count($kontrak_duabulan),
                    'icon' => 'ti ti-calendar-time',
                    'accent' => 'linear-gradient(120deg,#34d399,#059669)',
                ],
            ];
        @endphp

        <div class="row mt-3">
            <div class="col">
                <div class="card contract-card">
                    <div class="card-header contract-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="ti ti-briefcase-off fs-4"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="mb-0">Karyawan Habis Kontrak</h4>
                                <small class="text-muted">Pantau kontrak yang segera atau sudah melewati jatuh tempo</small>
                            </div>
                        </div>
                        <span class="badge bg-label-success rounded-pill mt-3 mt-lg-0">
                            Total {{ count($kontrak_lewat) + count($kontrak_bulanini) + count($kontrak_bulandepan) + count($kontrak_duabulan) }}
                            Kontrak
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="contract-summary">
                            @foreach ($contractSummary as $summary)
                                <div class="contract-summary__item" style="--contract-summary-bg: {{ $summary['accent'] }};">
                                    <div class="contract-summary__icon">
                                        <i class="{{ $summary['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <p class="mb-1"
                                            style="opacity: 0.9; font-size: 0.8rem; letter-spacing: 0.04em; text-transform: uppercase;">
                                            {{ $summary['label'] }}
                                        </p>
                                        <p class="contract-summary__count">{{ $summary['count'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="contract-tabs nav-align-top mt-4">
                            <ul class="nav nav-tabs" role="tablist">
                                @foreach ($contractTabs as $tab)
                                    <li class="nav-item" role="presentation">
                                        <button type="button" class="nav-link {{ $tab['active'] ? 'active' : '' }}" role="tab"
                                            data-bs-toggle="tab" data-bs-target="#{{ $tab['id'] }}" aria-controls="{{ $tab['id'] }}"
                                            aria-selected="{{ $tab['active'] ? 'true' : 'false' }}" tabindex="{{ $tab['active'] ? '0' : '-1' }}"
                                            style="--contract-accent: {{ $tab['accent'] }};">
                                            <i class="{{ $tab['icon'] }} me-2"></i>
                                            {{ $tab['label'] }}
                                            <span class="badge rounded-pill badge-center h-px-20 w-px-20 {{ $tab['badge'] }} ms-2">
                                                {{ count($tab['items']) }}
                                            </span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="tab-content mt-3" style="padding: 0 !important;">
                                @foreach ($contractTabs as $tab)
                                    <div class="tab-pane fade {{ $tab['active'] ? 'show active' : '' }}" id="{{ $tab['id'] }}"
                                        role="tabpanel">
                                        @if (count($tab['items']) === 0)
                                            <div class="contract-empty">
                                                <i class="ti ti-confetti fs-1 mb-2 d-block"></i>
                                                Tidak ada kontrak pada kategori ini.
                                            </div>
                                        @else
                                            <div class="table-responsive contract-table-wrapper">
                                                <table class="table table-hover align-middle mb-0 contract-table">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th>No. Kontrak</th>
                                                            <th>NIK</th>
                                                            <th>Nama Karyawan</th>
                                                            <th>Jabatan</th>
                                                            <th>Dept</th>
                                                            <th>Cabang</th>
                                                            <th>Akhir Kontrak</th>
                                                            @if ($tab['showRemaining'])
                                                                <th class="text-center">Sisa Waktu</th>
                                                            @endif
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($tab['items'] as $d)
                                                            @php
                                                                $sisahari = hitungSisahari($d->sampai);
                                                                $isLate = $sisahari < 0;
                                                            @endphp
                                                            <tr class="{{ $isLate ? 'contract-row--overdue' : '' }}">
                                                                <td>{{ $d->no_kontrak }}</td>
                                                                <td>{{ $d->nik }}</td>
                                                                <td>{{ formatName($d->nama_karyawan) }}</td>
                                                                <td>{{ singkatString($d->nama_jabatan) }}</td>
                                                                <td>{{ $d->kode_dept }}</td>
                                                                <td>{{ textupperCase($d->kode_cabang) }}</td>
                                                                <td>{{ formatIndo($d->sampai) }}</td>
                                                                @if ($tab['showRemaining'])
                                                                    <td class="text-center">
                                                                        <span
                                                                            class="contract-pill {{ $isLate ? 'contract-pill--danger' : 'contract-pill--safe' }}">
                                                                            {{ $sisahari }} Hari
                                                                        </span>
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sertifikasi Expired -->
        <div class="row mt-3">
            <div class="col">
                <div class="card contract-card" style="border-left: 4px solid #ef4444;">
                    <div class="card-header contract-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar me-3">
                                <span class="avatar-initial rounded bg-label-danger">
                                    <i class="ti ti-certificate-off fs-4"></i>
                                </span>
                            </div>
                            <div>
                                <h4 class="mb-0">Sertifikasi / Pelatihan Expired</h4>
                                <small class="text-muted">Daftar sertifikasi karyawan yang telah melewati masa berlaku</small>
                            </div>
                        </div>
                        <span class="badge bg-label-danger rounded-pill mt-3 mt-lg-0">
                            Total {{ count($sertifikasi_expired) }} Sertifikasi
                        </span>
                    </div>
                    <div class="card-body">
                        @if (count($sertifikasi_expired) === 0)
                            <div class="contract-empty">
                                <i class="ti ti-shield-check fs-1 mb-2 d-block text-success"></i>
                                Tidak ada sertifikasi karyawan yang expired.
                            </div>
                        @else
                            <div class="table-responsive contract-table-wrapper mt-3">
                                <table class="table table-hover align-middle mb-0 contract-table" style="border-radius: 8px;">
                                    <thead class="table-dark" style="background: #7f1d1d;">
                                        <tr>
                                            <th>NIK</th>
                                            <th>Nama Karyawan</th>
                                            <th>Nama Sertifikasi</th>
                                            <th>Tanggal Expired</th>
                                            <th>Cabang</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sertifikasi_expired as $s)
                                            <tr>
                                                <td>{{ $s->nik }}</td>
                                                <td>{{ formatName($s->karyawan->nama_karyawan ?? 'Unknown') }}</td>
                                                <td><span class="fw-medium">{{ $s->nama_pelatihan }}</span></td>
                                                <td>
                                                    <span class="badge bg-label-danger">
                                                        {{ \Carbon\Carbon::parse($s->tanggal_expired)->format('d M Y') }}
                                                    </span>
                                                </td>
                                                <td>{{ textupperCase($s->karyawan->kode_cabang ?? '-') }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('karyawan-pelatihan.index', ['nama_karyawan' => $s->nik, 'status' => 'expired']) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Lihat Sertifikasi">
                                                        <i class="ti ti-external-link"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="row mb-2">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Status Karyawan</h4>
                    </div>
                    <div class="card-body">
                        {!! $chart->container() !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Pendidikan Karyawan</h4>
                    </div>
                    <div class="card-body">
                        {!! $pddchart->container() !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Jenis Kelamin</h4>
                    </div>
                    <div class="card-body">
                        {!! $jkchart->container() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('myscript')
<script src="{{ $chart->cdn() }}"></script>
{{ $chart->script() }}
{{ $jkchart->script() }}
{{ $pddchart->script() }}
<script>
    window.Config = {
        kirimUcapanBirthdayUrl: '{{ route('dashboard.kirim.ucapan.birthday') }}',
        csrfToken: '{{ csrf_token() }}'
    };
</script>
<script src="{{ asset('assets/js/dashboard.js') }}"></script>
@endpush

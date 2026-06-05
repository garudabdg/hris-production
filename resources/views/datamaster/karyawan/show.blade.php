@extends('layouts.app')
<link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-profile.css') }}" />
@section('titlepage', 'Karyawan')

@section('content')
@section('navigasi')
    <span class="text-muted">Karyawan/</span> Detail
@endsection
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="user-profile-header-banner">
                <div class="rounded-top" style="height: 250px; background-image: url('{{ asset('assets/img/bg-didimax.jpg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat; background-color: {{ $general_setting->theme_color_1 ?? '#005b9f' }};"></div>
            </div>
            <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                    @if (Storage::disk('public')->exists('/karyawan/' . $karyawan->foto))
                        <img src="{{ getfotoKaryawan($karyawan->foto) }}" alt="user image" class="d-block  ms-0 ms-sm-4 rounded " height="150"
                            width="140" style="object-fit: cover">
                    @else
                        <img src="{{ asset('assets/img/avatars/No_Image_Available.jpg') }}" alt="user image"
                            class="d-block h-auto ms-0 ms-sm-4 rounded user-profile-img" width="150">
                    @endif

                </div>
                <div class="flex-grow-1 mt-3 mt-sm-5">
                    <div
                        class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                        <div class="user-profile-info">
                            <h4 class="mb-2 fw-bold">{{ textCamelCase($karyawan->nama_karyawan) }}</h4>
                            <div class="d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2 mb-3">
                                <span class="badge bg-label-secondary" style="font-size: 12px;"><i class="ti ti-barcode me-1"></i> {{ $karyawan->nik_show ?? $karyawan->nik }}</span>
                                <span class="badge bg-label-primary" style="font-size: 12px;"><i class="ti ti-user me-1"></i> {{ textCamelCase($karyawan->nama_jabatan) }}</span>
                                <span class="badge bg-label-info" style="font-size: 12px;"><i class="ti ti-building-arch me-1"></i> {{ textCamelCase($karyawan->nama_dept) }}</span>
                                <span class="badge bg-label-warning" style="font-size: 12px;"><i class="ti ti-building me-1"></i> {{ textCamelCase($karyawan->nama_cabang) }}</span>
                            </div>
                            <ul class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-3 text-muted">
                                <li class="list-inline-item d-flex align-items-center gap-1">
                                    <i class="ti ti-calendar-event"></i>
                                    Bergabung: <span class="fw-medium text-dark">{{ !empty($karyawan->tanggal_masuk) ? DateToIndo($karyawan->tanggal_masuk) : '-' }}</span>
                                </li>
                                @if ($karyawan->status_aktif_karyawan === '0')
                                    <li class="list-inline-item d-flex align-items-center gap-1 text-danger">
                                        <i class="ti ti-calendar-off"></i>
                                        Nonaktif: <span class="fw-medium text-danger">{{ !empty($karyawan->tanggal_nonaktif) ? DateToIndo($karyawan->tanggal_nonaktif) : '-' }}</span>
                                    </li>
                                @endif
                                
                                @if ($karyawan->status_karyawan)
                                    @php
                                        $status_karyawan_text = $karyawan->status_karyawan == 'K' ? 'Kontrak' 
                                            : ($karyawan->status_karyawan == 'T' ? 'Tetap' 
                                            : ($karyawan->status_karyawan == 'M' ? 'Mitra' 
                                            : ($karyawan->status_karyawan == 'O' ? 'Outsourcing' 
                                            : $karyawan->status_karyawan)));
                                        $badge_class = $karyawan->status_karyawan == 'T' ? 'bg-label-success' : 'bg-label-primary';
                                    @endphp
                                    <li class="list-inline-item d-flex align-items-center gap-1">
                                        <i class="ti ti-briefcase"></i>
                                        Status: <span class="badge {{ $badge_class }} py-1 px-2" style="font-size: 10px;">{{ $status_karyawan_text }}</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="text-center text-md-end mt-3 mt-md-0 d-flex flex-column align-items-md-end align-items-center gap-2">
                            @if ($karyawan->status_aktif_karyawan === '1')
                                <span class="badge bg-success fs-6 px-3 py-2 shadow-sm rounded-pill">
                                    <i class="ti ti-check me-1"></i> Aktif
                                </span>
                            @else
                                <span class="badge bg-danger fs-6 px-3 py-2 shadow-sm rounded-pill">
                                    <i class="ti ti-x me-1"></i> Nonaktif
                                </span>
                            @endif
                            <a href="{{ route('karyawan.export-pdf', Crypt::encrypt($karyawan->nik)) }}" class="btn btn-sm btn-danger shadow-sm rounded-pill mt-1" target="_blank">
                                <i class="ti ti-file-type-pdf me-1"></i> Unduh PDF
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- User Profile Content -->
<div class="row">
    <div class="col-xl-4 col-lg-5 col-md-5">
        <!-- About User -->
        <div class="card mb-4">
            <div class="card-body">
                <small class="card-text text-uppercase text-muted small">Data Pribadi</small>
                <ul class="list-unstyled mb-4 mt-3">
                    <li class="d-flex align-items-center mb-3">
                        <i class="ti ti-barcode text-heading"></i><span class="fw-medium mx-2 text-heading">NIK:</span>
                        <span>{{ $karyawan->nik_show ?? $karyawan->nik }}</span>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <i class="ti ti-credit-card text-heading"></i><span class="fw-medium mx-2 text-heading">No.
                            KTP:</span>
                        <span>{{ $karyawan->no_ktp }}</span>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <i class="ti ti-user text-heading"></i><span class="fw-medium mx-2 text-heading">
                            Nama Lengkap:</span> <span>{{ textCamelCase($karyawan->nama_karyawan) }}</span>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <i class="ti ti-map-pin text-heading"></i><span class="fw-medium mx-2 text-heading">
                            Tempat Lahir:</span> <span>{{ textCamelCase($karyawan->tempat_lahir) }}</span>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <i class="ti ti-calendar text-heading"></i><span class="fw-medium mx-2 text-heading">
                            Tanggal Lahir:</span>
                        <span>{{ !empty($karyawan->tanggal_lahir) ? DateToIndo($karyawan->tanggal_lahir) : '' }}</span>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <i class="ti ti-gender-genderfluid text-heading"></i><span class="fw-medium mx-2 text-heading">
                            Jenis Kelamin:</span>
                        <span>{{ $karyawan->jenis_kelamin == 'L' ? 'Laki - Laki' : 'Perempuan' }}</span>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <i class="ti ti-friends text-heading"></i><span class="fw-medium mx-2 text-heading">
                            Status Kawin:</span>
                        <span>{{ $karyawan->keterangan_status_kawin }} </span>
                    </li>
                     <li class="d-flex align-items-center mb-3">
                        <i class="ti ti-school text-heading"></i><span class="fw-medium mx-2 text-heading">
                            Pendidikan:</span>
                        <span>{{ $karyawan->pendidikan_terakhir }} </span>
                    </li>
                    <li class="d-flex align-items-start mb-3">
                        <i class="ti ti-map text-heading mt-1"></i>
                        <span class="fw-medium mx-2 text-heading">
                            Alamat:
                        </span>
                        <span>{{ textCamelCase($karyawan->alamat) }}</span>
                    </li>
                    <li class="d-flex align-items-center mb-3">
                        <i class="ti ti-phone text-heading"></i><span class="fw-medium mx-2 text-heading">
                            No. HP:</span>
                        <span>{{ $karyawan->no_hp }}</span>
                    </li>
                </ul>
            </div>
        </div>
        <!--/ About User -->
        <!-- User Account -->
        <div class="card mb-4">
            <div class="card-body">
                <small class="card-text text-uppercase text-muted small">Akun Pengguna</small>
                @if ($user)
                    <ul class="list-unstyled mb-4 mt-3">
                        <li class="d-flex align-items-center mb-3">
                            <i class="ti ti-user-circle text-heading"></i><span class="fw-medium mx-2 text-heading">Username :</span>
                            <span>{{ $user->username }}</span>
                        </li>
                        <li class="d-flex align-items-center mb-3">
                            <i class="ti ti-mail text-heading"></i><span class="fw-medium mx-2 text-heading">Email :</span>
                            <span>{{ $user->email }}</span>
                        </li>
                        {{-- <li class="d-flex align-items-center mb-3">
                            <i class="ti ti-lock text-heading"></i><span class="fw-medium mx-2 text-heading">Password :</span>
                            <span>********</span>
                        </li> --}}
                    </ul>
                @else
                    <div class="alert alert-danger mt-4" role="alert">
                        User Belum di Buat
                    </div>
                @endif
            </div>
        </div>
        <!--/ User Account -->

        <!-- Aset & Tiket IT -->
        <div class="card mb-4">
            <div class="card-body">
                <small class="card-text text-uppercase text-muted small">Aset & Tiket IT</small>
                <ul class="list-unstyled mb-4 mt-3">
                    <li class="d-flex align-items-center mb-3">
                        <i class="ti ti-ticket text-heading"></i><span class="fw-medium mx-2 text-heading">Total Tiket Dibuat:</span>
                        <span class="badge bg-primary">{{ $total_tickets }} Tiket</span>
                    </li>
                    <li class="d-flex align-items-start mb-3">
                        <i class="ti ti-device-laptop text-heading mt-1"></i><span class="fw-medium mx-2 text-heading">Aset Dipegang:</span>
                        <div>
                            @if($assets->count() > 0)
                                @foreach($assets as $asset)
                                    <div class="mb-1"><span class="badge bg-label-info">{{ $asset->kode_asset }}</span> - {{ $asset->nama_asset }}</div>
                                @endforeach
                            @else
                                <span class="text-muted">Tidak ada aset</span>
                            @endif
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <!--/ Aset & Tiket IT -->

    </div>
    <div class="col-xl-8 col-lg-7 col-md-7">
        <!-- Employment Details -->
        <!-- Employment Details Removed as per request (Redundant with Header) -->

        <!-- Activity Timeline -->
        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-pills flex-column flex-sm-row mb-4">
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0);" onclick="showTab('face')"><i class="ti-xs ti ti-face-id me-1"></i> Wajah</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0);" onclick="showTab('mutation')"><i class="ti-xs ti ti-home-move me-1"></i>
                            Mutasi/Promosi/Demosi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0);" onclick="showTab('salary')"><i class="ti-xs ti ti-coins me-1"></i>
                            Gaji</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0);" onclick="showTab('allowance')"><i class="ti-xs ti ti-report-money me-1"></i> Tunjangan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:void(0);" onclick="showTab('pelatihan')"><i class="ti-xs ti ti-certificate me-1"></i> Pelatihan & Sertifikasi</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="row" id="face_completeness">
            <div class="col-md-12">
                <div class="card card-action mb-4">
                    <div class="card-header align-items-center d-flex justify-content-between">
                        <div>
                            <a href="#" class="btn btn-primary" id="btnAddface"><i class="ti ti-face-id me-1"></i> Tambah Wajah</a>
                        </div>
                        <div>
                            <form id="formHapusSemuaWajah" method="POST"
                                action="{{ route('facerecognition.destroyAll', Crypt::encrypt($karyawan->nik)) }}" style="display:inline">
                                @csrf
                                <button type="button" class="btn btn-danger" id="btnHapusSemuaWajah"><i class="ti ti-trash me-1"></i>Hapus Semua
                                    Wajah</button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($karyawan_wajah as $d)
                                @php
                                    $folder = $karyawan->nik . '-' . getNamaDepan(strtolower($karyawan->nama_karyawan));
                                    $url = url('/storage/uploads/facerecognition/' . $folder . '/' . $d->wajah);
                                    $timestamp = time();
                                    $urlWithTimestamp = $url . '?v=' . $timestamp;
                                @endphp
                                <div class="col-6 col-md-4 col-lg-3">
                                    <div class="card h-100">
                                        <div class="position-relative">
                                            <img src="{{ $urlWithTimestamp }}" class="card-img-top face-image" alt="Foto Wajah"
                                                style="height: 200px; object-fit: cover; cursor: pointer;" data-bs-toggle="modal"
                                                data-bs-target="#modalFotoWajah" data-image="{{ $urlWithTimestamp }}">
                                            <div class="position-absolute top-0 end-0 p-2">
                                                <form method="POST" name="deleteform" class="deleteform d-inline"
                                                    action="{{ route('facerecognition.delete', Crypt::encrypt($d->id)) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="#" class="delete-confirm">
                                                        <i class="ti ti-trash text-danger bg-white rounded-circle p-1"></i>
                                                    </a>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Activity Timeline -->
        
        <!-- Mutation History -->
        <div class="row" style="display: none;" id="mutation_completeness">
            <div class="col-md-12">
               @if($mutasi->isEmpty())
                <div class="alert alert-info">Belum ada data riwayat mutasi/promosi/demosi.</div>
               @else
                 @foreach ($mutasi as $d)
                    <div class="card mb-2 shadow-sm border">
                        <div class="card-body p-2">
                            <div class="row align-items-center">
                                <!-- Identity -->
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="ti ti-calendar me-1 text-muted"></i>
                                        <span class="fw-bold text-dark" style="font-size: 13px;">{{ date('d-m-Y', strtotime($d->tanggal_mutasi)) }}</span>
                                    </div>
                                    <div>
                                        @if ($d->jenis_mutasi == 'MUTASI')
                                            <span class="badge bg-info" style="font-size: 10px;">Mutasi</span>
                                        @elseif ($d->jenis_mutasi == 'PROMOSI')
                                            <span class="badge bg-success" style="font-size: 10px;">Promosi</span>
                                        @else
                                            <span class="badge bg-warning" style="font-size: 10px;">Demosi</span>
                                        @endif
                                    </div>
                                </div>
                                <!-- Mutation Details -->
                                <div class="col-md-5">
                                    <div class="d-flex align-items-center justify-content-start" style="font-size: 11px;">
                                        <div class="text-secondary text-end pe-2" style="width: 45%;">
                                            <div class="fw-bold">{{ $d->cabangLama->nama_cabang ?? '-' }}</div>
                                            <div>{{ $d->deptLama->nama_dept ?? '-' }}</div>
                                            <div>{{ $d->jabatanLama->nama_jabatan ?? '-' }}</div>
                                        </div>
                                        <div class="text-center px-1" style="width: 10%;">
                                            <i class="ti ti-arrow-right text-primary"></i>
                                        </div>
                                        <div class="text-primary ps-2" style="width: 45%;">
                                            <div class="fw-bold">{{ $d->cabangBaru->nama_cabang ?? '-' }}</div>
                                            <div>{{ $d->deptBaru->nama_dept ?? '-' }}</div>
                                            <div>{{ $d->jabatanBaru->nama_jabatan ?? '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Keterangan & SK -->
                                <div class="col-md-3">
                                    <div class="text-muted mb-1" style="font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">
                                        <em>{{ $d->keterangan ?? 'Tidak ada keterangan' }}</em>
                                    </div>
                                    @if ($d->doc_sk)
                                        <a href="{{ asset('storage/uploads/mutasi/' . $d->doc_sk) }}" target="_blank" class="text-primary" style="font-size: 11px;">
                                            <i class="ti ti-file-text me-1"></i> Lihat SK
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
               @endif
            </div>
        </div>
        <!--/ Mutation History -->

        <!-- Pelatihan & Sertifikasi -->
        <div class="row" style="display: none;" id="pelatihan_completeness">
            <div class="col-md-12">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header border-bottom d-flex justify-content-between align-items-center bg-transparent pt-3 pb-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="ti ti-certificate me-2"></i>Daftar Pelatihan & Sertifikasi</h5>
                        @can('karyawan.edit')
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahPelatihan">
                            <i class="ti ti-plus me-1"></i> Tambah Data
                        </button>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        @if($karyawan->pelatihan && $karyawan->pelatihan->isEmpty())
                            <div class="text-center p-5">
                                <i class="ti ti-certificate text-muted mb-3" style="font-size: 4rem; opacity: 0.5;"></i>
                                <h6 class="text-muted">Belum ada riwayat pelatihan atau sertifikasi</h6>
                                <p class="text-muted small mb-0">Klik tombol Tambah Data untuk memasukkan riwayat baru.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle mb-0 text-nowrap">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Nama Pelatihan/Sertifikasi</th>
                                            <th>Tgl Pelatihan</th>
                                            <th>Tgl Expired</th>
                                            <th>Status</th>
                                            <th class="text-center">Sertifikat</th>
                                            @can('karyawan.edit')
                                            <th class="text-center pe-4">Aksi</th>
                                            @endcan
                                        </tr>
                                    </thead>
                                    <tbody class="table-border-bottom-0">
                                        @foreach($karyawan->pelatihan ?? [] as $pelatihan)
                                        <tr>
                                            <td class="ps-4 fw-medium text-dark">{{ $pelatihan->nama_pelatihan }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ti ti-calendar-event text-muted me-2" style="font-size: 1.1rem;"></i>
                                                    {{ $pelatihan->tanggal_pelatihan ? $pelatihan->tanggal_pelatihan->format('d M Y') : '-' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ti ti-calendar-off text-muted me-2" style="font-size: 1.1rem;"></i>
                                                    {{ $pelatihan->tanggal_expired ? $pelatihan->tanggal_expired->format('d M Y') : '-' }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($pelatihan->tanggal_expired)
                                                    @if(\Carbon\Carbon::now()->gt($pelatihan->tanggal_expired))
                                                        <span class="badge bg-label-danger"><i class="ti ti-alert-circle me-1"></i>Expired</span>
                                                    @else
                                                        <span class="badge bg-label-success"><i class="ti ti-check me-1"></i>Active</span>
                                                    @endif
                                                @else
                                                    <span class="badge bg-label-info"><i class="ti ti-infinity me-1"></i>Lifetime</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($pelatihan->file_sertifikat)
                                                    <a href="{{ Storage::url('uploads/pelatihan/' . $pelatihan->file_sertifikat) }}" target="_blank" class="btn btn-sm btn-icon btn-label-info" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Sertifikat">
                                                        <i class="ti ti-file-text"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted small"><i>-</i></span>
                                                @endif
                                            </td>
                                            @can('karyawan.edit')
                                            <td class="text-center pe-4">
                                                <button type="button" class="btn btn-sm btn-icon btn-label-primary btnEditPelatihan" data-id="{{ $pelatihan->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Data">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <form action="{{ route('karyawan-pelatihan.destroy', $pelatihan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data sertifikasi ini? File dokumen juga akan terhapus secara permanen.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-label-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Data">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            @endcan
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
        <!--/ Pelatihan & Sertifikasi -->

    </div>
</div>
<x-modal-form id="modal" show="loadmodal" size="modal-lg" />
<!--/ User Profile Content -->

<!-- Modal Tambah Pelatihan -->
<div class="modal fade" id="modalTambahPelatihan" tabindex="-1" aria-labelledby="modalTambahPelatihanLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('karyawan-pelatihan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="nik" value="{{ $karyawan->nik }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahPelatihanLabel">Tambah Pelatihan/Sertifikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Pelatihan/Sertifikasi</label>
                        <input type="text" class="form-control" name="nama_pelatihan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pelatihan</label>
                        <input type="date" class="form-control" name="tanggal_pelatihan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Expired (Kosongkan jika seumur hidup)</label>
                        <input type="date" class="form-control" name="tanggal_expired">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload Sertifikat (PDF/JPG/PNG)</label>
                        <input type="file" class="form-control" name="file_sertifikat" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnHapusSemua = document.getElementById('btnHapusSemuaWajah');
        if (btnHapusSemua) {
            btnHapusSemua.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Yakin ingin menghapus SEMUA data wajah karyawan ini?')) {
                    document.getElementById('formHapusSemuaWajah').submit();
                }
            });
        }
    });
</script>

<!-- Modal Foto Wajah -->
<div class="modal fade" id="modalFotoWajah" tabindex="-1" aria-labelledby="modalFotoWajahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFotoWajahLabel">Foto Wajah</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <img src="" id="modalImage" class="img-fluid" alt="Foto Wajah">
            <div class="modal-body text-center">

            </div>
        </div>
    </div>
</div>

@endsection
@push('myscript')
<script src="{{ asset('assets/external/js/face-model-cache.js') }}"></script>
<style>

    .nav-pills .nav-link.active,
    .nav-pills .show>.nav-link {
        background-color: {{ $general_setting->theme_color_1 }} !important;
        color: #fff !important;
        box-shadow: 0 2px 4px 0 rgba(15, 77, 58, 0.4);
    }
</style>
<script>
    function showTab(tab) {
        // Hide all tabs
        $("#face_completeness").hide();
        $("#mutation_completeness").hide();
        $("#pelatihan_completeness").hide();
        
        // Remove active class from all nav links
        $(".nav-link").removeClass("active");

        // Show selected tab and set active class
        if (tab == 'face') {
            $("#face_completeness").show();
            $(".nav-link:contains('Wajah')").addClass("active");
        } else if (tab == 'mutation') {
            $("#mutation_completeness").show();
            $(".nav-link:contains('Mutasi')").addClass("active");
        } else if (tab == 'pelatihan') {
            $("#pelatihan_completeness").show();
            $(".nav-link:contains('Pelatihan')").addClass("active");
        } else if (tab == 'salary') {
             // Future implementation
             $(".nav-link:contains('Gaji')").addClass("active");
        } else if (tab == 'allowance') {
             // Future implementation
             $(".nav-link:contains('Tunjangan')").addClass("active");
        }
    }

    $("#btnAddface").click(function(e) {
        e.preventDefault();
        $('#modal').modal("show");
        // $('#modal').find(".modal-title").text("Tambah Wajah");
        $("#loadmodal").html(`<div class="sk-wave sk-primary" style="margin:auto">
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            <div class="sk-wave-rect"></div>
            </div>`);
        $("#loadmodal").load('/facerecognition/' + '{{ Crypt::encrypt($karyawan->nik) }}' + '/create');
    });

    // Event listener untuk modal foto wajah
    document.addEventListener('DOMContentLoaded', function() {
        const modalFotoWajah = document.getElementById('modalFotoWajah');
        if (modalFotoWajah) {
            modalFotoWajah.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const imageUrl = button.getAttribute('data-image');

                const modalImage = this.querySelector('#modalImage');
                modalImage.src = imageUrl;
            });
        }

        // Clear cache ketika wajah dihapus (individual)
        const deleteForms = document.querySelectorAll('.deleteform');
        deleteForms.forEach(form => {
            form.addEventListener('submit', async function(e) {
                const nik = '{{ $karyawan->nik }}';
                // Clear cache descriptors untuk NIK ini
                if (window.FaceModelCache && typeof window.FaceModelCache.clearDescriptors === 'function') {
                    try {
                        await window.FaceModelCache.clearDescriptors(nik);
                        console.log(`[Face Cache] Cleared descriptors for ${nik} after face deletion`);
                    } catch (error) {
                        console.warn(`[Face Cache] Failed to clear cache:`, error);
                    }
                }
            });
        });

        // Clear cache ketika semua wajah dihapus
        const btnHapusSemua = document.getElementById('btnHapusSemuaWajah');
        if (btnHapusSemua) {
            btnHapusSemua.addEventListener('click', async function(e) {
                const nik = '{{ $karyawan->nik }}';
                // Clear cache descriptors untuk NIK ini sebelum submit
                if (window.FaceModelCache && typeof window.FaceModelCache.clearDescriptors === 'function') {
                    try {
                        await window.FaceModelCache.clearDescriptors(nik);
                        console.log(`[Face Cache] Cleared descriptors for ${nik} before deleting all faces`);
                    } catch (error) {
                        console.warn(`[Face Cache] Failed to clear cache:`, error);
                    }
                }
            });
        }
    });

    $(document).ready(function() {
        $('.btnEditPelatihan').click(function() {
            var id = $(this).data('id');
            $('#modalEditPelatihan').modal('show');
            $('#loadmodalEditPelatihan').html('<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            $('#loadmodalEditPelatihan').load(`/karyawan-pelatihan/${id}/edit`);
        });
    });
</script>

<!-- Modal Edit Pelatihan -->
<div class="modal fade" id="modalEditPelatihan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Pelatihan / Sertifikasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="loadmodalEditPelatihan">
                <div class="d-flex justify-content-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush

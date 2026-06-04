@extends('layouts.app')
@section('titlepage', 'Data Sertifikasi Karyawan')

@section('content')
@section('navigasi')
    <div class="d-flex justify-content-between align-items-center w-100">
        <div>
            Sertifikasi Karyawan
            <div class="text-muted mt-1" style="font-size: 0.75rem; font-weight: normal; text-transform: none; letter-spacing: 0px;">
                Manajemen data pelatihan dan sertifikasi seluruh karyawan.
            </div>
        </div>
        <nav aria-label="breadcrumb" class="d-none d-md-block" style="font-size: 0.75rem;">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('dashboard.index') }}">
                        <i class="ti ti-home-2 ti-xs"></i>
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="javascript:void(0);">
                        <i class="ti ti-database ti-xs me-1"></i> Data Master
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <i class="ti ti-certificate ti-xs me-1"></i> Sertifikasi
                </li>
            </ol>
        </nav>
    </div>
@endsection

<div class="row">
    <div class="col-lg-12 col-sm-12 col-xs-12">
        <div class="card">
            <div class="card-header border-bottom">
                <form action="{{ route('karyawan-pelatihan.index') }}" method="GET">
                    <div class="row g-2">
                        <div class="col-lg-4 col-md-6 col-sm-12">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                                <input type="text" name="nama_karyawan" class="form-control" placeholder="Cari Nama / NIK..." value="{{ request('nama_karyawan') }}" aria-label="Cari Nama / NIK..." aria-describedby="basic-addon-search31" />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-4 col-sm-12">
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif / Lifetime</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary"><i class="ti ti-search me-1"></i> Cari</button>
                                <a href="{{ route('karyawan-pelatihan.index') }}" class="btn btn-secondary"><i class="ti ti-refresh"></i></a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="table-responsive text-nowrap">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Karyawan</th>
                            <th>Nama Pelatihan / Sertifikasi</th>
                            <th>Tanggal Pelatihan</th>
                            <th>Berlaku Sampai</th>
                            <th>Status</th>
                            <th>Sertifikat</th>
                            @can('pelatihan.delete')
                            <th>Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse ($pelatihan as $d)
                            <tr>
                                <td>
                                    <div class="d-flex justify-content-start align-items-center gap-3">
                                        <div class="avatar avatar-sm">
                                            @if (!empty($d->karyawan->foto) && Storage::disk('public')->exists('/karyawan/' . $d->karyawan->foto))
                                                <img src="{{ getfotoKaryawan($d->karyawan->foto) }}" alt="Avatar" class="rounded-circle" style="object-fit: cover; width: 40px; height: 40px;">
                                            @else
                                                <img src="{{ asset('assets/img/avatars/No_Image_Available.jpg') }}" alt="Avatar" class="rounded-circle" style="object-fit: cover; width: 40px; height: 40px;">
                                            @endif
                                        </div>
                                        <div class="d-flex flex-column">
                                            <a href="{{ route('karyawan.show', Crypt::encrypt($d->nik)) }}" class="text-body text-truncate fw-bold">{{ textCamelCase($d->karyawan->nama_karyawan ?? 'Unknown') }}</a>
                                            <small class="text-muted">{{ $d->nik }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $d->nama_pelatihan }}</span>
                                </td>
                                <td>{{ $d->tanggal_pelatihan ? $d->tanggal_pelatihan->format('d M Y') : '-' }}</td>
                                <td>{{ $d->tanggal_expired ? $d->tanggal_expired->format('d M Y') : 'Seumur Hidup' }}</td>
                                <td>
                                    @php
                                        $is_active = !$d->tanggal_expired || \Carbon\Carbon::now()->lt($d->tanggal_expired);
                                    @endphp
                                    @if($is_active)
                                        <span class="badge bg-label-success">Aktif</span>
                                    @else
                                        <span class="badge bg-label-danger">Expired</span>
                                    @endif
                                </td>
                                <td>
                                    @if($d->file_sertifikat)
                                        <a href="{{ Storage::url('uploads/pelatihan/' . $d->file_sertifikat) }}" target="_blank" class="btn btn-sm btn-icon btn-label-info" data-bs-toggle="tooltip" title="Lihat Sertifikat">
                                            <i class="ti ti-download"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @can('pelatihan.edit')
                                    <button class="btn btn-sm btn-icon btn-label-primary btnEditPelatihan" data-id="{{ $d->id }}" data-bs-toggle="tooltip" title="Edit Data">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    @endcan
                                    @can('pelatihan.delete')
                                    <form action="{{ route('karyawan-pelatihan.destroy', $d->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus sertifikasi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-icon btn-label-danger" data-bs-toggle="tooltip" title="Hapus"><i class="ti ti-trash"></i></button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center p-4">
                                    <i class="ti ti-certificate text-muted mb-2" style="font-size: 3rem; opacity: 0.5;"></i>
                                    <p class="text-muted mb-0">Belum ada data pelatihan/sertifikasi yang ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-end">
                {{ $pelatihan->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit -->
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
@endsection

@push('myscript')
<script>
    $(document).ready(function() {
        $('.btnEditPelatihan').click(function() {
            var id = $(this).data('id');
            $('#modalEditPelatihan').modal('show');
            $('#loadmodalEditPelatihan').html('<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            $('#loadmodalEditPelatihan').load(`/karyawan-pelatihan/${id}/edit`);
        });
    });
</script>
@endpush

@extends('layouts.app')
@section('titlepage', 'Peminjaman Aset')

@section('content')
@section('navigasi')
    <span>Peminjaman Aset</span>
@endsection

<div class="row">
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-header p-3 bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="m-0 font-weight-bold text-primary"><i class="ti ti-package-export me-2"></i>Data Peminjaman Aset</h5>
                <div class="d-flex gap-2">
                    @can('approvallayer.index')
                        <a href="{{ route('approvallayer.index') }}" class="btn btn-info btn-sm">
                            <i class="ti ti-settings me-1"></i>Konfigurasi Approval
                        </a>
                    @endcan
                    @can('asset.pinjam.create')
                        <a href="#" class="btn btn-primary btn-sm" id="btnCreate">
                            <i class="fa fa-plus me-1"></i>Tambah Peminjaman
                        </a>
                    @endcan
                </div>
            </div>

            <div class="card-body p-3 bg-white">
                {{-- Filter --}}
                <form action="{{ route('asset-pinjam.index') }}" method="GET">
                    <div class="row g-2 mb-3">
                        <div class="col-lg-4 col-md-6">
                            <input type="text" name="nama_karyawan" value="{{ Request('nama_karyawan') }}"
                                class="form-control" placeholder="Cari nama karyawan...">
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="0" {{ Request('status') === '0' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                                <option value="1" {{ Request('status') === '1' ? 'selected' : '' }}>Sedang Dipinjam</option>
                                <option value="2" {{ Request('status') === '2' ? 'selected' : '' }}>Ditolak</option>
                                <option value="3" {{ Request('status') === '3' ? 'selected' : '' }}>Dikembalikan</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <select name="kode_cabang" class="form-select">
                                <option value="">Semua Cabang</option>
                                @foreach ($cabang as $c)
                                    <option value="{{ $c->kode_cabang }}" {{ Request('kode_cabang') == $c->kode_cabang ? 'selected' : '' }}>
                                        {{ $c->nama_cabang }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">
                                <i class="ti ti-search me-1"></i>Cari
                            </button>
                            <a href="{{ route('asset-pinjam.index') }}" class="btn btn-secondary btn-sm flex-fill">
                                <i class="ti ti-refresh me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {!! session('success') !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {!! session('error') !!}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Peminjam</th>
                                <th>Aset</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th>Status</th>
                                <th>Menunggu</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pinjam as $d)
                                @php
                                    $approvalService = app(\App\Services\ApprovalService::class);
                                    $userRole        = auth()->user()->getRoleNames()->first();
                                    $nextLayer       = $d->getNextApprovalLayer();
                                    $canApprove      = false;
                                    if (auth()->user()->isSuperAdmin() || ($nextLayer && $nextLayer->role_name == $userRole)) {
                                        $canApprove = true;
                                    }
                                    $canCancel = false;
                                    if ($d->approval_step > 1 && $d->status == 0) {
                                        $lastStep = $d->approval_step - 1;
                                        $lastApproval = $d->approvals->where('level', $lastStep)->where('user_id', auth()->id())->first();
                                        if ($lastApproval) $canCancel = true;
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration + ($pinjam->currentPage() - 1) * $pinjam->perPage() }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @php $path = Storage::url('karyawan/' . $d->foto); @endphp
                                            @if (!empty($d->foto) && Storage::disk('public')->exists('/karyawan/' . $d->foto))
                                                <img src="{{ $path }}" alt="Foto" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                            @else
                                                <img src="{{ asset('assets/img/avatars/No_Image_Available.jpg') }}" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                            @endif
                                            <div>
                                                <div class="fw-semibold" style="font-size:13px;">{{ $d->nama_karyawan }}</div>
                                                <div class="text-muted" style="font-size:11px;">{{ $d->nama_dept }} · {{ $d->nama_cabang }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold" style="font-size:13px;">{{ $d->nama_asset }}</div>
                                        <div class="text-muted" style="font-size:11px;">{{ $d->kode_asset }}</div>
                                    </td>
                                    <td style="font-size:12px;">{{ \Carbon\Carbon::parse($d->tanggal_pinjam)->format('d/m/Y') }}</td>
                                    <td style="font-size:12px;">
                                        {{ \Carbon\Carbon::parse($d->tanggal_kembali_rencana)->format('d/m/Y') }}
                                        @if ($d->tanggal_kembali_aktual)
                                            <div class="text-success" style="font-size:11px;">
                                                Kembali: {{ \Carbon\Carbon::parse($d->tanggal_kembali_aktual)->format('d/m/Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        {!! $d->status_badge !!}
                                        @if ($d->status == 0 && $d->waiting_role)
                                            <div class="text-muted mt-1" style="font-size:10px;">Menunggu: {{ $d->waiting_role }}</div>
                                        @endif
                                    </td>
                                    <td style="font-size:11px;">
                                        @if ($d->status == 0)
                                            <span class="text-warning">{{ $d->waiting_role ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="#" class="btn btn-sm btn-outline-secondary py-1 px-2 btnShow"
                                                data-id="{{ Crypt::encrypt($d->id) }}" title="Detail">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            @can('asset.pinjam.approve')
                                                @if ($d->status == 0)
                                                    @if ($canApprove)
                                                        <a href="#" class="btn btn-sm btn-outline-primary py-1 px-2 btnApprove"
                                                            data-id="{{ Crypt::encrypt($d->id) }}" title="Approve">
                                                            <i class="ti ti-check"></i>
                                                        </a>
                                                    @endif
                                                    @if ($canCancel)
                                                        <form method="POST" action="{{ route('asset-pinjam.cancelapprove', Crypt::encrypt($d->id)) }}" class="d-inline cancel-confirm-form">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-warning py-1 px-2" title="Batalkan Approval">
                                                                <i class="ti ti-arrow-back-up"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @elseif ($d->status == 1)
                                                    <a href="#" class="btn btn-sm btn-outline-success py-1 px-2 btnKembali"
                                                        data-id="{{ Crypt::encrypt($d->id) }}" title="Catat Pengembalian">
                                                        <i class="ti ti-package-import"></i>
                                                    </a>
                                                @endif
                                            @endcan
                                            @can('asset.pinjam.delete')
                                                @if ($d->status != 1)
                                                    <form method="POST" action="{{ route('asset-pinjam.destroy', Crypt::encrypt($d->id)) }}" class="d-inline delete-confirm-form">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" title="Hapus">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Tidak ada data peminjaman.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $pinjam->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<x-modal-form id="modal" show="loadmodal" />

@endsection

@push('myscript')
<script>
    // Tombol Create
    $('#btnCreate').click(function(e) {
        e.preventDefault();
        $('#loadmodal').html('<div class="sk-wave sk-primary" style="margin:auto"><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div></div>');
        $('#modal').modal('show');
        $('.modal-title').text('Tambah Peminjaman Aset');
        $('#loadmodal').load("{{ route('asset-pinjam.create') }}");
    });

    // Tombol Show
    $(document).on('click', '.btnShow', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        $('#loadmodal').html('<div class="sk-wave sk-primary" style="margin:auto"><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div></div>');
        $('#modal').modal('show');
        $('.modal-title').text('Detail Peminjaman Aset');
        $('#loadmodal').load(`/asset-pinjam/${id}/show`);
    });

    // Tombol Approve
    $(document).on('click', '.btnApprove', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        $('#loadmodal').html('<div class="sk-wave sk-primary" style="margin:auto"><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div></div>');
        $('#modal').modal('show');
        $('.modal-title').text('Persetujuan Peminjaman Aset');
        $('#loadmodal').load(`/asset-pinjam/${id}/approve`);
    });

    // Tombol Kembali
    $(document).on('click', '.btnKembali', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        $('#loadmodal').html('<div class="sk-wave sk-primary" style="margin:auto"><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div></div>');
        $('#modal').modal('show');
        $('.modal-title').text('Catat Pengembalian Aset');
        $('#loadmodal').load(`/asset-pinjam/${id}/kembali`);
    });

    // Konfirmasi Cancel Approval
    $(document).on('submit', '.cancel-confirm-form', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Batalkan Approval?',
            text: 'Approval pada tahap sebelumnya akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });

    // Konfirmasi Hapus
    $(document).on('submit', '.delete-confirm-form', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Hapus Data?',
            text: 'Data peminjaman ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
</script>
@endpush

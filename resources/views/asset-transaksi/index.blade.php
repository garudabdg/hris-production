@extends('layouts.app')
@section('titlepage', 'Transaksi Barang')

@section('content')
@section('navigasi')
    <span>Transaksi Barang In / Out</span>
@endsection

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-circle-check me-2"></i>{!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ti ti-alert-circle me-2"></i>{!! session('error') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar">
                    <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-arrows-exchange fs-4"></i></span>
                </div>
                <div>
                    <p class="mb-0 small text-muted">Total Transaksi</p>
                    <h4 class="mb-0">{{ $summary['total'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar">
                    <span class="avatar-initial rounded bg-label-success"><i class="ti ti-package-import fs-4"></i></span>
                </div>
                <div>
                    <p class="mb-0 small text-muted">Barang Masuk</p>
                    <h4 class="mb-0">{{ $summary['barang_in'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar">
                    <span class="avatar-initial rounded bg-label-danger"><i class="ti ti-package-export fs-4"></i></span>
                </div>
                <div>
                    <p class="mb-0 small text-muted">Barang Keluar</p>
                    <h4 class="mb-0">{{ $summary['barang_out'] }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main Card --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="mb-0"><i class="ti ti-arrows-exchange me-2"></i>Riwayat Transaksi Barang</h5>
            <small class="text-muted">Pencatatan barang masuk dan barang keluar</small>
        </div>
        <div class="d-flex gap-2">
            @if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.transaksi.create'))
                <a href="#" class="btn btn-primary btn-sm" id="btnCreate">
                    <i class="ti ti-plus me-1"></i>Tambah Transaksi
                </a>
            @endif
        </div>
    </div>

    {{-- Filter --}}
    <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('asset-transaksi.index') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Cari kode / aset / PIC..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="tipe" class="form-select form-select-sm">
                    <option value="">Semua Tipe</option>
                    <option value="in" {{ request('tipe') == 'in' ? 'selected' : '' }}>Barang Masuk</option>
                    <option value="out" {{ request('tipe') == 'out' ? 'selected' : '' }}>Barang Keluar</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="kode_cabang" class="form-select form-select-sm">
                    <option value="">Semua Cabang</option>
                    @foreach ($cabang as $c)
                        <option value="{{ $c->kode_cabang }}" {{ request('kode_cabang') == $c->kode_cabang ? 'selected' : '' }}>
                            {{ textUpperCase($c->nama_cabang) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="dari" class="form-control form-control-sm flatpickr-date"
                    placeholder="Dari tanggal" value="{{ request('dari') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="sampai" class="form-control form-control-sm flatpickr-date"
                    placeholder="Sampai tanggal" value="{{ request('sampai') }}">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-search"></i></button>
                <a href="{{ route('asset-transaksi.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-x"></i></a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Tipe</th>
                    <th>Aset</th>
                    <th>Kategori</th>
                    <th class="text-center">Jumlah</th>
                    <th>Tanggal</th>
                    <th>Cabang</th>
                    <th>PIC</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $t)
                    <tr>
                        <td>{{ $transactions->firstItem() + $loop->index }}</td>
                        <td><code>{{ $t->kode_transaksi }}</code></td>
                        <td>{!! $t->tipe_badge !!}</td>
                        <td>
                            <div>
                                <span class="fw-semibold">{{ $t->asset->nama_asset ?? '-' }}</span>
                                <br><small class="text-muted">{{ $t->kode_asset }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $t->tipe === 'in' ? 'info' : 'warning' }}">
                                {{ $t->kategori_label }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold {{ $t->tipe === 'in' ? 'text-success' : 'text-danger' }}">
                                {{ $t->tipe === 'in' ? '+' : '-' }}{{ $t->jumlah }}
                            </span>
                        </td>
                        <td>{{ $t->tanggal_transaksi->format('d/m/Y') }}</td>
                        <td>{{ optional($t->cabang)->nama_cabang ?? '-' }}</td>
                        <td>{{ $t->penanggung_jawab ?? '-' }}</td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="#" class="btn btn-sm btn-outline-info btnShow" data-id="{{ $t->id }}" title="Detail">
                                    <i class="ti ti-eye"></i>
                                </a>
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.transaksi.delete'))
                                    <form method="POST" action="{{ route('asset-transaksi.destroy', $t->id) }}" class="d-inline delete-confirm-form">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="ti ti-package-off fs-2 d-block mb-2"></i>
                            Belum ada transaksi barang.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($transactions->hasPages())
        <div class="card-footer">{{ $transactions->links() }}</div>
    @endif
</div>

<x-modal-form id="modal" show="loadmodal" />

@endsection

@push('myscript')
<script>
$(function () {
    // Tombol Create
    $('#btnCreate').click(function(e) {
        e.preventDefault();
        $('#loadmodal').html('<div class="sk-wave sk-primary" style="margin:auto"><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div></div>');
        $('#modal').modal('show');
        $('.modal-title').text('Tambah Transaksi Barang');
        $('#loadmodal').load("{{ route('asset-transaksi.create') }}");
    });

    // Tombol Show
    $(document).on('click', '.btnShow', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        $('#loadmodal').html('<div class="sk-wave sk-primary" style="margin:auto"><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div><div class="sk-wave-rect"></div></div>');
        $('#modal').modal('show');
        $('.modal-title').text('Detail Transaksi Barang');
        $('#loadmodal').load(`/asset-transaksi/${id}/show`);
    });

    // Konfirmasi Hapus
    $(document).on('submit', '.delete-confirm-form', function(e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: 'Hapus Transaksi?',
            text: 'Data transaksi akan dihapus dan stok akan dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush

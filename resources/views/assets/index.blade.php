@extends('layouts.app')
@section('titlepage', 'Manajemen Aset')

@section('content')
@section('navigasi')
    <span>Manajemen Aset</span>
@endsection

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('import_errors'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="ti ti-alert-triangle me-2"></i><strong>Import selesai dengan beberapa peringatan:</strong>
        <ul class="mb-0 mt-1">
            @foreach (session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar">
                    <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-package fs-4"></i></span>
                </div>
                <div>
                    <p class="mb-0 small text-muted">Total Aset</p>
                    <h4 class="mb-0">{{ $summary['total'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar">
                    <span class="avatar-initial rounded bg-label-success"><i class="ti ti-circle-check fs-4"></i></span>
                </div>
                <div>
                    <p class="mb-0 small text-muted">Tersedia</p>
                    <h4 class="mb-0">{{ $summary['tersedia'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar">
                    <span class="avatar-initial rounded bg-label-warning"><i class="ti ti-arrow-up-right fs-4"></i></span>
                </div>
                <div>
                    <p class="mb-0 small text-muted">Dipinjam</p>
                    <h4 class="mb-0">{{ $summary['dipinjam'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar">
                    <span class="avatar-initial rounded bg-label-danger"><i class="ti ti-tool fs-4"></i></span>
                </div>
                <div>
                    <p class="mb-0 small text-muted">Dalam Perbaikan</p>
                    <h4 class="mb-0">{{ $summary['dalam_perbaikan'] }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="mb-0"><i class="ti ti-package me-2"></i>Daftar Aset</h5>
            <small class="text-muted">Kelola seluruh aset perusahaan</small>
        </div>
        <div class="d-flex gap-2">
            @if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.kategori.index'))
            <a href="{{ route('assets.kategori.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-tags me-1"></i> Kategori
            </a>
            @endif
            @if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.export'))
            <a href="{{ route('assets.export', request()->all()) }}" class="btn btn-outline-success btn-sm">
                <i class="ti ti-file-spreadsheet me-1"></i> Export Excel
            </a>
            @endif
            @if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.import'))
            <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalImport">
                <i class="ti ti-upload me-1"></i> Import
            </button>
            @endif
            @if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.create'))
            <a href="{{ route('assets.create') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i> Tambah Aset
            </a>
            @endif
        </div>
    </div>
    <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('assets.index') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Cari nama / kode / merk..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nama_kategori }}
                        </option>
                    @endforeach
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
                <select name="kondisi" class="form-select form-select-sm">
                    <option value="">Semua Kondisi</option>
                    <option value="baik" {{ request('kondisi') == 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="rusak" {{ request('kondisi') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                    <option value="dalam_perbaikan" {{ request('kondisi') == 'dalam_perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                    <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="tidak_aktif" {{ request('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-search"></i></button>
                <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-x"></i></a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Aset</th>
                    <th>Kategori</th>
                    <th>Cabang</th>
                    <th>Merk / No. Seri</th>
                    <th>Kondisi</th>
                    <th>Status</th>
                    <th>Stok</th>
                    <th>Valuation</th>
                    <th>Harga Pembelian</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assets as $a)
                    <tr style="cursor: pointer;" onclick="if(!event.target.closest('a') && !event.target.closest('button') && !event.target.closest('form')) { @if(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.show')) window.location.href='{{ route('assets.show', $a->id) }}'; @endif }">
                        <td>{{ $assets->firstItem() + $loop->index }}</td>
                        <td>
                            <a href="{{ route('assets.barcode', $a->id) }}" target="_blank" title="Print Barcode" class="text-decoration-none">
                                <code>{{ $a->kode_asset }}</code>
                                <i class="ti ti-barcode ms-1 text-muted" style="font-size:12px;"></i>
                            </a>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if ($a->foto && Storage::disk('public')->exists('assets/' . $a->foto))
                                    <img src="{{ asset('storage/assets/' . $a->foto) }}"
                                        class="rounded" width="36" height="36" style="object-fit:cover;">
                                @else
                                    <div class="avatar" style="width:36px;height:36px;">
                                        <span class="avatar-initial rounded bg-label-secondary" style="font-size:14px;">
                                            <i class="ti ti-package"></i>
                                        </span>
                                    </div>
                                @endif
                                <div>
                                    <span class="fw-semibold">{{ $a->nama_asset }}</span>
                                    @if ($a->lokasi)
                                        <br><small class="text-muted"><i class="ti ti-map-pin me-1"></i>{{ $a->lokasi }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $a->category->nama_kategori ?? '-' }}</td>
                        <td>
                            {{ optional($a->cabang)->nama_cabang ?? '-' }}
                            @if ($a->pic)
                                <br><small class="text-muted" title="Pemilik / Penanggung Jawab"><i class="ti ti-user me-1" style="font-size:11px;"></i>{{ $a->pic->nama_karyawan }}</small>
                            @endif
                        </td>
                        <td>
                            <span>{{ $a->merk ?? '-' }}</span>
                            @if ($a->no_seri) <br><small class="text-muted">{{ $a->no_seri }}</small> @endif
                        </td>
                        <td>{!! $a->kondisi_badge !!}</td>
                        <td>{!! $a->status_badge !!}</td>
                        <td>{{ $a->jumlah_stok ?? '-' }}</td>
                        <td>{!! $a->asset_valuation_badge ?? '<span class="text-muted small">-</span>' !!}</td>
                        <td>{{ $a->nilai_perolehan ? 'Rp ' . number_format($a->nilai_perolehan, 0, ',', '.') : '-' }}</td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.show'))
                                    <a href="{{ route('assets.show', $a->id) }}" class="btn btn-sm btn-outline-info" title="Detail">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                @endif
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.edit'))
                                    <a href="{{ route('assets.edit', $a->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="ti ti-pencil"></i>
                                    </a>
                                @endif
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.delete'))
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete"
                                        data-id="{{ $a->id }}" data-nama="{{ $a->nama_asset }}" title="Hapus">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="ti ti-package-off fs-2 d-block mb-2"></i>
                            Belum ada aset terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($assets->hasPages())
        <div class="card-footer">{{ $assets->links() }}</div>
    @endif
</div>

{{-- Form delete --}}
<form id="formDelete" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>

{{-- Modal Import --}}
<div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalImportLabel">
                    <i class="ti ti-upload me-2 text-info"></i>Import Data Aset
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('assets.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    {{-- Petunjuk --}}
                    <div class="alert alert-info py-2 px-3 mb-3">
                        <p class="mb-1 fw-semibold"><i class="ti ti-info-circle me-1"></i>Petunjuk Import:</p>
                        <ol class="mb-0 ps-3 small">
                            <li>Download template terlebih dahulu.</li>
                            <li>Isi data sesuai format pada template.</li>
                            <li>Lihat sheet <strong>Referensi</strong> untuk daftar kode cabang dan kategori.</li>
                            <li>Kode aset yang sudah ada akan <strong>dilewati</strong> (tidak diganti).</li>
                            <li>Unggah file yang sudah diisi, lalu klik <strong>Import</strong>.</li>
                        </ol>
                    </div>

                    {{-- Download template --}}
                    <div class="mb-3">
                        <a href="{{ route('assets.import.template') }}" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="ti ti-file-download me-1"></i> Download Template Excel
                        </a>
                    </div>

                    {{-- Upload file --}}
                    <div class="mb-1">
                        <label class="form-label fw-semibold" for="importFile">
                            Upload File <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror"
                            id="importFile" name="file" accept=".xlsx,.xls,.csv" required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Format: xlsx, xls, csv. Maks 5MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info text-white" id="btnImport">
                        <i class="ti ti-upload me-1"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('myscript')
<script>
$(function () {
    $('.btn-delete').on('click', function () {
        const id   = $(this).data('id');
        const nama = $(this).data('nama');
        Swal.fire({
            icon: 'warning',
            title: 'Hapus Aset?',
            text: `"${nama}" akan dihapus permanen.`,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (result.isConfirmed) {
                const form = $('#formDelete');
                form.attr('action', `{{ url('manajemen-aset') }}/${id}`);
                form.submit();
            }
        });
    });

    // Loading state saat import
    $('#modalImport form').on('submit', function () {
        const btn = $('#btnImport');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Memproses...');
    });

    // Buka modal otomatis jika ada error validasi file
    @if ($errors->has('file'))
        var modal = new bootstrap.Modal(document.getElementById('modalImport'));
        modal.show();
    @endif
});
</script>
@endpush

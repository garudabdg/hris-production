@extends('layouts.app')
@section('titlepage', 'Checklist Perawatan Aset')

@section('content')
@section('navigasi')
    <span>Manajemen Aset</span>
    <span> / Checklist Perawatan</span>
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

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar">
                    <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-checklist fs-4"></i></span>
                </div>
                <div>
                    <p class="mb-0 small text-muted">Total Perawatan</p>
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
                    <p class="mb-0 small text-muted">Kondisi Baik</p>
                    <h4 class="mb-0">{{ $summary['baik'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar">
                    <span class="avatar-initial rounded bg-label-warning"><i class="ti ti-alert-triangle fs-4"></i></span>
                </div>
                <div>
                    <p class="mb-0 small text-muted">Cukup Baik</p>
                    <h4 class="mb-0">{{ $summary['cukup_baik'] }}</h4>
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
                    <p class="mb-0 small text-muted">Rusak</p>
                    <h4 class="mb-0">{{ $summary['rusak'] }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="mb-0"><i class="ti ti-checklist me-2"></i>Checklist Perawatan Aset</h5>
            <small class="text-muted">Riwayat pemeriksaan & perawatan aset</small>
        </div>
        @if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.perawatan.create'))
        <a href="{{ route('asset-perawatan.create') }}" class="btn btn-primary btn-sm">
            <i class="ti ti-plus me-1"></i> Buat Checklist
        </a>
        @endif
    </div>
    <div class="card-body border-bottom pb-3">
        <form method="GET" action="{{ route('asset-perawatan.index') }}" class="row g-2">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Cari kode / petugas / aset..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="kode_asset" class="form-select form-select-sm">
                    <option value="">Semua Aset</option>
                    @foreach ($assets as $a)
                        <option value="{{ $a->kode_asset }}" {{ request('kode_asset') == $a->kode_asset ? 'selected' : '' }}>
                            {{ $a->nama_asset }} ({{ $a->kode_asset }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="dari" class="form-control form-control-sm"
                    value="{{ request('dari') }}" placeholder="Dari tanggal">
            </div>
            <div class="col-md-2">
                <input type="date" name="sampai" class="form-control form-control-sm"
                    value="{{ request('sampai') }}" placeholder="Sampai tanggal">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-search"></i></button>
                <a href="{{ route('asset-perawatan.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ti ti-x"></i></a>
                <button type="submit" name="export_pdf" value="1" formaction="{{ route('asset-perawatan.export-pdf') }}" formtarget="_blank" class="btn btn-outline-danger btn-sm" title="Export PDF"><i class="ti ti-file-pdf"></i></button>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Aset</th>
                    <th>Kategori</th>
                    <th>Tanggal</th>
                    <th>Petugas</th>
                    <th>Total Item</th>
                    <th>Hasil</th>
                    <th>Dibuat Oleh</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($perawatans as $p)
                    <tr>
                        <td>{{ $perawatans->firstItem() + $loop->index }}</td>
                        <td><code>{{ $p->kode_perawatan }}</code></td>
                        <td>
                            <div>
                                <span class="fw-semibold">{{ $p->asset?->nama_asset ?? '-' }}</span>
                                <br><small class="text-muted">{{ $p->kode_asset }}</small>
                            </div>
                        </td>
                        <td>
                            @if ($p->asset?->category)
                                <span class="badge bg-label-info">{{ $p->asset->category->nama_kategori }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $p->tanggal_perawatan?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $p->petugas ?? '-' }}</td>
                        <td>
                            <span class="badge bg-label-secondary">{{ $p->items->count() }} item</span>
                        </td>
                        <td>{!! $p->hasil_badge !!}</td>
                        <td>{{ $p->user?->name ?? '-' }}</td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('asset-perawatan.show', $p->id) }}"
                                    class="btn btn-sm btn-outline-primary" title="Detail">
                                    <i class="ti ti-eye"></i>
                                </a>
                                @if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.perawatan.delete'))
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="confirmDelete({{ $p->id }}, '{{ $p->kode_perawatan }}')" title="Hapus">
                                    <i class="ti ti-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="ti ti-inbox fs-2 d-block mb-2"></i>Belum ada data perawatan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($perawatans->hasPages())
    <div class="card-footer d-flex justify-content-center">
        {{ $perawatans->links() }}
    </div>
    @endif
</div>

{{-- Modal Hapus --}}
<form id="deleteForm" method="POST" action="">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('myscript')
<script>
function confirmDelete(id, kode) {
    if (confirm('Hapus checklist perawatan ' + kode + '? Data ini tidak dapat dikembalikan.')) {
        const form = document.getElementById('deleteForm');
        form.action = '/asset-perawatan/' + id;
        form.submit();
    }
}
</script>
@endpush

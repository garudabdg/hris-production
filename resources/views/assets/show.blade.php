@extends('layouts.app')
@section('titlepage', 'Detail Aset')

@section('content')
@section('navigasi')
    <a href="{{ route('assets.index') }}">Manajemen Aset</a>
    <span> / Detail</span>
@endsection

<div class="row">
    <div class="col-lg-8 col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="ti ti-package me-2"></i>{{ $asset->nama_asset }}</h5>
                    <small class="text-muted"><code>{{ $asset->kode_asset }}</code></small>
                </div>
                <div class="d-flex gap-2">
                    @if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.perawatan.create'))
                    <a href="{{ route('asset-perawatan.create', ['kode_asset' => $asset->kode_asset]) }}"
                        class="btn btn-sm btn-outline-info">
                        <i class="ti ti-checklist me-1"></i> Perawatan
                    </a>
                    @endif
                    <a href="{{ route('assets.edit', $asset->id) }}" class="btn btn-sm btn-primary">
                        <i class="ti ti-pencil me-1"></i> Edit
                    </a>
                    <a href="{{ route('assets.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @if ($asset->foto && Storage::disk('public')->exists('assets/' . $asset->foto))
                        <div class="col-12 text-center">
                            <img src="{{ asset('storage/assets/' . $asset->foto) }}"
                                class="rounded border shadow-sm" style="max-height:220px; object-fit:cover;">
                        </div>
                    @endif
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Kategori</p>
                        <p class="fw-semibold mb-0">{{ $asset->category->nama_kategori ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Cabang</p>
                        <p class="fw-semibold mb-0">{{ optional($asset->cabang)->nama_cabang ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Merk</p>
                        <p class="fw-semibold mb-0">{{ $asset->merk ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">No. Seri</p>
                        <p class="fw-semibold mb-0">{{ $asset->no_seri ?? '-' }}</p>
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1">Kondisi</p>
                        {!! $asset->kondisi_badge !!}
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1">Status</p>
                        {!! $asset->status_badge !!}
                    </div>
                    <div class="col-md-4">
                        <p class="text-muted small mb-1">Tanggal Pembelian</p>
                        <p class="fw-semibold mb-0">{{ $asset->tanggal_perolehan ? $asset->tanggal_perolehan->format('d-m-Y') : '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Harga Pembelian</p>
                        <p class="fw-semibold mb-0">{{ $asset->nilai_perolehan ? 'Rp ' . number_format($asset->nilai_perolehan, 0, ',', '.') : '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Lokasi</p>
                        <p class="fw-semibold mb-0">{{ $asset->lokasi ?? '-' }}</p>
                    </div>
                    @if ($asset->deskripsi)
                        <div class="col-12">
                            <p class="text-muted small mb-1">Deskripsi</p>
                            <p class="mb-0">{{ $asset->deskripsi }}</p>
                        </div>
                    @endif
                    @if ($asset->catatan)
                        <div class="col-12">
                            <p class="text-muted small mb-1">Catatan</p>
                            <p class="mb-0 fst-italic text-muted">{{ $asset->catatan }}</p>
                        </div>
                    @endif
                    @if ($asset->asset_valuation)
                        <div class="col-12">
                            <hr class="my-2">
                            <p class="text-muted small mb-2 fw-semibold"><i class="ti ti-shield-check me-1"></i>Asset Valuation</p>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Confidentiality</p>
                                    <p class="mb-0">{{ \App\Models\Asset::valuationLabel($asset->confidentiality) }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Availability</p>
                                    <p class="mb-0">{{ \App\Models\Asset::valuationLabel($asset->availability) }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p class="text-muted small mb-1">Integrity</p>
                                    <p class="mb-0">{{ \App\Models\Asset::valuationLabel($asset->integrity) }}</p>
                                </div>
                                <div class="col-12">
                                    <p class="text-muted small mb-1">Score Total</p>
                                    <p class="mb-0">{!! $asset->asset_valuation_badge !!}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Ditambahkan</p>
                        <p class="mb-0">{{ $asset->created_at->format('d-m-Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Diperbarui</p>
                        <p class="mb-0">{{ $asset->updated_at->format('d-m-Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Riwayat Perawatan --}}
@if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.perawatan.index'))
@php $perawatanList = $asset->perawatan()->with('items','user')->orderByDesc('tanggal_perawatan')->limit(5)->get(); @endphp
@if ($perawatanList->isNotEmpty())
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="ti ti-checklist me-2"></i>Riwayat Perawatan</h6>
                <a href="{{ route('asset-perawatan.index', ['kode_asset' => $asset->kode_asset]) }}"
                    class="btn btn-outline-primary btn-sm">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Petugas</th>
                            <th>Item</th>
                            <th>Hasil</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($perawatanList as $p)
                        <tr>
                            <td><code>{{ $p->kode_perawatan }}</code></td>
                            <td>{{ $p->tanggal_perawatan?->format('d/m/Y') }}</td>
                            <td>{{ $p->petugas ?? $p->user?->name ?? '-' }}</td>
                            <td><span class="badge bg-label-secondary">{{ $p->items->count() }} item</span></td>
                            <td>{!! $p->hasil_badge !!}</td>
                            <td>
                                <a href="{{ route('asset-perawatan.show', $p->id) }}"
                                    class="btn btn-xs btn-outline-primary btn-sm"><i class="ti ti-eye"></i></a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif
@endif

@endsection

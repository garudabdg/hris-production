@extends('layouts.app')
@section('titlepage', 'Detail Checklist Perawatan')

@section('content')
@section('navigasi')
    <span>Manajemen Aset</span>
    <span> / <a href="{{ route('asset-perawatan.index') }}">Checklist Perawatan</a></span>
    <span> / {{ $assetPerawatan->kode_perawatan }}</span>
@endsection

<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- Header Card --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                    <div>
                        <h5 class="fw-bold mb-1">
                            <i class="ti ti-checklist me-2 text-primary"></i>
                            {{ $assetPerawatan->kode_perawatan }}
                        </h5>
                        <div class="d-flex flex-wrap gap-3 text-muted small">
                            <span><i class="ti ti-calendar me-1"></i>{{ $assetPerawatan->tanggal_perawatan?->format('d F Y') }}</span>
                            <span><i class="ti ti-user me-1"></i>Petugas: {{ $assetPerawatan->petugas ?? '-' }}</span>
                            <span><i class="ti ti-user-circle me-1"></i>Input oleh: {{ $assetPerawatan->user?->name ?? '-' }}</span>
                            <span><i class="ti ti-clock me-1"></i>{{ $assetPerawatan->created_at?->format('d/m/Y H:i') }}</span>
                        </div>
                        @if ($assetPerawatan->catatan)
                            <p class="mt-2 mb-0 text-muted"><i class="ti ti-notes me-1"></i>{{ $assetPerawatan->catatan }}</p>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="mb-1">{!! $assetPerawatan->hasil_badge !!}</div>
                        <small class="text-muted">Hasil Keseluruhan</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Aset --}}
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti ti-package me-2"></i>Informasi Aset</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Kode Aset</small>
                        <code class="fs-6">{{ $assetPerawatan->kode_asset }}</code>
                    </div>
                    <div class="col-md-5">
                        <small class="text-muted d-block">Nama Aset</small>
                        <span class="fw-semibold">{{ $assetPerawatan->asset?->nama_asset ?? '-' }}</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Kategori</small>
                        @if ($assetPerawatan->asset?->category)
                            <span class="badge bg-label-info fs-6">{{ $assetPerawatan->asset->category->nama_kategori }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                    @if ($assetPerawatan->asset?->merk)
                    <div class="col-md-3">
                        <small class="text-muted d-block">Merk</small>
                        <span>{{ $assetPerawatan->asset->merk }}</span>
                    </div>
                    @endif
                    @if ($assetPerawatan->asset?->no_seri)
                    <div class="col-md-4">
                        <small class="text-muted d-block">No. Seri</small>
                        <span>{{ $assetPerawatan->asset->no_seri }}</span>
                    </div>
                    @endif
                    @if ($assetPerawatan->asset?->cabang)
                    <div class="col-md-5">
                        <small class="text-muted d-block">Cabang</small>
                        <span>{{ $assetPerawatan->asset->cabang->nama_cabang }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Checklist Items --}}
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0"><i class="ti ti-list-check me-2"></i>Hasil Checklist Pemeriksaan</h6>
                <span class="badge bg-label-primary">{{ $assetPerawatan->items->count() }} item</span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px;">No</th>
                            <th>Item Pemeriksaan</th>
                            <th style="width:180px;">Klasifikasi</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assetPerawatan->items as $i => $item)
                            <tr class="{{ $item->klasifikasi === 'rusak' ? 'table-danger' : ($item->klasifikasi === 'cukup_baik' ? 'table-warning' : '') }}">
                                <td class="text-center text-muted">{{ $i + 1 }}</td>
                                <td class="fw-semibold">{{ $item->item_name }}</td>
                                <td>{!! $item->klasifikasi_badge !!}</td>
                                <td class="text-muted">{{ $item->keterangan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Tidak ada item checklist.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Ringkasan --}}
            @php
                $totalBaik      = $assetPerawatan->items->where('klasifikasi', 'baik')->count();
                $totalCukup     = $assetPerawatan->items->where('klasifikasi', 'cukup_baik')->count();
                $totalRusak     = $assetPerawatan->items->where('klasifikasi', 'rusak')->count();
                $totalAll       = $assetPerawatan->items->count();
            @endphp
            @if ($totalAll > 0)
            <div class="card-footer">
                <div class="row g-2 text-center">
                    <div class="col">
                        <div class="p-2 rounded bg-label-success">
                            <div class="fw-bold text-success fs-5">{{ $totalBaik }}</div>
                            <small class="text-muted">Baik</small>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 rounded bg-label-warning">
                            <div class="fw-bold text-warning fs-5">{{ $totalCukup }}</div>
                            <small class="text-muted">Cukup Baik</small>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 rounded bg-label-danger">
                            <div class="fw-bold text-danger fs-5">{{ $totalRusak }}</div>
                            <small class="text-muted">Rusak</small>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-2 rounded bg-label-secondary">
                            <div class="fw-bold fs-5">{{ $totalAll }}</div>
                            <small class="text-muted">Total Item</small>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card-footer d-flex justify-content-between align-items-center">
                <a href="{{ route('asset-perawatan.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i> Kembali
                </a>
                @if (auth()->user()->isSuperAdmin() || auth()->user()->can('asset.perawatan.delete'))
                <form method="POST" action="{{ route('asset-perawatan.destroy', $assetPerawatan->id) }}"
                    onsubmit="return confirm('Hapus checklist {{ $assetPerawatan->kode_perawatan }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="ti ti-trash me-1"></i> Hapus
                    </button>
                </form>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

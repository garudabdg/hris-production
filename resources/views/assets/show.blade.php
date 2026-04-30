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
@endsection

@extends('layouts.app')
@section('titlepage', 'Tambah Aset')

@section('content')
@section('navigasi')
    <a href="{{ route('assets.index') }}">Manajemen Aset</a>
    <span> / Tambah Aset</span>
@endsection

<div class="row">
    <div class="col-lg-8 col-md-10 col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="ti ti-package me-2"></i>Form Tambah Aset</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode Aset <span class="text-danger">*</span></label>
                            <input type="text" name="kode_asset" class="form-control @error('kode_asset') is-invalid @enderror"
                                value="{{ old('kode_asset') }}" placeholder="Cth: AST-001">
                            @error('kode_asset') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Aset <span class="text-danger">*</span></label>
                            <input type="text" name="nama_asset" class="form-control @error('nama_asset') is-invalid @enderror"
                                value="{{ old('nama_asset') }}" placeholder="Nama aset">
                            @error('nama_asset') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nama_kategori }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cabang</label>
                            <select name="kode_cabang" class="form-select @error('kode_cabang') is-invalid @enderror">
                                <option value="">-- Pilih Cabang --</option>
                                @foreach ($cabang as $c)
                                    <option value="{{ $c->kode_cabang }}" {{ old('kode_cabang') == $c->kode_cabang ? 'selected' : '' }}>
                                        {{ textUpperCase($c->nama_cabang) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kode_cabang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Merk</label>
                            <input type="text" name="merk" class="form-control @error('merk') is-invalid @enderror"
                                value="{{ old('merk') }}" placeholder="Merk / brand">
                            @error('merk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Seri</label>
                            <input type="text" name="no_seri" class="form-control @error('no_seri') is-invalid @enderror"
                                value="{{ old('no_seri') }}" placeholder="Nomor seri / serial number">
                            @error('no_seri') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kondisi <span class="text-danger">*</span></label>
                            <select name="kondisi" class="form-select @error('kondisi') is-invalid @enderror">
                                <option value="baik" {{ old('kondisi','baik') == 'baik' ? 'selected' : '' }}>Baik</option>
                                <option value="rusak" {{ old('kondisi') == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                <option value="dalam_perbaikan" {{ old('kondisi') == 'dalam_perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                            </select>
                            @error('kondisi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="tersedia" {{ old('status','tersedia') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                <option value="dipinjam" {{ old('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                <option value="tidak_aktif" {{ old('status') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Pembelian</label>
                            <input type="text" name="tanggal_perolehan" class="form-control flatpickr-date @error('tanggal_perolehan') is-invalid @enderror"
                                value="{{ old('tanggal_perolehan') }}" placeholder="Pilih tanggal">
                            @error('tanggal_perolehan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Pembelian (Rp)</label>
                            <input type="number" name="nilai_perolehan" class="form-control @error('nilai_perolehan') is-invalid @enderror"
                                value="{{ old('nilai_perolehan') }}" placeholder="0">
                            @error('nilai_perolehan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lokasi</label>
                            <input type="text" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror"
                                value="{{ old('lokasi') }}" placeholder="Ruangan / gedung / lantai">
                            @error('lokasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
                                rows="3" placeholder="Deskripsi singkat aset">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror"
                                rows="2" placeholder="Catatan tambahan">{{ old('catatan') }}</textarea>
                            @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Foto Aset</label>
                            <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                            <div class="text-muted small mt-1">Format: JPG, PNG, WEBP. Maksimal 2MB.</div>
                            @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Simpan</button>
                        <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

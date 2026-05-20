@extends('layouts.app')
@section('titlepage', 'Edit Aset')

@section('content')
@section('navigasi')
    <a href="{{ route('assets.index') }}">Manajemen Aset</a>
    <span> / Edit Aset</span>
@endsection

<div class="row">
    <div class="col-lg-8 col-md-10 col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="ti ti-pencil me-2"></i>Edit Aset — {{ $asset->nama_asset }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('assets.update', $asset->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode Aset <span class="text-danger">*</span></label>
                            <input type="text" name="kode_asset" class="form-control @error('kode_asset') is-invalid @enderror"
                                value="{{ old('kode_asset', $asset->kode_asset) }}">
                            @error('kode_asset') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Aset <span class="text-danger">*</span></label>
                            <input type="text" name="nama_asset" class="form-control @error('nama_asset') is-invalid @enderror"
                                value="{{ old('nama_asset', $asset->nama_asset) }}">
                            @error('nama_asset') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $asset->category_id) == $cat->id ? 'selected' : '' }}>
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
                                    <option value="{{ $c->kode_cabang }}" {{ old('kode_cabang', $asset->kode_cabang) == $c->kode_cabang ? 'selected' : '' }}>
                                        {{ textUpperCase($c->nama_cabang) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kode_cabang') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Merk</label>
                            <input type="text" name="merk" class="form-control @error('merk') is-invalid @enderror"
                                value="{{ old('merk', $asset->merk) }}">
                            @error('merk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Seri</label>
                            <input type="text" name="no_seri" class="form-control @error('no_seri') is-invalid @enderror"
                                value="{{ old('no_seri', $asset->no_seri) }}">
                            @error('no_seri') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kondisi <span class="text-danger">*</span></label>
                            <select name="kondisi" class="form-select @error('kondisi') is-invalid @enderror">
                                <option value="baik" {{ old('kondisi', $asset->kondisi) == 'baik' ? 'selected' : '' }}>Baik</option>
                                <option value="rusak" {{ old('kondisi', $asset->kondisi) == 'rusak' ? 'selected' : '' }}>Rusak</option>
                                <option value="dalam_perbaikan" {{ old('kondisi', $asset->kondisi) == 'dalam_perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                            </select>
                            @error('kondisi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="tersedia" {{ old('status', $asset->status) == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                <option value="dipinjam" {{ old('status', $asset->status) == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                                <option value="tidak_aktif" {{ old('status', $asset->status) == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Pembelian</label>
                            <input type="text" name="tanggal_perolehan" class="form-control flatpickr-date @error('tanggal_perolehan') is-invalid @enderror"
                                value="{{ old('tanggal_perolehan', $asset->tanggal_perolehan ? $asset->tanggal_perolehan->format('Y-m-d') : '') }}">
                            @error('tanggal_perolehan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Pembelian (Rp)</label>
                            <input type="number" name="nilai_perolehan" class="form-control @error('nilai_perolehan') is-invalid @enderror"
                                value="{{ old('nilai_perolehan', $asset->nilai_perolehan) }}">
                            @error('nilai_perolehan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lokasi</label>
                            <input type="text" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror"
                                value="{{ old('lokasi', $asset->lokasi) }}">
                            @error('lokasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror"
                                rows="3">{{ old('deskripsi', $asset->deskripsi) }}</textarea>
                            @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror"
                                rows="2">{{ old('catatan', $asset->catatan) }}</textarea>
                            @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Foto Aset</label>
                            @if ($asset->foto && Storage::disk('public')->exists('assets/' . $asset->foto))
                                <div class="mb-2">
                                    <img src="{{ asset('storage/assets/' . $asset->foto) }}"
                                        class="rounded border" height="100" style="object-fit:cover;">
                                    <div class="small text-muted mt-1">Foto saat ini. Upload foto baru untuk mengganti.</div>
                                </div>
                            @endif
                            <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                            <div class="text-muted small mt-1">Format: JPG, PNG, WEBP. Maksimal 2MB.</div>
                            @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- ── Asset Valuation ─────────────────────────────── --}}
                        <div class="col-12">
                            <hr class="my-2">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="ti ti-shield-check text-primary fs-5"></i>
                                <h6 class="mb-0 fw-bold">Asset Valuation</h6>
                                <span class="text-muted small">(Confidentiality + Availability + Integrity)</span>
                            </div>
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Confidentiality</label>
                                    <select name="confidentiality" id="val_c" class="form-select @error('confidentiality') is-invalid @enderror" onchange="calcValuation()">
                                        <option value="">-- Pilih --</option>
                                        <option value="1" @selected(old('confidentiality', $asset->confidentiality) == '1')>1 - Low</option>
                                        <option value="2" @selected(old('confidentiality', $asset->confidentiality) == '2')>2 - Medium</option>
                                        <option value="3" @selected(old('confidentiality', $asset->confidentiality) == '3')>3 - High</option>
                                    </select>
                                    @error('confidentiality') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Availability</label>
                                    <select name="availability" id="val_a" class="form-select @error('availability') is-invalid @enderror" onchange="calcValuation()">
                                        <option value="">-- Pilih --</option>
                                        <option value="1" @selected(old('availability', $asset->availability) == '1')>1 - Low</option>
                                        <option value="2" @selected(old('availability', $asset->availability) == '2')>2 - Medium</option>
                                        <option value="3" @selected(old('availability', $asset->availability) == '3')>3 - High</option>
                                    </select>
                                    @error('availability') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Integrity</label>
                                    <select name="integrity" id="val_i" class="form-select @error('integrity') is-invalid @enderror" onchange="calcValuation()">
                                        <option value="">-- Pilih --</option>
                                        <option value="1" @selected(old('integrity', $asset->integrity) == '1')>1 - Low</option>
                                        <option value="2" @selected(old('integrity', $asset->integrity) == '2')>2 - Medium</option>
                                        <option value="3" @selected(old('integrity', $asset->integrity) == '3')>3 - High</option>
                                    </select>
                                    @error('integrity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12">
                                    <div id="valuation_result" class="d-none alert py-2 px-3 mb-0">
                                        <strong>Asset Valuation:</strong>
                                        <span id="valuation_text"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- ── End Asset Valuation ─────────────────────────── --}}

                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Perbarui</button>
                        <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<script>
function calcValuation() {
    const c = parseInt(document.getElementById('val_c').value) || 0;
    const a = parseInt(document.getElementById('val_a').value) || 0;
    const i = parseInt(document.getElementById('val_i').value) || 0;
    const resultEl = document.getElementById('valuation_result');
    const textEl   = document.getElementById('valuation_text');

    if (c && a && i) {
        const score = c + a + i;
        let label, cls;
        if (score <= 4)      { label = 'Low';    cls = 'alert-info'; }
        else if (score <= 6) { label = 'Medium'; cls = 'alert-warning'; }
        else                 { label = 'High';   cls = 'alert-danger'; }

        resultEl.className = 'alert py-2 px-3 mb-0 ' + cls;
        textEl.innerHTML = `<strong>${score} — ${label}</strong> &nbsp;<span class="text-muted small">(C:${c} + A:${a} + I:${i})</span>`;
        resultEl.classList.remove('d-none');
    } else {
        resultEl.classList.add('d-none');
    }
}
document.addEventListener('DOMContentLoaded', calcValuation);
</script>
@endpush

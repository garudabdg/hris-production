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
                            <input type="text" name="kode_asset" id="kode_asset" class="form-control @error('kode_asset') is-invalid @enderror"
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
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
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
                            <label class="form-label">Pemilik / Penanggung Jawab</label>
                            <select name="nik" id="nik" class="form-select select2Karyawan @error('nik') is-invalid @enderror">
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($karyawan as $k)
                                    <option value="{{ $k->nik }}" {{ old('nik') == $k->nik ? 'selected' : '' }}>
                                        {{ $k->nama_karyawan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                            <label class="form-label">Jumlah Stok</label>
                            <input type="number" name="jumlah_stok" class="form-control @error('jumlah_stok') is-invalid @enderror"
                                value="{{ old('jumlah_stok', 1) }}" placeholder="1">
                            @error('jumlah_stok') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                        <option value="1" @selected(old('confidentiality') == '1')>1 - Low</option>
                                        <option value="2" @selected(old('confidentiality') == '2')>2 - Medium</option>
                                        <option value="3" @selected(old('confidentiality') == '3')>3 - High</option>
                                    </select>
                                    @error('confidentiality') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Availability</label>
                                    <select name="availability" id="val_a" class="form-select @error('availability') is-invalid @enderror" onchange="calcValuation()">
                                        <option value="">-- Pilih --</option>
                                        <option value="1" @selected(old('availability') == '1')>1 - Low</option>
                                        <option value="2" @selected(old('availability') == '2')>2 - Medium</option>
                                        <option value="3" @selected(old('availability') == '3')>3 - High</option>
                                    </select>
                                    @error('availability') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold">Integrity</label>
                                    <select name="integrity" id="val_i" class="form-select @error('integrity') is-invalid @enderror" onchange="calcValuation()">
                                        <option value="">-- Pilih --</option>
                                        <option value="1" @selected(old('integrity') == '1')>1 - Low</option>
                                        <option value="2" @selected(old('integrity') == '2')>2 - Medium</option>
                                        <option value="3" @selected(old('integrity') == '3')>3 - High</option>
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
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> Simpan</button>
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
$(function() {
    const select2Karyawan = $('.select2Karyawan');
    if (select2Karyawan.length) {
        select2Karyawan.each(function() {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
                placeholder: '-- Pilih Karyawan --',
                allowClear: true,
                dropdownParent: $this.parent()
            });
        });
    }

    $('#category_id').on('change', function() {
        const categoryId = $(this).val();
        if (categoryId) {
            $.ajax({
                url: "{{ route('assets.generate-code') }}",
                type: "GET",
                data: { category_id: categoryId },
                dataType: "json",
                success: function(response) {
                    if (response && response.code) {
                        $('#kode_asset').val(response.code);
                    }
                },
                error: function(xhr) {
                    console.error('Gagal mengambil kode aset otomatis:', xhr);
                }
            });
        } else {
            $('#kode_asset').val('');
        }
    });
});

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
// Init on page load (jika ada old value)
document.addEventListener('DOMContentLoaded', calcValuation);
</script>
@endpush

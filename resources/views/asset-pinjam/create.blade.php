<form action="{{ route('asset-pinjam.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label fw-semibold">Aset yang Dipinjam <span class="text-danger">*</span></label>
                <select name="kode_asset" class="form-select @error('kode_asset') is-invalid @enderror" required>
                    <option value="">-- Pilih Aset --</option>
                    @foreach ($assets as $a)
                        <option value="{{ $a->kode_asset }}" {{ old('kode_asset') == $a->kode_asset ? 'selected' : '' }}>
                            {{ $a->nama_asset }} ({{ $a->kode_asset }}) {{ $a->merk ? '- '.$a->merk : '' }}
                        </option>
                    @endforeach
                </select>
                @error('kode_asset') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Peminjam (Karyawan) <span class="text-danger">*</span></label>
                <select name="nik" class="form-select @error('nik') is-invalid @enderror" required>
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach ($karyawan_list as $k)
                        <option value="{{ $k->nik }}" {{ old('nik') == $k->nik ? 'selected' : '' }}>
                            {{ $k->nama_karyawan }}
                        </option>
                    @endforeach
                </select>
                @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Tanggal Pinjam <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}"
                    class="form-control @error('tanggal_pinjam') is-invalid @enderror" required>
                @error('tanggal_pinjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Rencana Tgl Kembali <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_kembali_rencana" value="{{ old('tanggal_kembali_rencana') }}"
                    class="form-control @error('tanggal_kembali_rencana') is-invalid @enderror" required>
                @error('tanggal_kembali_rencana') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Foto Kondisi Aset Saat Dipinjam</label>
                <input type="file" name="foto_kondisi_pinjam" accept="image/*"
                    class="form-control @error('foto_kondisi_pinjam') is-invalid @enderror">
                <small class="text-muted">Format: JPG/PNG/JPEG. Maks 2MB.</small>
                @error('foto_kondisi_pinjam') <div class="invalid-feedback">{{ $message }}</div> @enderror
                {{-- Preview foto --}}
                <div id="previewWrapper" class="mt-2 d-none">
                    <img id="fotoPreview" src="#" alt="Preview" style="max-height:150px; border-radius:8px; border:1px solid #ddd;">
                </div>
            </div>

            <div class="col-md-12">
                <label class="form-label fw-semibold">Catatan</label>
                <textarea name="catatan" class="form-control @error('catatan') is-invalid @enderror"
                    rows="3" placeholder="Tujuan peminjaman, keperluan, dll...">{{ old('catatan') }}</textarea>
                @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">
            <i class="ti ti-send me-1"></i>Ajukan Peminjaman
        </button>
    </div>
</form>

<script>
    document.querySelector('[name="foto_kondisi_pinjam"]').addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('fotoPreview').src = e.target.result;
                document.getElementById('previewWrapper').classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }
    });
</script>

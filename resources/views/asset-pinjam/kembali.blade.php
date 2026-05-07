<form action="{{ route('asset-pinjam.storekembali', Crypt::encrypt($pinjam->id)) }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="modal-body">
        <div class="row g-3">
            {{-- Info ringkas --}}
            <div class="col-12">
                <div class="p-3 bg-light rounded" style="font-size:13px;">
                    <div class="fw-bold">{{ $pinjam->nama_karyawan }}</div>
                    <div class="text-muted">
                        Aset: <strong>{{ $pinjam->nama_asset }}</strong>
                        {{ $pinjam->merk ? '— ' . $pinjam->merk : '' }}
                    </div>
                    <div class="text-muted">Dipinjam: {{ \Carbon\Carbon::parse($pinjam->tanggal_pinjam)->format('d M Y') }}</div>
                    <div class="text-muted">Rencana Kembali: <strong>{{ \Carbon\Carbon::parse($pinjam->tanggal_kembali_rencana)->format('d M Y') }}</strong></div>
                </div>
            </div>

            {{-- Foto kondisi sebelum dipinjam untuk perbandingan --}}
            @if ($pinjam->foto_kondisi_pinjam)
            <div class="col-12">
                <label class="form-label fw-semibold mb-1">Foto Kondisi Saat Dipinjam (Referensi)</label>
                <div>
                    <img src="{{ Storage::url($pinjam->foto_kondisi_pinjam) }}" alt="Kondisi Awal"
                        style="max-height:140px; border-radius:8px; border:1px solid #ddd;">
                </div>
            </div>
            @endif

            <div class="col-md-6">
                <label class="form-label fw-semibold">Tanggal Pengembalian <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_kembali_aktual" value="{{ date('Y-m-d') }}"
                    class="form-control @error('tanggal_kembali_aktual') is-invalid @enderror" required>
                @error('tanggal_kembali_aktual') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Kondisi Aset Saat Kembali <span class="text-danger">*</span></label>
                <select name="kondisi_kembali" class="form-select @error('kondisi_kembali') is-invalid @enderror" required>
                    <option value="">-- Pilih Kondisi --</option>
                    <option value="baik" {{ $pinjam->kondisi_asset == 'baik' ? 'selected' : '' }}>Baik</option>
                    <option value="rusak">Rusak</option>
                    <option value="dalam_perbaikan">Dalam Perbaikan</option>
                </select>
                @error('kondisi_kembali') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Foto Kondisi Aset Saat Kembali</label>
                <input type="file" name="foto_kondisi_kembali" accept="image/*"
                    class="form-control @error('foto_kondisi_kembali') is-invalid @enderror">
                <small class="text-muted">Format: JPG/PNG/JPEG. Maks 2MB.</small>
                @error('foto_kondisi_kembali') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div id="previewWrapper" class="mt-2 d-none">
                    <img id="fotoPreview" src="#" alt="Preview"
                        style="max-height:160px; border-radius:8px; border:1px solid #ddd;">
                </div>
            </div>

            <div class="col-12">
                <label class="form-label fw-semibold">Catatan Pengembalian</label>
                <textarea name="catatan_kembali" class="form-control" rows="2"
                    placeholder="Catatan kondisi, kerusakan, dll..."></textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-success">
            <i class="ti ti-package-import me-1"></i>Konfirmasi Pengembalian
        </button>
    </div>
</form>

<script>
    document.querySelector('[name="foto_kondisi_kembali"]').addEventListener('change', function() {
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

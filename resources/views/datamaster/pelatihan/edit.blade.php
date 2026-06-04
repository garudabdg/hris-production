<form action="{{ route('karyawan-pelatihan.update', $pelatihan->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="form-label">Nama Pelatihan / Sertifikasi <span class="text-danger">*</span></label>
            <input type="text" name="nama_pelatihan" class="form-control" value="{{ $pelatihan->nama_pelatihan }}" required>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Tanggal Pelatihan <span class="text-danger">*</span></label>
            <input type="date" name="tanggal_pelatihan" class="form-control" value="{{ $pelatihan->tanggal_pelatihan ? $pelatihan->tanggal_pelatihan->format('Y-m-d') : '' }}" required>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Berlaku Sampai</label>
            <input type="date" name="tanggal_expired" class="form-control" value="{{ $pelatihan->tanggal_expired ? $pelatihan->tanggal_expired->format('Y-m-d') : '' }}">
            <small class="text-muted">Kosongkan jika berlaku seumur hidup (Lifetime).</small>
        </div>
        
        <div class="col-md-12 mb-3">
            <label class="form-label">File Sertifikat (Opsional)</label>
            <input type="file" name="file_sertifikat" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            @if($pelatihan->file_sertifikat)
                <div class="mt-2">
                    <small class="text-info"><i class="ti ti-file-check me-1"></i> File saat ini: {{ $pelatihan->file_sertifikat }}</small>
                </div>
            @endif
            <small class="text-muted">Format: PDF, JPG, PNG (Max: 2MB). Biarkan kosong jika tidak ingin mengubah file.</small>
        </div>
    </div>
    
    <div class="modal-footer px-0 pb-0 mt-3">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
</form>

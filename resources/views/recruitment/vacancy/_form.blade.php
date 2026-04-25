@php $edit = $edit ?? false; @endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Cabang <span class="text-danger">*</span></label>
        <select name="kode_cabang" class="form-select" required>
            <option value="">-- Pilih Cabang --</option>
            @foreach($cabangs as $c)
            <option value="{{ $c->kode_cabang }}" {{ old('kode_cabang', $v->kode_cabang ?? '') == $c->kode_cabang ? 'selected' : '' }}>
                {{ $c->nama_cabang }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Posisi / Nama Lowongan <span class="text-danger">*</span></label>
        <input type="text" name="posisi" class="form-control" value="{{ old('posisi', $v->posisi ?? '') }}" required placeholder="Contoh: Staff Accounting">
    </div>
    <div class="col-md-6">
        <label class="form-label">Departemen <span class="text-danger">*</span></label>
        <select name="kode_dept" class="form-select" required>
            <option value="">-- Pilih Departemen --</option>
            @foreach($departements as $d)
            <option value="{{ $d->kode_dept }}" {{ old('kode_dept', $v->kode_dept ?? '') == $d->kode_dept ? 'selected' : '' }}>
                {{ $d->nama_dept }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Jabatan <span class="text-danger">*</span></label>
        <select name="kode_jabatan" class="form-select" required>
            <option value="">-- Pilih Jabatan --</option>
            @foreach($jabatans as $j)
            <option value="{{ $j->kode_jabatan }}" {{ old('kode_jabatan', $v->kode_jabatan ?? '') == $j->kode_jabatan ? 'selected' : '' }}>
                {{ $j->nama_jabatan }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Kuota <span class="text-danger">*</span></label>
        <input type="number" name="kuota" class="form-control" value="{{ old('kuota', $v->kuota ?? 1) }}" min="1" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Deadline Lamaran</label>
        <input type="date" name="deadline" class="form-control" value="{{ old('deadline', isset($v->deadline) ? $v->deadline->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select" required>
            <option value="buka" {{ old('status', $v->status ?? 'buka') == 'buka' ? 'selected' : '' }}>Buka</option>
            <option value="tutup" {{ old('status', $v->status ?? '') == 'tutup' ? 'selected' : '' }}>Tutup</option>
        </select>
    </div>
    <div class="col-12">
        <label class="form-label">Deskripsi Pekerjaan</label>
        <textarea name="deskripsi_pekerjaan" class="form-control" rows="4" placeholder="Tulis deskripsi pekerjaan...">{{ old('deskripsi_pekerjaan', $v->deskripsi_pekerjaan ?? '') }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Kualifikasi</label>
        <textarea name="kualifikasi" class="form-control" rows="4" placeholder="Tulis kualifikasi yang dibutuhkan...">{{ old('kualifikasi', $v->kualifikasi ?? '') }}</textarea>
    </div>
</div>

@extends('layouts.app')
@section('titlepage', 'Buat Tiket IT')

@section('content')
@section('navigasi')
    <a href="{{ route('it-ticket.index') }}">IT Ticket</a>
    <span> / Buat Tiket</span>
@endsection

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="ti ti-ticket me-2"></i>Form Pengaduan Layanan IT</h5>
                <small class="text-muted">Semua tiket tercatat sebagai audit trail sesuai ISO 27001</small>
            </div>
            <div class="card-body">
                <form action="{{ route('it-ticket.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Judul --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Pengaduan <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
                            value="{{ old('judul') }}" placeholder="Contoh: Laptop tidak bisa connect WiFi">
                        @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi Detail <span class="text-danger">*</span></label>
                        <textarea name="deskripsi" rows="5" class="form-control @error('deskripsi') is-invalid @enderror"
                            placeholder="Jelaskan masalah secara detail: kapan terjadi, dampak yang dirasakan, langkah yang sudah dicoba...">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        {{-- Kategori --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori Masalah <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                                <option value="" disabled {{ old('kategori') ? '' : 'selected' }}>Pilih Kategori...</option>
                                <option value="hardware"  {{ old('kategori')=='hardware'  ?'selected':'' }}>🖥️ Hardware (PC, Printer, dll)</option>
                                <option value="software"  {{ old('kategori')=='software'  ?'selected':'' }}>💻 Software / Aplikasi</option>
                                <option value="jaringan"  {{ old('kategori')=='jaringan'  ?'selected':'' }}>🌐 Jaringan / Internet</option>
                                <option value="keamanan"  {{ old('kategori')=='keamanan'  ?'selected':'' }}>🔒 Keamanan Informasi</option>
                                <option value="akses"     {{ old('kategori')=='akses'     ?'selected':'' }}>🔑 Hak Akses / Login</option>
                                <option value="data"      {{ old('kategori')=='data'      ?'selected':'' }}>📂 Data / Backup</option>
                                <option value="lainnya"   {{ old('kategori')=='lainnya'   ?'selected':'' }}>📌 Lainnya</option>
                            </select>
                            @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Lokasi --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lokasi <span class="text-danger">*</span></label>
                            <input type="text" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror" 
                                value="{{ old('lokasi') }}" placeholder="Contoh: Lt. 2 Ruang Meeting" required>
                            @error('lokasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasRole('it staff'))
                        {{-- Prioritas --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prioritas <span class="text-danger">*</span></label>
                            <select name="prioritas" class="form-select @error('prioritas') is-invalid @enderror">
                                <option value="low"      {{ old('prioritas','medium')=='low'      ?'selected':'' }}>🟢 Low — Tidak mengganggu operasional (SLA 14 hari)</option>
                                <option value="medium"   {{ old('prioritas','medium')=='medium'   ?'selected':'' }}>🔵 Medium — Sedikit mengganggu (SLA 7 hari)</option>
                                <option value="high"     {{ old('prioritas','medium')=='high'     ?'selected':'' }}>🟠 High — Mengganggu pekerjaan (SLA 3 hari)</option>
                                <option value="critical" {{ old('prioritas','medium')=='critical' ?'selected':'' }}>🔴 Critical — Operasional terhenti (SLA 1 hari)</option>
                            </select>
                            @error('prioritas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Klasifikasi Data (ISO 27001) --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Klasifikasi Data
                                <i class="ti ti-info-circle text-muted ms-1" title="ISO 27001 - Data Classification"></i>
                                <span class="text-danger">*</span>
                            </label>
                            <select name="klasifikasi_data" class="form-select @error('klasifikasi_data') is-invalid @enderror">
                                <option value="public"       {{ old('klasifikasi_data','internal')=='public'       ?'selected':'' }}>🟢 Public — Data umum</option>
                                <option value="internal"     {{ old('klasifikasi_data','internal')=='internal'     ?'selected':'' }}>🟡 Internal — Data internal perusahaan</option>
                                <option value="confidential" {{ old('klasifikasi_data','internal')=='confidential' ?'selected':'' }}>🔴 Confidential — Data rahasia</option>
                            </select>
                            @error('klasifikasi_data')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @endif


                        {{-- Dampak --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Dampak <span class="text-danger">*</span></label>
                            <select name="dampak" class="form-select @error('dampak') is-invalid @enderror">
                                <option value="individu"    {{ old('dampak','individu')=='individu'    ?'selected':'' }}>👤 Individu — Hanya saya</option>
                                <option value="departemen"  {{ old('dampak','individu')=='departemen'  ?'selected':'' }}>👥 Departemen — Satu departemen</option>
                                <option value="cabang"      {{ old('dampak','individu')=='cabang'      ?'selected':'' }}>🏢 Cabang — Satu cabang</option>
                                <option value="perusahaan"  {{ old('dampak','individu')=='perusahaan'  ?'selected':'' }}>🏭 Perusahaan — Seluruh perusahaan</option>
                            </select>
                            @error('dampak')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Cabang --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cabang</label>
                            @php
                                $defaultKodeCabang = old('kode_cabang');
                                // Auto-select cabang dari data karyawan jika belum ada
                                if (empty($defaultKodeCabang)) {
                                    $karyawan = \App\Models\Karyawan::where('nik', auth()->user()->username)->first();
                                    $defaultKodeCabang = $karyawan ? $karyawan->kode_cabang : null;
                                }
                            @endphp
                            <select name="kode_cabang" class="form-select select2">
                                <option value="">-- Pilih Cabang --</option>
                                @foreach ($cabang as $c)
                                    <option value="{{ $c->kode_cabang }}" {{ $defaultKodeCabang==$c->kode_cabang?'selected':'' }}>
                                        {{ textUpperCase($c->nama_cabang) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kode_cabang')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Lampiran --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lampiran</label>
                            <input type="file" name="lampiran" class="form-control @error('lampiran') is-invalid @enderror"
                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.zip">
                            <div class="form-text">Maks 5MB. Format: jpg, png, pdf, doc, docx, xlsx, zip</div>
                            @error('lampiran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Info SLA --}}
                    <div class="alert alert-info mt-3 mb-0 py-2">
                        <i class="ti ti-clock me-2"></i>
                        <strong>SLA Response Time:</strong>
                        Critical: 1 hari &nbsp;|&nbsp; High: 3 hari &nbsp;|&nbsp; Medium: 7 hari &nbsp;|&nbsp; Low: 14 hari
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-send me-1"></i> Kirim Tiket
                        </button>
                        <a href="{{ route('it-ticket.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('myscript')
<script>
$(function () {
    $('.select2').select2({ width: '100%' });
});
</script>
@endpush

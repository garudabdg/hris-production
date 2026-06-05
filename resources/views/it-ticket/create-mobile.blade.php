@extends('layouts.mobile.modern')
@section('title', 'Buat Tiket IT')

@section('header_left')
    <a href="{{ route('it-ticket.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
<style>
    body { background: #eef6f3 !important; }

    .form-container { padding: 12px 4px; }

    .form-label-group {
        position: relative;
        margin-bottom: 12px;
        background: transparent !important;
        border: 1.5px solid {{ $t['primary'] ?? '#2d5a4c' }};
        border-radius: 14px;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .form-label-group:focus-within {
        border-color: {{ $t['primary'] ?? '#2d5a4c' }};
        box-shadow: 0 0 0 3px {{ $t['primary'] ?? '#2d5a4c' }}22;
    }
    .form-label-group .input-icon {
        position: absolute;
        left: 14px; top: 12px;
        font-size: 20px;
        color: {{ $t['primary'] ?? '#2d5a4c' }};
        z-index: 10;
        pointer-events: none;
    }
    .form-label-group input,
    .form-label-group select,
    .form-label-group textarea {
        width: 100% !important;
        height: 48px;
        padding: 20px 14px 4px 42px !important;
        font-size: 14px;
        font-weight: 500;
        color: #1e293b;
        background: transparent !important;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        display: block !important;
        appearance: none;
        -webkit-appearance: none;
    }
    .form-label-group textarea {
        height: 100px !important;
        padding-top: 24px !important;
        resize: none;
    }
    .form-label-group .select-chevron {
        position: absolute;
        right: 14px; top: 15px;
        font-size: 16px;
        color: {{ $t['primary'] ?? '#2d5a4c' }};
        pointer-events: none;
        z-index: 10;
    }
    .form-label-group label {
        position: absolute;
        top: 13px; left: 42px;
        font-size: 13px;
        color: {{ $t['primary'] ?? '#2d5a4c' }};
        opacity: 0.75;
        pointer-events: none;
        transition: all 0.2s ease-in-out;
        margin-bottom: 0;
        z-index: 5;
    }
    .form-label-group input:focus ~ label,
    .form-label-group input:not(:placeholder-shown) ~ label,
    .form-label-group select:focus ~ label,
    .form-label-group select:valid ~ label,
    .form-label-group textarea:focus ~ label,
    .form-label-group textarea:not(:placeholder-shown) ~ label {
        top: 3px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        opacity: 1;
    }

    .section-title {
        font-size: 11px;
        font-weight: 700;
        color: {{ $t['primary'] ?? '#2d5a4c' }};
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .sla-info {
        background: {{ $t['primary'] ?? '#2d5a4c' }}12;
        border: 1px solid {{ $t['primary'] ?? '#2d5a4c' }}30;
        border-radius: 12px;
        padding: 10px 14px;
        margin-bottom: 14px;
        font-size: 11px;
        color: {{ $t['primary'] ?? '#2d5a4c' }};
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }

    .btn-submit-modern {
        width: 100%;
        height: 52px;
        background: {{ $t['primary'] ?? '#2d5a4c' }};
        color: #fff;
        border: none;
        border-radius: 14px;
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 8px;
        transition: all 0.3s;
        box-shadow: 0 4px 14px {{ $t['primary'] ?? '#2d5a4c' }}40;
    }
    .btn-submit-modern:active {
        transform: scale(0.97);
        box-shadow: none;
    }

    .file-upload-area {
        border: 1.5px dashed {{ $t['primary'] ?? '#2d5a4c' }}60;
        border-radius: 14px;
        padding: 14px;
        text-align: center;
        background: {{ $t['primary'] ?? '#2d5a4c' }}06;
        margin-bottom: 12px;
    }
    .file-upload-area input[type="file"] {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        top: 0; left: 0;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="px-4 pb-10">
    <div class="form-container">
        <form action="{{ route('it-ticket.store') }}" method="POST" enctype="multipart/form-data" id="formTicket" autocomplete="off">
            @csrf

            {{-- Judul --}}
            <p class="section-title">
                <ion-icon name="create-outline" style="font-size:14px;"></ion-icon>
                Informasi Pengaduan
            </p>

            <div class="form-label-group">
                <ion-icon name="document-text-outline" class="input-icon"></ion-icon>
                <input type="text" name="judul" id="judul" placeholder=" " value="{{ old('judul') }}" required>
                <label for="judul">Judul Pengaduan</label>
            </div>

            <div class="form-label-group">
                <ion-icon name="chatbox-outline" class="input-icon" style="top:16px;"></ion-icon>
                <textarea name="deskripsi" id="deskripsi" placeholder=" " required>{{ old('deskripsi') }}</textarea>
                <label for="deskripsi">Deskripsi Detail Masalah</label>
            </div>

            {{-- Kategori --}}
            <div class="form-label-group">
                <ion-icon name="grid-outline" class="input-icon"></ion-icon>
                <select name="kategori" id="kategori" required>
                    <option value="" disabled {{ old('kategori') ? '' : 'selected' }}></option>
                    <option value="hardware"  {{ old('kategori')=='hardware'  ?'selected':'' }}>🖥️ Hardware</option>
                    <option value="software"  {{ old('kategori')=='software'  ?'selected':'' }}>💻 Software / Aplikasi</option>
                    <option value="jaringan"  {{ old('kategori')=='jaringan'  ?'selected':'' }}>🌐 Jaringan / Internet</option>
                    <option value="keamanan"  {{ old('kategori')=='keamanan'  ?'selected':'' }}>🔒 Keamanan Informasi</option>
                    <option value="akses"     {{ old('kategori')=='akses'     ?'selected':'' }}>🔑 Hak Akses / Login</option>
                    <option value="data"      {{ old('kategori')=='data'      ?'selected':'' }}>📂 Data / Backup</option>
                    <option value="lainnya"   {{ old('kategori')=='lainnya'   ?'selected':'' }}>📌 Lainnya</option>
                </select>
                <ion-icon name="chevron-down-outline" class="select-chevron"></ion-icon>
                <label for="kategori">Kategori Masalah</label>
            </div>

            {{-- Lokasi --}}
            <div class="form-label-group">
                <ion-icon name="location-outline" class="input-icon"></ion-icon>
                <input type="text" name="lokasi" id="lokasi" placeholder=" " value="{{ old('lokasi') }}" required>
                <label for="lokasi">Lokasi (Cth: Ruang Meeting Lt. 2)</label>
            </div>

            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasRole('it staff'))
            {{-- Prioritas --}}
            <p class="section-title">
                <ion-icon name="flag-outline" style="font-size:14px;"></ion-icon>
                Prioritas & Klasifikasi
            </p>

            <div class="form-label-group">
                <ion-icon name="flag-outline" class="input-icon"></ion-icon>
                <select name="prioritas" id="prioritas" required>
                    <option value="low"      {{ old('prioritas','medium')=='low'      ?'selected':'' }}>🟢 Low </option>
                    <option value="medium"   {{ old('prioritas','medium')=='medium'   ?'selected':'' }}>🔵 Medium</option>
                    <option value="high"     {{ old('prioritas','medium')=='high'     ?'selected':'' }}>🟠 High</option>
                    <option value="critical" {{ old('prioritas','medium')=='critical' ?'selected':'' }}>🔴 Critical</option>
                </select>
                <ion-icon name="chevron-down-outline" class="select-chevron"></ion-icon>
                <label for="prioritas">Prioritas</label>
            </div>

            {{-- Klasifikasi Data ISO 27001 --}}
            <div class="form-label-group">
                <ion-icon name="shield-checkmark-outline" class="input-icon"></ion-icon>
                <select name="klasifikasi_data" id="klasifikasi_data" required>
                    <option value="public"       {{ old('klasifikasi_data','internal')=='public'       ?'selected':'' }}>🟢 Public — Data umum</option>
                    <option value="internal"     {{ old('klasifikasi_data','internal')=='internal'     ?'selected':'' }}>🟡 Internal — Data internal perusahaan</option>
                    <option value="confidential" {{ old('klasifikasi_data','internal')=='confidential' ?'selected':'' }}>🔴 Confidential — Data rahasia</option>
                </select>
                <ion-icon name="chevron-down-outline" class="select-chevron"></ion-icon>
                <label for="klasifikasi_data">Klasifikasi Data (ISO 27001)</label>
            </div>
            @endif


            {{-- Dampak --}}
            <div class="form-label-group">
                <ion-icon name="people-outline" class="input-icon"></ion-icon>
                <select name="dampak" id="dampak" required>
                    <option value="individu"   {{ old('dampak','individu')=='individu'   ?'selected':'' }}>👤 Individu — Hanya saya</option>
                    <option value="departemen" {{ old('dampak','individu')=='departemen' ?'selected':'' }}>👥 Departemen — Satu departemen</option>
                    <option value="cabang"     {{ old('dampak','individu')=='cabang'     ?'selected':'' }}>🏢 Cabang — Satu cabang</option>
                    <option value="perusahaan" {{ old('dampak','individu')=='perusahaan' ?'selected':'' }}>🏭 Perusahaan — Seluruh perusahaan</option>
                </select>
                <ion-icon name="chevron-down-outline" class="select-chevron"></ion-icon>
                <label for="dampak">Dampak Masalah</label>
            </div>

            {{-- Cabang --}}
            @if(count($cabang) > 1)
            <div class="form-label-group">
                <ion-icon name="business-outline" class="input-icon"></ion-icon>
                <select name="kode_cabang" id="kode_cabang">
                    <option value="" disabled selected></option>
                    @foreach($cabang as $c)
                        <option value="{{ $c->kode_cabang }}" {{ old('kode_cabang')==$c->kode_cabang?'selected':'' }}>
                            {{ textUpperCase($c->nama_cabang) }}
                        </option>
                    @endforeach
                </select>
                <ion-icon name="chevron-down-outline" class="select-chevron"></ion-icon>
                <label for="kode_cabang">Cabang</label>
            </div>
            @else
                @foreach($cabang as $c)
                    <input type="hidden" name="kode_cabang" value="{{ $c->kode_cabang }}">
                @endforeach
            @endif

            {{-- SLA Info --}}
            <div class="sla-info">
                <ion-icon name="time-outline" style="font-size:18px;flex-shrink:0;margin-top:1px;"></ion-icon>
                <div>
                    <div style="font-weight:700;margin-bottom:2px;">SLA Response Time</div>
                    <div style="line-height:1.6;">🔴 Critical &nbsp;|&nbsp; 🟠 High &nbsp;|&nbsp; 🔵 Medium &nbsp;|&nbsp; 🟢 Low</div>
                </div>
            </div>

            {{-- Lampiran --}}
            <p class="section-title">
                <ion-icon name="attach-outline" style="font-size:14px;"></ion-icon>
                Lampiran (Opsional)
            </p>

            <div class="file-upload-area relative">
                <input type="file" name="lampiran" id="lampiran" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xlsx,.zip" onchange="updateFileName(this)">
                <ion-icon name="cloud-upload-outline" style="font-size:28px;color:{{ $t['primary'] ?? '#2d5a4c' }};display:block;margin:0 auto 6px;"></ion-icon>
                <div id="file-name-label" style="font-size:12px;color:{{ $t['primary'] ?? '#2d5a4c' }};font-weight:600;">Ketuk untuk pilih file</div>
                <div style="font-size:10px;color:#94a3b8;margin-top:3px;">Maks 5MB — jpg, png, pdf, doc, xlsx, zip</div>
            </div>

            <button type="submit" class="btn-submit-modern" id="btnKirim">
                <ion-icon name="paper-plane-outline"></ion-icon>
                <span>Kirim Tiket</span>
            </button>
        </form>
    </div>
</div>
@endsection

@push('myscript')
<script>
    function updateFileName(input) {
        const label = document.getElementById('file-name-label');
        if (input.files && input.files[0]) {
            label.textContent = input.files[0].name;
        } else {
            label.textContent = 'Ketuk untuk pilih file';
        }
    }

    document.getElementById('formTicket').addEventListener('submit', function(e) {
        const judul = document.getElementById('judul').value.trim();
        const deskripsi = document.getElementById('deskripsi').value.trim();
        const kategori = document.getElementById('kategori').value;
        const lokasi = document.getElementById('lokasi').value.trim();

        if (!judul) {
            e.preventDefault();
            Swal.fire({ title: 'Oops!', text: 'Judul pengaduan harus diisi!', icon: 'warning' });
            return;
        }
        if (!deskripsi) {
            e.preventDefault();
            Swal.fire({ title: 'Oops!', text: 'Deskripsi detail harus diisi!', icon: 'warning' });
            return;
        }
        if (!kategori) {
            e.preventDefault();
            Swal.fire({ title: 'Oops!', text: 'Pilih kategori masalah!', icon: 'warning' });
            return;
        }
        if (!lokasi) {
            e.preventDefault();
            Swal.fire({ title: 'Oops!', text: 'Lokasi Anda harus diisi!', icon: 'warning' });
            return;
        }

        const btn = document.getElementById('btnKirim');
        btn.disabled = true;
        btn.innerHTML = '<ion-icon name="sync-outline" class="animate-spin"></ion-icon><span>Mengirim...</span>';
    });
</script>
@endpush

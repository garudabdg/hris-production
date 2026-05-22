@extends('layouts.mobile.modern')

@section('title', 'ID Card')

@section('header_left')
    <a href="{{ route('dashboard.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/15 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <link rel="stylesheet" href="{{ asset('assets/css/idcard.css') }}">
@endpush

@section('content')
    <div class="idcard-page-layout">

        {{-- ── CARD ── --}}
        <div class="idcard-wrapper" id="idcard-area">

            {{-- Header strip --}}
            <div class="card-header-strip">
                @if ($generalsetting->logo && Storage::exists('public/logo/' . $generalsetting->logo))
                    <img src="{{ asset('storage/logo/' . $generalsetting->logo) }}" class="header-logo" alt="Logo">
                @else
                    <div class="header-logo-placeholder">{{ strtoupper(substr($generalsetting->nama_perusahaan ?? 'E', 0, 1)) }}</div>
                @endif
                <div class="header-company">
                    <div class="header-company-name">{{ $generalsetting->nama_perusahaan ?? 'E-Presensi' }}</div>
                    <div class="header-tag">Employee Pass</div>
                </div>
                <div class="header-badge">ID CARD</div>
            </div>

            {{-- Avatar overlap --}}
            <div class="avatar-overlap">
                <div class="avatar-outer">
                    <div class="avatar-inner">
                        @if (!empty($karyawan->foto))
                            <img src="{{ getfotoKaryawan($karyawan->foto) }}" alt="Foto">
                        @else
                            <img src="{{ asset('assets/template/img/sample/avatar/avatar1.jpg') }}" alt="Foto">
                        @endif
                    </div>
                </div>
            </div>

            {{-- Name & jabatan --}}
            <div class="card-identity">
                <div class="emp-name">{{ textUpperCase($karyawan->nama_karyawan) }}</div>
                <span class="emp-jabatan">{{ $karyawan->nama_jabatan }}</span>
            </div>

            <div class="card-divider"></div>

            {{-- Info list --}}
            <div class="card-info-list">
                <div class="info-item">
                    <div class="info-icon"><ion-icon name="finger-print-outline"></ion-icon></div>
                    <div class="info-text">
                        <span class="info-label">NIK / Employee ID</span>
                        <span class="info-value">{{ $karyawan->nik }}</span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><ion-icon name="grid-outline"></ion-icon></div>
                    <div class="info-text">
                        <span class="info-label">Departemen</span>
                        <span class="info-value">{{ $karyawan->nama_dept }}</span>
                    </div>
                </div>

            </div>

            {{-- Barcode --}}
            <div class="card-barcode">
                <div class="barcode-inner">
                    {!! DNS1D::getBarcodeHTML($karyawan->nik, 'C128', 1.6, 38, 'black') !!}
                    <span class="barcode-nik">{{ $karyawan->nik }}</span>
                </div>
            </div>

            {{-- Accent bar --}}
            <div class="card-accent-bar"></div>
        </div>

        {{-- ── DOWNLOAD BUTTON ── --}}
        <button id="btn-download-card" class="btn-download">
            <ion-icon name="cloud-download-outline" style="font-size:20px"></ion-icon>
            <span>SIMPAN KE GALERI</span>
        </button>

    </div>
@endsection

@push('myscript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
(function () {
    var btn  = document.getElementById('btn-download-card');
    var area = document.getElementById('idcard-area');
    if (!btn || !area) return;

    btn.addEventListener('click', function () {
        var orig = btn.innerHTML;
        btn.innerHTML = '<ion-icon name="sync-outline" style="font-size:20px"></ion-icon><span>MEMPROSES...</span>';
        btn.disabled = true;

        html2canvas(area, {
            backgroundColor: '#ffffff',
            scale: 3,
            useCORS: true,
            logging: false,
            width: area.offsetWidth,
            height: area.offsetHeight,
            windowWidth: area.offsetWidth,
            x: 0,
            y: 0
        }).then(function (canvas) {
            var link = document.createElement('a');
            link.download = 'IDCard_{{ $karyawan->nik }}.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            btn.innerHTML = orig;
            btn.disabled = false;
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Tersimpan!', text: 'ID Card berhasil diunduh.', timer: 2000, showConfirmButton: false });
            }
        }).catch(function (e) {
            btn.innerHTML = orig;
            btn.disabled = false;
            alert('Gagal mengunduh: ' + e.message);
        });
    });
})();
</script>
@endpush

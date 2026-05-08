@extends('layouts.mobile.modern')

@section('title', 'ID Card')

@section('header_left')
    <a href="{{ route('dashboard.index') }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/15 text-white active:scale-95 transition-all">
        <ion-icon name="chevron-back-outline" class="text-lg"></ion-icon>
    </a>
@endsection

@push('mystyle')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        :root {
            --card-primary: {{ $t['primary'] }};
            --card-primary-light: {{ $t['primary_light'] }};
        }

        .idcard-page-layout {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 16px 16px 40px;
            background: {{ $t['bg_body'] }};
            gap: 16px;
        }

        /* ── CARD ── */
        .idcard-wrapper {
            font-family: 'Inter', sans-serif;
            width: 360px;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06);
            position: relative;
        }

        /* ── HEADER STRIP ── */
        .card-header-strip {
            background: linear-gradient(135deg, var(--card-primary) 0%, var(--card-primary-light) 100%);
            padding: 24px 20px 56px;
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
            overflow: hidden;
        }
        .card-header-strip::before {
            content: '';
            position: absolute;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
            top: -60px; right: -60px;
        }
        .card-header-strip::after {
            content: '';
            position: absolute;
            width: 120px; height: 120px;
            background: rgba(255,255,255,0.04);
            border-radius: 50%;
            bottom: -30px; left: 20px;
        }
        .header-logo {
            height: 36px;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
        }
        .header-logo-placeholder {
            height: 36px; width: 36px;
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 800; font-size: 14px;
            flex-shrink: 0;
        }
        .header-company {
            flex: 1;
            min-width: 0;
        }
        .header-company-name {
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .header-tag {
            margin-top: 3px;
            font-size: 9px;
            color: rgba(255,255,255,0.7);
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .header-badge {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 8px;
            padding: 4px 10px;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
            flex-shrink: 0;
        }

        /* ── AVATAR (overlap header) ── */
        .avatar-overlap {
            display: flex;
            justify-content: center;
            margin-top: -44px;
            position: relative;
            z-index: 2;
        }
        .avatar-outer {
            width: 88px; height: 88px;
            border-radius: 50%;
            padding: 3px;
            background: linear-gradient(135deg, var(--card-primary), var(--card-primary-light));
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        .avatar-inner {
            width: 100%; height: 100%;
            border-radius: 50%;
            background: #fff;
            padding: 2px;
            overflow: hidden;
            display: flex; align-items: center; justify-content: center;
        }
        .avatar-inner img {
            width: 100%; height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        /* ── NAME & JABATAN ── */
        .card-identity {
            text-align: center;
            padding: 10px 20px 0;
        }
        .emp-name {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.2;
            letter-spacing: -0.3px;
        }
        .emp-jabatan {
            display: inline-block;
            margin-top: 6px;
            padding: 4px 14px;
            background: rgba(0,0,0,0.06);
            color: var(--card-primary);
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        /* ── DIVIDER ── */
        .card-divider {
            margin: 14px 20px 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        }

        /* ── INFO ROWS ── */
        .card-info-list {
            padding: 12px 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 12px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #f1f5f9;
        }
        .info-icon {
            width: 32px; height: 32px;
            background: #fff;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            color: var(--card-primary);
            font-size: 16px;
            flex-shrink: 0;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .info-text { display: flex; flex-direction: column; min-width: 0; }
        .info-label {
            font-size: 9px;
            color: #94a3b8;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 13px;
            color: #334155;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── BARCODE ── */
        .card-barcode {
            padding: 4px 20px 20px;
            text-align: center;
        }
        .barcode-inner {
            display: inline-block;
            background: #fff;
            border: 1px dashed #e2e8f0;
            border-radius: 12px;
            padding: 10px 14px 6px;
        }
        .barcode-nik {
            display: block;
            margin-top: 4px;
            font-size: 11px;
            letter-spacing: 3px;
            color: #64748b;
            font-weight: 700;
        }

        /* ── BOTTOM ACCENT ── */
        .card-accent-bar {
            height: 6px;
            background: linear-gradient(90deg, var(--card-primary), var(--card-primary-light));
        }

        /* ── DOWNLOAD BUTTON ── */
        .btn-download {
            width: 100%;
            max-width: 360px;
            background: var(--card-primary);
            color: #fff;
            border: none;
            border-radius: 16px;
            padding: 15px 20px;
            font-size: 15px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }
        .btn-download:active { transform: scale(0.97); opacity: 0.9; }
    </style>
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

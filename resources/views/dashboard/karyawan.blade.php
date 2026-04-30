<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="{{ $t['primary'] }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: {{ $t['bg_body'] ?? '#e8f0ed' }};
            -webkit-tap-highlight-color: transparent;
        }
        .hero-bg {
            background-color: {{ $t['primary'] ?? '#2d5a4c' }};
            border-bottom-left-radius: 40px;
            border-bottom-right-radius: 40px;
            position: relative;
            padding-bottom: 80px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .glass-icon {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-radius: 12px;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .glass-icon:active { transform: scale(0.92); background: rgba(255, 255, 255, 0.2); }
        #jam {
            text-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .fade-in {
            animation: fadeIn 0.4s ease-out forwards;
            opacity: 0;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Avatar Enhancement */
        .avatar-wrapper {
            position: relative;
            width: 84px;
            height: 84px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .avatar-wrapper:active { transform: scale(0.9); }
        .avatar-inner {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            overflow: hidden;
            position: relative;
            z-index: 2;
        }
        .avatar-pulse {
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.15);
            z-index: 1;
            animation: avatar-pulse 3s infinite;
        }
        @keyframes avatar-pulse {
            0% { transform: scale(1); opacity: 0.2; }
            50% { transform: scale(1.1); opacity: 0.1; }
            100% { transform: scale(1); opacity: 0.2; }
        }
        .alert-cream  { background-color: #fff3cd; border: 1px solid #ffeeba; }
        .alert-danger  { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .alert-info    { background-color: #e3f2fd; border: 1px solid #b8daff; }
        .dot { height: 6px; width: 6px; background: {{ ($t['primary'] ?? '#2d5a4c') }}33; border-radius: 50%; display: inline-block; margin: 0 4px; transition: all .3s; }
        .dot.active { width: 18px; border-radius: 10px; background: {{ $t['primary'] ?? '#2d5a4c' }}; }

        /* Slide carousel */
        .carousel-wrapper { width: 100%; overflow: hidden; position: relative; border-radius: 15px; }
        .carousel-track { display: flex; transition: transform 0.5s ease; width: 100%; }
        .carousel-track .alert-slide { width: 100%; flex: 0 0 100%; flex-shrink: 0; box-sizing: border-box; }
        .alert-slide-content { display: grid; grid-template-columns: 36px minmax(0, 1fr); gap: 12px; align-items: start; width: 100%; }
        .alert-slide-text { min-width: 0; width: 100%; }
        .alert-slide-text-wrapper { width: 100%; display: block; }
        .alert-slide-text h4, .alert-slide-text p, .alert-slide-text span { 
            white-space: normal !important; 
            overflow-wrap: break-word !important; 
            word-wrap: break-word !important;
            word-break: normal !important;
            display: block;
        }
    </style>
</head>
<body>
    <div class="max-w-lg mx-auto min-h-screen">

        {{-- ===== HERO SECTION ===== --}}
        <div class="hero-bg px-5 pt-6 pb-14 text-white overflow-hidden relative">
            {{-- Top Icons --}}
            <div class="flex justify-between items-center mb-3 relative z-10">
                <button id="notificationBtn" class="glass-icon relative" style="border: none; background: transparent; cursor: pointer;">
                    <ion-icon name="notifications-outline" style="font-size:24px;"></ion-icon>
                    @php
                        $user = auth()->user();
                        $userkaryawan = null;
                        if ($user->hasRole('karyawan')) {
                            $userkaryawan = \App\Models\Userkaryawan::where('id_user', $user->id)->first();
                        }
                        
                        $unreadNotifCount = \Illuminate\Notifications\DatabaseNotification::where('notifiable_id', $user->id)
                            ->whereNull('read_at')
                            ->get()
                            ->filter(function($notif) use ($user, $userkaryawan) {
                                if (($notif->data['type'] ?? null) !== 'approval_status') {
                                    return true;
                                }
                                if ($user->hasRole('karyawan') && $userkaryawan) {
                                    $approvalCode = $notif->data['approval_code'] ?? null;
                                    $approvalType = $notif->data['approval_type'] ?? null;
                                    
                                    if ($approvalType === 'IZIN_SAKIT') {
                                        return \App\Models\Izinsakit::where('kode_izin_sakit', $approvalCode)->where('nik', $userkaryawan->nik)->exists();
                                    } elseif ($approvalType === 'IZIN_ABSEN') {
                                        return \App\Models\Izinabsen::where('kode_izin', $approvalCode)->where('nik', $userkaryawan->nik)->exists();
                                    } elseif ($approvalType === 'IZIN_CUTI') {
                                        return \App\Models\Izincuti::where('kode_izin_cuti', $approvalCode)->where('nik', $userkaryawan->nik)->exists();
                                    } elseif ($approvalType === 'IZIN_DINAS') {
                                        return \App\Models\Izindinas::where('kode_izin_dinas', $approvalCode)->where('nik', $userkaryawan->nik)->exists();
                                    }
                                    return false;
                                }
                                return true;
                            })
                            ->count();
                    @endphp
                    @if($unreadNotifCount > 0)
                        <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-500 rounded-full border-2 border-[{{ $t['primary'] ?? '#2d5a4c' }}] text-[9px] font-bold flex items-center justify-center px-1 shadow-sm">{{ $unreadNotifCount }}</span>
                    @endif
                </button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="#" onclick="event.preventDefault(); this.closest('form').submit();" class="glass-icon">
                        <ion-icon name="exit-outline" style="font-size:24px;"></ion-icon>
                    </a>
                </form>
            </div>

            {{-- Notification Dropdown Modal --}}
            <div id="notificationModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; padding: 20px;">
                <div style="position: absolute; top: 100px; right: 20px; width: 400px; max-width: 90vw; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-height: 70vh; overflow-y: auto; color: #333;">
                    <div style="padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: white;">
                        <h6 style="margin: 0; font-size: 16px; font-weight: 700;">Notifikasi</h6>
                        <button onclick="closeNotificationModal()" style="border: none; background: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
                    </div>
                    <div id="notificationList" style="padding: 0;">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            {{-- User Row --}}
            <div class="flex justify-between items-start mb-0 relative z-10">
                {{-- Left: Name --}}
                <div class="fade-in" style="animation-delay:.05s">
                    <h3 style="font-size:20px; font-weight:800; line-height:1.1;">{{ $karyawan->nama_karyawan }}👋</h3>
                    <span style="font-size:13px; font-weight:400; opacity:.8; display:block; margin-top:2px;">{{ $karyawan->nama_jabatan }} ({{ $karyawan->nama_dept }})</span>
                    @if($karyawan->tanggal_lahir)
                        <span style="font-size:12px; font-weight:400; opacity:.7; display:block; margin-top:2px;">🎂 {{ DateToIndo($karyawan->tanggal_lahir) }}</span>
                    @endif
                </div>
                {{-- Right: Avatar --}}
                <a href="{{ route('profile.index') }}" class="fade-in group" style="animation-delay:.1s">
                    <div class="avatar-wrapper">
                        <div class="avatar-pulse"></div>
                        <div class="avatar-inner">
                            @if (!empty($karyawan->foto) && Storage::disk('public')->exists('/karyawan/' . $karyawan->foto))
                                <div style="width:100%; height:100%; background-image:url({{ getfotoKaryawan($karyawan->foto) }}); background-size:cover; background-position:center;"></div>
                            @else
                                <img src="{{ asset('assets/template/img/sample/avatar/avatar1.jpg') }}" style="width:100%; height:100%; object-fit:cover;">
                            @endif
                        </div>
                    </div>
                </a>
            </div>

            {{-- Clock --}}
            <div class="text-center mt-0 mb-4 fade-in relative z-10" style="animation-delay:.15s">
                <h2 id="jam" style="font-size:44px; font-weight:900; letter-spacing:-2px; line-height:1; margin-bottom:6px;">0:00:00</h2>
                <span style="font-size:14px; font-weight:400; opacity:.85;">Hari ini : {{ getNamaHari(date('D')) }}, {{ DateToIndo(date('Y-m-d')) }}</span>
            </div>
        </div>

        {{-- ===== FLOATING ALERT CARD ===== --}}
        <div style="margin-top:-60px; padding:0 20px; position:relative; z-index:10;">
            @php
                $activeAlerts = [];
                if (!empty($pengumuman)) { $activeAlerts[] = 'pengumuman'; }
                if (!empty($notif_kontrak)) { $activeAlerts[] = 'kontrak'; }
                if (!empty($notif_sp)) { $activeAlerts[] = 'sp'; }
            @endphp

            @if (count($activeAlerts) > 0)
                <div id="alertCarousel" class="carousel-wrapper rounded-[15px] shadow-md">
                    <div class="carousel-track">
                    @foreach ($activeAlerts as $idx => $type)
                        <div class="alert-slide rounded-[15px] p-4" style="
                            @if($type == 'kontrak') background-color:#fff3cd; border:1px solid #ffeeba;
                            @elseif($type == 'sp') background-color:#f8d7da; border:1px solid #f5c6cb;
                            @elseif($type == 'pengumuman') background-color:#e3f2fd; border:1px solid #b8daff;
                            @endif">
                            <div class="alert-slide-content">
                                {{-- Icon --}}
                                <div class="shrink-0 w-[36px] h-[36px] rounded-full flex items-center justify-center"
                                    style="@if($type=='kontrak') background:rgba(255,193,7,.2); @elseif($type=='sp') background:rgba(220,53,69,.2); @else background:rgba(12,84,96,.15); @endif">
                                    @if($type == 'kontrak')
                                        <ion-icon name="alert-circle" style="font-size:24px; color:#ffc107;"></ion-icon>
                                    @elseif($type == 'sp')
                                        <ion-icon name="warning" style="font-size:24px; color:#dc3545;"></ion-icon>
                                    @else
                                        <ion-icon name="megaphone" style="font-size:24px; color:#0c5460;"></ion-icon>
                                    @endif
                                    </div>
                                {{-- Text --}}
                                <div class="alert-slide-text">
                                    <div class="alert-slide-text-wrapper">
                                    @if($type == 'kontrak')
                                        <h4 style="font-size:14px; font-weight:700; color:#856404; margin:0 0 4px 0; letter-spacing: -0.2px;">Masa Kontrak Segera Berakhir</h4>
                                        <p style="font-size:12px; color:#856404; opacity:.85; line-height:1.5; margin:0;">
                                            Sisa masa kontrak Anda adalah <strong>{{ $notif_kontrak['sisa_hari'] }} hari</strong>.<br> (Selesai: {{ $notif_kontrak['tanggal_akhir'] }}).
                                        </p>
                                        <span style="font-size:11px; color:#856404; opacity:.6; margin-top:6px; display:inline-block; font-weight: 500;">Mohon segera koordinasi dengan HRD.</span>
                                    @elseif($type == 'sp')
                                        <h4 style="font-size:14px; font-weight:700; color:#721c24; margin:0 0 4px 0; letter-spacing: -0.2px;">Peringatan Disiplin</h4>
                                        <p style="font-size:12px; color:#721c24; opacity:.85; line-height:1.5; margin:0;">
                                            Status aktif: <strong>{{ $notif_sp->jenis_sp }}</strong>.<br>
                                            Berlaku s/d: <strong>{{ \Carbon\Carbon::parse($notif_sp->sampai)->translatedFormat('d F Y') }}</strong>.
                                        </p>
                                        <span style="font-size:11px; color:#721c24; opacity:.6; margin-top:6px; display:inline-block; font-weight: 500;">Tetap jaga profesionalisme kerja Anda.</span>
                                    @elseif($type == 'pengumuman')
                                        <h4 style="font-size:14px; font-weight:700; color:#0c5460; margin:0 0 2px 0; letter-spacing: -0.2px;">{{ $pengumuman->judul }}</h4>
                                        <span style="font-size:10px; color:#0c5460; opacity:.6; font-weight: 600;">{{ \Carbon\Carbon::parse($pengumuman->created_at)->translatedFormat('d F Y') }}</span>
                                        <div style="font-size:12px; color:#0c5460; opacity:.85; margin-top:6px; line-height:1.5;">{!! Str::limit($pengumuman->isi, 100) !!}</div>
                                    @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>

                {{-- Dots --}}
                @if(count($activeAlerts) > 1)
                    <div class="flex justify-center mt-3 gap-1">
                        @foreach($activeAlerts as $idx => $type)
                            <span class="dot {{ $idx == 0 ? 'active' : '' }}" data-idx="{{ $idx }}"></span>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>

        {{-- ===== ATTENDANCE SECTION ===== --}}
        <div class="px-5 mt-3 fade-in" style="animation-delay:.2s">
            <div class="bg-white rounded-[15px] py-6 px-5 shadow-sm border border-gray-100 flex items-center">
                {{-- Jam Masuk --}}
                <div class="flex-1 flex items-center gap-3">
                    <div class="flex items-center justify-center w-[40px] h-[40px] rounded-full overflow-hidden bg-gray-50 border border-gray-100">
                        @if (!empty($presensi->foto_in) && Storage::disk('public')->exists('/uploads/absensi/' . $presensi->foto_in))
                            <img src="{{ url('/storage/uploads/absensi/' . $presensi->foto_in) }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <ion-icon name="camera-outline" style="font-size:32px; color: {{ $t['primary'] ?? '#2d5a4c' }}; grayscale: 0.2;"></ion-icon>
                        @endif
                    </div>
                    <div>
                        <span class="block text-[14px] font-bold text-gray-800" style="letter-spacing:-0.2px; line-height: 1.2;">Jam Masuk</span>
                        <span class="block text-[16px] font-bold text-gray-400 mt-1" style="letter-spacing: 1px;">{{ !empty($presensi->jam_in) ? date('H:i', strtotime($presensi->jam_in)) : '-- : --' }}</span>
                    </div>
                </div>

                {{-- Vertical Separator --}}
                <div class="w-[1.5px] h-[35px] bg-gray-100 mx-2"></div>

                {{-- Jam Pulang --}}
                <div class="flex-1 flex items-center gap-3 pl-4">
                    <div class="flex items-center justify-center w-[40px] h-[40px] rounded-full overflow-hidden bg-gray-50 border border-gray-100">
                        @if (!empty($presensi->foto_out) && Storage::disk('public')->exists('/uploads/absensi/' . $presensi->foto_out))
                            <img src="{{ url('/storage/uploads/absensi/' . $presensi->foto_out) }}" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <ion-icon name="camera-outline" style="font-size:32px; color: {{ $t['primary'] ?? '#2d5a4c' }}; grayscale: 0.2;"></ion-icon>
                        @endif
                    </div>
                    <div>
                        <span class="block text-[14px] font-bold text-gray-800" style="letter-spacing:-0.2px; line-height: 1.2;">Jam Pulang</span>
                        <span class="block text-[16px] font-bold text-gray-400 mt-1" style="letter-spacing: 1px;">{{ !empty($presensi->jam_out) ? date('H:i', strtotime($presensi->jam_out)) : '-- : --' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== ATTENDANCE RECAP SECTION ===== --}}
        <div class="px-5 mt-3 fade-in" style="animation-delay:.25s">
            <div class="bg-white rounded-[15px] py-3 shadow-sm border border-gray-100 text-center">
                <h4 style="font-size:15px; font-weight:700; color:#444; margin-bottom:2px; letter-spacing:-0.2px;">Rekap Presensi Bulan {{ $bulan_skrg }}</h4>
                <span style="font-size:12px; font-weight:400; color:#999; display:block; margin-bottom:8px;">Update Terakhir: {{ date('H:i') }} WIB</span>

                <div class="flex items-center">
                    {{-- Hadir --}}
                    <div class="flex-1">
                        <span class="block text-[28px] font-bold" style="color: {{ $t['primary'] ?? '#2d5a4c' }}; line-height: 1.1;">{{ $rekappresensi->hadir ?? 0 }}</span>
                        <span class="block text-[12px] font-normal text-gray-400 mt-1">Hadir</span>
                    </div>

                    {{-- Separator --}}
                    <div class="w-[1px] h-[40px] bg-gray-100"></div>

                    {{-- Sakit --}}
                    <div class="flex-1">
                        <span class="block text-[28px] font-bold" style="color: #ff9800; line-height: 1.1;">{{ $rekappresensi->sakit ?? 0 }}</span>
                        <span class="block text-[12px] font-normal text-gray-400 mt-1">Sakit</span>
                    </div>

                    {{-- Separator --}}
                    <div class="w-[1px] h-[40px] bg-gray-100"></div>

                    {{-- Izin --}}
                    <div class="flex-1">
                        <span class="block text-[28px] font-bold" style="color: #2196f3; line-height: 1.1;">{{ $rekappresensi->izin ?? 0 }}</span>
                        <span class="block text-[12px] font-normal text-gray-400 mt-1">Izin</span>
                    </div>

                    {{-- Separator --}}
                    <div class="w-[1px] h-[40px] bg-gray-100"></div>

                    {{-- Cuti --}}
                    <div class="flex-1">
                        <span class="block text-[28px] font-bold" style="color: #ff5252; line-height: 1.1;">{{ $rekappresensi->cuti ?? 0 }}</span>
                        <span class="block text-[12px] font-normal text-gray-400 mt-1">Cuti</span>
                    </div>
                </div>
            </div>
        </div>


        @php
            $scheme = $general_setting->mobile_theme_scheme ?? 'green';
        @endphp
        {{-- ===== MENU GRID ===== --}}
        <div class="px-4 mt-4 fade-in" style="animation-delay:.3s">
            <div class="grid grid-cols-4 gap-2">
                {{-- ID Card --}}
                <a href="{{ route('karyawan.idcard', Crypt::encrypt($karyawan->nik)) }}" class="block">
                    <div class="bg-white rounded-[12px] text-center" style="padding:5px 5px; line-height:0.8rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                        @if ($scheme == 'green')
                            <img src="{{ asset('assets/template/img/3d/card.webp') }}" alt="" style="width:40px; margin:0 auto 0;">
                        @else
                            <ion-icon name="id-card-outline" style="font-size:40px; color: {{ $t['primary'] ?? '#2d5a4c' }}; margin-bottom:0;"></ion-icon>
                        @endif
                        <br><span style="font-size:0.75rem; font-weight:400; color: {{ $t['primary'] ?? '#2d5a4c' }};">ID Card</span>
                    </div>
                </a>

                {{-- Istirahat (DISABLED) --}}
                {{--
                <a href="{{ route('presensiistirahat.create') }}" class="block">
                    <div class="bg-white rounded-[12px] text-center" style="padding:5px 5px; line-height:0.8rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                        @if ($scheme == 'green')
                            <img src="{{ asset('assets/template/img/3d/praying.png') }}" alt="" style="width:40px; margin:0 auto 0;">
                        @else
                            <ion-icon name="cafe-outline" style="font-size:40px; color: {{ $t['primary'] ?? '#2d5a4c' }}; margin-bottom:0;"></ion-icon>
                        @endif
                        <br><span style="font-size:0.75rem; font-weight:400; color: {{ $t['primary'] ?? '#2d5a4c' }};">Istirahat</span>
                    </div>
                </a>
                --}}

                {{-- Lembur --}}
                @if(!isset($hideLembur) || !$hideLembur)
                    <a href="{{ route('lembur.index') }}" class="block">
                        <div class="bg-white rounded-[12px] text-center" style="padding:5px 5px; line-height:0.8rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                            @if ($scheme == 'green')
                                <img src="{{ asset('assets/template/img/3d/clock.png') }}" alt="" style="width:40px; margin:0 auto 0;">
                            @else
                                <ion-icon name="time-outline" style="font-size:40px; color: {{ $t['primary'] ?? '#2d5a4c' }}; margin-bottom:0;"></ion-icon>
                            @endif
                            <br><span style="font-size:0.75rem; font-weight:400; color: {{ $t['primary'] ?? '#2d5a4c' }};">Lembur</span>
                        </div>
                    </a>
                @endif

                {{-- Slip Gaji --}}
                <a href="{{ route('slipgaji.index') }}" class="block">
                    <div class="bg-white rounded-[12px] text-center" style="padding:5px 5px; line-height:0.8rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                        @if ($scheme == 'green')
                            <img src="{{ asset('assets/template/img/3d/slipgaji.png') }}" alt="" style="width:40px; margin:0 auto 0;">
                        @else
                            <ion-icon name="cash-outline" style="font-size:40px; color: {{ $t['primary'] ?? '#2d5a4c' }}; margin-bottom:0;"></ion-icon>
                        @endif
                        <br><span style="font-size:0.75rem; font-weight:400; color: {{ $t['primary'] ?? '#2d5a4c' }};">Slip Gaji</span>
                    </div>
                </a>

                {{-- Aktivitas --}}
                @can('aktivitaskaryawan.index')
                <a href="{{ route('aktivitaskaryawan.index') }}" class="block">
                    <div class="bg-white rounded-[12px] text-center" style="padding:5px 5px; line-height:0.8rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                        @if ($scheme == 'green')
                            <img src="{{ asset('assets/template/img/3d/activity.png') }}" alt="" style="width:40px; margin:0 auto 0;">
                        @else
                            <ion-icon name="pulse-outline" style="font-size:40px; color: {{ $t['primary'] ?? '#2d5a4c' }}; margin-bottom:0;"></ion-icon>
                        @endif
                        <br><span style="font-size:0.75rem; font-weight:400; color: {{ $t['primary'] ?? '#2d5a4c' }};">Aktivitas</span>
                    </div>
                </a>
                @endcan

                {{-- Visit --}}
                @can('kunjungan.index')
                <a href="{{ route('kunjungan.index') }}" class="block">
                    <div class="bg-white rounded-[12px] text-center" style="padding:5px 5px; line-height:0.8rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                        @if ($scheme == 'green')
                            <img src="{{ asset('assets/template/img/3d/maps.png') }}" alt="" style="width:40px; margin:0 auto 0;">
                        @else
                            <ion-icon name="map-outline" style="font-size:40px; color: {{ $t['primary'] ?? '#2d5a4c' }}; margin-bottom:0;"></ion-icon>
                        @endif
                        <br><span style="font-size:0.75rem; font-weight:400; color: {{ $t['primary'] ?? '#2d5a4c' }};">Visit</span>
                    </div>
                </a>
                @endcan

                {{-- Scan Wajah --}}
                <a href="javascript:void(0)" id="btnDaftarkanWajah" class="block">
                    <div class="bg-white rounded-[12px] text-center" style="padding:5px 5px; line-height:0.8rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                        @if ($scheme == 'green')
                            <img src="{{ asset('assets/template/img/3d/scanwajah.png') }}" alt="" style="width:40px; margin:0 auto 0;">
                        @else
                            <ion-icon name="scan-outline" style="font-size:40px; color: {{ $t['primary'] ?? '#2d5a4c' }}; margin-bottom:0;"></ion-icon>
                        @endif
                        <br><span style="font-size:0.75rem; font-weight:400; color: {{ $t['primary'] ?? '#2d5a4c' }};">Wajah</span>
                    </div>
                </a>

                {{-- Lainnya --}}
                <a href="{{ route('shortcut.index') }}" class="block">
                    <div class="bg-white rounded-[12px] shadow-sm text-center" style="padding:5px 5px; line-height:0.8rem;">
                        <ion-icon name="apps-outline" style="font-size:40px; color: {{ $t['primary'] ?? '#2d5a4c' }}; margin-bottom:2px;"></ion-icon>
                        <br><span style="font-size:0.75rem; font-weight:400; color: {{ $t['primary'] ?? '#2d5a4c' }};">Lainnya</span>
                    </div>
                </a>

                {{-- IT Ticket --}}
                @can('it-ticket.index')
                <a href="{{ route('it-ticket.index') }}" class="block">
                    <div class="bg-white rounded-[12px] text-center relative" style="padding:5px 5px; line-height:0.8rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                        @php
                            $myOpenTickets = \App\Models\ItTicket::where('pemohon_id', auth()->id())
                                ->whereIn('status', ['open','in_progress','pending'])
                                ->count();
                        @endphp
                        @if($myOpenTickets > 0)
                            <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] bg-red-500 rounded-full text-white text-[9px] font-bold flex items-center justify-center px-1 z-10">{{ $myOpenTickets }}</span>
                        @endif
                        <ion-icon name="headset-outline" style="font-size:40px; color: {{ $t['primary'] ?? '#2d5a4c' }}; margin-bottom:0;"></ion-icon>
                        <br><span style="font-size:0.75rem; font-weight:400; color: {{ $t['primary'] ?? '#2d5a4c' }};">IT Ticket</span>
                    </div>
                </a>
                @endcan

            </div>
        </div>

        {{-- ===== HISTORY LIST ===== --}}
        <div class="px-4 mt-5 fade-in" style="animation-delay:.35s; margin-bottom:30px;">
            {{-- Tabs --}}
            <div class="flex rounded-full overflow-hidden border border-gray-200 mb-3" style="background:#f5f5f5;">
                <button id="tabPresensi" onclick="switchTab('presensi')" class="flex-1 py-2 text-center text-[13px] font-semibold transition-all rounded-full" style="background:{{ $t['primary'] ?? '#2d5a4c' }}; color:white;">
                    30 Hari terakhir
                </button>
                @if(!isset($hideLembur) || !$hideLembur)
                    <button id="tabLembur" onclick="switchTab('lembur')" class="flex-1 py-2 text-center text-[13px] font-medium transition-all rounded-full flex items-center justify-center gap-1" style="color:#888;">
                        Lembur
                        @if(isset($notiflembur) && $notiflembur > 0)
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-white text-[10px] font-bold" style="background:#ff5252;">{{ $notiflembur }}</span>
                        @endif
                    </button>
                @endif
            </div>

            {{-- Tab Content: Presensi --}}
            <div id="contentPresensi">
                @foreach ($datapresensi as $d)
                    @php
                        $namahari = ['Sun'=>'Minggu','Mon'=>'Senin','Tue'=>'Selasa','Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu'];
                        $day_eng = date('D', strtotime($d->tanggal));
                        $day_indo = $namahari[$day_eng] ?? $day_eng;
                        $day_short = strtoupper(substr($day_indo, 0, 3));
                        $tgl = date('d', strtotime($d->tanggal));

                        $text_color = $d->status == 'h' ? ($t['primary'] ?? '#2d5a4c') : ($d->status == 'i' ? '#1e90ff' : ($d->status == 's' ? '#ff6384' : ($d->status == 'c' ? '#ff9f40' : '#e74c3c')));
                        $bg_color = $d->status == 'h' ? (($t['primary'] ?? '#2d5a4c') . '18') : ($d->status == 'i' ? '#1e90ff18' : ($d->status == 's' ? '#ff638418' : ($d->status == 'c' ? '#ff9f4018' : '#e74c3c18')));
                    @endphp
                    <div class="bg-white rounded-[12px] mb-2 p-3 flex items-center gap-3" style="border:1px solid {{ $text_color }}4d; box-shadow: 0 1px 4px rgba(0,0,0,0.02);">
                        {{-- Day Badge --}}
                        <div class="shrink-0 flex flex-col items-center justify-center rounded-[10px]" style="width:45px; height:45px; background:{{ $bg_color }};">
                            <span style="font-size:10px; font-weight:700; color:{{ $text_color }}; line-height:1;">{{ $day_short }}</span>
                            <span style="font-size:16px; font-weight:800; color:{{ $text_color }}; line-height:1.2;">{{ $tgl }}</span>
                        </div>
                        {{-- Details --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center">
                                <h5 style="font-size:14px; font-weight:600; color:#333; margin:0;">{{ DateToIndo($d->tanggal) }}</h5>
                                <span style="background:#f8f9fa; color:#666; font-size:10px; border:1px solid #eee; padding:2px 6px; border-radius:20px; white-space:nowrap;">
                                    {{ $d->nama_jam_kerja }} ({{ date('H:i', strtotime($d->jam_masuk)) }} - {{ date('H:i', strtotime($d->jam_pulang)) }})
                                </span>
                            </div>
                            <div style="margin-top:2px;">
                                @if ($d->status == 'h')
                                    @php
                                        $jam_in_ts = strtotime($d->jam_in);
                                        $jam_masuk_ts = strtotime($d->tanggal . ' ' . $d->jam_masuk);
                                        $is_late = $jam_in_ts > $jam_masuk_ts;
                                        $jam_telat = 0; $menit_telat = 0; $desimal_terlambat = 0;
                                        if ($is_late) {
                                            $terlambat_selisih = $jam_in_ts - $jam_masuk_ts;
                                            $jam_telat = floor($terlambat_selisih / 3600);
                                            $sisa = $terlambat_selisih % 3600;
                                            $menit_telat = floor($sisa / 60);
                                            $desimal_terlambat = $jam_telat + round($menit_telat / 60, 2);
                                        }
                                        $denda_display = 0; $potongan_jam = 0; $potongan_jam_terlambat = 0; $pulangcepat = 0; $potongan_tidak_scan = 0;
                                        $denda_dari_db = !empty($d->denda) ? $d->denda : null;
                                        if ($denda_dari_db !== null) {
                                            $denda_display = $denda_dari_db;
                                            if ($is_late && $desimal_terlambat >= 1) { $potongan_jam_terlambat = $desimal_terlambat > $d->total_jam ? $d->total_jam : $desimal_terlambat; }
                                        } else {
                                            if ($is_late) {
                                                if ($desimal_terlambat < 1) { $denda_display = hitungdenda($denda_list, $menit_telat); } else { $potongan_jam_terlambat = $desimal_terlambat > $d->total_jam ? $d->total_jam : $desimal_terlambat; }
                                            }
                                        }
                                        $pulangcepat = hitungpulangcepat($d->tanggal, $d->jam_out, $d->jam_pulang, $d->istirahat, $d->jam_awal_istirahat, $d->jam_akhir_istirahat, $d->lintashari);
                                        $pulangcepat = $pulangcepat > $d->total_jam ? $d->total_jam : $pulangcepat;
                                        if ($d->tanggal != date('Y-m-d') && (empty($d->jam_out) || empty($d->jam_in))) { $potongan_tidak_scan = $d->total_jam; }
                                        $potongan_jam = $potongan_tidak_scan > 0 ? $potongan_tidak_scan : ($pulangcepat + $potongan_jam_terlambat);
                                        $status_potongan_row = isset($d->status_potongan) ? $d->status_potongan : $namasettings->status_potongan_jam;
                                        if ($status_potongan_row == 0) { $potongan_jam = 0; $denda_display = 0; }
                                    @endphp
                                    <div class="flex justify-between items-center">
                                        <span style="color:#555; font-size:12px; font-weight:500;">
                                            {{ $d->jam_in != null ? date('H:i', strtotime($d->jam_in)) : '__:__' }}
                                            <span style="color:#ccc; margin:0 4px;">-</span>
                                            {{ $d->jam_out != null ? date('H:i', strtotime($d->jam_out)) : '__:__' }}
                                        </span>
                                        @if ($is_late)
                                            <span style="background:#ff525218; color:#ff5252; font-size:10px; padding:1px 6px; border-radius:10px; font-weight:600;">Telat {{ $jam_telat > 0 ? $jam_telat . 'j ' : '' }}{{ $menit_telat }}m</span>
                                        @else
                                            <span style="background:{{ ($t['primary'] ?? '#2d5a4c') }}18; color:{{ $t['primary'] ?? '#2d5a4c' }}; font-size:10px; padding:1px 6px; border-radius:10px; font-weight:600;">Tepat Waktu</span>
                                        @endif
                                    </div>
                                    @if ($d->jam_in != null)
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @if ($denda_display > 0)
                                                <span style="background:#ff525218; color:#ff5252; font-size:10px; padding:1px 6px; border-radius:10px;">Denda Rp. {{ number_format($denda_display) }}</span>
                                            @endif
                                            @if ($pulangcepat > 0)
                                                <span style="background:#ff525218; color:#ff5252; font-size:10px; padding:1px 6px; border-radius:10px;">Pulang Cepat</span>
                                            @endif
                                            @if ($potongan_jam > 0 && ($d->jam_out != null || $d->tanggal != date('Y-m-d')))
                                                @if ($namasettings->status_potongan_jam == 1 || (isset($d->status_potongan) && $d->status_potongan == 1))
                                                    <span style="background:#ff525218; color:#ff5252; font-size:10px; padding:1px 6px; border-radius:10px;">PJ: {{ number_format($potongan_jam, 2) }} Jam</span>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                @elseif ($d->status == 'i')
                                    <span style="color:#1e90ff; font-size:12px;">Izin: {{ $d->keterangan_izin }}</span>
                                @elseif ($d->status == 's')
                                    <span style="color:#ff6384; font-size:12px;">Sakit: {{ $d->keterangan_izin_sakit }}</span>
                                @elseif ($d->status == 'c')
                                    <span style="color:#ff9f40; font-size:12px;">Cuti: {{ $d->keterangan_izin_cuti }}</span>
                                @elseif ($d->status == 'a')
                                    @php
                                        $potongan_jam = $d->total_jam;
                                        $denda_display = !empty($d->denda) ? $d->denda : 0;
                                        $status_potongan_row = isset($d->status_potongan) ? $d->status_potongan : $namasettings->status_potongan_jam;
                                        if ($status_potongan_row == 0) { $potongan_jam = 0; }
                                    @endphp
                                    <span style="color:#e74c3c; font-size:12px;">Alpha: Tanpa Keterangan</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Tab Content: Lembur --}}
            @if(!isset($hideLembur) || !$hideLembur)
                <div id="contentLembur" style="display:none;">
                    @foreach ($lembur as $d)
                        <a href="{{ route('lembur.createpresensi', Crypt::encrypt($d->id)) }}" class="block">
                            <div class="bg-white rounded-[12px] mb-2 p-3 flex items-center gap-3" style="border:1px solid #1f7ee420; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                                <div class="shrink-0 flex items-center justify-center rounded-[10px]" style="width:45px; height:45px; background:#1f7ee418;">
                                    <ion-icon name="timer-outline" style="font-size:24px; color:#1f7ee4;"></ion-icon>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h5 style="font-size:14px; font-weight:600; color:#333; margin:0;">{{ DateToIndo($d->tanggal) }}</h5>
                                    <p style="font-size:12px; color:#888; margin:2px 0;">{{ $d->keterangan }}</p>
                                    <div class="flex items-center gap-1 flex-wrap">
                                        @if ($d->lembur_in != null)
                                            <span style="background:#28a74518; color:#28a745; font-size:10px; padding:1px 6px; border-radius:10px;">{{ date('H:i', strtotime($d->lembur_in)) }}</span>
                                        @else
                                            <span style="background:#ff525218; color:#ff5252; font-size:10px; padding:1px 6px; border-radius:10px;">Belum Absen</span>
                                        @endif
                                        <span style="color:#ccc; font-size:10px;">-</span>
                                        @if ($d->lembur_out != null)
                                            <span style="background:#28a74518; color:#28a745; font-size:10px; padding:1px 6px; border-radius:10px;">{{ date('H:i', strtotime($d->lembur_out)) }}</span>
                                        @else
                                            <span style="background:#ff525218; color:#ff5252; font-size:10px; padding:1px 6px; border-radius:10px;">Belum Absen</span>
                                        @endif
                                        <span style="color:#999; font-size:10px; margin-left:4px;">
                                            ({{ date('H:i', strtotime($d->lembur_mulai)) }} - {{ date('H:i', strtotime($d->lembur_selesai)) }})
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @endcan
            </div>
        </div>

        {{-- ===== IT TICKET SUMMARY ===== --}}
        @can('it-ticket.index')
        @php
            $myTickets = \App\Models\ItTicket::where('pemohon_id', auth()->id())
                ->whereIn('status', ['open','in_progress','pending'])
                ->orderByRaw("FIELD(prioritas,'critical','high','medium','low')")
                ->orderByDesc('created_at')
                ->take(3)
                ->get();
        @endphp
        @if($myTickets->count() > 0)
        <div class="px-4 mt-4 fade-in" style="animation-delay:.28s;">
            <div class="flex justify-between items-center mb-2">
                <h4 style="font-size:15px; font-weight:700; color:#333;">🎫 IT Ticket Aktif</h4>
                <a href="{{ route('it-ticket.index') }}" style="font-size:12px; color:{{ $t['primary'] ?? '#2d5a4c' }}; font-weight:600;">Lihat Semua →</a>
            </div>
            @foreach($myTickets as $tkt)
            @php
                $tktColors = ['critical'=>'#dc3545','high'=>'#fd7e14','medium'=>'#0d6efd','low'=>'#6c757d'];
                $tktColor = $tktColors[$tkt->prioritas] ?? '#6c757d';
                $statusLabels = ['open'=>'Open','in_progress'=>'In Progress','pending'=>'Pending'];
                $statusLabel = $statusLabels[$tkt->status] ?? $tkt->status;
            @endphp
            <a href="{{ route('it-ticket.show', $tkt->id) }}" class="block">
                <div class="bg-white rounded-[12px] mb-2 p-3 flex items-start gap-3" style="border:1px solid {{ $tktColor }}33; box-shadow:0 1px 4px rgba(0,0,0,0.04);">
                    <div class="shrink-0 flex items-center justify-center rounded-[10px] mt-1" style="width:36px;height:36px;background:{{ $tktColor }}18;">
                        <ion-icon name="headset-outline" style="font-size:20px;color:{{ $tktColor }};"></ion-icon>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start gap-1">
                            <span style="font-size:13px;font-weight:600;color:#333;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px;">{{ $tkt->judul }}</span>
                            <span style="font-size:10px;font-weight:700;color:{{ $tktColor }};background:{{ $tktColor }}18;padding:2px 7px;border-radius:20px;white-space:nowrap;">{{ ucfirst($tkt->prioritas) }}</span>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span style="font-size:10px;color:#888;">{{ $tkt->nomor_tiket }}</span>
                            <span style="font-size:10px;background:#f0f0f0;color:#555;padding:1px 6px;border-radius:10px;">{{ $statusLabel }}</span>
                            @if($tkt->isOverdue())
                                <span style="font-size:10px;background:#dc354518;color:#dc3545;padding:1px 6px;border-radius:10px;">⚠ Overdue</span>
                            @else
                                <span style="font-size:10px;color:#aaa;">SLA: {{ $tkt->tanggal_target?->format('d/m') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
            <a href="{{ route('it-ticket.create') }}" class="block mt-2">
                <div class="rounded-[12px] flex items-center justify-center gap-2 py-2" style="border:1.5px dashed {{ $t['primary'] ?? '#2d5a4c' }}55;background:{{ $t['primary'] ?? '#2d5a4c' }}08;">
                    <ion-icon name="add-circle-outline" style="font-size:18px;color:{{ $t['primary'] ?? '#2d5a4c' }};"></ion-icon>
                    <span style="font-size:13px;font-weight:600;color:{{ $t['primary'] ?? '#2d5a4c' }};">Buat Tiket Baru</span>
                </div>
            </a>
        </div>
        @endif
        @endcan

        </div>
    </div>


        {{-- ===== BIRTHDAY MODAL ===== --}}
        @if (isset($is_birthday) && $is_birthday)
            <div id="birthdayModal" class="fixed inset-0 z-[1000] flex items-center justify-center p-4" style="display:none;">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
                <div class="relative rounded-[30px] w-full max-w-[340px] overflow-hidden shadow-2xl animate-bounce-in" style="background:{{ $t['primary'] ?? '#2d5a4c' }};">
                    <div id="confetti-container" class="absolute inset-0 pointer-events-none"></div>
                    <div class="p-8 text-center relative z-10">
                        <button onclick="hideBirthday()" class="absolute top-4 right-4 text-white/50 hover:text-white">
                            <ion-icon name="close-circle-outline" style="font-size:28px;"></ion-icon>
                        </button>
                        <div class="mb-6 animate-bounce">
                            <span style="font-size:70px; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));">🎂</span>
                        </div>
                        <h2 class="text-2xl font-extrabold text-white mb-1">Selamat Ulang Tahun!</h2>
                        <h3 class="text-xl font-bold text-white/90 mb-4">{{ $karyawan->nama_karyawan }}</h3>
                        @if ($umur)
                            <p class="text-white/80 mb-6 leading-relaxed text-sm">Selamat ulang tahun yang ke-<strong class="text-white">{{ $umur }}</strong> tahun! Semoga sukses dan bahagia selalu. 🎊</p>
                        @endif
                        <div class="flex flex-col gap-2 mb-8 text-left max-w-[240px] mx-auto bg-white/10 p-4 rounded-2xl">
                            <div class="flex items-center gap-2 text-white">
                                <ion-icon name="sparkles" class="text-yellow-300"></ion-icon>
                                <span class="text-xs">Panjang umur & sehat selalu</span>
                            </div>
                            <div class="flex items-center gap-2 text-white">
                                <ion-icon name="sparkles" class="text-yellow-300"></ion-icon>
                                <span class="text-xs">Sukses dalam karir & rezeki</span>
                            </div>
                        </div>
                        <button onclick="hideBirthday()" class="w-full py-3 rounded-full bg-white text-{{ $t['primary'] ?? '#2d5a4c' }} font-bold shadow-lg transition-all active:scale-95">
                            Terima Kasih! 🙏
                        </button>
                    </div>
                </div>
            </div>
            <style>
                @keyframes bounce-in {
                    0% { opacity:0; transform:scale(0.8) translateY(20px); }
                    70% { transform:scale(1.05) translateY(-5px); }
                    100% { opacity:1; transform:scale(1) translateY(0); }
                }
                .animate-bounce-in { animation: bounce-in 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
                .confetti { position: absolute; width:10px; height:10px; border-radius:3px; animation: confetti-fall 3s linear forwards; }
                @keyframes confetti-fall {
                    0% { transform: translateY(-20px) rotate(0deg); opacity: 1; }
                    100% { transform: translateY(300px) rotate(720deg); opacity: 0; }
                }
            </style>
        @endif

        {{-- ===== BOTTOM NAV ===== --}}
        <div style="height: 100px;"></div>
        @include('layouts.mobile.bottomNav')

    {{-- ===== SCRIPTS ===== --}}
    <script>
        // Real-time clock
        function updateClock() {
            var d = new Date();
            var h = d.getHours();
            var m = d.getMinutes() < 10 ? '0' + d.getMinutes() : d.getMinutes();
            var s = d.getSeconds() < 10 ? '0' + d.getSeconds() : d.getSeconds();
            var el = document.getElementById('jam');
            if (el) el.textContent = h + ':' + m + ':' + s;
            setTimeout(updateClock, 1000);
        }
        updateClock();

        // Alert Carousel - Slide
        $(document).ready(function() {
            var track = $('.carousel-track');
            var slides = $('.alert-slide');
            var dots = $('.dot');
            if (slides.length > 1) {
                var current = 0;
                setInterval(function() {
                    current = (current + 1) % slides.length;
                    track.css('transform', 'translateX(-' + (current * 100) + '%)');
                    dots.removeClass('active');
                    $(dots[current]).addClass('active');
                }, 5000);
            }

            // Birthday logic
            @if (isset($is_birthday) && $is_birthday)
                setTimeout(function(){
                    $('#birthdayModal').fadeIn(400);
                    createConfetti();
                }, 1500);
            @endif
        });

        // Birthday Confetti
        function createConfetti() {
            var container = document.getElementById('confetti-container');
            if (!container) return;
            var colors = ['#ffd700', '#ff6b6b', '#4ecdc4', '#95e1d3', '#ffe66d'];
            for (var i = 0; i < 50; i++) {
                var c = document.createElement('div');
                c.className = 'confetti';
                c.style.left = Math.random() * 100 + '%';
                c.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                c.style.animationDelay = (Math.random() * 2) + 's';
                container.appendChild(c);
            }
        }

        function hideBirthday() {
            $('#birthdayModal').fadeOut(300);
        }

        // History Tab Switching
        function switchTab(tab) {
            var primary = '{{ $t["primary"] ?? "#2d5a4c" }}';
            if (tab === 'presensi') {
                document.getElementById('contentPresensi').style.display = '';
                if (document.getElementById('contentLembur')) {
                    document.getElementById('contentLembur').style.display = 'none';
                }
                document.getElementById('tabPresensi').style.background = primary;
                document.getElementById('tabPresensi').style.color = 'white';
                if (document.getElementById('tabLembur')) {
                    document.getElementById('tabLembur').style.background = 'transparent';
                    document.getElementById('tabLembur').style.color = '#888';
                }
            } else {
                document.getElementById('contentPresensi').style.display = 'none';
                if (document.getElementById('contentLembur')) {
                    document.getElementById('contentLembur').style.display = '';
                }
                if (document.getElementById('tabLembur')) {
                    document.getElementById('tabLembur').style.background = primary;
                    document.getElementById('tabLembur').style.color = 'white';
                }
                document.getElementById('tabPresensi').style.background = 'transparent';
                document.getElementById('tabPresensi').style.color = '#888';
            }
        }

        // Face Recognition Registration Handler
        $("#btnDaftarkanWajah").click(function(e) {
            e.preventDefault();
            window.location.href = "{{ route('facerecognition.karyawan.create') }}";
        });

        // Notification Modal Handler
        document.getElementById('notificationBtn').addEventListener('click', function(e) {
            e.preventDefault();
            loadNotifications();
            document.getElementById('notificationModal').style.display = 'flex';
        });

        function closeNotificationModal() {
            document.getElementById('notificationModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('notificationModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNotificationModal();
            }
        });

        function loadNotifications() {
            fetch("{{ route('notification.get') }}")
                .then(response => response.json())
                .then(data => {
                    const listDiv = document.getElementById('notificationList');
                    
                    if (!data.notifications || data.notifications.length === 0) {
                        listDiv.innerHTML = '<div style="padding: 40px 20px; text-align: center; color: #999;"><p>Tidak ada notifikasi</p></div>';
                        return;
                    }

                    let html = '';
                    data.notifications.forEach(notif => {
                        let badge = '';
                        let icon = '';
                        
                        if (notif.status === 1) {
                            badge = '<span style="display: inline-block; background: #28a745; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; margin-right: 8px;">✅ Disetujui</span>';
                        } else if (notif.status === 2) {
                            badge = '<span style="display: inline-block; background: #dc3545; color: white; padding: 3px 8px; border-radius: 3px; font-size: 11px; margin-right: 8px;">❌ Ditolak</span>';
                        } else {
                            badge = '<span style="display: inline-block; background: #ffc107; color: #333; padding: 3px 8px; border-radius: 3px; font-size: 11px; margin-right: 8px;">⏳ Pending</span>';
                        }

                        if (notif.approval_type === 'IZIN_SAKIT') {
                            icon = '🏥';
                        } else if (notif.approval_type === 'IZIN_ABSEN') {
                            icon = '📅';
                        } else if (notif.approval_type === 'IZIN_CUTI') {
                            icon = '🎯';
                        } else if (notif.approval_type === 'IZIN_DINAS') {
                            icon = '💼';
                        }

                        const bgColor = !notif.is_read ? '#f8f9fa' : '#ffffff';
                        const borderLeft = !notif.is_read ? '4px solid #007bff' : '4px solid transparent';

                        html += `
                            <div style="padding: 15px 20px; border-bottom: 1px solid #eee; background: ${bgColor}; border-left: ${borderLeft};">
                                <div style="display: flex; gap: 10px;">
                                    <div style="font-size: 24px;">${icon}</div>
                                    <div style="flex: 1;">
                                        <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px;">
                                            ${badge}
                                            ${!notif.is_read ? '<span style="display: inline-block; width: 8px; height: 8px; background: #007bff; border-radius: 50%;"></span>' : ''}
                                        </div>
                                        <div style="font-weight: 600; font-size: 13px; margin-bottom: 4px;">${notif.title}</div>
                                        <div style="font-size: 12px; color: #666; margin-bottom: 6px;">${notif.message}</div>
                                        <div style="font-size: 11px; color: #999;">
                                            👤 ${notif.approver_name} • ⏰ ${notif.created_at}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    listDiv.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    document.getElementById('notificationList').innerHTML = '<div style="padding: 20px; text-align: center; color: #999;">Error loading notifications</div>';
                });
        }
    </script>
</body>
</html>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status Lamaran</title>
</head>
<body style="margin:0;padding:0;background:#f4f5fb;font-family:'Segoe UI',Arial,sans-serif;">

@php
$statusConfig = [
    'pending'   => ['color'=>'#8592a3', 'bg'=>'#f0f1f3', 'label'=>'Pending',           'icon'=>'⏳', 'desc'=>'Lamaran Anda sedang menunggu untuk ditinjau oleh tim HRD kami.'],
    'review'    => ['color'=>'#03c3ec', 'bg'=>'#e8fafe', 'label'=>'Sedang Ditinjau',    'icon'=>'🔍', 'desc'=>'Tim HRD kami sedang meninjau profil dan dokumen lamaran Anda.'],
    'interview' => ['color'=>'#ffab00', 'bg'=>'#fff8e6', 'label'=>'Undangan Interview', 'icon'=>'🎤', 'desc'=>'Selamat! Anda lolos seleksi administrasi dan diundang untuk mengikuti sesi interview.'],
    'offering'  => ['color'=>'#696cff', 'bg'=>'#f0f0ff', 'label'=>'Penawaran Kerja',   'icon'=>'📋', 'desc'=>'Selamat! Anda telah melalui proses seleksi dengan baik. Tim HRD akan segera menyampaikan penawaran kerja kepada Anda.'],
    'diterima'  => ['color'=>'#28c76f', 'bg'=>'#e8faf1', 'label'=>'Diterima',          'icon'=>'🎉', 'desc'=>'Selamat! Anda resmi diterima sebagai bagian dari tim kami. Tim HRD akan menghubungi Anda untuk informasi onboarding selanjutnya.'],
    'ditolak'   => ['color'=>'#ea5455', 'bg'=>'#fdf0f0', 'label'=>'Tidak Dilanjutkan', 'icon'=>'📩', 'desc'=>'Terima kasih atas minat Anda. Setelah melalui proses seleksi, kami belum dapat melanjutkan lamaran Anda saat ini. Kami mendoakan yang terbaik untuk karir Anda.'],
];
$sc = $statusConfig[$r->status] ?? $statusConfig['pending'];
@endphp

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5fb;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

                    {{-- HEADER --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#696cff 0%,#5a5fd6 100%);border-radius:12px 12px 0 0;padding:32px 40px;text-align:center;">
                            <p style="margin:0 0 6px;color:rgba(255,255,255,.8);font-size:13px;">{{ config('app.name') }} — Sistem Rekrutmen</p>
                            <h1 style="margin:0;color:#fff;font-size:22px;font-weight:700;">Update Status Lamaran Anda</h1>
                        </td>
                    </tr>

                    {{-- STATUS BANNER --}}
                    <tr>
                        <td style="background:{{ $sc['bg'] }};border-left:4px solid {{ $sc['color'] }};padding:20px 40px;text-align:center;">
                            <div style="font-size:36px;margin-bottom:6px;">{{ $sc['icon'] }}</div>
                            <div style="font-size:18px;font-weight:700;color:{{ $sc['color'] }};">{{ $sc['label'] }}</div>
                        </td>
                    </tr>

                    {{-- BODY --}}
                    <tr>
                        <td style="background:#ffffff;padding:32px 40px;">

                            <p style="margin:0 0 16px;font-size:15px;color:#444;">
                                Halo <strong>{{ $r->nama_lengkap }}</strong>,
                            </p>
                            <p style="margin:0 0 24px;font-size:14px;color:#666;line-height:1.8;">
                                {{ $sc['desc'] }}
                            </p>

                            {{-- INFO BOX --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9ff;border:1px solid #e0e2ff;border-radius:10px;margin-bottom:24px;">
                                <tr>
                                    <td style="padding:18px 24px;">
                                        <p style="margin:0 0 12px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#696cff;">Detail Lamaran</p>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:5px 0;width:130px;font-size:13px;color:#888;">Kode Lamaran</td>
                                                <td style="padding:5px 0;font-size:13px;">
                                                    <strong style="background:#696cff;color:#fff;padding:2px 10px;border-radius:20px;font-size:11px;">{{ $r->kode_recruitment }}</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:5px 0;font-size:13px;color:#888;border-top:1px solid #eee;">Posisi Dilamar</td>
                                                <td style="padding:5px 0;font-size:13px;color:#333;font-weight:600;border-top:1px solid #eee;">{{ $r->posisi_dilamar }}</td>
                                            </tr>
                                            @if($r->cabang)
                                            <tr>
                                                <td style="padding:5px 0;font-size:13px;color:#888;border-top:1px solid #eee;">Cabang</td>
                                                <td style="padding:5px 0;font-size:13px;color:#333;border-top:1px solid #eee;">{{ $r->cabang->nama_cabang }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td style="padding:5px 0;font-size:13px;color:#888;border-top:1px solid #eee;">Status Terbaru</td>
                                                <td style="padding:5px 0;border-top:1px solid #eee;">
                                                    <span style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;border:1px solid {{ $sc['color'] }};">
                                                        {{ $sc['icon'] }} {{ $sc['label'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @if($r->status === 'interview' && $r->tanggal_interview)
                                            <tr>
                                                <td style="padding:5px 0;font-size:13px;color:#888;border-top:1px solid #eee;">Tanggal Interview</td>
                                                <td style="padding:5px 0;font-size:13px;color:#333;font-weight:700;border-top:1px solid #eee;">
                                                    📅 {{ $r->tanggal_interview->format('d F Y') }}
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- CATATAN INTERVIEW jika ada --}}
                            @if(in_array($r->status, ['interview','offering','diterima']) && $r->catatan_interview)
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td style="background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:14px 18px;">
                                        <p style="margin:0 0 6px;font-size:12px;font-weight:700;color:#f57f17;text-transform:uppercase;letter-spacing:.5px;">Catatan</p>
                                        <p style="margin:0;font-size:13px;color:#795548;line-height:1.7;">{{ $r->catatan_interview }}</p>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            {{-- KONTAK INFO --}}
                            <p style="margin:0 0 8px;font-size:13px;color:#666;line-height:1.7;">
                                Jika ada pertanyaan lebih lanjut, silakan hubungi tim HRD kami:
                            </p>
                            <p style="margin:0 0 24px;font-size:13px;">
                                📧 <a href="mailto:{{ config('mail.from.address') }}" style="color:#696cff;text-decoration:none;">{{ config('mail.from.address') }}</a>
                            </p>

                            <p style="margin:0;font-size:13px;color:#888;">Salam,<br><strong style="color:#333;">Tim HRD {{ config('app.name') }}</strong></p>
                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td style="background:#f8f9ff;border-top:1px solid #e8e9ff;border-radius:0 0 12px 12px;padding:16px 40px;text-align:center;">
                            <p style="margin:0 0 4px;font-size:13px;color:#696cff;font-weight:700;">{{ config('app.name') }}</p>
                            <p style="margin:0;font-size:11px;color:#aaa;">Email ini dikirim otomatis, mohon tidak membalas email ini.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>

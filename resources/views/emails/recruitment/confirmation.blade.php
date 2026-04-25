<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Lamaran Kerja</title>
</head>
<body style="margin:0;padding:0;background:#f4f5fb;font-family:'Segoe UI',Arial,sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5fb;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

                    {{-- HEADER --}}
                    <tr>
                        <td style="background:linear-gradient(135deg,#696cff 0%,#5a5fd6 100%);border-radius:12px 12px 0 0;padding:36px 40px;text-align:center;">
                            <h1 style="margin:0;color:#fff;font-size:24px;font-weight:700;letter-spacing:.5px;">
                                🎉 Lamaran Diterima!
                            </h1>
                            <p style="margin:8px 0 0;color:rgba(255,255,255,.85);font-size:14px;">
                                HRIS PT DIDIMAX BERJANGKA — Sistem Rekrutmen
                            </p>
                        </td>
                    </tr>

                    {{-- BODY --}}
                    <tr>
                        <td style="background:#ffffff;padding:36px 40px;">

                            <p style="margin:0 0 16px;font-size:15px;color:#444;">
                                Halo <strong>{{ $r->nama_lengkap }}</strong>,
                            </p>
                            <p style="margin:0 0 24px;font-size:14px;color:#666;line-height:1.7;">
                                Terima kasih telah mengirimkan lamaran kerja kepada kami. Kami telah menerima data Anda dan sedang dalam proses peninjauan oleh tim HRD.
                            </p>

                            {{-- INFO BOX --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9ff;border:1px solid #e0e2ff;border-radius:10px;margin-bottom:28px;">
                                <tr>
                                    <td style="padding:20px 24px;">
                                        <p style="margin:0 0 14px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#696cff;">
                                            Ringkasan Lamaran
                                        </p>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:6px 0;width:140px;font-size:13px;color:#888;">Kode Lamaran</td>
                                                <td style="padding:6px 0;font-size:13px;color:#333;">
                                                    <strong style="background:#696cff;color:#fff;padding:2px 10px;border-radius:20px;font-size:12px;">
                                                        {{ $r->kode_recruitment }}
                                                    </strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#888;border-top:1px solid #eee;">Posisi Dilamar</td>
                                                <td style="padding:6px 0;font-size:13px;color:#333;font-weight:600;border-top:1px solid #eee;">{{ $r->posisi_dilamar }}</td>
                                            </tr>
                                            @if($r->cabang)
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#888;border-top:1px solid #eee;">Cabang</td>
                                                <td style="padding:6px 0;font-size:13px;color:#333;border-top:1px solid #eee;">{{ $r->cabang->nama_cabang }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#888;border-top:1px solid #eee;">Tanggal Melamar</td>
                                                <td style="padding:6px 0;font-size:13px;color:#333;border-top:1px solid #eee;">{{ $r->tanggal_melamar?->format('d F Y') }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 0;font-size:13px;color:#888;border-top:1px solid #eee;">Status</td>
                                                <td style="padding:6px 0;border-top:1px solid #eee;">
                                                    <span style="background:#fff3e0;color:#e65100;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                                                        ⏳ Sedang Diproses
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- NEXT STEPS --}}
                            <p style="margin:0 0 12px;font-size:14px;font-weight:700;color:#333;">Langkah Selanjutnya</p>
                            <p style="margin:0 0 16px;font-size:14px;color:#666;line-height:1.7;">
                                Tim HRD kami akan meninjau lamaran Anda. Jika profil Anda sesuai, kami akan menghubungi Anda melalui:
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td style="padding:8px 16px;background:#f0fff4;border-left:3px solid #28c76f;border-radius:0 6px 6px 0;margin-bottom:8px;">
                                        <span style="font-size:13px;color:#333;">📱 <strong>WhatsApp / Telepon:</strong> {{ $r->no_hp }}</span>
                                    </td>
                                </tr>
                                @if($r->email)
                                <tr><td style="height:8px;"></td></tr>
                                <tr>
                                    <td style="padding:8px 16px;background:#f0fff4;border-left:3px solid #28c76f;border-radius:0 6px 6px 0;">
                                        <span style="font-size:13px;color:#333;">📧 <strong>Email:</strong> {{ $r->email }}</span>
                                    </td>
                                </tr>
                                @endif
                            </table>

                            {{-- WARNING --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                                <tr>
                                    <td style="background:#fff8e1;border:1px solid #ffe082;border-radius:8px;padding:14px 18px;">
                                        <p style="margin:0;font-size:13px;color:#795548;line-height:1.6;">
                                            ⚠️ <strong>Pastikan nomor HP dan email Anda selalu aktif</strong> agar kami dapat menghubungi Anda untuk proses seleksi selanjutnya.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:14px;color:#666;line-height:1.7;">
                                Jika ada pertanyaan, silakan hubungi kami di recruitment@didimax.co.id
                            </p>

                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td style="background:#f8f9ff;border-top:1px solid #e8e9ff;border-radius:0 0 12px 12px;padding:20px 40px;text-align:center;">
                            <p style="margin:0 0 4px;font-size:13px;color:#696cff;font-weight:700;">
                                PT DIDIMAX BERJANGKA
                            </p>
                            <p style="margin:0;font-size:12px;color:#aaa;">
                                Email ini dikirim otomatis, mohon tidak membalas email ini.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>

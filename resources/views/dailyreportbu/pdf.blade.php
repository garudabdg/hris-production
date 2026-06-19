<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daily Report BU - {{ $report->karyawan->nama_karyawan }} - {{ \Carbon\Carbon::parse($report->tanggal)->format('d_m_Y') }}</title>
    <style>
        @page { margin: 30px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2d5a4c; padding-bottom: 10px; }
        .header h2 { margin: 0; color: #2d5a4c; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 12px; }
        
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 3px; vertical-align: top; }
        .info-label { width: 100px; font-weight: bold; }
        
        .section-title { background: #2d5a4c; color: #fff; padding: 5px 10px; font-weight: bold; font-size: 12px; margin-top: 20px; margin-bottom: 10px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 6px; }
        table.data-table th { background: #f5f5f5; text-align: center; font-weight: bold; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .text-capitalize { text-transform: capitalize; }
        
        .footer { position: fixed; bottom: -10px; left: 0; right: 0; height: 30px; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>DAILY REPORT - BUSINESS (BU)</h2>
        <p>PT DIDIMAX BERJANGKA</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Nama Karyawan</td>
            <td>: {{ $report->karyawan->nama_karyawan }}</td>
            <td class="info-label">Tanggal</td>
            <td>: {{ \Carbon\Carbon::parse($report->tanggal)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">NIK</td>
            <td>: {{ $report->nik }}</td>
            <td class="info-label">Team</td>
            <td>: {{ $report->sub_departemen ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-title">SECTION 1: AKTIVITAS ONLINE</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Platform</th>
                <th>Posting</th>
                <th>Share Group</th>
                <th>Add Group</th>
                <th>Add Friend</th>
                <th>Inbox</th>
                <th>Story</th>
                <th>Broadcast</th>
                <th>Fanspage</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totals = ['posting'=>0, 'share_group'=>0, 'add_group'=>0, 'add_friend'=>0, 'inbox'=>0, 'story'=>0, 'broadcast'=>0, 'fanspage'=>0, 'subtotal'=>0];
            @endphp
            @foreach ($platforms as $platform)
                @php
                    $act = $report->onlineActivities->where('platform', $platform)->first();
                    if($act) {
                        $totals['posting'] += $act->posting;
                        $totals['share_group'] += $act->share_group;
                        $totals['add_group'] += $act->add_group;
                        $totals['add_friend'] += $act->add_friend;
                        $totals['inbox'] += $act->inbox;
                        $totals['story'] += $act->story;
                        $totals['broadcast'] += $act->broadcast;
                        $totals['fanspage'] += $act->fanspage;
                        $totals['subtotal'] += $act->subtotal;
                    }
                @endphp
                <tr>
                    <td class="text-capitalize fw-bold">{{ $platform }}</td>
                    <td class="text-center">{{ $act->posting ?? 0 }}</td>
                    <td class="text-center">{{ $act->share_group ?? 0 }}</td>
                    <td class="text-center">{{ $act->add_group ?? 0 }}</td>
                    <td class="text-center">{{ $act->add_friend ?? 0 }}</td>
                    <td class="text-center">{{ $act->inbox ?? 0 }}</td>
                    <td class="text-center">{{ $act->story ?? 0 }}</td>
                    <td class="text-center">{{ $act->broadcast ?? 0 }}</td>
                    <td class="text-center">{{ $act->fanspage ?? 0 }}</td>
                    <td class="text-center fw-bold">{{ $act->subtotal ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f9f9f9;">
                <td class="text-right fw-bold">TOTAL KESELURUHAN</td>
                <td class="text-center fw-bold">{{ $totals['posting'] }}</td>
                <td class="text-center fw-bold">{{ $totals['share_group'] }}</td>
                <td class="text-center fw-bold">{{ $totals['add_group'] }}</td>
                <td class="text-center fw-bold">{{ $totals['add_friend'] }}</td>
                <td class="text-center fw-bold">{{ $totals['inbox'] }}</td>
                <td class="text-center fw-bold">{{ $totals['story'] }}</td>
                <td class="text-center fw-bold">{{ $totals['broadcast'] }}</td>
                <td class="text-center fw-bold">{{ $totals['fanspage'] }}</td>
                <td class="text-center fw-bold">{{ $totals['subtotal'] }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="section-title">SECTION 2: AKTIVITAS OFFLINE (APPOINTMENT / CTO / CANVASING)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 100px;">Tipe Kegiatan</th>
                <th>Nama Prospek</th>
                <th style="width: 100px;">No WhatsApp</th>
                <th>Alamat / Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report->offlineActivities as $index => $offline)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center text-capitalize">{{ $offline->tipe }}</td>
                    <td>{{ $offline->nama_prospek }}</td>
                    <td>{{ $offline->whatsapp }}</td>
                    <td>{{ $offline->alamat }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #999;">Tidak ada data aktivitas offline</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">SECTION 3: PENGOLAHAN DATA CALON NASABAH</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Prospek</th>
                <th>Akun Sosial Media</th>
                <th style="width: 100px;">No WhatsApp</th>
                <th style="width: 80px;">Status Lead</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report->nasabahData as $index => $nasabah)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $nasabah->nama }}</td>
                    <td>{{ $nasabah->akun_sosial_media }}</td>
                    <td>{{ $nasabah->no_whatsapp }}</td>
                    <td class="text-center text-capitalize">{{ $nasabah->status_lead }}</td>
                    <td>{{ $nasabah->keterangan }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="color: #999;">Tidak ada pengolahan data nasabah</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($report->catatan)
    <div style="margin-top: 20px;">
        <span style="font-weight: bold;">Catatan:</span><br>
        <div style="border: 1px dashed #ccc; padding: 10px; margin-top: 5px; background-color: #fcfcfc;">
            {{ $report->catatan }}
        </div>
    </div>
    @endif

    <div class="footer">
        Dicetak pada: {{ date('d-m-Y H:i:s') }} | Sistem HRIS DIDIMAX
    </div>
</body>
</html>

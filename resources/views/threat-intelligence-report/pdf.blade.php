<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Threat Intelligence Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: middle;
        }
        .header-logo {
            width: 20%;
            text-align: center;
        }
        .header-title {
            width: 50%;
            text-align: center;
            font-size: 16px;
            font-weight: bold;
        }
        .header-info {
            width: 30%;
            font-size: 10px;
        }
        .header-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-info td {
            border: none;
            padding: 2px 4px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 5px;
            word-wrap: break-word;
        }
        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        
        .text-center { text-align: center; }
        .text-capitalize { text-transform: capitalize; }
        
        .status-open { color: #dc3545; font-weight: bold; }
        .status-investigating { color: #fd7e14; font-weight: bold; }
        .status-mitigated { color: #28a745; font-weight: bold; }
        .status-closed { color: #6c757d; font-weight: bold; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="header-logo">
                <!-- If you have a logo, you can use: <img src="{{ public_path('assets/images/logo.png') }}" width="100"> -->
                <strong>DIDIMAX</strong>
            </td>
            <td class="header-title">
                THREAT INTELLIGENCE REPORT
            </td>
            <td class="header-info" style="padding: 0;">
                <table>
                    <tr>
                        <td width="40%">No. Dok</td>
                        <td width="5%">:</td>
                        <td>DMB-FRM-IT-2412</td>
                    </tr>
                    <tr>
                        <td>Version</td>
                        <td>:</td>
                        <td>1.0</td>
                    </tr>
                    <tr>
                        <td>Tgl Efektif</td>
                        <td>:</td>
                        <td>01 Maret 2023</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="8%">Tanggal</th>
                <th width="12%">Jenis Ancaman</th>
                <th width="12%">Sumber Ancaman</th>
                <th width="20%">Deskripsi</th>
                <th width="15%">Dampak</th>
                <th width="20%">Tindakan</th>
                <th width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $row->jenis_ancaman }}</td>
                <td>{{ $row->sumber_ancaman ?? '-' }}</td>
                <td>{{ $row->deskripsi_insiden }}</td>
                <td>{{ $row->dampak ?? '-' }}</td>
                <td>{{ $row->tindakan_yang_diambil ?? '-' }}</td>
                <td class="text-center">
                    @php
                        $statusClass = 'status-closed';
                        $statusLower = strtolower($row->status);
                        if($statusLower == 'open' || $statusLower == 'ada masalah') $statusClass = 'status-open';
                        elseif($statusLower == 'investigating' || $statusLower == 'proses') $statusClass = 'status-investigating';
                        elseif($statusLower == 'resolved' || $statusLower == 'tidak ada masalah') $statusClass = 'status-mitigated';
                    @endphp
                    <span class="{{ $statusClass }}">{{ $row->status }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada data report</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>

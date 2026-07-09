<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Riwayat Perawatan Aset</title>
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
        .text-success { color: green; }
        .text-warning { color: orange; }
        .text-danger { color: red; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="header-logo">
                <span style="color: #0d6efd; font-size: 16px; font-weight: bold;">
                    <span style="font-size: 20px;">@</span> DIDIMAX
                </span>
            </td>
            <td class="header-title">
                LAPORAN RIWAYAT<br>PERAWATAN ASET
            </td>
            <td class="header-info" style="padding: 0;">
                <table>
                    <tr>
                        <td width="40%">Filter Aset</td>
                        <td width="5%">:</td>
                        <td>{{ $request->kode_asset ?: 'Semua Aset' }}</td>
                    </tr>
                    <tr>
                        <td>Periode</td>
                        <td>:</td>
                        <td>
                            {{ $request->dari ? \Carbon\Carbon::parse($request->dari)->format('d/m/Y') : '-' }} 
                            s/d 
                            {{ $request->sampai ? \Carbon\Carbon::parse($request->sampai)->format('d/m/Y') : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td>Tgl Cetak</td>
                        <td>:</td>
                        <td>{{ date('d-m-Y H:i') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="10%">Kode Perawatan</th>
                <th width="15%">Aset</th>
                <th width="10%">Kategori</th>
                <th width="10%">Tanggal</th>
                <th width="12%">Petugas</th>
                <th width="8%">Total Item</th>
                <th width="12%">Hasil Akhir</th>
                <th width="10%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($perawatans as $index => $row)
            @php
                $totalItems = $row->items->count();
                $rusak = $row->items->where('klasifikasi', 'rusak')->count();
                $cukupBaik = $row->items->where('klasifikasi', 'cukup_baik')->count();
                
                $hasil = 'Baik';
                $class = 'text-success';
                
                if ($rusak > 0) {
                    $hasil = 'Rusak (' . $rusak . ' item)';
                    $class = 'text-danger';
                } elseif ($cukupBaik > 0) {
                    $hasil = 'Cukup Baik (' . $cukupBaik . ' item)';
                    $class = 'text-warning';
                }
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $row->kode_perawatan }}</td>
                <td>
                    <strong>{{ $row->asset?->nama_asset ?? '-' }}</strong><br>
                    <span style="font-size: 9px; color: #555;">{{ $row->kode_asset }}</span>
                </td>
                <td class="text-center">{{ $row->asset?->category?->nama_kategori ?? '-' }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($row->tanggal_perawatan)->format('d M Y') }}</td>
                <td class="text-center">{{ $row->petugas ?? '-' }}</td>
                <td class="text-center">{{ $totalItems }}</td>
                <td class="text-center"><strong class="{{ $class }}">{{ $hasil }}</strong></td>
                <td>{{ $row->catatan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Tidak ada data perawatan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>

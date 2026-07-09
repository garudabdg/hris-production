<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Detail Checklist Perawatan Aset</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 5px;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        
        .header-logo {
            color: #0056b3;
            font-size: 18px;
            font-weight: bold;
            font-style: italic;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            border: none;
            padding: 1px 4px;
        }
        .box {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #000;
            margin-right: 5px;
            vertical-align: middle;
            text-align: center;
            line-height: 10px;
            font-size: 10px;
            font-family: 'DejaVu Sans', sans-serif;
        }
    </style>
</head>
<body>

    <table class="table">
        <!-- HEADER -->
        <tr>
            <td width="20%" class="text-center">
                <span style="color: #0d6efd; font-size: 16px; font-weight: bold;">
                    <span style="font-size: 20px;">@</span> DIDIMAX
                </span>
            </td>
            <td width="55%" class="text-center fw-bold" style="font-size: 14px;">
                DETAIL CHEKLIST PERAWATAN {{ strtoupper($asset->category ? $asset->category->nama_kategori : 'ASET') }}
            </td>
            <td width="25%" style="padding: 0;">
                <table class="info-table" style="font-size: 10px;">
                    <tr>
                        <td width="40%">No. Form</td>
                        <td width="5%">:</td>
                        <td>DMB-FRM-IT-2414C</td>
                    </tr>
                    <tr>
                        <td>Version</td>
                        <td>:</td>
                        <td>1.0</td>
                    </tr>
                    <tr>
                        <td>Tgl Efektif</td>
                        <td>:</td>
                        <td>01 Maret 2024</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- INFO ROW -->
    <table class="info-table" style="margin-top: 10px; margin-bottom: 10px;">
        <tr>
            <td width="15%" class="fw-bold">Kode Perawatan</td>
            <td width="2%">:</td>
            <td width="33%">{{ $assetPerawatan->kode_perawatan }}</td>
            
            <td width="15%" class="fw-bold">Tanggal Perawatan</td>
            <td width="2%">:</td>
            <td width="33%">{{ \Carbon\Carbon::parse($assetPerawatan->tanggal_perawatan)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Kode Aset</td>
            <td>:</td>
            <td>{{ $asset->kode_asset }}</td>
            
            <td class="fw-bold">Petugas</td>
            <td>:</td>
            <td>{{ $assetPerawatan->petugas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Nama Aset</td>
            <td>:</td>
            <td>{{ $asset->nama_asset }}</td>
            
            <td class="fw-bold">Catatan Umum</td>
            <td>:</td>
            <td>{{ $assetPerawatan->catatan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Lokasi / Cabang</td>
            <td>:</td>
            <td colspan="4">{{ $asset->cabang ? $asset->cabang->nama_cabang : 'Pusat' }}</td>
        </tr>
    </table>

    <!-- MAIN TABLE -->
    <table class="table text-center">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th width="5%" rowspan="2">No</th>
                <th width="40%" rowspan="2">Item Pemeriksaan</th>
                <th width="30%" colspan="3">Klasifikasi</th>
                <th width="25%" rowspan="2">Keterangan</th>
            </tr>
            <tr style="background-color: #f2f2f2;">
                <th width="10%">Baik</th>
                <th width="10%">Cukup Baik</th>
                <th width="10%">Rusak</th>
            </tr>
        </thead>
        <tbody>
            @foreach($assetPerawatan->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ $item->item_name }}</td>
                <td><span class="box">{{ $item->klasifikasi === 'baik' ? '✓' : '' }}</span></td>
                <td><span class="box">{{ $item->klasifikasi === 'cukup_baik' ? '✓' : '' }}</span></td>
                <td><span class="box">{{ $item->klasifikasi === 'rusak' ? '✓' : '' }}</span></td>
                <td class="text-left">{{ $item->keterangan ?? '' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- FOOTER -->
    <table style="width: 100%; margin-top: 40px; text-align: center; border: none;">
        <tr>
            <td style="width: 50%; border: none;">
                Mengetahui / Menyetujui,<br><br><br><br><br>
                ( Yayan Supriatna )<br>
                Supervisor / Manager
            </td>
            <td style="width: 50%; border: none;">
                Petugas Perawatan,<br><br><br><br><br>
                ( M Niftah )<br>
                Teknisi / Staff IT
            </td>
        </tr>
    </table>

</body>
</html>

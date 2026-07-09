<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ID Control List</title>
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
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td class="header-logo">
                <strong>DIDIMAX</strong>
            </td>
            <td class="header-title">
                ID CONTROL LIST
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
                <th width="5%">No</th>
                <th width="10%">Periode</th>
                <th width="15%">Nama Aplikasi</th>
                <th width="15%">Role / ID Name</th>
                <th width="15%">Nama Pengguna / ID User</th>
                <th width="12%">Divisi</th>
                <th width="12%">Lokasi</th>
                <th width="8%">Type ID</th>
                <th width="8%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lists as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $row->period }}</td>
                <td>{{ $row->nama_aplikasi }}</td>
                <td>{{ $row->role }}</td>
                <td>{{ $row->karyawan ? $row->karyawan->nama_karyawan : $row->nama_pengguna }}</td>
                <td>{{ $row->division }}</td>
                <td>{{ $row->cabang ? $row->cabang->nama_cabang : $row->location }}</td>
                <td>{{ $row->type_id }}</td>
                <td>
                    @if($row->remarks == '1')
                        Active
                    @elseif($row->remarks == '0')
                        non-Active
                    @else
                        {{ $row->remarks }}
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Tidak ada data report</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>

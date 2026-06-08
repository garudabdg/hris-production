<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Tamu {{ $tanggal }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .table-data th, .table-data td {
            border: 1px solid #333;
            padding: 5px;
            text-align: center;
        }
        .table-data th {
            background-color: #024a75;
            color: white;
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
            border: none;
        }
        .header td {
            border: none;
        }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td style="width: 80px; padding-right: 15px;">
                    <img src="{{ public_path('logo.png') }}" alt="Logo Aplikasi" style="max-width: 80px;">
                </td>
                <td>
                    <h3 style="margin: 0; padding: 0;">LAPORAN DATA TAMU</h3>
                    <h4 style="margin: 5px 0; padding: 0;">{{ $cabang ? $cabang->nama_cabang : $generalsetting->nama_perusahaan }}</h4>
                    <p style="margin: 0; padding: 0; font-style: italic;">
                        {{ $cabang ? $cabang->alamat_cabang : $generalsetting->alamat }}<br>
                        Telp: {{ $cabang ? $cabang->telepon_cabang : $generalsetting->telepon }}
                    </p>
                </td>
                <td style="text-align: right; vertical-align: bottom;">
                    <strong>Tanggal:</strong> {{ date('d-m-Y', strtotime($tanggal)) }}
                </td>
            </tr>
        </table>
        <hr style="border: 1px solid #333;">
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Tamu</th>
                <th>No Telpon</th>
                <th>Plat Nomor</th>
                <th>Bertemu Dengan</th>
                <th>Keperluan</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tamus as $t)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td style="text-align: left;">{{ $t->nama_tamu }}</td>
                    <td>{{ $t->no_telp }}</td>
                    <td>{{ $t->plat_nomor }}</td>
                    <td style="text-align: left;">{{ $t->tujuan }}</td>
                    <td style="text-align: left;">{{ $t->keperluan }}</td>
                    <td>{{ \Carbon\Carbon::parse($t->created_at)->format('H:i') }}</td>
                    <td>{{ $t->jam_out ? $t->jam_out : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Tidak ada data tamu pada tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>

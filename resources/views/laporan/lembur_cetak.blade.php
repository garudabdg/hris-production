<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Lembur - {{ date('Ymd_His') }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .header {
            width: 100%;
            margin-bottom: 20px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .header h4 {
            line-height: 1.4;
            margin: 0 0 5px 0;
            font-size: 14px;
            text-transform: uppercase;
        }

        .header span {
            font-style: italic;
            font-size: 10px;
        }

        .tabel-meta {
            margin-top: 15px;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .tabel-meta td {
            padding: 3px 5px;
            font-size: 11px;
            vertical-align: top;
        }

        .tabel-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 10px;
        }

        .tabel-data th {
            border: 1px solid #131212;
            padding: 8px 6px;
            background-color: #024a75;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        .tabel-data td {
            border: 1px solid #131212;
            padding: 6px;
            vertical-align: middle;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 3px 6px;
            font-weight: bold;
            border-radius: 3px;
            font-size: 9px;
        }

        .badge-pending {
            background-color: #ffeeb3;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .badge-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .badge-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Ensure header repeats on multi-page print */
        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-footer-group;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <table>
            <tr>
                <td style="width: 80px; padding-right: 15px; text-align: left; vertical-align: top;">
                    @if ($generalsetting->logo && Storage::exists('public/logo/' . $generalsetting->logo))
                        <img src="{{ asset('storage/logo/' . $generalsetting->logo) }}" alt="Logo Perusahaan" style="max-width: 100px;">
                    @else
                        <img src="https://placehold.co/100x100?text=Logo" alt="Logo Default" style="max-width: 100px;">
                    @endif
                </td>
                <td style="vertical-align: top;">
                    <h4 style="margin: 0; padding: 0;">
                        Laporan Lembur Karyawan
                        <br>
                        {{ $generalsetting->nama_perusahaan }}
                        <br>
                        Periode {{ date('d-m-Y', strtotime($periode_dari)) }} - {{ date('d-m-Y', strtotime($periode_sampai)) }}
                    </h4>
                    <span style="font-style: italic;">{{ $generalsetting->alamat }}</span><br>
                    <span style="font-style: italic;">{{ $generalsetting->telepon }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="tabel-data">
        <thead>
            <tr>
                <th>No</th>
                <th>NIK</th>
                <th>Nama Karyawan</th>
                <th>Jabatan</th>
                <th>Cabang</th>
                <th>Departemen</th>
                <th>Tanggal</th>
                <th>Rencana Lembur (Jam)</th>
                <th>Absen Masuk & Pulang</th>
                <th>Jam Rencana</th>
                <th>Jam Riil (Aktual)</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $total_rencana = 0;
                $total_aktual = 0;
            @endphp
            @forelse ($lembur as $d)
                @php
                    $rencana_jam = hitungjamlembur($d->lembur_mulai, $d->lembur_selesai);
                    $aktual_jam = 0;
                    if ($d->lembur_in && $d->lembur_out) {
                        $aktual_jam = hitungLembur([
                            [
                                'lembur_in' => $d->lembur_in,
                                'lembur_out' => $d->lembur_out,
                                'lembur_mulai' => $d->lembur_mulai,
                                'lembur_selesai' => $d->lembur_selesai
                            ]
                        ]);
                    }
                    $total_rencana += $rencana_jam;
                    $total_aktual += $aktual_jam;

                    $search = [
                        'nik' => $d->nik,
                        'tanggal' => $d->tanggal,
                    ];
                    $ceklibur = ceklibur($datalibur ?? [], $search);
                    $is_weekend = in_array(date('w', strtotime($d->tanggal)), [0, 6]);
                    $is_designated_holiday = !empty($ceklibur);
                    $row_style = '';
                    if ($is_designated_holiday && !$is_weekend) {
                        $row_style = 'background-color: #59B292; color: white;';
                    } elseif ($is_weekend) {
                        $row_style = 'background-color: #622B14; color: white;';
                    }
                @endphp
                <tr @if($row_style) style="{{ $row_style }}" @endif>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center">{{ $d->nik_show ?? $d->nik }}</td>
                    <td>{{ $d->nama_karyawan }}</td>
                    <td>{{ $d->nama_jabatan }}</td>
                    <td class="text-center">{{ $d->nama_cabang }}</td>
                    <td class="text-center">{{ $d->nama_dept }}</td>
                    <td class="text-center">{{ date('d-m-Y', strtotime($d->tanggal)) }}</td>
                    <td class="text-center">
                        {{ date('H:i', strtotime($d->lembur_mulai)) }} - {{ date('H:i', strtotime($d->lembur_selesai)) }}
                    </td>
                    <td class="text-center">
                        @if ($d->lembur_in && $d->lembur_out)
                            {{ date('H:i', strtotime($d->lembur_in)) }} - {{ date('H:i', strtotime($d->lembur_out)) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center" style="font-weight: bold;">
                        {{ formatAngkaDesimal($rencana_jam) }}
                    </td>
                    <td class="text-center" style="font-weight: bold;">
                        {{ formatAngkaDesimal($aktual_jam) }}
                    </td>
                    <td class="text-center">
                        @if ($d->status == '1')
                            <span class="badge badge-approved">Disetujui</span>
                        @elseif ($d->status == '2')
                            <span class="badge badge-rejected">Ditolak</span>
                        @else
                            <span class="badge badge-pending">Pending</span>
                        @endif
                    </td>
                    <td>{{ $d->keterangan }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center" style="padding: 15px; font-style: italic; color: #888;">
                        Tidak ada data lembur dalam periode yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($lembur->isNotEmpty())
            <tfoot>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="9" class="text-right" style="padding: 8px;">TOTAL JAM</td>
                    <td class="text-center" style="padding: 8px; border: 1px solid #131212;">
                        {{ formatAngkaDesimal($total_rencana) }}
                    </td>
                    <td class="text-center" style="padding: 8px; border: 1px solid #131212;">
                        {{ formatAngkaDesimal($total_aktual) }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <table width="100%" style="margin-top: 60px; page-break-inside: avoid; font-size: 11px;">
        <tr>
            <td style="text-align: center; vertical-align: bottom;" height="80px">
                <u>Diny Nurani</u><br>
                <i><b>HRD Manager</b></i>
            </td>
            <td style="text-align: center; vertical-align: bottom;">
                <u>R. Andrie Gunawan</u><br>
                <i><b>Direktur</b></i>
            </td>
        </tr>
    </table>

</body>

</html>

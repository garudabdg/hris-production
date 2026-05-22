<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laporan Lembur</title>
</head>

<body>
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr>
                <td colspan="13" style="font-weight: bold; font-size: 14px;">LAPORAN LEMBUR KARYAWAN</td>
            </tr>
            <tr>
                <td colspan="13" style="font-weight: bold; font-size: 14px;">{{ $generalsetting->nama_perusahaan }}</td>
            </tr>
            <tr>
                <td colspan="13" style="font-size: 12px;">PERIODE {{ date('d-m-Y', strtotime($periode_dari)) }} - {{ date('d-m-Y', strtotime($periode_sampai)) }}</td>
            </tr>
            <tr>
                <td colspan="13" style="font-size: 11px; font-style: italic;">{{ $generalsetting->alamat }}</td>
            </tr>
            <tr>
                <td colspan="13" style="font-size: 11px; font-style: italic;">{{ $generalsetting->telepon }}</td>
            </tr>
            <tr>
                <td colspan="13"></td>
            </tr>
            <tr style="background-color: #024a75; color: white;">
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; vertical-align: middle;">No</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; vertical-align: middle;">NIK</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: left; vertical-align: middle;">Nama Karyawan</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: left; vertical-align: middle;">Jabatan</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; vertical-align: middle;">Cabang</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; vertical-align: middle;">Departemen</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; vertical-align: middle;">Tanggal</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; vertical-align: middle;">Rencana Lembur (Jam)</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; vertical-align: middle;">Absen Masuk & Pulang</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; vertical-align: middle;">Jam Rencana</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; vertical-align: middle;">Jam Riil (Aktual)</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: center; vertical-align: middle;">Status</th>
                <th style="border: 1px solid #000000; font-weight: bold; text-align: left; vertical-align: middle;">Keterangan</th>
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
                @endphp
                <tr>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $loop->iteration }}</td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">'{{ $d->nik_show ?? $d->nik }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle;">{{ $d->nama_karyawan }}</td>
                    <td style="border: 1px solid #000000; vertical-align: middle;">{{ $d->nama_jabatan }}</td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $d->nama_cabang }}</td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ $d->nama_dept }}</td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">{{ date('d-m-Y', strtotime($d->tanggal)) }}</td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">
                        {{ date('H:i', strtotime($d->lembur_mulai)) }} - {{ date('H:i', strtotime($d->lembur_selesai)) }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">
                        @if ($d->lembur_in && $d->lembur_out)
                            {{ date('H:i', strtotime($d->lembur_in)) }} - {{ date('H:i', strtotime($d->lembur_out)) }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
                        {{ formatAngkaDesimal($rencana_jam) }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle; font-weight: bold;">
                        {{ formatAngkaDesimal($aktual_jam) }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: center; vertical-align: middle;">
                        @if ($d->status == '1')
                            Disetujui
                        @elseif ($d->status == '2')
                            Ditolak
                        @else
                            Pending
                        @endif
                    </td>
                    <td style="border: 1px solid #000000; vertical-align: middle;">{{ $d->keterangan }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="border: 1px solid #000000; text-align: center; vertical-align: middle; font-style: italic; height: 30px;">
                        Tidak ada data lembur dalam periode yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($lembur->isNotEmpty())
            <tfoot>
                <tr style="background-color: #f2f2f2; font-weight: bold;">
                    <td colspan="9" style="border: 1px solid #000000; text-align: right; font-weight: bold; padding: 6px;">TOTAL JAM</td>
                    <td style="border: 1px solid #000000; text-align: center; font-weight: bold; padding: 6px;">
                        {{ formatAngkaDesimal($total_rencana) }}
                    </td>
                    <td style="border: 1px solid #000000; text-align: center; font-weight: bold; padding: 6px;">
                        {{ formatAngkaDesimal($total_aktual) }}
                    </td>
                    <td colspan="2" style="border: 1px solid #000000;"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>

</html>

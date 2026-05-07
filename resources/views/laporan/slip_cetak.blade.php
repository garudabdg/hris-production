<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji {{ date('Y-m-d H:i:s') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            background: #e8e8e8;
            padding: 20px;
            color: #333;
        }
        .page-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: flex-start;
        }
        .slip-card {
            width: 700px;
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 6px;
            overflow: hidden;
            page-break-inside: avoid;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        /* ── HEADER ── */
        .slip-header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 18px;
            border-bottom: 3px solid #1a56b0;
        }
        .slip-header img.logo {
            height: 56px;
            width: auto;
            object-fit: contain;
        }
        .slip-header .logo-placeholder {
            width: 56px; height: 56px;
            background: #1a56b0;
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: bold; font-size: 18px;
        }
        .company-info { flex: 1; }
        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #1a56b0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .company-address {
            font-size: 9.5px;
            color: #555;
            margin-top: 2px;
            line-height: 1.5;
        }
        .slip-title-box {
            text-align: right;
            min-width: 130px;
        }
        .slip-title-box .title {
            font-size: 14px;
            font-weight: bold;
            color: #1a56b0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .slip-title-box .periode {
            font-size: 9.5px;
            color: #666;
            margin-top: 3px;
        }

        /* ── INFO KARYAWAN ── */
        .employee-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            background: #f4f8ff;
            border-bottom: 1px solid #dce6f0;
            padding: 10px 18px;
        }
        .emp-row {
            display: flex;
            padding: 2px 0;
            font-size: 10.5px;
        }
        .emp-label {
            width: 100px;
            color: #555;
            font-weight: bold;
            flex-shrink: 0;
        }
        .emp-sep { margin: 0 4px; color: #888; }
        .emp-value { color: #222; }

        /* ── BODY ── */
        .slip-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border-bottom: 1px solid #dce6f0;
        }
        .slip-col {
            padding: 12px 18px;
        }
        .slip-col:first-child {
            border-right: 1px solid #dce6f0;
        }
        .col-title {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 4px 8px;
            border-radius: 3px;
            margin-bottom: 8px;
            text-align: center;
        }
        .col-title.earn  { background: #d4edda; color: #155724; }
        .col-title.deduct { background: #f8d7da; color: #721c24; }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2.5px 0;
            font-size: 10.5px;
            border-bottom: 1px dotted #eee;
        }
        .item-row:last-child { border-bottom: none; }
        .item-name { color: #444; }
        .item-amount { font-family: 'Courier New', monospace; font-size: 10.5px; text-align: right; white-space: nowrap; }
        .item-amount.plus  { color: #155724; }
        .item-amount.minus { color: #721c24; }

        .subtotal-row {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 10.5px;
            padding: 5px 0 0 0;
            margin-top: 4px;
            border-top: 1.5px solid #999;
        }

        /* ── PENYESUAIAN ── */
        .adjust-section {
            padding: 8px 18px;
            display: flex;
            gap: 24px;
            border-bottom: 1px solid #dce6f0;
            background: #fffde7;
        }
        .adjust-title {
            font-size: 10px;
            font-weight: bold;
            color: #856404;
            text-transform: uppercase;
            margin-right: 8px;
        }
        .adjust-item {
            font-size: 10.5px;
            color: #555;
        }
        .adjust-item span { font-family: 'Courier New', monospace; color: #333; }

        /* ── GAJI BERSIH ── */
        .net-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            background: #1a56b0;
            color: #fff;
        }
        .net-label {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .net-amount {
            font-size: 16px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }

        /* ── FOOTER ── */
        .slip-footer {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            padding: 10px 18px;
            font-size: 9.5px;
            color: #777;
            border-top: 1px solid #e0e0e0;
            gap: 12px;
        }
        .sign-box { text-align: center; }
        .sign-line {
            border-bottom: 1px solid #999;
            margin: 28px 10px 4px 10px;
        }
        .sign-label { font-size: 9px; color: #666; }

        @media print {
            body { background: white; padding: 5px; }
            .slip-card { box-shadow: none; border: 1px solid #999; width: 100%; page-break-after: always; }
            .page-container { gap: 0; }
        }
        @media (max-width: 800px) {
            .slip-card { width: 100%; }
            .slip-body { grid-template-columns: 1fr; }
            .slip-col:first-child { border-right: none; border-bottom: 1px solid #dce6f0; }
        }
    </style>
</head>

<body>
    @foreach ($laporan_presensi as $d)
        @php
            $tanggal_presensi = $periode_dari;
            $total_denda = 0;
            $total_potongan_jam = 0;
            $total_tunjangan = 0;

            // Mapping jadwal untuk NIK ini dari berbagai sumber (sama seperti presensi_cetak & gaji_cetak)
            $mapJadwalByDate = $jadwal_bydate[$d['nik']] ?? [];
            $mapJadwalGrupByDate = $jadwal_grup_bydate[$d['nik']] ?? [];
            $mapJadwalByDay = $jadwal_byday[$d['nik']] ?? [];

            // Kalkulasi upah per jam
            $upah_perjam = $d['gaji_pokok'] / $generalsetting->total_jam_bulan;
        @endphp

        {{-- Proses kalkulasi denda dan potongan jam --}}
        @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
            @php
                $denda = 0;
                $potongan_jam = 0;
                $search = [
                    'nik' => $d['nik'],
                    'tanggal' => $tanggal_presensi,
                ];
                $ceklibur = ceklibur($datalibur, $search);
                $ceklembur = ceklembur($datalembur, $search);
                $lembur = hitungLembur($ceklembur);
                if (!empty($ceklembur)) {
                    $jml_jam_lembur = $lembur;
                } else {
                    $jml_jam_lembur = 0;
                }
            @endphp

            @if (isset($d[$tanggal_presensi]))
                @if ($d[$tanggal_presensi]['status'] == 'h')
                    @php
                        $jam_masuk = $tanggal_presensi . ' ' . $d[$tanggal_presensi]['jam_masuk'];
                        $terlambat = hitungjamterlambat($d[$tanggal_presensi]['jam_in'], $jam_masuk);

                        // Jika denda sudah dikunci di database, gunakan nilai tersebut
                        $denda_dari_db =
                            isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null ? $d[$tanggal_presensi]['denda'] : null;

                        if ($denda_dari_db !== null) {
                            // Denda sudah dikunci, gunakan dari DB
                            $denda = $denda_dari_db;

                            // Potongan jam tetap dihitung dengan rumus
                            if ($terlambat != null) {
                                if ($terlambat['desimal_terlambat'] < 1) {
                                    $potongan_jam_terlambat = 0;
                                } else {
                                    $potongan_jam_terlambat =
                                        $terlambat['desimal_terlambat'] > $d[$tanggal_presensi]['total_jam']
                                            ? $d[$tanggal_presensi]['total_jam']
                                            : $terlambat['desimal_terlambat'];
                                }
                            } else {
                                $potongan_jam_terlambat = 0;
                            }
                        } else {
                            // Belum dikunci → gunakan rumus hitungdenda seperti biasa
                            if ($terlambat != null) {
                                if ($terlambat['desimal_terlambat'] < 1) {
                                    $potongan_jam_terlambat = 0;
                                    $denda = hitungdenda($denda_list, $terlambat['menitterlambat']);
                                } else {
                                    $potongan_jam_terlambat =
                                        $terlambat['desimal_terlambat'] > $d[$tanggal_presensi]['total_jam']
                                            ? $d[$tanggal_presensi]['total_jam']
                                            : $terlambat['desimal_terlambat'];
                                    $denda = 0;
                                }
                            } else {
                                $potongan_jam_terlambat = 0;
                                $denda = 0;
                            }
                        }

                        $pulangcepat = hitungpulangcepat(
                            $tanggal_presensi,
                            $d[$tanggal_presensi]['jam_out'],
                            $d[$tanggal_presensi]['jam_pulang'],
                            $d[$tanggal_presensi]['istirahat'],
                            $d[$tanggal_presensi]['jam_awal_istirahat'],
                            $d[$tanggal_presensi]['jam_akhir_istirahat'],
                            $d[$tanggal_presensi]['lintashari'],
                        );
                        $pulangcepat = $pulangcepat > $d[$tanggal_presensi]['total_jam'] ? $d[$tanggal_presensi]['total_jam'] : $pulangcepat;

                        $potongan_tidak_absen_masuk_atau_pulang =
                            empty($d[$tanggal_presensi]['jam_out']) || empty($d[$tanggal_presensi]['jam_in'])
                                ? $d[$tanggal_presensi]['total_jam']
                                : 0;
                        $potongan_jam =
                            $potongan_tidak_absen_masuk_atau_pulang == 0
                                ? $pulangcepat + $potongan_jam_terlambat
                                : $potongan_tidak_absen_masuk_atau_pulang;
                    @endphp
                @elseif($d[$tanggal_presensi]['status'] == 'i')
                    @php
                        $potongan_jam = $d[$tanggal_presensi]['total_jam'];

                        // Izin: jika denda sudah dikunci, ambil dari DB, jika tidak 0
                        $denda_dari_db =
                            isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null ? $d[$tanggal_presensi]['denda'] : null;
                        $denda = $denda_dari_db !== null ? $denda_dari_db : 0;
                    @endphp
                @elseif($d[$tanggal_presensi]['status'] == 'a')
                    @php
                        $potongan_jam = $d[$tanggal_presensi]['total_jam'];

                        // Alpa: jika denda sudah dikunci, ambil dari DB, jika tidak 0
                        $denda_dari_db =
                            isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null ? $d[$tanggal_presensi]['denda'] : null;
                        $denda = $denda_dari_db !== null ? $denda_dari_db : 0;
                    @endphp
                @endif
            @else
                @php
                    // Tidak ada data presensi di tanggal ini
                    // Jika hari libur, tidak ada potongan jam
                    if (empty($ceklibur)) {
                        // Bukan libur → cek jadwal berurutan (sama seperti presensi_cetak & gaji_cetak)
                        // 1) Jadwal by-date per karyawan
                        $totalJamJadwal = $mapJadwalByDate[$tanggal_presensi] ?? null;

                        // 2) Kalau kosong, cek jadwal grup by-date
                        if ($totalJamJadwal === null) {
                            $totalJamJadwal = $mapJadwalGrupByDate[$tanggal_presensi] ?? null;
                        }

                        // 3) Kalau masih kosong, cek jadwal by-day per karyawan
                        if ($totalJamJadwal === null) {
                            $nama_hari = getHari($tanggal_presensi);
                            $totalJamJadwal = $mapJadwalByDay[$nama_hari] ?? null;
                        }

                        // 4) Kalau masih kosong, cek jadwal by-day per departemen & cabang
                        if ($totalJamJadwal === null) {
                            $nama_hari = isset($nama_hari) ? $nama_hari : getHari($tanggal_presensi);
                            $keyDeptCabang = $d['kode_dept'] . '|' . $d['kode_cabang'];
                            $mapDept = $jadwal_bydept[$keyDeptCabang] ?? [];
                            $totalJamJadwal = $mapDept[$nama_hari] ?? null;
                        }

                        // Jika ada jadwal tapi tidak ada presensi sama sekali → potongan jam = total_jam jadwal
                        if ($totalJamJadwal !== null) {
                            $potongan_jam = $totalJamJadwal;
                        }
                    }
                @endphp
            @endif

            @php
                $total_denda += $denda;
                $total_potongan_jam += $potongan_jam;
                $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
            @endphp
        @endwhile

        @php
            // Final calculations
            if ($generalsetting->status_potongan_jam == 0) {
                $total_potongan_jam = 0;
            } elseif ($total_potongan_jam > $generalsetting->total_jam_bulan) {
                $total_potongan_jam = $generalsetting->total_jam_bulan;
            }
            $jumlah_potongan_jam = ROUND($upah_perjam) * $total_potongan_jam;
            $total_potongan = ROUND($jumlah_potongan_jam) + $total_denda + $d['bpjs_kesehatan'] + $d['bpjs_tenagakerja'];

        @endphp

        <!-- Slip akan ditampilkan dalam container flex untuk layout horizontal -->
    @endforeach

    <!-- Container untuk layout -->
    <div class="page-container">
        @foreach ($laporan_presensi as $d)
            @php
                $tanggal_presensi = $periode_dari;
                $total_denda = 0;
                $total_potongan_jam = 0;
                $total_tunjangan = 0;
                $total_jam_lembur = 0;

                // Mapping jadwal untuk NIK ini dari berbagai sumber (sama seperti presensi_cetak & gaji_cetak)
                $mapJadwalByDate = $jadwal_bydate[$d['nik']] ?? [];
                $mapJadwalGrupByDate = $jadwal_grup_bydate[$d['nik']] ?? [];
                $mapJadwalByDay = $jadwal_byday[$d['nik']] ?? [];

                // Kalkulasi tunjangan
                foreach ($jenis_tunjangan as $j) {
                    $total_tunjangan += $d[$j->kode_jenis_tunjangan];
                }

                // Kalkulasi bruto
                $bruto = $d['gaji_pokok'] + $total_tunjangan;

                // Kalkulasi upah per jam
                $upah_perjam = $d['gaji_pokok'] / $generalsetting->total_jam_bulan;
            @endphp

            {{-- Proses kalkulasi denda dan potongan jam --}}
            @while (strtotime($tanggal_presensi) <= strtotime($periode_sampai))
                @php
                    $denda = 0;
                    $potongan_jam = 0;
                    $search = [
                        'nik' => $d['nik'],
                        'tanggal' => $tanggal_presensi,
                    ];
                    $ceklibur = ceklibur($datalibur, $search);
                    $ceklembur = ceklembur($datalembur, $search);
                    $lembur = hitungLembur($ceklembur);
                    if (!empty($ceklembur)) {
                        $jml_jam_lembur = $lembur;
                    } else {
                        $jml_jam_lembur = 0;
                    }
                @endphp

                @if (isset($d[$tanggal_presensi]))
                    @if ($d[$tanggal_presensi]['status'] == 'h')
                        @php
                            $jam_masuk = $tanggal_presensi . ' ' . $d[$tanggal_presensi]['jam_masuk'];
                            $terlambat = hitungjamterlambat($d[$tanggal_presensi]['jam_in'], $jam_masuk);

                            // Jika denda sudah dikunci di database, gunakan nilai tersebut
                            $denda_dari_db =
                                isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null
                                    ? $d[$tanggal_presensi]['denda']
                                    : null;

                            if ($denda_dari_db !== null) {
                                // Denda sudah dikunci, gunakan dari DB
                                $denda = $denda_dari_db;

                                // Potongan jam tetap dihitung dengan rumus
                                if ($terlambat != null) {
                                    if ($terlambat['desimal_terlambat'] < 1) {
                                        $potongan_jam_terlambat = 0;
                                    } else {
                                        $potongan_jam_terlambat =
                                            $terlambat['desimal_terlambat'] > $d[$tanggal_presensi]['total_jam']
                                                ? $d[$tanggal_presensi]['total_jam']
                                                : $terlambat['desimal_terlambat'];
                                    }
                                } else {
                                    $potongan_jam_terlambat = 0;
                                }
                            } else {
                                // Belum dikunci → gunakan rumus hitungdenda seperti biasa
                                if ($terlambat != null) {
                                    if ($terlambat['desimal_terlambat'] < 1) {
                                        $potongan_jam_terlambat = 0;
                                        $denda = hitungdenda($denda_list, $terlambat['menitterlambat']);
                                    } else {
                                        $potongan_jam_terlambat =
                                            $terlambat['desimal_terlambat'] > $d[$tanggal_presensi]['total_jam']
                                                ? $d[$tanggal_presensi]['total_jam']
                                                : $terlambat['desimal_terlambat'];
                                        $denda = 0;
                                    }
                                } else {
                                    $potongan_jam_terlambat = 0;
                                    $denda = 0;
                                }
                            }

                            $pulangcepat = hitungpulangcepat(
                                $tanggal_presensi,
                                $d[$tanggal_presensi]['jam_out'],
                                $d[$tanggal_presensi]['jam_pulang'],
                                $d[$tanggal_presensi]['istirahat'],
                                $d[$tanggal_presensi]['jam_awal_istirahat'],
                                $d[$tanggal_presensi]['jam_akhir_istirahat'],
                                $d[$tanggal_presensi]['lintashari'],
                            );
                            $pulangcepat = $pulangcepat > $d[$tanggal_presensi]['total_jam'] ? $d[$tanggal_presensi]['total_jam'] : $pulangcepat;
                            $potongan_tidak_absen_masuk_atau_pulang =
                                empty($d[$tanggal_presensi]['jam_out']) || empty($d[$tanggal_presensi]['jam_in'])
                                    ? $d[$tanggal_presensi]['total_jam']
                                    : 0;
                            $potongan_jam =
                                $potongan_tidak_absen_masuk_atau_pulang == 0
                                    ? $pulangcepat + $potongan_jam_terlambat
                                    : $potongan_tidak_absen_masuk_atau_pulang;
                        @endphp
                    @elseif($d[$tanggal_presensi]['status'] == 'i')
                        @php
                            $potongan_jam = $d[$tanggal_presensi]['total_jam'];

                            // Izin: jika denda sudah dikunci, ambil dari DB, jika tidak 0
                            $denda_dari_db =
                                isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null
                                    ? $d[$tanggal_presensi]['denda']
                                    : null;
                            $denda = $denda_dari_db !== null ? $denda_dari_db : 0;
                        @endphp
                    @elseif($d[$tanggal_presensi]['status'] == 'a')
                        @php
                            $potongan_jam = $d[$tanggal_presensi]['total_jam'];

                            // Alpa: jika denda sudah dikunci, ambil dari DB, jika tidak 0
                            $denda_dari_db =
                                isset($d[$tanggal_presensi]['denda']) && $d[$tanggal_presensi]['denda'] !== null
                                    ? $d[$tanggal_presensi]['denda']
                                    : null;
                            $denda = $denda_dari_db !== null ? $denda_dari_db : 0;
                        @endphp
                    @endif
                @else
                    @php
                        // Tidak ada data presensi di tanggal ini
                        // Jika hari libur, tidak ada potongan jam
                        if (empty($ceklibur)) {
                            // Bukan libur → cek jadwal berurutan (sama seperti presensi_cetak & gaji_cetak)
                            // 1) Jadwal by-date per karyawan
                            $totalJamJadwal = $mapJadwalByDate[$tanggal_presensi] ?? null;

                            // 2) Kalau kosong, cek jadwal grup by-date
                            if ($totalJamJadwal === null) {
                                $totalJamJadwal = $mapJadwalGrupByDate[$tanggal_presensi] ?? null;
                            }

                            // 3) Kalau masih kosong, cek jadwal by-day per karyawan
                            if ($totalJamJadwal === null) {
                                $nama_hari = getHari($tanggal_presensi);
                                $totalJamJadwal = $mapJadwalByDay[$nama_hari] ?? null;
                            }

                            // 4) Kalau masih kosong, cek jadwal by-day per departemen & cabang
                            if ($totalJamJadwal === null) {
                                $nama_hari = isset($nama_hari) ? $nama_hari : getHari($tanggal_presensi);
                                $keyDeptCabang = $d['kode_dept'] . '|' . $d['kode_cabang'];
                                $mapDept = $jadwal_bydept[$keyDeptCabang] ?? [];
                                $totalJamJadwal = $mapDept[$nama_hari] ?? null;
                            }

                            // Jika ada jadwal tapi tidak ada presensi sama sekali → potongan jam = total_jam jadwal
                            if ($totalJamJadwal !== null) {
                                $potongan_jam = $totalJamJadwal;
                            }
                        }
                    @endphp
                @endif

                @php
                    $status_potongan_harian = isset($d[$tanggal_presensi]['status_potongan']) ? $d[$tanggal_presensi]['status_potongan'] : $generalsetting->status_potongan_jam;
                    if ($status_potongan_harian == 0) {
                        $potongan_jam = 0;
                    }
                    $total_denda += $denda;
                    $total_potongan_jam += $potongan_jam;
                    $total_jam_lembur += $jml_jam_lembur;
                    $tanggal_presensi = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_presensi)));
                @endphp
            @endwhile

            @php
                // Final calculations
                if ($total_potongan_jam > $generalsetting->total_jam_bulan) {
                    $total_potongan_jam = $generalsetting->total_jam_bulan;
                }
                $upah_lembur = ROUND($upah_perjam) * ROUND($total_jam_lembur, 2);
                $jumlah_potongan_jam = ROUND($upah_perjam) * $total_potongan_jam;
                $total_potongan = ROUND($jumlah_potongan_jam) + $total_denda + $d['bpjs_kesehatan'] + $d['bpjs_tenagakerja'];
                $bruto_total = $bruto + ROUND($upah_lembur);
                $gaji_bersih = $d['gaji_pokok'] + $total_tunjangan - $total_potongan + $d['penambah'] - $d['pengurang'] + ROUND($upah_lembur);
            @endphp

            <div class="slip-card">

                {{-- ── HEADER ── --}}
                <div class="slip-header">
                    @if (!empty($generalsetting->logo) && file_exists(storage_path('app/public/logo/' . $generalsetting->logo)))
                        <img src="{{ asset('storage/logo/' . $generalsetting->logo) }}" alt="Logo" class="logo">
                    @else
                        <div class="logo-placeholder">{{ strtoupper(substr($generalsetting->nama_perusahaan, 0, 2)) }}</div>
                    @endif
                    <div class="company-info">
                        <div class="company-name">{{ $generalsetting->nama_perusahaan }}</div>
                        <div class="company-address">
                            {{ $generalsetting->alamat }}<br>
                            @if ($generalsetting->telepon) Telp: {{ $generalsetting->telepon }} @endif
                        </div>
                    </div>
                    <div class="slip-title-box">
                        <div class="title">Slip Gaji</div>
                        <div class="periode">
                            {{ \Carbon\Carbon::parse($periode_dari)->translatedFormat('d M Y') }}<br>
                            s/d {{ \Carbon\Carbon::parse($periode_sampai)->translatedFormat('d M Y') }}
                        </div>
                    </div>
                </div>

                {{-- ── INFO KARYAWAN ── --}}
                <div class="employee-section">
                    <div>
                        <div class="emp-row">
                            <span class="emp-label">NIK</span>
                            <span class="emp-sep">:</span>
                            <span class="emp-value">{{ $d['nik_show'] ?? $d['nik'] }}</span>
                        </div>
                        <div class="emp-row">
                            <span class="emp-label">Nama</span>
                            <span class="emp-sep">:</span>
                            <span class="emp-value"><strong>{{ $d['nama_karyawan'] }}</strong></span>
                        </div>
                        <div class="emp-row">
                            <span class="emp-label">Jabatan</span>
                            <span class="emp-sep">:</span>
                            <span class="emp-value">{{ $d['nama_jabatan'] ?? '-' }}</span>
                        </div>
                    </div>
                    <div>
                        <div class="emp-row">
                            <span class="emp-label">Departemen</span>
                            <span class="emp-sep">:</span>
                            <span class="emp-value">{{ $d['nama_dept'] ?? $d['kode_dept'] ?? '-' }}</span>
                        </div>
                        <div class="emp-row">
                            <span class="emp-label">Cabang</span>
                            <span class="emp-sep">:</span>
                            <span class="emp-value">{{ $d['nama_cabang'] ?? $d['kode_cabang'] ?? '-' }}</span>
                        </div>
                        @if (!empty($d['tanggal_masuk']))
                        <div class="emp-row">
                            <span class="emp-label">Tgl Masuk</span>
                            <span class="emp-sep">:</span>
                            <span class="emp-value">{{ \Carbon\Carbon::parse($d['tanggal_masuk'])->translatedFormat('d M Y') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ── PENGHASILAN & POTONGAN ── --}}
                <div class="slip-body">
                    {{-- Kolom Penghasilan --}}
                    <div class="slip-col">
                        <div class="col-title earn">&#43; Penghasilan</div>

                        <div class="item-row">
                            <span class="item-name">Gaji Pokok</span>
                            <span class="item-amount plus">{{ number_format($d['gaji_pokok'], 0, ',', '.') }}</span>
                        </div>
                        @foreach ($jenis_tunjangan as $j)
                            @if ($d[$j->kode_jenis_tunjangan] > 0)
                            <div class="item-row">
                                <span class="item-name">{{ $j->jenis_tunjangan }}</span>
                                <span class="item-amount plus">{{ number_format($d[$j->kode_jenis_tunjangan], 0, ',', '.') }}</span>
                            </div>
                            @endif
                        @endforeach
                        @if ($total_jam_lembur > 0)
                        <div class="item-row">
                            <span class="item-name">Lembur ({{ formatAngkaDesimal($total_jam_lembur) }} jam)</span>
                            <span class="item-amount plus">{{ formatAngka($upah_lembur) }}</span>
                        </div>
                        @endif

                        <div class="subtotal-row">
                            <span>Sub Total</span>
                            <span style="font-family:'Courier New',monospace;">{{ number_format($bruto_total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Kolom Potongan --}}
                    <div class="slip-col">
                        <div class="col-title deduct">&#8722; Potongan</div>

                        @if ($total_denda > 0)
                        <div class="item-row">
                            <span class="item-name">Denda Keterlambatan</span>
                            <span class="item-amount minus">{{ number_format($total_denda, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if ($jumlah_potongan_jam > 0)
                        <div class="item-row">
                            <span class="item-name">Potongan Jam ({{ number_format($total_potongan_jam, 2) }} jam)</span>
                            <span class="item-amount minus">{{ number_format($jumlah_potongan_jam, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if ($d['bpjs_kesehatan'] > 0)
                        <div class="item-row">
                            <span class="item-name">BPJS Kesehatan</span>
                            <span class="item-amount minus">{{ number_format($d['bpjs_kesehatan'], 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if ($d['bpjs_tenagakerja'] > 0)
                        <div class="item-row">
                            <span class="item-name">BPJS Ketenagakerjaan</span>
                            <span class="item-amount minus">{{ number_format($d['bpjs_tenagakerja'], 0, ',', '.') }}</span>
                        </div>
                        @endif

                        @if ($total_denda == 0 && $jumlah_potongan_jam == 0 && $d['bpjs_kesehatan'] == 0 && $d['bpjs_tenagakerja'] == 0)
                        <div class="item-row" style="color:#aaa; font-style:italic;">
                            <span class="item-name">Tidak ada potongan</span>
                            <span class="item-amount">-</span>
                        </div>
                        @endif

                        <div class="subtotal-row">
                            <span>Sub Total</span>
                            <span style="font-family:'Courier New',monospace;">{{ number_format($total_potongan, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- ── PENYESUAIAN ── --}}
                @if ($d['penambah'] > 0 || $d['pengurang'] > 0)
                <div class="adjust-section">
                    <span class="adjust-title">&#9654; Penyesuaian</span>
                    @if ($d['penambah'] > 0)
                    <div class="adjust-item">Penambah: <span>+ {{ number_format($d['penambah'], 0, ',', '.') }}</span></div>
                    @endif
                    @if ($d['pengurang'] > 0)
                    <div class="adjust-item">Pengurang: <span>- {{ number_format($d['pengurang'], 0, ',', '.') }}</span></div>
                    @endif
                </div>
                @endif

                {{-- ── GAJI BERSIH ── --}}
                <div class="net-section">
                    <div>
                        <div class="net-label">&#9654; Gaji Bersih</div>
                        <div style="font-size:9px; opacity:0.8; margin-top:2px;">
                            {{ \Carbon\Carbon::parse($periode_dari)->translatedFormat('M Y') }}
                        </div>
                    </div>
                    <div class="net-amount">Rp {{ number_format($gaji_bersih, 0, ',', '.') }}</div>
                </div>

                {{-- ── FOOTER TANDA TANGAN ── --}}
                <div class="slip-footer">
                    <div class="sign-box">
                        <div class="sign-line"></div>
                        <div class="sign-label">Karyawan</div>
                        <div style="font-size:9px; margin-top:2px;">({{ $d['nama_karyawan'] }})</div>
                    </div>
                    <div class="sign-box" style="text-align:center; align-self:end; font-size:9px; color:#aaa;">
                        Dicetak: {{ date('d/m/Y H:i') }}
                    </div>
                    <div class="sign-box">
                        <div class="sign-line"></div>
                        <div class="sign-label">HRD / Pimpinan</div>
                        <div style="font-size:9px; margin-top:2px;">({{ $generalsetting->nama_hrd ?? '' }})</div>
                    </div>
                </div>

            </div>{{-- end slip-card --}}
        @endforeach
    </div>
</body>

</html>

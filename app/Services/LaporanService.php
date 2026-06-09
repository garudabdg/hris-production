<?php

namespace App\Services;

use App\Models\Pengaturanumum;
use App\Models\Presensi;
use App\Models\Karyawan;
use App\Models\Denda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LaporanService
{
    public function prosesKunciLaporan(Request $request)
    {
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        [$periode_dari, $periode_sampai] = $this->resolvePeriodeLaporan($request, $generalsetting);

        $jadwalMapping = $this->getJadwalMapping($periode_dari, $periode_sampai);
        $jadwal_bydate = $jadwalMapping['bydate'];
        $jadwal_grup_bydate = $jadwalMapping['grup_bydate'];
        $jadwal_byday = $jadwalMapping['byday'];
        $jadwal_bydept = $jadwalMapping['bydept'];

        $datalibur = getdatalibur($periode_dari, $periode_sampai);

        $presensi_query = Presensi::leftJoin('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->leftJoin('karyawan', 'presensi.nik', '=', 'karyawan.nik')
            ->select(
                'presensi.*',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang'
            )
            ->whereBetween('presensi.tanggal', [$periode_dari, $periode_sampai]);

        $this->applyKaryawanFilters($presensi_query, $request, 'presensi.nik');

        $presensi_list_raw = $presensi_query->get();
        $presensi_list = $presensi_list_raw->groupBy('nik');
        $denda_list = Denda::all()->toArray();

        $karyawan_query = Karyawan::query()
            ->select('karyawan.nik', 'karyawan.kode_dept', 'karyawan.kode_cabang');

        $this->applyKaryawanFilters($karyawan_query, $request, 'karyawan.nik');

        if (!empty($request->jenis_upah)) {
            $gaji_pokok = DB::table('gaji_pokok')->select('nik', 'jenis_upah');
            $karyawan_query->leftJoinSub($gaji_pokok, 'gaji_pokok', function ($join) {
                $join->on('karyawan.nik', '=', 'gaji_pokok.nik');
            });
            $karyawan_query->where('gaji_pokok.jenis_upah', $request->jenis_upah);
        }

        $karyawan_list = $karyawan_query->get();

        $updated_count = 0;
        $inserted_alpa_count = 0;
        
        $dendaUpdates = [];
        $alpaInserts = [];
        $now = Carbon::now()->toDateTimeString();

        foreach ($karyawan_list as $karyawan) {
            $presensi_karyawan = $presensi_list[$karyawan->nik] ?? collect();
            $presensi_by_tanggal = $presensi_karyawan->keyBy('tanggal');

            $tanggal_loop = $periode_dari;
            while (strtotime($tanggal_loop) <= strtotime($periode_sampai)) {
                if ($presensi_by_tanggal->has($tanggal_loop)) {
                    $presensi = $presensi_by_tanggal[$tanggal_loop];
                    
                    if ($presensi->status === 'h') {
                        $jam_masuk = $presensi->tanggal . ' ' . $presensi->jam_masuk;
                        $terlambat = hitungjamterlambat($presensi->jam_in, $jam_masuk);

                        $denda = null;
                        if ($terlambat != null) {
                            if ($terlambat['desimal_terlambat'] < 1) {
                                $denda = hitungdenda($denda_list, $terlambat['menitterlambat']);
                            }
                        }

                        $dendaValue = $denda === null ? 'null' : $denda;
                        $dendaUpdates[$dendaValue][] = $presensi->id;
                        $updated_count++;
                    }
                } else {
                    $search = [
                        'nik' => $karyawan->nik,
                        'tanggal' => $tanggal_loop,
                    ];
                    
                    $ceklibur = ceklibur($datalibur, $search);
                    $nama_hari = getHari($tanggal_loop);

                    if (empty($ceklibur)) {
                        $mapJadwalByDate = $jadwal_bydate[$karyawan->nik] ?? [];
                        $mapJadwalGrupByDate = $jadwal_grup_bydate[$karyawan->nik] ?? [];
                        $mapJadwalByDay = $jadwal_byday[$karyawan->nik] ?? [];
                        
                        $jadwal_info = null;
                        $kode_jam_kerja = null;
                        
                        if (isset($mapJadwalByDate[$tanggal_loop])) {
                            $jadwal_info = $mapJadwalByDate[$tanggal_loop];
                            $kode_jam_kerja = $jadwal_info['kode_jam_kerja'];
                        }
                        elseif (isset($mapJadwalGrupByDate[$tanggal_loop])) {
                            $jadwal_info = $mapJadwalGrupByDate[$tanggal_loop];
                            $kode_jam_kerja = $jadwal_info['kode_jam_kerja'];
                        }
                        elseif (isset($mapJadwalByDay[$nama_hari])) {
                            $jadwal_info = $mapJadwalByDay[$nama_hari];
                            $kode_jam_kerja = $jadwal_info['kode_jam_kerja'];
                        }
                        else {
                            $keyDeptCabang = $karyawan->kode_dept . '|' . $karyawan->kode_cabang;
                            $mapDept = $jadwal_bydept[$keyDeptCabang] ?? [];
                            if (isset($mapDept[$nama_hari])) {
                                $jadwal_info = $mapDept[$nama_hari];
                                $kode_jam_kerja = $jadwal_info['kode_jam_kerja'];
                            }
                        }

                        if ($kode_jam_kerja !== null) {
                            $alpaInserts[] = [
                                'nik' => $karyawan->nik,
                                'tanggal' => $tanggal_loop,
                                'kode_jam_kerja' => $kode_jam_kerja,
                                'status' => 'a',
                                'jam_in' => null,
                                'jam_out' => null,
                                'denda' => null,
                                'status_potongan' => $generalsetting->status_potongan_jam,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                            $inserted_alpa_count++;
                        }
                    }
                }

                $tanggal_loop = date('Y-m-d', strtotime('+1 day', strtotime($tanggal_loop)));
            }
        }

        foreach ($dendaUpdates as $dendaValue => $ids) {
            $chunks = array_chunk($ids, 1000);
            $actualDenda = $dendaValue === 'null' ? null : $dendaValue;
            
            foreach ($chunks as $chunk) {
                Presensi::whereIn('id', $chunk)->update([
                    'denda' => $actualDenda,
                    'status_potongan' => $generalsetting->status_potongan_jam
                ]);
            }
        }

        if (!empty($alpaInserts)) {
            $chunks = array_chunk($alpaInserts, 500);
            foreach ($chunks as $chunk) {
                Presensi::insert($chunk);
            }
        }

        return [
            'success' => true,
            'message' => "Laporan berhasil dikunci. Total {$updated_count} presensi telah diupdate dengan denda, {$inserted_alpa_count} presensi alpa telah dibuat.",
            'updated_count' => $updated_count,
            'inserted_alpa_count' => $inserted_alpa_count
        ];
    }

    public function prosesBatalkanKunciLaporan(Request $request)
    {
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        [$periode_dari, $periode_sampai] = $this->resolvePeriodeLaporan($request, $generalsetting);

        $presensi_query = Presensi::query()
            ->select('presensi.id')
            ->whereBetween('presensi.tanggal', [$periode_dari, $periode_sampai]);

        if (!empty($request->kode_cabang) || !empty($request->kode_dept) || !empty($request->sub_departemen) || !empty($request->nik)) {
            $presensi_query->leftJoin('karyawan', 'presensi.nik', '=', 'karyawan.nik');
        }

        $this->applyKaryawanFilters($presensi_query, $request, 'presensi.nik');

        if (!empty($request->jenis_upah)) {
            $gaji_pokok = DB::table('gaji_pokok')->select('nik', 'jenis_upah');
            $presensi_query->leftJoinSub($gaji_pokok, 'gaji_pokok', function ($join) {
                $join->on('presensi.nik', '=', 'gaji_pokok.nik');
            });
            $presensi_query->where('gaji_pokok.jenis_upah', $request->jenis_upah);
        }

        $presensi_ids = $presensi_query->pluck('presensi.id')->toArray();

        $updated_count = 0;
        if (!empty($presensi_ids)) {
            $chunks = array_chunk($presensi_ids, 1000);
            foreach ($chunks as $chunk) {
                $updated_count += Presensi::whereIn('id', $chunk)->update([
                    'denda' => null,
                    'status_potongan' => null
                ]);
            }
        }

        $alpa_query = Presensi::query()
            ->whereBetween('presensi.tanggal', [$periode_dari, $periode_sampai])
            ->where('presensi.status', 'a');

        if (!empty($request->kode_cabang) || !empty($request->kode_dept) || !empty($request->sub_departemen) || !empty($request->nik)) {
            $alpa_query->leftJoin('karyawan', 'presensi.nik', '=', 'karyawan.nik');
        }

        $this->applyKaryawanFilters($alpa_query, $request, 'presensi.nik');

        if (!empty($request->jenis_upah)) {
            $gaji_pokok = DB::table('gaji_pokok')->select('nik', 'jenis_upah');
            $alpa_query->leftJoinSub($gaji_pokok, 'gaji_pokok', function ($join) {
                $join->on('presensi.nik', '=', 'gaji_pokok.nik');
            });
            $alpa_query->where('gaji_pokok.jenis_upah', $request->jenis_upah);
        }

        $alpa_ids = $alpa_query->pluck('presensi.id')->toArray();

        $deleted_alpa_count = 0;
        if (!empty($alpa_ids)) {
            $chunks = array_chunk($alpa_ids, 1000);
            foreach ($chunks as $chunk) {
                $deleted_alpa_count += Presensi::whereIn('id', $chunk)->delete();
            }
        }

        return [
            'success' => true,
            'message' => "Kunci laporan berhasil dibatalkan. Total {$updated_count} presensi direset dendanya, dan {$deleted_alpa_count} presensi alpa dihapus.",
            'updated_count' => $updated_count,
            'deleted_alpa_count' => $deleted_alpa_count
        ];
    }

    public function resolvePeriodeLaporan(Request $request, $generalsetting)
    {
        if ($request->periode_laporan == 1) {
            if ($generalsetting->periode_laporan_next_bulan == 1) {
                if ($request->bulan == 1) {
                    $bulan = 12;
                    $tahun = $request->tahun - 1;
                } else {
                    $bulan = $request->bulan - 1;
                    $tahun = $request->tahun;
                }
            } else {
                $bulan = $request->bulan;
                $tahun = $request->tahun;
            }

            $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
            $periode_dari = $tahun . '-' . $bulan . '-' . $generalsetting->periode_laporan_dari;
            $periode_sampai = $request->tahun . '-' . str_pad($request->bulan, 2, '0', STR_PAD_LEFT) . '-' . $generalsetting->periode_laporan_sampai;
        } else {
            $bulan = str_pad($request->bulan, 2, '0', STR_PAD_LEFT);
            $periode_dari = $request->tahun . '-' . $bulan . '-01';
            $periode_sampai = date('Y-m-t', strtotime($periode_dari));
        }

        return [$periode_dari, $periode_sampai];
    }

    public function applyKaryawanFilters($query, Request $request, $nikColumn = 'karyawan.nik')
    {
        if (!empty($request->kode_cabang)) {
            $query->whereIn('karyawan.kode_cabang', (array) $request->kode_cabang);
        }
        if (!empty($request->kode_dept)) {
            $query->whereIn('karyawan.kode_dept', (array) $request->kode_dept);
        }
        if (!empty($request->sub_departemen)) {
            $query->whereIn('karyawan.sub_departemen', (array) $request->sub_departemen);
        }
        if (!empty($request->nik)) {
            $query->whereIn($nikColumn, (array) $request->nik);
        }
    }

    public function getJadwalMapping($periode_dari, $periode_sampai, $withColor = false)
    {
        $selectCols = [
            'presensi_jamkerja_bydate.nik',
            'presensi_jamkerja_bydate.tanggal',
            'presensi_jamkerja.kode_jam_kerja',
            'presensi_jamkerja.total_jam'
        ];
        if ($withColor) {
            $selectCols = [
                'presensi_jamkerja_bydate.nik',
                'presensi_jamkerja_bydate.tanggal',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            ];
        }

        $jadwal_bydate = DB::table('presensi_jamkerja_bydate')
            ->join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(...$selectCols)
            ->whereBetween('presensi_jamkerja_bydate.tanggal', [$periode_dari, $periode_sampai])
            ->get()
            ->groupBy('nik')
            ->map(function ($rows) use ($withColor) {
                $result = [];
                foreach ($rows as $row) {
                    if ($withColor) {
                        $result[$row->tanggal] = [
                            'nama_jam_kerja' => $row->nama_jam_kerja,
                            'jam_masuk' => $row->jam_masuk,
                            'jam_pulang' => $row->jam_pulang,
                            'color' => $row->color,
                        ];
                    } else {
                        $result[$row->tanggal] = [
                            'kode_jam_kerja' => $row->kode_jam_kerja ?? null,
                            'total_jam' => $row->total_jam ?? null
                        ];
                    }
                }
                return $result;
            });

        $selectGrupCols = [
            'grup_detail.nik',
            'grup_jamkerja_bydate.tanggal',
            'presensi_jamkerja.kode_jam_kerja',
            'presensi_jamkerja.total_jam'
        ];
        if ($withColor) {
            $selectGrupCols = [
                'grup_detail.nik',
                'grup_jamkerja_bydate.tanggal',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            ];
        }

        $jadwal_grup_bydate = DB::table('grup_detail')
            ->join('grup_jamkerja_bydate', 'grup_detail.kode_grup', '=', 'grup_jamkerja_bydate.kode_grup')
            ->join('presensi_jamkerja', 'grup_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(...$selectGrupCols)
            ->whereBetween('grup_jamkerja_bydate.tanggal', [$periode_dari, $periode_sampai])
            ->get()
            ->groupBy('nik')
            ->map(function ($rows) use ($withColor) {
                $result = [];
                foreach ($rows as $row) {
                    if ($withColor) {
                        $result[$row->tanggal] = [
                            'nama_jam_kerja' => $row->nama_jam_kerja,
                            'jam_masuk' => $row->jam_masuk,
                            'jam_pulang' => $row->jam_pulang,
                            'color' => $row->color,
                        ];
                    } else {
                        $result[$row->tanggal] = [
                            'kode_jam_kerja' => $row->kode_jam_kerja ?? null,
                            'total_jam' => $row->total_jam ?? null
                        ];
                    }
                }
                return $result;
            });

        $selectDayCols = [
            'presensi_jamkerja_byday.nik',
            'presensi_jamkerja_byday.hari',
            'presensi_jamkerja.kode_jam_kerja',
            'presensi_jamkerja.total_jam'
        ];
        if ($withColor) {
            $selectDayCols = [
                'presensi_jamkerja_byday.nik',
                'presensi_jamkerja_byday.hari',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            ];
        }

        $jadwal_byday = DB::table('presensi_jamkerja_byday')
            ->join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(...$selectDayCols)
            ->get()
            ->groupBy('nik')
            ->map(function ($rows) use ($withColor) {
                $result = [];
                foreach ($rows as $row) {
                    if ($withColor) {
                        $result[$row->hari] = [
                            'nama_jam_kerja' => $row->nama_jam_kerja,
                            'jam_masuk' => $row->jam_masuk,
                            'jam_pulang' => $row->jam_pulang,
                            'color' => $row->color,
                        ];
                    } else {
                        $result[$row->hari] = [
                            'kode_jam_kerja' => $row->kode_jam_kerja ?? null,
                            'total_jam' => $row->total_jam ?? null
                        ];
                    }
                }
                return $result;
            });

        $selectDeptCols = [
            'presensi_jamkerja_bydept.kode_dept',
            'presensi_jamkerja_bydept.kode_cabang',
            'presensi_jamkerja_bydept_detail.hari',
            'presensi_jamkerja.kode_jam_kerja',
            'presensi_jamkerja.total_jam'
        ];
        if ($withColor) {
            $selectDeptCols = [
                'presensi_jamkerja_bydept.kode_dept',
                'presensi_jamkerja_bydept.kode_cabang',
                'presensi_jamkerja_bydept_detail.hari',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.color'
            ];
        }

        $jadwal_bydept = DB::table('presensi_jamkerja_bydept_detail')
            ->join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
            ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(...$selectDeptCols)
            ->get()
            ->groupBy(function ($row) {
                return $row->kode_dept . '|' . $row->kode_cabang;
            })
            ->map(function ($rows) use ($withColor) {
                $result = [];
                foreach ($rows as $row) {
                    if ($withColor) {
                        $result[$row->hari] = [
                            'nama_jam_kerja' => $row->nama_jam_kerja,
                            'jam_masuk' => $row->jam_masuk,
                            'jam_pulang' => $row->jam_pulang,
                            'color' => $row->color,
                        ];
                    } else {
                        $result[$row->hari] = [
                            'kode_jam_kerja' => $row->kode_jam_kerja ?? null,
                            'total_jam' => $row->total_jam ?? null
                        ];
                    }
                }
                return $result;
            });

        return [
            'bydate' => $jadwal_bydate,
            'byday' => $jadwal_byday,
            'bydept' => $jadwal_bydept
        ];
    }

    public function buildLaporanQuery($periode_dari, $periode_sampai, Request $request, $user, $userkaryawan, &$jenis_tunjangan)
    {
        $presensi_detail  = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->leftJoin('presensi_izinabsen_approve', 'presensi.id', '=', 'presensi_izinabsen_approve.id_presensi')
            ->leftJoin('presensi_izinabsen', 'presensi_izinabsen_approve.kode_izin', '=', 'presensi_izinabsen.kode_izin')
            ->leftJoin('presensi_izinsakit_approve', 'presensi.id', '=', 'presensi_izinsakit_approve.id_presensi')
            ->leftJoin('presensi_izinsakit', 'presensi_izinsakit_approve.kode_izin_sakit', '=', 'presensi_izinsakit.kode_izin_sakit')
            ->leftJoin('presensi_izincuti_approve', 'presensi.id', '=', 'presensi_izincuti_approve.id_presensi')
            ->leftJoin('presensi_izincuti', 'presensi_izincuti_approve.kode_izin_cuti', '=', 'presensi_izincuti.kode_izin_cuti')
            ->select(
                'presensi.*',
                'nama_jam_kerja',
                'jam_masuk',
                'jam_pulang',
                'istirahat',
                'jam_awal_istirahat',
                'jam_akhir_istirahat',
                'lintashari',
                'total_jam',
                'presensi_izinabsen.keterangan as keterangan_izin_absen',
                'presensi_izinsakit.keterangan as keterangan_izin_sakit',
                'presensi_izincuti.keterangan as keterangan_izin_cuti'
            )
            ->whereBetween('presensi.tanggal', [$periode_dari, $periode_sampai]);

        $gaji_pokok = \App\Models\Gajipokok::select(
            'nik',
            'jumlah',
            'jenis_upah'
        )
            ->whereIn('kode_gaji', function ($query) use ($periode_sampai) {
                $query->select(DB::raw('MAX(kode_gaji)'))
                    ->from('karyawan_gaji_pokok')
                    ->where('tanggal_berlaku', '<=', $periode_sampai)
                    ->groupBy('nik');
            });

        $bpjs_kesehatan = \App\Models\Bpjskesehatan::select(
            'nik',
            'jumlah'
        )
            ->whereIn('kode_bpjs_kesehatan', function ($query) use ($periode_sampai) {
                $query->select(DB::raw('MAX(kode_bpjs_kesehatan)'))
                    ->from('karyawan_bpjskesehatan')
                    ->where('tanggal_berlaku', '<=', $periode_sampai)
                    ->groupBy('nik');
            });

        $bpjs_tenagakerja = \App\Models\Bpjstenagakerja::select(
            'nik',
            'jumlah'
        )
            ->whereIn('kode_bpjs_tk', function ($query) use ($periode_sampai) {
                $query->select(DB::raw('MAX(kode_bpjs_tk)'))
                    ->from('karyawan_bpjstenagakerja')
                    ->where('tanggal_berlaku', '<=', $periode_sampai)
                    ->groupBy('nik');
            });

        //Tunjangan
        $jenis_tunjangan = \App\Models\Jenistunjangan::orderBy('kode_jenis_tunjangan')->get();
        $select_tunjangan = [];
        $select_field_tunjangan = [];
        foreach ($jenis_tunjangan as $d) {
            $select_tunjangan[] = DB::raw('SUM(IF(karyawan_tunjangan_detail.kode_jenis_tunjangan = "' . $d->kode_jenis_tunjangan . '", karyawan_tunjangan_detail.jumlah, 0)) as jumlah_' . $d->kode_jenis_tunjangan);
            $select_field_tunjangan[] = 'jumlah_' . $d->kode_jenis_tunjangan;
        }
        $tunjangan = \App\Models\Detailtunjangan::query();
        $tunjangan->join('karyawan_tunjangan', 'karyawan_tunjangan_detail.kode_tunjangan', '=', 'karyawan_tunjangan.kode_tunjangan');
        $tunjangan->select(
            'karyawan_tunjangan.nik',
            ...$select_tunjangan
        );
        $tunjangan->whereIn('karyawan_tunjangan_detail.kode_tunjangan', function ($query) use ($periode_sampai) {
            $query->select(DB::raw('MAX(kode_tunjangan)'))
                ->from('karyawan_tunjangan')
                ->where('tanggal_berlaku', '<=', $periode_sampai)
                ->groupBy('nik');
        });

        $tunjangan->groupBy('karyawan_tunjangan.nik');

        $penyesuaian_gaji = \App\Models\Detailpenyesuaiangaji::select('nik', 'penambah', 'pengurang')
            ->join('karyawan_penyesuaian_gaji', 'karyawan_penyesuaian_gaji_detail.kode_penyesuaian_gaji', '=', 'karyawan_penyesuaian_gaji.kode_penyesuaian_gaji')
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun);

        $q_presensi = Karyawan::query();
        $q_presensi->select(
            'karyawan.nik',
            'karyawan.nik_show',
            'nama_karyawan',
            'karyawan.tanggal_masuk',
            'nama_jabatan',
            'karyawan.kode_dept',
            'nama_dept',
            'karyawan.sub_departemen',
            'karyawan.kode_cabang',
            'cabang.nama_cabang',
            'presensi.tanggal',
            'presensi.status',
            'presensi.kode_jam_kerja',
            'presensi.nama_jam_kerja',
            'presensi.jam_masuk',
            'presensi.jam_pulang',
            'presensi.jam_in',
            'presensi.jam_out',
            'presensi.istirahat',
            'presensi.jam_awal_istirahat',
            'presensi.jam_akhir_istirahat',
            'presensi.lintashari',
            'presensi.keterangan_izin_absen',
            'presensi.keterangan_izin_sakit',
            'presensi.keterangan_izin_cuti',
            'presensi.total_jam',
            'presensi.denda',
            'presensi.status_potongan',
            'gaji_pokok.jumlah as gaji_pokok',
            'gaji_pokok.jenis_upah',
            'bpjs_kesehatan.jumlah as bpjs_kesehatan',
            'bpjs_tenagakerja.jumlah as bpjs_tenagakerja',
            'penambah',
            'pengurang',
            ...$select_field_tunjangan
        );
        $q_presensi->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan');
        $q_presensi->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        $q_presensi->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang');
        $q_presensi->leftJoinSub($presensi_detail, 'presensi', function ($join) {
            $join->on('karyawan.nik', '=', 'presensi.nik');
        });
        $q_presensi->leftJoinSub($gaji_pokok, 'gaji_pokok', function ($join) {
            $join->on('karyawan.nik', '=', 'gaji_pokok.nik');
        });

        $q_presensi->leftJoinSub($bpjs_kesehatan, 'bpjs_kesehatan', function ($join) {
            $join->on('karyawan.nik', '=', 'bpjs_kesehatan.nik');
        });

        $q_presensi->leftJoinSub($bpjs_tenagakerja, 'bpjs_tenagakerja', function ($join) {
            $join->on('karyawan.nik', '=', 'bpjs_tenagakerja.nik');
        });

        $q_presensi->leftJoinSub($tunjangan, 'tunjangan', function ($join) {
            $join->on('karyawan.nik', '=', 'tunjangan.nik');
        });

        $q_presensi->leftJoinSub($penyesuaian_gaji, 'penyesuaian_gaji', function ($join) {
            $join->on('karyawan.nik', '=', 'penyesuaian_gaji.nik');
        });

        $this->applyKaryawanFilters($q_presensi, $request, 'karyawan.nik');

        // Jangan filter jenis_upah untuk Laporan Presensi (format 1) — bisa bikin data kosong
        // kalau karyawan belum punya data gaji_pokok
        if (!empty($request->jenis_upah) && $request->format_laporan != 1) {
            $q_presensi->where('gaji_pokok.jenis_upah', $request->jenis_upah);
        }

        if ($user->hasRole('karyawan') && $userkaryawan) {
            $q_presensi->where('karyawan.nik', $userkaryawan->nik);
        }
        $q_presensi->orderBy('karyawan.nama_karyawan');
        $q_presensi->orderBy('presensi.tanggal', 'asc');

        return $q_presensi;
    }

    public function formatLaporanData($presensi, $jenis_tunjangan)
    {
        return $presensi->groupBy('nik')->map(function ($rows) use ($jenis_tunjangan) {
            $data = [
                'nik' => $rows->first()->nik,
                'nik_show' => $rows->first()->nik_show,
                'nama_karyawan' => $rows->first()->nama_karyawan,
                'nama_jabatan' => $rows->first()->nama_jabatan,
                'kode_dept' => $rows->first()->kode_dept,
                'nama_dept' => $rows->first()->nama_dept,
                'sub_departemen' => $rows->first()->sub_departemen,
                'kode_cabang' => $rows->first()->kode_cabang,
                'nama_cabang' => $rows->first()->nama_cabang,
                'tanggal_masuk' => $rows->first()->tanggal_masuk,
                'gaji_pokok' => $rows->first()->gaji_pokok,
                'jenis_upah' => $rows->first()->jenis_upah,
                'bpjs_kesehatan' => $rows->first()->bpjs_kesehatan,
                'bpjs_tenagakerja' => $rows->first()->bpjs_tenagakerja,
                'penambah' => $rows->first()->penambah,
                'pengurang' => $rows->first()->pengurang,
            ];

            foreach ($jenis_tunjangan as $j) {
                $data = [
                    ...$data,
                    $j->kode_jenis_tunjangan => $rows->first()->{"jumlah_" . $j->kode_jenis_tunjangan}
                ];
            }

            foreach ($rows as $row) {
                $data[$row->tanggal] = [
                    'status' => $row->status,
                    'kode_jam_kerja' => $row->kode_jam_kerja,
                    'nama_jam_kerja' => $row->nama_jam_kerja,
                    'jam_masuk' => $row->jam_masuk,
                    'jam_pulang' => $row->jam_pulang,
                    'jam_in' => $row->jam_in,
                    'jam_out' => $row->jam_out,
                    'istirahat' => $row->istirahat,
                    'jam_awal_istirahat' => $row->jam_awal_istirahat,
                    'jam_akhir_istirahat' => $row->jam_akhir_istirahat,
                    'lintashari' => $row->lintashari,
                    'keterangan_izin_absen' => $row->keterangan_izin_absen,
                    'keterangan_izin_sakit' => $row->keterangan_izin_sakit,
                    'keterangan_izin_cuti' => $row->keterangan_izin_cuti,
                    'total_jam' => $row->total_jam,
                    'denda' => $row->denda ?? null,
                    'status_potongan' => $row->status_potongan ?? null
                ];
            }
            return $data;
        });
    }
}

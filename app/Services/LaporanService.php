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
            'grup_bydate' => $jadwal_grup_bydate,
            'byday' => $jadwal_byday,
            'bydept' => $jadwal_bydept
        ];
    }
}

<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\Userkaryawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresensiController extends Controller
{
    public function presensi(Request $request)
    {
        $user         = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();

        $bulan = $request->bulan ?? date('Y-m');
        [$tahun, $bln] = explode('-', $bulan);

        $list = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('presensi.nik', $userkaryawan->nik)
            ->whereYear('presensi.tanggal', $tahun)
            ->whereMonth('presensi.tanggal', $bln)
            ->leftJoin('presensi_izinabsen_approve', 'presensi.id', '=', 'presensi_izinabsen_approve.id_presensi')
            ->leftJoin('presensi_izinabsen', 'presensi_izinabsen_approve.kode_izin', '=', 'presensi_izinabsen.kode_izin')
            ->leftJoin('presensi_izinsakit_approve', 'presensi.id', '=', 'presensi_izinsakit_approve.id_presensi')
            ->leftJoin('presensi_izinsakit', 'presensi_izinsakit_approve.kode_izin_sakit', '=', 'presensi_izinsakit.kode_izin_sakit')
            ->leftJoin('presensi_izincuti_approve', 'presensi.id', '=', 'presensi_izincuti_approve.id_presensi')
            ->leftJoin('presensi_izincuti', 'presensi_izincuti_approve.kode_izin_cuti', '=', 'presensi_izincuti.kode_izin_cuti')
            ->select(
                'presensi.*',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.total_jam',
                'presensi_jamkerja.lintashari',
                'presensi_izinabsen.keterangan as keterangan_izin',
                'presensi_izinsakit.keterangan as keterangan_izin_sakit',
                'presensi_izincuti.keterangan as keterangan_izin_cuti',
            )
            ->orderBy('presensi.tanggal', 'desc')
            ->get()
            ->map(function ($d) {
                return [
                    'tanggal'         => $d->tanggal,
                    'status'          => $d->status,
                    'jam_in'          => $d->jam_in  ? date('H:i', strtotime($d->jam_in))  : null,
                    'jam_out'         => $d->jam_out ? date('H:i', strtotime($d->jam_out)) : null,
                    'jam_masuk'       => date('H:i', strtotime($d->jam_masuk)),
                    'jam_pulang'      => date('H:i', strtotime($d->jam_pulang)),
                    'nama_jam_kerja'  => $d->nama_jam_kerja,
                    'keterangan_izin' => $d->keterangan_izin ?? $d->keterangan_izin_sakit ?? $d->keterangan_izin_cuti,
                    'foto_in'         => $d->foto_in,
                    'foto_out'        => $d->foto_out,
                    'denda'           => $d->denda,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $list,
        ]);
    }

    public function rekap(Request $request)
    {
        $user         = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();

        $bulan = $request->bulan ?? date('Y-m');
        [$tahun, $bln] = explode('-', $bulan);

        $rekap = Presensi::select(
            DB::raw("SUM(IF(status='h',1,0)) as hadir"),
            DB::raw("SUM(IF(status='i',1,0)) as izin"),
            DB::raw("SUM(IF(status='s',1,0)) as sakit"),
            DB::raw("SUM(IF(status='a',1,0)) as alpa"),
            DB::raw("SUM(IF(status='c',1,0)) as cuti")
        )
            ->where('nik', $userkaryawan->nik)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'bulan' => Carbon::createFromDate($tahun, $bln)->translatedFormat('F Y'),
                'hadir' => (int) ($rekap->hadir ?? 0),
                'izin'  => (int) ($rekap->izin  ?? 0),
                'sakit' => (int) ($rekap->sakit  ?? 0),
                'alpa'  => (int) ($rekap->alpa  ?? 0),
                'cuti'  => (int) ($rekap->cuti  ?? 0),
            ],
        ]);
    }
}

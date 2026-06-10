<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use KaryawanApiHelper;

    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $hari_ini = Carbon::now(config('app.timezone'))->format('Y-m-d');
        
        $dashboardData = $this->dashboardService->getKaryawanData($user);

        $presensi = $dashboardData['presensi'] ?? null;
        $rekap    = $dashboardData['rekappresensi'] ?? null;
        $pengumuman = $dashboardData['pengumuman'] ?? null;

        return response()->json([
            'success' => true,
            'data' => [
                'karyawan'      => $this->formatKaryawan($dashboardData['karyawan'] ?? null),
                'presensi_hari_ini' => $presensi ? [
                    'jam_in'  => $presensi->jam_in  ? date('H:i', strtotime($presensi->jam_in))  : null,
                    'jam_out' => $presensi->jam_out ? date('H:i', strtotime($presensi->jam_out)) : null,
                    'foto_in'  => $presensi->foto_in,
                    'foto_out' => $presensi->foto_out,
                    'status'   => $presensi->status,
                ] : null,
                'rekap_bulan_ini' => [
                    'hadir' => (int) ($rekap->hadir ?? 0),
                    'izin'  => (int) ($rekap->izin  ?? 0),
                    'sakit' => (int) ($rekap->sakit  ?? 0),
                    'alpa'  => (int) ($rekap->alpa  ?? 0),
                    'cuti'  => (int) ($rekap->cuti  ?? 0),
                    'bulan' => Carbon::parse($hari_ini)->translatedFormat('F Y'),
                ],
                'lembur' => [
                    'hide'         => $dashboardData['hideLembur'] ?? false,
                    'notif_count'  => $dashboardData['notiflembur'] ?? 0,
                ],
                'notif_kontrak' => $dashboardData['notif_kontrak'] ?? null,
                'is_birthday'   => $dashboardData['is_birthday'] ?? false,
                'umur'          => $dashboardData['umur'] ?? null,
                'pengumuman'    => $pengumuman ? [
                    'judul'      => $pengumuman->judul,
                    'isi'        => strip_tags($pengumuman->isi),
                    'created_at' => Carbon::parse($pengumuman->created_at)->translatedFormat('d F Y'),
                ] : null,
            ],
        ]);
    }
}

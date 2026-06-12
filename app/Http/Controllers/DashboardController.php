<?php

namespace App\Http\Controllers;

use App\Charts\JeniskelaminkaryawanChart;
use App\Charts\PendidikankaryawanChart;
use App\Charts\StatusKaryawanChart;
use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Denda;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\Pengumuman;
use App\Models\User;
use App\Models\Userkaryawan;
use App\Models\Pengaturanumum;
use App\Http\Controllers\KaryawanApprovalController;
use App\Jobs\SendWaMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(\App\Services\DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(StatusKaryawanChart $chart, JeniskelaminkaryawanChart $jkchart, PendidikankaryawanChart $pddchart, Request $request)
    {
        $agent = new Agent();
        $user = auth()->user();

        if ($user->hasRole('karyawan')) {
            $data = $this->dashboardService->getKaryawanData($user);
            return view('dashboard.karyawan', $data);
        } else {
            // Dashboard Admin
            $data = $this->dashboardService->getAdminData($user, $request, $chart, $jkchart, $pddchart);
            return view('dashboard.dashboard', $data);
        }
    }

    public function kirimUcapanBirthday(Request $request)
    {
        try {
            $count = $this->dashboardService->processKirimUcapanBirthday($request);

            if ($count === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada karyawan yang ulang tahun hari ini atau tidak ada nomor HP yang tersedia.'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => "Ucapan ulang tahun sedang dikirim ke {$count} karyawan."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}

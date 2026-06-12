<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\Pengaturanumum;
use App\Models\Presensi;
use App\Models\Jamkerja;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use App\Models\Detailsetjamkerjabydept;
use App\Jobs\SendWaMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublicPresensiController extends Controller
{
    protected $presensiService;

    public function __construct(\App\Services\PresensiService $presensiService)
    {
        $this->presensiService = $presensiService;
    }
    public function index()
    {
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $scheme = $generalsetting->mobile_theme_scheme ?? 'green';
        
        $colors = [
            'green' => ['primary' => '#32745e', 'rgb' => '50, 116, 94', 'secondary' => '#283ebe', 'bg_gradient' => 'linear-gradient(135deg, #064e3b 0%, #065f46 100%)'],
            'blue' => ['primary' => '#0d47a1', 'rgb' => '13, 71, 161', 'secondary' => '#1976d2', 'bg_gradient' => 'linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%)'],
            'red' => ['primary' => '#b71c1c', 'rgb' => '183, 28, 28', 'secondary' => '#d32f2f', 'bg_gradient' => 'linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%)'],
            'purple' => ['primary' => '#4a148c', 'rgb' => '74, 20, 140', 'secondary' => '#7b1fa2', 'bg_gradient' => 'linear-gradient(135deg, #4c1d95 0%, #5b21b6 100%)'],
            'orange' => ['primary' => '#e65100', 'rgb' => '230, 81, 0', 'secondary' => '#f57c00', 'bg_gradient' => 'linear-gradient(135deg, #7c2d12 0%, #9a3412 100%)'],
            'dark' => ['primary' => '#bb86fc', 'rgb' => '187, 134, 252', 'secondary' => '#cf6679', 'bg_gradient' => 'linear-gradient(135deg, #121212 0%, #1e1e1e 100%)'],
        ];

        $active_colors = $colors[$scheme] ?? $colors['green'];

        return view('presensi.public_kiosk', [
            'generalsetting' => $generalsetting,
            'active_colors' => $active_colors
        ]);
    }

    public function checkRfid(Request $request)
    {
        $result = $this->presensiService->checkRfidKiosk($request);
        
        // If status is error, the original code returned 200 OK with status='error'
        return response()->json($result, 200);
    }

    public function store(Request $request)
    {
        $result = $this->presensiService->prosesPresensiKiosk($request);
        
        return response()->json($result, 200);
    }
}

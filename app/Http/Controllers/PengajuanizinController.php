<?php

namespace App\Http\Controllers;

use App\Models\Izinabsen;
use App\Models\Izincuti;
use App\Models\Izindinas;
use App\Models\Izinsakit;
use App\Models\ApprovalLayer;
use App\Models\User;
use App\Models\Userkaryawan;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengajuanizinController extends Controller
{
    public function index()
    {
        $user = User::where('id', auth()->user()->id)->first();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        
        // Cek departemen karyawan
        $hideCuti = false;
        if ($userkaryawan) {
            $karyawan = \App\Models\Karyawan::where('nik', $userkaryawan->nik)->first();
            if ($karyawan && $karyawan->kode_dept == 'BU') {
                $hideCuti = true;
            }
        }

        $izinabsen = Izinabsen::where('nik', $userkaryawan->nik)
            ->select('kode_izin as kode', 'tanggal', 'keterangan', 'dari', 'sampai', DB::raw('\'i\' as ket'), 'status as status_izin', 'approval_step');

        $izinsakit = Izinsakit::where('nik', $userkaryawan->nik)
            ->select('kode_izin_sakit as kode', 'tanggal', 'keterangan', 'dari', 'sampai', DB::raw('\'s\' as ket'), 'status as status_izin', 'approval_step');

        $izincuti = Izincuti::where('nik', $userkaryawan->nik)
            ->select('kode_izin_cuti as kode', 'tanggal', 'keterangan', 'dari', 'sampai', DB::raw('\'c\' as ket'), 'status as status_izin', 'approval_step');

        $izin_dinas = Izindinas::where('nik', $userkaryawan->nik)
            ->select('kode_izin_dinas as kode', 'tanggal', 'keterangan', 'dari', 'sampai', DB::raw('\'d\' as ket'), 'status as status_izin', 'approval_step');

        // Gabungkan query, exclude cuti jika departemen BU
        $pengajuan_izin = $izinabsen->union($izinsakit);
        if (!$hideCuti) {
            $pengajuan_izin = $pengajuan_izin->union($izincuti);
        }
        $pengajuan_izin = $pengajuan_izin->union($izin_dinas)->orderBy('tanggal', 'desc')->get();

        // Resolve nama approver yang sedang menunggu (untuk status pending)
        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();
        $approvalLayers = ApprovalLayer::where('feature', 'IZIN')->get(); // Ambil sekali (Fix N+1)
        
        foreach ($pengajuan_izin as $item) {
            $item->waiting_role = null;
            if ($item->status_izin == 0 && $item->approval_step) {
                $layer = $approvalLayers->where('level', $item->approval_step)
                    ->filter(function ($l) use ($karyawan) {
                        return $l->kode_dept === $karyawan->kode_dept || is_null($l->kode_dept);
                    })
                    ->sortBy(function ($l) {
                        return !is_null($l->kode_dept) ? 0 : 1;
                    })
                    ->first();
                $item->waiting_role = $layer?->role_name;
            }
        }
        
        $data['pengajuan_izin'] = $pengajuan_izin;
        $data['hideCuti'] = $hideCuti; // Kirim ke view jika diperlukan
        return view('pengajuanizin.index', $data);
    }
}

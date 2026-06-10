<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Izinabsen;
use App\Models\Izincuti;
use App\Models\Izindinas;
use App\Models\Izinsakit;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IzinController extends Controller
{
    public function pengajuanIzin(Request $request)
    {
        $user         = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $nik          = $userkaryawan->nik;

        $izinabsen = Izinabsen::where('nik', $nik)
            ->select('kode_izin as kode', 'tanggal', 'keterangan', 'dari', 'sampai',
                DB::raw("'Izin Absen' as jenis"), 'status as status_izin', 'approval_step');

        $izinsakit = Izinsakit::where('nik', $nik)
            ->select('kode_izin_sakit as kode', 'tanggal', 'keterangan', 'dari', 'sampai',
                DB::raw("'Izin Sakit' as jenis"), 'status as status_izin', 'approval_step');

        $izincuti = Izincuti::where('nik', $nik)
            ->select('kode_izin_cuti as kode', 'tanggal', 'keterangan', 'dari', 'sampai',
                DB::raw("'Cuti' as jenis"), 'status as status_izin', 'approval_step');

        $izindinas = Izindinas::where('nik', $nik)
            ->select('kode_izin_dinas as kode', 'tanggal', 'keterangan', 'dari', 'sampai',
                DB::raw("'Izin Dinas' as jenis"), 'status as status_izin', 'approval_step');

        $query = $izinabsen->union($izinsakit)->union($izincuti)->union($izindinas)
            ->orderBy('tanggal', 'desc');

        if ($request->has('status') && $request->status !== '') {
            $query = DB::table(DB::raw("({$query->toSql()}) as izin"))
                ->mergeBindings($query->getQuery())
                ->where('status_izin', $request->status)
                ->orderBy('tanggal', 'desc');
        }

        $list = DB::table(DB::raw("({$izinabsen->union($izinsakit)->union($izincuti)->union($izindinas)->orderBy('tanggal', 'desc')->toSql()}) as izin"))
            ->mergeBindings($izinabsen->union($izinsakit)->union($izincuti)->union($izindinas)->getQuery())
            ->when($request->has('status') && $request->status !== '', function ($q) use ($request) {
                $q->where('status_izin', $request->status);
            })
            ->orderBy('tanggal', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $list,
        ]);
    }
}

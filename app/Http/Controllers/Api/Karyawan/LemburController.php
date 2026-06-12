<?php

namespace App\Http\Controllers\Api\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Lembur;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;

class LemburController extends Controller
{
    public function lembur(Request $request)
    {
        $user         = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();

        $query = Lembur::where('lembur.nik', $userkaryawan->nik)
            ->orderBy('lembur.tanggal', 'desc');

        if ($request->dari && $request->sampai) {
            $query->whereBetween('tanggal', [$request->dari, $request->sampai]);
        }

        $list = $query->paginate(15)->through(function ($d) {
            $start    = strtotime($d->lembur_mulai);
            $end      = strtotime($d->lembur_selesai);
            $diff     = $end - $start;
            $hours    = floor($diff / 3600);
            $minutes  = floor(($diff % 3600) / 60);

            return [
                'id'            => $d->id,
                'tanggal'       => $d->tanggal,
                'keterangan'    => $d->keterangan,
                'lembur_mulai'  => date('H:i', strtotime($d->lembur_mulai)),
                'lembur_selesai'=> date('H:i', strtotime($d->lembur_selesai)),
                'durasi'        => $hours . 'j' . ($minutes > 0 ? ' ' . $minutes . 'm' : ''),
                'lembur_in'     => $d->lembur_in  ? date('H:i', strtotime($d->lembur_in))  : null,
                'lembur_out'    => $d->lembur_out ? date('H:i', strtotime($d->lembur_out)) : null,
                'status'        => (int) $d->status,
                'approval_step' => $d->approval_step,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $list,
        ]);
    }
}

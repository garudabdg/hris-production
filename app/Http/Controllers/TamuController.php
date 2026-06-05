<?php

namespace App\Http\Controllers;

use App\Models\Tamu;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;

class TamuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        
        $tamus = Tamu::whereDate('tanggal_bertamu', $tanggal)
            ->orderBy('jam_in', 'desc')
            ->get();

        $user = auth()->user();
        $query = \App\Models\Karyawan::query();
        
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            if (!empty($userCabangs)) {
                $query->whereIn('kode_cabang', $userCabangs);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        
        $karyawans = $query->orderBy('nama_karyawan')->get();

        return view('tamu.index', compact('tamus', 'tanggal', 'karyawans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_tamu' => 'required|string|max:255',
            'tujuan' => 'required|string|max:255',
            'keperluan' => 'required|string',
        ]);

        Tamu::create([
            'nama_tamu' => $request->nama_tamu,
            'tanggal_bertamu' => Carbon::today()->format('Y-m-d'),
            'jam_in' => Carbon::now()->format('H:i:s'),
            'tujuan' => $request->tujuan,
            'keperluan' => $request->keperluan,
            'jam_out' => null,
        ]);

        return Redirect::back()->with(['success' => 'Data tamu berhasil ditambahkan']);
    }

    /**
     * Update the jam_out for the specified resource.
     */
    public function updateOut($id)
    {
        $tamu = Tamu::findOrFail($id);
        
        if (!$tamu->jam_out) {
            $tamu->update([
                'jam_out' => Carbon::now()->format('H:i:s')
            ]);
            return Redirect::back()->with(['success' => 'Berhasil set jam keluar tamu']);
        }

        return Redirect::back()->with(['warning' => 'Tamu sudah memiliki jam keluar']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tamu = Tamu::findOrFail($id);
        $tamu->delete();

        return Redirect::back()->with(['success' => 'Data tamu berhasil dihapus']);
    }
}

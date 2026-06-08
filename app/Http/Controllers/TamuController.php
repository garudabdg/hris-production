<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Tamu;
use App\Models\Pengaturanumum;
use App\Exports\TamuExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
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

    public function exportExcel(Request $request)
    {
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $tamus = Tamu::whereDate('created_at', $tanggal)->orderBy('created_at', 'asc')->get();
        $generalsetting = Pengaturanumum::first();
        
        $user = Auth::user();
        $cabang = null;
        if (method_exists($user, 'getCabang')) {
            $cabangList = $user->getCabang();
            if ($cabangList->count() > 0) {
                $cabang = $cabangList->first();
            }
        }

        return Excel::download(new TamuExport($tamus, $tanggal, $generalsetting, $cabang), 'Data_Tamu_' . $tanggal . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $tanggal = $request->tanggal ?? date('Y-m-d');
        $tamus = Tamu::whereDate('created_at', $tanggal)->orderBy('created_at', 'asc')->get();
        $generalsetting = Pengaturanumum::first();
        
        $user = Auth::user();
        $cabang = null;
        if (method_exists($user, 'getCabang')) {
            $cabangList = $user->getCabang();
            if ($cabangList->count() > 0) {
                // If superadmin it might return all cabangs, we just pick first or null, but user requested branch logo and address. Usually they are filtered by user's branch.
                $cabang = $cabangList->first();
            }
        }

        $data = [
            'tamus' => $tamus,
            'tanggal' => $tanggal,
            'generalsetting' => $generalsetting,
            'cabang' => $cabang
        ];

        $pdf = Pdf::loadView('tamu.export_pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download('Data_Tamu_' . $tanggal . '.pdf');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_tamu' => 'required|string|max:255',
            'no_telp' => 'required|string|max:50',
            'plat_nomor' => 'nullable|string|max:50',
            'tujuan' => 'required|string|max:255',
            'keperluan' => 'required|string',
            'foto_wajah' => 'required|string',
            'foto_ktp' => 'required|string',
        ]);

        $foto_wajah_path = null;
        if ($request->foto_wajah) {
            if (strpos($request->foto_wajah, ';base64,') !== false) {
                $image_parts = explode(";base64,", $request->foto_wajah);
                if (count($image_parts) == 2) {
                    $image_base64 = base64_decode($image_parts[1]);
                    $fileName = 'wajah_' . time() . '_' . uniqid() . '.jpg';
                    \Illuminate\Support\Facades\Storage::disk('public')->put('tamu/' . $fileName, $image_base64);
                    $foto_wajah_path = 'tamu/' . $fileName;
                }
            } else {
                $foto_wajah_path = $request->foto_wajah;
            }
        }

        $foto_ktp_path = null;
        if ($request->foto_ktp) {
            if (strpos($request->foto_ktp, ';base64,') !== false) {
                $image_parts = explode(";base64,", $request->foto_ktp);
                if (count($image_parts) == 2) {
                    $image_base64 = base64_decode($image_parts[1]);
                    $fileName = 'ktp_' . time() . '_' . uniqid() . '.jpg';
                    \Illuminate\Support\Facades\Storage::disk('public')->put('tamu/' . $fileName, $image_base64);
                    $foto_ktp_path = 'tamu/' . $fileName;
                }
            } else {
                $foto_ktp_path = $request->foto_ktp;
            }
        }

        Tamu::create([
            'nama_tamu' => $request->nama_tamu,
            'no_telp' => $request->no_telp,
            'plat_nomor' => $request->plat_nomor,
            'foto_wajah' => $foto_wajah_path,
            'foto_ktp' => $foto_ktp_path,
            'tanggal_bertamu' => Carbon::today()->format('Y-m-d'),
            'jam_in' => Carbon::now()->format('H:i:s'),
            'tujuan' => $request->tujuan,
            'keperluan' => $request->keperluan,
            'jam_out' => null,
        ]);

        return Redirect::back()->with(['success' => 'Data tamu berhasil ditambahkan']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $tamu = Tamu::findOrFail($id);

        $request->validate([
            'nama_tamu' => 'required|string|max:255',
            'no_telp' => 'required|string|max:50',
            'plat_nomor' => 'nullable|string|max:50',
            'tujuan' => 'required|string|max:255',
            'keperluan' => 'required|string',
        ]);

        $foto_wajah_path = $tamu->foto_wajah;
        if ($request->foto_wajah && strpos($request->foto_wajah, ';base64,') !== false) {
            $image_parts = explode(";base64,", $request->foto_wajah);
            if (count($image_parts) == 2) {
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = 'wajah_' . time() . '_' . uniqid() . '.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put('tamu/' . $fileName, $image_base64);
                $foto_wajah_path = 'tamu/' . $fileName;
            }
        } elseif ($request->foto_wajah && strpos($request->foto_wajah, 'tamu/') !== false) {
            $foto_wajah_path = $request->foto_wajah;
        }

        $foto_ktp_path = $tamu->foto_ktp;
        if ($request->foto_ktp && strpos($request->foto_ktp, ';base64,') !== false) {
            $image_parts = explode(";base64,", $request->foto_ktp);
            if (count($image_parts) == 2) {
                $image_base64 = base64_decode($image_parts[1]);
                $fileName = 'ktp_' . time() . '_' . uniqid() . '.jpg';
                \Illuminate\Support\Facades\Storage::disk('public')->put('tamu/' . $fileName, $image_base64);
                $foto_ktp_path = 'tamu/' . $fileName;
            }
        } elseif ($request->foto_ktp && strpos($request->foto_ktp, 'tamu/') !== false) {
            $foto_ktp_path = $request->foto_ktp;
        }

        $tamu->update([
            'nama_tamu' => $request->nama_tamu,
            'no_telp' => $request->no_telp,
            'plat_nomor' => $request->plat_nomor,
            'foto_wajah' => $foto_wajah_path,
            'foto_ktp' => $foto_ktp_path,
            'tujuan' => $request->tujuan,
            'keperluan' => $request->keperluan,
        ]);

        return Redirect::back()->with(['success' => 'Data tamu berhasil diperbarui']);
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

    /**
     * Search guest by name or phone for autocomplete.
     */
    public function search(Request $request)
    {
        $search = $request->q;
        $tamus = Tamu::select('nama_tamu', 'no_telp', 'plat_nomor', 'foto_wajah', 'foto_ktp')
            ->where('nama_tamu', 'like', "%$search%")
            ->orWhere('no_telp', 'like', "%$search%")
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
            
        $results = [];
        $uniquePhones = [];
        
        foreach ($tamus as $tamu) {
            if(!in_array($tamu->no_telp, $uniquePhones)) {
                $uniquePhones[] = $tamu->no_telp;
                $results[] = [
                    'id' => $tamu->nama_tamu,
                    'text' => $tamu->nama_tamu . ' (' . $tamu->no_telp . ')',
                    'nama_tamu' => $tamu->nama_tamu,
                    'no_telp' => $tamu->no_telp,
                    'plat_nomor' => $tamu->plat_nomor,
                    'foto_wajah' => $tamu->foto_wajah,
                    'foto_ktp' => $tamu->foto_ktp,
                ];
            }
        }

        return response()->json($results);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PenerimaanTelepon;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;

class PenerimaanTeleponController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        
        $telepons = PenerimaanTelepon::whereDate('tanggal', $tanggal)
            ->orderBy('waktu', 'desc')
            ->get();

        $user = auth()->user();
        $query = Karyawan::query();
        
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            if (!empty($userCabangs)) {
                $query->whereIn('kode_cabang', $userCabangs);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        
        $karyawans = $query->with('departemen')->orderBy('nama_karyawan')->get();

        return view('penerimaan-telepon.index', compact('telepons', 'tanggal', 'karyawans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_penelpon' => 'required|string|max:255',
            'nama_perusahaan' => 'nullable|string|max:255',
            'no_telp' => 'required|string|max:50',
            'tujuan' => 'required|string|max:255',
            'departemen' => 'required|string|max:255',
            'keperluan' => 'required|string',
            'tindak_lanjut' => 'required|string',
            'pesan' => 'nullable|string',
        ]);

        PenerimaanTelepon::create([
            'tanggal' => Carbon::today()->format('Y-m-d'),
            'waktu' => Carbon::now()->format('H:i:s'),
            'nama_penelpon' => $request->nama_penelpon,
            'nama_perusahaan' => $request->nama_perusahaan,
            'no_telp' => $request->no_telp,
            'tujuan' => $request->tujuan,
            'departemen' => $request->departemen,
            'keperluan' => $request->keperluan,
            'tindak_lanjut' => $request->tindak_lanjut,
            'pesan' => $request->pesan,
        ]);

        return Redirect::back()->with(['success' => 'Data penerimaan telepon berhasil ditambahkan']);
    }

    public function update(Request $request, $id)
    {
        $telepon = PenerimaanTelepon::findOrFail($id);

        $request->validate([
            'nama_penelpon' => 'required|string|max:255',
            'nama_perusahaan' => 'nullable|string|max:255',
            'no_telp' => 'required|string|max:50',
            'tujuan' => 'required|string|max:255',
            'departemen' => 'required|string|max:255',
            'keperluan' => 'required|string',
            'tindak_lanjut' => 'required|string',
            'pesan' => 'nullable|string',
        ]);

        $telepon->update([
            'nama_penelpon' => $request->nama_penelpon,
            'nama_perusahaan' => $request->nama_perusahaan,
            'no_telp' => $request->no_telp,
            'tujuan' => $request->tujuan,
            'departemen' => $request->departemen,
            'keperluan' => $request->keperluan,
            'tindak_lanjut' => $request->tindak_lanjut,
            'pesan' => $request->pesan,
        ]);

        return Redirect::back()->with(['success' => 'Data penerimaan telepon berhasil diperbarui']);
    }

    public function destroy($id)
    {
        $telepon = PenerimaanTelepon::findOrFail($id);
        $telepon->delete();

        return Redirect::back()->with(['success' => 'Data penerimaan telepon berhasil dihapus']);
    }
}

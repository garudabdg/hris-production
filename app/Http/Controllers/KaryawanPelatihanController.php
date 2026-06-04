<?php

namespace App\Http\Controllers;

use App\Models\KaryawanPelatihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KaryawanPelatihanController extends Controller
{
    public function index(Request $request)
    {
        $query = KaryawanPelatihan::with('karyawan');

        if (filled($request->nama_karyawan)) {
            $keyword = $request->nama_karyawan;
            $query->whereHas('karyawan', function($q) use ($keyword) {
                $q->where('nama_karyawan', 'like', '%' . $keyword . '%')
                  ->orWhere('nik', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $now = \Carbon\Carbon::now()->format('Y-m-d');
            if ($request->status == 'active') {
                $query->where(function($q) use ($now) {
                    $q->whereNull('tanggal_expired')
                      ->orWhere('tanggal_expired', '>=', $now);
                });
            } elseif ($request->status == 'expired') {
                $query->whereNotNull('tanggal_expired')
                      ->where('tanggal_expired', '<', $now);
            }
        }

        $pelatihan = $query->orderBy('tanggal_pelatihan', 'desc')->paginate(15);
        $pelatihan->appends($request->all());

        $karyawan = \App\Models\Karyawan::orderBy('nama_karyawan')->get();

        return view('datamaster.pelatihan.index', compact('pelatihan', 'karyawan'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'nama_pelatihan' => 'required|string|max:255',
            'tanggal_pelatihan' => 'required|date',
            'tanggal_expired' => 'nullable|date',
            'file_sertifikat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except(['_token', 'file_sertifikat']);

        if ($request->hasFile('file_sertifikat')) {
            $file = $request->file('file_sertifikat');
            $fileName = $request->nik . '_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('public/uploads/pelatihan', $fileName);
            $data['file_sertifikat'] = $fileName;
        }

        KaryawanPelatihan::create($data);

        return redirect()->back()->with('success', 'Data Pelatihan/Sertifikat berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $pelatihan = KaryawanPelatihan::findOrFail($id);
        return view('datamaster.pelatihan.edit', compact('pelatihan'));
    }

    public function update(Request $request, $id)
    {
        $pelatihan = KaryawanPelatihan::findOrFail($id);

        $request->validate([
            'nama_pelatihan' => 'required|string|max:255',
            'tanggal_pelatihan' => 'required|date',
            'tanggal_expired' => 'nullable|date',
            'file_sertifikat' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $data = $request->except(['_token', 'file_sertifikat', '_method']);

        if ($request->hasFile('file_sertifikat')) {
            $file = $request->file('file_sertifikat');
            $fileName = $pelatihan->nik . '_' . time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/uploads/pelatihan', $fileName);
            $data['file_sertifikat'] = $fileName;

            if ($pelatihan->file_sertifikat) {
                Storage::delete('public/uploads/pelatihan/' . $pelatihan->file_sertifikat);
            }
        }

        $pelatihan->update($data);

        return redirect()->back()->with('success', 'Data Pelatihan/Sertifikat berhasil diupdate!');
    }

    public function destroy($id)
    {
        $pelatihan = KaryawanPelatihan::findOrFail($id);
        
        if ($pelatihan->file_sertifikat) {
            Storage::delete('public/uploads/pelatihan/' . $pelatihan->file_sertifikat);
        }

        $pelatihan->delete();

        return redirect()->back()->with('success', 'Data Pelatihan/Sertifikat berhasil dihapus!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\MutasiKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class MutasiKaryawanController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $query = MutasiKaryawan::query();
        $query->with(['karyawan', 'cabangLama', 'cabangBaru', 'deptLama', 'deptBaru', 'jabatanLama', 'jabatanBaru']);

        if (!empty($request->nama_karyawan)) {
            $query->whereHas('karyawan', function($q) use ($request) {
                $q->where('nama_karyawan', 'like', '%' . $request->nama_karyawan . '%');
            });
        }

        if ($request->filled('jenis_mutasi')) {
            $query->where('jenis_mutasi', $request->jenis_mutasi);
        }

        if ($request->filled('kode_cabang')) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_cabang_lama', $request->kode_cabang)
                  ->orWhere('kode_cabang_baru', $request->kode_cabang);
            });
        }

        // Batasi akses: non-super-admin hanya lihat data cabangnya
        if (!$user->isSuperAdmin()) {
            $allowedCabang = $user->getCabangCodes();
            if (!empty($allowedCabang)) {
                $query->whereHas('karyawan', function ($q) use ($allowedCabang) {
                    $q->whereIn('kode_cabang', $allowedCabang);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $query->orderBy('tanggal_mutasi', 'desc');
        $mutasi = $query->paginate(10);
        $mutasi->appends($request->all());

        $karyawan = $user->isSuperAdmin()
            ? Karyawan::orderBy('nama_karyawan')->get()
            : Karyawan::whereIn('kode_cabang', $user->getCabangCodes())->orderBy('nama_karyawan')->get();

        $cabang     = $user->getCabang();
        $departemen = $user->getDepartemen();
        $jabatan    = Jabatan::orderBy('nama_jabatan')->get();

        return view('mutasi.index', compact('mutasi', 'karyawan', 'cabang', 'departemen', 'jabatan'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $karyawan = $user->isSuperAdmin()
            ? Karyawan::orderBy('nama_karyawan')->get()
            : Karyawan::whereIn('kode_cabang', $user->getCabangCodes())->orderBy('nama_karyawan')->get();

        $cabang     = $user->isSuperAdmin() ? Cabang::orderBy('nama_cabang')->get() : $user->getCabang();
        $departemen = $user->isSuperAdmin() ? Departemen::orderBy('nama_dept')->get() : $user->getDepartemen();
        $jabatan    = Jabatan::orderBy('nama_jabatan')->get();

        return view('mutasi.create', compact('karyawan', 'cabang', 'departemen', 'jabatan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'tanggal_mutasi' => 'required|date',
            'jenis_mutasi' => 'required',
            'kode_cabang_baru' => 'required',
            'kode_dept_baru' => 'required',
            'kode_jabatan_baru' => 'required',
            'doc_sk' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $karyawan = Karyawan::find($request->nik);
        if (!$karyawan) {
             return Redirect::back()->with(['warning' => 'Data Karyawan Tidak Ditemukan']);
        }

        DB::beginTransaction();
        try {
            $doc_sk = null;
            if ($request->hasFile('doc_sk')) {
                $doc_sk = $request->file('doc_sk')->store('mutasi_sk', 'public');
            }

            MutasiKaryawan::create([
                'nik' => $request->nik,
                'tanggal_mutasi' => $request->tanggal_mutasi,
                'jenis_mutasi' => $request->jenis_mutasi,
                'kode_cabang_lama' => $karyawan->kode_cabang,
                'kode_cabang_baru' => $request->kode_cabang_baru,
                'kode_dept_lama' => $karyawan->kode_dept,
                'kode_dept_baru' => $request->kode_dept_baru,
                'kode_jabatan_lama' => $karyawan->kode_jabatan,
                'kode_jabatan_baru' => $request->kode_jabatan_baru,
                'status_karyawan_lama' => $karyawan->status_karyawan,
                'status_karyawan_baru' => $request->status_karyawan_baru ?? $karyawan->status_karyawan,
                'keterangan' => $request->keterangan,
                'doc_sk' => $doc_sk,
                'user_id' => auth()->id()
            ]);

            // Update Data Karyawan
            $karyawan->update([
                'kode_cabang' => $request->kode_cabang_baru,
                'kode_dept' => $request->kode_dept_baru,
                'kode_jabatan' => $request->kode_jabatan_baru,
                'status_karyawan' => $request->status_karyawan_baru ?? $karyawan->status_karyawan
            ]);

            DB::commit();
            return Redirect::route('mutasi.index')->with(['success' => 'Data Mutasi Berhasil Disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => 'Gagal Menyimpan Data: ' . $e->getMessage()]);
        }
    }
    
    public function getKaryawan($nik)
    {
        $karyawan = Karyawan::with(['cabang', 'departemen', 'jabatan'])->where('nik', $nik)->first();
        return response()->json($karyawan);
    }
    
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $mutasi = MutasiKaryawan::findOrFail($id);
            $karyawan = Karyawan::where('nik', $mutasi->nik)->first();

            // Kembalikan data karyawan ke data lama
            if ($karyawan) {
                $karyawan->update([
                    'kode_cabang' => $mutasi->kode_cabang_lama,
                    'kode_dept' => $mutasi->kode_dept_lama,
                    'kode_jabatan' => $mutasi->kode_jabatan_lama,
                    'status_karyawan' => $mutasi->status_karyawan_lama
                ]);
            }

            if ($mutasi->doc_sk) {
                Storage::disk('public')->delete('uploads/mutasi/' . $mutasi->doc_sk); // Pastikan path sesuai
            }
            
            $mutasi->delete();
            
            DB::commit();
            return Redirect::back()->with(['success' => 'Data Berhasil Dihapus dan Data Karyawan Dikembalikan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(['warning' => 'Gagal Menghapus Data: ' . $e->getMessage()]);
        }
    }
}

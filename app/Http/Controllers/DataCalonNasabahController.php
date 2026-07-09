<?php

namespace App\Http\Controllers;

use App\Models\DataCalonNasabah;
use App\Models\User;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exports\DataCalonNasabahExport;
use Maatwebsite\Excel\Facades\Excel;

class DataCalonNasabahController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = DataCalonNasabah::with('karyawan')->orderBy('tanggal', 'desc')->orderBy('id', 'desc');

        if ($user->hasRole('karyawan')) {
            $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();
            $query->where('nik', $user_karyawan->nik);
        } else {
            if (!$user->isSuperAdmin()) {
                $userCabangs = $user->getCabangCodes();
                $deptMap = $user->getDepartemenAccessMap();

                $query->whereHas('karyawan', function($q) use ($userCabangs, $deptMap) {
                    if (!empty($userCabangs)) {
                        $q->whereIn('kode_cabang', $userCabangs);
                    } else {
                        $q->whereRaw('1 = 0');
                    }

                    if (array_key_exists('BU', $deptMap)) {
                        $q->where('kode_dept', 'BU');
                        if (!empty($deptMap['BU'])) {
                            $q->whereIn('sub_departemen', $deptMap['BU']);
                        }
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                });
            }

            // Admin can filter by NIK
            if ($request->filled('nik')) {
                $query->where('nik', $request->nik);
            }
        }

        $nasabahs = $query->paginate(15)->withQueryString();

        return view('datanasabah.index', compact('nasabahs'));
    }

    public function exportExcel(Request $request)
    {
        $user = auth()->user();
        $query = DataCalonNasabah::with('karyawan')->orderBy('tanggal', 'desc')->orderBy('id', 'desc');

        if ($user->hasRole('karyawan')) {
            $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();
            $query->where('nik', $user_karyawan->nik);
        } else {
            if (!$user->isSuperAdmin()) {
                $userCabangs = $user->getCabangCodes();
                $deptMap = $user->getDepartemenAccessMap();

                $query->whereHas('karyawan', function($q) use ($userCabangs, $deptMap) {
                    if (!empty($userCabangs)) {
                        $q->whereIn('kode_cabang', $userCabangs);
                    } else {
                        $q->whereRaw('1 = 0');
                    }

                    if (array_key_exists('BU', $deptMap)) {
                        $q->where('kode_dept', 'BU');
                        if (!empty($deptMap['BU'])) {
                            $q->whereIn('sub_departemen', $deptMap['BU']);
                        }
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                });
            }

            // Admin can filter by NIK
            if ($request->filled('nik')) {
                $query->where('nik', $request->nik);
            }
        }

        $nasabahs = $query->get();

        return Excel::download(new DataCalonNasabahExport($nasabahs), 'Data_Calon_Nasabah_' . date('Ymd_His') . '.xlsx');
    }

    public function create()
    {
        return view('datanasabah.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();

        $nik = $user->hasRole('karyawan') ? $user_karyawan->nik : $request->nik;

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'nama' => 'required|string|max:255',
            'status_lead' => 'required|in:cold,warm,hot',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DataCalonNasabah::create([
            'nik' => $nik,
            'tanggal' => $request->tanggal,
            'nama' => $request->nama,
            'akun_sosial_media' => $request->akun_sosial_media,
            'no_whatsapp' => $request->no_whatsapp,
            'status_lead' => $request->status_lead,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('data-calon-nasabah.index')->with('success', 'Data calon nasabah berhasil ditambahkan.');
    }

    private function checkAccessToNasabah($user, $nasabah)
    {
        if ($user->hasRole('karyawan')) {
            $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();
            return $nasabah->nik == $user_karyawan->nik;
        }

        if (!$user->isSuperAdmin()) {
            $karyawan = $nasabah->karyawan;
            if (!$karyawan) return false;

            $userCabangs = $user->getCabangCodes();
            $deptMap = $user->getDepartemenAccessMap();

            if (!in_array($karyawan->kode_cabang, $userCabangs)) return false;
            if (!array_key_exists('BU', $deptMap)) return false;
            if ($karyawan->kode_dept != 'BU') return false;

            if (!empty($deptMap['BU'])) {
                if (!in_array($karyawan->sub_departemen, $deptMap['BU'])) {
                    return false;
                }
            }
        }

        return true;
    }

    public function edit($id)
    {
        $nasabah = DataCalonNasabah::findOrFail($id);
        
        if (!$this->checkAccessToNasabah(auth()->user(), $nasabah)) {
            return abort(403);
        }

        return view('datanasabah.edit', compact('nasabah'));
    }

    public function update(Request $request, $id)
    {
        $nasabah = DataCalonNasabah::findOrFail($id);
        
        if (!$this->checkAccessToNasabah(auth()->user(), $nasabah)) {
            return abort(403);
        }

        $validator = Validator::make($request->all(), [
            'tanggal' => 'required|date',
            'nama' => 'required|string|max:255',
            'status_lead' => 'required|in:cold,warm,hot',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $nasabah->update([
            'tanggal' => $request->tanggal,
            'nama' => $request->nama,
            'akun_sosial_media' => $request->akun_sosial_media,
            'no_whatsapp' => $request->no_whatsapp,
            'status_lead' => $request->status_lead,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('data-calon-nasabah.index')->with('success', 'Data calon nasabah berhasil diupdate.');
    }

    public function destroy($id)
    {
        $nasabah = DataCalonNasabah::findOrFail($id);
        
        if (!$this->checkAccessToNasabah(auth()->user(), $nasabah)) {
            return abort(403);
        }

        $nasabah->delete();

        return redirect()->route('data-calon-nasabah.index')->with('success', 'Data calon nasabah berhasil dihapus.');
    }
}

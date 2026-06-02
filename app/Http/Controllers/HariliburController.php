<?php

namespace App\Http\Controllers;

use App\Models\Approveizincuti;
use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Detailharilibur;
use App\Models\Detailsetjamkerjabydept;
use App\Models\Harilibur;
use App\Models\Izincuti;
use App\Models\Karyawan;
use App\Models\Presensi;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class HariliburController extends Controller
{

    public function index(Request $request)
    {
        $data['user'] = User::where('id', '=', auth()->user()->id)->first();
        $query = Harilibur::query();
        $query->join('cabang', 'hari_libur.kode_cabang', '=', 'cabang.kode_cabang');
        if (!empty($request->kode_cabang)) {
            $query->where('hari_libur.kode_cabang', $request->kode_cabang);
        }
        if (!empty($request->dari) && !empty($request->sampai)) {
            $query->whereBetween('tanggal', [$request->dari, $request->sampai]);
        }
        $harilibur = $query->paginate(15);
        $harilibur->appends($request->all());
        $data['harilibur'] = $harilibur;

        $data['cabang'] = Cabang::orderBy('kode_cabang')->get();

        return view('harilibur.index', $data);
    }

    public function create()
    {
        $data['cabang'] = Cabang::orderBy('kode_cabang')->get();
        $data['user'] = User::where('id', '=', auth()->user()->id)->first();
        return view('harilibur.create', $data);
    }

    public function store(Request $request)
    {
        $user = User::findorFail(auth()->user()->id);
        $role = $user->getRoleNames()->first();
        $validationRules = [
            'tanggal' => 'required|date',
            'keterangan' => 'required'
        ];
        if ($user->hasRole(['super admin', 'admin pusat'])) {
            $validationRules['kode_cabang'] = 'required';
        }

        $request->validate($validationRules);

        try {
            $lastharilibur = Harilibur::select('kode_libur')
                ->whereRaw('MID(kode_libur,3,2)="' . date('y', strtotime($request->tanggal)) . '"')
                ->orderBy('kode_libur', 'desc')
                ->first();

            // $lasthariliburLR = Harilibur::select('kode_libur')
            //     ->whereRaw('MID(kode_libur,3,2)="' . date('y', strtotime($request->tanggal)) . '"')
            //     ->whereRaw('LEFT(kode_libur,2)="' . "LR" . '"')
            //     ->orderBy('kode_libur', 'desc')
            //     ->first();
            $last_kode_libur = $lastharilibur != null ? $lastharilibur->kode_libur : '';


            $kode_libur = buatkode($last_kode_libur, "LB" . date('y', strtotime($request->tanggal)), 3);


            if ($user->hasRole(['super admin', 'admin pusat'])) {
                $kode_cabang = $request->kode_cabang;
            } else {
                $firstCabang = $user->cabangs()->first();
                $kode_cabang = $firstCabang ? $firstCabang->kode_cabang : null;
                if (empty($kode_cabang)) {
                    return Redirect::back()->with(messageError('User tidak memiliki akses cabang. Hubungi admin.'));
                }
            }
            Harilibur::create([
                'kode_libur' => $kode_libur,
                'tanggal' => $request->tanggal,
                'kode_cabang' => $kode_cabang,
                'keterangan' => $request->keterangan,
                'is_cuti_bersama' => $request->has('is_cuti_bersama') ? 1 : 0,
            ]);

            return Redirect::back()->with(messageSuccess('Data Harilibur Berhasil Di Tambahkan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }


    public function edit($kode_libur)
    {
        $kode_libur = Crypt::decrypt($kode_libur);
        $data['harilibur'] = Harilibur::where('kode_libur', $kode_libur)->first();
        $data['cabang'] = Cabang::orderBy('kode_cabang')->get();
        $data['user'] = User::where('id', '=', auth()->user()->id)->first();
        return view('harilibur.edit', $data);
    }

    public function update(Request $request, $kode_libur)
    {
        $user = User::findorFail(auth()->user()->id);
        $kode_libur = Crypt::decrypt($kode_libur);
        $validationRules = [
            'tanggal' => 'required|date',
            'keterangan' => 'required'
        ];
        if ($user->hasRole(['super admin', 'admin pusat'])) {
            $validationRules['kode_cabang'] = 'required';
        }

        $request->validate($validationRules);

        try {

            if ($user->hasRole(['super admin', 'admin pusat'])) {
                $kode_cabang = $request->kode_cabang;
            } else {
                $firstCabang = $user->cabangs()->first();
                $kode_cabang = $firstCabang ? $firstCabang->kode_cabang : null;
                if (empty($kode_cabang)) {
                    return Redirect::back()->with(messageError('User tidak memiliki akses cabang. Hubungi admin.'));
                }
            }
            Harilibur::where('kode_libur', $kode_libur)->update([
                'tanggal' => $request->tanggal,
                'kode_cabang' => $kode_cabang,
                'keterangan' => $request->keterangan,
                'is_cuti_bersama' => $request->has('is_cuti_bersama') ? 1 : 0,
            ]);

            return Redirect::back()->with(messageSuccess('Data Harilibur Berhasil Di Tambahkan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function destroy($kode_libur)
    {
        $kode_libur = Crypt::decrypt($kode_libur);
        try {
            Harilibur::where('kode_libur', $kode_libur)->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function aturharilibur($kode_libur)
    {
        $kode_libur = Crypt::decrypt($kode_libur);
        $data['harilibur'] = Harilibur::where('kode_libur', $kode_libur)
            ->join('cabang', 'hari_libur.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        return view('harilibur.aturharilibur', $data);
    }

    public function aturkaryawan($kode_libur)
    {
        $kode_libur = Crypt::decrypt($kode_libur);
        $harilibur = Harilibur::where('kode_libur', $kode_libur)
            ->join('cabang', 'hari_libur.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        $data['departemen'] = Departemen::orderBy('kode_dept')->get();
        $data['harilibur'] = $harilibur;


        return view('harilibur.aturkaryawan', $data);
    }

    function getkaryawan(Request $request)
    {
        $kode_libur = Crypt::decrypt($request->kode_libur);
        $harilibur = Harilibur::where('kode_libur', $kode_libur)
            ->join('cabang', 'hari_libur.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        $data['harilibur'] = $harilibur;

        $query = Karyawan::query();
        $query->select('karyawan.nik', 'karyawan.nik_show', 'karyawan.nama_karyawan', 'harilibur.nik as ceklibur', 'nama_dept');
        $query->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        if (!empty($request->kode_dept)) {
            $kodeDeptArr = is_array($request->kode_dept) ? $request->kode_dept : [$request->kode_dept];
            $query->whereIn('karyawan.kode_dept', $kodeDeptArr);
        }
        $query->where('karyawan.kode_cabang', $harilibur->kode_cabang);


        if (!empty($request->nama_karyawan)) {
            $query->where('nama_karyawan', 'like', '%' . $request->nama_karyawan . '%');
        }

        //left join ke detail hari libur berdasarkan kode libur
        $query->leftJoinSub(
            Detailharilibur::select('nik')->where('kode_libur', $kode_libur),
            'harilibur',
            'karyawan.nik',
            '=',
            'harilibur.nik'
        );
        $query->orderBy('nama_karyawan');
        $data['karyawan'] = $query->get();
        return view('harilibur.getkaryawan', $data);
    }


    public function updateliburkaryawan(Request $request)
    {
        DB::beginTransaction();
        try {
            $harilibur = Harilibur::where('kode_libur', $request->kode_libur)->first();
            $cek = Detailharilibur::where('nik', $request->nik)->where('kode_libur', $request->kode_libur)->first();
            if ($cek != null) {
                // Remove employee from holiday
                if ($harilibur && $harilibur->is_cuti_bersama) {
                    $this->deleteCutiBersamaRecord($request->nik, $harilibur);
                }
                Detailharilibur::where('nik', $request->nik)->where('kode_libur', $request->kode_libur)->delete();
            } else {
                // Add employee to holiday
                Detailharilibur::create([
                    'nik' => $request->nik,
                    'kode_libur' => $request->kode_libur,
                ]);
                if ($harilibur && $harilibur->is_cuti_bersama) {
                    $this->createCutiBersamaRecord($request->nik, $harilibur);
                }
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Update Success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    function getkaryawanlibur($kode_libur)
    {
        $kode_libur = Crypt::decrypt($kode_libur);
        $data['detailharilibur'] = Detailharilibur::join('karyawan', 'hari_libur_detail.nik', '=', 'karyawan.nik')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->select('hari_libur_detail.*', 'karyawan.nik', 'karyawan.nik_show', 'karyawan.nama_karyawan', 'karyawan.kode_dept', 'departemen.nama_dept')
            ->where('kode_libur', $kode_libur)->get();
        return view('harilibur.getkaryawanlibur', $data);
    }


    public function deletekaryawanlibur(Request $request)
    {
        DB::beginTransaction();
        try {
            $harilibur = Harilibur::where('kode_libur', $request->kode_libur)->first();
            if ($harilibur && $harilibur->is_cuti_bersama) {
                $this->deleteCutiBersamaRecord($request->nik, $harilibur);
            }
            Detailharilibur::where('nik', $request->nik)->where('kode_libur', $request->kode_libur)->delete();
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Delete Success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    public function tambahkansemua(Request $request)
    {
        $kode_libur = $request->kode_libur;
        $harilibur = Harilibur::where('kode_libur', $kode_libur)
            ->join('cabang', 'hari_libur.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        $query = Karyawan::query();
        $query->select('karyawan.nik', 'karyawan.nik_show', 'karyawan.nama_karyawan', 'karyawan.kode_dept', 'nama_dept', 'harilibur.nik as ceklibur');


        if (!empty($request->kode_dept)) {
            $kodeDeptArr = is_array($request->kode_dept) ? $request->kode_dept : [$request->kode_dept];
            $query->whereIn('karyawan.kode_dept', $kodeDeptArr);
        }
        $query->where('karyawan.kode_cabang', $harilibur->kode_cabang);
        if (!empty($request->nama_karyawan)) {
            $query->where('nama_karyawan', 'like', '%' . $request->nama_karyawan . '%');
        }
        //left join ke detail hari libur berdasarkan kode libur
        $query->leftJoinSub(
            Detailharilibur::select('nik')->where('kode_libur', $kode_libur),
            'harilibur',
            'karyawan.nik',
            '=',
            'harilibur.nik'
        );
        $query->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        $query->orderBy('nama_karyawan');
        $karyawan = $query->get();

        try {
            DB::beginTransaction();
            // Hanya hapus detail libur untuk karyawan dari departemen yang dipilih
            $nikList = $karyawan->pluck('nik')->toArray();

            // Jika cuti bersama, hapus record izin cuti lama terlebih dahulu
            $hariLiburRecord = Harilibur::where('kode_libur', $kode_libur)->first();
            if ($hariLiburRecord && $hariLiburRecord->is_cuti_bersama && !empty($nikList)) {
                foreach ($nikList as $nik) {
                    $this->deleteCutiBersamaRecord($nik, $hariLiburRecord);
                }
            }

            if (!empty($nikList)) {
                Detailharilibur::where('kode_libur', $request->kode_libur)
                    ->whereIn('nik', $nikList)
                    ->delete();
            }
            $insertData = [];
            foreach ($karyawan as $d) {
                $insertData[] = [
                    'nik' => $d->nik,
                    'kode_libur' => $request->kode_libur,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            // Bulk insert for better performance
            if (!empty($insertData)) {
                Detailharilibur::insert($insertData);
            }

            // Buat record cuti bersama untuk semua karyawan
            if ($hariLiburRecord && $hariLiburRecord->is_cuti_bersama) {
                foreach ($karyawan as $d) {
                    $this->createCutiBersamaRecord($d->nik, $hariLiburRecord);
                }
            }

            DB::commit();

            $message = count($insertData) . ' karyawan berhasil ditambahkan ke hari libur';
            if ($hariLiburRecord && $hariLiburRecord->is_cuti_bersama) {
                $message .= ' (jatah cuti otomatis terpotong)';
            }
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function batalkansemua(Request $request)
    {
        $kode_libur = $request->kode_libur;
        $harilibur = Harilibur::where('kode_libur', $kode_libur)
            ->join('cabang', 'hari_libur.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        $query = Karyawan::query();
        $query->select('karyawan.nik', 'karyawan.nik_show', 'karyawan.nama_karyawan', 'karyawan.kode_dept', 'nama_dept', 'harilibur.nik as ceklibur');


        if (!empty($request->kode_dept)) {
            $kodeDeptArr = is_array($request->kode_dept) ? $request->kode_dept : [$request->kode_dept];
            $query->whereIn('karyawan.kode_dept', $kodeDeptArr);
        }
        $query->where('karyawan.kode_cabang', $harilibur->kode_cabang);
        if (!empty($request->nama_karyawan)) {
            $query->where('nama_karyawan', 'like', '%' . $request->nama_karyawan . '%');
        }
        //left join ke detail hari libur berdasarkan kode libur
        $query->leftJoinSub(
            Detailharilibur::select('nik')->where('kode_libur', $kode_libur),
            'harilibur',
            'karyawan.nik',
            '=',
            'harilibur.nik'
        );
        $query->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        $query->orderBy('nama_karyawan');
        $karyawan = $query->get();

        try {
            DB::beginTransaction();
            //Hapus Data Libur - bulk delete for better performance
            $nikList = $karyawan->pluck('nik')->toArray();
            $deletedCount = 0;

            // Jika cuti bersama, hapus record izin cuti terkait
            $hariLiburRecord = Harilibur::where('kode_libur', $kode_libur)->first();
            if ($hariLiburRecord && $hariLiburRecord->is_cuti_bersama && !empty($nikList)) {
                foreach ($nikList as $nik) {
                    $this->deleteCutiBersamaRecord($nik, $hariLiburRecord);
                }
            }

            if (!empty($nikList)) {
                $deletedCount = Detailharilibur::where('kode_libur', $request->kode_libur)
                    ->whereIn('nik', $nikList)
                    ->delete();
            }
            DB::commit();

            $message = $deletedCount . ' karyawan berhasil dibatalkan dari hari libur';
            if ($hariLiburRecord && $hariLiburRecord->is_cuti_bersama) {
                $message .= ' (jatah cuti dikembalikan)';
            }
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }


    /**
     * Buat record izin cuti dan presensi untuk cuti bersama
     * Auto-approve tanpa proses approval karena ini keputusan perusahaan
     */
    private function createCutiBersamaRecord(string $nik, $harilibur): void
    {
        $tanggal = $harilibur->tanggal;
        $keterangan = 'Cuti Bersama - ' . $harilibur->keterangan;

        // Cek apakah sudah ada izin cuti untuk tanggal ini
        $existingCuti = Izincuti::where('nik', $nik)
            ->where('dari', $tanggal)
            ->where('sampai', $tanggal)
            ->where('keterangan', 'like', 'Cuti Bersama%')
            ->first();

        if ($existingCuti) {
            return; // Sudah ada, skip
        }

        // Generate kode izin cuti
        $format = "IC" . date('ym', strtotime($tanggal));
        $lastizincuti = Izincuti::select('kode_izin_cuti')
            ->whereRaw('LEFT(kode_izin_cuti, 6) = ?', [$format])
            ->orderBy('kode_izin_cuti', 'desc')
            ->first();
        $last_kode = $lastizincuti != null ? $lastizincuti->kode_izin_cuti : '';
        $kode_izin_cuti = buatkode($last_kode, $format, 4);

        // Buat record izin cuti (auto-approved, status = 1)
        Izincuti::create([
            'kode_izin_cuti' => $kode_izin_cuti,
            'nik' => $nik,
            'tanggal' => $tanggal,
            'dari' => $tanggal,
            'sampai' => $tanggal,
            'kode_cuti' => 'C01', // Cuti Tahunan
            'keterangan' => $keterangan,
            'status' => 1, // Auto-approved
            'approval_step' => 1,
            'id_user' => auth()->user()->id,
        ]);

        // Cari jam kerja untuk tanggal tersebut
        $karyawan = Karyawan::where('nik', $nik)->first();
        if (!$karyawan) {
            return;
        }

        $namahari = getnamaHari(date('D', strtotime($tanggal)));

        $jamkerja = Setjamkerjabydate::join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('nik', $nik)
            ->where('tanggal', $tanggal)
            ->first();

        if ($jamkerja == null) {
            $jamkerja = Setjamkerjabyday::join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->where('nik', $nik)
                ->where('hari', $namahari)
                ->first();
        }

        if ($jamkerja == null) {
            $jamkerja = Detailsetjamkerjabydept::join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
                ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->where('kode_dept', $karyawan->kode_dept)
                ->where('kode_cabang', $karyawan->kode_cabang)
                ->where('hari', $namahari)
                ->first();
        }

        if ($jamkerja != null) {
            // Buat record presensi
            $presensi = Presensi::create([
                'nik' => $nik,
                'tanggal' => $tanggal,
                'kode_jam_kerja' => $jamkerja->kode_jam_kerja,
                'status' => 'c',
            ]);

            // Buat record approve izin cuti
            Approveizincuti::create([
                'id_presensi' => $presensi->id,
                'kode_izin_cuti' => $kode_izin_cuti,
            ]);
        }
    }


    /**
     * Hapus record izin cuti dan presensi terkait cuti bersama
     */
    private function deleteCutiBersamaRecord(string $nik, $harilibur): void
    {
        $tanggal = $harilibur->tanggal;

        // Cari izin cuti yang terkait dengan cuti bersama ini
        $izinCuti = Izincuti::where('nik', $nik)
            ->where('dari', $tanggal)
            ->where('sampai', $tanggal)
            ->where('keterangan', 'like', 'Cuti Bersama%')
            ->first();

        if (!$izinCuti) {
            return;
        }

        // Hapus presensi & approve izin cuti terkait
        $approves = Approveizincuti::where('kode_izin_cuti', $izinCuti->kode_izin_cuti)->get();
        if ($approves->isNotEmpty()) {
            Presensi::whereIn('id', $approves->pluck('id_presensi'))->delete();
            Approveizincuti::where('kode_izin_cuti', $izinCuti->kode_izin_cuti)->delete();
        }

        // Hapus izin cuti
        $izinCuti->delete();
    }
}

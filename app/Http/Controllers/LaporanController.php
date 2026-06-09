<?php

namespace App\Http\Controllers;

use App\Models\Bpjskesehatan;
use App\Models\Bpjstenagakerja;
use App\Models\Cabang;
use App\Models\Denda;
use App\Models\Departemen;
use App\Models\Detailpenyesuaiangaji;
use App\Models\Detailtunjangan;
use App\Models\Gajipokok;
use App\Models\Jenistunjangan;
use App\Models\Karyawan;
use App\Models\Pengaturanumum;
use App\Models\Presensi;
use App\Models\User;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exports\PresensiExport;
use App\Exports\GajiExport;
use App\Exports\PresensiKaryawanExport;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    protected $laporanService;

    public function __construct(\App\Services\LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }
    public function cuti()
    {
        $data['list_bulan'] = config('global.list_bulan');
        $data['start_year'] = config('global.start_year');
        $data['cabang'] = Auth::user()->getCabang();
        $data['departemen'] = Auth::user()->getDepartemen();
        $data['cuti'] = \App\Models\Cuti::orderBy('kode_cuti')->get();
        return view('laporan.cuti', $data);
    }

    public function cetakcuti(Request $request)
    {
        $tahun = $request->tahun;
        $kode_cabang = $request->kode_cabang;
        $kode_dept = $request->kode_dept;
        $kode_cuti = $request->kode_cuti;
        $generalsetting = \App\Models\Pengaturanumum::where('id', 1)->first();

        // Get Master Cuti info if specific cuti selected
        $master_cuti = null;
        if (!empty($kode_cuti)) {
            $master_cuti = \App\Models\Cuti::where('kode_cuti', $kode_cuti)->first();
        }

        // Get Employees Query
        $query = Karyawan::query();
        $query->orderBy('nama_karyawan');

        // Batasi akses berdasarkan cabang user login
        $allowedCabang = Auth::user()->getCabangCodes();
        if (!empty($kode_cabang)) {
            // Intersect dengan cabang yang diizinkan
            $query->where('kode_cabang', $kode_cabang);
        } elseif (!empty($allowedCabang)) {
            $query->whereIn('kode_cabang', $allowedCabang);
        }

        if (!empty($kode_dept)) {
            $query->where('kode_dept', $kode_dept);
        }
        $karyawan = $query->get();

        // Get Approved Leave Data (Days)
        // Join with Presensi and IzinCuti
        $cuti_data = DB::table('presensi_izincuti_approve')
            ->join('presensi', 'presensi_izincuti_approve.id_presensi', '=', 'presensi.id')
            ->join('presensi_izincuti', 'presensi_izincuti_approve.kode_izin_cuti', '=', 'presensi_izincuti.kode_izin_cuti')
            ->select('presensi.nik', 'presensi.tanggal', 'presensi_izincuti.kode_cuti')
            ->whereRaw('YEAR(presensi.tanggal) = ?', [$tahun])
            ->get();

        // Process data structure
        $rekap_cuti = [];
        foreach ($karyawan as $k) {
            $rekap_cuti[$k->nik] = [
                'nama' => $k->nama_karyawan,
                'bulan' => array_fill(1, 12, 0),
                'total_ambil' => 0,
                'sisa' => 0 
            ];
        }

        foreach ($cuti_data as $d) {
            // Check if employee exists in the filtered list
            if (isset($rekap_cuti[$d->nik])) {
                // Filter by specific cuti type if requested
                if (!empty($kode_cuti) && $d->kode_cuti != $kode_cuti) {
                    continue;
                }

                $bulan = (int)date('m', strtotime($d->tanggal));
                $rekap_cuti[$d->nik]['bulan'][$bulan]++;
                $rekap_cuti[$d->nik]['total_ambil']++;
            }
        }
        
        $data['tahun'] = $tahun;
        $data['rekap_cuti'] = $rekap_cuti;
        $data['master_cuti'] = $master_cuti;
        $data['namacabang'] = !empty($kode_cabang) ? Cabang::where('kode_cabang', $kode_cabang)->first()->nama_cabang : 'Semua Cabang';
        $data['namadept'] = !empty($kode_dept) ? Departemen::where('kode_dept', $kode_dept)->first()->nama_dept : 'Semua Departemen';
        $data['jenis_cuti'] = !empty($master_cuti) ? $master_cuti->jenis_cuti : 'Semua Jenis Cuti';
        $data['generalsetting'] = $generalsetting;

        if(isset($_POST['exportexcel'])){
             // Future export
        }
        
        return view('laporan.cetak_cuti', $data);

    }

    public function presensi()
    {
        $data['list_bulan'] = config('global.list_bulan');
        $data['start_year'] = config('global.start_year');
        $data['cabang'] = Auth::user()->getCabang();
        $data['departemen'] = Auth::user()->getDepartemen();
        return view('laporan.presensi', $data);
    }


    public function cetakpresensi(Request $request)
    {
        ini_set('pcre.jit', '0');
        ini_set('pcre.backtrack_limit', '100000000');
        libxml_use_internal_errors(true);

        $user = User::where('id', Auth::user()->id)->first();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        [$periode_dari, $periode_sampai] = $this->laporanService->resolvePeriodeLaporan($request, $generalsetting);




        $jenis_tunjangan = null;
        $q_presensi = $this->laporanService->buildLaporanQuery($periode_dari, $periode_sampai, $request, $user, $userkaryawan, $jenis_tunjangan);
        $presensi = $q_presensi->get();

        $jadwalMapping = $this->laporanService->getJadwalMapping($periode_dari, $periode_sampai, false);
        $jadwal_bydate = $jadwalMapping['bydate'];
        $jadwal_grup_bydate = $jadwalMapping['grup_bydate'];
        $jadwal_byday = $jadwalMapping['byday'];
        $jadwal_bydept = $jadwalMapping['bydept'];


        $data['periode_dari'] = $periode_dari;
        $data['periode_sampai'] = $periode_sampai;
        $data['jmlhari'] = hitungJumlahHari($periode_dari, $periode_sampai) + 1;
        $data['denda_list'] = Denda::all()->toArray();
        $data['datalibur'] = getdatalibur($periode_dari, $periode_sampai);
        $data['datalembur'] = getlembur($periode_dari, $periode_sampai);
        $data['generalsetting'] = $generalsetting;
        // Kirim mapping jadwal ke view untuk dipakai saat karyawan tidak presensi
        $data['jadwal_bydate'] = $jadwal_bydate;
        $data['jadwal_grup_bydate'] = $jadwal_grup_bydate;
        $data['jadwal_byday'] = $jadwal_byday;
        $data['jadwal_bydept'] = $jadwal_bydept;
        // Simpan parameter request untuk button kunci laporan
        $data['request_params'] = [
            'periode_laporan' => $request->periode_laporan,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'kode_cabang' => $request->kode_cabang ?? [],
            'kode_dept' => $request->kode_dept ?? [],
            'nik' => $request->nik ?? [],
            'jenis_upah' => $request->jenis_upah ?? ''
        ];

        // Laporan per-karyawan hanya jika tepat 1 NIK dipilih
        $nik_list = array_filter((array) ($request->nik ?? []));
        if (count($nik_list) === 1 && $request->format_laporan == 1) {
            $karyawan = Karyawan::join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
                ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
                ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
                ->select('karyawan.*', 'jabatan.nama_jabatan', 'departemen.nama_dept', 'cabang.nama_cabang')
                ->where('karyawan.nik', $nik_list[0])
                ->first();
            $data['karyawan'] = $karyawan;
            $data['presensi'] = $presensi;
            if ($request->has('exportButton')) {
                return Excel::download(new PresensiKaryawanExport($data), 'Laporan Presensi Karyawan ' . $periode_dari . ' - ' . $periode_sampai . '.xlsx');
            }
            return view('laporan.presensi_karyawan_cetak', $data);
        } else {
            $laporan_presensi = $this->laporanService->formatLaporanData($presensi, $jenis_tunjangan);
            $data['laporan_presensi'] = $laporan_presensi;
            $data['jenis_tunjangan'] = $jenis_tunjangan;


            if ($user->hasRole('karyawan')) {
                $first_row = $laporan_presensi->first();
                $jenis_upah = ($first_row && isset($first_row['jenis_upah'])) ? $first_row['jenis_upah'] : 'Bulanan';
                $view = ($jenis_upah == 'Harian') ? 'laporan.slip_karyawan_harian_cetak' : 'laporan.slip_karyawan_cetak';
                return view($view, $data);
            } else {
                if ($request->format_laporan == 1) {
                    if ($request->has('exportButton')) {
                        return Excel::download(new PresensiExport($data), 'Rekap Presensi ' . $periode_dari . ' - ' . $periode_sampai . '.xlsx');
                    }
                    return view('laporan.presensi_cetak', $data);
                } else if ($request->format_laporan == 2) {
                    if ($request->has('exportButton')) {
                        $view = $request->jenis_upah == 'Harian' ? 'laporan.gaji_harian_excel' : 'laporan.gaji_excel';
                        return Excel::download(new GajiExport($data, $view), 'Rekap Gaji ' . $periode_dari . ' - ' . $periode_sampai . '.xlsx');
                    }

                    if ($request->jenis_upah == 'Harian') {
                        return view('laporan.gaji_harian_cetak', $data);
                    }

                    return view('laporan.gaji_cetak', $data);
                } else if ($request->format_laporan == 3) {
                    $first_row = $laporan_presensi->first();
                    $jenis_upah = $request->jenis_upah ?: (($first_row && isset($first_row['jenis_upah'])) ? $first_row['jenis_upah'] : 'Bulanan');
                    
                    if ($user->hasRole('karyawan')) {
                        $view = $jenis_upah == 'Harian' ? 'laporan.slip_karyawan_harian_cetak' : 'laporan.slip_karyawan_cetak';
                        return view($view, $data);
                    }
                    $view = $jenis_upah == 'Harian' ? 'laporan.slip_harian_cetak' : 'laporan.slip_cetak';
                    return view($view, $data);
                }
            }
        }
    }

    public function kunciLaporan(Request $request)
    {
        try {
            $result = $this->laporanService->prosesKunciLaporan($request);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunci laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function batalkanKunciLaporan(Request $request)
    {
        try {
            $result = $this->laporanService->prosesBatalkanKunciLaporan($request);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan kunci laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function jadwal()
    {
        $data['list_bulan'] = config('global.list_bulan');
        $data['start_year'] = config('global.start_year');
        $cabang = Cabang::orderBy('kode_cabang')->get();
        $departemen = Departemen::orderBy('kode_dept')->get();
        $data['cabang'] = $cabang;
        $data['departemen'] = $departemen;
        return view('laporan.jadwal', $data);
    }

    public function cetakjadwal(Request $request)
    {
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $periode_dari = $request->dari;
        $periode_sampai = $request->sampai;

        $jadwalMapping = $this->laporanService->getJadwalMapping($periode_dari, $periode_sampai, true);
        $jadwal_bydate = $jadwalMapping['bydate'];
        $jadwal_grup_bydate = $jadwalMapping['grup_bydate'];
        $jadwal_byday = $jadwalMapping['byday'];
        $jadwal_bydept = $jadwalMapping['bydept'];

        $q_karyawan = Karyawan::query();
        $q_karyawan->select('karyawan.nik', 'karyawan.nik_show', 'nama_karyawan', 'nama_jabatan', 'karyawan.kode_dept', 'nama_dept', 'karyawan.kode_cabang');
        $q_karyawan->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan');
        $q_karyawan->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');

        $this->laporanService->applyKaryawanFilters($q_karyawan, $request, 'karyawan.nik');

        $q_karyawan->orderBy('karyawan.nama_karyawan');
        $karyawan = $q_karyawan->get();

        $data['periode_dari'] = $periode_dari;
        $data['periode_sampai'] = $periode_sampai;
        $data['jmlhari'] = hitungJumlahHari($periode_dari, $periode_sampai) + 1;
        $data['datalibur'] = getdatalibur($periode_dari, $periode_sampai);
        $data['generalsetting'] = $generalsetting;
        $data['jadwal_bydate'] = $jadwal_bydate;
        $data['jadwal_grup_bydate'] = $jadwal_grup_bydate;
        $data['jadwal_byday'] = $jadwal_byday;
        $data['jadwal_bydept'] = $jadwal_bydept;
        $data['karyawan'] = $karyawan;

        return view('laporan.jadwal_cetak', $data);
    }

    public function lembur()
    {
        // Auto-seed permission if it doesn't exist
        $permission = \Spatie\Permission\Models\Permission::where('name', 'laporan.lembur')->first();
        if (!$permission) {
            $permissiongroup = \App\Models\Permission_group::firstOrCreate(['name' => 'Laporan']);
            $permission = \Spatie\Permission\Models\Permission::firstOrCreate(
                ['name' => 'laporan.lembur'], 
                ['id_permission_group' => $permissiongroup->id]
            );
            $role = \Spatie\Permission\Models\Role::findById(1);
            if ($role && !$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        $data['cabang'] = Auth::user()->getCabang();
        $data['departemen'] = Auth::user()->getDepartemen();
        return view('laporan.lembur', $data);
    }

    public function cetaklembur(Request $request)
    {
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $periode_dari = $request->dari;
        $periode_sampai = $request->sampai;
        $status = $request->status;

        $q_lembur = \App\Models\Lembur::query()
            ->join('karyawan', 'lembur.nik', '=', 'karyawan.nik')
            ->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->leftJoin('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->select(
                'lembur.*',
                'karyawan.nama_karyawan',
                'karyawan.nik_show',
                'jabatan.nama_jabatan',
                'departemen.nama_dept',
                'cabang.nama_cabang'
            )
            ->whereBetween('lembur.tanggal', [$periode_dari, $periode_sampai]);

        $user = auth()->user();
        if (!$user->hasRole('karyawan') && !$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!empty($userCabangs)) {
                $q_lembur->whereIn('karyawan.kode_cabang', $userCabangs);
            } else {
                $q_lembur->whereRaw('1 = 0');
            }
            
            if (!empty($userDepartemens)) {
                $q_lembur->whereIn('karyawan.kode_dept', $userDepartemens);
            } else {
                $q_lembur->whereRaw('1 = 0');
            }
        }

        $this->laporanService->applyKaryawanFilters($q_lembur, $request, 'karyawan.nik');
        if ($status !== null && $status !== '') {
            $q_lembur->where('lembur.status', $status);
        }

        $lembur_data = $q_lembur->orderBy('lembur.tanggal', 'asc')
            ->orderBy('karyawan.nama_karyawan', 'asc')
            ->get();

        $data['lembur'] = $lembur_data;
        $data['periode_dari'] = $periode_dari;
        $data['periode_sampai'] = $periode_sampai;
        $data['generalsetting'] = $generalsetting;
        $data['datalibur'] = getdatalibur($periode_dari, $periode_sampai);

        if ($request->has('exportButton')) {
            return Excel::download(new \App\Exports\LemburExport($data), 'Laporan Lembur ' . $periode_dari . ' - ' . $periode_sampai . '.xlsx');
        }

        return view('laporan.lembur_cetak', $data);
    }
}

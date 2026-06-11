<?php

namespace App\Http\Controllers;

use App\Models\Izinabsen;
use App\Models\Izinsakit;
use App\Models\Izincuti;
use App\Models\Izindinas;
use App\Models\Userkaryawan;
use App\Models\User;
use App\Models\ApprovalLayer;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class KaryawanApprovalController extends Controller
{
    /**
     * Tampilkan daftar izin pending yang bisa di-approve oleh karyawan (via delegasi admin).
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $approvalService = app(ApprovalService::class);

        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userkaryawan || !$userkaryawan->approval_admin_id) {
            abort(403, 'Anda tidak memiliki akses approval.');
        }

        $admin = User::find($userkaryawan->approval_admin_id);
        if (!$admin) {
            abort(403, 'Admin approval tidak ditemukan.');
        }

        $adminRoles = $admin->getRoleNames();

        // Get admin's cabang & departemen access
        $adminDeptCodes = $admin->getDepartemenCodes();
        $adminCabangCodes = $admin->getCabangCodes();

        // Cari semua ApprovalLayer yang cocok dengan role admin
        $layers = ApprovalLayer::whereIn('role_name', $adminRoles)
                    ->where('feature', 'IZIN')
                    ->get();

        $pendingIzinAbsen = collect();
        $pendingIzinSakit = collect();
        $pendingIzinCuti = collect();
        $pendingIzinDinas = collect();

        if ($layers->isNotEmpty()) {
            $pendingIzinAbsen = self::buildIzinQuery(Izinabsen::class, 'presensi_izinabsen', $layers, $adminDeptCodes, $adminCabangCodes, true)->get()->unique('kode_izin');
            $pendingIzinSakit = self::buildIzinQuery(Izinsakit::class, 'presensi_izinsakit', $layers, $adminDeptCodes, $adminCabangCodes, true)->get()->unique('kode_izin_sakit');
            $pendingIzinCuti = self::buildIzinQuery(Izincuti::class, 'presensi_izincuti', $layers, $adminDeptCodes, $adminCabangCodes, true)->get()->unique('kode_izin_cuti');
            $pendingIzinDinas = self::buildIzinQuery(Izindinas::class, 'presensi_izindinas', $layers, $adminDeptCodes, $adminCabangCodes, true)->get()->unique('kode_izin_dinas');
        }

        $data['pendingIzinAbsen'] = $pendingIzinAbsen;
        $data['pendingIzinSakit'] = $pendingIzinSakit;
        $data['pendingIzinCuti'] = $pendingIzinCuti;
        $data['pendingIzinDinas'] = $pendingIzinDinas;
        $data['admin'] = $admin;
        $data['totalPending'] = $pendingIzinAbsen->count() + $pendingIzinSakit->count() + $pendingIzinCuti->count() + $pendingIzinDinas->count();

        return view('karyawanapproval.index', $data);
    }

    /**
     * Hitung total pending approval untuk badge di shortcut.
     */
    public static function getPendingCount($userId)
    {
        $userkaryawan = Userkaryawan::where('id_user', $userId)->first();
        if (!$userkaryawan || !$userkaryawan->approval_admin_id) {
            return 0;
        }

        $admin = User::find($userkaryawan->approval_admin_id);
        if (!$admin) return 0;

        $adminRoles = $admin->getRoleNames();
        $layers = ApprovalLayer::whereIn('role_name', $adminRoles)->where('feature', 'IZIN')->get();

        if ($layers->isEmpty()) return 0;

        $adminDeptCodes = $admin->getDepartemenCodes();
        $adminCabangCodes = $admin->getCabangCodes();

        $count = 0;
        $count += self::buildIzinQuery(Izinabsen::class, 'presensi_izinabsen', $layers, $adminDeptCodes, $adminCabangCodes, false)->count();
        $count += self::buildIzinQuery(Izinsakit::class, 'presensi_izinsakit', $layers, $adminDeptCodes, $adminCabangCodes, false)->count();
        $count += self::buildIzinQuery(Izincuti::class, 'presensi_izincuti', $layers, $adminDeptCodes, $adminCabangCodes, false)->count();
        $count += self::buildIzinQuery(Izindinas::class, 'presensi_izindinas', $layers, $adminDeptCodes, $adminCabangCodes, false)->count();

        return $count;
    }

    /**
     * Helper untuk membangun query pending izin berdasarkan multi-layer (Fix N+1 query)
     */
    private static function buildIzinQuery($modelClass, $tablePrefix, $layers, $adminDeptCodes, $adminCabangCodes, $withSelect = false)
    {
        $q = $modelClass::where($tablePrefix . '.status', 0)
            ->join('karyawan', $tablePrefix . '.nik', '=', 'karyawan.nik');
            
        if ($withSelect) {
            $q->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
              ->select($tablePrefix . '.*', 'karyawan.nama_karyawan', 'karyawan.kode_dept', 'karyawan.kode_jabatan', 'departemen.nama_dept');
        }

        // Apply layers with OR logic (group them)
        $q->where(function($query) use ($layers, $tablePrefix) {
            foreach ($layers as $fl) {
                $query->orWhere(function($subq) use ($fl, $tablePrefix) {
                    $level = $fl['level'] ?? $fl->level;
                    $dept = $fl['kode_dept'] ?? $fl->kode_dept;
                    $jab = $fl['kode_jabatan'] ?? $fl->kode_jabatan;

                    $subq->where($tablePrefix . '.approval_step', $level);
                    if ($dept) {
                        $subq->where('karyawan.kode_dept', $dept);
                    }
                    if ($jab) {
                        $subq->where('karyawan.kode_jabatan', $jab);
                    }
                });
            }
        });

        // Filter by admin's access rights
        if (!empty($adminDeptCodes)) {
            $q->whereIn('karyawan.kode_dept', $adminDeptCodes);
        }
        if (!empty($adminCabangCodes)) {
            $q->whereIn('karyawan.kode_cabang', $adminCabangCodes);
        }

        return $q;
    }

    /**
     * Validasi akses delegasi karyawan. Return admin user atau abort 403.
     */
    private function validateDelegationAccess()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userkaryawan || !$userkaryawan->approval_admin_id) {
            abort(403, 'Anda tidak memiliki akses approval delegasi.');
        }
        $admin = User::find($userkaryawan->approval_admin_id);
        if (!$admin) {
            abort(403, 'Admin approval tidak ditemukan.');
        }
        return $admin;
    }

    // ==================== IZIN ABSEN ====================
    public function approveIzinAbsen($kode_izin)
    {
        $admin = $this->validateDelegationAccess();
        $kode_izin = Crypt::decrypt($kode_izin);
        $izinabsen = Izinabsen::where('kode_izin', $kode_izin)
            ->join('karyawan', 'presensi_izinabsen.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();

        // Check admin's access
        $adminCabangs = $admin->getCabangCodes();
        $adminDepts = $admin->getDepartemenCodes();
        if (!in_array($izinabsen->kode_cabang, $adminCabangs) || !in_array($izinabsen->kode_dept, $adminDepts)) {
            abort(403, 'Admin tidak memiliki akses ke izin absen ini.');
        }

        $data['izinabsen'] = $izinabsen;
        return view('karyawanapproval.approve_izinabsen', $data);
    }

    public function storeApproveIzinAbsen(Request $request, $kode_izin)
    {
        $this->validateDelegationAccess();
        app(IzinabsenController::class)->storeapprove($request, $kode_izin, app(ApprovalService::class));
        return redirect()->route('karyawan-approval.index');
    }

    public function cancelApproveIzinAbsen($kode_izin)
    {
        $this->validateDelegationAccess();
        app(IzinabsenController::class)->cancelapprove($kode_izin);
        return redirect()->route('karyawan-approval.index');
    }

    // ==================== IZIN SAKIT ====================
    public function approveIzinSakit($kode_izin_sakit)
    {
        $admin = $this->validateDelegationAccess();
        $kode_izin_sakit = Crypt::decrypt($kode_izin_sakit);
        $izinsakit = Izinsakit::where('kode_izin_sakit', $kode_izin_sakit)
            ->join('karyawan', 'presensi_izinsakit.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();

        $adminCabangs = $admin->getCabangCodes();
        $adminDepts = $admin->getDepartemenCodes();
        if (!in_array($izinsakit->kode_cabang, $adminCabangs) || !in_array($izinsakit->kode_dept, $adminDepts)) {
            abort(403, 'Admin tidak memiliki akses ke izin sakit ini.');
        }

        $data['izinsakit'] = $izinsakit;
        return view('karyawanapproval.approve_izinsakit', $data);
    }

    public function storeApproveIzinSakit(Request $request, $kode_izin_sakit)
    {
        $this->validateDelegationAccess();
        app(IzinsakitController::class)->storeapprove($request, $kode_izin_sakit);
        return redirect()->route('karyawan-approval.index');
    }

    public function cancelApproveIzinSakit($kode_izin_sakit)
    {
        $this->validateDelegationAccess();
        app(IzinsakitController::class)->cancelapprove($kode_izin_sakit);
        return redirect()->route('karyawan-approval.index');
    }

    // ==================== IZIN CUTI ====================
    public function approveIzinCuti($kode_izin_cuti)
    {
        $admin = $this->validateDelegationAccess();
        $kode_izin_cuti = Crypt::decrypt($kode_izin_cuti);
        $izincuti = Izincuti::where('kode_izin_cuti', $kode_izin_cuti)
            ->join('karyawan', 'presensi_izincuti.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();

        $adminCabangs = $admin->getCabangCodes();
        $adminDepts = $admin->getDepartemenCodes();
        if (!in_array($izincuti->kode_cabang, $adminCabangs) || !in_array($izincuti->kode_dept, $adminDepts)) {
            abort(403, 'Admin tidak memiliki akses ke izin cuti ini.');
        }

        $data['izincuti'] = $izincuti;
        return view('karyawanapproval.approve_izincuti', $data);
    }

    public function storeApproveIzinCuti(Request $request, $kode_izin_cuti)
    {
        $this->validateDelegationAccess();
        app(IzincutiController::class)->storeapprove($request, $kode_izin_cuti);
        return redirect()->route('karyawan-approval.index');
    }

    public function cancelApproveIzinCuti($kode_izin_cuti)
    {
        $this->validateDelegationAccess();
        app(IzincutiController::class)->cancelapprove($kode_izin_cuti);
        return redirect()->route('karyawan-approval.index');
    }

    // ==================== IZIN DINAS ====================
    public function approveIzinDinas($kode_izin_dinas)
    {
        $admin = $this->validateDelegationAccess();
        $kode_izin_dinas = Crypt::decrypt($kode_izin_dinas);
        $izindinas = Izindinas::where('kode_izin_dinas', $kode_izin_dinas)
            ->join('karyawan', 'presensi_izindinas.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();

        $adminCabangs = $admin->getCabangCodes();
        $adminDepts = $admin->getDepartemenCodes();
        if (!in_array($izindinas->kode_cabang, $adminCabangs) || !in_array($izindinas->kode_dept, $adminDepts)) {
            abort(403, 'Admin tidak memiliki akses ke izin dinas ini.');
        }

        $data['izindinas'] = $izindinas;
        return view('karyawanapproval.approve_izindinas', $data);
    }

    public function storeApproveIzinDinas(Request $request, $kode_izin_dinas)
    {
        $this->validateDelegationAccess();
        app(IzindinasController::class)->storeapprove($request, $kode_izin_dinas);
        return redirect()->route('karyawan-approval.index');
    }

    public function cancelApproveIzinDinas($kode_izin_dinas)
    {
        $this->validateDelegationAccess();
        app(IzindinasController::class)->cancelapprove($kode_izin_dinas);
        return redirect()->route('karyawan-approval.index');
    }
}

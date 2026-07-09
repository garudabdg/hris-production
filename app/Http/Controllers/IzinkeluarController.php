<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Izinkeluar;
use App\Models\Karyawan;
use App\Models\User;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Models\Approval;
use App\Models\ApprovalLayer;
use App\Services\ApprovalService;
use App\Notifications\ApprovalStatusNotification;

class IzinkeluarController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $qizin = Izinkeluar::query();
        $qizin->join('karyawan', 'presensi_izinkeluar.nik', '=', 'karyawan.nik');
        $qizin->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan');
        $qizin->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        $qizin->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang');

        $this->filterQueryByAccess($qizin, $user);

        $qizin->select('presensi_izinkeluar.*', 'karyawan.nama_karyawan', 'karyawan.nik_show', 'karyawan.foto', 'jabatan.nama_jabatan', 'departemen.nama_dept', 'cabang.nama_cabang', 'karyawan.kode_dept');
        if (!empty($request->dari) && !empty($request->sampai)) {
            $qizin->whereBetween('presensi_izinkeluar.tanggal', [$request->dari, $request->sampai]);
        }
        if (!empty($request->nama_karyawan)) {
            $qizin->where('karyawan.nama_karyawan', 'like', '%' . $request->nama_karyawan . '%');
        }

        if (!empty($request->kode_cabang)) {
            $qizin->where('karyawan.kode_cabang', $request->kode_cabang);
        }

        if (!empty($request->kode_dept)) {
            $qizin->where('karyawan.kode_dept', $request->kode_dept);
        }

        if (!empty($request->status) || $request->status === '0') {
            $qizin->where('presensi_izinkeluar.status', $request->status);
        }
        $qizin->orderBy('presensi_izinkeluar.status');
        $qizin->orderBy('presensi_izinkeluar.tanggal', 'desc');
        $izinkeluar = $qizin->paginate(15);
        $izinkeluar->appends($request->all());

        $data['izinkeluar'] = $izinkeluar;
        $data['cabang'] = $user->getCabang();
        $data['departemen'] = $user->getDepartemen();
        return view('izinkeluar.index', $data);
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        if ($user->hasRole('karyawan')) {
            return view('izinkeluar.create-mobile');
        }
        
        $qkaryawan = Karyawan::query();
        $qkaryawan->select('karyawan.nik', 'karyawan.nama_karyawan');
        
        $this->filterQueryByAccess($qkaryawan, $user, 'kode_cabang', 'kode_dept');
        
        $karyawan = $qkaryawan->get();

        $data['karyawan'] = $karyawan;

        return view('izinkeluar.create', $data);
    }

    public function edit($kode_izin_keluar)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $kode_izin_keluar = Crypt::decrypt($kode_izin_keluar);

        $izinkeluar = Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)
            ->join('karyawan', 'presensi_izinkeluar.nik', '=', 'karyawan.nik')
            ->first();
        
        $this->checkAccess($user, $izinkeluar);
        
        $qkaryawan = Karyawan::query();
        $qkaryawan->select('karyawan.nik', 'karyawan.nama_karyawan');
        
        $this->filterQueryByAccess($qkaryawan, $user, 'kode_cabang', 'kode_dept');
        
        $karyawan = $qkaryawan->get();

        $data['karyawan'] = $karyawan;
        $data['izinkeluar'] = $izinkeluar;

        return view('izinkeluar.edit', $data);
    }

    public function store(Request $request)
    {
        $user = User::findorfail(auth()->user()->id);
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $role = $user->getRoleNames()->first();

        $nik = $user->hasRole('karyawan') ? $userkaryawan->nik : $request->nik;

        if ($role == 'karyawan') {
            $request->validate([
                'tanggal' => 'required',
                'jam_keluar' => 'required',
                'keperluan' => 'required',
            ]);
        } else {
            $request->validate([
                'nik' => 'required',
                'tanggal' => 'required',
                'jam_keluar' => 'required',
                'keperluan' => 'required',
            ]);
        }

        DB::beginTransaction();
        try {
            $lastizin = Izinkeluar::select('kode_izin_keluar')
                ->whereYear('tanggal', date('Y', strtotime($request->tanggal)))
                ->whereMonth('tanggal', date('m', strtotime($request->tanggal)))
                ->orderBy("kode_izin_keluar", "desc")
                ->first();
            $last_kode_izin = $lastizin != null ? $lastizin->kode_izin_keluar : '';
            $kode_izin_keluar  = buatkode($last_kode_izin, "IK"  . date('ym', strtotime($request->tanggal)), 4);

            Izinkeluar::create([
                'kode_izin_keluar' => $kode_izin_keluar,
                'nik' => $nik,
                'tanggal' => $request->tanggal,
                'jam_keluar' => $request->jam_keluar,
                'jam_kembali' => $request->jam_kembali,
                'keperluan' => $request->keperluan,
                'status' => 0,
                'approval_step' => 1,
            ]);
            
            // --- NOTIFIKASI KE ADMIN ---
            $karyawan_info = Karyawan::where('nik', $nik)->first();
            if ($karyawan_info) {
                $pesan = $karyawan_info->nama_karyawan . " mengajukan Izin Keluar / Offline pada " . formatIndo($request->tanggal) . " jam " . $request->jam_keluar . ".";
                $url = rtrim(env('APP_URL'), '/') . '/izinkeluar';
                sendAdminNotification($karyawan_info->kode_cabang, $karyawan_info->kode_dept, "Pengajuan Izin Keluar", $pesan, $url);
            }
            // ---------------------------
            
            DB::commit();

            if ($role == 'karyawan') {
                return Redirect::route('pengajuanizin.index')->with(messageSuccess('Data Berhasil Disimpan'));
            } else {
                return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function approve($kode_izin_keluar)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $kode_izin_keluar = Crypt::decrypt($kode_izin_keluar);
        $izinkeluar = Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)
            ->join('karyawan', 'presensi_izinkeluar.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        
        $this->checkAccess($user, $izinkeluar);

        $drivers = \App\Models\Karyawan::where('kode_dept', 'GA')->get();
        $vehicles = \App\Models\Asset::where('category_id', 1)->where('status', 'tersedia')->get();

        $data['izinkeluar'] = $izinkeluar;
        $data['drivers'] = $drivers;
        $data['vehicles'] = $vehicles;
        return view('izinkeluar.approve', $data);
    }

    public function storeapprove(Request $request, $kode_izin_keluar)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $approvalService = app(ApprovalService::class);
        
        $kode_izin_keluar = Crypt::decrypt($kode_izin_keluar);
        $izinkeluar = Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)
            ->join('karyawan', 'presensi_izinkeluar.nik', '=', 'karyawan.nik')
            ->select('presensi_izinkeluar.*', 'karyawan.kode_dept', 'karyawan.kode_cabang', 'karyawan.kode_jabatan')
            ->first();
        
        $this->checkAccess($user, $izinkeluar);
        
        $kode_dept = $izinkeluar->kode_dept;
        $kode_jabatan = $izinkeluar->kode_jabatan;
        $kode_cabang = $izinkeluar->kode_cabang;
        $currentStep = $izinkeluar->approval_step;
        $userRole = $user->getRoleNames()->first();
        $approvalUserId = $approvalService->getApprovalUserId($user);
        $approvalAdmin = $approvalUserId != $user->id ? User::find($approvalUserId) : $user;

         // Check Authorization using Service
        if (!$approvalService->canApprove('IZIN', $currentStep, $userRole, $kode_dept, $kode_jabatan, $user, $kode_cabang)) {
             if (!$user->isSuperAdmin()) {
                 return Redirect::back()->with(messageError('Anda tidak memiliki wewenang untuk menyetujui tahap ini.'));
             }
        }
        
        DB::beginTransaction();
        try {
            if (isset($request->approve)) {
                 // 1. Record Approval (atas nama admin jika delegasi)
                Approval::create([
                    'approvable_type' => Izinkeluar::class,
                    'approvable_id' => $kode_izin_keluar,
                    'user_id' => $approvalUserId,
                    'level' => $currentStep,
                    'status' => 'approved',
                    'keterangan' => 'Approved by ' . $approvalAdmin->name,
                ]);

                // 2. Check for Next Level rule
                $nextLevel = $currentStep + 1;
                $nextRule = $approvalService->getLayer('IZIN', $nextLevel, $kode_dept, $kode_jabatan, $kode_cabang);
                
                 if ($nextRule && !$user->hasRole('super admin')) {
                    // Update to next step
                    Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)->update(['approval_step' => $nextLevel]);
                    DB::commit();
                    return Redirect::back()->with(messageSuccess('Berhasil disetujui (Tahap ' . $currentStep . '). Menunggu approval tahap selanjutnya.'));
                } else {
                    // Final Approval
                    $updateData = ['status' => 1];
                    if ($request->filled('driver_nik')) {
                        $updateData['driver_nik'] = $request->driver_nik;
                    }
                    if ($request->filled('kode_asset_kendaraan')) {
                        $updateData['kode_asset_kendaraan'] = $request->kode_asset_kendaraan;
                        \App\Models\Asset::where('kode_asset', $request->kode_asset_kendaraan)->update(['status' => 'dipinjam']);
                    }

                    Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)->update($updateData);

                    // Kirim notifikasi ke karyawan bahwa izin keluar disetujui
                    $karyawanUser = User::whereHas('userkaryawan', function($q) use ($izinkeluar) {
                        $q->where('nik', $izinkeluar->nik);
                    })->first();
                    
                    if ($karyawanUser) {
                        $karyawanUser->notify(new ApprovalStatusNotification(
                            'IZIN_KELUAR',
                            $kode_izin_keluar,
                            1, // status: approved
                            $approvalAdmin->name
                        ));
                    }
                }

            } else {
                 // REJECTION Logic
                Approval::create([
                    'approvable_type' => Izinkeluar::class,
                    'approvable_id' => $kode_izin_keluar,
                    'user_id' => $approvalUserId,
                    'level' => $currentStep,
                    'status' => 'rejected',
                    'keterangan' => 'Rejected by ' . $approvalAdmin->name,
                ]);

                Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)->update([
                    'status' => 2
                ]);

                // Kirim notifikasi ke karyawan bahwa izin keluar ditolak
                $karyawanUser = User::whereHas('userkaryawan', function($q) use ($izinkeluar) {
                    $q->where('nik', $izinkeluar->nik);
                })->first();
                
                if ($karyawanUser) {
                    $karyawanUser->notify(new ApprovalStatusNotification(
                        'IZIN_KELUAR',
                        $kode_izin_keluar,
                        2, // status: rejected
                        $approvalAdmin->name
                    ));
                }
            }
            DB::commit();
            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function cancelapprove($kode_izin_keluar)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $kode_izin_keluar = Crypt::decrypt($kode_izin_keluar);
        $izinkeluar = Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)
            ->join('karyawan', 'presensi_izinkeluar.nik', '=', 'karyawan.nik')
            ->select('presensi_izinkeluar.*', 'karyawan.kode_dept', 'karyawan.kode_cabang')
            ->first();
        
        $this->checkAccess($user, $izinkeluar);
        
        DB::beginTransaction();
        try {
             // Case 1: Status is Pending (0) but moved steps (Intermediate Cancellation)
             if ($izinkeluar->status == 0) {
                 $lastStep = $izinkeluar->approval_step - 1;
                 
                 $lastApproval = Approval::where('approvable_type', Izinkeluar::class)
                    ->where('approvable_id', $kode_izin_keluar)
                    ->where('level', $lastStep)
                    ->where('user_id', $user->id) // Must be the one who approved it
                    ->first();

                if ($lastApproval) {
                    $lastApproval->delete();
                    Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)->update([
                        'approval_step' => $lastStep
                    ]);
                    DB::commit();
                    return Redirect::back()->with(messageSuccess('Approval dibatalkan. Kembali ke tahap sebelumnya.'));
                } else {
                     return Redirect::back()->with(messageError('Anda tidak dapat membatalkan approval ini (Bukan approver terakhir atau sudah diproses lanjut).'));
                }
             }
            // Case 2: Status is Final Approved (1)
            else if ($izinkeluar->status == 1) {
                  // Find final approval record (highest level)
                 $lastApproval = Approval::where('approvable_type', Izinkeluar::class)
                    ->where('approvable_id', $kode_izin_keluar)
                    ->where('user_id', $user->id)
                    ->orderBy('level', 'desc')
                    ->first();
                    
                if($lastApproval){
                     // Revert step to this level (so it becomes pending at this level again)
                     $revertStep = $lastApproval->level;
                     $lastApproval->delete();
                     
                     Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)->update([
                        'status' => 0,
                        'approval_step' => $revertStep
                    ]);
                    DB::commit();
                     return Redirect::back()->with(messageSuccess('Approval final dibatalkan. Kembali ke tahap sebelumnya.'));
                } else {
                    // Fallback
                    Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)->update([
                        'status' => 0
                    ]);
                     DB::commit();
                    return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
                }
            }
             return Redirect::back()->with(messageError('Status tidak valid untuk pembatalan.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function destroy($kode_izin_keluar)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $kode_izin_keluar = Crypt::decrypt($kode_izin_keluar);
        $izinkeluar = Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)
            ->join('karyawan', 'presensi_izinkeluar.nik', '=', 'karyawan.nik')
            ->first();
        
        $this->checkAccess($user, $izinkeluar, true);
        
        try {
            Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function update(Request $request, $kode_izin_keluar)
    {
        $kode_izin_keluar = Crypt::decrypt($kode_izin_keluar);
        $request->validate([
            'nik' => 'required',
            'tanggal' => 'required',
            'jam_keluar' => 'required',
            'keperluan' => 'required',
        ]);
        DB::beginTransaction();
        try {
            Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)->update([
                'nik' => $request->nik,
                'tanggal' => $request->tanggal,
                'jam_keluar' => $request->jam_keluar,
                'jam_kembali' => $request->jam_kembali,
                'keperluan' => $request->keperluan
            ]);
            DB::commit();
            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function show($kode_izin_keluar)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $kode_izin_keluar = Crypt::decrypt($kode_izin_keluar);
        $izinkeluar = Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)
            ->join('karyawan', 'presensi_izinkeluar.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        
        $this->checkAccess($user, $izinkeluar);

        $data['izinkeluar'] = $izinkeluar;
        return view('izinkeluar.show', $data);
    }

    private function checkAccess($user, $izinkeluar, $allowOwner = false)
    {
        if ($user->isSuperAdmin()) return;

        if ($allowOwner) {
            $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
            if ($userkaryawan && $userkaryawan->nik == $izinkeluar->nik) return;
        }

        $accessUser = $user->getApprovalAdmin() ?? $user;
        $userCabangs = $accessUser->getCabangCodes();
        $userDepartemens = $accessUser->getDepartemenCodes();
        
        $karyawanCabang = $izinkeluar->kode_cabang ?? Karyawan::where('nik', $izinkeluar->nik)->value('kode_cabang');
        $karyawanDept = $izinkeluar->kode_dept ?? Karyawan::where('nik', $izinkeluar->nik)->value('kode_dept');

        if (!in_array($karyawanCabang, $userCabangs) || !in_array($karyawanDept, $userDepartemens)) {
            abort(403, 'Anda tidak memiliki akses ke izin keluar ini.');
        }
    }

    private function filterQueryByAccess($query, $user, $colCabang = 'karyawan.kode_cabang', $colDept = 'karyawan.kode_dept')
    {
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!empty($userCabangs)) {
                $query->whereIn($colCabang, $userCabangs);
            } else {
                $query->whereRaw('1 = 0');
            }
            
            if (!empty($userDepartemens)) {
                $query->whereIn($colDept, $userDepartemens);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
    }

    public function selesai($kode_izin_keluar)
    {
        $kode_izin_keluar = Crypt::decrypt($kode_izin_keluar);
        $izinkeluar = Izinkeluar::where('kode_izin_keluar', $kode_izin_keluar)->first();
        
        if ($izinkeluar && $izinkeluar->status == 1 && empty($izinkeluar->jam_kembali)) {
            DB::beginTransaction();
            try {
                $izinkeluar->update([
                    'jam_kembali' => date('H:i:s')
                ]);
                
                if (!empty($izinkeluar->kode_asset_kendaraan)) {
                    \App\Models\Asset::where('kode_asset', $izinkeluar->kode_asset_kendaraan)->update([
                        'status' => 'tersedia'
                    ]);
                }
                
                DB::commit();
                return Redirect::back()->with(messageSuccess('Izin Keluar diselesaikan. Kendaraan dikembalikan.'));
            } catch (\Exception $e) {
                DB::rollBack();
                return Redirect::back()->with(messageError($e->getMessage()));
            }
        }
        return Redirect::back()->with(messageError('Izin tidak valid untuk diselesaikan.'));
    }
}

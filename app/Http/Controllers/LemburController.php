<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Approval;
use App\Models\ApprovalLayer;
use App\Models\Facerecognition;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\Pengaturanumum;
use App\Models\User;
use App\Models\Userkaryawan;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class LemburController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        // Cek jika user adalah karyawan departemen BU (Business), tampilkan error
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if ($userkaryawan) {
            $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();
            if ($karyawan && $karyawan->kode_dept == 'BU') {
                return redirect()->route('dashboard.index')->with('error', 'Fitur lembur tidak tersedia untuk departemen Business.');
            }
        }
        
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $qlembur = Lembur::query();
        $qlembur->join('karyawan', 'lembur.nik', '=', 'karyawan.nik');
        $qlembur->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan');
        $qlembur->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        $qlembur->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang');
        
        // Filter berdasarkan akses cabang dan departemen jika bukan super admin dan bukan karyawan
        if (!$user->hasRole('karyawan') && !$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!empty($userCabangs)) {
                $qlembur->whereIn('karyawan.kode_cabang', $userCabangs);
            } else {
                $qlembur->whereRaw('1 = 0');
            }
            
            if (!empty($userDepartemens)) {
                $qlembur->whereIn('karyawan.kode_dept', $userDepartemens);
            } else {
                $qlembur->whereRaw('1 = 0');
            }
        }
        
        if (!empty($request->dari) && !empty($request->sampai)) {
            $qlembur->whereBetween('lembur.tanggal', [$request->dari, $request->sampai]);
        }
        if (!empty($request->nama_karyawan)) {
            $qlembur->where('karyawan.nama_karyawan', 'like', '%' . $request->nama_karyawan . '%');
        }
        if (!empty($request->kode_cabang)) {
            $qlembur->where('karyawan.kode_cabang', $request->kode_cabang);
        }

        if (!empty($request->kode_dept)) {
            $qlembur->where('karyawan.kode_dept', $request->kode_dept);
        }

        if (!empty($request->status) || $request->status === '0') {
            $qlembur->where('lembur.status', $request->status);
        }

        if ($user->hasRole('karyawan')) {
            $qlembur->where('lembur.nik', $userkaryawan->nik);
        }

        $qlembur->orderBy('lembur.status');
        $qlembur->orderBy('lembur.tanggal', 'desc');
        $qlembur->select(
            'lembur.*',
            'karyawan.nama_karyawan',
            'karyawan.foto',
            'karyawan.kode_dept',
            'karyawan.kode_cabang',
            'karyawan.kode_jabatan',
            'jabatan.nama_jabatan',
            'departemen.nama_dept',
            'cabang.nama_cabang'
        );
        $lembur = $qlembur->paginate(15);
        $lembur->appends($request->all());

        $approvalService = app(\App\Services\ApprovalService::class);

        // Resolve waiting_role untuk karyawan (tampil di mobile view)
        if ($user->hasRole('karyawan') && $karyawan) {
            foreach ($lembur as $item) {
                $item->waiting_role = null;
                if ($item->status == 0 && $item->approval_step) {
                    $layer = $approvalService->getLayer('IZIN', $item->approval_step, $karyawan->kode_dept, $karyawan->kode_jabatan, $karyawan->kode_cabang);
                    $item->waiting_role = $layer?->role_name;
                }
            }
        } else {
            // Admin/HRD view: resolve waiting_role per item berdasarkan dept+cabang masing-masing
            foreach ($lembur as $item) {
                $item->waiting_role = null;
                if ($item->status == 0 && $item->approval_step) {
                    $layer = $approvalService->getLayer('IZIN', $item->approval_step, $item->kode_dept, $item->kode_jabatan, $item->kode_cabang);
                    $item->waiting_role = $layer?->role_name;
                }
            }
        }

        $data['lembur'] = $lembur;
        
        if ($user->hasRole('karyawan')) {
            $data['cabang'] = collect();
            $data['departemen'] = collect();
        } else {
            $data['cabang'] = $user->getCabang();
            $data['departemen'] = $user->getDepartemen();
        }

        if ($user->hasRole('karyawan')) {
            return view('lembur.index-karyawan', $data);
        } else {
            return view('lembur.index', $data);
        }
    }


    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $qkaryawan = Karyawan::query();
        $qkaryawan->select('karyawan.nik', 'karyawan.nama_karyawan');
        
        // Filter karyawan berdasarkan akses jika bukan super admin
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!empty($userCabangs)) {
                $qkaryawan->whereIn('kode_cabang', $userCabangs);
            } else {
                $qkaryawan->whereRaw('1 = 0');
            }
            
            if (!empty($userDepartemens)) {
                $qkaryawan->whereIn('kode_dept', $userDepartemens);
            } else {
                $qkaryawan->whereRaw('1 = 0');
            }
        }
        
        $karyawan = $qkaryawan->get();
        $data['karyawan'] = $karyawan;

        if ($user->hasRole('karyawan')) {
            return view('lembur.create-karyawan', $data);
        } else {
            return view('lembur.create', $data);
        }
    }


    public function store(Request $request)
    {
        $user = User::where('id', '=', auth()->user()->id)->first();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$user->hasRole('karyawan')) {
            $nik = $request->nik;
            $request->validate([
                'nik' => 'required',
                'dari' => 'required',
                'sampai' => 'required',
                'keterangan' => 'required',
            ]);
        } else {
            $nik = $userkaryawan->nik;
            $request->validate([
                'dari' => 'required',
                'sampai' => 'required',
                'keterangan' => 'required',
            ]);
        }


        try {
            Lembur::create([
                'nik' => $nik,
                'tanggal' => date('Y-m-d', strtotime($request->dari)),
                'lembur_mulai' => $request->dari,
                'lembur_selesai' => $request->sampai,
                'keterangan' => $request->keterangan,
                'status' => 0,
            ]);
            if ($user->hasRole('karyawan')) {
                return Redirect::route('lembur.index')->with('success', 'Data Lembur berhasil disimpan');
            } else {
                return Redirect::back()->with('success', 'Data Lembur berhasil disimpan');
            }
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'Data Lembur gagal disimpan' . $e->getMessage());
        }
    }


    public function edit($id)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $id = Crypt::decrypt($id);
        $lembur = Lembur::where('id', $id)
            ->join('karyawan', 'lembur.nik', '=', 'karyawan.nik')
            ->first();
        
        // Cek akses jika bukan super admin dan bukan karyawan
        if (!$user->hasRole('karyawan') && !$user->isSuperAdmin()) {
            $karyawanData = Karyawan::where('nik', $lembur->nik)->first();
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!in_array($karyawanData->kode_cabang, $userCabangs) || !in_array($karyawanData->kode_dept, $userDepartemens)) {
                abort(403, 'Anda tidak memiliki akses ke data lembur ini.');
            }
        }
        
        $qkaryawan = Karyawan::query();
        $qkaryawan->select('karyawan.nik', 'karyawan.nama_karyawan');
        
        // Filter karyawan berdasarkan akses jika bukan super admin
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!empty($userCabangs)) {
                $qkaryawan->whereIn('kode_cabang', $userCabangs);
            } else {
                $qkaryawan->whereRaw('1 = 0');
            }
            
            if (!empty($userDepartemens)) {
                $qkaryawan->whereIn('kode_dept', $userDepartemens);
            } else {
                $qkaryawan->whereRaw('1 = 0');
            }
        }
        
        $karyawan = $qkaryawan->get();
        $data['karyawan'] = $karyawan;
        $data['lembur'] = $lembur;
        return view('lembur.edit', $data);
    }


    public function update(Request $request, $id)
    {
        $id = Crypt::decrypt($id);
        $request->validate([
            'nik' => 'required',
            'dari' => 'required',
            'sampai' => 'required',
            'keterangan' => 'required',
        ]);

        try {
            $lembur = Lembur::find($id);
            $newStatus = $lembur->status;
            if ($request->lembur_in && $request->lembur_out) {
                $newStatus = 1;
            }

            Lembur::where('id', $id)->update([
                'nik' => $request->nik,
                'tanggal' => date('Y-m-d', strtotime($request->dari)),
                'lembur_mulai' => $request->dari,
                'lembur_selesai' => $request->sampai,
                'keterangan' => $request->keterangan,
                'lembur_in' => $request->lembur_in,
                'lembur_out' => $request->lembur_out,
                'status' => $newStatus,
            ]);
            return Redirect::back()->with('success', 'Data Lembur berhasil disimpan');
        } catch (\Exception $e) {
            return Redirect::back()->with('error', 'Data Lembur gagal disimpan' . $e->getMessage());
        }
    }


    public function approve($id)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $id = Crypt::decrypt($id);
        $lembur = Lembur::where('id', $id)
            ->join('karyawan', 'lembur.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        
        // Cek akses jika bukan super admin dan bukan karyawan
        if (!$user->hasRole('karyawan') && !$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!in_array($lembur->kode_cabang, $userCabangs) || !in_array($lembur->kode_dept, $userDepartemens)) {
                abort(403, 'Anda tidak memiliki akses ke data lembur ini.');
            }
        }

        // Load approval history with user relationship (sama seperti cuti)
        $approvals = Approval::where('approvable_type', 'App\Models\Lembur')
            ->where('approvable_id', $id)
            ->with('user')
            ->orderBy('level', 'asc')
            ->get();

        $data['lembur'] = $lembur;
        $data['approvals'] = $approvals;
        return view('lembur.approve', $data);
    }


    public function storeapprove(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $approvalService = app(ApprovalService::class);

        $id = Crypt::decrypt($id);
        $lembur = Lembur::where('lembur.id', $id)
            ->join('karyawan', 'lembur.nik', '=', 'karyawan.nik')
            ->select('lembur.*', 'karyawan.kode_dept', 'karyawan.kode_cabang', 'karyawan.kode_jabatan')
            ->first();

        // Cek akses jika bukan super admin
        if (!$user->isSuperAdmin()) {
            $accessUser = $user->getApprovalAdmin() ?? $user;
            $userCabangs = $accessUser->getCabangCodes();
            $userDepartemens = $accessUser->getDepartemenCodes();

            if (!in_array($lembur->kode_cabang, $userCabangs) || !in_array($lembur->kode_dept, $userDepartemens)) {
                abort(403, 'Anda tidak memiliki akses ke data lembur ini.');
            }
        }

        $kode_dept     = $lembur->kode_dept;
        $kode_jabatan  = $lembur->kode_jabatan;
        $kode_cabang   = $lembur->kode_cabang;
        $currentStep   = $lembur->approval_step;
        $userRole      = $user->getRoleNames()->first();
        $approvalUserId = $approvalService->getApprovalUserId($user);
        $approvalAdmin  = $approvalUserId != $user->id ? User::find($approvalUserId) : $user;

        // Check Authorization using Service
        if (!$approvalService->canApprove('IZIN', $currentStep, $userRole, $kode_dept, $kode_jabatan, $user, $kode_cabang)) {
            if (!$user->isSuperAdmin()) {
                return Redirect::back()->with(messageError('Anda tidak memiliki wewenang untuk approval tahap ke-' . $currentStep));
            }
        }

        DB::beginTransaction();
        try {
            if (isset($request->approve)) {
                // 1. Record Approval
                Approval::create([
                    'approvable_type' => 'App\Models\Lembur',
                    'approvable_id'   => $id,
                    'user_id'         => $approvalUserId,
                    'level'           => $currentStep,
                    'status'          => 'approved',
                    'keterangan'      => $request->keterangan ?? ('Approved by ' . $approvalAdmin->name),
                ]);

                // 2. Check for Next Level rule
                $nextLevel = $currentStep + 1;
                $nextRule  = $approvalService->getLayer('IZIN', $nextLevel, $kode_dept, $kode_jabatan, $kode_cabang);

                if ($nextRule && !$user->hasRole('super admin')) {
                    // Move to next step
                    Lembur::where('id', $id)->update(['approval_step' => $nextLevel]);
                    DB::commit();
                    return Redirect::back()->with(messageSuccess('Berhasil disetujui (Tahap ' . $currentStep . '). Menunggu approval tahap selanjutnya.'));
                } else {
                    // Final Approval
                    Lembur::where('id', $id)->update(['status' => 1]);
                }
            } else {
                // Rejection Logic
                Approval::create([
                    'approvable_type' => 'App\Models\Lembur',
                    'approvable_id'   => $id,
                    'user_id'         => $approvalUserId,
                    'level'           => $currentStep,
                    'status'          => 'rejected',
                    'keterangan'      => $request->keterangan ?? ('Rejected by ' . $approvalAdmin->name),
                ]);

                Lembur::where('id', $id)->update(['status' => 2]);
            }

            DB::commit();
            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }


    public function cancelapprove($id)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $id = Crypt::decrypt($id);
        $lembur = Lembur::where('lembur.id', $id)
            ->join('karyawan', 'lembur.nik', '=', 'karyawan.nik')
            ->select('lembur.*', 'karyawan.kode_dept', 'karyawan.kode_cabang')
            ->first();

        // Cek akses jika bukan super admin
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();

            if (!in_array($lembur->kode_cabang, $userCabangs) || !in_array($lembur->kode_dept, $userDepartemens)) {
                abort(403, 'Anda tidak memiliki akses ke data lembur ini.');
            }
        }

        DB::beginTransaction();
        try {
            // Case 1: Status pending (0) tapi sudah ada step (intermediate cancel)
            if ($lembur->status == 0) {
                $lastStep = $lembur->approval_step - 1;

                $lastApproval = Approval::where('approvable_type', 'App\Models\Lembur')
                    ->where('approvable_id', $id)
                    ->where('level', $lastStep)
                    ->where('user_id', $user->id)
                    ->first();

                if ($lastApproval) {
                    $lastApproval->delete();
                    Lembur::where('id', $id)->update(['approval_step' => $lastStep]);
                    DB::commit();
                    return Redirect::back()->with(messageSuccess('Approval dibatalkan. Kembali ke tahap sebelumnya.'));
                } else {
                    return Redirect::back()->with(messageError('Anda tidak dapat membatalkan approval ini (Bukan approver terakhir atau sudah diproses lanjut).'));
                }
            }
            // Case 2: Status final approved (1)
            else if ($lembur->status == 1) {
                $lastApproval = Approval::where('approvable_type', 'App\Models\Lembur')
                    ->where('approvable_id', $id)
                    ->where('user_id', $user->id)
                    ->orderBy('level', 'desc')
                    ->first();

                if ($lastApproval) {
                    $revertStep = $lastApproval->level;
                    $lastApproval->delete();

                    Lembur::where('id', $id)->update([
                        'status'        => 0,
                        'approval_step' => $revertStep,
                    ]);
                    DB::commit();
                    return Redirect::back()->with(messageSuccess('Approval final dibatalkan. Kembali ke tahap sebelumnya.'));
                } else {
                    // Fallback
                    Lembur::where('id', $id)->update(['status' => 0]);
                    DB::commit();
                    return Redirect::back()->with(messageSuccess('Data Berhasil Dibatalkan'));
                }
            }
            // Case 3: Status is Rejected (2) — cancel rejection and stay at current step
            else if ($lembur->status == 2) {
                // Find and delete the rejection record
                $rejectedApproval = Approval::where('approvable_type', 'App\Models\Lembur')
                    ->where('approvable_id', $id)
                    ->where('status', 'rejected')
                    ->first();
                
                if ($rejectedApproval) {
                    $rejectedApproval->delete();
                }

                // Reset status to pending, leave approval_step as is
                Lembur::where('id', $id)->update([
                    'status' => 0
                ]);
                
                DB::commit();
                return Redirect::back()->with(messageSuccess('Penolakan berhasil dibatalkan. Pengajuan kembali ke status Pending di tahap ' . $lembur->approval_step . '.'));
            }
            return Redirect::back()->with(messageError('Status tidak valid untuk pembatalan.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $id = Crypt::decrypt($id);
        $lembur = Lembur::where('id', $id)
            ->join('karyawan', 'lembur.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        
        // Cek akses jika bukan super admin dan bukan karyawan
        if (!$user->hasRole('karyawan') && !$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!in_array($lembur->kode_cabang, $userCabangs) || !in_array($lembur->kode_dept, $userDepartemens)) {
                abort(403, 'Anda tidak memiliki akses ke data lembur ini.');
            }
        }
        
        $data['lembur'] = $lembur;

        // Load approval history with user relationship (sama seperti cuti)
        $approvals = Approval::where('approvable_type', 'App\Models\Lembur')
            ->where('approvable_id', $id)
            ->with('user')
            ->orderBy('level', 'asc')
            ->get();
        $data['approvals'] = $approvals;
        $data['encryptedKode'] = Crypt::encrypt($id);

        return view('lembur.show', $data);
    }

    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $id = Crypt::decrypt($id);
        $lembur = Lembur::where('id', $id)
            ->join('karyawan', 'lembur.nik', '=', 'karyawan.nik')
            ->first();
        
        // Cek akses jika bukan super admin dan bukan karyawan
        if (!$user->hasRole('karyawan') && !$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
            
            if (!in_array($lembur->kode_cabang, $userCabangs) || !in_array($lembur->kode_dept, $userDepartemens)) {
                abort(403, 'Anda tidak memiliki akses ke data lembur ini.');
            }
        }
        
        try {
            Lembur::where('id', $id)->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }


    public function createpresensi($id)
    {
        $id = Crypt::decrypt($id);
        $lembur = Lembur::where('id', $id)
            ->select(
                'lembur.*',
                'karyawan.nama_karyawan',
                'cabang.nama_cabang',
                'cabang.radius_cabang',
                'cabang.lokasi_cabang'
            )
            ->join('karyawan', 'lembur.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();

        $data['wajah'] = Facerecognition::where('nik', $lembur->nik)->count();
        $data['lembur'] = $lembur;
        return view('lembur.create-presensi', $data);
    }

    public function storepresensi(Request $request)
    {
        $id_lembur = $request->id_lembur;
        $lembur = Lembur::where('id', $id_lembur)
            ->select(
                'lembur.*',
                'karyawan.nama_karyawan',
                'karyawan.lock_location',
                'cabang.nama_cabang',
                'cabang.radius_cabang',
                'cabang.lokasi_cabang'
            )
            ->join('karyawan', 'lembur.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();

        $status_lock_location = $lembur->lock_location;
        $status = $request->status;
        $lokasi = $request->lokasi;

        $tanggal_sekarang = date("Y-m-d");
        $jam_sekarang = date("H:i");

        //Get Lokasi User
        $koordinat_user = explode(",", $lokasi);
        $latitude_user = $koordinat_user[0];
        $longitude_user = $koordinat_user[1];

        //Get Lokasi Kantor
        $lokasi_kantor = $lembur->lokasi_cabang;

        $koordinat_kantor = explode(",", $lokasi_kantor);
        $latitude_kantor = $koordinat_kantor[0];
        $longitude_kantor = $koordinat_kantor[1];

        $jarak = hitungjarak($latitude_kantor, $longitude_kantor, $latitude_user, $longitude_user);
        $radius = round($jarak["meters"]);


        $in_out = $status == 1 ? "in" : "out";
        $image = $request->image;
        $folderPath = "public/uploads/lembur/";
        $formatName = $lembur->nik . "-" . $tanggal_sekarang . "-" . $in_out;
        $formatName = $lembur->nik . "-" . $tanggal_sekarang . "-" . $in_out;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = $formatName . ".png";
            $file = $folderPath . $fileName;
            Storage::put($file, file_get_contents($image));
        } else {
            $image_parts = explode(";base64", $image);
            $image_base64 = base64_decode($image_parts[1]);
            $fileName = $formatName . ".png";
            $file = $folderPath . $fileName;
            Storage::put($file, $image_base64);
        }

        $tanggal_presensi = $tanggal_sekarang;
        $jam_presensi = $tanggal_presensi . " " . $jam_sekarang;
        $batas_jam_absen = 30;


        $mulai_lembur = $lembur->lembur_mulai;
        //Jam Mulai Absen adalah 60 Menit Sebelum Jam Masuk
        $jam_mulai_masuk = date('Y-m-d H:i', strtotime('-' . $batas_jam_absen . ' minutes', strtotime($mulai_lembur)));

        //Jamulai Absen Pulang adalah 1 Jam dari Jam Masuk
        $jam_mulai_pulang =  date('Y-m-d H:i', strtotime('+' . $batas_jam_absen . ' minutes', strtotime($mulai_lembur)));


        //return $jam_mulai_pulang;
        $jam_pulang = $lembur->lembur_selesai;

        if ($status_lock_location == 1 && $radius > $lembur->radius_cabang) {
            return response()->json(['status' => false, 'message' => 'Anda Berada Di Luar Radius Kantor, Jarak Anda ' . formatAngka($radius) . ' Meters Dari Kantor', 'notifikasi' => 'notifikasi_radius'], 400);
        } else {
            if ($status == 1) {
                if ($lembur->lembur_in != null) {
                    return response()->json(['status' => false, 'message' => 'Anda Sudah Memulai Absen Lembur', 'notifikasi' => 'notifikasi_sudahabsen'], 400);
                } else if ($jam_presensi < $jam_mulai_masuk) {
                    return response()->json(['status' => false, 'message' => 'Maaf Belum Waktunya Absen Masuk, Waktu Absen Dimulai Pukul ' . formatIndo3($jam_mulai_masuk), 'notifikasi' => 'notifikasi_mulaiabsen'], 400);
                } else if ($jam_presensi > $jam_mulai_pulang) {
                    return response()->json(['status' => false, 'message' => 'Maaf Waktu Absen Masuk Sudah Habis ', 'notifikasi' => 'notifikasi_akhirabsen'], 400);
                } else {
                    try {
                        Lembur::where('id', $id_lembur)->update([
                            'lembur_in' => $jam_presensi,
                            'lokasi_lembur_in' => $lokasi,
                            'foto_lembur_in' => $fileName
                        ]);
                        // Storage::put($file, $image_base64);
                        return response()->json(['status' => true, 'message' => 'Berhasil Memulai Lembur', 'notifikasi' => 'notifikasi_absenmasuk'], 200);
                    } catch (\Exception $e) {
                        return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
                    }
                }
            } else {
                if ($lembur->lembur_out != null) {
                    return response()->json(['status' => false, 'message' => 'Anda Sudah Absen Pulang', 'notifikasi' => 'notifikasi_sudahabsen'], 400);
                } else if ($jam_presensi < $jam_mulai_pulang) {
                    return response()->json(['status' => false, 'message' => 'Maaf Belum Waktunya Absen Pulang, Waktu Absen Dimulai Pukul ' . formatIndo3($jam_mulai_pulang), 'notifikasi' => 'notifikasi_mulaiabsen'], 400);
                } else if ($jam_presensi > $jam_pulang) {
                    return response()->json(['status' => false, 'message' => 'Maaf Waktu Absen Masuk Sudah Habis ', 'notifikasi' => 'notifikasi_akhirabsen'], 400);
                } else {
                    try {
                        Lembur::where('id', $id_lembur)->update([
                            'lembur_out' => $jam_presensi,
                            'lokasi_lembur_out' => $lokasi,
                            'foto_lembur_out' => $fileName
                        ]);
                        // Storage::put($file, $image_base64);
                        return response()->json(['status' => true, 'message' => 'Berhasil Absen Pulang', 'notifikasi' => 'notifikasi_absenpulang'], 200);
                    } catch (\Exception $e) {
                        return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
                    }
                }
            }
        }
    }
}

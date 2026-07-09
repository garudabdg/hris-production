<?php

namespace App\Http\Controllers;

use App\Models\Approveizinabsen;
use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Detailsetjamkerjabydept;
use App\Models\Izinabsen;
use App\Models\Izincuti;
use App\Models\Izinsakit;
use App\Models\Karyawan;
use App\Models\Pengaturanumum;
use App\Models\Presensi;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use App\Models\User;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Jenssegers\Agent\Agent;
use App\Services\ApprovalService;
use App\Models\Approval;
use App\Models\ApprovalLayer;
use App\Notifications\ApprovalStatusNotification;


class IzinabsenController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $qizin = Izinabsen::query();
        $qizin->join('karyawan', 'presensi_izinabsen.nik', '=', 'karyawan.nik');
        $qizin->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan');
        $qizin->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        $qizin->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang');

        $this->filterQueryByAccess($qizin, $user);

        if (!empty($request->dari) && !empty($request->sampai)) {
            $qizin->whereBetween('presensi_izinabsen.tanggal', [$request->dari, $request->sampai]);
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
            $qizin->where('presensi_izinabsen.status', $request->status);
        }
        $qizin->select(
            'presensi_izinabsen.*',
            'karyawan.nama_karyawan',
            'karyawan.nik_show',
            'karyawan.foto',
            'jabatan.nama_jabatan',
            'karyawan.kode_dept',
            'karyawan.kode_cabang',
            'karyawan.kode_jabatan',
            'departemen.nama_dept',
            'cabang.nama_cabang'
        );
        $qizin->orderBy('presensi_izinabsen.status');
        $qizin->orderBy('presensi_izinabsen.tanggal', 'desc');
        $izinabsen = $qizin->paginate(15);
        $izinabsen->appends($request->all());

        $data['izinabsen'] = $izinabsen;
        $data['cabang'] = $user->getCabang();
        $data['departemen'] = $user->getDepartemen();
        return view('izinabsen.index', $data);
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $agent = new Agent();
        if ($user->hasRole('karyawan')) {
            return view('izinabsen.create-mobile');
        }
        
        $qkaryawan = Karyawan::query();
        $qkaryawan->select('karyawan.nik', 'karyawan.nama_karyawan');
        
        $this->filterQueryByAccess($qkaryawan, $user, 'kode_cabang', 'kode_dept');
        
        $karyawan = $qkaryawan->get();

        $data['karyawan'] = $karyawan;

        return view('izinabsen.create', $data);
    }

    public function edit($kode_izin)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $kode_izin = Crypt::decrypt($kode_izin);
        $izinabsen = Izinabsen::where('kode_izin', $kode_izin)
            ->join('karyawan', 'presensi_izinabsen.nik', '=', 'karyawan.nik')
            ->first();
        
        $this->checkAccess($user, $izinabsen);
        
        $qkaryawan = Karyawan::query();
        $qkaryawan->select('karyawan.nik', 'karyawan.nama_karyawan');
        
        $this->filterQueryByAccess($qkaryawan, $user, 'kode_cabang', 'kode_dept');
        
        $karyawan = $qkaryawan->get();
        $data['karyawan'] = $karyawan;
        $data['izinabsen'] = $izinabsen;

        return view('izinabsen.edit', $data);
    }

    public function store(Request $request)
    {
        $user = User::findorfail(auth()->user()->id);
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $role = $user->getRoleNames()->first();
        $general_setting = Pengaturanumum::where('id', 1)->first();
        $nik = $user->hasRole('karyawan') ? $userkaryawan->nik : $request->nik;

        if ($role == 'karyawan') {
            $request->validate([
                'dari' => 'required',
                'sampai' => 'required',
                'keterangan' => 'required',
            ]);
        } else {
            $request->validate([
                'nik' => 'required',
                'dari' => 'required',
                'sampai' => 'required',
                'keterangan' => 'required',
            ]);
        }

        DB::beginTransaction();
        try {
            $jmlhari = hitungHari($request->dari, $request->sampai);
            $batasi_hari_izin = $general_setting->batasi_hari_izin;
            $jml_hari_izin_max = $general_setting->jml_hari_izin_max;

            if ($jmlhari > $jml_hari_izin_max && $batasi_hari_izin == 1) {
                return Redirect::back()->with(messageError('Tidak Boleh Lebih dari ' . $jml_hari_izin_max . ' Hari!'));
            }

            $cek_izin_absen = Izinabsen::where('nik', $nik)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('dari', [$request->dari, $request->sampai])
                        ->orWhereBetween('sampai', [$request->dari, $request->sampai]);
                })
                ->first();

            $cek_izin_sakit = Izinsakit::where('nik', $nik)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('dari', [$request->dari, $request->sampai])
                        ->orWhereBetween('sampai', [$request->dari, $request->sampai]);
                })
                ->first();

            $cek_izin_cuti = Izincuti::where('nik', $nik)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('dari', [$request->dari, $request->sampai])
                        ->orWhereBetween('sampai', [$request->dari, $request->sampai]);
                })
                ->first();

            //dd($nik . "-" . $cek_izin_absen . "-" . $cek_izin_sakit . "-" . $cek_izin_cuti);
            if ($cek_izin_absen) {
                return Redirect::back()->with(messageError('Anda Sudah Mengajukan Izin Absen/Sakit/Cuti Pada Rentang Tanggal Tersebut!'));
            } else if ($cek_izin_sakit) {
                return Redirect::back()->with(messageError('Anda Sudah Mengajukan Izin Absen/Sakit/Cuti Absen Pada Rentang Tanggal Tersebut!'));
            } else if ($cek_izin_cuti) {
                return Redirect::back()->with(messageError('Anda Sudah Mengajukan Izin Absen/Sakit/Cuti Absen Pada Rentang Tanggal Tersebut!'));
            }
            $lastizin = Izinabsen::select('kode_izin')
                ->whereYear('dari', date('Y', strtotime($request->dari)))
                ->whereMonth('dari', date('m', strtotime($request->dari)))
                ->orderBy("kode_izin", "desc")
                ->first();
            $last_kode_izin = $lastizin != null ? $lastizin->kode_izin : '';
            $kode_izin  = buatkode($last_kode_izin, "IA"  . date('ym', strtotime($request->dari)), 4);

            $izin = new Izinabsen();
            $izin->kode_izin = $kode_izin;
            $izin->nik = $nik;
            $izin->tanggal = $request->dari;
            $izin->dari = $request->dari;
            $izin->sampai = $request->sampai;
            $izin->keterangan = $request->keterangan;
            $izin->status = 0;
            $izin->approval_step = 1;
            $izin->save();
            
            // --- NOTIFIKASI KE ADMIN ---
            $karyawan_info = Karyawan::where('nik', $nik)->first();
            if ($karyawan_info) {
                $pesan = $karyawan_info->nama_karyawan . " mengajukan Izin Absen mulai " . formatIndo($request->dari) . " s.d " . formatIndo($request->sampai) . ".";
                $url = rtrim(env('APP_URL'), '/') . '/izinabsen';
                sendAdminNotification($karyawan_info->kode_cabang, $karyawan_info->kode_dept, "Pengajuan Izin Absen", $pesan, $url);
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

    public function approve($kode_izin)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $kode_izin = Crypt::decrypt($kode_izin);
        $izinabsen = Izinabsen::where('kode_izin', $kode_izin)
            ->join('karyawan', 'presensi_izinabsen.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        
        $this->checkAccess($user, $izinabsen);

        // Load approval history with user relationship
        $approvals = \App\Models\Approval::where('approvable_type', 'App\Models\Izinabsen')
            ->where('approvable_id', $kode_izin)
            ->with('user')
            ->orderBy('level', 'asc')
            ->get();

        $data['izinabsen'] = $izinabsen;
        $data['approvals'] = $approvals;
        return view('izinabsen.approve', $data);
    }

    public function storeapprove(Request $request, $kode_izin, ApprovalService $approvalService)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $kode_izin = Crypt::decrypt($kode_izin);
        $izinabsen = Izinabsen::where('kode_izin', $kode_izin)
            ->join('karyawan', 'presensi_izinabsen.nik', '=', 'karyawan.nik')
            ->select('presensi_izinabsen.*', 'karyawan.kode_cabang', 'karyawan.kode_dept', 'karyawan.kode_jabatan')
            ->first();
        
        $this->checkAccess($user, $izinabsen);
        $dari = $izinabsen->dari;
        $sampai = $izinabsen->sampai;
        $nik = $izinabsen->nik;
        $kode_dept = $izinabsen->kode_dept;
        $kode_jabatan = $izinabsen->kode_jabatan;
        $kode_cabang = $izinabsen->kode_cabang;
        $error = '';
        
        // Dynamic Approval Logic
        $userRole = $user->getRoleNames()->first();
        $currentStep = $izinabsen->approval_step;
        $approvalUserId = $approvalService->getApprovalUserId($user);
        $approvalAdmin = $approvalUserId != $user->id ? User::find($approvalUserId) : $user;

        // Check Authorization using Service
        if (!$approvalService->canApprove('IZIN', $currentStep, $userRole, $kode_dept, $kode_jabatan, $user, $kode_cabang)) {
             if (!$user->isSuperAdmin()) {
                 return Redirect::back()->with(messageError('Anda tidak memiliki wewenang untuk approval tahap ke-' . $currentStep));
             }
        }

        DB::beginTransaction();
        try {
            if (isset($request->approve)) {
                
                // 1. Record Approval (atas nama admin jika delegasi)
                Approval::create([
                    'approvable_type' => Izinabsen::class,
                    'approvable_id' => $kode_izin,
                    'user_id' => $approvalUserId,
                    'level' => $currentStep,
                    'status' => 'approved',
                    'keterangan' => 'Approved by ' . $approvalAdmin->name,
                ]);
                
                // 2. Check for Next Level rule
                $nextLevel = $currentStep + 1;
                $nextRule = $approvalService->getLayer('IZIN', $nextLevel, $kode_dept, $kode_jabatan, $kode_cabang);
                
                if ($nextRule && !$user->hasRole('super admin')) {
                    // Move to next step
                     Izinabsen::where('kode_izin', $kode_izin)->update([
                        'approval_step' => $nextLevel
                    ]);
                    DB::commit();
                    return Redirect::back()->with(messageSuccess('Berhasil disetujui (Tahap ' . $currentStep . '). Menunggu approval tahap selanjutnya.'));
                }

                // If No Next Rule -> FINAL APPROVAL (Legacy Logic)
                Izinabsen::where('kode_izin', $kode_izin)->update([
                    'status' => 1
                ]);

                // Kirim notifikasi ke karyawan bahwa izin absen disetujui
                $karyawanUser = User::whereHas('userkaryawan', function($q) use ($nik) {
                    $q->where('nik', $nik);
                })->first();
                
                if ($karyawanUser) {
                    $karyawanUser->notify(new ApprovalStatusNotification(
                        'IZIN_ABSEN',
                        $kode_izin,
                        1, // status: approved
                        $approvalAdmin->name
                    ));
                }

                $error .= $this->generatePresensiIzin($dari, $sampai, $nik, $kode_dept, $izinabsen->kode_cabang, $kode_izin);
            } else {
                // REJECTION
                Approval::create([
                    'approvable_type' => Izinabsen::class,
                    'approvable_id' => $kode_izin,
                    'user_id' => $approvalUserId,
                    'level' => $currentStep,
                    'status' => 'rejected',
                    'keterangan' => 'Rejected by ' . $approvalAdmin->name,
                ]);
                
                Izinabsen::where('kode_izin', $kode_izin)->update([
                    'status' => 2
                ]);

                // Kirim notifikasi ke karyawan bahwa izin absen ditolak
                $karyawanUser = User::whereHas('userkaryawan', function($q) use ($nik) {
                    $q->where('nik', $nik);
                })->first();
                
                if ($karyawanUser) {
                    $karyawanUser->notify(new ApprovalStatusNotification(
                        'IZIN_ABSEN',
                        $kode_izin,
                        2, // status: rejected
                        $approvalAdmin->name
                    ));
                }
            }
            if (!empty($error)) {
                DB::rollBack();
                return Redirect::back()->with(messageError($error));
            }
            DB::commit();
            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function cancelapprove($kode_izin)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $kode_izin = Crypt::decrypt($kode_izin);
        $izinabsen = Izinabsen::where('kode_izin', $kode_izin)
            ->join('karyawan', 'presensi_izinabsen.nik', '=', 'karyawan.nik')
            ->select('presensi_izinabsen.*', 'karyawan.kode_cabang', 'karyawan.kode_dept')
            ->first();
        
        $this->checkAccess($user, $izinabsen);
        
        DB::beginTransaction();
        try {
             // Case 1: Status is Pending (0) but moved steps (Intermediate Cancellation)
             if ($izinabsen->status == 0) {
                 // Logic: Find the approval for the *previous* step (current_step - 1)
                 $lastStep = $izinabsen->approval_step - 1;
                 
                $lastApproval = Approval::where('approvable_type', Izinabsen::class)
                    ->where('approvable_id', $kode_izin)
                    ->where('level', $lastStep)
                    ->where('user_id', $user->id) // Must be the one who approved it
                    ->first();

                if ($lastApproval) {
                    $lastApproval->delete();
                    Izinabsen::where('kode_izin', $kode_izin)->update([
                        'approval_step' => $lastStep
                    ]);
                    DB::commit();
                    return Redirect::back()->with(messageSuccess('Approval dibatalkan. Kembali ke tahap sebelumnya.'));
                } else {
                     return Redirect::back()->with(messageError('Anda tidak dapat membatalkan approval ini (Bukan approver terakhir atau sudah diproses lanjut).'));
                }
            }
            // Case 2: Status is Final Approved (1) 
            else if ($izinabsen->status == 1) {
                 // Find final approval record (highest level)
                 $lastApproval = Approval::where('approvable_type', Izinabsen::class)
                    ->where('approvable_id', $kode_izin)
                    ->where('user_id', $user->id)
                    ->orderBy('level', 'desc')
                    ->first();

                if($lastApproval){
                     // Revert step to this level (so it becomes pending at this level again)
                     $revertStep = $lastApproval->level;
                     $lastApproval->delete();
                     
                     // Delete Presensi Data
                     $presensi = Approveizinabsen::where('kode_izin', $kode_izin)->get();
                     Presensi::whereIn('id', $presensi->pluck('id_presensi'))->delete();
                     Approveizinabsen::where('kode_izin', $kode_izin)->delete();

                     Izinabsen::where('kode_izin', $kode_izin)->update([
                        'status' => 0,
                        'approval_step' => $revertStep
                    ]);
                    DB::commit();
                    return Redirect::back()->with(messageSuccess('Approval final dibatalkan. Kembali ke tahap sebelumnya.'));

                } else {
                     // Fallback/Legacy
                     $presensi = Approveizinabsen::where('kode_izin', $kode_izin)->get();
                     Izinabsen::where('kode_izin', $kode_izin)->update([
                        'status' => 0
                    ]);
                    Approveizinabsen::where('kode_izin', $kode_izin)->delete();
                    Presensi::whereIn('id', $presensi->pluck('id_presensi'))->delete();
                    DB::commit();
                    return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
                }
            }
            // Case 3: Status is Rejected (2) — cancel rejection and stay at current step
            else if ($izinabsen->status == 2) {
                // Find and delete the rejection record
                $rejectedApproval = Approval::where('approvable_type', Izinabsen::class)
                    ->where('approvable_id', $kode_izin)
                    ->where('status', 'rejected')
                    ->first();
                
                if ($rejectedApproval) {
                    $rejectedApproval->delete();
                }

                // Reset status to pending, leave approval_step as is
                Izinabsen::where('kode_izin', $kode_izin)->update([
                    'status' => 0
                ]);
                
                DB::commit();
                return Redirect::back()->with(messageSuccess('Penolakan berhasil dibatalkan. Pengajuan kembali ke status Pending di tahap ' . $izinabsen->approval_step . '.'));
            }
             return Redirect::back()->with(messageError('Status tidak valid untuk pembatalan.'));

        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function destroy($kode_izin)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $kode_izin = Crypt::decrypt($kode_izin);
        $izinabsen = Izinabsen::where('kode_izin', $kode_izin)
            ->join('karyawan', 'presensi_izinabsen.nik', '=', 'karyawan.nik')
            ->first();
        
        $this->checkAccess($user, $izinabsen, true);
        
        try {
            Izinabsen::where('kode_izin', $kode_izin)->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function update(Request $request, $kode_izin)
    {
        $kode_izin = Crypt::decrypt($kode_izin);
        $request->validate([
            'nik' => 'required',
            'dari' => 'required',
            'sampai' => 'required',
            'keterangan' => 'required',
        ]);
        DB::beginTransaction();
        try {
            Izinabsen::where('kode_izin', $kode_izin)->update([
                'nik' => $request->nik,
                'tanggal' => $request->dari,
                'dari' => $request->dari,
                'sampai' => $request->sampai,
                'keterangan' => $request->keterangan
            ]);
            DB::commit();
            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }


    public function show($kode_izin)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $kode_izin = Crypt::decrypt($kode_izin);
        $izinabsen = Izinabsen::where('kode_izin', $kode_izin)
            ->join('karyawan', 'presensi_izinabsen.nik', '=', 'karyawan.nik')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        
        $this->checkAccess($user, $izinabsen);

        // Load approval history with user relationship
        $approvals = \App\Models\Approval::where('approvable_type', 'App\Models\Izinabsen')
            ->where('approvable_id', $kode_izin)
            ->with('user')
            ->orderBy('level', 'asc')
            ->get();

        $data['izinabsen'] = $izinabsen;
        $data['approvals'] = $approvals;
        $data['encryptedKode'] = Crypt::encrypt($kode_izin);
        return view('izinabsen.show', $data);
    }

    private function checkAccess($user, $izinabsen, $allowOwner = false)
    {
        if ($user->isSuperAdmin()) return;

        if ($allowOwner) {
            $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
            if ($userkaryawan && $userkaryawan->nik == $izinabsen->nik) return;
        }

        $accessUser = $user->getApprovalAdmin() ?? $user;
        $userCabangs = $accessUser->getCabangCodes();
        $userDepartemens = $accessUser->getDepartemenCodes();
        
        $karyawanCabang = $izinabsen->kode_cabang ?? Karyawan::where('nik', $izinabsen->nik)->value('kode_cabang');
        $karyawanDept = $izinabsen->kode_dept ?? Karyawan::where('nik', $izinabsen->nik)->value('kode_dept');

        if (!in_array($karyawanCabang, $userCabangs) || !in_array($karyawanDept, $userDepartemens)) {
            abort(403, 'Anda tidak memiliki akses ke izin absen ini.');
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

    private function generatePresensiIzin($dari, $sampai, $nik, $kode_dept, $kode_cabang, $kode_izin)
    {
        $error = '';
        while (strtotime($dari) <= strtotime($sampai)) {
            $namahari = getnamaHari(date('D', strtotime($dari)));

            $jamkerja = \App\Models\Setjamkerjabydate::join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->where('nik', $nik)->where('tanggal', $dari)->first()
                ?? \App\Models\Setjamkerjabyday::join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->where('nik', $nik)->where('hari', $namahari)->first()
                ?? \App\Models\Detailsetjamkerjabydept::join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
                ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->where('kode_dept', $kode_dept)->where('kode_cabang', $kode_cabang)
                ->where('hari', $namahari)->first();

            if ($jamkerja == null) {
                $error .= 'Jam Kerja pada Tanggal ' . $dari . ' Belum Di Set! <br>';
            } else {
                $presensi = \App\Models\Presensi::create([
                    'nik' => $nik,
                    'tanggal' => $dari,
                    'kode_jam_kerja' => $jamkerja->kode_jam_kerja,
                    'status' => 'i',
                ]);
                \App\Models\Approveizinabsen::create([
                    'id_presensi' => $presensi->id,
                    'kode_izin' => $kode_izin,
                ]);
            }
            $dari = date('Y-m-d', strtotime($dari . ' +1 day'));
        }
        return $error;
    }
}

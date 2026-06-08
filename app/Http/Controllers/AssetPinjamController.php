<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetPinjam;
use App\Models\Approval;
use App\Models\ApprovalLayer;
use App\Models\Karyawan;
use App\Models\User;
use App\Models\Userkaryawan;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class AssetPinjamController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.pinjam.index'), 403);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        $q = AssetPinjam::query()
            ->join('karyawan', 'asset_pinjam.nik', '=', 'karyawan.nik')
            ->join('assets', 'asset_pinjam.kode_asset', '=', 'assets.kode_asset')
            ->leftJoin('asset_categories', 'assets.category_id', '=', 'asset_categories.id')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang');

        $this->filterQueryByAccess($q, $user, 'karyawan.kode_cabang', 'karyawan.kode_dept');

        $this->applySearchFilters($q, $request);

        $q->select(
            'asset_pinjam.*',
            'karyawan.nama_karyawan',
            'karyawan.foto',
            'karyawan.kode_dept',
            'karyawan.kode_cabang',
            'karyawan.kode_jabatan',
            'departemen.nama_dept',
            'cabang.nama_cabang',
            'assets.nama_asset',
            'assets.foto as foto_asset',
            'assets.kondisi',
            'asset_categories.nama_kategori'
        );
        $q->orderBy('asset_pinjam.status');
        $q->orderBy('asset_pinjam.tanggal_pinjam', 'desc');

        $pinjam = $q->paginate(15);
        $pinjam->appends($request->all());

        $this->resolveWaitingRole($pinjam);

        $data['pinjam']   = $pinjam;
        $data['assets']   = Asset::where('status', 'tersedia')->orWhereIn('kode_asset', $pinjam->pluck('kode_asset'))->get();
        $data['cabang']   = $user->getCabang();
        return view('asset-pinjam.index', $data);
    }

    public function create()
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.pinjam.create'), 403);

        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Karyawan yang bisa dipilih sesuai akses cabang
        $qKaryawan = Karyawan::query()->where('status_aktif_karyawan', 1);
        if (!$user->isSuperAdmin()) {
            $this->filterQueryByAccess($qKaryawan, $user, 'kode_cabang', null);
        }
        $data['karyawan_list'] = $qKaryawan->orderBy('nama_karyawan')->get(['nik', 'nama_karyawan', 'kode_cabang', 'kode_dept']);

        // Asset tersedia sesuai akses cabang
        $qAsset = Asset::query()->where('status', 'tersedia');
        if (!$user->isSuperAdmin()) {
            $this->filterQueryByAccess($qAsset, $user, 'kode_cabang', null);
        }
        $data['assets'] = $qAsset->orderBy('nama_asset')->get();

        return view('asset-pinjam.create', $data);
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.pinjam.create'), 403);

        $request->validate([
            'kode_asset'             => 'required|exists:assets,kode_asset',
            'nik'                    => 'required|exists:karyawan,nik',
            'tanggal_pinjam'         => 'required|date',
            'tanggal_kembali_rencana'=> 'required|date|after_or_equal:tanggal_pinjam',
            'catatan'                => 'nullable|string|max:500',
            'foto_kondisi_pinjam'    => 'nullable|image|max:2048',
        ]);

        // Pastikan asset masih tersedia
        $asset = Asset::where('kode_asset', $request->kode_asset)->first();
        if ($asset->status !== 'tersedia') {
            return Redirect::back()->with(messageError('Asset tidak tersedia untuk dipinjam.'));
        }

        // Generate kode_pinjam
        $last = AssetPinjam::orderByDesc('id')->first();
        $kode_pinjam = buatkode($last?->kode_pinjam ?? '', 'AP' . date('ym'), 4);

        $fotoPath = $this->handleFotoUpload($request, 'foto_kondisi_pinjam');

        DB::beginTransaction();
        try {
            AssetPinjam::create([
                'kode_pinjam'             => $kode_pinjam,
                'kode_asset'              => $request->kode_asset,
                'nik'                     => $request->nik,
                'tanggal_pinjam'          => $request->tanggal_pinjam,
                'tanggal_kembali_rencana' => $request->tanggal_kembali_rencana,
                'catatan'                 => $request->catatan,
                'foto_kondisi_pinjam'     => $fotoPath,
                'status'                  => 0,
                'approval_step'           => 1,
                'id_user'                 => auth()->id(),
            ]);
            DB::commit();
            return Redirect::route('asset-pinjam.index')->with(messageSuccess('Pengajuan peminjaman berhasil disimpan.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function show($id)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.pinjam.index'), 403);

        $id = Crypt::decrypt($id);
        $pinjam = AssetPinjam::with(['asset.category', 'karyawan', 'approvals.user'])
            ->join('karyawan', 'asset_pinjam.nik', '=', 'karyawan.nik')
            ->join('assets', 'asset_pinjam.kode_asset', '=', 'assets.kode_asset')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->select(
                'asset_pinjam.*',
                'karyawan.nama_karyawan', 'karyawan.foto', 'karyawan.kode_dept', 'karyawan.kode_cabang', 'karyawan.kode_jabatan',
                'departemen.nama_dept', 'cabang.nama_cabang',
                'assets.nama_asset', 'assets.merk', 'assets.no_seri', 'assets.kondisi', 'assets.lokasi', 'assets.foto as foto_asset'
            )
            ->where('asset_pinjam.id', $id)
            ->firstOrFail();

        return view('asset-pinjam.show', compact('pinjam'));
    }

    public function approve($id)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.pinjam.approve'), 403);

        $id = Crypt::decrypt($id);
        $pinjam = AssetPinjam::with(['asset', 'approvals.user'])
            ->join('karyawan', 'asset_pinjam.nik', '=', 'karyawan.nik')
            ->join('assets', 'asset_pinjam.kode_asset', '=', 'assets.kode_asset')
            ->select(
                'asset_pinjam.*',
                'karyawan.nama_karyawan', 'karyawan.foto', 'karyawan.kode_dept', 'karyawan.kode_cabang', 'karyawan.kode_jabatan',
                'assets.nama_asset', 'assets.merk', 'assets.no_seri', 'assets.kondisi', 'assets.foto as foto_asset'
            )
            ->where('asset_pinjam.id', $id)
            ->firstOrFail();

        $approvals = Approval::where('approvable_type', AssetPinjam::class)
            ->where('approvable_id', $pinjam->kode_pinjam)
            ->with('user')
            ->orderBy('level')
            ->get();

        return view('asset-pinjam.approve', compact('pinjam', 'approvals'));
    }

    public function storeapprove(Request $request, $id, ApprovalService $approvalService)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.pinjam.approve'), 403);

        $id = Crypt::decrypt($id);
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $pinjam = AssetPinjam::join('karyawan', 'asset_pinjam.nik', '=', 'karyawan.nik')
            ->select('asset_pinjam.*', 'karyawan.kode_dept', 'karyawan.kode_cabang', 'karyawan.kode_jabatan')
            ->where('asset_pinjam.id', $id)
            ->firstOrFail();

        if ($pinjam->status != 0) {
            return Redirect::back()->with(messageError('Pengajuan ini sudah diproses.'));
        }

        $this->checkAccess($user, $pinjam);

        $userRole        = $user->getRoleNames()->first();
        $currentStep     = $pinjam->approval_step;
        $approvalUserId  = $approvalService->getApprovalUserId($user);
        $approvalAdmin   = $approvalUserId != $user->id ? User::find($approvalUserId) : $user;

        if (!$approvalService->canApprove('PINJAM', $currentStep, $userRole, $pinjam->kode_dept, $pinjam->kode_jabatan, $user, $pinjam->kode_cabang)) {
            if (!$user->isSuperAdmin()) {
                return Redirect::back()->with(messageError('Anda tidak memiliki wewenang untuk approval tahap ke-' . $currentStep));
            }
        }

        DB::beginTransaction();
        try {
            if (isset($request->approve)) {
                Approval::create([
                    'approvable_type' => AssetPinjam::class,
                    'approvable_id'   => $pinjam->kode_pinjam,
                    'user_id'         => $approvalUserId,
                    'level'           => $currentStep,
                    'status'          => 'approved',
                    'keterangan'      => 'Approved by ' . $approvalAdmin->name,
                ]);

                $nextLevel = $currentStep + 1;
                $nextRule  = $approvalService->getLayer('PINJAM', $nextLevel, $pinjam->kode_dept, $pinjam->kode_jabatan, $pinjam->kode_cabang);

                if ($nextRule && !$user->hasRole('super admin')) {
                    AssetPinjam::where('id', $id)->update(['approval_step' => $nextLevel]);
                    DB::commit();
                    return Redirect::back()->with(messageSuccess('Disetujui (Tahap ' . $currentStep . '). Menunggu approval tahap selanjutnya.'));
                }

                // Final approval — ubah status asset jadi dipinjam
                AssetPinjam::where('id', $id)->update(['status' => 1]);
                Asset::where('kode_asset', $pinjam->kode_asset)->update(['status' => 'dipinjam']);
                DB::commit();
                return Redirect::back()->with(messageSuccess('Peminjaman disetujui. Status asset diperbarui menjadi Dipinjam.'));

            } elseif (isset($request->reject)) {
                $request->validate(['catatan_penolakan' => 'required|string|max:500']);

                Approval::create([
                    'approvable_type' => AssetPinjam::class,
                    'approvable_id'   => $pinjam->kode_pinjam,
                    'user_id'         => $approvalUserId,
                    'level'           => $currentStep,
                    'status'          => 'rejected',
                    'keterangan'      => $request->catatan_penolakan,
                ]);
                AssetPinjam::where('id', $id)->update([
                    'status'            => 2,
                    'catatan_penolakan' => $request->catatan_penolakan,
                ]);
                DB::commit();
                return Redirect::back()->with(messageSuccess('Pengajuan peminjaman ditolak.'));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function cancelapprove($id)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.pinjam.approve'), 403);

        $id = Crypt::decrypt($id);
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $pinjam = AssetPinjam::where('id', $id)->firstOrFail();

        if ($pinjam->status != 0 || $pinjam->approval_step <= 1) {
            return Redirect::back()->with(messageError('Tidak dapat membatalkan approval ini.'));
        }

        $lastStep = $pinjam->approval_step - 1;

        // Cek apakah user ini yang approve step sebelumnya
        $lastApproval = Approval::where('approvable_type', AssetPinjam::class)
            ->where('approvable_id', $pinjam->kode_pinjam)
            ->where('level', $lastStep)
            ->where('user_id', auth()->id())
            ->first();

        if (!$lastApproval && !$user->isSuperAdmin()) {
            return Redirect::back()->with(messageError('Anda tidak dapat membatalkan approval yang bukan milik Anda.'));
        }

        DB::beginTransaction();
        try {
            $lastApproval?->delete();
            AssetPinjam::where('id', $id)->update(['approval_step' => $lastStep]);
            DB::commit();
            return Redirect::back()->with(messageSuccess('Approval berhasil dibatalkan.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function kembali($id)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.pinjam.approve'), 403);

        $id = Crypt::decrypt($id);
        $pinjam = AssetPinjam::join('karyawan', 'asset_pinjam.nik', '=', 'karyawan.nik')
            ->join('assets', 'asset_pinjam.kode_asset', '=', 'assets.kode_asset')
            ->select(
                'asset_pinjam.*',
                'karyawan.nama_karyawan',
                'assets.nama_asset', 'assets.merk', 'assets.kondisi as kondisi_asset', 'assets.foto as foto_asset'
            )
            ->where('asset_pinjam.id', $id)
            ->where('asset_pinjam.status', 1)
            ->firstOrFail();

        return view('asset-pinjam.kembali', compact('pinjam'));
    }

    public function storekembali(Request $request, $id)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.pinjam.approve'), 403);

        $request->validate([
            'tanggal_kembali_aktual' => 'required|date',
            'kondisi_kembali'        => 'required|in:baik,rusak,dalam_perbaikan',
            'foto_kondisi_kembali'   => 'nullable|image|max:2048',
            'catatan_kembali'        => 'nullable|string|max:500',
        ]);

        $id = Crypt::decrypt($id);
        $pinjam = AssetPinjam::where('id', $id)->where('status', 1)->firstOrFail();

        $fotoPath = $this->handleFotoUpload($request, 'foto_kondisi_kembali', $pinjam->foto_kondisi_kembali);

        DB::beginTransaction();
        try {
            AssetPinjam::where('id', $id)->update([
                'status'                  => 3,
                'tanggal_kembali_aktual'  => $request->tanggal_kembali_aktual,
                'foto_kondisi_kembali'    => $fotoPath,
                'catatan'                 => $pinjam->catatan . ($request->catatan_kembali ? "\n[Catatan Kembali] " . $request->catatan_kembali : ''),
            ]);

            // Update kondisi dan status asset
            Asset::where('kode_asset', $pinjam->kode_asset)->update([
                'status'  => 'tersedia',
                'kondisi' => $request->kondisi_kembali,
            ]);

            DB::commit();
            return Redirect::route('asset-pinjam.index')->with(messageSuccess('Asset berhasil dicatat sebagai dikembalikan.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->can('asset.pinjam.delete'), 403);

        $id = Crypt::decrypt($id);
        $pinjam = AssetPinjam::where('id', $id)->firstOrFail();

        if ($pinjam->status == 1) {
            return Redirect::back()->with(messageError('Tidak dapat menghapus peminjaman yang sedang aktif.'));
        }

        DB::beginTransaction();
        try {
            // Hapus foto
            if ($pinjam->foto_kondisi_pinjam) {
                Storage::disk('public')->delete($pinjam->foto_kondisi_pinjam);
            }
            if ($pinjam->foto_kondisi_kembali) {
                Storage::disk('public')->delete($pinjam->foto_kondisi_kembali);
            }
            Approval::where('approvable_type', AssetPinjam::class)
                ->where('approvable_id', $pinjam->kode_pinjam)
                ->delete();
            $pinjam->delete();
            DB::commit();
            return Redirect::back()->with(messageSuccess('Data peminjaman berhasil dihapus.'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    protected function filterQueryByAccess($query, $user, $colCabang, $colDept)
    {
        $userCabangs = $user->getCabangCodes();
        if (!empty($userCabangs)) {
            $query->whereIn($colCabang, $userCabangs);
        } else {
            $query->whereRaw('1 = 0');
        }
        
        if ($colDept) {
            $userDepartemens = $user->getDepartemenCodes();
            if (!empty($userDepartemens)) {
                $query->whereIn($colDept, $userDepartemens);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
    }

    private function applySearchFilters($query, $request)
    {
        if ($request->filled('nama_karyawan')) {
            $query->where('karyawan.nama_karyawan', 'like', '%' . $request->nama_karyawan . '%');
        }
        if ($request->filled('kode_cabang')) {
            $query->where('karyawan.kode_cabang', $request->kode_cabang);
        }
        if ($request->filled('kode_asset')) {
            $query->where('asset_pinjam.kode_asset', $request->kode_asset);
        }
        if ($request->status !== null && $request->status !== '') {
            $query->where('asset_pinjam.status', $request->status);
        }
    }

    private function resolveWaitingRole($items)
    {
        $approvalService = app(ApprovalService::class);
        foreach ($items as $item) {
            $item->waiting_role = null;
            if ($item->status == 0 && $item->approval_step) {
                $layer = $approvalService->getLayer('PINJAM', $item->approval_step, $item->kode_dept, $item->kode_jabatan, $item->kode_cabang);
                $item->waiting_role = $layer?->role_name;
            }
        }
    }

    private function checkAccess($user, $pinjam)
    {
        if (!$user->isSuperAdmin()) {
            $accessUser = $user->getApprovalAdmin() ?? $user;
            $userCabangs = $accessUser->getCabangCodes();
            $userDepartemens = $accessUser->getDepartemenCodes();
            
            if (!in_array($pinjam->kode_cabang, $userCabangs) || !in_array($pinjam->kode_dept, $userDepartemens)) {
                abort(403, 'Anda tidak memiliki akses ke pengajuan ini.');
            }
        }
    }

    private function handleFotoUpload(Request $request, $fieldName, $oldFotoPath = null)
    {
        $fotoPath = $oldFotoPath;
        if ($request->hasFile($fieldName)) {
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            $fotoPath = $request->file($fieldName)->store('asset-pinjam', 'public');
        }
        return $fotoPath;
    }
}

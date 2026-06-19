<?php

namespace App\Http\Controllers;

use App\Models\DailyReportBu;
use App\Models\DailyReportBuOnline;
use App\Models\DailyReportBuOffline;
use App\Models\DailyReportBuNasabah;
use App\Models\Karyawan;
use App\Models\User;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

/**
 * Controller: Daily Report Business (BU)
 * 
 * Mengelola daily report untuk karyawan divisi Business (BU).
 * Karyawan BU bisa mengisi 1 report per hari.
 * Admin bisa melihat, mengelola, dan export semua report.
 */
class DailyReportBuController extends Controller
{
    /**
     * Daftar daily report
     * - Karyawan: lihat report milik sendiri
     * - Admin: lihat semua report dengan filter
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = User::where('id', auth()->user()->id)->first();
        $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();

        // Build query
        $query = DailyReportBu::join('karyawan', 'daily_report_bu.nik', '=', 'karyawan.nik')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->select(
                'daily_report_bu.*',
                'karyawan.nama_karyawan',
                'karyawan.kode_dept',
                'karyawan.kode_cabang',
                'departemen.nama_dept'
            );

        // Apply access filters berdasarkan role
        $this->applyAccessFilters($query, $user, $user_karyawan);

        // Filter berdasarkan karyawan (nik)
        if ($request->filled('nik')) {
            $query->where('daily_report_bu.nik', $request->nik);
        }

        // Filter berdasarkan tanggal awal
        if ($request->filled('tanggal_awal')) {
            $query->whereDate('daily_report_bu.tanggal', '>=', $request->tanggal_awal);
        }

        // Filter berdasarkan tanggal akhir
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('daily_report_bu.tanggal', '<=', $request->tanggal_akhir);
        }

        // Filter berdasarkan sub departemen (team)
        if ($request->filled('sub_departemen')) {
            $query->where('daily_report_bu.sub_departemen', $request->sub_departemen);
        }

        $reports = $query->orderBy('daily_report_bu.tanggal', 'desc')->paginate(10);

        // Ambil daftar karyawan untuk filter
        $karyawans = $this->getKaryawansBuByAccess($user);

        // Ambil list sub departemen unik
        $subDepartemens = DailyReportBu::distinct()->pluck('sub_departemen')->filter()->values();

        // Jika karyawan → tampilan desktop khusus
        if ($user->hasRole('karyawan')) {
            return redirect()->route('dashboard.index');
        }

        return view('dailyreportbu.index', compact('reports', 'karyawans', 'subDepartemens'));
    }

    /**
     * Form buat daily report baru
     */
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = User::where('id', auth()->user()->id)->first();
        $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();

        // Platforms untuk section online
        $platforms = ['facebook', 'instagram', 'whatsapp', 'tiktok'];

        // Tipe untuk section offline
        $tipeOffline = ['appointment', 'cto', 'canvasing'];

        if ($user->hasRole('karyawan')) {
            $karyawan = Karyawan::where('nik', $user_karyawan->nik)
                ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
                ->select('karyawan.*', 'departemen.nama_dept')
                ->first();

            // Cek apakah sudah ada report hari ini
            $today = Carbon::now(config('app.timezone'))->format('Y-m-d');
            $existingReport = DailyReportBu::where('nik', $karyawan->nik)
                ->where('tanggal', $today)
                ->first();

            if ($existingReport) {
                return redirect()->route('dailyreportbu.edit', $existingReport->id)
                    ->with('info', 'Anda sudah mengisi report hari ini. Silakan edit report yang ada.');
            }

            return view('dailyreportbu.create', compact('karyawan', 'platforms', 'tipeOffline'));
        }

        // Admin view
        $karyawans = $this->getKaryawansBuByAccess($user);
        return view('dailyreportbu.create', compact('karyawans', 'platforms', 'tipeOffline'));
    }

    /**
     * Simpan daily report baru (DB Transaction)
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = User::where('id', auth()->user()->id)->first();
        $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();

        // Force NIK jika karyawan
        if ($user->hasRole('karyawan')) {
            $request->merge(['nik' => $user_karyawan->nik]);
        } else {
            // Validasi akses admin
            if (!$user->isSuperAdmin() && $request->filled('nik')) {
                $karyawan = Karyawan::where('nik', $request->nik)->first();
                if (!$this->checkAccessToKaryawan($user, $karyawan)) {
                    return redirect()->back()
                        ->withErrors(['nik' => 'Anda tidak memiliki akses ke karyawan ini.'])
                        ->withInput();
                }
            }
        }

        // Validasi input utama
        $validator = Validator::make($request->all(), [
            'nik' => 'required|exists:karyawan,nik',
            'tanggal' => 'required|date',
            'catatan' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Cek duplikat (1 report per hari)
        $existing = DailyReportBu::where('nik', $request->nik)
            ->where('tanggal', $request->tanggal)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withErrors(['tanggal' => 'Daily report untuk tanggal ini sudah ada.'])
                ->withInput();
        }

        // Ambil sub_departemen dari data karyawan
        $karyawan = Karyawan::where('nik', $request->nik)->first();
        $subDepartemen = $karyawan->sub_departemen ?? null;

        DB::beginTransaction();
        try {
            // 1. Simpan header report
            $report = DailyReportBu::create([
                'nik' => $request->nik,
                'tanggal' => $request->tanggal,
                'sub_departemen' => $subDepartemen,
                'catatan' => $request->catatan,
            ]);

            // 2. Simpan aktivitas online (4 platform)
            $platforms = ['facebook', 'instagram', 'whatsapp', 'tiktok'];
            foreach ($platforms as $platform) {
                DailyReportBuOnline::create([
                    'daily_report_bu_id' => $report->id,
                    'platform' => $platform,
                    'posting' => (int) ($request->input("online.{$platform}.posting", 0)),
                    'share_group' => (int) ($request->input("online.{$platform}.share_group", 0)),
                    'add_group' => (int) ($request->input("online.{$platform}.add_group", 0)),
                    'add_friend' => (int) ($request->input("online.{$platform}.add_friend", 0)),
                    'inbox' => (int) ($request->input("online.{$platform}.inbox", 0)),
                    'story' => (int) ($request->input("online.{$platform}.story", 0)),
                    'broadcast' => (int) ($request->input("online.{$platform}.broadcast", 0)),
                    'fanspage' => (int) ($request->input("online.{$platform}.fanspage", 0)),
                    'link_postingan' => $request->input("online.{$platform}.link_postingan", null),
                ]);
            }

            // 3. Simpan aktivitas offline (dinamis)
            if ($request->has('offline')) {
                foreach ($request->input('offline', []) as $offlineData) {
                    // Skip baris kosong
                    if (empty($offlineData['nama_prospek']) && empty($offlineData['whatsapp']) && empty($offlineData['alamat'])) {
                        continue;
                    }
                    DailyReportBuOffline::create([
                        'daily_report_bu_id' => $report->id,
                        'tipe' => $offlineData['tipe'] ?? 'appointment',
                        'nama_prospek' => $offlineData['nama_prospek'] ?? null,
                        'whatsapp' => $offlineData['whatsapp'] ?? null,
                        'alamat' => $offlineData['alamat'] ?? null,
                    ]);
                }
            }

            // 4. Simpan data calon nasabah (dinamis)
            if ($request->has('nasabah')) {
                foreach ($request->input('nasabah', []) as $nasabahData) {
                    // Skip baris kosong
                    if (empty($nasabahData['nama'])) {
                        continue;
                    }
                    DailyReportBuNasabah::create([
                        'daily_report_bu_id' => $report->id,
                        'nama' => $nasabahData['nama'],
                        'akun_sosial_media' => $nasabahData['akun_sosial_media'] ?? null,
                        'no_whatsapp' => $nasabahData['no_whatsapp'] ?? null,
                        'status_lead' => $nasabahData['status_lead'] ?? 'cold',
                        'keterangan' => $nasabahData['keterangan'] ?? null,
                    ]);
                }
            }

            DB::commit();

            $redirect = $user->hasRole('karyawan') ? route('dashboard.index') : route('dailyreportbu.index');
            return redirect($redirect)
                ->with('success', 'Daily report berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data.'])
                ->withInput();
        }
    }

    /**
     * Detail daily report lengkap
     */
    public function show($id)
    {
        /** @var \App\Models\User $user */
        $user = User::where('id', auth()->user()->id)->first();
        $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();

        $report = DailyReportBu::with(['onlineActivities', 'offlineActivities', 'nasabahData', 'karyawan'])
            ->findOrFail($id);

        // Validasi akses
        if ($user->hasRole('karyawan') && $report->nik !== $user_karyawan->nik) {
            abort(403, 'Unauthorized action.');
        }

        if (!$user->hasRole('karyawan') && !$user->isSuperAdmin()) {
            $karyawan = Karyawan::where('nik', $report->nik)->first();
            abort_unless($this->checkAccessToKaryawan($user, $karyawan), 403);
        }

        // Platforms untuk iterasi tampilan
        $platforms = ['facebook', 'instagram', 'whatsapp', 'tiktok'];

        // Jika karyawan → tampilan standalone desktop
        if ($user->hasRole('karyawan')) {
            return view('dailyreportbu.show', compact('report', 'platforms'));
        }

        return view('dailyreportbu.show', compact('report', 'platforms'));
    }

    /**
     * Form edit daily report
     */
    public function edit($id)
    {
        /** @var \App\Models\User $user */
        $user = User::where('id', auth()->user()->id)->first();
        $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();

        $report = DailyReportBu::with(['onlineActivities', 'offlineActivities', 'nasabahData'])
            ->findOrFail($id);

        // Validasi akses karyawan
        if ($user->hasRole('karyawan') && $report->nik !== $user_karyawan->nik) {
            abort(403, 'Unauthorized action.');
        }

        // Validasi akses admin
        if (!$user->hasRole('karyawan') && !$user->isSuperAdmin()) {
            $karyawan = Karyawan::where('nik', $report->nik)->first();
            abort_unless($this->checkAccessToKaryawan($user, $karyawan), 403);
        }

        $platforms = ['facebook', 'instagram', 'whatsapp', 'tiktok'];
        $tipeOffline = ['appointment', 'cto', 'canvasing'];

        if ($user->hasRole('karyawan')) {
            $karyawan = Karyawan::where('nik', $user_karyawan->nik)
                ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
                ->select('karyawan.*', 'departemen.nama_dept')
                ->first();
            return view('dailyreportbu.edit', compact('report', 'karyawan', 'platforms', 'tipeOffline'));
        }

        $karyawans = $this->getKaryawansBuByAccess($user);
        return view('dailyreportbu.edit', compact('report', 'karyawans', 'platforms', 'tipeOffline'));
    }

    /**
     * Update daily report (hapus & buat ulang child records)
     */
    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = User::where('id', auth()->user()->id)->first();
        $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();

        $report = DailyReportBu::findOrFail($id);

        // Validasi akses karyawan
        if ($user->hasRole('karyawan') && $report->nik !== $user_karyawan->nik) {
            abort(403, 'Unauthorized action.');
        }

        // Validasi akses admin
        if (!$user->hasRole('karyawan') && !$user->isSuperAdmin()) {
            $karyawan = Karyawan::where('nik', $report->nik)->first();
            abort_unless($this->checkAccessToKaryawan($user, $karyawan), 403);
        }

        // Validasi input
        $validator = Validator::make($request->all(), [
            'catatan' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Update header
            $report->update([
                'catatan' => $request->catatan,
            ]);

            // Hapus dan buat ulang child records
            $report->onlineActivities()->delete();
            $report->offlineActivities()->delete();
            $report->nasabahData()->delete();

            // Re-create online activities
            $platforms = ['facebook', 'instagram', 'whatsapp', 'tiktok'];
            foreach ($platforms as $platform) {
                DailyReportBuOnline::create([
                    'daily_report_bu_id' => $report->id,
                    'platform' => $platform,
                    'posting' => (int) ($request->input("online.{$platform}.posting", 0)),
                    'share_group' => (int) ($request->input("online.{$platform}.share_group", 0)),
                    'add_group' => (int) ($request->input("online.{$platform}.add_group", 0)),
                    'add_friend' => (int) ($request->input("online.{$platform}.add_friend", 0)),
                    'inbox' => (int) ($request->input("online.{$platform}.inbox", 0)),
                    'story' => (int) ($request->input("online.{$platform}.story", 0)),
                    'broadcast' => (int) ($request->input("online.{$platform}.broadcast", 0)),
                    'fanspage' => (int) ($request->input("online.{$platform}.fanspage", 0)),
                    'link_postingan' => $request->input("online.{$platform}.link_postingan", null),
                ]);
            }

            // Re-create offline activities
            if ($request->has('offline')) {
                foreach ($request->input('offline', []) as $offlineData) {
                    if (empty($offlineData['nama_prospek']) && empty($offlineData['whatsapp']) && empty($offlineData['alamat'])) {
                        continue;
                    }
                    DailyReportBuOffline::create([
                        'daily_report_bu_id' => $report->id,
                        'tipe' => $offlineData['tipe'] ?? 'appointment',
                        'nama_prospek' => $offlineData['nama_prospek'] ?? null,
                        'whatsapp' => $offlineData['whatsapp'] ?? null,
                        'alamat' => $offlineData['alamat'] ?? null,
                    ]);
                }
            }

            // Re-create nasabah data
            if ($request->has('nasabah')) {
                foreach ($request->input('nasabah', []) as $nasabahData) {
                    if (empty($nasabahData['nama'])) {
                        continue;
                    }
                    DailyReportBuNasabah::create([
                        'daily_report_bu_id' => $report->id,
                        'nama' => $nasabahData['nama'],
                        'akun_sosial_media' => $nasabahData['akun_sosial_media'] ?? null,
                        'no_whatsapp' => $nasabahData['no_whatsapp'] ?? null,
                        'status_lead' => $nasabahData['status_lead'] ?? 'cold',
                        'keterangan' => $nasabahData['keterangan'] ?? null,
                    ]);
                }
            }

            DB::commit();

            $redirect = $user->hasRole('karyawan') ? route('dashboard.index') : route('dailyreportbu.index');
            return redirect($redirect)
                ->with('success', 'Daily report berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Terjadi kesalahan saat memperbarui data.'])
                ->withInput();
        }
    }

    /**
     * Hapus daily report (cascade via FK)
     */
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = User::where('id', auth()->user()->id)->first();
        $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();

        $report = DailyReportBu::findOrFail($id);

        // Validasi akses karyawan
        if ($user->hasRole('karyawan') && $report->nik !== $user_karyawan->nik) {
            abort(403, 'Unauthorized action.');
        }

        // Validasi akses admin
        if (!$user->hasRole('karyawan') && !$user->isSuperAdmin()) {
            $karyawan = Karyawan::where('nik', $report->nik)->first();
            abort_unless($this->checkAccessToKaryawan($user, $karyawan), 403);
        }

        $report->delete(); // Cascade deletes child records via FK

        $redirect = $user->hasRole('karyawan') ? route('dashboard.index') : route('dailyreportbu.index');
        return redirect($redirect)
            ->with('success', 'Daily report berhasil dihapus.');
    }

    /**
     * Export daily report ke PDF
     */
    public function exportPdf(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = User::where('id', auth()->user()->id)->first();
        $user_karyawan = Userkaryawan::where('id_user', $user->id)->first();

        // Jika export single report
        if ($request->filled('id')) {
            $report = DailyReportBu::with(['onlineActivities', 'offlineActivities', 'nasabahData', 'karyawan'])
                ->findOrFail($request->id);

            // Validasi akses
            if ($user->hasRole('karyawan') && $report->nik !== $user_karyawan->nik) {
                abort(403, 'Unauthorized action.');
            }

            $karyawan = Karyawan::where('nik', $report->nik)
                ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
                ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
                ->select('karyawan.*', 'departemen.nama_dept', 'cabang.nama_cabang')
                ->first();

            $platforms = ['facebook', 'instagram', 'whatsapp', 'tiktok'];

            $pdf = Pdf::loadView('dailyreportbu.pdf', compact('report', 'karyawan', 'platforms'));
            $pdf->setPaper('A4', 'landscape');

            $filename = 'daily_report_bu_' . $report->tanggal->format('Y-m-d') . '_' . $report->nik . '.pdf';

            return $pdf->stream($filename);
        }

        return redirect()->back()->withErrors(['error' => 'ID report tidak ditemukan.']);
    }

    // ================================================================
    // PRIVATE HELPER METHODS
    // ================================================================

    /**
     * Apply access filters berdasarkan role user
     */
    private function applyAccessFilters($query, User $user, $user_karyawan)
    {
        if ($user->hasRole('karyawan')) {
            // Karyawan hanya lihat milik sendiri
            $query->where('daily_report_bu.nik', $user_karyawan->nik);
        } else {
            // Admin: filter berdasarkan cabang & departemen access
            if (!$user->isSuperAdmin()) {
                $userCabangs = $user->getCabangCodes();
                $userDepartemens = $user->getDepartemenCodes();

                if (!empty($userCabangs)) {
                    $query->whereIn('karyawan.kode_cabang', $userCabangs);
                } else {
                    $query->whereRaw('1 = 0');
                }

                if (!empty($userDepartemens)) {
                    $query->whereIn('karyawan.kode_dept', $userDepartemens);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }
    }

    /**
     * Ambil karyawan BU berdasarkan akses user
     */
    private function getKaryawansBuByAccess($user)
    {
        $query = Karyawan::where('kode_dept', 'BU');

        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();

            if (!empty($userCabangs)) {
                $query->whereIn('kode_cabang', $userCabangs);
            } else {
                $query->whereRaw('1 = 0');
            }

            // Pastikan BU termasuk dalam akses departemen user
            if (!empty($userDepartemens) && !in_array('BU', $userDepartemens)) {
                $query->whereRaw('1 = 0');
            }
        }

        return $query->orderBy('nama_karyawan')->get();
    }

    /**
     * Cek apakah user punya akses ke karyawan tertentu
     */
    private function checkAccessToKaryawan(User $user, $karyawan)
    {
        if (!$user->hasRole('karyawan') && !$user->isSuperAdmin() && $karyawan) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();

            if (!in_array($karyawan->kode_cabang, $userCabangs) || !in_array($karyawan->kode_dept, $userDepartemens)) {
                return false;
            }
        }
        return true;
    }
}

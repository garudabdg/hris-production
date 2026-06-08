<?php

namespace App\Http\Controllers;

use App\Mail\RecruitmentConfirmation;
use App\Mail\RecruitmentStatusUpdated;
use App\Jobs\SendWaMessage;
use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Jabatan;
use App\Models\Pengaturanumum;
use App\Models\Recruitment;
use App\Models\RecruitmentVacancy;
use App\Models\User;
use App\Notifications\NewRecruitmentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class RecruitmentController extends Controller
{
    // ─── ADMIN: List semua pelamar ────────────────────────────────────────────
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $isHrdCabang = !$user->isSuperAdmin() && $user->hasRole('hrd');

        $query = Recruitment::with(['cabang', 'departemen', 'jabatan'])
            ->orderByDesc('tanggal_melamar');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_recruitment', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('no_hp', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kode_cabang')) {
            $query->where('kode_cabang', $request->kode_cabang);
        }

        if ($request->filled('kode_dept')) {
            $query->where('kode_dept', $request->kode_dept);
        }

        // Batasi akses berdasarkan cabang user
        if (!$user->isSuperAdmin()) {
            $allowedCabang = $user->getCabangCodes();
            if (!empty($allowedCabang)) {
                $query->whereIn('kode_cabang', $allowedCabang);
            }
        }

        $recruitments = $query->paginate(20)->withQueryString();

        // Untuk HRD cabang: data dikelompokkan per cabang
        $recruitmentsByCabang = null;
        if ($isHrdCabang) {
            $allQuery = Recruitment::with(['cabang', 'departemen', 'jabatan'])
                ->orderByDesc('tanggal_melamar');

            $allowedCabang = $user->getCabangCodes();
            if (!empty($allowedCabang)) {
                $allQuery->whereIn('kode_cabang', $allowedCabang);
            }

            if ($request->filled('search')) {
                $allQuery->where(function ($q) use ($request) {
                    $q->where('nama_lengkap', 'like', '%' . $request->search . '%')
                      ->orWhere('kode_recruitment', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%')
                      ->orWhere('no_hp', 'like', '%' . $request->search . '%');
                });
            }
            if ($request->filled('status')) {
                $allQuery->where('status', $request->status);
            }

            $recruitmentsByCabang = $allQuery->get()->groupBy('kode_cabang');
        }

        $data['recruitments']       = $recruitments;
        $data['recruitmentsByCabang'] = $recruitmentsByCabang;
        $data['isHrdCabang']        = $isHrdCabang;
        $data['cabang']             = $user->getCabang();
        $data['departemen']         = $user->getDepartemen();
        $data['statuses']           = [
            'pending'   => 'Pending',
            'review'    => 'Review',
            'interview' => 'Interview',
            'offering'  => 'Penawaran',
            'diterima'  => 'Diterima',
            'ditolak'   => 'Ditolak',
        ];

        return view('recruitment.index', $data);
    }

    // ─── PUBLIC: Tampilkan form pendaftaran ────────────────────────────────────
    public function create()
    {
        // Lowongan yang masih buka, dikelompokkan per cabang
        $vacancies = RecruitmentVacancy::where('status', 'buka')
            ->where(function ($q) {
                $q->whereNull('deadline')->orWhere('deadline', '>=', now()->toDateString());
            })
            ->orderBy('kode_cabang')
            ->orderBy('posisi')
            ->get()
            ->groupBy('kode_cabang');

        // Hanya tampilkan cabang yang punya lowongan aktif
        $activeCabangCodes = $vacancies->keys()->toArray();
        $data['cabang']    = Cabang::whereIn('kode_cabang', $activeCabangCodes)
            ->orderBy('nama_cabang')
            ->get();
        $data['vacancies']       = $vacancies;
        $data['fixedCabang']     = null;

        return view('recruitment.create', $data);
    }

    // ─── PUBLIC: Form lamaran khusus cabang tertentu ────────────────────────────
    public function createByCabang($kode_cabang)
    {
        $cabang = Cabang::where('kode_cabang', $kode_cabang)->firstOrFail();

        $vacancies = RecruitmentVacancy::where('status', 'buka')
            ->where('kode_cabang', $kode_cabang)
            ->where(function ($q) {
                $q->whereNull('deadline')->orWhere('deadline', '>=', now()->toDateString());
            })
            ->orderBy('posisi')
            ->get()
            ->groupBy('kode_cabang');

        $data['cabang']      = collect([$cabang]);
        $data['vacancies']   = $vacancies;
        $data['fixedCabang'] = $cabang; // cabang terkunci

        return view('recruitment.create', $data);
    }

    // ─── PUBLIC: Simpan lamaran baru ───────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap'   => 'required|string|max:255',
            'tempat_lahir'   => 'nullable|string|max:100',
            'tanggal_lahir'  => 'nullable|date',
            'jenis_kelamin'  => 'required|in:L,P',
            'no_hp'          => 'required|string|max:20',
            'email'          => 'nullable|email|max:255',
            'alamat'         => 'nullable|string',
            'pendidikan_terakhir' => 'nullable|string',
            'posisi_dilamar' => 'required|string|max:255',
            'foto'           => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'cv'             => 'nullable|mimes:pdf,doc,docx|max:5120',
            'ijazah'         => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'nama_lengkap.required'  => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'no_hp.required'         => 'Nomor HP wajib diisi.',
            'posisi_dilamar.required' => 'Posisi yang dilamar wajib diisi.',
            'foto.required'          => 'Pas foto wajib diupload.',
            'foto.image'             => 'Foto harus berupa gambar (jpg/jpeg/png).',
            'foto.max'               => 'Ukuran foto maksimal 2MB.',
            'cv.mimes'               => 'CV harus berformat PDF, DOC, atau DOCX.',
            'cv.max'                 => 'Ukuran CV maksimal 5MB.',
        ]);

        // Cek duplikat: email atau no_hp yang masih dalam proses aktif
        $activeStatuses = ['pending', 'review', 'interview', 'offering'];

        if ($request->filled('email')) {
            $duplikatEmail = Recruitment::where('email', $request->email)
                ->whereIn('status', $activeStatuses)
                ->first();

            if ($duplikatEmail) {
                return back()->withInput()->withErrors([
                    'email' => 'Email ini sudah terdaftar dan masih dalam proses seleksi (kode: ' . $duplikatEmail->kode_recruitment . '). Silakan tunggu hasil seleksi sebelum melamar kembali.',
                ]);
            }
        }

        $duplikatHp = Recruitment::where('no_hp', $request->no_hp)
            ->whereIn('status', $activeStatuses)
            ->first();

        if ($duplikatHp) {
            return back()->withInput()->withErrors([
                'no_hp' => 'Nomor HP ini sudah terdaftar dan masih dalam proses seleksi (kode: ' . $duplikatHp->kode_recruitment . '). Silakan tunggu hasil seleksi sebelum melamar kembali.',
            ]);
        }

        // Upload foto
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $fotoName = time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
            $foto->storeAs('recruitment/foto', $fotoName, 'public');
            $fotoPath = $fotoName;
        }

        // Upload CV
        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cv = $request->file('cv');
            $cvName = time() . '_' . uniqid() . '.' . $cv->getClientOriginalExtension();
            $cv->storeAs('recruitment/cv', $cvName, 'public');
            $cvPath = $cvName;
        }

        // Upload Ijazah
        $ijazahPath = null;
        if ($request->hasFile('ijazah')) {
            $ijazah = $request->file('ijazah');
            $ijazahName = time() . '_' . uniqid() . '.' . $ijazah->getClientOriginalExtension();
            $ijazah->storeAs('recruitment/ijazah', $ijazahName, 'public');
            $ijazahPath = $ijazahName;
        }

        $recruitment = Recruitment::create([
            'kode_recruitment'    => Recruitment::generateKode(),
            'nama_lengkap'        => $request->nama_lengkap,
            'tempat_lahir'        => $request->tempat_lahir,
            'tanggal_lahir'       => $request->tanggal_lahir,
            'jenis_kelamin'       => $request->jenis_kelamin,
            'agama'               => $request->agama,
            'status_kawin'        => $request->status_kawin,
            'no_ktp'              => $request->no_ktp,
            'alamat'              => $request->alamat,
            'no_hp'               => $request->no_hp,
            'email'               => $request->email,
            'pendidikan_terakhir' => $request->pendidikan_terakhir,
            'jurusan'             => $request->jurusan,
            'nama_institusi'      => $request->nama_institusi,
            'tahun_lulus'         => $request->tahun_lulus,
            'pengalaman_kerja'    => $request->pengalaman_kerja,
            'keahlian'            => $request->keahlian,
            'kode_cabang'         => $request->kode_cabang ?: null,
            'kode_dept'           => $request->kode_dept ?: null,
            'kode_jabatan'        => $request->kode_jabatan ?: null,
            'posisi_dilamar'      => $request->posisi_dilamar,
            'tanggal_melamar'     => now()->toDateString(),
            'tanggal_tersedia'    => $request->tanggal_tersedia,
            'ekspektasi_gaji'     => $request->ekspektasi_gaji ? str_replace(['.', ','], ['', '.'], $request->ekspektasi_gaji) : null,
            'foto'                => $fotoPath,
            'cv'                  => $cvPath,
            'ijazah'              => $ijazahPath,
            'status'              => 'pending',
        ]);

        // Kirim email konfirmasi jika pelamar punya email
        if ($recruitment->email) {
            try {
                Mail::to($recruitment->email)->send(new RecruitmentConfirmation($recruitment));
            } catch (\Exception $e) {
                // Gagal kirim email tidak menghentikan proses
                \Log::warning('Gagal kirim email recruitment: ' . $e->getMessage());
            }
        }

        // Kirim notifikasi ke semua user yang punya permission recruitment.index
        try {
            $recipients = User::permission('recruitment.index')->get();
            \Illuminate\Support\Facades\Notification::send($recipients, new NewRecruitmentNotification($recruitment));
        } catch (\Exception $e) {
            \Log::warning('Gagal kirim notifikasi recruitment: ' . $e->getMessage());
        }

        // --- NOTIFIKASI PUSH ONESIGNAL (ADMIN CABANG & SUPER ADMIN) ---
        try {
            $pesanPush = "Ada lamaran baru dari " . $recruitment->nama_lengkap . " untuk posisi " . $recruitment->posisi_dilamar . ".";
            $urlPush = rtrim(env('APP_URL'), '/') . '/recruitment';
            sendAdminNotification($recruitment->kode_cabang, $recruitment->kode_dept, "Lamaran Kerja Baru", $pesanPush, $urlPush);
        } catch (\Exception $e) {}
        // --------------------------------------------------------------

        return redirect()->route('recruitment.success')
            ->with('success', 'Lamaran berhasil dikirim! Kami akan menghubungi Anda segera.');
    }

    // ─── PUBLIC: Konfirmasi kehadiran interview ────────────────────────────────
    public function konfirmasiInterview($token, $jawaban)
    {
        $recruitment = Recruitment::where('token_konfirmasi', $token)
            ->where('status', 'interview')
            ->firstOrFail();

        if (!in_array($jawaban, ['hadir', 'tidak_hadir'])) {
            abort(404);
        }

        // Jika sudah pernah konfirmasi, tampilkan status saja
        if ($recruitment->konfirmasi_interview) {
            $sudah = $recruitment->konfirmasi_interview === 'hadir' ? 'HADIR' : 'TIDAK HADIR';
            return view('recruitment.konfirmasi', [
                'recruitment' => $recruitment,
                'jawaban'     => $recruitment->konfirmasi_interview,
                'sudah'       => true,
                'pesan'       => "Anda sudah mengkonfirmasi: {$sudah}",
            ]);
        }

        $recruitment->update([
            'konfirmasi_interview' => $jawaban,
            'konfirmasi_at'        => now(),
        ]);

        return view('recruitment.konfirmasi', [
            'recruitment' => $recruitment,
            'jawaban'     => $jawaban,
            'sudah'       => false,
            'pesan'       => $jawaban === 'hadir'
                ? 'Terima kasih! Kehadiran Anda telah dikonfirmasi. Sampai jumpa di sesi interview.'
                : 'Terima kasih atas konfirmasinya. Kami akan menghubungi Anda untuk penjadwalan ulang.',
        ]);
    }

    // ─── PUBLIC: Halaman sukses setelah daftar ─────────────────────────────────
    public function success()
    {
        return view('recruitment.success');
    }

    // ─── ADMIN: Detail pelamar ─────────────────────────────────────────────────
    public function show($id)
    {
        $recruitment = Recruitment::with(['cabang', 'departemen', 'jabatan', 'prosesOleh'])->findOrFail($id);
        $data['recruitment'] = $recruitment;
        $data['jabatan']     = Jabatan::orderBy('nama_jabatan')->get();
        return view('recruitment.show', $data);
    }

    // ─── ADMIN: Form edit data pelamar ──────────────────────────────────────
    public function edit($id)
    {
        $recruitment = Recruitment::findOrFail($id);
        $data['recruitment'] = $recruitment;
        $data['cabang'] = Cabang::orderBy('nama_cabang')->get();
        $data['departemen'] = Departemen::orderBy('nama_dept')->get();
        $data['jabatan'] = Jabatan::orderBy('nama_jabatan')->get();
        return view('recruitment.edit', $data);
    }

    // ─── ADMIN: Update data pelamar ────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_lengkap'   => 'required|string|max:255',
            'tempat_lahir'   => 'nullable|string|max:100',
            'tanggal_lahir'  => 'nullable|date',
            'jenis_kelamin'  => 'required|in:L,P',
            'no_hp'          => 'required|string|max:20',
            'email'          => 'nullable|email|max:255',
            'alamat'         => 'nullable|string',
            'pendidikan_terakhir' => 'nullable|string',
            'posisi_dilamar' => 'required|string|max:255',
            'foto'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'cv'             => 'nullable|mimes:pdf,doc,docx|max:5120',
            'ijazah'         => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $recruitment = Recruitment::findOrFail($id);

        // Handle foto
        if ($request->hasFile('foto')) {
            if ($recruitment->foto) {
                Storage::disk('public')->delete('recruitment/foto/' . $recruitment->foto);
            }
            $foto = $request->file('foto');
            $fotoName = time() . '_' . uniqid() . '.' . $foto->getClientOriginalExtension();
            $foto->storeAs('recruitment/foto', $fotoName, 'public');
            $recruitment->foto = $fotoName;
        }

        // Handle CV
        if ($request->hasFile('cv')) {
            if ($recruitment->cv) {
                Storage::disk('public')->delete('recruitment/cv/' . $recruitment->cv);
            }
            $cv = $request->file('cv');
            $cvName = time() . '_' . uniqid() . '.' . $cv->getClientOriginalExtension();
            $cv->storeAs('recruitment/cv', $cvName, 'public');
            $recruitment->cv = $cvName;
        }

        // Handle Ijazah
        if ($request->hasFile('ijazah')) {
            if ($recruitment->ijazah) {
                Storage::disk('public')->delete('recruitment/ijazah/' . $recruitment->ijazah);
            }
            $ijazah = $request->file('ijazah');
            $ijazahName = time() . '_' . uniqid() . '.' . $ijazah->getClientOriginalExtension();
            $ijazah->storeAs('recruitment/ijazah', $ijazahName, 'public');
            $recruitment->ijazah = $ijazahName;
        }

        $recruitment->fill([
            'nama_lengkap'        => $request->nama_lengkap,
            'tempat_lahir'        => $request->tempat_lahir,
            'tanggal_lahir'       => $request->tanggal_lahir,
            'jenis_kelamin'       => $request->jenis_kelamin,
            'agama'               => $request->agama,
            'status_kawin'        => $request->status_kawin,
            'no_ktp'              => $request->no_ktp,
            'alamat'              => $request->alamat,
            'no_hp'               => $request->no_hp,
            'email'               => $request->email,
            'pendidikan_terakhir' => $request->pendidikan_terakhir,
            'jurusan'             => $request->jurusan,
            'nama_institusi'      => $request->nama_institusi,
            'tahun_lulus'         => $request->tahun_lulus,
            'pengalaman_kerja'    => $request->pengalaman_kerja,
            'keahlian'            => $request->keahlian,
            'kode_cabang'         => $request->kode_cabang ?: null,
            'kode_dept'           => $request->kode_dept ?: null,
            'kode_jabatan'        => $request->kode_jabatan ?: null,
            'posisi_dilamar'      => $request->posisi_dilamar,
            'tanggal_tersedia'    => $request->tanggal_tersedia,
            'ekspektasi_gaji'     => $request->ekspektasi_gaji ? str_replace(['.', ','], ['', '.'], $request->ekspektasi_gaji) : null,
        ]);

        $recruitment->save();

        return redirect()->route('recruitment.show', $id)
            ->with('success', 'Data pelamar berhasil diperbarui.');
    }

    // ─── ADMIN: Update status & catatan ───────────────────────────────────────
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'            => 'required|in:pending,review,interview,offering,diterima,ditolak',
            'catatan_hr'        => 'nullable|string',
            'catatan_interview' => 'nullable|string',
            'tanggal_interview' => 'nullable|date',
            'jam_interview'     => 'nullable|date_format:H:i',
        ]);

        $recruitment = Recruitment::findOrFail($id);
        $recruitment->update([
            'status'            => $request->status,
            'catatan_hr'        => $request->catatan_hr,
            'catatan_interview' => $request->catatan_interview,
            'tanggal_interview' => $request->tanggal_interview,
            'jam_interview'     => $request->jam_interview,
            'diproses_oleh'     => Auth::id(),
        ]);
        // Generate token konfirmasi baru jika status interview
        if ($request->status === 'interview') {
            $recruitment->token_konfirmasi  = \Illuminate\Support\Str::random(40);
            $recruitment->konfirmasi_interview = null;
            $recruitment->konfirmasi_at        = null;
            $recruitment->save();
        }

        $recruitment->load('cabang');

        $notifInfo = [];

        // ── Kirim Email ────────────────────────────────────────────────────────
        if ($recruitment->email) {
            try {
                Mail::to($recruitment->email)->send(new RecruitmentStatusUpdated($recruitment));
                $notifInfo[] = 'email';
            } catch (\Exception $e) {
                \Log::warning('Gagal kirim email update status recruitment: ' . $e->getMessage());
            }
        }

        // ── Kirim WhatsApp ─────────────────────────────────────────────────────
        $setting = Pengaturanumum::first();
        if ($recruitment->no_hp && $setting && $setting->notifikasi_wa) {
            try {
                $waMessage = $this->buildWaStatusMessage($recruitment);
                    SendWaMessage::dispatch($recruitment->no_hp, $waMessage, false, true, 'recruitment'); // direct=true → kirim ke nomor pelamar langsung
                $notifInfo[] = 'WhatsApp';
            } catch (\Exception $e) {
                \Log::warning('Gagal dispatch WA recruitment: ' . $e->getMessage());
            }
        }

        $notifText = !empty($notifInfo)
            ? ' Notifikasi via ' . implode(' & ', $notifInfo) . ' dikirim ke pelamar.'
            : '';

        return redirect()->route('recruitment.show', $id)
            ->with('success', 'Status lamaran berhasil diperbarui.' . $notifText);
    }

    /**
     * Buat pesan WA untuk notifikasi update status pelamar
     */
    private function buildWaStatusMessage(Recruitment $recruitment): string
    {
        $statusMap = [
            'pending'   => '⏳ *Menunggu Review*',
            'review'    => '🔍 *Sedang Direview*',
            'interview' => '📅 *Dipanggil Interview*',
            'offering'  => '💼 *Penawaran Kerja*',
            'diterima'  => '✅ *DITERIMA*',
            'ditolak'   => '❌ *Tidak Lolos Seleksi*',
        ];

        $statusLabel = $statusMap[$recruitment->status] ?? strtoupper($recruitment->status);
        $setting     = Pengaturanumum::first();
        $appName     = $setting->nama_perusahaan ?? config('app.name', 'PT DIDIMAX BERJANGKA');
        $cabang      = $recruitment->cabang->nama_cabang ?? '-';
        $alamatCabang = $recruitment->cabang->alamat_cabang ?? '-';

        $msg  = "━━━━━━━━━━━━━━━━━\n";
        $msg .= "📢 *UPDATE LAMARAN KERJA*\n";
        $msg .= "━━━━━━━━━━━━━━━━━\n\n";
        $msg .= "Halo Yth, *{$recruitment->nama_lengkap}*,\n\n";
        $msg .= "Berikut update status lamaran Anda:\n\n";
        $msg .= "🏢 *Perusahaan:* {$appName}\n";
        $msg .= "📍 *Cabang:* {$cabang}\n";
        $msg .= "📍*Alamat:* {$alamatCabang}\n";
        $msg .= "💼 *Posisi:* {$recruitment->posisi_dilamar}\n";
        $msg .= "🔖 *Kode Lamaran:* {$recruitment->kode_recruitment}\n";
        $msg .= "📊 *Status:* {$statusLabel}\n";

        // Tambahkan info jadwal interview
        if ($recruitment->status === 'interview' && $recruitment->tanggal_interview) {
            $tgl = \Carbon\Carbon::parse($recruitment->tanggal_interview)->translatedFormat('l, d F Y');
            $msg .= "\n📅 *Jadwal Interview:* {$tgl}\n";
            if ($recruitment->jam_interview) {
                $jam = \Carbon\Carbon::parse($recruitment->jam_interview)->format('H:i');
                $msg .= "⏰ *Jam:* {$jam} WIB\n";
            }

            // Link konfirmasi kehadiran
            if ($recruitment->token_konfirmasi) {
                $linkHadir      = url("/recruitment/konfirmasi/{$recruitment->token_konfirmasi}/hadir");
                $linkTidakHadir = url("/recruitment/konfirmasi/{$recruitment->token_konfirmasi}/tidak_hadir");
                $msg .= "\n✅ *Konfirmasi Kehadiran:*\n";
                $msg .= "👍 Hadir: {$linkHadir}\n";
                $msg .= "👎 Tidak Hadir: {$linkTidakHadir}\n";
            }
        }

        // Tambahkan catatan jika ada
        if ($recruitment->catatan_interview) {
            $msg .= "\n📝 *Catatan:*\n{$recruitment->catatan_interview}\n";
        }

        // Pesan penutup per status
        $closing = [
            'review'    => "\nLamaran Anda sedang kami review. Kami akan segera menghubungi Anda.",
            'interview' => "\nMohon hadir tepat waktu sesuai jadwal. Memakai pakaian rapi. Bawa dokumen asli, FC KTP dan CV terbaru.",
            'offering'  => "\nSelamat! Anda lolos seleksi. Tim HR kami akan segera menghubungi untuk detail penawaran.",
            'diterima'  => "\n🎉 *Selamat!* Anda resmi bergabung bersama kami. Silakan tunggu info selanjutnya dari HR.",
            'ditolak'   => "\nTerima kasih telah melamar. Semoga sukses di kesempatan berikutnya.",
        ];
        if (isset($closing[$recruitment->status])) {
            $msg .= $closing[$recruitment->status] . "\n";
        }

        $msg .= "\n_Pesan ini dikirim otomatis oleh sistem {$appName}_";

        return $msg;
    }

    // ─── ADMIN: Hapus lamaran ──────────────────────────────────────────────────
    public function destroy($id)
    {
        $recruitment = Recruitment::findOrFail($id);

        // Hapus file uploads
        if ($recruitment->foto) {
            Storage::disk('public')->delete('recruitment/foto/' . $recruitment->foto);
        }
        if ($recruitment->cv) {
            Storage::disk('public')->delete('recruitment/cv/' . $recruitment->cv);
        }
        if ($recruitment->ijazah) {
            Storage::disk('public')->delete('recruitment/ijazah/' . $recruitment->ijazah);
        }

        $recruitment->delete();

        return redirect()->route('recruitment.index')
            ->with('success', 'Data lamaran berhasil dihapus.');
    }

    // ─── AJAX: Get jabatan by departemen ──────────────────────────────────────
    public function getJabatan(Request $request)
    {
        $jabatan = Jabatan::when($request->kode_dept, function ($q) use ($request) {
            $q->where('kode_dept', $request->kode_dept);
        })->orderBy('nama_jabatan')->get();

        return response()->json($jabatan);
    }
}

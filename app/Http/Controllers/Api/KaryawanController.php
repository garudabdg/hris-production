<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Denda;
use App\Models\Izinabsen;
use App\Models\Izincuti;
use App\Models\Izindinas;
use App\Models\Izinsakit;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\Pengaturanumum;
use App\Models\Pengumuman;
use App\Models\Presensi;
use App\Models\User;
use App\Models\Userkaryawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KaryawanController extends Controller
{
    // =============================================
    // AUTH
    // =============================================

    /**
     * POST /api/karyawan/login
     * Body: { id_user, password, device_name }
     *   id_user : bisa email atau username (sama seperti form login web)
     */
    public function login(Request $request)
    {
        $request->validate([
            'id_user'     => 'required|string',
            'password'    => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->id_user) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . ceil($seconds / 60) . ' menit.',
            ], 429);
        }

        // Cek apakah id_user berupa email atau username (sama seperti LoginRequest web)
        $field = filter_var($request->id_user, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $request->id_user)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey);
            return response()->json([
                'success' => false,
                'message' => 'Username/email atau password salah.',
            ], 401);
        }

        RateLimiter::clear($throttleKey);

        if (!$user->hasRole('karyawan')) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini bukan akun karyawan.',
            ], 403);
        }

        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userkaryawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        $karyawan = Karyawan::where('nik', $userkaryawan->nik)
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();

        // Hapus token lama (opsional — uncomment jika 1 device 1 token)
        // $user->tokens()->delete();

        $deviceName = $request->device_name ?? 'mobile';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token'     => $token,
                'user'      => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'username' => $user->username,
                    'email'    => $user->email,
                ],
                'karyawan' => $this->formatKaryawan($karyawan),
            ],
        ]);
    }

    /**
     * POST /api/karyawan/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    // =============================================
    // DASHBOARD
    // =============================================

    /**
     * GET /api/karyawan/dashboard
     */
    public function dashboard(Request $request)
    {
        $user        = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $hari_ini    = Carbon::now(config('app.timezone'))->format('Y-m-d');

        $karyawan = Karyawan::where('karyawan.nik', $userkaryawan->nik)
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();

        // Presensi hari ini
        $presensi = Presensi::where('nik', $userkaryawan->nik)
            ->where('tanggal', $hari_ini)
            ->first();

        // Rekap bulan ini
        $rekap = Presensi::select(
            DB::raw("SUM(IF(status='h',1,0)) as hadir"),
            DB::raw("SUM(IF(status='i',1,0)) as izin"),
            DB::raw("SUM(IF(status='s',1,0)) as sakit"),
            DB::raw("SUM(IF(status='a',1,0)) as alpa"),
            DB::raw("SUM(IF(status='c',1,0)) as cuti")
        )
            ->where('nik', $userkaryawan->nik)
            ->whereRaw('MONTH(tanggal) = MONTH(?)', [$hari_ini])
            ->whereRaw('YEAR(tanggal) = YEAR(?)', [$hari_ini])
            ->first();

        // Lembur
        $hideLembur = $karyawan && $karyawan->kode_dept == 'BU';
        $notiflembur = 0;
        if (!$hideLembur) {
            $notiflembur = Lembur::where('nik', $userkaryawan->nik)
                ->where('status', 1)
                ->where(function ($q) {
                    $q->whereNull('lembur_in')->orWhereNull('lembur_out');
                })
                ->count();
        }

        // Notif kontrak
        $notif_kontrak = null;
        $kontrak = DB::table('kontrak')
            ->where('nik', $userkaryawan->nik)
            ->where('status_kontrak', '1')
            ->orderBy('sampai', 'desc')
            ->first();
        if ($kontrak) {
            $tgl_akhir  = Carbon::parse($kontrak->sampai);
            $today      = Carbon::now(config('app.timezone'));
            $sisa_hari  = $today->diffInDays($tgl_akhir, false);
            if ($sisa_hari >= 0 && $sisa_hari <= 30) {
                $notif_kontrak = [
                    'sisa_hari'     => $sisa_hari,
                    'tanggal_akhir' => $tgl_akhir->translatedFormat('d F Y'),
                ];
            }
        }

        // Ulang tahun
        $is_birthday = false;
        $umur = null;
        if ($karyawan && $karyawan->tanggal_lahir) {
            $lahir = Carbon::parse($karyawan->tanggal_lahir);
            $today = Carbon::now();
            if ($lahir->month == $today->month && $lahir->day == $today->day) {
                $is_birthday = true;
                $umur = $lahir->age;
            }
        }

        // Pengumuman
        $pengumuman = Pengumuman::orderBy('created_at', 'desc')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'karyawan'      => $this->formatKaryawan($karyawan),
                'presensi_hari_ini' => $presensi ? [
                    'jam_in'  => $presensi->jam_in  ? date('H:i', strtotime($presensi->jam_in))  : null,
                    'jam_out' => $presensi->jam_out ? date('H:i', strtotime($presensi->jam_out)) : null,
                    'foto_in'  => $presensi->foto_in,
                    'foto_out' => $presensi->foto_out,
                    'status'   => $presensi->status,
                ] : null,
                'rekap_bulan_ini' => [
                    'hadir' => (int) ($rekap->hadir ?? 0),
                    'izin'  => (int) ($rekap->izin  ?? 0),
                    'sakit' => (int) ($rekap->sakit  ?? 0),
                    'alpa'  => (int) ($rekap->alpa  ?? 0),
                    'cuti'  => (int) ($rekap->cuti  ?? 0),
                    'bulan' => Carbon::parse($hari_ini)->translatedFormat('F Y'),
                ],
                'lembur' => [
                    'hide'         => $hideLembur,
                    'notif_count'  => $notiflembur,
                ],
                'notif_kontrak' => $notif_kontrak,
                'is_birthday'   => $is_birthday,
                'umur'          => $umur,
                'pengumuman'    => $pengumuman ? [
                    'judul'      => $pengumuman->judul,
                    'isi'        => strip_tags($pengumuman->isi),
                    'created_at' => Carbon::parse($pengumuman->created_at)->translatedFormat('d F Y'),
                ] : null,
            ],
        ]);
    }

    // =============================================
    // PRESENSI
    // =============================================

    /**
     * GET /api/karyawan/presensi
     * Query: bulan (Y-m), limit (default 30)
     */
    public function presensi(Request $request)
    {
        $user         = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();

        $bulan = $request->bulan ?? date('Y-m');
        [$tahun, $bln] = explode('-', $bulan);

        $list = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('presensi.nik', $userkaryawan->nik)
            ->whereYear('presensi.tanggal', $tahun)
            ->whereMonth('presensi.tanggal', $bln)
            ->leftJoin('presensi_izinabsen_approve', 'presensi.id', '=', 'presensi_izinabsen_approve.id_presensi')
            ->leftJoin('presensi_izinabsen', 'presensi_izinabsen_approve.kode_izin', '=', 'presensi_izinabsen.kode_izin')
            ->leftJoin('presensi_izinsakit_approve', 'presensi.id', '=', 'presensi_izinsakit_approve.id_presensi')
            ->leftJoin('presensi_izinsakit', 'presensi_izinsakit_approve.kode_izin_sakit', '=', 'presensi_izinsakit.kode_izin_sakit')
            ->leftJoin('presensi_izincuti_approve', 'presensi.id', '=', 'presensi_izincuti_approve.id_presensi')
            ->leftJoin('presensi_izincuti', 'presensi_izincuti_approve.kode_izin_cuti', '=', 'presensi_izincuti.kode_izin_cuti')
            ->select(
                'presensi.*',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.total_jam',
                'presensi_jamkerja.lintashari',
                'presensi_izinabsen.keterangan as keterangan_izin',
                'presensi_izinsakit.keterangan as keterangan_izin_sakit',
                'presensi_izincuti.keterangan as keterangan_izin_cuti',
            )
            ->orderBy('presensi.tanggal', 'desc')
            ->get()
            ->map(function ($d) {
                return [
                    'tanggal'         => $d->tanggal,
                    'status'          => $d->status,
                    'jam_in'          => $d->jam_in  ? date('H:i', strtotime($d->jam_in))  : null,
                    'jam_out'         => $d->jam_out ? date('H:i', strtotime($d->jam_out)) : null,
                    'jam_masuk'       => date('H:i', strtotime($d->jam_masuk)),
                    'jam_pulang'      => date('H:i', strtotime($d->jam_pulang)),
                    'nama_jam_kerja'  => $d->nama_jam_kerja,
                    'keterangan_izin' => $d->keterangan_izin ?? $d->keterangan_izin_sakit ?? $d->keterangan_izin_cuti,
                    'foto_in'         => $d->foto_in,
                    'foto_out'        => $d->foto_out,
                    'denda'           => $d->denda,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $list,
        ]);
    }

    /**
     * GET /api/karyawan/rekap
     * Query: bulan (Y-m)
     */
    public function rekap(Request $request)
    {
        $user         = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();

        $bulan = $request->bulan ?? date('Y-m');
        [$tahun, $bln] = explode('-', $bulan);

        $rekap = Presensi::select(
            DB::raw("SUM(IF(status='h',1,0)) as hadir"),
            DB::raw("SUM(IF(status='i',1,0)) as izin"),
            DB::raw("SUM(IF(status='s',1,0)) as sakit"),
            DB::raw("SUM(IF(status='a',1,0)) as alpa"),
            DB::raw("SUM(IF(status='c',1,0)) as cuti")
        )
            ->where('nik', $userkaryawan->nik)
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bln)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'bulan' => Carbon::createFromDate($tahun, $bln)->translatedFormat('F Y'),
                'hadir' => (int) ($rekap->hadir ?? 0),
                'izin'  => (int) ($rekap->izin  ?? 0),
                'sakit' => (int) ($rekap->sakit  ?? 0),
                'alpa'  => (int) ($rekap->alpa  ?? 0),
                'cuti'  => (int) ($rekap->cuti  ?? 0),
            ],
        ]);
    }

    // =============================================
    // LEMBUR
    // =============================================

    /**
     * GET /api/karyawan/lembur
     * Query: dari, sampai, page
     */
    public function lembur(Request $request)
    {
        $user         = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();

        $query = Lembur::where('lembur.nik', $userkaryawan->nik)
            ->orderBy('lembur.tanggal', 'desc');

        if ($request->dari && $request->sampai) {
            $query->whereBetween('tanggal', [$request->dari, $request->sampai]);
        }

        $list = $query->paginate(15)->through(function ($d) {
            $start    = strtotime($d->lembur_mulai);
            $end      = strtotime($d->lembur_selesai);
            $diff     = $end - $start;
            $hours    = floor($diff / 3600);
            $minutes  = floor(($diff % 3600) / 60);

            return [
                'id'            => $d->id,
                'tanggal'       => $d->tanggal,
                'keterangan'    => $d->keterangan,
                'lembur_mulai'  => date('H:i', strtotime($d->lembur_mulai)),
                'lembur_selesai'=> date('H:i', strtotime($d->lembur_selesai)),
                'durasi'        => $hours . 'j' . ($minutes > 0 ? ' ' . $minutes . 'm' : ''),
                'lembur_in'     => $d->lembur_in  ? date('H:i', strtotime($d->lembur_in))  : null,
                'lembur_out'    => $d->lembur_out ? date('H:i', strtotime($d->lembur_out)) : null,
                'status'        => (int) $d->status,
                'approval_step' => $d->approval_step,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $list,
        ]);
    }

    // =============================================
    // PENGAJUAN IZIN
    // =============================================

    /**
     * GET /api/karyawan/pengajuan-izin
     * Query: status (0/1/2), page
     */
    public function pengajuanIzin(Request $request)
    {
        $user         = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $nik          = $userkaryawan->nik;

        $izinabsen = Izinabsen::where('nik', $nik)
            ->select('kode_izin as kode', 'tanggal', 'keterangan', 'dari', 'sampai',
                DB::raw("'Izin Absen' as jenis"), 'status as status_izin', 'approval_step');

        $izinsakit = Izinsakit::where('nik', $nik)
            ->select('kode_izin_sakit as kode', 'tanggal', 'keterangan', 'dari', 'sampai',
                DB::raw("'Izin Sakit' as jenis"), 'status as status_izin', 'approval_step');

        $izincuti = Izincuti::where('nik', $nik)
            ->select('kode_izin_cuti as kode', 'tanggal', 'keterangan', 'dari', 'sampai',
                DB::raw("'Cuti' as jenis"), 'status as status_izin', 'approval_step');

        $izindinas = Izindinas::where('nik', $nik)
            ->select('kode_izin_dinas as kode', 'tanggal', 'keterangan', 'dari', 'sampai',
                DB::raw("'Izin Dinas' as jenis"), 'status as status_izin', 'approval_step');

        $query = $izinabsen->union($izinsakit)->union($izincuti)->union($izindinas)
            ->orderBy('tanggal', 'desc');

        if ($request->has('status') && $request->status !== '') {
            $query = DB::table(DB::raw("({$query->toSql()}) as izin"))
                ->mergeBindings($query->getQuery())
                ->where('status_izin', $request->status)
                ->orderBy('tanggal', 'desc');
        }

        $list = DB::table(DB::raw("({$izinabsen->union($izinsakit)->union($izincuti)->union($izindinas)->orderBy('tanggal', 'desc')->toSql()}) as izin"))
            ->mergeBindings($izinabsen->union($izinsakit)->union($izincuti)->union($izindinas)->getQuery())
            ->when($request->has('status') && $request->status !== '', function ($q) use ($request) {
                $q->where('status_izin', $request->status);
            })
            ->orderBy('tanggal', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $list,
        ]);
    }

    // =============================================
    // PROFIL
    // =============================================

    /**
     * GET /api/karyawan/profil
     */
    public function profil(Request $request)
    {
        $user         = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $karyawan     = Karyawan::where('karyawan.nik', $userkaryawan->nik)
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();

        return response()->json([
            'success' => true,
            'data' => array_merge($this->formatKaryawan($karyawan), [
                'no_ktp'         => $karyawan->no_ktp,
                'no_hp'          => $karyawan->no_hp,
                'alamat'         => $karyawan->alamat,
                'tanggal_lahir'  => $karyawan->tanggal_lahir,
                'tempat_lahir'   => $karyawan->tempat_lahir,
                'jenis_kelamin'  => $karyawan->jenis_kelamin,
                'tanggal_masuk'  => $karyawan->tanggal_masuk,
                'status_karyawan'=> $karyawan->status_karyawan,
                'email'          => $user->email,
                'username'       => $user->username,
                'foto_url'       => !empty($karyawan->foto) && Storage::disk('public')->exists('/karyawan/' . $karyawan->foto)
                    ? url(Storage::url('karyawan/' . $karyawan->foto))
                    : null,
            ]),
        ]);
    }

    /**
     * PUT /api/karyawan/profil
     */
    public function updateProfil(Request $request)
    {
        $user         = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $karyawan     = Karyawan::where('nik', $userkaryawan->nik)->first();

        $request->validate([
            'nama_karyawan' => 'required|string|max:100',
            'no_ktp'        => 'nullable|string|max:20',
            'no_hp'         => 'nullable|string|max:20',
            'alamat'        => 'nullable|string|max:500',
            'tanggal_lahir' => 'nullable|date',
            'foto'          => 'nullable|file|mimes:jpg,jpeg,png|max:20480',
            'email'         => 'nullable|email|max:100',
        ]);

        try {
            $data_foto = [];
            if ($request->hasFile('foto')) {
                $foto_name            = $karyawan->nik . '.' . $request->file('foto')->getClientOriginalExtension();
                $destination_foto_path = '/public/karyawan';
                $data_foto             = ['foto' => $foto_name];

                if (!Storage::exists($destination_foto_path)) {
                    Storage::makeDirectory($destination_foto_path, 0775, true);
                }
                Storage::delete($destination_foto_path . '/' . $karyawan->foto);
                $request->file('foto')->storeAs($destination_foto_path, $foto_name);
            }

            Karyawan::where('nik', $karyawan->nik)->update(array_merge([
                'nama_karyawan' => $request->nama_karyawan,
                'no_ktp'        => $request->no_ktp,
                'no_hp'         => $request->no_hp,
                'alamat'        => $request->alamat,
                'tanggal_lahir' => $request->tanggal_lahir,
            ], $data_foto));

            User::where('id', $user->id)->update([
                'name'  => $request->nama_karyawan,
                'email' => $request->email ?? $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data.',
            ], 500);
        }
    }

    // =============================================
    // NOTIFIKASI
    // =============================================

    /**
     * GET /api/karyawan/notifikasi
     */
    public function notifikasi(Request $request)
    {
        $user = $request->user();

        $notifs = \Illuminate\Notifications\DatabaseNotification::where('notifiable_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get()
            ->map(function ($n) {
                return [
                    'id'            => $n->id,
                    'is_read'       => !is_null($n->read_at),
                    'type'          => $n->data['type']          ?? null,
                    'title'         => $n->data['title']         ?? null,
                    'message'       => $n->data['message']       ?? null,
                    'approval_type' => $n->data['approval_type'] ?? null,
                    'status'        => $n->data['status']        ?? null,
                    'approver_name' => $n->data['approver_name'] ?? null,
                    'created_at'    => Carbon::parse($n->created_at)->diffForHumans(),
                ];
            });

        $unread = \Illuminate\Notifications\DatabaseNotification::where('notifiable_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count'  => $unread,
                'notifications' => $notifs,
            ],
        ]);
    }

    /**
     * POST /api/karyawan/notifikasi/read-all
     */
    public function readAllNotifikasi(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi telah dibaca.',
        ]);
    }

    // =============================================
    // HELPER
    // =============================================

    private function formatKaryawan($karyawan): array
    {
        if (!$karyawan) return [];

        return [
            'nik'          => $karyawan->nik,
            'nik_show'     => $karyawan->nik_show ?? $karyawan->nik,
            'nama'         => $karyawan->nama_karyawan,
            'jabatan'      => $karyawan->nama_jabatan ?? null,
            'departemen'   => $karyawan->nama_dept    ?? null,
            'cabang'       => $karyawan->nama_cabang  ?? null,
            'kode_dept'    => $karyawan->kode_dept    ?? null,
            'tanggal_lahir'=> $karyawan->tanggal_lahir ?? null,
            'foto_url'     => !empty($karyawan->foto) && Storage::disk('public')->exists('/karyawan/' . $karyawan->foto)
                ? url(Storage::url('karyawan/' . $karyawan->foto))
                : null,
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AjuanJadwal;
use App\Models\Cabang;
use App\Models\Detailharilibur;
use App\Models\Detailsetjamkerjabydept;
use App\Models\Facerecognition;
use App\Models\GrupDetail;
use App\Models\GrupJamkerjaBydate;
use App\Models\Izindinas;
use App\Models\Jamkerja;
use App\Models\Karyawan;
use App\Models\Pengaturanumum;
use App\Models\Presensi;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use App\Models\Userkaryawan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PresensiKaryawanController extends Controller
{
    protected $presensiService;

    public function __construct(\App\Services\PresensiService $presensiService)
    {
        $this->presensiService = $presensiService;
    }
    // ──────────────────────────────────────────────────────────────────────
    // GET /api/karyawan/presensi/info
    // Ambil info presensi hari ini + jam kerja yang berlaku.
    // Dipakai untuk menampilkan halaman absen sebelum submit.
    // ──────────────────────────────────────────────────────────────────────
    public function info(Request $request)
    {
        [$karyawan, $userkaryawan, $error] = $this->getKaryawan($request);
        if ($error) return $error;

        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $cabang         = Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();
        $timezone       = $cabang->timezone ?? $generalsetting->timezone ?? config('app.timezone');
        $now            = Carbon::now($timezone);
        $hariini        = $now->format('Y-m-d');

        // Cek lintas hari dari presensi kemarin
        $kemarin = $now->copy()->subDay()->format('Y-m-d');
        $presensiKemarin = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('presensi.nik', $karyawan->nik)
            ->where('presensi.tanggal', $kemarin)
            ->first();

        if ($presensiKemarin && $presensiKemarin->lintashari == 1) {
            if ($now->format('H:i') < $generalsetting->batas_presensi_lintashari) {
                $hariini = $kemarin;
            }
        }

        $namahari   = getnamaHari(date('D', strtotime($hariini)));
        $presensi   = Presensi::where('nik', $karyawan->nik)->where('tanggal', $hariini)->first();
        $jamkerja   = $this->resolveJamKerja($karyawan, $hariini, $namahari);
        $ceklibur   = Detailharilibur::join('hari_libur', 'hari_libur_detail.kode_libur', '=', 'hari_libur.kode_libur')
            ->where('nik', $karyawan->nik)
            ->where('tanggal', $hariini)
            ->first();

        // Cek izin yang disetujui hari ini
        $izinAktif = null;
        if ($presensi && $presensi->status != 'h') {
            $izinAktif = $presensi->status;
        }

        return response()->json([
            'success'         => true,
            'tanggal'         => $hariini,
            'jam_sekarang'    => $now->format('H:i'),
            'timezone'        => $timezone,
            'presensi_hari_ini' => $presensi ? [
                'id'          => $presensi->id,
                'jam_in'      => $presensi->jam_in,
                'jam_out'     => $presensi->jam_out,
                'foto_in'     => $presensi->foto_in ? url('/storage/uploads/absensi/' . $presensi->foto_in) : null,
                'foto_out'    => $presensi->foto_out ? url('/storage/uploads/absensi/' . $presensi->foto_out) : null,
                'status'      => $presensi->status,
            ] : null,
            'jam_kerja'       => $jamkerja ? [
                'kode_jam_kerja'      => $jamkerja->kode_jam_kerja,
                'nama_jam_kerja'      => $jamkerja->nama_jam_kerja,
                'jam_masuk'           => $jamkerja->jam_masuk,
                'jam_pulang'          => $jamkerja->jam_pulang,
                'lintashari'          => $jamkerja->lintashari,
                'istirahat'           => $jamkerja->istirahat,
                'jam_awal_istirahat'  => $jamkerja->jam_awal_istirahat ?? null,
                'jam_akhir_istirahat' => $jamkerja->jam_akhir_istirahat ?? null,
            ] : null,
            'lokasi_kantor'   => $cabang ? [
                'nama_cabang'    => $cabang->nama_cabang,
                'lokasi_cabang'  => $cabang->lokasi_cabang,
                'radius_cabang'  => $cabang->radius_cabang,
            ] : null,
            'wajah_terdaftar' => Facerecognition::where('nik', $karyawan->nik)->count(),
            'libur'           => $ceklibur ? true : false,
            'keterangan_libur'=> $ceklibur ? $ceklibur->nama_libur ?? null : null,
            'izin_aktif'      => $izinAktif,
            'lock_location'   => (bool) $karyawan->lock_location,
            'batasi_absen'    => (bool) $generalsetting->batasi_absen,
            'batas_jam_absen' => $generalsetting->batas_jam_absen,
            'batas_jam_absen_pulang' => $generalsetting->batas_jam_absen_pulang,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // GET /api/karyawan/presensi/jamkerja
    // Ambil daftar pilihan jam kerja (jika karyawan boleh pilih sendiri)
    // ──────────────────────────────────────────────────────────────────────
    public function jamKerjaList(Request $request)
    {
        [$karyawan, $userkaryawan, $error] = $this->getKaryawan($request);
        if ($error) return $error;

        if ($karyawan->lock_jam_kerja == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Jam kerja karyawan sudah dikunci, tidak perlu memilih.',
            ], 422);
        }

        $jamkerja = Jamkerja::orderBy('jam_masuk')->get()->map(fn($j) => [
            'kode_jam_kerja'      => $j->kode_jam_kerja,
            'nama_jam_kerja'      => $j->nama_jam_kerja,
            'jam_masuk'           => $j->jam_masuk,
            'jam_pulang'          => $j->jam_pulang,
            'lintashari'          => $j->lintashari,
        ]);

        return response()->json(['success' => true, 'data' => $jamkerja]);
    }

    // ──────────────────────────────────────────────────────────────────────
    // POST /api/karyawan/presensi
    // Submit absen masuk (status=1) atau pulang (status=2).
    //
    // Body (multipart/form-data atau JSON):
    //   status          : 1 (masuk) | 2 (pulang)
    //   kode_jam_kerja  : kode jam kerja yang dipilih
    //   lokasi          : "lat,lng"  lokasi user
    //   lokasi_cabang   : "lat,lng"  lokasi cabang yang dipilih
    //   image           : base64 string ATAU file upload (key=image)
    // ──────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'status'          => 'required|in:1,2',
            'kode_jam_kerja'  => 'required|string',
            'lokasi'          => 'required|string',
            'lokasi_cabang'   => 'required|string',
        ]);

        [$karyawan, $userkaryawan, $error] = $this->getKaryawan($request);
        if ($error) return $error;

        $result = $this->presensiService->prosesPresensiMobile($request, $karyawan);
        
        $statusCode = $result['status_code'] ?? 200;
        unset($result['status_code']);

        return response()->json($result, $statusCode);
    }

    // ──────────────────────────────────────────────────────────────────────
    // POST /api/karyawan/presensi/istirahat
    // Submit absen istirahat: status=1 (mulai) | status=2 (selesai)
    // ──────────────────────────────────────────────────────────────────────
    public function istirahat(Request $request)
    {
        $request->validate([
            'status' => 'required|in:1,2',
            'lokasi' => 'nullable|string',
        ]);

        [$karyawan, $userkaryawan, $error] = $this->getKaryawan($request);
        if ($error) return $error;

        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $cabang         = Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();
        $timezone       = $cabang->timezone ?? $generalsetting->timezone ?? config('app.timezone');
        $now            = Carbon::now($timezone);
        $hariini        = $now->format('Y-m-d');

        $presensi = Presensi::where('nik', $karyawan->nik)->where('tanggal', $hariini)->first();

        if (!$presensi || $presensi->jam_in == null) {
            return response()->json(['success' => false, 'message' => 'Anda belum absen masuk hari ini.'], 400);
        }

        $jamIstirahat = $now->format('Y-m-d H:i');
        $lokasi       = $request->input('lokasi');

        if ($request->status == 1) {
            if ($presensi->istirahat_in != null) {
                return response()->json(['success' => false, 'message' => 'Anda sudah mulai istirahat.'], 400);
            }
            Presensi::where('id', $presensi->id)->update([
                'istirahat_in'        => $jamIstirahat,
                'lokasi_istirahat_in' => $lokasi,
            ]);
            return response()->json(['success' => true, 'message' => 'Berhasil mulai istirahat.', 'jam_istirahat_in' => $jamIstirahat]);
        } else {
            if ($presensi->istirahat_in == null) {
                return response()->json(['success' => false, 'message' => 'Anda belum mulai istirahat.'], 400);
            }
            if ($presensi->istirahat_out != null) {
                return response()->json(['success' => false, 'message' => 'Anda sudah selesai istirahat.'], 400);
            }
            Presensi::where('id', $presensi->id)->update([
                'istirahat_out'        => $jamIstirahat,
                'lokasi_istirahat_out' => $lokasi,
            ]);
            return response()->json(['success' => true, 'message' => 'Berhasil selesai istirahat.', 'jam_istirahat_out' => $jamIstirahat]);
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Resolve jam kerja yang berlaku untuk karyawan pada tanggal tertentu.
     * Sama seperti logika di PresensiController::create().
     */
    private function resolveJamKerja(Karyawan $karyawan, string $hariini, string $namahari)
    {
        return $this->presensiService->getJamKerjaKaryawan($karyawan, $hariini);
    }

    private function getKaryawan(Request $request): array
    {
        $user         = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        if (!$userkaryawan) {
            return [null, null, response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404)];
        }
        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();
        if (!$karyawan) {
            return [null, null, response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404)];
        }
        return [$karyawan, $userkaryawan, null];
    }
}

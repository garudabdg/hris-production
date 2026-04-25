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
use App\Jobs\SendWaMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PresensiKaryawanController extends Controller
{
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

        $generalsetting    = Pengaturanumum::where('id', 1)->first();
        $cabang            = Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();
        $timezone          = $cabang->timezone ?? $generalsetting->timezone ?? config('app.timezone');
        $now               = Carbon::now($timezone);
        $tanggalSekarang   = $now->format('Y-m-d');
        $jamSekarang       = $now->format('H:i');
        $tanggalKemarin    = $now->copy()->subDay()->format('Y-m-d');
        $tanggalBesok      = $now->copy()->addDay()->format('Y-m-d');

        // Resolusi tanggal presensi (lintas hari)
        $presensiKemarin = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('presensi.nik', $karyawan->nik)
            ->where('presensi.tanggal', $tanggalKemarin)
            ->first();

        $lintasHari = $presensiKemarin ? $presensiKemarin->lintashari : 0;
        $batasLintashari = $generalsetting->batas_presensi_lintashari;

        $tanggalPresensi = $lintasHari == 1 ? $tanggalKemarin : $tanggalSekarang;
        $tanggalPulang   = $lintasHari == 1 ? $tanggalSekarang : $tanggalSekarang;

        if ($lintasHari == 1 && $jamSekarang > $batasLintashari) {
            $tanggalPresensi = $tanggalSekarang;
            $tanggalPulang   = $tanggalBesok;
        }

        $jamKerja = Jamkerja::where('kode_jam_kerja', $request->kode_jam_kerja)->first();
        if (!$jamKerja) {
            return response()->json(['success' => false, 'message' => 'Kode jam kerja tidak ditemukan'], 422);
        }

        // Resolusi tanggal pulang berdasarkan lintas hari jam kerja
        if ($presensiKemarin) {
            if ($presensiKemarin->lintashari == 1) {
                if ($jamSekarang > $batasLintashari) {
                    $tanggalPresensi = $tanggalSekarang;
                    $tanggalPulang   = $tanggalBesok;
                    $jamKerjaPulang  = $jamKerja->jam_pulang;
                } else {
                    $tanggalPresensi = $tanggalKemarin;
                    $tanggalPulang   = $tanggalSekarang;
                    $jamKerjaPulang  = $presensiKemarin->jam_pulang;
                }
            } else {
                $tanggalPresensi = $tanggalSekarang;
                $tanggalPulang   = $jamKerja->lintashari == 1 ? $tanggalBesok : $tanggalSekarang;
                $jamKerjaPulang  = $jamKerja->jam_pulang;
            }
        } else {
            $tanggalPresensi = $tanggalSekarang;
            $tanggalPulang   = $jamKerja->lintashari == 1 ? $tanggalBesok : $tanggalSekarang;
            $jamKerjaPulang  = $jamKerja->jam_pulang;
        }

        // Hitung radius
        $koordinatUser   = explode(',', $request->lokasi);
        $koordinatKantor = explode(',', $request->lokasi_cabang);
        $jarak           = hitungjarak($koordinatKantor[0], $koordinatKantor[1], $koordinatUser[0], $koordinatUser[1]);
        $radius          = round($jarak['meters']);

        // Cek izin dinas (bypass lock_location)
        $statusLockLocation = $karyawan->lock_location;
        $izinDinas = Izindinas::where('nik', $karyawan->nik)
            ->where('status', 1)
            ->where('dari', '<=', $tanggalPresensi)
            ->where('sampai', '>=', $tanggalPresensi)
            ->first();
        if ($izinDinas) $statusLockLocation = 0;

        if ($statusLockLocation == 1 && $radius > $cabang->radius_cabang) {
            return response()->json([
                'success'    => false,
                'message'    => 'Anda berada di luar radius kantor. Jarak Anda ' . formatAngka($radius) . ' meter dari kantor.',
                'notifikasi' => 'notifikasi_radius',
            ], 400);
        }

        // Simpan foto
        $inOut      = $request->status == 1 ? 'in' : 'out';
        $formatName = $karyawan->nik . '-' . $tanggalPresensi . '-' . $inOut;
        $folderPath = 'public/uploads/absensi/';

        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath, 0775, true);
        }

        try {
            if ($request->hasFile('image')) {
                $file     = $request->file('image');
                $fileName = $formatName . '.png';
                Storage::put($folderPath . $fileName, file_get_contents($file));
            } elseif ($request->filled('image')) {
                $imageParts = explode(';base64,', $request->image);
                $imageData  = base64_decode($imageParts[1] ?? $request->image);
                $fileName   = $formatName . '.png';
                Storage::put($folderPath . $fileName, $imageData);
            } else {
                $fileName = null;
            }
        } catch (\Exception $e) {
            $fileName = null;
        }

        // Batas waktu absen
        $batasJamAbsen       = $generalsetting->batas_jam_absen * 60;
        $batasJamAbsenPulang = $generalsetting->batas_jam_absen_pulang * 60;

        $jamMasukCarbon = Carbon::parse($tanggalPresensi . ' ' . $jamKerja->jam_masuk, $timezone);
        $jamMulaiMasuk  = $jamMasukCarbon->copy()->subMinutes($batasJamAbsen);
        $jamAkhirMasuk  = $jamMasukCarbon->copy()->addMinutes($batasJamAbsen);
        $jamPulangCarbon = Carbon::parse($tanggalPulang . ' ' . $jamKerjaPulang, $timezone);
        $jamMulaiPulang  = $jamPulangCarbon->copy()->subMinutes($batasJamAbsenPulang);

        $jamPresensiCarbon = Carbon::now($timezone);

        $presensiHariini = Presensi::where('nik', $karyawan->nik)->where('tanggal', $tanggalPresensi)->first();

        if ($request->status == 1) {
            // ─── ABSEN MASUK ───
            if ($presensiHariini && $presensiHariini->jam_in != null) {
                return response()->json(['success' => false, 'message' => 'Anda sudah absen masuk hari ini.', 'notifikasi' => 'notifikasi_sudahabsen'], 400);
            }
            if ($generalsetting->batasi_absen == 1 && $jamPresensiCarbon->lt($jamMulaiMasuk)) {
                return response()->json(['success' => false, 'message' => 'Belum waktunya absen masuk. Waktu absen mulai pukul ' . $jamMulaiMasuk->format('H:i'), 'notifikasi' => 'notifikasi_mulaiabsen'], 400);
            }
            if ($generalsetting->batasi_absen == 1 && $jamPresensiCarbon->gt($jamAkhirMasuk)) {
                return response()->json(['success' => false, 'message' => 'Waktu absen masuk sudah habis.', 'notifikasi' => 'notifikasi_akhirabsen'], 400);
            }

            try {
                $jamPresensiString = $jamPresensiCarbon->format('Y-m-d H:i');
                if ($presensiHariini) {
                    Presensi::where('id', $presensiHariini->id)->update([
                        'jam_in'    => $jamPresensiString,
                        'lokasi_in' => $request->lokasi,
                        'foto_in'   => $fileName,
                    ]);
                } else {
                    Presensi::create([
                        'nik'            => $karyawan->nik,
                        'tanggal'        => $tanggalPresensi,
                        'jam_in'         => $jamPresensiString,
                        'jam_out'        => null,
                        'lokasi_in'      => $request->lokasi,
                        'lokasi_out'     => null,
                        'foto_in'        => $fileName,
                        'foto_out'       => null,
                        'kode_jam_kerja' => $request->kode_jam_kerja,
                        'status'         => 'h',
                    ]);
                }

                $this->kirimNotifikasiWA($generalsetting, $karyawan, 'masuk', $jamPresensiString);

                return response()->json([
                    'success'    => true,
                    'message'    => 'Berhasil absen masuk.',
                    'notifikasi' => 'notifikasi_absenmasuk',
                    'jam_in'     => $jamPresensiString,
                ]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan presensi.'], 500);
            }

        } else {
            // ─── ABSEN PULANG ───
            if ($presensiHariini && $presensiHariini->jam_out != null) {
                return response()->json(['success' => false, 'message' => 'Anda sudah absen pulang hari ini.', 'notifikasi' => 'notifikasi_sudahabsen'], 400);
            }
            if ($generalsetting->batasi_absen == 1 && $jamPresensiCarbon->lt($jamMulaiPulang)) {
                return response()->json(['success' => false, 'message' => 'Belum waktunya absen pulang. Waktu absen pulang mulai pukul ' . $jamMulaiPulang->format('H:i'), 'notifikasi' => 'notifikasi_mulaiabsen'], 400);
            }

            try {
                $jamPresensiString = $jamPresensiCarbon->format('Y-m-d H:i');
                if ($presensiHariini) {
                    Presensi::where('id', $presensiHariini->id)->update([
                        'jam_out'    => $jamPresensiString,
                        'lokasi_out' => $request->lokasi,
                        'foto_out'   => $fileName,
                    ]);
                } else {
                    Presensi::create([
                        'nik'            => $karyawan->nik,
                        'tanggal'        => $tanggalPresensi,
                        'jam_in'         => null,
                        'jam_out'        => $jamPresensiString,
                        'lokasi_in'      => null,
                        'lokasi_out'     => $request->lokasi,
                        'foto_in'        => null,
                        'foto_out'       => $fileName,
                        'kode_jam_kerja' => $request->kode_jam_kerja,
                        'status'         => 'h',
                    ]);
                }

                $this->kirimNotifikasiWA($generalsetting, $karyawan, 'pulang', $jamPresensiString);

                return response()->json([
                    'success'    => true,
                    'message'    => 'Berhasil absen pulang.',
                    'notifikasi' => 'notifikasi_absenpulang',
                    'jam_out'    => $jamPresensiString,
                ]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan presensi.'], 500);
            }
        }
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
        // Cek ajuan jadwal yang disetujui
        $ajuanJadwal = AjuanJadwal::where('nik', $karyawan->nik)
            ->where('tanggal', $hariini)
            ->where('status', 'a')
            ->first();
        if ($ajuanJadwal) {
            return Jamkerja::where('kode_jam_kerja', $ajuanJadwal->kode_jam_kerja_tujuan)->first();
        }

        // By date (personal)
        $jamkerja = Setjamkerjabydate::join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('nik', $karyawan->nik)
            ->where('tanggal', $hariini)
            ->first();
        if ($jamkerja) return $jamkerja;

        // Grup
        $cekGroup = GrupDetail::where('nik', $karyawan->nik)->first();
        if ($cekGroup) {
            $jamkerja = GrupJamkerjaBydate::where('kode_grup', $cekGroup->kode_grup)
                ->where('tanggal', $hariini)
                ->join('presensi_jamkerja', 'grup_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->first();
            if ($jamkerja) return $jamkerja;
        }

        // By day (personal)
        $jamkerja = Setjamkerjabyday::join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('nik', $karyawan->nik)
            ->where('hari', $namahari)
            ->first();
        if ($jamkerja) return $jamkerja;

        // By departemen
        $jamkerja = Detailsetjamkerjabydept::join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
            ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('kode_dept', $karyawan->kode_dept)
            ->where('kode_cabang', $karyawan->kode_cabang)
            ->where('hari', $namahari)
            ->first();

        return $jamkerja;
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

    private function kirimNotifikasiWA($generalsetting, $karyawan, string $tipe, string $jam): void
    {
        if (!$generalsetting->notifikasi_wa) return;
        try {
            $kata    = $tipe === 'masuk' ? 'Masuk' : 'Pulang';
            $message = "Terimakasih, Hari ini {$karyawan->nama_karyawan} absen {$kata} pada {$jam}";
            if ($generalsetting->tujuan_notifikasi_wa == 0) {
                if ($karyawan->no_hp) dispatch(new SendWaMessage($karyawan->no_hp, $message));
            } else {
                dispatch(new SendWaMessage($generalsetting->id_group_wa, $message));
            }
        } catch (\Exception $e) {
            Log::error('Gagal kirim WA presensi API', ['nik' => $karyawan->nik, 'error' => $e->getMessage()]);
        }
    }
}

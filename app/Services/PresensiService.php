<?php

namespace App\Services;

use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\Pengaturanumum;
use App\Models\Presensi;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use App\Models\Detailsetjamkerjabydept;
use App\Models\Jamkerja;
use App\Models\Izindinas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PresensiService
{
    public function prosesPresensiMobile(Request $request, $karyawan)
    {
        $generalsetting    = Pengaturanumum::where('id', 1)->first();
        $cabang            = Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();
        $timezone          = $cabang->timezone ?? $generalsetting->timezone ?? config('app.timezone');
        $now               = Carbon::now($timezone);
        $tanggalSekarang   = $now->format('Y-m-d');
        $jamSekarang       = $now->format('H:i');
        $tanggalKemarin    = $now->copy()->subDay()->format('Y-m-d');
        $tanggalBesok      = $now->copy()->addDay()->format('Y-m-d');

        // Resolusi tanggal presensi (lintas hari)
        $resolved = $this->resolvePresensiDate($karyawan->nik, $request->kode_jam_kerja, $timezone, $generalsetting->batas_presensi_lintashari);
        $tanggalPresensi = $resolved['tanggal_presensi'];
        $tanggalPulang   = $resolved['tanggal_pulang'];
        $jamKerjaPulang  = $resolved['jam_kerja_pulang'];
        $jamKerja        = $resolved['jam_kerja'];

        // Hitung radius
        $koordinatUser   = explode(',', $request->lokasi ?? '');
        $koordinatKantor = explode(',', $request->lokasi_cabang ?? '');

        // Validasi format koordinat (harus memiliki latitude dan longitude)
        if (count($koordinatUser) < 2 || count($koordinatKantor) < 2) {
            return [
                'success'    => false,
                'message'    => 'Format lokasi tidak valid. Pastikan GPS aktif dan coba lagi.',
                'notifikasi' => 'notifikasi_lokasi',
                'status_code'=> 400
            ];
        }

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
            return [
                'success'    => false,
                'message'    => 'Anda berada di luar radius kantor. Jarak Anda ' . formatAngka($radius) . ' meter dari kantor.',
                'notifikasi' => 'notifikasi_radius',
                'status_code'=> 400
            ];
        }

        // Simpan foto
        $inOut      = $request->status == 1 ? 'in' : 'out';
        $fileName   = $this->simpanFotoPresensi($request, $karyawan->nik, $tanggalPresensi, $inOut);

        $boundaries = $this->calculateTimeBoundaries($jamKerja, $tanggalPresensi, $tanggalPulang, $jamKerjaPulang, $generalsetting, $timezone);
        $jamPresensiCarbon = Carbon::now($timezone);
        $presensiHariini = Presensi::where('nik', $karyawan->nik)->where('tanggal', $tanggalPresensi)->first();

        // Validasi waktu absen
        $validationError = $this->validateAttendanceTime($request->status, $presensiHariini, $jamPresensiCarbon, $boundaries, $generalsetting);
        if ($validationError) {
            $validationError['status_code'] = 400;
            return $validationError;
        }

        if ($request->status == 1) {
            // ─── ABSEN MASUK ───

            try {
                $jamPresensiString = $jamPresensiCarbon->format('Y-m-d H:i');
                $this->simpanRecordPresensi($karyawan->nik, $tanggalPresensi, 'in', [
                    'jam_in'         => $jamPresensiString,
                    'lokasi_in'      => $request->lokasi,
                    'foto_in'        => $fileName,
                    'kode_jam_kerja' => $request->kode_jam_kerja,
                ]);

                return [
                    'success'    => true,
                    'message'    => 'Berhasil absen masuk.',
                    'notifikasi' => 'notifikasi_absenmasuk',
                    'jam_in'     => $jamPresensiString,
                    'status_code'=> 200
                ];
            } catch (\Exception $e) {
                return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan presensi.', 'status_code' => 500];
            }

        } else {
            // ─── ABSEN PULANG ───

            try {
                $jamPresensiString = $jamPresensiCarbon->format('Y-m-d H:i');
                $this->simpanRecordPresensi($karyawan->nik, $tanggalPresensi, 'out', [
                    'jam_out'        => $jamPresensiString,
                    'lokasi_out'     => $request->lokasi,
                    'foto_out'       => $fileName,
                    'kode_jam_kerja' => $request->kode_jam_kerja,
                ]);

                return [
                    'success'    => true,
                    'message'    => 'Berhasil absen pulang.',
                    'notifikasi' => 'notifikasi_absenpulang',
                    'jam_out'    => $jamPresensiString,
                    'status_code'=> 200
                ];
            } catch (\Exception $e) {
                return ['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan presensi.', 'status_code' => 500];
            }
        }
    }

    /**
     * Resolusi tanggal presensi berdasarkan lintas hari.
     * Mengembalikan array [tanggal_presensi, tanggal_pulang, jam_kerja_pulang, jam_kerja].
     */
    public function resolvePresensiDate($karyawan_nik, $kodeJamKerja, $timezone, $batasLintashari): array
    {
        $now = Carbon::now($timezone);
        $tanggalSekarang = $now->format('Y-m-d');
        $jamSekarang     = $now->format('H:i');
        $tanggalKemarin  = $now->copy()->subDay()->format('Y-m-d');
        $tanggalBesok    = $now->copy()->addDay()->format('Y-m-d');

        $presensiKemarin = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('presensi.nik', $karyawan_nik)
            ->where('presensi.tanggal', $tanggalKemarin)
            ->first();

        $jamKerja = Jamkerja::where('kode_jam_kerja', $kodeJamKerja)->first();

        // Fallback or validation
        if (!$jamKerja) {
            // Let's fallback to JK01 if not found or throw exception
            $jamKerja = Jamkerja::where('kode_jam_kerja', 'JK01')->first();
        }

        if ($presensiKemarin) {
            if ($presensiKemarin->lintashari == 1) {
                if ($jamSekarang > $batasLintashari) {
                    $tanggalPresensi = $tanggalSekarang;
                    $tanggalPulang   = $tanggalBesok;
                    $jamKerjaPulang  = $jamKerja ? $jamKerja->jam_pulang : null;
                } else {
                    $tanggalPresensi = $tanggalKemarin;
                    $tanggalPulang   = $tanggalSekarang;
                    $jamKerjaPulang  = $presensiKemarin->jam_pulang;
                }
            } else {
                $tanggalPresensi = $tanggalSekarang;
                $tanggalPulang   = ($jamKerja && $jamKerja->lintashari == 1) ? $tanggalBesok : $tanggalSekarang;
                $jamKerjaPulang  = $jamKerja ? $jamKerja->jam_pulang : null;
            }
        } else {
            $tanggalPresensi = $tanggalSekarang;
            $tanggalPulang   = ($jamKerja && $jamKerja->lintashari == 1) ? $tanggalBesok : $tanggalSekarang;
            $jamKerjaPulang  = $jamKerja ? $jamKerja->jam_pulang : null;
        }

        return [
            'tanggal_presensi' => $tanggalPresensi,
            'tanggal_pulang'   => $tanggalPulang,
            'jam_kerja_pulang' => $jamKerjaPulang,
            'jam_kerja'        => $jamKerja,
        ];
    }

    /**
     * Simpan foto presensi (mendukung base64 dan file upload).
     * Mengembalikan nama file yang disimpan, atau null jika gagal.
     */
    public function simpanFotoPresensi(Request $request, string $nik, string $tanggal, string $inOut): ?string
    {
        $formatName = $nik . '-' . $tanggal . '-' . $inOut;
        $folderPath = 'public/uploads/absensi/';

        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath, 0775, true);
        }

        try {
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = $formatName . '.png';
                Storage::put($folderPath . $fileName, file_get_contents($file));
                return $fileName;
            } elseif ($request->filled('image')) {
                $image = $request->image;
                $imageParts = explode(';base64,', $image);
                $imageData = base64_decode($imageParts[1] ?? $image);
                $fileName = $formatName . '.png';
                Storage::put($folderPath . $fileName, $imageData);
                return $fileName;
            }
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan foto presensi', ['nik' => $nik, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Simpan atau update record presensi (untuk check-in atau check-out).
     */
    public function simpanRecordPresensi(string $nik, string $tanggal, string $tipe, array $data): Presensi
    {
        $presensi = Presensi::where('nik', $nik)->where('tanggal', $tanggal)->first();

        if ($presensi) {
            $presensi->update($data);
        } else {
            $presensi = Presensi::create(array_merge([
                'nik'     => $nik,
                'tanggal' => $tanggal,
                'status'  => 'h',
            ], $data));
        }

        return $presensi;
    }

    public function checkRfidKiosk(Request $request)
    {
        $rfid_uid = $request->rfid_uid;
        $karyawan = Karyawan::leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->leftJoin('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->select('karyawan.*', 'jabatan.nama_jabatan', 'departemen.nama_dept')
            ->where('karyawan.rfid_uid', $rfid_uid)
            ->first();

        if (!$karyawan) {
            return ['status' => 'error', 'message' => 'Kartu RFID tidak terdaftar'];
        }

        if ($karyawan->status_aktif_karyawan != '1') {
            return ['status' => 'error', 'message' => 'Karyawan tidak aktif'];
        }

        // Determine if this is check-in or check-out
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $cabang = Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();
        $timezone_cabang = $cabang->timezone ?? $generalsetting->timezone ?? config('app.timezone');
        $carbon_now = Carbon::now($timezone_cabang);
        $tanggal_sekarang = $carbon_now->format('Y-m-d');

        $presensi_hariini = Presensi::where('nik', $karyawan->nik)
            ->where('tanggal', $tanggal_sekarang)
            ->first();

        $type = ($presensi_hariini && $presensi_hariini->jam_in != null) ? 'out' : 'in';

        // Get jam kerja
        $jam_kerja = $this->getJamKerjaKaryawan($karyawan);

        // Foto URL
        $foto = null;
        if (!empty($karyawan->foto) && Storage::disk('public')->exists('karyawan/' . $karyawan->foto)) {
            $foto = url('/storage/karyawan/' . $karyawan->foto);
        }

        return [
            'status' => 'success',
            'nama' => $karyawan->nama_karyawan,
            'nik' => $karyawan->nik,
            'jabatan' => $karyawan->nama_jabatan ?? '-',
            'departemen' => $karyawan->nama_dept ?? '-',
            'foto' => $foto,
            'jam_kerja' => $jam_kerja ? $jam_kerja->nama_jam_kerja . ' (' . $jam_kerja->jam_masuk . ' - ' . $jam_kerja->jam_pulang . ')' : 'Tidak ada jadwal',
            'type' => $type
        ];
    }

    public function prosesPresensiKiosk(Request $request)
    {
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $rfid_uid = $request->rfid_uid;
        $karyawan = Karyawan::where('rfid_uid', $rfid_uid)->first();

        if (!$karyawan) {
            return ['status' => 'error', 'message' => 'Karyawan tidak ditemukan'];
        }

        $cabang = Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();
        $timezone_cabang = $cabang->timezone ?? $generalsetting->timezone ?? config('app.timezone');
        $carbon_now = Carbon::now($timezone_cabang);

        $tanggal_sekarang = $carbon_now->format('Y-m-d');
        $jam_sekarang = $carbon_now->format('H:i');
        $tanggal_kemarin = $carbon_now->copy()->subDay()->format('Y-m-d');
        $tanggal_besok = $carbon_now->copy()->addDay()->format('Y-m-d');

        $jam_kerja = $this->getJamKerjaKaryawan($karyawan);
        if (!$jam_kerja) {
            return ['status' => 'error', 'message' => 'Jadwal kerja tidak ditemukan'];
        }

        // Resolusi tanggal presensi (lintas hari)
        $resolved = $this->resolvePresensiDate($karyawan->nik, $jam_kerja->kode_jam_kerja, $timezone_cabang, $generalsetting->batas_presensi_lintashari);
        $tanggal_presensi = $resolved['tanggal_presensi'];
        $tanggal_pulang   = $resolved['tanggal_pulang'];
        $jam_kerja_pulang = $resolved['jam_kerja_pulang'];

        // Determine status (1: Masuk, 2: Pulang)
        $presensi_hariini_check = Presensi::where('nik', $karyawan->nik)
            ->where('tanggal', $tanggal_presensi)->first();
        $status = ($presensi_hariini_check && $presensi_hariini_check->jam_in != null) ? 2 : 1;
        $in_out = $status == 1 ? "in" : "out";

        // Image handling using helper
        $fileName = $this->simpanFotoPresensi($request, $karyawan->nik, $tanggal_presensi, $in_out);

        $jam_presensi = $tanggal_sekarang . " " . $jam_sekarang;
        $batas_jam_absen = $generalsetting->batas_jam_absen * 60;
        $batas_jam_absen_pulang = $generalsetting->batas_jam_absen_pulang * 60;

        $boundaries = $this->calculateTimeBoundaries($jam_kerja, $tanggal_presensi, $tanggal_pulang, $jam_kerja_pulang, $generalsetting, $timezone_cabang);
        $jam_presensi_carbon = Carbon::parse($jam_presensi, $timezone_cabang);
        $presensi_hariini = Presensi::where('nik', $karyawan->nik)->where('tanggal', $tanggal_presensi)->first();

        // Validasi waktu absen
        $validationError = $this->validateAttendanceTime($status, $presensi_hariini, $jam_presensi_carbon, $boundaries, $generalsetting);
        if ($validationError) {
            return ['status' => 'error', 'message' => $validationError['message']];
        }

        if ($status == 1) {
            // --- ABSEN MASUK ---
            try {
                $this->simpanRecordPresensi($karyawan->nik, $tanggal_presensi, 'in', [
                    'jam_in'         => $jam_presensi,
                    'foto_in'        => $fileName,
                    'kode_jam_kerja' => $jam_kerja->kode_jam_kerja,
                ]);

                return ['status' => 'success', 'message' => 'Berhasil Absen Masuk', 'type' => 'masuk'];
            } catch (\Exception $e) {
                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        } else {
            // --- ABSEN PULANG ---
            try {
                $this->simpanRecordPresensi($karyawan->nik, $tanggal_presensi, 'out', [
                    'jam_out'        => $jam_presensi,
                    'foto_out'       => $fileName,
                    'kode_jam_kerja' => $jam_kerja->kode_jam_kerja,
                ]);

                    return ['status' => 'success', 'message' => 'Berhasil Absen Pulang', 'type' => 'pulang'];
                } catch (\Exception $e) {
                    return ['status' => 'error', 'message' => $e->getMessage()];
                }
        }
    }

    public function prosesPresensiMesin(array $data)
    {
        $pin = $data['pin'] ?? null;
        $status_scan = $data['status_scan'] ?? null;
        $scan = $data['scan'] ?? null;

        if (!$pin || !$scan) {
            return ['status' => false, 'message' => 'Data tidak lengkap', 'status_code' => 400];
        }

        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $karyawan = Karyawan::where('pin', $pin)->first();

        if (!$karyawan) {
            return ['status' => false, 'message' => 'Karyawan Tidak Ditemukan', 'status_code' => 200];
        }

        $cabang = Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();
        $timezone_cabang = $cabang->timezone ?? $generalsetting->timezone ?? config('app.timezone');
        
        $carbon_scan = Carbon::parse($scan, $timezone_cabang);
        $tanggal_sekarang = $carbon_scan->format('Y-m-d');
        $jam_sekarang = $carbon_scan->format('H:i');

        // Dapatkan Jam Kerja
        $jamkerja = $this->getJamKerjaKaryawan($karyawan, $tanggal_sekarang);
        if (!$jamkerja) {
            $jamkerja = Jamkerja::where('kode_jam_kerja', 'JK01')->first();
        }

        if (!$jamkerja) {
            return ['status' => false, 'message' => 'Jam Kerja Tidak Ditemukan', 'status_code' => 200];
        }

        // Resolusi tanggal presensi untuk kasus lintas hari
        $resolved = $this->resolvePresensiDate($karyawan->nik, $jamkerja->kode_jam_kerja, $timezone_cabang, $generalsetting->batas_presensi_lintashari);
        $tanggal_presensi = $resolved['tanggal_presensi'];

        $presensi_hariini = Presensi::where('nik', $karyawan->nik)->where('tanggal', $tanggal_presensi)->first();
        
        if ($presensi_hariini && $presensi_hariini->status != 'h') {
            return ['status' => false, 'message' => 'Presensi Sudah Ada', 'status_code' => 200];
        }

        $jam_presensi = $tanggal_sekarang . " " . $jam_sekarang;

        if (in_array($status_scan, [0, 2, 4, 6, 8])) {
            // Absen Masuk
            if ($presensi_hariini && $presensi_hariini->jam_in != null) {
                return ['status' => false, 'message' => 'Anda Sudah Absen Masuk Hari Ini', 'notifikasi' => 'notifikasi_sudahabsen', 'status_code' => 400];
            }

            try {
                $this->simpanRecordPresensi($karyawan->nik, $tanggal_presensi, 'in', [
                    'jam_in'         => $jam_presensi,
                    'kode_jam_kerja' => $jamkerja->kode_jam_kerja,
                ]);

                return ['status' => true, 'message' => 'Berhasil Absen Masuk', 'notifikasi' => 'notifikasi_absenmasuk', 'status_code' => 200];
            } catch (\Exception $e) {
                return ['status' => false, 'message' => $e->getMessage(), 'status_code' => 400];
            }
        } else {
            // Absen Pulang
            try {
                $this->simpanRecordPresensi($karyawan->nik, $tanggal_presensi, 'out', [
                    'jam_out'        => $jam_presensi,
                    'kode_jam_kerja' => $jamkerja->kode_jam_kerja,
                ]);

                return ['status' => true, 'message' => 'Berhasil Absen Pulang', 'notifikasi' => 'notifikasi_absenpulang', 'status_code' => 200];
            } catch (\Exception $e) {
                return ['status' => false, 'message' => $e->getMessage(), 'status_code' => 400];
            }
        }
    }

    public function getJamKerjaKaryawan($karyawan, $tanggal = null)
    {
        $hariini = $tanggal ?? date("Y-m-d");
        $namahari = $this->getnamaHari(date('D', strtotime($hariini)));
        $kode_dept = $karyawan->kode_dept;

        // PRIORITAS UTAMA: Cek Ajuan Jadwal yang sudah disetujui
        $ajuan_jadwal = \App\Models\AjuanJadwal::where('nik', $karyawan->nik)
            ->where('tanggal', $hariini)
            ->where('status', 'a') // Approved
            ->first();

        if ($ajuan_jadwal) {
            return \App\Models\Jamkerja::where('kode_jam_kerja', $ajuan_jadwal->kode_jam_kerja_tujuan)->first();
        }

        // Cek Jam Kerja By Date
        $jamkerja = Setjamkerjabydate::join('presensi_jamkerja', 'presensi_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('nik', $karyawan->nik)
            ->where('tanggal', $hariini)
            ->first();
        if ($jamkerja) return $jamkerja;

        // Cek Jam Kerja Grup
        $cek_group = \App\Models\GrupDetail::where('nik', $karyawan->nik)->first();
        if ($cek_group) {
            $jamkerja = \App\Models\GrupJamkerjaBydate::where('kode_grup', $cek_group->kode_grup)
                ->where('tanggal', $hariini)
                ->join('presensi_jamkerja', 'grup_jamkerja_bydate.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
                ->first();
            if ($jamkerja) return $jamkerja;
        }

        // Cek Jam Kerja Harian
        $jamkerja = Setjamkerjabyday::join('presensi_jamkerja', 'presensi_jamkerja_byday.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('nik', $karyawan->nik)
            ->where('hari', $namahari)
            ->first();
        if ($jamkerja) return $jamkerja;

        // Cek Jam Kerja Departemen
        return Detailsetjamkerjabydept::join('presensi_jamkerja_bydept', 'presensi_jamkerja_bydept_detail.kode_jk_dept', '=', 'presensi_jamkerja_bydept.kode_jk_dept')
            ->join('presensi_jamkerja', 'presensi_jamkerja_bydept_detail.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('kode_dept', $kode_dept)
            ->where('kode_cabang', $karyawan->kode_cabang)
            ->where('hari', $namahari)
            ->first();
    }

    private function getnamaHari($hari)
    {
        $namaHari = [
            'Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'
        ];
        return $namaHari[$hari] ?? $hari;
    }

    /**
     * Menghitung batas jam absen masuk dan pulang.
     */
    public function calculateTimeBoundaries($jamKerja, $tanggalPresensi, $tanggalPulang, $jamKerjaPulang, $generalsetting, $timezone)
    {
        $batasJamAbsen       = $generalsetting->batas_jam_absen * 60;
        $batasJamAbsenPulang = $generalsetting->batas_jam_absen_pulang * 60;

        $jamMasukCarbon = Carbon::parse($tanggalPresensi . ' ' . $jamKerja->jam_masuk, $timezone);
        $jamPulangCarbon = Carbon::parse($tanggalPulang . ' ' . $jamKerjaPulang, $timezone);

        return [
            'mulai_masuk'  => $jamMasukCarbon->copy()->subMinutes($batasJamAbsen),
            'akhir_masuk'  => $jamMasukCarbon->copy()->addMinutes($batasJamAbsen),
            'mulai_pulang' => $jamPulangCarbon->copy()->subMinutes($batasJamAbsenPulang)
        ];
    }

    /**
     * Memvalidasi waktu absensi (sudah absen, belum waktunya, atau sudah habis).
     * Mengembalikan array response error jika tidak valid, atau null jika valid.
     */
    public function validateAttendanceTime($status, $presensiHariini, $jamPresensiCarbon, $boundaries, $generalsetting)
    {
        if ($status == 1) {
            // Absen Masuk
            if ($presensiHariini && $presensiHariini->jam_in != null) {
                return ['success' => false, 'message' => 'Anda sudah absen masuk hari ini.', 'notifikasi' => 'notifikasi_sudahabsen'];
            }
            if ($generalsetting->batasi_absen == 1) {
                if ($jamPresensiCarbon->lt($boundaries['mulai_masuk'])) {
                    return ['success' => false, 'message' => 'Belum waktunya absen masuk. Waktu absen mulai pukul ' . $boundaries['mulai_masuk']->format('H:i'), 'notifikasi' => 'notifikasi_mulaiabsen'];
                }
                if ($jamPresensiCarbon->gt($boundaries['akhir_masuk'])) {
                    return ['success' => false, 'message' => 'Waktu absen masuk sudah habis.', 'notifikasi' => 'notifikasi_akhirabsen'];
                }
            }
        } else {
            // Absen Pulang
            if ($presensiHariini && $presensiHariini->jam_out != null) {
                return ['success' => false, 'message' => 'Anda sudah absen pulang hari ini.', 'notifikasi' => 'notifikasi_sudahabsen'];
            }
            if ($generalsetting->batasi_absen == 1 && $jamPresensiCarbon->lt($boundaries['mulai_pulang'])) {
                return ['success' => false, 'message' => 'Belum waktunya absen pulang. Waktu absen pulang mulai pukul ' . $boundaries['mulai_pulang']->format('H:i'), 'notifikasi' => 'notifikasi_mulaiabsen'];
            }
        }

        return null; // Valid
    }
}

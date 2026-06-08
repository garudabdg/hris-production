<?php

namespace App\Services;

use App\Models\Cabang;
use App\Models\Karyawan;
use App\Models\Pengaturanumum;
use App\Models\Presensi;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use App\Models\Detailsetjamkerjabydept;
use App\Jobs\SendWaMessage;
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
                return ['success' => false, 'message' => 'Anda sudah absen masuk hari ini.', 'notifikasi' => 'notifikasi_sudahabsen', 'status_code' => 400];
            }
            if ($generalsetting->batasi_absen == 1 && $jamPresensiCarbon->lt($jamMulaiMasuk)) {
                return ['success' => false, 'message' => 'Belum waktunya absen masuk. Waktu absen mulai pukul ' . $jamMulaiMasuk->format('H:i'), 'notifikasi' => 'notifikasi_mulaiabsen', 'status_code' => 400];
            }
            if ($generalsetting->batasi_absen == 1 && $jamPresensiCarbon->gt($jamAkhirMasuk)) {
                return ['success' => false, 'message' => 'Waktu absen masuk sudah habis.', 'notifikasi' => 'notifikasi_akhirabsen', 'status_code' => 400];
            }

            try {
                $jamPresensiString = $jamPresensiCarbon->format('Y-m-d H:i');
                $this->simpanRecordPresensi($karyawan->nik, $tanggalPresensi, 'in', [
                    'jam_in'         => $jamPresensiString,
                    'lokasi_in'      => $request->lokasi,
                    'foto_in'        => $fileName,
                    'kode_jam_kerja' => $request->kode_jam_kerja,
                ]);

                $this->kirimNotifikasiWAMobile($generalsetting, $karyawan, 'masuk', $jamPresensiString);

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
            if ($presensiHariini && $presensiHariini->jam_out != null) {
                return ['success' => false, 'message' => 'Anda sudah absen pulang hari ini.', 'notifikasi' => 'notifikasi_sudahabsen', 'status_code' => 400];
            }
            if ($generalsetting->batasi_absen == 1 && $jamPresensiCarbon->lt($jamMulaiPulang)) {
                return ['success' => false, 'message' => 'Belum waktunya absen pulang. Waktu absen pulang mulai pukul ' . $jamMulaiPulang->format('H:i'), 'notifikasi' => 'notifikasi_mulaiabsen', 'status_code' => 400];
            }

            try {
                $jamPresensiString = $jamPresensiCarbon->format('Y-m-d H:i');
                $this->simpanRecordPresensi($karyawan->nik, $tanggalPresensi, 'out', [
                    'jam_out'        => $jamPresensiString,
                    'lokasi_out'     => $request->lokasi,
                    'foto_out'       => $fileName,
                    'kode_jam_kerja' => $request->kode_jam_kerja,
                ]);

                $this->kirimNotifikasiWAMobile($generalsetting, $karyawan, 'pulang', $jamPresensiString);

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

    private function kirimNotifikasiWAMobile($generalsetting, $karyawan, string $tipe, string $jam): void
    {
        return; // Disabled per request
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

        // Jam masuk & batas waktu absen
        $jam_masuk_string = $tanggal_presensi . " " . $jam_kerja->jam_masuk;
        $jam_masuk_carbon = Carbon::parse($jam_masuk_string, $timezone_cabang);
        $jam_mulai_masuk_carbon = $jam_masuk_carbon->copy()->subMinutes($batas_jam_absen);
        $jam_akhir_masuk_carbon = $jam_masuk_carbon->copy()->addMinutes($batas_jam_absen);

        // Jam pulang & batas waktu absen pulang
        $jam_pulang_string = $tanggal_pulang . " " . $jam_kerja_pulang;
        $jam_pulang_carbon = Carbon::parse($jam_pulang_string, $timezone_cabang);
        $jam_mulai_pulang_carbon = $jam_pulang_carbon->copy()->subMinutes($batas_jam_absen_pulang);

        $presensi_hariini = Presensi::where('nik', $karyawan->nik)
            ->where('tanggal', $tanggal_presensi)->first();

        $jam_presensi_carbon = Carbon::parse($jam_presensi, $timezone_cabang);

        if ($status == 1) {
            // --- ABSEN MASUK ---
            if ($presensi_hariini && $presensi_hariini->jam_in != null) {
                return ['status' => 'error', 'message' => 'Anda sudah absen masuk hari ini'];
            } else if ($jam_presensi_carbon->lt($jam_mulai_masuk_carbon) && $generalsetting->batasi_absen == 1) {
                return ['status' => 'error', 'message' => 'Belum waktunya absen masuk, mulai pukul ' . $jam_mulai_masuk_carbon->format('H:i')];
            } else if ($jam_presensi_carbon->gt($jam_akhir_masuk_carbon) && $generalsetting->batasi_absen == 1) {
                return ['status' => 'error', 'message' => 'Waktu absen masuk sudah habis'];
            } else {
            try {
                $this->simpanRecordPresensi($karyawan->nik, $tanggal_presensi, 'in', [
                    'jam_in'         => $jam_presensi,
                    'foto_in'        => $fileName,
                    'kode_jam_kerja' => $jam_kerja->kode_jam_kerja,
                ]);

                // Notifikasi WA
                // Disabled per request
                if (false && $generalsetting->notifikasi_wa == 1) {
                    try {
                        $message = "Terimakasih, Hari ini " . $karyawan->nama_karyawan . " absen Masuk pada " . $jam_presensi;
                        if ($generalsetting->tujuan_notifikasi_wa == 0) {
                            if ($karyawan->no_hp != "") {
                                dispatch(new SendWaMessage($karyawan->no_hp, $message));
                            }
                        } else {
                            dispatch(new SendWaMessage($generalsetting->id_group_wa, $message));
                        }
                    } catch (\Exception $waEx) {
                        Log::error('Gagal kirim WA (kiosk masuk)', ['nik' => $karyawan->nik, 'error' => $waEx->getMessage()]);
                    }
                }

                return ['status' => 'success', 'message' => 'Berhasil Absen Masuk', 'type' => 'masuk'];
            } catch (\Exception $e) {
                return ['status' => 'error', 'message' => $e->getMessage()];
            }
        }
    } else {
        // --- ABSEN PULANG ---
        if ($presensi_hariini && $presensi_hariini->jam_out != null) {
            return ['status' => 'error', 'message' => 'Anda sudah absen pulang hari ini'];
        } else if ($jam_presensi_carbon->lt($jam_mulai_pulang_carbon) && $generalsetting->batasi_absen == 1) {
            return ['status' => 'error', 'message' => 'Belum waktunya absen pulang, mulai pukul ' . $jam_mulai_pulang_carbon->format('H:i')];
        } else {
            try {
                $this->simpanRecordPresensi($karyawan->nik, $tanggal_presensi, 'out', [
                    'jam_out'        => $jam_presensi,
                    'foto_out'       => $fileName,
                    'kode_jam_kerja' => $jam_kerja->kode_jam_kerja,
                ]);

                // Notifikasi WA
                // Disabled per request
                if (false && $generalsetting->notifikasi_wa == 1) {
                    try {
                        $message = "Terimakasih, Hari ini " . $karyawan->nama_karyawan . " absen Pulang pada " . $jam_presensi . " Hati Hati di Jalan";
                        if ($generalsetting->tujuan_notifikasi_wa == 0) {
                            if ($karyawan->no_hp != "") {
                                dispatch(new SendWaMessage($karyawan->no_hp, $message));
                            }
                        } else {
                            dispatch(new SendWaMessage($generalsetting->id_group_wa, $message));
                        }
                    } catch (\Exception $waEx) {
                        Log::error('Gagal kirim WA (kiosk pulang)', ['nik' => $karyawan->nik, 'error' => $waEx->getMessage()]);
                    }
                }

                    return ['status' => 'success', 'message' => 'Berhasil Absen Pulang', 'type' => 'pulang'];
                } catch (\Exception $e) {
                    return ['status' => 'error', 'message' => $e->getMessage()];
                }
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
}

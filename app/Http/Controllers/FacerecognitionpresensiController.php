<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Denda;
use App\Models\Detailharilibur;
use App\Models\Detailsetjamkerjabydept;
use App\Models\Facerecognition;
use App\Models\Harilibur;
use App\Models\Izindinas;
use App\Models\Jabatan;
use App\Models\Jamkerja;
use App\Models\Karyawan;
use App\Models\Pengaturanumum;
use App\Models\Presensi;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use App\Models\Setjamkerjabydept;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Milon\Barcode\DNS1D;

class FacerecognitionpresensiController extends Controller
{
    protected $presensiService;

    public function __construct(\App\Services\PresensiService $presensiService)
    {
        $this->presensiService = $presensiService;
    }

    public function index()
    {
        return view('facerecognition-presensi.index');
    }

    public function scan($nik)
    {
        // Validasi NIK
        $karyawan = Karyawan::where('nik', $nik)->first();

        if (!$karyawan) {
            return response()->json(['status' => false, 'message' => 'NIK tidak ditemukan'], 404);
        }

        if ($karyawan->status_aktif_karyawan != '1') {
            return response()->json(['status' => false, 'message' => 'Karyawan tidak aktif'], 400);
        }

        $cabang = Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();

        $data = [
            'karyawan' => $karyawan,
            'cabang' => $cabang,
            'nik' => $nik
        ];

        return view('facerecognition-presensi.scan', $data);
    }

    public function scanAny()
    {
        return view('facerecognition-presensi.scan_any');
    }

    public function store(Request $request)
    {
        $generalsetting = Pengaturanumum::where('id', 1)->first();

        // Ambil NIK dari request
        $nik = $request->nik;

        $karyawan = Karyawan::where('nik', $nik)->first();

        if (!$karyawan) {
            return response()->json(['status' => false, 'message' => 'NIK tidak ditemukan'], 404);
        }

        if ($karyawan->status_aktif_karyawan != '1') {
            return response()->json(['status' => false, 'message' => 'Karyawan tidak aktif'], 400);
        }


        $status = $request->status;
        $lokasi = $request->lokasi;
        $kode_jam_kerja = $request->kode_jam_kerja;

        // Get Lokasi Kantor
        $cabang = Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();
        $timezone = $cabang->timezone ?? $generalsetting->timezone ?? config('app.timezone');

        // Resolusi tanggal presensi (lintas hari)
        $resolved = $this->presensiService->resolvePresensiDate($karyawan->nik, $kode_jam_kerja, $timezone, $generalsetting->batas_presensi_lintashari);
        $tanggal_presensi = $resolved['tanggal_presensi'];
        $tanggal_pulang   = $resolved['tanggal_pulang'];
        $jam_kerja_pulang = $resolved['jam_kerja_pulang'];
        $jam_kerja        = $resolved['jam_kerja'];

        if (!$jam_kerja) {
            return response()->json(['status' => false, 'message' => 'Jadwal kerja tidak ditemukan'], 400);
        }

        $in_out = $status == 1 ? "in" : "out";
        $fileName = $this->presensiService->simpanFotoPresensi($request, $karyawan->nik, $tanggal_presensi, $in_out);

        $boundaries = $this->presensiService->calculateTimeBoundaries($jam_kerja, $tanggal_presensi, $tanggal_pulang, $jam_kerja_pulang, $generalsetting, $timezone);
        $jam_presensi_carbon = \Carbon\Carbon::now($timezone);
        $presensi_hariini = Presensi::where('nik', $karyawan->nik)->where('tanggal', $tanggal_presensi)->first();

        // Validasi waktu absen
        $validationError = $this->presensiService->validateAttendanceTime($status, $presensi_hariini, $jam_presensi_carbon, $boundaries, $generalsetting);
        if ($validationError) {
            return response()->json(['status' => false, 'message' => $validationError['message'], 'notifikasi' => $validationError['notifikasi']], 400);
        }

        if ($status == 1) {
            try {
                $jam_presensi = $jam_presensi_carbon->format('Y-m-d H:i');
                $this->presensiService->simpanRecordPresensi($karyawan->nik, $tanggal_presensi, 'in', [
                    'jam_in'         => $jam_presensi,
                    'lokasi_in'      => $lokasi,
                    'foto_in'        => $fileName,
                    'kode_jam_kerja' => $kode_jam_kerja,
                ]);

                return response()->json(['status' => true, 'message' => 'Berhasil Absen Masuk', 'notifikasi' => 'notifikasi_absenmasuk'], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
            }
        } else {
            try {
                $jam_presensi = $jam_presensi_carbon->format('Y-m-d H:i');
                $this->presensiService->simpanRecordPresensi($karyawan->nik, $tanggal_presensi, 'out', [
                    'jam_out'        => $jam_presensi,
                    'lokasi_out'     => $lokasi,
                    'foto_out'       => $fileName,
                    'kode_jam_kerja' => $kode_jam_kerja,
                ]);

                return response()->json(['status' => true, 'message' => 'Berhasil Absen Pulang', 'notifikasi' => 'notifikasi_absenpulang'], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
            }
        }
        }
    }

    function sendwa($no_hp, $message)
    {
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $apiKey = $generalsetting->wa_api_key;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $no_hp,
                'message' => $message,
                'filename' => 'filename',
                'schedule' => 0,
                'typing' => true,
                'delay' => '2',
                'countryCode' => '62',
                'followup' => 0,
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $apiKey
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
        }
        curl_close($curl);

        if (isset($error_msg)) {
            echo $error_msg;
        }
    }

    public function getKaryawan($nik)
    {
        try {
            // Ambil data karyawan dengan join ke tabel jabatan
            $karyawan = Karyawan::leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
                ->select('karyawan.*', 'jabatan.nama_jabatan')
                ->where('karyawan.nik', $nik)
                ->first();

            if (!$karyawan) {
                return response()->json(['status' => false, 'message' => 'NIK tidak ditemukan'], 404);
            }

            if ($karyawan->status_aktif_karyawan != '1') {
                return response()->json(['status' => false, 'message' => 'Karyawan tidak aktif'], 400);
            }

            // Ambil jam kerja karyawan untuk hari ini menggunakan PresensiService
            $jamKerja = $this->presensiService->getJamKerjaKaryawan($karyawan);

            // Generate QR Code
            $d = new \Milon\Barcode\DNS2D();
            $qr_code = $d->getBarcodePNG($karyawan->nik, 'QRCODE');

            return response()->json([
                'status' => true,
                'karyawan' => $karyawan,
                'jam_kerja' => $jamKerja,
                'qr_code' => $qr_code
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function getAllWajah()
    {
        try {
            Log::info('Getting all employee face data');

            $result = Cache::rememberForever('all_employee_faces_data', function () {
                // Ambil karyawan aktif beserta relasi facerecognition
                $allKaryawan = Karyawan::with('facerecognition')
                    ->leftJoin('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
                    ->select('karyawan.*', 'jabatan.nama_jabatan')
                    ->where('karyawan.status_aktif_karyawan', '1')
                    ->get();

                Log::info('Found ' . $allKaryawan->count() . ' active employees');

                $data = [];
                $hasFaceData = false;

                foreach ($allKaryawan as $karyawan) {
                    $wajahData = [];
                    
                    foreach ($karyawan->facerecognition as $wajah) {
                        $wajahData[] = [
                            'wajah' => $wajah->wajah,
                            'created_at' => $wajah->created_at
                        ];
                        $hasFaceData = true;
                    }

                    $data[] = [
                        'nik' => $karyawan->nik,
                        'nama_karyawan' => $karyawan->nama_karyawan,
                        'kode_jabatan' => $karyawan->kode_jabatan,
                        'nama_jabatan' => $karyawan->nama_jabatan,
                        'status_aktif_karyawan' => $karyawan->status_aktif_karyawan,
                        'wajah_data' => $wajahData
                    ];
                }

                // Jika ada data wajah, filter hanya karyawan yang punya wajah.
                // Jika tidak ada data wajah sama sekali, biarkan semua karyawan dikembalikan (fallback behaviour).
                if ($hasFaceData) {
                    $data = array_filter($data, function ($k) {
                        return count($k['wajah_data']) > 0;
                    });
                    $data = array_values($data); // reset keys
                }

                return $data;
            });

            Log::info('Returning ' . count($result) . ' employee records');
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error in getAllWajah: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

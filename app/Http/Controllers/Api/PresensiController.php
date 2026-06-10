<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Detailsetjamkerjabydept;
use App\Models\GrupDetail;
use App\Models\GrupJamkerjaBydate;
use App\Models\Jamkerja;
use App\Models\Karyawan;
use App\Models\LogAbsen;
use App\Models\Pengaturanumum;
use App\Models\Presensi;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    protected $presensiService;

    public function __construct(\App\Services\PresensiService $presensiService)
    {
        $this->presensiService = $presensiService;
    }

    public function store(Request $request)
    {
        $original_data  = file_get_contents('php://input');
        $decoded_data   = json_decode($original_data, true);
        $data           = $decoded_data['data'] ?? [];

        $result = $this->presensiService->prosesPresensiMesin($data);
        
        $statusCode = $result['status_code'] ?? 200;
        unset($result['status_code']);

        return response()->json($result, $statusCode);
    }

    /**
     * Menerima data dari mesin Fingerspot REVO melalui adms
     * Data akan disimpan ke file txt untuk keperluan debugging dan logging
     * Response disesuaikan agar mesin tidak terus mengirim request
     */
    // public function receiveRevoData(Request $request)
    // {
    //     try {
    //         // Ambil raw data dari request
    //         $rawData = file_get_contents('php://input');

    //         // Ambil semua data dari request (termasuk form data dan JSON)
    //         $requestData = $request->all();

    //         // Buat hash dari raw data untuk mencegah duplikasi
    //         $dataHash = md5($rawData . $request->ip() . microtime(true));
    //         $cacheKey = 'revo_data_' . $dataHash;

    //         // Cek apakah data ini sudah pernah diterima (dalam 5 detik terakhir)
    //         if (Cache::has($cacheKey)) {
    //             // Data duplikat, langsung return OK tanpa proses ulang
    //             Log::info('Data REVO duplikat terdeteksi, skip processing', [
    //                 'hash' => $dataHash,
    //                 'ip' => $request->ip()
    //             ]);

    //             $responseText = 'OK';
    //             return response($responseText, 200)
    //                 ->header('Content-Type', 'text/plain')
    //                 ->header('Content-Length', strlen($responseText))
    //                 ->header('Connection', 'close');
    //         }

    //         // Set cache untuk 5 detik
    //         Cache::put($cacheKey, true, 5);

    //         // Buat timestamp untuk nama file
    //         $timestamp = date('Y-m-d_H-i-s');
    //         $dateFolder = date('Y-m-d');

    //         // Buat folder berdasarkan tanggal jika belum ada
    //         $folderPath = storage_path('app/public/revo_logs/' . $dateFolder);
    //         if (!file_exists($folderPath)) {
    //             mkdir($folderPath, 0755, true);
    //         }

    //         // Nama file dengan timestamp dan random string untuk menghindari duplikasi
    //         $fileName = 'revo_' . $timestamp . '_' . uniqid() . '.txt';
    //         $filePath = $folderPath . '/' . $fileName;

    //         // Siapkan konten untuk disimpan
    //         $content = "=== DATA REVO DARI adms ===\n";
    //         $content .= "Tanggal: " . date('Y-m-d H:i:s') . "\n";
    //         $content .= "IP Address: " . $request->ip() . "\n";
    //         $content .= "User Agent: " . ($request->userAgent() ?? 'N/A') . "\n";
    //         $content .= "Method: " . $request->method() . "\n";
    //         $content .= "URL: " . $request->fullUrl() . "\n";
    //         $content .= "Data Hash: " . $dataHash . "\n";
    //         $content .= "\n--- RAW DATA (HEX) ---\n";
    //         $content .= bin2hex($rawData) . "\n";
    //         $content .= "\n--- RAW DATA (STRING) ---\n";
    //         $content .= $rawData . "\n";
    //         $content .= "\n--- PARSED DATA ---\n";
    //         $content .= json_encode($requestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    //         $content .= "\n--- HEADERS ---\n";
    //         $content .= json_encode($request->headers->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    //         $content .= "\n=== END OF DATA ===\n";

    //         // Simpan ke file
    //         file_put_contents($filePath, $content);

    //         // Parse JSON dari raw data jika ada
    //         $jsonData = null;
    //         $parsedJson = null;
    //         if (!empty($rawData)) {
    //             // Coba extract JSON dari raw data (skip binary header jika ada)
    //             $jsonStart = strpos($rawData, '{');
    //             if ($jsonStart !== false) {
    //                 $jsonString = substr($rawData, $jsonStart);
    //                 $parsedJson = json_decode($jsonString, true);
    //             }
    //         }

    //         // Log juga ke Laravel log untuk tracking
    //         Log::info('Data REVO diterima dari adms', [
    //             'file' => $fileName,
    //             'ip' => $request->ip(),
    //             'data_count' => count($requestData),
    //             'raw_length' => strlen($rawData),
    //             'hash' => $dataHash,
    //             'request_code' => $request->header('request-code'),
    //             'dev_id' => $request->header('dev-id'),
    //             'trans_id' => $request->header('trans-id'),
    //             'parsed_json' => $parsedJson
    //         ]);

    //         // Ambil header dari request
    //         $requestCode = $request->header('request-code', '');
    //         $devId = $request->header('dev-id', '');
    //         $transId = $request->header('trans-id', '');
    //         $contentType = $request->header('Content-Type', '');

    //         // Response untuk realtime_glog - format binary/hex yang diharapkan adms
    //         if ($requestCode === 'realtime_glog') {
    //             // Response string "OK" dalam format binary/hex
    //             // "OK" dalam hex = 0x4F 0x4B
    //             $responseBinary = 'OK';

    //             // Log response untuk debugging
    //             Log::info('Response REVO realtime_glog', [
    //                 'request_code' => $requestCode,
    //                 'response_hex' => bin2hex($responseBinary),
    //                 'response_string' => $responseBinary,
    //                 'response_length' => strlen($responseBinary),
    //                 'response_format' => 'ok_string_hex'
    //             ]);

    //             return response($responseBinary, 200)
    //                 ->header('Content-Type', 'application/octet-stream')
    //                 ->header('Content-Length', strlen($responseBinary))
    //                 ->header('Connection', 'close');
    //         }

    //         // Response untuk receive_cmd - format binary/hex yang diharapkan adms
    //         if ($requestCode === 'receive_cmd') {
    //             // Response string "OK" dalam format binary/hex
    //             // "OK" dalam hex = 0x4F 0x4B
    //             $responseBinary = 'OK';

    //             // Log response untuk debugging
    //             Log::info('Response REVO receive_cmd', [
    //                 'request_code' => $requestCode,
    //                 'response_hex' => bin2hex($responseBinary),
    //                 'response_string' => $responseBinary,
    //                 'response_length' => strlen($responseBinary),
    //                 'response_format' => 'ok_string_hex'
    //             ]);

    //             return response($responseBinary, 200)
    //                 ->header('Content-Type', 'application/octet-stream')
    //                 ->header('Content-Length', strlen($responseBinary))
    //                 ->header('Connection', 'close');
    //         }

    //         // Jika content-type adalah application/octet-stream, return "OK" dalam hex
    //         if ($contentType === 'application/octet-stream') {
    //             // Response string "OK" dalam format binary/hex
    //             $responseBinary = 'OK';

    //             return response($responseBinary, 200)
    //                 ->header('Content-Type', 'application/octet-stream')
    //                 ->header('Content-Length', strlen($responseBinary))
    //                 ->header('Connection', 'close');
    //         }

    //         // Default: Response "OK" dalam format binary/hex
    //         $responseBinary = 'OK';

    //         return response($responseBinary, 200)
    //             ->header('Content-Type', 'application/octet-stream')
    //             ->header('Content-Length', strlen($responseBinary))
    //             ->header('Connection', 'close');
    //     } catch (\Exception $e) {
    //         // Log error
    //         Log::error('Error menerima data REVO dari adms', [
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString(),
    //             'ip' => $request->ip()
    //         ]);

    //         // Tetap return response sukses agar mesin tidak terus mengirim
    //         // Format "OK" dalam hex sesuai protokol adms
    //         $responseBinary = 'OK';

    //         return response($responseBinary, 200)
    //             ->header('Content-Type', 'application/octet-stream')
    //             ->header('Content-Length', strlen($responseBinary))
    //             ->header('Connection', 'close');
    //     }
    // }
}

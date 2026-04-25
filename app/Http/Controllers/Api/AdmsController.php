<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class admsController extends Controller
{
    public function capture(Request $request)
    {
        $requestCode = strtolower($request->header('request-code', 'unknown'));
        $rawBody     = trim($request->getContent());

    
        // ===============================
        // 1. LOG SEMUA REQUEST (DEBUG)
        // ===============================    
        Storage::append('adms_debug.txt',
            "TIME : ".now()."\n".
            "IP   : ".$request->ip()."\n".
            "CODE : ".$requestCode."\n".
            "BODY : ".$rawBody."\n".
            "---------------------------------\n"
        );

        // ===============================
        // 2. HANYA PROSES ABSENSI
        // ===============================
        if (!in_array($requestCode, ['realtime_glog', 'realtime_attlog'])) {
            return response('OK', 200);
        }

        // ===============================
        // 3. PARSE BODY (JSON / TEXT)
        // ===============================
        $data = json_decode($rawBody, true);

        if (!is_array($data)) {
            // fallback text format: key=value
            parse_str(str_replace(["\n", "\r", "\t"], "&", $rawBody), $data);
        }

        // ===============================
        // 4. AMBIL DATA PENTING
        // ===============================
        $userId = $data['user_id'] ?? $data['userid'] ?? null;
        $time   = $data['io_time'] ?? $data['time'] ?? null;

        if (!$userId || !$time) {
            return response('OK', 200);
        }

        // format waktu
        $datetime = \Carbon\Carbon::createFromFormat('YmdHis', $time)->toDateTimeString();

        // ===============================
        // 5. ANTI DUPLIKAT (SPAM HIT)
        // ===============================
        $hash = md5($userId.$datetime);

        if (Storage::exists("adms_cache/{$hash}.lock")) {
            Storage::append('adms_duplicate.txt',
                "DUPLICATE | {$userId} | {$datetime}\n"
            );
            return response('OK', 200);
        }

        Storage::put("adms_cache/{$hash}.lock", '1');

        // ===============================
        // 6. SIMPAN ABSENSI VALID
        // ===============================
        Storage::append('adms_success.txt',
            "USER_ID : {$userId}\n".
            "TIME    : {$datetime}\n".
            "SOURCE  : {$requestCode}\n".
            "---------------------------------\n"
        );

        return response('OK', 200);
    }
}

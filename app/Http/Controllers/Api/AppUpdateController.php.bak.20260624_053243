<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengaturanumum;

class AppUpdateController extends Controller
{
    /**
     * Check for the latest APK update.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUpdate()
    {
        $settings = Pengaturanumum::first();

        // Default response if no settings found
        if (!$settings) {
            return response()->json([
                'latest_version' => '1.0.0',
                'download_url' => '',
                'force_update' => false,
            ]);
        }

        $downloadUrl = $settings->apk_download_url ?? '';
        if ($downloadUrl) {
            $downloadUrl = rtrim(env('APP_URL', 'https://hris.didimax.id'), '/') . '/download-apk';
        }

        return response()->json([
            'latest_version' => $settings->apk_version ?? '1.0.0',
            'download_url' => $downloadUrl,
            'force_update' => (bool) $settings->apk_force_update,
        ]);
    }
}

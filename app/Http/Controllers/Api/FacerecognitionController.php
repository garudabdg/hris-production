<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facerecognition;
use App\Models\Karyawan;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FacerecognitionController extends Controller
{
    private function formatFaceImageUrls($wajahList, $folderPath, $encodedFolder)
    {
        return $wajahList->map(function ($wajah) use ($folderPath, $encodedFolder) {
            $filePath    = $folderPath . $wajah->wajah;
            $fileExists  = Storage::exists($filePath);

            $imageUrl = null;
            if ($fileExists) {
                try {
                    $ts = Storage::lastModified($filePath);
                } catch (\Exception $e) {
                    $ts = \Carbon\Carbon::parse($wajah->created_at)->timestamp;
                }
                $encodedFile = rawurlencode($wajah->wajah);
                $imageUrl    = url('/storage/uploads/facerecognition/' . $encodedFolder . '/' . $encodedFile . '?v=' . $ts);
            }

            return [
                'id'         => $wajah->id,
                'wajah'      => $wajah->wajah,
                'image_url'  => $imageUrl,
                'created_at' => $wajah->created_at,
            ];
        });
    }

    /**
     * GET /api/karyawan/face
     * Ambil daftar wajah yang sudah terdaftar milik karyawan yang login.
     */
    public function index(Request $request)
    {
        $user        = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();

        if (!$userkaryawan) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404);
        }

        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();
        if (!$karyawan) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404);
        }

        $namaFolder   = $karyawan->nik . '-' . getNamaDepan(strtolower($karyawan->nama_karyawan));
        $folderPath   = 'public/uploads/facerecognition/' . $namaFolder . '/';
        $encodedFolder = rawurlencode($namaFolder);

        $wajahList = Facerecognition::where('nik', $userkaryawan->nik)
            ->orderBy('created_at', 'asc')
            ->get();

        $wajahList = $this->formatFaceImageUrls($wajahList, $folderPath, $encodedFolder);

        return response()->json([
            'success' => true,
            'total'   => $wajahList->count(),
            'data'    => $wajahList,
        ]);
    }

    /**
     * POST /api/karyawan/face
     * Simpan data wajah baru (file upload atau base64).
     * Mendukung:
     *   - files[]  : multi-file upload (multipart/form-data) + metadata JSON
     *   - images   : JSON array base64
     *   - image    : single base64 string
     */
    public function store(Request $request)
    {
        $user        = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();

        if (!$userkaryawan) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404);
        }

        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();
        if (!$karyawan) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404);
        }

        $request->validate([
            'files'   => 'nullable|array',
            'files.*' => 'file|mimes:jpg,jpeg,png|max:20480',
        ]);

        $nik        = $userkaryawan->nik;
        $namaFolder = $nik . '-' . getNamaDepan(strtolower($karyawan->nama_karyawan));
        $folderPath = 'public/uploads/facerecognition/' . $namaFolder . '/';

        // Buat folder jika belum ada
        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath, 0775, true);
        }

        try {
            $saved  = [];
            $cekWajah = Facerecognition::where('nik', $nik)->count();
            $urutan = $cekWajah + 1;
            $insertData = [];
            $now = \Carbon\Carbon::now();

            if ($request->hasFile('files')) {
                // ── Metode 1: File upload (multipart) ──────────────────────
                $metadata = json_decode($request->input('metadata', '[]'), true);

                foreach ($request->file('files') as $index => $file) {
                    $direction = $metadata[$index]['direction'] ?? 'front';
                    $fileName  = $urutan . '_' . $direction . '.png';

                    $file->storeAs($folderPath, $fileName);

                    $insertData[] = [
                        'nik' => $nik,
                        'wajah' => $fileName,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $saved[] = $fileName;
                    $urutan++;
                }

            } elseif ($request->has('images')) {
                // ── Metode 2: Array base64 JSON ─────────────────────────────
                $images = json_decode($request->input('images'), true);

                foreach ($images as $img) {
                    $direction  = $img['direction'] ?? 'front';
                    $imageParts = explode(';base64,', $img['image']);
                    $imageData  = base64_decode($imageParts[1] ?? $img['image']);
                    $fileName   = $urutan . '_' . $direction . '.png';

                    Storage::put($folderPath . $fileName, $imageData);
                    $insertData[] = [
                        'nik' => $nik,
                        'wajah' => $fileName,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $saved[] = $fileName;
                    $urutan++;
                }

            } elseif ($request->has('image')) {
                // ── Metode 3: Single base64 ─────────────────────────────────
                $imageParts = explode(';base64,', $request->input('image'));
                $imageData  = base64_decode($imageParts[1] ?? $request->input('image'));
                $fileName   = $urutan . '.png';

                Storage::put($folderPath . $fileName, $imageData);
                $insertData[] = [
                    'nik' => $nik,
                    'wajah' => $fileName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $saved[] = $fileName;

            } else {
                return response()->json(['success' => false, 'message' => 'Tidak ada gambar yang dikirim'], 422);
            }

            if (!empty($insertData)) {
                Facerecognition::insert($insertData);
            }

            return response()->json([
                'success' => true,
                'message' => count($saved) . ' gambar berhasil disimpan',
                'files'   => $saved,
                'total'   => Facerecognition::where('nik', $nik)->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan gambar'], 500);
        }
    }

    /**
     * DELETE /api/karyawan/face/{id}
     * Hapus satu data wajah milik karyawan yang login.
     */
    public function destroy(Request $request, $id)
    {
        $user        = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();

        if (!$userkaryawan) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404);
        }

        // Pastikan wajah milik karyawan yang login
        $wajah = Facerecognition::where('id', $id)
            ->where('nik', $userkaryawan->nik)
            ->first();

        if (!$wajah) {
            return response()->json(['success' => false, 'message' => 'Data wajah tidak ditemukan'], 404);
        }

        $karyawan   = Karyawan::where('nik', $userkaryawan->nik)->first();
        $namaFolder = $karyawan->nik . '-' . getNamaDepan(strtolower($karyawan->nama_karyawan));
        $filePath   = 'public/uploads/facerecognition/' . $namaFolder . '/' . $wajah->wajah;

        try {
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
            $wajah->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data wajah berhasil dihapus',
                'total'   => Facerecognition::where('nik', $userkaryawan->nik)->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data wajah'], 500);
        }
    }

    /**
     * DELETE /api/karyawan/face
     * Hapus semua data wajah milik karyawan yang login.
     */
    public function destroyAll(Request $request)
    {
        $user        = $request->user();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();

        if (!$userkaryawan) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404);
        }

        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();
        if (!$karyawan) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 404);
        }

        $namaFolder = $karyawan->nik . '-' . getNamaDepan(strtolower($karyawan->nama_karyawan));
        $folderPath = 'public/uploads/facerecognition/' . $namaFolder;

        try {
            if (Storage::exists('public/' . $folderPath) || Storage::exists($folderPath)) {
                $path = Storage::exists('public/' . $folderPath) ? 'public/' . $folderPath : $folderPath;
                Storage::deleteDirectory($path);
            }
            Facerecognition::where('nik', $userkaryawan->nik)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Semua data wajah berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data wajah'], 500);
        }
    }
}

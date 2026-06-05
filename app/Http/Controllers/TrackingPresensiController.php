<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Cabang;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TrackingPresensiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        // Default tanggal hari ini
        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));

        // Ambil cabang untuk filter berdasarkan akses user
        $cabangs = $user->getCabang();

        // Ambil data presensi dengan koordinat
        $presensis = $this->getPresensiData($tanggal, $request->get('kode_cabang'), $user, $request->get('kode_dept'), $request->get('sub_departemen'));

        // Ambil data cabang dengan radius untuk ditampilkan di peta
        $cabangRadius = $this->getCabangRadius($request->get('kode_cabang'), $user);

        return view('trackingpresensi.index', compact('presensis', 'cabangs', 'tanggal', 'cabangRadius'));
    }

    /**
     * Get presensi data with coordinates for AJAX
     */
    public function getData(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        $tanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $kode_cabang = $request->get('kode_cabang');
        $kode_dept = $request->get('kode_dept');
        $sub_departemen = $request->get('sub_departemen');

        $presensis = $this->getPresensiData($tanggal, $kode_cabang, $user, $kode_dept, $sub_departemen);
        $cabangRadius = $this->getCabangRadius($kode_cabang, $user);

        return response()->json([
            'presensis' => $presensis,
            'cabangRadius' => $cabangRadius
        ]);
    }

    /**
     * Get presensi data with coordinates
     */
    private function getPresensiData($tanggal, $kode_cabang = null, $user = null, $kode_dept = null, $sub_departemen = null)
    {
        $query = Presensi::select([
            'presensi.id',
            'presensi.nik',
            'presensi.tanggal',
            'presensi.jam_in',
            'presensi.jam_out',
            'presensi.lokasi_in',
            'presensi.lokasi_out',
            'presensi.foto_in',
            'presensi.foto_out',
            'karyawan.nama_karyawan',
            'karyawan.kode_cabang',
            'karyawan.kode_dept',
            'karyawan.sub_departemen',
            'cabang.nama_cabang',
            'cabang.lokasi_cabang',
            'cabang.radius_cabang'
        ])
            ->join('karyawan', 'presensi.nik', '=', 'karyawan.nik')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->where('presensi.tanggal', $tanggal)
            ->whereNotNull('presensi.lokasi_in')
            ->where('presensi.lokasi_in', '!=', '');

        // Filter berdasarkan akses cabang dan departemen jika bukan super admin
        if ($user && !$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            
            if (!empty($userCabangs)) {
                $query->whereIn('karyawan.kode_cabang', $userCabangs);
            } else {
                $query->whereRaw('1 = 0');
            }
            
            $deptAccessMap = $user->getDepartemenAccessMap();
            if (!empty($deptAccessMap)) {
                $query->where(function($q) use ($deptAccessMap) {
                    foreach ($deptAccessMap as $deptCode => $subDepts) {
                        if (empty($subDepts)) {
                            // Full access ke departemen ini
                            $q->orWhere('karyawan.kode_dept', $deptCode);
                        } else {
                            // Akses parsial (hanya sub-departemen tertentu)
                            $q->orWhere(function($q2) use ($deptCode, $subDepts) {
                                $q2->where('karyawan.kode_dept', $deptCode)
                                   ->whereIn('karyawan.sub_departemen', $subDepts);
                            });
                        }
                    }
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Filter by cabang jika dipilih
        if ($kode_cabang) {
            $query->where('karyawan.kode_cabang', $kode_cabang);
        }

        // Filter by departemen jika dipilih
        if ($kode_dept) {
            $query->where('karyawan.kode_dept', $kode_dept);
        }

        // Filter by sub_departemen (team) jika dipilih
        if ($sub_departemen) {
            $query->where('karyawan.sub_departemen', $sub_departemen);
        }

        $presensis = $query->get();

        // Parse koordinat dari field lokasi_in dan tambahkan offset untuk marker yang sama
        $coordinateCount = [];
        $presensis->transform(function ($presensi) use (&$coordinateCount) {
            $lokasi_in = $presensi->lokasi_in;

            // Parse koordinat dari format "lat,lng" atau "latitude,longitude"
            if (strpos($lokasi_in, ',') !== false) {
                $coords = explode(',', $lokasi_in);
                if (count($coords) >= 2) {
                    $lat = floatval(trim($coords[0]));
                    $lng = floatval(trim($coords[1]));

                    // Buat key untuk koordinat
                    $coordKey = $lat . ',' . $lng;

                    // Hitung berapa kali koordinat ini muncul
                    if (!isset($coordinateCount[$coordKey])) {
                        $coordinateCount[$coordKey] = 0;
                    }
                    $coordinateCount[$coordKey]++;

                    // Tambahkan offset kecil untuk marker yang sama (maksimal 5 marker)
                    $offset = ($coordinateCount[$coordKey] - 1) * 0.0001; // Offset sekitar 10 meter
                    if ($coordinateCount[$coordKey] > 5) {
                        $offset = (($coordinateCount[$coordKey] - 1) % 5) * 0.0001;
                    }

                    $presensi->latitude = $lat + $offset;
                    $presensi->longitude = $lng + $offset;
                    $presensi->original_latitude = $lat;
                    $presensi->original_longitude = $lng;
                    $presensi->marker_count = $coordinateCount[$coordKey];
                }
            }

            return $presensi;
        });

        return $presensis;
    }

    /**
     * Get cabang data with radius for map display
     */
    private function getCabangRadius($kode_cabang = null, $user = null)
    {
        $query = Cabang::select([
            'kode_cabang',
            'nama_cabang',
            'lokasi_cabang',
            'radius_cabang'
        ])
            ->whereNotNull('lokasi_cabang')
            ->where('lokasi_cabang', '!=', '')
            ->whereNotNull('radius_cabang')
            ->where('radius_cabang', '>', 0);

        // Filter berdasarkan akses cabang jika bukan super admin
        if ($user && !$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            if (!empty($userCabangs)) {
                $query->whereIn('kode_cabang', $userCabangs);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Filter by cabang jika dipilih
        if ($kode_cabang) {
            $query->where('kode_cabang', $kode_cabang);
        }

        $cabangs = $query->get();

        // Parse koordinat dari field lokasi_cabang
        $cabangs->transform(function ($cabang) {
            $lokasi_cabang = $cabang->lokasi_cabang;

            // Parse koordinat dari format "lat,lng" atau "latitude,longitude"
            if (strpos($lokasi_cabang, ',') !== false) {
                $coords = explode(',', $lokasi_cabang);
                if (count($coords) >= 2) {
                    $cabang->latitude = floatval(trim($coords[0]));
                    $cabang->longitude = floatval(trim($coords[1]));
                }
            }

            return $cabang;
        });

        return $cabangs;
    }
}

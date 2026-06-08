<?php

namespace App\Http\Controllers;

use App\Models\AjuanJadwal;
use App\Models\Cabang;
use App\Models\Denda;
use App\Models\Detailharilibur;
use App\Models\Detailsetjamkerjabydept;
use App\Models\Device;
use App\Models\Facerecognition;
use App\Models\GrupDetail;
use App\Models\GrupJamkerjaBydate;
use App\Models\Harilibur;
use App\Models\Izindinas;
use App\Models\Jamkerja;
use App\Models\Karyawan;
use App\Models\Pengaturanumum;
use App\Models\Presensi;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use App\Models\Setjamkerjabydept;
use App\Models\User;
use App\Models\Userkaryawan;
use App\Jobs\SendWaMessage;
use Carbon\Carbon;
use CURLFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    protected $presensiService;

    public function __construct(\App\Services\PresensiService $presensiService)
    {
        $this->presensiService = $presensiService;
    }

    public function index(Request $request)
    {
       
        $user = auth()->user();

        $tanggal = !empty($request->tanggal) ? $request->tanggal : date('Y-m-d');
        $presensi = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->select(
                'presensi.id',
                'presensi.nik',
                'presensi.tanggal',
                'presensi.kode_jam_kerja',
                'nama_jam_kerja',
                'jam_masuk',
                'jam_pulang',
                'istirahat',
                'jam_awal_istirahat',
                'jam_akhir_istirahat',
                'jam_in',
                'foto_in',
                'jam_out',
                'foto_out',
                'status',
                'lintashari',
                'total_jam',
                'presensi.denda',
                'presensi.status_potongan'
            )
            ->where('presensi.tanggal', $tanggal);

        $query = Karyawan::query();
        $query->select(
            'presensi.id',
            'karyawan.nik',
            'karyawan.nik_show',
            'nama_karyawan',
            'karyawan.foto',
            'karyawan.kode_dept',
            'karyawan.kode_cabang',
            'presensi.tanggal as tanggal_presensi',
            'presensi.jam_in',
            'presensi.kode_jam_kerja',
            'nama_jam_kerja',
            'jam_masuk',
            'jam_pulang',
            'istirahat',
            'jam_awal_istirahat',
            'jam_akhir_istirahat',
            'jam_in',
            'jam_out',
            'status',
            'foto_in',
            'foto_out',
            'lintashari',
            'karyawan.pin',
            'total_jam',
            'presensi.denda',
            'presensi.status_potongan'
        );
        $query->leftjoinSub($presensi, 'presensi', function ($join) {
            $join->on('karyawan.nik', '=', 'presensi.nik');
        });
        
        // Filter berdasarkan akses cabang dan departemen jika bukan super admin
        if (!$user->isSuperAdmin()) {
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
        
        // Urutkan berdasarkan jam clock in terbaru (descending), NULL di akhir
        $query->orderByRaw('CASE WHEN jam_in IS NULL THEN 1 ELSE 0 END');
        $query->orderBy('jam_in', 'DESC');
        
        if (!empty($request->kode_cabang)) {
            $query->where('karyawan.kode_cabang', $request->kode_cabang);
        }

        if (!empty($request->nama_karyawan)) {
            $query->where('nama_karyawan', 'like', '%' . $request->nama_karyawan . '%');
        }

        $karyawan = $query->paginate(10);
        $karyawan->appends(request()->all());
        $data['karyawan'] = $karyawan;
        $data['cabang'] = $user->getCabang();
        $data['denda_list'] = Denda::all()->toArray();
        return view('presensi.index', $data);
    }
    public function create(Request $request)
    {
        $kode_jam_kerja = $request->kode_jam_kerja ?? null;

        //Get Data Karyawan By User
        //Get Data Karyawan By User
        $user = User::where('id', auth()->user()->id)->first();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();

        if ($karyawan->lock_jam_kerja == 0 && $kode_jam_kerja == null) {
            $presensi = Presensi::where('nik', $karyawan->nik)->where('tanggal', date('Y-m-d'))->first();
            if ($presensi != null) {
                return redirect('/presensi/create?kode_jam_kerja=' . $presensi->kode_jam_kerja);
            }
            $data['jamkerja'] = Jamkerja::orderBy('jam_masuk')->get();
            return view('presensi.pilih_jam_kerja', $data);
        }

        $general_setting = Pengaturanumum::where('id', 1)->first();
        //Cek Lokasi Kantor
        $lokasi_kantor = Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();

        // Ambil timezone dari cabang (jika ada), jika tidak gunakan default sistem
        $timezone_cabang = $lokasi_kantor->timezone ?? $general_setting->timezone ?? config('app.timezone');

        // Gunakan Carbon dengan timezone cabang untuk mendapatkan waktu lokal cabang
        $carbon_now = Carbon::now($timezone_cabang);
        $hariini = $carbon_now->format('Y-m-d');
        $jamsekarang = $carbon_now->format('H:i');
        $tgl_sebelumnya = $carbon_now->copy()->subDay()->format('Y-m-d');
        $cekpresensi_sebelumnya = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('tanggal', $tgl_sebelumnya)
            ->where('nik', $karyawan->nik)
            ->first();

        // dd($cekpresensi_sebelumnya);
        $ceklintashari_presensi = $cekpresensi_sebelumnya != null  ? $cekpresensi_sebelumnya->lintashari : 0;

        if ($ceklintashari_presensi == 1) {
            if ($jamsekarang < $general_setting->batas_presensi_lintashari) {
                $hariini = $tgl_sebelumnya;
            }
        }

        $namahari = getnamaHari(date('D', strtotime($hariini)));

        $kode_dept = $karyawan->kode_dept;

        //Cek Presensi
        $presensi = Presensi::where('nik', $karyawan->nik)->where('tanggal', $hariini)->first();


        if ($kode_jam_kerja == null) {
            $jamkerja = $this->presensiService->getJamKerjaKaryawan($karyawan, $hariini);
        } else {
            $jamkerja = Jamkerja::where('kode_jam_kerja', $kode_jam_kerja)->first();
        }

        // dd($jamkerja);
        $ceklibur = Detailharilibur::join('hari_libur', 'hari_libur_detail.kode_libur', '=', 'hari_libur.kode_libur')
            ->where('nik', $karyawan->nik)
            ->where('tanggal', $hariini)
            ->first();
        $data['harilibur'] = $ceklibur;

        if ($presensi != null && $presensi->status != 'h') {
            return view('presensi.notif_izin');
        } else if ($ceklibur != null) {
            return view('presensi.notif_libur', $data);
        } else if ($jamkerja == null) {
            return view('presensi.notif_jamkerja');
        }

        $kode_cabang_array = $karyawan->kode_cabang_array ?? [];
        $data['cabang'] = Cabang::WhereIn('kode_cabang', $kode_cabang_array)
            ->orWhere('kode_cabang', $karyawan->kode_cabang)
            ->get();

        $data['hariini'] = $hariini;
        $data['jam_kerja'] = $jamkerja;
        $data['lokasi_kantor'] = $lokasi_kantor;
        $data['presensi'] = $presensi;
        $data['karyawan'] = $karyawan;
        $data['wajah'] = Facerecognition::where('nik', $karyawan->nik)->count();



        return view('presensi.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'status'          => 'required|in:1,2',
            'kode_jam_kerja'  => 'required|string',
            'lokasi'          => 'required|string',
            'lokasi_cabang'   => 'required|string',
        ]);

        $user = User::where('id', auth()->user()->id)->first();
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        $karyawan = Karyawan::where('nik', $userkaryawan->nik)->first();

        if (!$karyawan) {
            return response()->json(['status' => false, 'message' => 'Karyawan tidak ditemukan.'], 404);
        }

        $result = $this->presensiService->prosesPresensiMobile($request, $karyawan);
        
        $statusCode = $result['status_code'] ?? 200;
        unset($result['status_code']);

        // Mapper dari response API (success) ke response AJAX Web (status)
        if (isset($result['success'])) {
            $result['status'] = $result['success'];
            unset($result['success']);
        }

        return response()->json($result, $statusCode);
    }


    function sendwa($no_hp, $message)
    {
        dispatch(new SendWaMessage($no_hp, $message));
    }
    public function edit(Request $request)
    {
        $nik = Crypt::decrypt($request->nik);
        $tanggal = $request->tanggal;

        $karyawan = Karyawan::where('nik', $nik)->first();
        $jam_kerja = Jamkerja::all();
        $presensi = Presensi::where('nik', $nik)->where('tanggal', $tanggal)->first();
        if ($presensi && $presensi->status_potongan !== null) {
            return '<div class="alert alert-warning">Data Presensi Sudah Dikunci, Hubungi Admin Untuk Membuka Kunci Laporan</div>';
        }
        $data['presensi'] = $presensi;
        $data['karyawan'] = $karyawan;
        $data['jam_kerja'] = $jam_kerja;
        $data['tanggal'] = $tanggal;

        return view('presensi.edit', $data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'tanggal' => 'required',
            'kode_jam_kerja' => 'required',
            'status' => 'required',
        ]);
        $presensi = Presensi::where('nik', $request->nik)->where('tanggal', $request->tanggal)->first();
        if ($presensi && $presensi->status_potongan !== null) {
            return redirect()->back()->with(['warning' => 'Data Presensi Sudah Dikunci, Hubungi Admin Untuk Membuka Kunci Laporan']);
        }

        $nik = Crypt::decrypt($request->nik);
        $tanggal = $request->tanggal;
        $kode_jam_kerja = $request->kode_jam_kerja;
        $jam_in = $request->jam_in;
        $jam_out = $request->jam_out;
        $status = $request->status;

        try {
            $cekpresensi = Presensi::where('nik', $nik)->where('tanggal', $tanggal)->first();
            if (!empty($cekpresensi)) {
                Presensi::where('nik', $nik)->where('tanggal', $tanggal)->update([
                    'jam_in' => $jam_in,
                    'jam_out' => $jam_out,
                    'status' => $status,
                    'kode_jam_kerja' => $kode_jam_kerja,
                ]);
            } else {
                Presensi::create([
                    'nik' => $nik,
                    'tanggal' => $tanggal,
                    'jam_in' => $jam_in,
                    'jam_out' => $jam_out,
                    'kode_jam_kerja' => $kode_jam_kerja,
                    'status' => $status
                ]);
            }

            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }


    public function show($id, $status)
    {
        $presensi = Presensi::where('id', $id)
            ->join('karyawan', 'presensi.nik', '=', 'karyawan.nik')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        $cabang = Cabang::where('kode_cabang', $presensi->kode_cabang)->first();
        $lokasi = explode(',', $cabang->lokasi_cabang);
        $data['latitude'] = $lokasi[0];
        $data['longitude'] = $lokasi[1];
        // if (!empty($presensi->lokasi_cabang)) {
        //     $lokasi = explode(',', $presensi->lokasi_cabang);
        //     $data['latitude'] = $lokasi[0];
        //     $data['longitude'] = $lokasi[1];
        // } else {
        //     $data['latitude'] = $cabang->latitude_cabang;
        //     $data['longitude'] = $cabang->longitude_cabang;
        // }
        $data['presensi'] = $presensi;
        $data['status'] = $status;
        $data['cabang'] = $cabang;

        return view('presensi.show', $data);
    }


    public function getdatamesin(Request $request)
    {

        $tanggal = $request->tanggal;
        $pin = $request->pin;
        $general_setting = Pengaturanumum::where('id', 1)->first();
        // dd($pin);
        // $kode_jadwal = $request->kode_jadwal;
        // if ($kode_jadwal == "JD004") {
        //     $nextday = date('Y-m-d', strtotime('+1 day', strtotime($tanggal)));
        // } else {
        //     $nextday =  $tanggal;
        // }
        $specific_value = $pin;
        $karyawan = Karyawan::where('pin', $pin)->first();
        $is_locked = false;
        if ($karyawan) {
            $presensi_lock = Presensi::where('nik', $karyawan->nik)->where('tanggal', $tanggal)->first();
            if ($presensi_lock && $presensi_lock->status_potongan !== null) {
                $is_locked = true;
            }
        }


        //Mesin 1
        $url = 'https://developer.fingerspot.io/api/get_attlog';
        $data = '{"trans_id":"1", "cloud_id":"' . $general_setting->cloud_id . '", "start_date":"' . $tanggal . '", "end_date":"' . $tanggal . '"}';
        $authorization = "Authorization: Bearer " . $general_setting->api_key;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($result);
        $datamesin1 = $res->data;

        $filtered_array = array_filter($datamesin1, function ($obj) use ($specific_value) {
            return $obj->pin == $specific_value;
        });


        //Mesin 2
        // $url = 'https://developer.fingerspot.io/api/get_attlog';
        // $data = '{"trans_id":"1", "cloud_id":"C268909557211236", "start_date":"' . $tanggal . '", "end_date":"' . $tanggal . '"}';
        // $authorization = "Authorization: Bearer QNBCLO9OA0AWILQD";

        // $ch = curl_init($url);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        // $result2 = curl_exec($ch);
        // curl_close($ch);
        // $res2 = json_decode($result2);
        // $datamesin2 = $res2->data;

        // $filtered_array_2 = array_filter($datamesin2, function ($obj) use ($specific_value) {
        //     return $obj->pin == $specific_value;
        // });


        return view('presensi.getdatamesin', compact('filtered_array', 'is_locked'));
    }


    public function histori(Request $request)
    {
        $user = User::where('id', auth()->user()->id)->first();
        $userkaryawan = Userkaryawan::where('id_user', auth()->user()->id)->first();
        $data['datapresensi'] = Presensi::join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('presensi.nik', $userkaryawan->nik)
            ->leftJoin('presensi_izinabsen_approve', 'presensi.id', '=', 'presensi_izinabsen_approve.id_presensi')
            ->leftJoin('presensi_izinabsen', 'presensi_izinabsen_approve.kode_izin', '=', 'presensi_izinabsen.kode_izin')

            ->leftJoin('presensi_izinsakit_approve', 'presensi.id', '=', 'presensi_izinsakit_approve.id_presensi')
            ->leftJoin('presensi_izinsakit', 'presensi_izinsakit_approve.kode_izin_sakit', '=', 'presensi_izinsakit.kode_izin_sakit')

            ->leftJoin('presensi_izincuti_approve', 'presensi.id', '=', 'presensi_izincuti_approve.id_presensi')
            ->leftJoin('presensi_izincuti', 'presensi_izincuti_approve.kode_izin_cuti', '=', 'presensi_izincuti.kode_izin_cuti')
            ->select(
                'presensi.*',
                'presensi_jamkerja.nama_jam_kerja',
                'presensi_jamkerja.jam_masuk',
                'presensi_jamkerja.jam_pulang',
                'presensi_jamkerja.total_jam',
                'presensi_jamkerja.lintashari',
                'presensi_izinabsen.keterangan as keterangan_izin',
                'presensi_izinsakit.keterangan as keterangan_izin_sakit',
                'presensi_izincuti.keterangan as keterangan_izin_cuti'
            )
            ->when(!empty($request->dari) && !empty($request->sampai), function ($q) use ($request) {
                $q->whereBetween('presensi.tanggal', [$request->dari, $request->sampai]);
            })
            ->orderBy('presensi.tanggal', 'desc')
            ->limit(30)
            ->get();
            
        $data['namasettings'] = Pengaturanumum::first();
        $data['denda_list'] = Denda::orderBy('dari')->get()->toArray();
        
        return view('presensi.histori', $data);
    }


    public function updatefrommachine(Request $request, $pin, $status_scan)
    {
        $pin = Crypt::decrypt($pin);
        $scan = $request->scan_date;

        $karyawan       = Karyawan::where('pin', $pin)->first();

        if ($karyawan == null) {
            return Redirect::back()->with(messageError('Karyawan Tidak Ditemukan'));
            $nik = "";
        } else {
            $nik = $karyawan->nik;
        }

        // Ambil timezone dari cabang
        $cabang = Cabang::where('kode_cabang', $karyawan->kode_cabang)->first();
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $timezone_cabang = $cabang->timezone ?? $generalsetting->timezone ?? config('app.timezone');

        // Konversi waktu scan ke timezone cabang
        $carbon_scan = Carbon::parse($scan)->setTimezone($timezone_cabang);
        $tanggal_sekarang = $carbon_scan->format('Y-m-d');
        $jam_sekarang = $carbon_scan->format('H:i');
        $tanggal_kemarin = $carbon_scan->copy()->subDay()->format('Y-m-d');
        $tanggal_besok = $carbon_scan->copy()->addDay()->format('Y-m-d');

        //Cek Presensi Kemarin
        $presensi_kemarin = Presensi::where('nik', $karyawan->nik)
            ->join('presensi_jamkerja', 'presensi.kode_jam_kerja', '=', 'presensi_jamkerja.kode_jam_kerja')
            ->where('nik', $karyawan->nik)
            ->where('tanggal', $tanggal_kemarin)->first();

        $lintas_hari = $presensi_kemarin ? $presensi_kemarin->lintashari : 0;

        //Jika Presensi Kemarin Status Lintas Hari nya 1 Makan Tanggal Presensi Sekarang adalah Tanggal Kemarin
        $tanggal_presensi = $lintas_hari == 1 ? $tanggal_kemarin : $tanggal_sekarang;
        $tanggal_pulang = $lintas_hari == 1 ? $tanggal_besok : $tanggal_sekarang;


        $jamkerja = $this->presensiService->getJamKerjaKaryawan($karyawan, $tanggal_presensi);
        if ($jamkerja == null) {
            $jamkerja = Jamkerja::where('kode_jam_kerja', 'JK01')->first();
        }

        //Cek Presensi
        $presensi = Presensi::where('nik', $karyawan->nik)->where('tanggal', $tanggal_presensi)->first();

        //Cek Jika Laporan Sudah Dikunci
        if ($presensi != null && $presensi->status_potongan !== null) {
             return Redirect::back()->with(messageError('Data Presensi Sudah Dikunci'));
        }

        if ($presensi != null && $presensi->status != 'h') {
            return Redirect::back()->with(messageError('Sudah Melakukan Presesni'));
        } else if ($jamkerja == null) {
            return Redirect::back()->with(messageError('Tidak Memiliki Jadwal'));
        }

        $kode_jam_kerja = $jamkerja->kode_jam_kerja;
        $jam_kerja = Jamkerja::where('kode_jam_kerja', $kode_jam_kerja)->first();

        $jam_presensi = $tanggal_sekarang . " " . $jam_sekarang;

        $jam_masuk = $tanggal_presensi . " " . date('H:i', strtotime($jam_kerja->jam_masuk));

        $presensi_hariini = Presensi::where('nik', $karyawan->nik)
            ->where('tanggal', $tanggal_presensi)
            ->first();

        if (in_array($status_scan, [0, 2, 4, 6, 8])) {
            if ($presensi_hariini && $presensi_hariini->jam_in != null) {
                return Redirect::back()->with(messageError('Sudah Melakukan Presensi Masuk'));
            } else {
                try {
                    if ($presensi_hariini != null) {
                        Presensi::where('id', $presensi_hariini->id)->update([
                            'jam_in' => $jam_presensi,
                        ]);
                    } else {
                        Presensi::create([
                            'nik' => $karyawan->nik,
                            'tanggal' => $tanggal_presensi,
                            'jam_in' => $jam_presensi,
                            'jam_out' => null,
                            'lokasi_out' => null,
                            'foto_out' => null,
                            'kode_jam_kerja' => $kode_jam_kerja,
                            'status' => 'h'
                        ]);
                    }


                    return Redirect::back()->with(messageSuccess('Berhasil Melakukan Presensi Masuk'));
                } catch (\Exception $e) {
                    return Redirect::back()->with(messageError($e->getMessage()));
                }
            }
        } else {
            try {
                if ($presensi_hariini != null) {
                    Presensi::where('id', $presensi_hariini->id)->update([
                        'jam_out' => $jam_presensi,
                    ]);
                } else {
                    Presensi::create([
                        'nik' => $karyawan->nik,
                        'tanggal' => $tanggal_presensi,
                        'jam_in' => null,
                        'jam_out' => $jam_presensi,
                        'lokasi_in' => null,
                        'foto_in' => null,
                        'kode_jam_kerja' => $kode_jam_kerja,
                        'status' => 'h'
                    ]);
                }
                return Redirect::back()->with(messageSuccess('Berhasil Melakukan Presensi Pulang'));
            } catch (\Exception $e) {
                return Redirect::back()->with(messageError($e->getMessage()));
            }
        }
    }

    public function destroy($id)
    {
        $presensi = Presensi::find($id);
        if ($presensi) {
            if ($presensi->status_potongan != null) {
                return Redirect::back()->with(['warning' => 'Data Presensi Sudah Dikunci, Hubungi Admin Untuk Membuka Kunci Laporan']);
            }
            try {
                $folderPath = "public/uploads/absensi/";
                Storage::delete($folderPath . $presensi->foto_in);
                Storage::delete($folderPath . $presensi->foto_out);
                $presensi->delete();
                return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
            } catch (\Exception $e) {
                return Redirect::back()->with(messageError($e->getMessage()));
            }
        } else {
            return Redirect::back()->with(messageError('Data Tidak Ditemukan'));
        }
    }
}

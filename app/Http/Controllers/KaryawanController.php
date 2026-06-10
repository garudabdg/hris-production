<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Facerecognition;
use App\Models\Jabatan;
use App\Models\Jamkerja;
use App\Models\Karyawan;
use App\Models\MutasiKaryawan;
use App\Models\Pengaturanumum;
use App\Models\Setjamkerjabydate;
use App\Models\Setjamkerjabyday;
use App\Models\Statuskawin;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Userkaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Imports\KaryawanImport;
use App\Exports\TemplateKaryawanExport;
use App\Exports\KaryawanExport;
use App\Jobs\SendWaMessage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $query = Karyawan::query()->with('pelatihan');
        $latest_gaji = DB::table('karyawan_gaji_pokok')
            ->select('nik', 'jenis_upah')
            ->whereIn('kode_gaji', function ($query) {
                $query->select(DB::raw('MAX(kode_gaji)'))
                    ->from('karyawan_gaji_pokok')
                    ->groupBy('nik');
            });

        $query->select('karyawan.*', 'departemen.nama_dept', 'jabatan.nama_jabatan', 'cabang.nama_cabang', 'id_user', 'gaji.jenis_upah');
        $query->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept');
        $query->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan');
        $query->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang');
        $query->leftJoin('users_karyawan', 'karyawan.nik', '=', 'users_karyawan.nik');
        $query->leftJoinSub($latest_gaji, 'gaji', function ($join) {
            $join->on('karyawan.nik', '=', 'gaji.nik');
        });

        // Filter berdasarkan akses cabang dan departemen jika bukan super admin
        $query->accessFilter($user);

        if (!empty($request->kode_cabang)) {
            $query->where('karyawan.kode_cabang', $request->kode_cabang);
        }

        if (!empty($request->kode_dept)) {
            $query->where('karyawan.kode_dept', $request->kode_dept);
        }
        if (!empty($request->sub_departemen)) {
            $query->where('karyawan.sub_departemen', $request->sub_departemen);
        }
        if ($request->has('status_aktif') && $request->status_aktif !== '' && $request->status_aktif !== null) {
            $query->where('karyawan.status_aktif_karyawan', $request->status_aktif);
        }
        if (!empty($request->kode_group)) {
            $query->where('karyawan.kode_group', $request->kode_group);
        }

        if (filled($request->nama_karyawan)) {
            $keyword = $request->nama_karyawan;
            $query->where(function($q) use ($keyword) {
                $q->where('karyawan.nama_karyawan', 'like', '%' . $keyword . '%')
                  ->orWhere('karyawan.nik', 'like', '%' . $keyword . '%')
                  ->orWhere('karyawan.nik_show', 'like', '%' . $keyword . '%');
            });
        }
        $query->orderBy('karyawan.nama_karyawan', 'asc');
        $karyawan = $query->paginate(15);
        $karyawan->appends($request->all());

        $data['karyawan'] = $karyawan;
        $data['cabang'] = $user->getCabang();
        $data['departemen'] = $user->getDepartemen();

        return view('datamaster.karyawan.index', $data);
    }


    public function create()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $data['status_kawin'] = Statuskawin::orderBy('kode_status_kawin')->get();
        $data['cabang'] = $user->getCabang();
        $data['departemen'] = $user->getDepartemen();
        $data['jabatan'] = Jabatan::orderBy('kode_jabatan')->get();
        return view('datamaster.karyawan.create', $data);
    }


    public function generateNik()
    {
        try {
            $tahun = date('y');
            $bulan = date('m');
            $prefix = $tahun . $bulan; // e.g., 2510

            $last = Karyawan::where('nik', 'like', $prefix . '%')
                ->orderBy('nik', 'desc')
                ->first();

            $lastNumber = 0;
            if ($last) {
                $lastNumber = (int)substr($last->nik, 4, 5);
            }
            $nextNumber = $lastNumber + 1;
            $nikAuto = $prefix . str_pad((string)$nextNumber, 5, '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true,
                'nik' => $nikAuto
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(\App\Http\Requests\Karyawan\StoreKaryawanRequest $request, \App\Services\KaryawanService $karyawanService)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Validasi akses cabang dan departemen jika bukan super admin
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();

            if (!in_array($request->kode_cabang, $userCabangs)) {
                return Redirect::back()->with(messageError('Anda tidak memiliki akses ke cabang yang dipilih'));
            }

            if (!in_array($request->kode_dept, $userDepartemens)) {
                return Redirect::back()->with(messageError('Anda tidak memiliki akses ke departemen yang dipilih'));
            }
        }

        try {
            $data = $request->validated();
            unset($data['foto']);
            $file = $request->file('foto');
            
            $karyawanService->storeKaryawan($data, $file);

            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }


    public function edit($nik)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $nik = Crypt::decrypt($nik);
        $data['karyawan'] = Karyawan::where('nik', $nik)->first();
        $data['status_kawin'] = Statuskawin::orderBy('kode_status_kawin')->get();
        $data['cabang'] = $user->getCabang();
        $data['departemen'] = $user->getDepartemen();
        $data['jabatan'] = Jabatan::orderBy('kode_jabatan')->get();
        return view('datamaster.karyawan.edit', $data);
    }


    public function update($nik, \App\Http\Requests\Karyawan\UpdateKaryawanRequest $request, \App\Services\KaryawanService $karyawanService)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $nik = Crypt::decrypt($nik);

        // Validasi akses cabang dan departemen jika bukan super admin
        if (!$user->isSuperAdmin()) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();

            if (!in_array($request->kode_cabang, $userCabangs)) {
                return Redirect::back()->with(messageError('Anda tidak memiliki akses ke cabang yang dipilih'));
            }

            if (!in_array($request->kode_dept, $userDepartemens)) {
                return Redirect::back()->with(messageError('Anda tidak memiliki akses ke departemen yang dipilih'));
            }
        }

        try {
            $data = $request->validated();
            unset($data['foto']);
            $file = $request->file('foto');
            
            $karyawanService->updateKaryawan($nik, $data, $file);

            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }


    public function lockunlocklocation($nik)
    {
        $nik = Crypt::decrypt($nik);
        try {
            $karyawan = Karyawan::where('nik', $nik)->first();
            if ($karyawan->lock_location == '1') {
                $lock_location = 0;
            } else {
                $lock_location = 1;
            }

            Karyawan::where('nik', $nik)->update([
                'lock_location' => $lock_location
            ]);
            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function lockunlockjamkerja($nik)
    {
        $nik = Crypt::decrypt($nik);
        try {
            $karyawan = Karyawan::where('nik', $nik)->first();
            if ($karyawan->lock_jam_kerja == '1') {
                $lock_jam_kerja = 0;
            } else {
                $lock_jam_kerja = 1;
            }

            Karyawan::where('nik', $nik)->update([
                'lock_jam_kerja' => $lock_jam_kerja
            ]);
            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function show($nik)
    {
        $nik = Crypt::decrypt($nik);
        $karyawan = Karyawan::with('pelatihan')->where('nik', $nik)
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('status_kawin', 'karyawan.kode_status_kawin', '=', 'status_kawin.kode_status_kawin')

            ->first();
        $user_karyawan = Userkaryawan::where('nik', $nik)->first();
        $user = $user_karyawan ? User::where('id', $user_karyawan->id_user)->first() : null;
        $karyawan_wajah = Facerecognition::where('nik', $nik)->get();
        $mutasi = MutasiKaryawan::with(['cabangLama', 'cabangBaru', 'deptLama', 'deptBaru', 'jabatanLama', 'jabatanBaru'])
            ->where('nik', $nik)
            ->orderBy('tanggal_mutasi', 'desc')
            ->get();
            
        // Get Assets assigned to this Karyawan
        $assets = \App\Models\Asset::where('nik', $nik)->get();

        // Get Total Tickets created by this Karyawan
        $total_tickets = 0;
        if ($user) {
            $total_tickets = \App\Models\ItTicket::where('pemohon_id', $user->id)->count();
        }
            
        $data['karyawan'] = $karyawan;
        $data['user'] = $user;
        $data['karyawan_wajah'] = $karyawan_wajah;
        $data['mutasi'] = $mutasi;
        $data['assets'] = $assets;
        $data['total_tickets'] = $total_tickets;
        return view('datamaster.karyawan.show', $data);
    }

    public function exportPdf($nik)
    {
        $nik = Crypt::decrypt($nik);
        $karyawan = Karyawan::with('pelatihan')->where('nik', $nik)
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('status_kawin', 'karyawan.kode_status_kawin', '=', 'status_kawin.kode_status_kawin')
            ->first();

        if (!$karyawan) abort(404, 'Karyawan tidak ditemukan.');

        $user_karyawan = Userkaryawan::where('nik', $nik)->first();
        $user = $user_karyawan ? User::where('id', $user_karyawan->id_user)->first() : null;
        
        $mutasi = MutasiKaryawan::with(['cabangLama', 'cabangBaru', 'deptLama', 'deptBaru', 'jabatanLama', 'jabatanBaru'])
            ->where('nik', $nik)
            ->orderBy('tanggal_mutasi', 'desc')
            ->get();
            
        $assets = \App\Models\Asset::where('nik', $nik)->get();

        $total_tickets = 0;
        if ($user) {
            $total_tickets = \App\Models\ItTicket::where('pemohon_id', $user->id)->count();
        }

        $kontrak = \App\Models\Kontrak::where('nik', $nik)->orderBy('sampai', 'desc')->first();
            
        $data['karyawan'] = $karyawan;
        $data['user'] = $user;
        $data['mutasi'] = $mutasi;
        $data['assets'] = $assets;
        $data['total_tickets'] = $total_tickets;
        $data['kontrak'] = $kontrak;

        $pdf = Pdf::loadView('datamaster.karyawan.pdf_profile', $data);
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Profil_Karyawan_' . str_replace(' ', '_', $karyawan->nama_karyawan) . '.pdf';
        return $pdf->download($filename);
    }


    public function destroy($nik)
    {
        $nik = Crypt::decrypt($nik);
        try {
            $karyawan = Karyawan::where('nik', $nik)->first();
            $user_karyawan = Userkaryawan::where('nik', $nik)->first();
            if (!empty($user_karyawan)) {
                User::where('id', $user_karyawan->id_user)->delete();
                Userkaryawan::where('nik', $nik)->delete();
            }
            //$facerecognition = Facerecognition::where('nik', $nik)->get();
            // foreach ($facerecognition as $fr) {
            //     $nama_file = $facerecognition->wajah;
            //     $nama_folder = $karyawan->nik . "-" . getNamaDepan(strtolower($karyawan->nama_karyawan));
            //     $path = 'public/uploads/facerecognition/' . $nama_folder . "/" . $nama_file;
            //     Storage::delete($path);
            // }

            $nama_folder = $karyawan->nik . "-" . getNamaDepan(strtolower($karyawan->nama_karyawan));
            $path_folder = 'public/uploads/facerecognition/' . $nama_folder;
            Storage::deleteDirectory($path_folder);


            $nama_file_foto = $karyawan->foto;
            $path_foto = '/public/karyawan/' . $nama_file_foto;
            Storage::delete($path_foto);
            Karyawan::where('nik', $nik)->delete();
            return Redirect::back()->with(messageSuccess('Data Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function setjamkerja($nik)
    {
        $nik = Crypt::decrypt($nik);
        $data['karyawan'] = Karyawan::where('nik', $nik)
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        $data['list_bulan'] = config('global.list_bulan');
        $data['start_year'] = config('global.start_year');
        $data['jamkerja'] = Jamkerja::orderBy('kode_jam_kerja')->get();
        $data['jamkerjabyday'] = Setjamkerjabyday::where('nik', $nik)->pluck('kode_jam_kerja', 'hari')->toArray();
        // dd($data['jamkerjabyday']);
        return view('datamaster.karyawan.setjamkerja', $data);
    }

    public function setcabang($nik)
    {
        $nik = Crypt::decrypt($nik);
        $data['karyawan'] = Karyawan::where('nik', $nik)
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();
        // Exclude cabang utama dari pilihan
        $data['cabang'] = Cabang::where('kode_cabang', '!=', $data['karyawan']->kode_cabang)->orderBy('kode_cabang')->get();
        $data['kode_cabang_array'] = $data['karyawan']->kode_cabang_array ?? [];
        return view('datamaster.karyawan.setcabang', $data);
    }

    public function storecabang(Request $request, $nik)
    {
        $nik = Crypt::decrypt($nik);
        try {
            // Ambil cabang utama karyawan
            $karyawan = Karyawan::where('nik', $nik)->first();
            $kode_cabang_utama = $karyawan->kode_cabang;

            // Gabungkan cabang utama dengan cabang yang dipilih
            $kode_cabang_array = $request->kode_cabang_array ?? [];
            $kode_cabang_array[] = $kode_cabang_utama; // Tambahkan cabang utama
            $kode_cabang_array = array_unique($kode_cabang_array); // Hapus duplikasi

            Karyawan::where('nik', $nik)->update([
                'kode_cabang_array' => $kode_cabang_array
            ]);
            return Redirect::back()->with(messageSuccess('Data Cabang Berhasil Disimpan'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }


    public function storejamkerjabyday(Request $request, $nik)
    {
        $nik = Crypt::decrypt($nik);
        $hari = $request->hari;
        $kode_jam_kerja = $request->kode_jam_kerja;
        DB::beginTransaction();
        try {
            Setjamkerjabyday::where('nik', $nik)->delete();
            
            $dataInsert = [];
            $now = now();
            for ($i = 0; $i < count($hari); $i++) {
                if (!empty($kode_jam_kerja[$i])) {
                    $dataInsert[] = [
                        'nik' => $nik,
                        'hari' => $hari[$i],
                        'kode_jam_kerja' => $kode_jam_kerja[$i],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            
            if (!empty($dataInsert)) {
                Setjamkerjabyday::insert($dataInsert);
            }
            
            DB::commit();
            return Redirect::back()->with(messageSuccess('Data Berhasil Disimpan'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function storejamkerjabydate(Request $request)
    {
        // Convert tanggal to proper format (YYYY-MM-DD) to avoid timezone issues
        $tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');

        try {
            $cek = Setjamkerjabydate::where('nik', $request->nik)->where('tanggal', $tanggal)->first();
            if (!empty($cek)) {
                // Update jika sudah ada
                Setjamkerjabydate::where('nik', $request->nik)->where('tanggal', $tanggal)->update([
                    'kode_jam_kerja' => $request->kode_jam_kerja
                ]);
                return response()->json(['success' => true, 'message' => 'Data Berhasil Diupdate']);
            }

            // Simpan baru
            Setjamkerjabydate::create([
                'nik' => $request->nik,
                'tanggal' => $tanggal,
                'kode_jam_kerja' => $request->kode_jam_kerja
            ]);

            return response()->json(['success' => true, 'message' => 'Data Berhasil Disimpan']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function getjamkerjabydate(Request $request)
    {
        $nik = $request->nik;
        $tanggal = $request->tanggal;
        $bulan = $request->bulan;
        $tahun = $request->tahun;

        $jamkerjabydate = Setjamkerjabydate::where('nik', $nik)
            ->join('presensi_jamkerja', 'presensi_jamkerja.kode_jam_kerja', '=', 'presensi_jamkerja_bydate.kode_jam_kerja')
            ->whereRaw('MONTH(tanggal) = ? AND YEAR(tanggal) = ?', [$bulan, $tahun])
            ->orderBy('tanggal', 'asc')
            ->get();


        return response()->json($jamkerjabydate);
    }

    public function deletejamkerjabydate(Request $request)
    {
        // Convert tanggal to proper format (YYYY-MM-DD) to avoid timezone issues
        $tanggal = Carbon::parse($request->tanggal)->format('Y-m-d');

        try {
            Setjamkerjabydate::where('nik', $request->nik)->where('tanggal', $tanggal)->delete();
            return response()->json(['success' => true, 'message' => 'Data Berhasil Dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function createuser($nik)
    {
        $generalsetting = Pengaturanumum::first();
        $nik = Crypt::decrypt($nik);
        $karyawan = Karyawan::where('nik', $nik)->first();
        DB::beginTransaction();
        try {
            //code...
            $user = User::create([
                'name' => $karyawan->nama_karyawan,
                'username' => $karyawan->nik,
                'password' => Hash::make($karyawan->nik),
                'email' => strtolower(removeTitik($karyawan->nik)) . '@belum.diset',
            ]);

            Userkaryawan::create([
                'nik' => $nik,
                'id_user' => $user->id
            ]);

            $user->assignRole('karyawan');
            DB::commit();

            // Kirim notifikasi WA ke karyawan
            if (!empty($karyawan->no_hp)) {
                $appName = optional(Pengaturanumum::first())->nama_perusahaan ?? 'HRIS';
                $apkUrl = route('download.apk');
                $waMessage = "Halo *{$karyawan->nama_karyawan}*,\n\n"
                    . "Akun HRIS *{$appName}* Anda telah dibuat.\n\n"
                    . "🔐 *Informasi Login Sementara:*\n"
                    . "Username: *{$karyawan->nik}*\n"
                    . "Password: *{$karyawan->nik}*\n\n"
                    . "📱 *Download Aplikasi Android:*\n{$apkUrl}\n\n"
                    . "⚠️ *PENTING:*\n"
                    . "Silakan login sekarang juga untuk *melengkapi Profil Anda* (mengubah Username, Email aktif, dan Password baru).\n\n"
                    . "Terima kasih.";
                SendWaMessage::dispatch($karyawan->no_hp, $waMessage, false, true, 'presensi');
            }

            return Redirect::route('karyawan.index')->with(messageSuccess('User Berhasil Dibuat'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function generateAllUser()
    {
        $generalsetting = Pengaturanumum::first();
        // Get all active employees who don't have a user yet
        $karyawan = Karyawan::where('status_aktif_karyawan', 1)
            ->leftJoin('users_karyawan', 'karyawan.nik', '=', 'users_karyawan.nik')
            ->whereNull('users_karyawan.id_user')
            ->select('karyawan.*')
            ->get();

        if ($karyawan->isEmpty()) {
            return Redirect::back()->with(messageError('Tidak ada karyawan aktif yang belum memiliki user'));
        }

        DB::beginTransaction();
        try {
            $count = 0;
            $appName = optional($generalsetting)->nama_perusahaan ?? 'HRIS';
            $apkUrl = route('download.apk');
            $now = now();
            
            // Dapatkan ID Role karyawan
            $role = \Spatie\Permission\Models\Role::findByName('karyawan');

            // Proses dalam chunk untuk menghindari memory limit dan N+1
            foreach ($karyawan->chunk(100) as $chunk) {
                $usersData = [];
                $niks = [];
                $waJobs = [];

                foreach ($chunk as $k) {
                    $niks[] = $k->nik;
                    $usersData[] = [
                        'name' => $k->nama_karyawan,
                        'username' => $k->nik,
                        'password' => Hash::make($k->nik),
                        'email' => strtolower(removeTitik($k->nik)) . '@belum.diset',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    
                    if (!empty($k->no_hp)) {
                        $waMessage = "Halo *{$k->nama_karyawan}*,\n\n"
                            . "Akun HRIS *{$appName}* Anda telah dibuat.\n\n"
                            . "🔐 *Informasi Login Sementara:*\n"
                            . "Username: *{$k->nik}*\n"
                            . "Password: *{$k->nik}*\n\n"
                            . "📱 *Download Aplikasi Android:*\n{$apkUrl}\n\n"
                            . "⚠️ *PENTING:*\n"
                            . "Silakan login sekarang juga untuk *melengkapi Profil Anda* (mengubah Username, Email aktif, dan Password baru).\n\n"
                            . "Terima kasih.";
                        
                        $waJobs[] = [
                            'no_hp' => $k->no_hp,
                            'message' => $waMessage
                        ];
                    }
                }

                // Batch Insert Users
                User::insert($usersData);
                
                // Ambil kembali User yang baru diinsert (karena insert tidak mereturn ID)
                $insertedUsers = User::whereIn('username', $niks)->get();
                
                $userKaryawanData = [];
                $modelHasRolesData = [];
                
                foreach ($insertedUsers as $user) {
                    $userKaryawanData[] = [
                        'nik' => $user->username,
                        'id_user' => $user->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    
                    $modelHasRolesData[] = [
                        'role_id' => $role->id,
                        'model_type' => 'App\Models\User',
                        'model_id' => $user->id
                    ];
                    $count++;
                }
                
                // Batch Insert Userkaryawan & Roles
                Userkaryawan::insert($userKaryawanData);
                DB::table('model_has_roles')->insert($modelHasRolesData);
                
                // Dispatch Job Notifikasi WA
                foreach ($waJobs as $job) {
                    SendWaMessage::dispatch($job['no_hp'], $job['message'], false, true, 'presensi');
                }
            }

            DB::commit();
            return Redirect::back()->with(messageSuccess($count . ' User Berhasil Dibuat'));
        } catch (\Exception $e) {
            DB::rollBack();
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function deleteuser($nik)
    {
        $nik = Crypt::decrypt($nik);
        try {
            $user_karyawan = Userkaryawan::where('nik', $nik)->first();
            User::where('id', $user_karyawan->id_user)->delete();
            Userkaryawan::where('nik', $nik)->delete();
            return Redirect::back()->with(messageSuccess('User Berhasil Dihapus'));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError('Data User gagal dihapus ' . $e->getMessage()));
        }
    }

    public function import()
    {
        return view('datamaster.karyawan.import_modal');
    }

    public function download_template()
    {
        return Excel::download(new TemplateKaryawanExport, 'template_import_karyawan.xlsx');
    }

    public function import_proses(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('file');
            Excel::import(new KaryawanImport, $file);
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diimport'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function export(Request $request)
    {
        return Excel::download(new KaryawanExport($request->all()), 'karyawan_export.xlsx');
    }


    public function getkaryawan(Request $request)
    {
        $kode_cabang = array_filter((array) $request->kode_cabang);
        $kode_dept   = array_filter((array) $request->kode_dept);

        // Batasi akses berdasarkan cabang user login
        $allowedCabang = auth()->user()->getCabangCodes();

        $query = Karyawan::query();

        // Jika user bukan super admin, batasi ke cabang yang diizinkan
        if (!empty($allowedCabang)) {
            // Jika ada filter cabang dari request, intersect dengan cabang yang diizinkan
            if (!empty($kode_cabang)) {
                $kode_cabang = array_intersect($kode_cabang, $allowedCabang);
            } else {
                $kode_cabang = $allowedCabang;
            }
        }

        if (!empty($kode_cabang)) {
            $query->whereIn('kode_cabang', $kode_cabang);
        }

        if (!empty($kode_dept)) {
            $query->whereIn('kode_dept', $kode_dept);
        }

        $karyawan = $query->orderBy('nama_karyawan')->get();
        return response()->json($karyawan);
    }

    public function bulkLockUnlock(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $query = Karyawan::query();

        $query->accessFilter($user);

        if (!empty($request->kode_cabang)) {
            $query->where('kode_cabang', $request->kode_cabang);
        }
        if (!empty($request->kode_dept)) {
            $query->where('kode_dept', $request->kode_dept);
        }
        if (!empty($request->sub_departemen)) {
            $query->where('sub_departemen', $request->sub_departemen);
        }
        if ($request->has('status_aktif') && $request->status_aktif !== '' && $request->status_aktif !== null) {
            $query->where('status_aktif_karyawan', $request->status_aktif);
        }
        if (!empty($request->kode_group)) {
            $query->where('kode_group', $request->kode_group);
        }

        if (filled($request->nama_karyawan)) {
            $keyword = $request->nama_karyawan;
            $query->where(function($q) use ($keyword) {
                $q->where('nama_karyawan', 'like', '%' . $keyword . '%')
                  ->orWhere('nik', 'like', '%' . $keyword . '%')
                  ->orWhere('nik_show', 'like', '%' . $keyword . '%');
            });
        }

        $action = $request->input('bulk_action');
        
        try {
            $updateData = [];
            $message = "Pengaturan berhasil diperbarui.";

            switch ($action) {
                case 'lock_location':
                    $updateData['lock_location'] = 1;
                    $message = "Lock Location berhasil diaktifkan untuk karyawan yang difilter.";
                    break;
                case 'unlock_location':
                    $updateData['lock_location'] = 0;
                    $message = "Lock Location berhasil dinonaktifkan untuk karyawan yang difilter.";
                    break;
                case 'lock_jam_kerja':
                    $updateData['lock_jam_kerja'] = 1;
                    $message = "Lock Jam Kerja berhasil diaktifkan untuk karyawan yang difilter.";
                    break;
                case 'unlock_jam_kerja':
                    $updateData['lock_jam_kerja'] = 0;
                    $message = "Lock Jam Kerja berhasil dinonaktifkan untuk karyawan yang difilter.";
                    break;
                default:
                    return Redirect::back()->with(messageError('Aksi tidak valid.'));
            }

            $query->update($updateData);

            return Redirect::back()->with(messageSuccess($message));
        } catch (\Exception $e) {
            return Redirect::back()->with(messageError($e->getMessage()));
        }
    }

    public function idcard($nik)
    {
        $nik = Crypt::decrypt($nik);
        $karyawan = Karyawan::where('nik', $nik)
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->first();
        $data['karyawan'] = $karyawan;
        $generalsetting = Pengaturanumum::where('id', 1)->first();
        $data['generalsetting'] = $generalsetting;
        return view('datamaster.karyawan.idcard', $data);
    }
}

<?php

namespace App\Services;

use App\Models\Cabang;
use App\Models\Departemen;
use App\Models\Denda;
use App\Models\Karyawan;
use App\Models\Lembur;
use App\Models\Presensi;
use App\Models\Pengumuman;
use App\Models\Userkaryawan;
use App\Models\Pengaturanumum;
use App\Http\Controllers\KaryawanApprovalController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getKaryawanData($user)
    {
        $hari_ini = Carbon::now(config('app.timezone'))->format('Y-m-d');
        $userkaryawan = Userkaryawan::where('id_user', $user->id)->first();
        
        $data['karyawan'] = Karyawan::where('nik', $userkaryawan->nik)
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->first();

        $data['presensi'] = Presensi::where('presensi.nik', $userkaryawan->nik)->where('presensi.tanggal', $hari_ini)->first();
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
                'presensi_izincuti.keterangan as keterangan_izin_cuti',
            )
            ->orderBy('presensi.tanggal', 'desc')
            ->limit(30)
            ->get();
            
        $data['rekappresensi'] = Presensi::select(
            DB::raw("SUM(IF(status='h',1,0)) as hadir"),
            DB::raw("SUM(IF(status='i',1,0)) as izin"),
            DB::raw("SUM(IF(status='s',1,0)) as sakit"),
            DB::raw("SUM(IF(status='a',1,0)) as alpa"),
            DB::raw("SUM(IF(status='c',1,0)) as cuti")
        )
            ->groupBy('presensi.nik')
            ->whereRaw('MONTH(presensi.tanggal) = MONTH(?)', [$hari_ini])
            ->whereRaw('YEAR(presensi.tanggal) = YEAR(?)', [$hari_ini])
            ->where('presensi.nik', $userkaryawan->nik)
            ->first();

        // Cek departemen karyawan
        $hideLembur = false;
        if ($data['karyawan'] && $data['karyawan']->kode_dept == 'BU') {
            $hideLembur = true;
        }
        
        if (!$hideLembur) {
            $data['lembur'] = Lembur::where('nik', $userkaryawan->nik)->where('status', 1)
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();
            $data['notiflembur'] = Lembur::where('nik', $userkaryawan->nik)
                ->where('status', 1)
                ->where(function ($q) {
                    $q->whereNull('lembur_in')->orWhereNull('lembur_out');
                })
                ->count();
        } else {
            $data['lembur'] = collect(); // Empty collection
            $data['notiflembur'] = 0;
        }
        $data['hideLembur'] = $hideLembur;

        // Cek apakah hari ini adalah ulang tahun karyawan
        $isBirthday = false;
        $umur = null;
        if ($data['karyawan'] && $data['karyawan']->tanggal_lahir) {
            $tanggalLahir = Carbon::parse($data['karyawan']->tanggal_lahir);
            $today = Carbon::now();
            if ($tanggalLahir->month == $today->month && $tanggalLahir->day == $today->day) {
                $isBirthday = true;
                $umur = $tanggalLahir->age;
            }
        }
        $data['is_birthday'] = $isBirthday;
        $data['umur'] = $umur;

        // Cek Notifikasi Kontrak Berakhir (H-30)
        $kontrak = DB::table('kontrak')
            ->where('nik', $userkaryawan->nik)
            ->where('status_kontrak', '1')
            ->orderBy('sampai', 'desc')
            ->first();

        $notif_kontrak = null;
        if ($kontrak) {
            $tgl_akhir = Carbon::parse($kontrak->sampai);
            $today = Carbon::now(config('app.timezone'));
            $sisa_hari = $today->diffInDays($tgl_akhir, false);

            if ($sisa_hari >= 0 && $sisa_hari <= 30) {
                 $notif_kontrak = [
                    'sisa_hari' => $sisa_hari,
                    'tanggal_akhir' => $tgl_akhir->translatedFormat('d F Y')
                ];
            }
        }
        $data['notif_kontrak'] = $notif_kontrak;

        // Cek Notifikasi SP Aktif
        $notif_sp = DB::table('pelanggaran')
            ->where('nik', $userkaryawan->nik)
            ->where('dari', '<=', $today->toDateString())
            ->where('sampai', '>=', $today->toDateString())
            ->first();
        
        $data['notif_sp'] = $notif_sp;

        // Cek Pengumuman Aktif
        $data['pengumuman'] = Pengumuman::orderBy('created_at', 'desc')->first();
        $data['namasettings'] = Pengaturanumum::first();
        $data['denda_list'] = Denda::orderBy('dari')->get()->toArray();
        $data['pendingApprovalCount'] = KaryawanApprovalController::getPendingCount($user->id);
        $data['bulan_skrg'] = Carbon::parse($hari_ini)->translatedFormat('F');
        $data['tahun_skrg'] = Carbon::parse($hari_ini)->year;

        return $data;
    }

    public function getAdminData($user, Request $request, $chart, $jkchart, $pddchart)
    {
        $sk = new Karyawan();

        // 1. Ambil Permission Sekali Saja
        $isSuperAdmin = $user->isSuperAdmin();
        $userCabangs = null;
        $userDepartemens = null;

        if (!$isSuperAdmin) {
            $userCabangs = $user->getCabangCodes();
            $userDepartemens = $user->getDepartemenCodes();
        }

        // 2. Modifikasi request untuk getRekapstatuskaryawan
        $filterRequest = new Request($request->all());
        if (!$isSuperAdmin) {
            if (empty($userCabangs) || empty($userDepartemens)) {
                $filterRequest->merge(['kode_cabang' => 'INVALID']);
            } else {
                if (empty($filterRequest->kode_cabang) && count($userCabangs) == 1) {
                    $filterRequest->merge(['kode_cabang' => $userCabangs[0]]);
                }
                if (empty($filterRequest->kode_dept) && count($userDepartemens) == 1) {
                    $filterRequest->merge(['kode_dept' => $userDepartemens[0]]);
                }
            }
        }
        $data['status_karyawan'] = $sk->getRekapstatuskaryawan($filterRequest);

        // 3. Modifikasi request untuk chart
        $chartRequest = new Request($request->all());
        if (!$isSuperAdmin) {
            if (!empty($userCabangs)) $chartRequest->merge(['user_cabangs' => $userCabangs]);
            if (!empty($userDepartemens)) $chartRequest->merge(['user_departemens' => $userDepartemens]);
        }

        $data['chart'] = $chart->build($chartRequest);
        $data['jkchart'] = $jkchart->build($chartRequest);
        $data['pddchart'] = $pddchart->build($chartRequest);

        // 4. Query Presensi
        $queryPresensi = Presensi::query()
            ->join('karyawan', 'presensi.nik', '=', 'karyawan.nik')
            ->select(
                DB::raw("SUM(IF(status='h',1,0)) as hadir"),
                DB::raw("SUM(IF(status='i',1,0)) as izin"),
                DB::raw("SUM(IF(status='s',1,0)) as sakit"),
                DB::raw("SUM(IF(status='a',1,0)) as alpa"),
                DB::raw("SUM(IF(status='c',1,0)) as cuti")
            );

        if (!$isSuperAdmin) {
            if (!empty($userCabangs)) {
                $queryPresensi->whereIn('karyawan.kode_cabang', $userCabangs);
            } else {
                $queryPresensi->whereRaw('1 = 0');
            }

            if (!empty($userDepartemens)) {
                $queryPresensi->whereIn('karyawan.kode_dept', $userDepartemens);
            } else {
                $queryPresensi->whereRaw('1 = 0');
            }
        }

        if (!empty($request->tanggal)) {
            $queryPresensi->where('tanggal', $request->tanggal);
        } else {
            $queryPresensi->where('tanggal', Carbon::now(config('app.timezone'))->format('Y-m-d'));
        }

        if (!empty($request->kode_cabang)) $queryPresensi->where('karyawan.kode_cabang', $request->kode_cabang);
        if (!empty($request->kode_dept)) $queryPresensi->where('karyawan.kode_dept', $request->kode_dept);
        
        $data['rekappresensi'] = $queryPresensi->first();
        $data['departemen'] = $user->getDepartemen();
        $data['cabang'] = $user->getCabang();
        
        // 5. Birthday & Kontrak
        $today = Carbon::now(config('app.timezone'));
        $data['birthday'] = Karyawan::whereMonth('tanggal_lahir', $today->month)->whereDay('tanggal_lahir', $today->day)
            ->join('jabatan', 'karyawan.kode_jabatan', '=', 'jabatan.kode_jabatan')
            ->join('departemen', 'karyawan.kode_dept', '=', 'departemen.kode_dept')
            ->join('cabang', 'karyawan.kode_cabang', '=', 'cabang.kode_cabang')
            ->select(
                'karyawan.*',
                'jabatan.nama_jabatan',
                'departemen.nama_dept',
                'cabang.nama_cabang',
                'karyawan.status_karyawan'
            )
            ->accessFilter($user)
            ->when($request->kode_cabang, function ($query) use ($request) {
                $query->where('karyawan.kode_cabang', $request->kode_cabang);
            })
            ->when($request->kode_dept, function ($query) use ($request) {
                $query->where('karyawan.kode_dept', $request->kode_dept);
            })
            ->orderBy('tanggal_lahir', 'asc')->get();

        $data['kontrak_lewat'] = $sk->getRekapkontrak(0, $userCabangs, $userDepartemens);
        $data['kontrak_bulanini'] = $sk->getRekapkontrak(1, $userCabangs, $userDepartemens);
        $data['kontrak_bulandepan'] = $sk->getRekapkontrak(2, $userCabangs, $userDepartemens);
        $data['kontrak_duabulan'] = $sk->getRekapkontrak(3, $userCabangs, $userDepartemens);

        // 6. Sertifikasi Expired
        $now = Carbon::now(config('app.timezone'))->format('Y-m-d');
        $data['sertifikasi_expired'] = \App\Models\KaryawanPelatihan::with('karyawan')
            ->whereNotNull('tanggal_expired')
            ->where('tanggal_expired', '<', $now)
            ->when(!$isSuperAdmin, function ($query) use ($userCabangs, $userDepartemens) {
                $query->whereHas('karyawan', function($q) use ($userCabangs, $userDepartemens) {
                    if (!empty($userCabangs)) {
                        $q->whereIn('kode_cabang', $userCabangs);
                    } else {
                        $q->whereRaw('1 = 0');
                    }

                    if (!empty($userDepartemens)) {
                        $q->whereIn('kode_dept', $userDepartemens);
                    } else {
                        $q->whereRaw('1 = 0');
                    }
                });
            })
            ->orderBy('tanggal_expired', 'desc')
            ->get();

        return $data;
    }

    public function processKirimUcapanBirthday(Request $request)
    {
        $today = Carbon::now(config('app.timezone'));
        $birthday = Karyawan::whereMonth('tanggal_lahir', $today->month)
            ->whereDay('tanggal_lahir', $today->day)
            ->when($request->kode_cabang, function ($query) use ($request) {
                $query->where('kode_cabang', $request->kode_cabang);
            })
            ->when($request->kode_dept, function ($query) use ($request) {
                $query->where('kode_dept', $request->kode_dept);
            })
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '')
            ->get();

        if ($birthday->count() == 0) {
            return false;
        }

        $count = 0;
        foreach ($birthday as $karyawan) {
            $umur = Carbon::parse($karyawan->tanggal_lahir)->age;

            $message = "🎉 *Selamat Ulang Tahun!* 🎂\n\n";
            $message .= "Halo *{$karyawan->nama_karyawan}*,\n\n";
            $message .= "Di hari yang istimewa ini, kami ingin mengucapkan:\n\n";
            $message .= "🎂 *Selamat Ulang Tahun yang ke-{$umur}!* 🎂\n\n";
            $message .= "Semoga di hari ulang tahunmu ini:\n";
            $message .= "✨ Panjang umur\n";
            $message .= "✨ Sehat selalu\n";
            $message .= "✨ Bahagia selalu\n";
            $message .= "✨ Sukses dalam karir\n";
            $message .= "✨ Diberkahi rezeki yang berlimpah\n\n";
            $message .= "Terima kasih atas dedikasi dan kontribusinya selama ini. Semoga hubungan kerja kita terus berjalan dengan baik!\n\n";
            $message .= "*Salam Hangat,*\nTim HR";

            $phoneNumber = preg_replace('/^0+/', '', $karyawan->no_hp);
            if (!str_starts_with($phoneNumber, '62')) {
                $phoneNumber = '62' . $phoneNumber;
            }

            \App\Jobs\SendWaMessage::dispatch($phoneNumber, $message, true, false, 'birthday');
            $count++;
        }

        return $count;
    }
}

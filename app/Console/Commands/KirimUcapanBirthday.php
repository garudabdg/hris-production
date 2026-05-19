<?php

namespace App\Console\Commands;

use App\Jobs\SendWaMessage;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class KirimUcapanBirthday extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'birthday:kirim-ucapan
                            {--kode_cabang= : Filter hanya cabang tertentu}
                            {--dry-run : Tampilkan siapa yang akan dikirim tanpa benar-benar mengirim}';

    /**
     * The console command description.
     */
    protected $description = 'Kirim ucapan selamat ulang tahun otomatis via WhatsApp ke karyawan yang berulang tahun hari ini';

    public function handle(): int
    {
        $today = Carbon::now(config('app.timezone'));
        $this->info("Cek ulang tahun tanggal: {$today->format('d-m-Y')}");

        $query = Karyawan::whereMonth('tanggal_lahir', $today->month)
            ->whereDay('tanggal_lahir', $today->day)
            ->whereNotNull('no_hp')
            ->where('no_hp', '!=', '');

        if ($this->option('kode_cabang')) {
            $query->where('kode_cabang', $this->option('kode_cabang'));
        }

        $karyawanList = $query->get();

        if ($karyawanList->isEmpty()) {
            $this->info('Tidak ada karyawan yang berulang tahun hari ini.');
            return Command::SUCCESS;
        }

        $this->info("Ditemukan {$karyawanList->count()} karyawan berulang tahun hari ini:");

        foreach ($karyawanList as $karyawan) {
            $umur = Carbon::parse($karyawan->tanggal_lahir)->age;

            $message  = "🎉 *Selamat Ulang Tahun!* 🎂\n\n";
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

            // Format nomor HP → format 62xxx
            $phoneNumber = preg_replace('/^0+/', '', $karyawan->no_hp);
            if (!str_starts_with($phoneNumber, '62')) {
                $phoneNumber = '62' . $phoneNumber;
            }

            if ($this->option('dry-run')) {
                $this->line("  [DRY-RUN] {$karyawan->nama_karyawan} → {$phoneNumber}");
            } else {
                SendWaMessage::dispatch($phoneNumber, $message, true);
                $this->line("  ✓ Dispatched: {$karyawan->nama_karyawan} → {$phoneNumber}");
            }
        }

        $this->info('Selesai.');
        return Command::SUCCESS;
    }
}

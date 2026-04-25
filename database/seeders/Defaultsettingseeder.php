<?php

namespace Database\Seeders;

use App\Models\Pengaturanumum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class Defaultsettingseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Pengaturanumum::updateOrInsert(['id' => 1], [
            'nama_perusahaan' => 'PT. DIDIMAX BERJANGKA',
            'alamat' => 'Jl. Garuda No 88 Bandung',
            'telepon' => '031-1234567',
            'logo' => 'logo.png',
            'total_jam_bulan' => 173,
            'denda' => true,
            'periode_laporan_dari' => '1',
            'periode_laporan_sampai' => '31',
            'periode_laporan_next_bulan' => false
        ]);
    }
}

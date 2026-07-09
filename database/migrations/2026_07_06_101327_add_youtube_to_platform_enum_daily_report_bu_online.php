<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration: Tambah 'youtube' ke ENUM platform di tabel daily_report_bu_online
 * 
 * Juga backfill row youtube untuk semua report yang belum punya.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah 'youtube' ke ENUM platform
        DB::statement("ALTER TABLE daily_report_bu_online MODIFY COLUMN platform ENUM('facebook','instagram','whatsapp','tiktok','youtube') NOT NULL");

        // 2. Backfill: tambah row youtube untuk semua report yang belum punya
        $reportIds = DB::table('daily_report_bu')
            ->whereNotIn('id', function ($query) {
                $query->select('daily_report_bu_id')
                    ->from('daily_report_bu_online')
                    ->where('platform', 'youtube');
            })
            ->pluck('id');

        $now = now();
        $inserts = $reportIds->map(function ($id) use ($now) {
            return [
                'daily_report_bu_id' => $id,
                'platform' => 'youtube',
                'posting' => 0,
                'share_group' => 0,
                'add_group' => 0,
                'add_friend' => 0,
                'inbox' => 0,
                'story' => 0,
                'broadcast' => 0,
                'fanspage' => 0,
                'link_postingan' => null,
                'status_validasi' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        // Insert in chunks to avoid memory issues
        foreach (array_chunk($inserts, 500) as $chunk) {
            DB::table('daily_report_bu_online')->insert($chunk);
        }
    }

    public function down(): void
    {
        // Hapus semua row youtube
        DB::table('daily_report_bu_online')->where('platform', 'youtube')->delete();

        // Kembalikan ENUM ke semula
        DB::statement("ALTER TABLE daily_report_bu_online MODIFY COLUMN platform ENUM('facebook','instagram','whatsapp','tiktok') NOT NULL");
    }
};

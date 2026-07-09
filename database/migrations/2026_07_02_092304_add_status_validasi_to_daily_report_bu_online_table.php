<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Tambah kolom status_validasi ke tabel daily_report_bu_online
     * 
     * Kolom ini menyimpan status validasi link postingan oleh admin.
     * Default 'pending' — berubah ke 'verified' saat admin memverifikasi.
     */
    public function up(): void
    {
        Schema::table('daily_report_bu_online', function (Blueprint $table) {
            $table->enum('status_validasi', ['pending', 'verified'])
                ->default('pending')
                ->after('link_postingan')
                ->comment('Status validasi link postingan oleh admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_report_bu_online', function (Blueprint $table) {
            $table->dropColumn('status_validasi');
        });
    }
};

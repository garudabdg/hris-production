<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presensi_izinkeluar', function (Blueprint $table) {
            $table->string('kode_izin_keluar', 100)->primary();
            $table->date('tanggal');
            $table->char('nik', 9);
            $table->time('jam_keluar');
            $table->string('jam_kembali', 50)->nullable();
            $table->string('keperluan', 255);
            $table->string('keterangan_hrd', 255)->nullable();
            $table->char('status', 1)->default('0'); // 0: pending, 1: approved, 2: rejected
            $table->tinyInteger('approval_step')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_izinkeluar');
    }
};

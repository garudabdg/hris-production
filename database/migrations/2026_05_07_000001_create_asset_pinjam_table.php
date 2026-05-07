<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_pinjam', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pinjam', 20)->unique();
            $table->string('kode_asset');
            $table->foreign('kode_asset')->references('kode_asset')->on('assets')->cascadeOnDelete();
            $table->char('nik', 9);
            $table->foreign('nik')->references('nik')->on('karyawan')->cascadeOnDelete();
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali_rencana');
            $table->date('tanggal_kembali_aktual')->nullable();
            $table->text('catatan')->nullable();
            $table->text('catatan_penolakan')->nullable();
            $table->string('foto_kondisi_pinjam')->nullable();
            $table->string('foto_kondisi_kembali')->nullable();
            // 0=pending approval, 1=disetujui/sedang dipinjam, 2=ditolak, 3=dikembalikan
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('approval_step')->default(1);
            $table->unsignedBigInteger('id_user')->nullable();
            $table->foreign('id_user')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_pinjam');
    }
};

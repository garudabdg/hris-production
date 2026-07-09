<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('data_calon_nasabah', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->date('tanggal');
            $table->string('nama');
            $table->string('akun_sosial_media')->nullable();
            $table->string('no_whatsapp')->nullable();
            $table->enum('status_lead', ['cold', 'warm', 'hot'])->default('cold');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Opsional: Foreign key ke karyawan agar data konsisten
            $table->foreign('nik')->references('nik')->on('karyawan')->onDelete('cascade');
        });

        // Migrasi data dari tabel lama ke tabel baru
        DB::statement("
            INSERT INTO data_calon_nasabah (nik, tanggal, nama, akun_sosial_media, no_whatsapp, status_lead, keterangan, created_at, updated_at)
            SELECT r.nik, r.tanggal, n.nama, n.akun_sosial_media, n.no_whatsapp, n.status_lead, n.keterangan, n.created_at, n.updated_at
            FROM daily_report_bu_nasabah n
            JOIN daily_report_bu r ON n.daily_report_bu_id = r.id
        ");
        
        // Hapus tabel lama
        Schema::dropIfExists('daily_report_bu_nasabah');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Untuk rollback, kita buat kembali tabel lama, tapi datanya tidak direstore otomatis di sini untuk kesederhanaan.
        Schema::create('daily_report_bu_nasabah', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daily_report_bu_id');
            $table->string('nama');
            $table->string('akun_sosial_media')->nullable();
            $table->string('no_whatsapp')->nullable();
            $table->enum('status_lead', ['cold', 'warm', 'hot'])->default('cold');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('daily_report_bu_id')
                ->references('id')
                ->on('daily_report_bu')
                ->onDelete('cascade');
        });

        Schema::dropIfExists('data_calon_nasabah');
    }
};

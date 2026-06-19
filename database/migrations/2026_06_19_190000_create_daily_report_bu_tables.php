<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Tabel Daily Report untuk divisi Business (BU)
     * 
     * Membuat 4 tabel:
     * 1. daily_report_bu - Header report (1 per hari per karyawan)
     * 2. daily_report_bu_online - Aktivitas online per platform sosmed
     * 3. daily_report_bu_offline - Aktivitas offline (appointment, CTO, canvasing)
     * 4. daily_report_bu_nasabah - Pengolahan data calon nasabah
     */
    public function up(): void
    {
        // Tabel 1: Header Daily Report
        Schema::create('daily_report_bu', function (Blueprint $table) {
            $table->id();
            $table->string('nik');
            $table->date('tanggal');
            $table->string('sub_departemen')->nullable()->comment('Team / Sub Departemen karyawan');
            $table->text('catatan')->nullable()->comment('Catatan tambahan');
            $table->timestamps();

            // Constraint: 1 report per hari per karyawan
            $table->unique(['nik', 'tanggal'], 'daily_report_bu_nik_tanggal_unique');

            // Foreign key ke tabel karyawan
            $table->foreign('nik')->references('nik')->on('karyawan')->onDelete('cascade');
        });

        // Tabel 2: Aktivitas Online per Platform
        Schema::create('daily_report_bu_online', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daily_report_bu_id');
            $table->enum('platform', ['facebook', 'instagram', 'whatsapp', 'tiktok']);
            $table->integer('posting')->default(0);
            $table->integer('share_group')->default(0);
            $table->integer('add_group')->default(0);
            $table->integer('add_friend')->default(0);
            $table->integer('inbox')->default(0);
            $table->integer('story')->default(0);
            $table->integer('broadcast')->default(0);
            $table->integer('fanspage')->default(0);
            $table->timestamps();

            $table->foreign('daily_report_bu_id')
                ->references('id')
                ->on('daily_report_bu')
                ->onDelete('cascade');
        });

        // Tabel 3: Aktivitas Offline (dinamis — bisa banyak baris per tipe)
        Schema::create('daily_report_bu_offline', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daily_report_bu_id');
            $table->enum('tipe', ['appointment', 'cto', 'canvasing']);
            $table->string('nama_prospek')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();

            $table->foreign('daily_report_bu_id')
                ->references('id')
                ->on('daily_report_bu')
                ->onDelete('cascade');
        });

        // Tabel 4: Pengolahan Data Calon Nasabah (dinamis)
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_report_bu_nasabah');
        Schema::dropIfExists('daily_report_bu_offline');
        Schema::dropIfExists('daily_report_bu_online');
        Schema::dropIfExists('daily_report_bu');
    }
};

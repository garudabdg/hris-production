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
        Schema::create('recruitments', function (Blueprint $table) {
            $table->id();
            $table->string('kode_recruitment')->unique();

            // Biodata
            $table->string('nama_lengkap');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('agama')->nullable();
            $table->string('status_kawin')->nullable();
            $table->string('no_ktp', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->string('email')->nullable();

            // Pendidikan
            $table->string('pendidikan_terakhir')->nullable(); // SD, SMP, SMA, D1, D2, D3, S1, S2, S3
            $table->string('jurusan')->nullable();
            $table->string('nama_institusi')->nullable();
            $table->year('tahun_lulus')->nullable();

            // Pengalaman
            $table->text('pengalaman_kerja')->nullable();
            $table->text('keahlian')->nullable();

            // Lamaran
            $table->string('kode_cabang')->nullable();
            $table->string('kode_dept')->nullable();
            $table->string('kode_jabatan')->nullable();
            $table->string('posisi_dilamar')->nullable();
            $table->date('tanggal_melamar');
            $table->date('tanggal_tersedia')->nullable(); // kapan bisa mulai bekerja
            $table->decimal('ekspektasi_gaji', 15, 2)->nullable();

            // Upload
            $table->string('foto')->nullable();
            $table->string('cv')->nullable();
            $table->string('ijazah')->nullable();

            // Status & Proses
            $table->enum('status', ['pending', 'review', 'interview', 'offering', 'diterima', 'ditolak'])->default('pending');
            $table->date('tanggal_interview')->nullable();
            $table->text('catatan_interview')->nullable();
            $table->text('catatan_hr')->nullable();
            $table->unsignedBigInteger('diproses_oleh')->nullable();

            $table->foreign('kode_cabang')->references('kode_cabang')->on('cabang')->nullOnDelete();
            $table->foreign('kode_dept')->references('kode_dept')->on('departemen')->nullOnDelete();
            $table->foreign('kode_jabatan')->references('kode_jabatan')->on('jabatan')->nullOnDelete();
            $table->foreign('diproses_oleh')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitments');
    }
};

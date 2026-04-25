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
        Schema::create('recruitment_vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('kode_cabang');
            $table->string('kode_dept')->nullable();
            $table->string('kode_jabatan')->nullable();
            $table->string('posisi');
            $table->integer('kuota')->default(1);
            $table->date('deadline')->nullable();
            $table->text('deskripsi_pekerjaan')->nullable();
            $table->text('kualifikasi')->nullable();
            $table->enum('status', ['buka', 'tutup'])->default('buka');
            $table->foreign('kode_cabang')->references('kode_cabang')->on('cabang')->cascadeOnDelete();
            $table->foreign('kode_dept')->references('kode_dept')->on('departemen')->nullOnDelete();
            $table->foreign('kode_jabatan')->references('kode_jabatan')->on('jabatan')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitment_vacancies');
    }
};

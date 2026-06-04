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
        Schema::create('karyawan_pelatihan', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 50);
            $table->string('nama_pelatihan');
            $table->date('tanggal_pelatihan')->nullable();
            $table->date('tanggal_expired')->nullable();
            $table->string('file_sertifikat')->nullable();
            $table->timestamps();

            // Foreign key to karyawan if it exists, otherwise just a logical reference
            // $table->foreign('nik')->references('nik')->on('karyawan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan_pelatihan');
    }
};

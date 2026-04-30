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
        Schema::create('it_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tiket', 30)->unique();               // TKT-YYYYMM-XXXX

            // Pemohon & cabang
            $table->unsignedBigInteger('pemohon_id');                  // users.id
            $table->string('kode_cabang', 10)->nullable();
            $table->foreign('pemohon_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('kode_cabang')->references('kode_cabang')->on('cabang')->nullOnDelete();

            // Informasi tiket
            $table->string('judul');
            $table->text('deskripsi');
            $table->enum('kategori', ['hardware', 'software', 'jaringan', 'keamanan', 'akses', 'data', 'lainnya']);
            $table->enum('prioritas', ['critical', 'high', 'medium', 'low'])->default('medium');

            // ISO 27001 – Klasifikasi & dampak
            $table->enum('klasifikasi_data', ['confidential', 'internal', 'public'])->default('internal');
            $table->enum('dampak', ['individu', 'departemen', 'cabang', 'perusahaan'])->default('individu');

            // Status & workflow
            $table->enum('status', ['open', 'in_progress', 'pending', 'resolved', 'closed'])->default('open');
            $table->unsignedBigInteger('assigned_to')->nullable();     // users.id IT staff
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();

            // SLA & resolusi
            $table->date('tanggal_target')->nullable();                // SLA due date
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->foreign('resolved_by')->references('id')->on('users')->nullOnDelete();
            $table->text('catatan_resolusi')->nullable();

            // Lampiran (file path CSV)
            $table->string('lampiran')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('it_tickets');
    }
};

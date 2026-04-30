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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('kode_asset')->unique();
            $table->string('nama_asset');
            $table->foreignId('category_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            $table->char('kode_cabang', 4)->nullable();
            $table->foreign('kode_cabang')->references('kode_cabang')->on('cabang')->nullOnDelete();
            $table->string('merk')->nullable();
            $table->string('no_seri')->nullable();
            $table->enum('kondisi', ['baik', 'rusak', 'dalam_perbaikan'])->default('baik');
            $table->enum('status', ['tersedia', 'dipinjam', 'tidak_aktif'])->default('tersedia');
            $table->date('tanggal_perolehan')->nullable();
            $table->decimal('nilai_perolehan', 15, 2)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->string('lokasi')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

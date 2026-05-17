<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 20)->unique();
            $table->string('kode_asset');
            $table->foreign('kode_asset')->references('kode_asset')->on('assets')->cascadeOnDelete();
            $table->enum('tipe', ['in', 'out']);
            $table->string('kategori_transaksi', 50);
            $table->integer('jumlah')->default(1);
            $table->date('tanggal_transaksi');
            $table->char('kode_cabang', 4)->nullable();
            $table->foreign('kode_cabang')->references('kode_cabang')->on('cabang')->nullOnDelete();
            $table->string('penanggung_jawab')->nullable();
            $table->text('catatan')->nullable();
            $table->string('foto_bukti')->nullable();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->foreign('id_user')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_transactions');
    }
};

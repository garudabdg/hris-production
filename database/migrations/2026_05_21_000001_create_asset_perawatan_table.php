<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_perawatan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_perawatan')->unique();
            $table->string('kode_asset');
            $table->foreign('kode_asset')->references('kode_asset')->on('assets')->cascadeOnDelete();
            $table->date('tanggal_perawatan');
            $table->string('petugas')->nullable();
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->foreign('id_user')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('asset_perawatan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_perawatan_id');
            $table->foreign('asset_perawatan_id')->references('id')->on('asset_perawatan')->cascadeOnDelete();
            $table->string('item_name');
            $table->enum('klasifikasi', ['baik', 'cukup_baik', 'rusak'])->default('baik');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_perawatan_items');
        Schema::dropIfExists('asset_perawatan');
    }
};

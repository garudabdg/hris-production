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
        Schema::table('presensi_izinkeluar', function (Blueprint $table) {
            $table->string('driver_nik', 100)->nullable();
            $table->string('kode_asset_kendaraan', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi_izinkeluar', function (Blueprint $table) {
            $table->dropColumn('driver_nik');
            $table->dropColumn('kode_asset_kendaraan');
        });
    }
};

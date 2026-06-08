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
        Schema::table('tamus', function (Blueprint $table) {
            $table->string('no_telp')->nullable()->after('nama_tamu');
            $table->string('plat_nomor')->nullable()->after('no_telp');
            $table->string('foto_wajah')->nullable()->after('plat_nomor');
            $table->string('foto_ktp')->nullable()->after('foto_wajah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tamus', function (Blueprint $table) {
            $table->dropColumn(['no_telp', 'plat_nomor', 'foto_wajah', 'foto_ktp']);
        });
    }
};

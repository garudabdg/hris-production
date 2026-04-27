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
        Schema::table('recruitments', function (Blueprint $table) {
            $table->string('token_konfirmasi', 64)->nullable()->unique()->after('jam_interview');
            $table->enum('konfirmasi_interview', ['hadir', 'tidak_hadir'])->nullable()->after('token_konfirmasi');
            $table->timestamp('konfirmasi_at')->nullable()->after('konfirmasi_interview');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitments', function (Blueprint $table) {
            $table->dropColumn(['token_konfirmasi', 'konfirmasi_interview', 'konfirmasi_at']);
        });
    }
};

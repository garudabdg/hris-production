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
        Schema::table('it_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('it_tickets', 'lokasi')) {
                // Walaupun wajib dari sisi UI, di sisi DB dibuat nullable
                // agar data lama yang tidak punya lokasi tidak menyebabkan error.
                $table->string('lokasi')->nullable()->after('kode_cabang');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('it_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('it_tickets', 'lokasi')) {
                $table->dropColumn('lokasi');
            }
        });
    }
};

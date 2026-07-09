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
        Schema::create('threat_intelligence_reports', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('jenis_ancaman');
            $table->string('sumber_ancaman');
            $table->text('deskripsi_insiden');
            $table->text('dampak');
            $table->text('tindakan_yang_diambil');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threat_intelligence_reports');
    }
};

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
        Schema::table('pengaturan_umum', function (Blueprint $table) {
            $table->string('provider_wa', 20)->default('local')->change();
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_umum', function (Blueprint $table) {
            $table->string('provider_wa', 2)->default('ig')->change();
        });
    }
};

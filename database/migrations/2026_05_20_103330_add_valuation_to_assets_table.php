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
        Schema::table('assets', function (Blueprint $table) {
            // Nilai: 1=Low, 2=Medium, 3=High
            $table->tinyInteger('confidentiality')->unsigned()->nullable()->after('catatan')->comment('1=Low, 2=Medium, 3=High');
            $table->tinyInteger('availability')->unsigned()->nullable()->after('confidentiality')->comment('1=Low, 2=Medium, 3=High');
            $table->tinyInteger('integrity')->unsigned()->nullable()->after('availability')->comment('1=Low, 2=Medium, 3=High');
            // asset_valuation = confidentiality + availability + integrity (3-4=Low, 5-6=Medium, 7-9=High)
            $table->tinyInteger('asset_valuation')->unsigned()->nullable()->after('integrity')->comment('Sum C+A+I: 3-4=Low, 5-6=Medium, 7-9=High');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['confidentiality', 'availability', 'integrity', 'asset_valuation']);
        });
    }
};

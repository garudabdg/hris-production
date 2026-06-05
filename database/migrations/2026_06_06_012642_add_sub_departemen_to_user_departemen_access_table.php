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
        Schema::table('user_departemen_access', function (Blueprint $table) {
            $table->json('sub_departemen')->nullable()->after('kode_dept');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_departemen_access', function (Blueprint $table) {
            $table->dropColumn('sub_departemen');
        });
    }
};

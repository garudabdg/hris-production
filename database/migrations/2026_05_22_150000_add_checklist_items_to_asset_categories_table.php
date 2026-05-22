<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->json('checklist_items')->nullable()->after('deskripsi');
        });
    }

    public function down(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropColumn('checklist_items');
        });
    }
};

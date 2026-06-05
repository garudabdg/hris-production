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
            if (!Schema::hasColumn('it_tickets', 'nomor_urut')) {
                $table->integer('nomor_urut')->nullable()->after('nomor_tiket');
            }
            if (!Schema::hasColumn('it_tickets', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assigned_to');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('it_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('it_tickets', 'nomor_urut')) {
                $table->dropColumn('nomor_urut');
            }
            if (Schema::hasColumn('it_tickets', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }
        });
    }
};

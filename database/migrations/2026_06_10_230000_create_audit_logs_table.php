<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Sengaja dikosongkan karena tabel audit_logs sudah ada sebelumnya
        // Migrasi utama ada di create_data_audit_logs_table
    }

    public function down()
    {
        // Sengaja dikosongkan
    }
};

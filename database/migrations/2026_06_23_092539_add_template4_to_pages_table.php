<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // The base pages migration already contains all template values for SQLite,
            // so no table recreation (and no FK breakage) is needed here.
            return;
        }

        DB::statement("ALTER TABLE pages MODIFY template ENUM('template1', 'template2', 'template3', 'template4', 'custom') DEFAULT 'template1'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE pages MODIFY template ENUM('template1', 'template2', 'template3', 'custom') DEFAULT 'template1'");
    }
};

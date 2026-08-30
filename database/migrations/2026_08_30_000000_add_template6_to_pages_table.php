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
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pages MODIFY template ENUM('template1', 'template2', 'template3', 'template4', 'template5', 'template6', 'custom') DEFAULT 'template1'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pages MODIFY template ENUM('template1', 'template2', 'template3', 'template4', 'template5', 'custom') DEFAULT 'template1'");
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('pages', 'account_name')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->string('account_name')->nullable()->after('title');
            });
        }

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pages MODIFY template ENUM('template1', 'template2', 'template3', 'template4', 'template5', 'custom') DEFAULT 'template1'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pages', 'account_name')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropColumn('account_name');
            });
        }

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE pages MODIFY template ENUM('template1', 'template2', 'template3', 'template4', 'custom') DEFAULT 'template1'");
        }
    }
};

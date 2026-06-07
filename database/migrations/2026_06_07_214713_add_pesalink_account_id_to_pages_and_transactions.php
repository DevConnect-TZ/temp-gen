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
        Schema::table('pages', function (Blueprint $table) {
            $table->foreignId('pesalink_account_id')->nullable()->after('payment_gateway')->constrained('pesa_link_accounts')->nullOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('pesalink_account_id')->nullable()->after('gateway')->constrained('pesa_link_accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pesalink_account_id');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pesalink_account_id');
        });
    }
};

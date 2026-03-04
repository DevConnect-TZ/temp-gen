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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->string('order_id')->unique(); // SonicPesa order ID
            $table->string('reference')->nullable(); // SonicPesa reference
            $table->string('buyer_email');
            $table->string('buyer_name');
            $table->string('buyer_phone');
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('TZS');
            $table->string('gateway')->default('sonicpesa'); // Payment gateway used
            $table->string('payment_status')->default('PENDING'); // PENDING, COMPLETED, CANCELLED, REJECTED, INPROGRESS
            $table->string('transaction_id')->nullable(); // transid from SonicPesa
            $table->string('channel')->nullable(); // AIRTELMONEY, etc.
            $table->string('msisdn')->nullable(); // Mobile number on gateway
            $table->json('response_data')->nullable(); // Full API response
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

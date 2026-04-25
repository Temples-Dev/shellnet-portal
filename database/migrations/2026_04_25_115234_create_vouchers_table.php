<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores pre-generated voucher codes that customers can redeem for plans.
     */
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();

            $table->string('code', 55)->unique();

            $table->enum('type', ['Hotspot', 'PPPoE', 'VPN'])->default('Hotspot');

            $table->foreignId('plan_id')
                  ->nullable()
                  ->constrained('plans')
                  ->nullOnDelete();

            $table->foreignId('router_id')
                  ->nullable()
                  ->constrained('routers')
                  ->nullOnDelete();

            // The customer who redeemed this voucher
            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained('customers')
                  ->nullOnDelete();

            $table->enum('status', ['unused', 'used', 'disabled'])->default('unused');

            // Admin or user who generated this voucher
            $table->foreignId('generated_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('used_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};

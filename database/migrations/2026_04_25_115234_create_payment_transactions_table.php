<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores payment gateway transaction records (Xendit, Midtrans, etc.).
     * status: 1 = unpaid, 2 = paid, 3 = failed, 4 = canceled.
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            $table->string('username', 32);

            // Payment gateway identifier, e.g. xendit, midtrans
            $table->string('gateway', 32);

            // External transaction ID returned by the gateway
            $table->string('gateway_trx_id', 512)->nullable();

            $table->foreignId('plan_id')
                ->nullable()
                ->constrained('plans')
                ->nullOnDelete();

            // Snapshot of the plan name at the time of payment initiation
            $table->string('plan_name', 40);

            $table->foreignId('router_id')
                ->nullable()
                ->constrained('routers')
                ->nullOnDelete();

            // Snapshot of the router name at the time of payment initiation
            $table->string('router_name', 32)->nullable();

            $table->decimal('price', 15, 2);

            $table->string('payment_method', 32)->nullable();
            $table->string('payment_channel', 32)->nullable();

            // Payment gateway redirect / checkout URL
            $table->string('pg_url', 512)->nullable();

            // Raw request payload sent to the gateway
            $table->json('pg_request')->nullable();

            // Raw response payload received from the gateway
            $table->json('pg_response')->nullable();

            // Soft reference to transactions.invoice — no FK constraint
            $table->string('invoice', 25)->nullable();

            // 1 = unpaid, 2 = paid, 3 = failed, 4 = canceled
            $table->unsignedTinyInteger('status')->default(1);

            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('gateway');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};

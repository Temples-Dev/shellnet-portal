<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores the billing transaction ledger for all recharge events.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->string('invoice', 25)->unique();

            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained('customers')
                  ->nullOnDelete();

            // Snapshot of the customer username at the time of the transaction
            $table->string('username', 32);

            // Snapshot of the plan name at the time of the transaction
            $table->string('plan_name', 40);

            $table->decimal('price', 15, 2);

            $table->enum('type', ['Hotspot', 'PPPoE', 'VPN', 'Balance'])
                  ->default('Hotspot');

            $table->string('method', 128)->nullable();

            $table->foreignId('router_id')
                  ->nullable()
                  ->constrained('routers')
                  ->nullOnDelete();

            // Snapshot of the router name at the time of the transaction
            $table->string('router_name', 32)->nullable();

            $table->string('note', 256)->nullable();

            $table->foreignId('admin_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('recharged_at')->nullable();
            $table->timestamp('expires_at')->nullable();

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

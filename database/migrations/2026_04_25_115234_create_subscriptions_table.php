<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Maps to tbl_user_recharges — stores active customer plan subscriptions.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->cascadeOnDelete();

            $table->foreignId('plan_id')
                  ->nullable()
                  ->constrained('plans')
                  ->nullOnDelete();

            // Snapshot of the plan name at the time of recharge
            $table->string('plan_name', 40);

            $table->foreignId('router_id')
                  ->nullable()
                  ->constrained('routers')
                  ->nullOnDelete();

            // Snapshot of the router name at the time of recharge
            $table->string('router_name', 32)->nullable();

            $table->enum('type', ['Hotspot', 'PPPoE', 'VPN', 'Balance'])
                  ->default('Hotspot');

            $table->string('method', 128)->nullable();

            $table->enum('status', ['on', 'off', 'expired'])->default('on');

            $table->timestamp('recharged_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->foreignId('admin_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index('customer_id');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};

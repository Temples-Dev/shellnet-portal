<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: this migration depends on `bandwidths` and `routers` tables.
     * Because all three share the same timestamp prefix, Laravel sorts them
     * alphabetically: bandwidths → plans → routers. The `router_id` foreign
     * key will therefore fail if migrations run in strict FK-check mode before
     * the `routers` table exists. Consider renaming this file to run after
     * `create_routers_table`, or wrap the router FK in Schema::table() in a
     * later migration.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 40);
            $table->foreignId('bandwidth_id')->nullable()->constrained('bandwidths')->nullOnDelete();
            $table->decimal('price', 15, 2);
            $table->decimal('price_old', 15, 2)->nullable();
            $table->enum('type', ['Hotspot', 'PPPoE', 'Balance', 'VPN'])->default('Hotspot');
            $table->boolean('is_limited')->default(false);
            $table->enum('limit_type', ['Time_Limit', 'Data_Limit', 'Both_Limit'])->nullable();
            $table->unsignedInteger('time_limit')->nullable();
            $table->enum('time_unit', ['Mins', 'Hrs'])->nullable();
            $table->unsignedInteger('data_limit')->nullable();
            $table->enum('data_unit', ['MB', 'GB'])->nullable();
            $table->unsignedInteger('validity')->default(30);
            $table->enum('validity_unit', ['Mins', 'Hrs', 'Days', 'Months', 'Period'])->default('Days');
            $table->unsignedSmallInteger('shared_users')->default(1);
            $table->foreignId('router_id')->nullable()->constrained('routers')->nullOnDelete();
            $table->boolean('is_radius')->default(false);
            $table->string('pool', 40)->nullable();
            $table->unsignedTinyInteger('expired_date')->default(20);
            $table->boolean('enabled')->default(true);
            $table->boolean('prepaid')->default(true);
            $table->enum('plan_type', ['Personal', 'Business'])->default('Personal');
            $table->string('device', 32)->nullable();
            $table->text('on_login')->nullable();
            $table->text('on_logout')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores discount coupon codes applicable to customer plan purchases.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50)->unique();

            // fixed = flat amount off, percent = percentage off
            $table->enum('type', ['fixed', 'percent'])->default('fixed');

            $table->decimal('value', 10, 2);

            $table->text('description')->nullable();

            // NULL means unlimited usage
            $table->unsignedInteger('max_usage')->nullable();

            $table->unsignedInteger('usage_count')->default(0);

            $table->enum('status', ['active', 'inactive'])->default('active');

            // Minimum order total required for the coupon to apply
            $table->decimal('min_order_amount', 10, 2)->default(0);

            // Cap on how much can be discounted (useful for percent type)
            $table->decimal('max_discount_amount', 10, 2)->nullable();

            $table->date('starts_at')->nullable();
            $table->date('expires_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};

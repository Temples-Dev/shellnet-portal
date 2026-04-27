<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('coupon_id')->nullable()->after('invoice');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('coupon_id');
            $table->decimal('original_price', 10, 2)->nullable()->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn(['coupon_id', 'discount_amount', 'original_price']);
        });
    }
};

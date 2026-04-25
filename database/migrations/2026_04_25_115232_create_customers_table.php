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
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username', 45)->unique();
            $table->string('password', 255);
            $table->string('photo', 128)->nullable()->default('/user.default.jpg');
            $table->string('pppoe_username', 32)->nullable()->unique();
            $table->string('pppoe_password', 255)->nullable();
            $table->string('pppoe_ip', 32)->nullable();
            $table->string('fullname', 45);
            $table->string('email', 128)->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->mediumText('address')->nullable();
            $table->string('city', 255)->nullable();
            $table->string('district', 255)->nullable();
            $table->string('state', 255)->nullable();
            $table->string('zip', 10)->nullable();
            $table->string('coordinates', 50)->nullable()->comment('lat,lng');
            $table->enum('account_type', ['Personal', 'Business'])->default('Personal');
            $table->decimal('balance', 15, 2)->default(0);
            $table->enum('service_type', ['Hotspot', 'PPPoE', 'VPN', 'Others'])->default('Hotspot');
            $table->boolean('auto_renewal')->default(false);
            $table->enum('status', ['Active', 'Inactive', 'Banned', 'Disabled', 'Limited', 'Suspended'])->default('Active');
            $table->foreignId('created_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

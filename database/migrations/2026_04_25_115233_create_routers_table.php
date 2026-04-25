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
        Schema::create('routers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 32)->unique();
            $table->string('ip_address', 128);
            $table->string('username', 50);
            $table->string('password', 255);
            $table->string('description', 256)->nullable();
            $table->string('coordinates', 50)->nullable();
            $table->string('coverage', 8)->nullable();
            $table->enum('status', ['Online', 'Offline'])->default('Offline');
            $table->timestamp('last_seen')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routers');
    }
};

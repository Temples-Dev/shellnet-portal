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
        Schema::create('bandwidths', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->unsignedInteger('rate_down');
            $table->enum('rate_down_unit', ['Kbps', 'Mbps'])->default('Mbps');
            $table->unsignedInteger('rate_up');
            $table->enum('rate_up_unit', ['Kbps', 'Mbps'])->default('Mbps');
            $table->string('burst', 128)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bandwidths');
    }
};

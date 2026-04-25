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
        Schema::create('meta', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('metable_type');
            $table->unsignedBigInteger('metable_id');
            $table->string('key', 64);
            $table->mediumText('value')->nullable();
            $table->timestamps();

            $table->unique(['metable_type', 'metable_id', 'key']);
            $table->index(['metable_type', 'metable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta');
    }
};

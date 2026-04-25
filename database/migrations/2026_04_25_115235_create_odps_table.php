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
        Schema::create('odps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 32);
            $table->unsignedSmallInteger('port_amount')->default(0);
            $table->decimal('attenuation', 5, 2)->nullable();
            $table->mediumText('address')->nullable();
            $table->string('coordinates', 50)->nullable();
            $table->unsignedInteger('coverage')->nullable()->comment('Coverage radius in meters');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odps');
    }
};

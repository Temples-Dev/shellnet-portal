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
        Schema::create('radius_accounting', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('session_id', 64)->comment('acctSessionId');
            $table->string('username', 64);
            $table->string('realm', 128)->nullable();
            $table->string('nas_id', 32)->nullable();
            $table->string('nas_ip', 15)->nullable();
            $table->string('nas_port_id', 32)->nullable();
            $table->string('nas_port_type', 32)->nullable();
            $table->string('framed_ip', 15)->nullable();
            $table->unsignedBigInteger('session_time')->default(0)->comment('Session duration in seconds');
            $table->unsignedBigInteger('input_octets')->default(0);
            $table->unsignedBigInteger('output_octets')->default(0);
            $table->string('status_type', 32)->nullable()->comment('Start, Stop, Interim-Update');
            $table->string('mac_address', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('username');
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radius_accounting');
    }
};

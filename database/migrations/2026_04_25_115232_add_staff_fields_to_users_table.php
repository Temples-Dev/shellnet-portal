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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 45)->unique()->nullable()->after('name');
            $table->string('photo', 128)->default('/admin.default.png')->nullable()->after('username');
            $table->string('phone', 32)->nullable()->after('photo');
            $table->string('city', 64)->nullable()->after('phone');
            $table->string('subdistrict', 64)->nullable()->after('city');
            $table->string('ward', 64)->nullable()->after('subdistrict');
            $table->enum('user_type', ['SuperAdmin', 'Admin', 'Report', 'Agent', 'Sales'])->default('Admin')->after('ward');
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->after('user_type');
            $table->json('data')->nullable()->after('status');
            $table->timestamp('last_login')->nullable()->after('data');
            $table->string('login_token', 40)->nullable()->after('last_login');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn([
                'username',
                'photo',
                'phone',
                'city',
                'subdistrict',
                'ward',
                'user_type',
                'status',
                'data',
                'last_login',
                'login_token',
            ]);
        });
    }
};

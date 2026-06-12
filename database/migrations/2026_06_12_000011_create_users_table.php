<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_users', function (Blueprint $table) {
            $table->id();
            $table->integer('root')->default(0);
            $table->string('photo', 128)->default('/admin.default.png');
            $table->string('username', 45);
            $table->string('fullname', 45);
            $table->string('password', 255);
            $table->string('phone', 32)->default('');
            $table->string('email', 128)->default('');
            $table->string('city', 64)->default('');
            $table->string('subdistrict', 64)->default('');
            $table->string('ward', 64)->default('');
            $table->enum('user_type', ['SuperAdmin', 'Admin', 'Report', 'Agent', 'Sales']);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->text('data')->nullable();
            $table->datetime('last_login')->nullable();
            $table->string('login_token', 40)->nullable();
            $table->datetime('created_at')->useCurrent();
            $table->datetime('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->rememberToken();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_users');
    }
};

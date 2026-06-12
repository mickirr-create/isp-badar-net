<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_user_recharges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('username', 32);
            $table->unsignedBigInteger('plan_id');
            $table->string('namebp', 40);
            $table->date('recharged_on');
            $table->time('recharged_time')->default('00:00:00');
            $table->date('expiration');
            $table->time('time');
            $table->string('status', 20);
            $table->string('method', 128)->default('');
            $table->string('routers', 32);
            $table->string('type', 15);
            $table->unsignedBigInteger('admin_id')->default(1);

            $table->foreign('customer_id')->references('id')->on('tbl_customers');
            $table->foreign('plan_id')->references('id')->on('tbl_plans');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_user_recharges');
    }
};

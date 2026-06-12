<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_customers_inbox', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->datetime('date_created');
            $table->datetime('date_read')->nullable();
            $table->string('subject', 64);
            $table->text('body')->nullable();
            $table->string('from', 8)->default('System');
            $table->unsignedBigInteger('admin_id')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_customers_inbox');
    }
};

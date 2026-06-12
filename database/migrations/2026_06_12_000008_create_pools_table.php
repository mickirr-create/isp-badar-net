<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_pool', function (Blueprint $table) {
            $table->id();
            $table->string('pool_name', 40);
            $table->string('local_ip', 40)->default('');
            $table->string('range_ip', 40);
            $table->string('routers', 40);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_pool');
    }
};

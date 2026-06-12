<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_port_pool', function (Blueprint $table) {
            $table->id();
            $table->string('public_ip', 40);
            $table->string('port_name', 40);
            $table->string('range_port', 40);
            $table->string('routers', 40);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_port_pool');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_odps', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32);
            $table->integer('port_amount');
            $table->decimal('attenuation', 15, 2)->default(0.00);
            $table->mediumText('address');
            $table->string('coordinates', 50);
            $table->integer('coverage')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_odps');
    }
};

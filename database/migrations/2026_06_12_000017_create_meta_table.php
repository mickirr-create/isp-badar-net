<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_meta', function (Blueprint $table) {
            $table->id();
            $table->string('tbl', 32);
            $table->unsignedBigInteger('tbl_id');
            $table->string('name', 32);
            $table->mediumText('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_meta');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_widgets', function (Blueprint $table) {
            $table->id();
            $table->integer('orders')->default(99);
            $table->tinyInteger('position')->default(1);
            $table->enum('user', ['Admin', 'Agent', 'Sales', 'Customer'])->default('Admin');
            $table->boolean('enabled')->default(true);
            $table->string('title', 64);
            $table->string('widget', 64)->default('');
            $table->text('content');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_widgets');
    }
};

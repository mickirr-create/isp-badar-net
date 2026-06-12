<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_voucher', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Hotspot', 'PPPOE']);
            $table->string('routers', 32);
            $table->unsignedBigInteger('id_plan');
            $table->string('code', 55);
            $table->string('user', 45);
            $table->string('status', 25);
            $table->timestamp('created_at')->useCurrent();
            $table->datetime('used_date')->nullable();
            $table->unsignedBigInteger('generated_by')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_voucher');
    }
};

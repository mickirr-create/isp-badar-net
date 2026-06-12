<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add billing fields to customers
        Schema::table('tbl_customers', function (Blueprint $table) {
            $table->integer('billing_day')->nullable()->after('auto_renewal');
            $table->boolean('throttle_enabled')->default(true)->after('billing_day');
            $table->string('throttle_profile')->nullable()->after('throttle_enabled');
        });

        // Add billing fields to plans
        Schema::table('tbl_plans', function (Blueprint $table) {
            $table->string('throttle_profile')->nullable()->after('type');
        });

        // Add billing fields to user_recharges
        Schema::table('tbl_user_recharges', function (Blueprint $table) {
            $table->boolean('throttle_applied')->default(false)->after('type');
            $table->date('last_throttle_check')->nullable()->after('throttle_applied');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_customers', function (Blueprint $table) {
            $table->dropColumn(['billing_day', 'throttle_enabled', 'throttle_profile']);
        });

        Schema::table('tbl_plans', function (Blueprint $table) {
            $table->dropColumn('throttle_profile');
        });

        Schema::table('tbl_user_recharges', function (Blueprint $table) {
            $table->dropColumn(['throttle_applied', 'last_throttle_check']);
        });
    }
};

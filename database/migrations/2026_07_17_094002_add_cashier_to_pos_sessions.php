<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sessions', function (Blueprint $table) {
            $table->string('cashier_name', 100)->nullable()->after('company_id');
            $table->string('cashier_pin', 60)->nullable()->after('cashier_name');
        });
    }

    public function down(): void
    {
        Schema::table('pos_sessions', function (Blueprint $table) {
            $table->dropColumn(['cashier_name', 'cashier_pin']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('reorder_point')->default(0)->after('stock_alert_threshold');
            $table->integer('reorder_quantity')->default(0)->after('reorder_point');
            $table->foreignId('preferred_supplier_id')->nullable()->after('reorder_quantity')
                ->constrained('suppliers')->nullOnDelete();
            $table->integer('lead_time_days')->default(7)->after('preferred_supplier_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('preferred_supplier_id');
            $table->dropColumn(['reorder_point', 'reorder_quantity', 'lead_time_days']);
        });
    }
};

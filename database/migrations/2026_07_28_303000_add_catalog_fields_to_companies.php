<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('catalog_enabled')->default(false)->after('invoice_footer');
            $table->string('catalog_slug')->nullable()->unique()->after('catalog_enabled');
            $table->string('catalog_title')->nullable()->after('catalog_slug');
            $table->text('catalog_description')->nullable()->after('catalog_title');
            $table->boolean('catalog_show_prices')->default(true)->after('catalog_description');
            $table->boolean('catalog_allow_orders')->default(false)->after('catalog_show_prices');
            $table->string('catalog_cover_color', 20)->default('#2563eb')->after('catalog_allow_orders');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'catalog_enabled',
                'catalog_slug',
                'catalog_title',
                'catalog_description',
                'catalog_show_prices',
                'catalog_allow_orders',
                'catalog_cover_color',
            ]);
        });
    }
};

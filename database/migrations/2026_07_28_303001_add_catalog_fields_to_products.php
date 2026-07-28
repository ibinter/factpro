<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('catalog_visible')->default(true)->after('barcode');
            $table->boolean('catalog_featured')->default(false)->after('catalog_visible');
            $table->integer('catalog_order')->default(0)->after('catalog_featured');
            $table->text('catalog_description_public')->nullable()->after('catalog_order');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'catalog_visible',
                'catalog_featured',
                'catalog_order',
                'catalog_description_public',
            ]);
        });
    }
};

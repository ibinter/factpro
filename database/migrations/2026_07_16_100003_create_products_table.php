<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type', 15)->default('product'); // product | service
            $table->string('name');
            $table->string('sku', 60)->nullable();
            $table->string('barcode', 60)->nullable();      // EAN-13
            $table->text('description')->nullable();
            $table->string('unit', 20)->default('unité');   // unité, kg, heure, jour…
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('cost', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(18.00);
            $table->decimal('stock_quantity', 12, 2)->default(0);
            $table->decimal('stock_alert_threshold', 12, 2)->nullable();
            $table->boolean('track_stock')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('image_path')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'name']);
            $table->index(['company_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

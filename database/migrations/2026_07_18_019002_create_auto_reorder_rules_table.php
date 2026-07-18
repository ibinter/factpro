<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auto_reorder_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->integer('trigger_threshold')->default(0);
            $table->integer('order_quantity')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->foreignId('last_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->integer('cooldown_hours')->default(24);
            $table->boolean('auto_approve')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auto_reorder_rules');
    }
};

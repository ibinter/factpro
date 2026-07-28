<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 20);
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->decimal('min_quantity', 12, 3)->default(0);
            $table->decimal('max_quantity', 12, 3)->nullable();
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_id']);
        });

        Schema::create('warehouse_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('reference');
            $table->enum('status', ['draft', 'in_transit', 'received', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('transferred_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        Schema::create('warehouse_transfer_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('warehouse_transfers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity_sent', 12, 3);
            $table->decimal('quantity_received', 12, 3)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_transfer_lines');
        Schema::dropIfExists('warehouse_transfers');
        Schema::dropIfExists('warehouse_stocks');
        Schema::dropIfExists('warehouses');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->default('materiel');
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('residual_value', 15, 2)->default(0);
            $table->date('purchase_date');
            $table->date('start_date');
            $table->integer('duration_years')->default(5);
            $table->string('depreciation_method')->default('linear');
            $table->string('status')->default('active');
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_price', 15, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->string('location')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('currency')->default('XOF');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->decimal('depreciation_amount', 15, 2);
            $table->decimal('accumulated_depreciation', 15, 2);
            $table->decimal('net_book_value', 15, 2);
            $table->decimal('rate', 8, 4);
            $table->timestamps();
            $table->unique(['asset_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('assets');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('reference')->nullable();
            $table->enum('type', ['apartment','house','villa','commercial','office','warehouse','land','parking'])->default('apartment');
            $table->string('address');
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->decimal('area_sqm', 8, 2)->nullable();
            $table->tinyInteger('bedrooms')->nullable();
            $table->tinyInteger('bathrooms')->nullable();
            $table->tinyInteger('floor')->nullable();
            $table->tinyInteger('total_floors')->nullable();
            $table->decimal('monthly_rent', 10, 2);
            $table->string('currency', 3)->default('XOF');
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->enum('status', ['available','rented','maintenance','for_sale'])->default('available')->index();
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_open_ended')->default(false);
            $table->decimal('monthly_rent', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->tinyInteger('payment_day')->default(1);
            $table->enum('status', ['active','expired','terminated'])->default('active')->index();
            $table->integer('renewal_notice_days')->default(90);
            $table->text('notes')->nullable();
            $table->date('terminated_at')->nullable();
            $table->string('terminate_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('rent_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete();
            $table->date('period_month');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending','paid','late','partial'])->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_payments');
        Schema::dropIfExists('leases');
        Schema::dropIfExists('properties');
    }
};

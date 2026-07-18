<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unique('company_id');
            $table->string('name', 100)->default('Programme Fidélité');
            $table->boolean('is_active')->default(true);
            $table->integer('points_per_1000')->default(1);
            $table->string('currency', 3)->default('XOF');
            $table->integer('bronze_threshold')->default(0);
            $table->integer('silver_threshold')->default(500);
            $table->integer('gold_threshold')->default(2000);
            $table->integer('expiry_months')->nullable();
            $table->timestamps();
        });

        Schema::create('loyalty_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->unsignedBigInteger('document_id')->nullable();
            $table->foreign('document_id')->references('id')->on('documents')->nullOnDelete();
            $table->enum('type', ['earned', 'redeemed', 'expired', 'adjusted'])->default('earned');
            $table->integer('points');
            $table->integer('balance_after');
            $table->text('description')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('loyalty_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('points_cost');
            $table->enum('reward_type', ['discount_percent', 'discount_fixed', 'free_product', 'gift'])->default('discount_percent');
            $table->decimal('reward_value', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->integer('stock')->nullable();
            $table->integer('redemptions_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_rewards');
        Schema::dropIfExists('loyalty_points');
        Schema::dropIfExists('loyalty_programs');
    }
};

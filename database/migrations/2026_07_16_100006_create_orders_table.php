<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number', 30)->unique(); // FP-2026-XXXXXX
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained();
            $table->unsignedTinyInteger('duration_months')->default(1); // 1 | 3 | 6 | 12
            $table->decimal('amount', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 3)->default('XOF');
            $table->string('country', 2)->default('CI');
            $table->string('payment_method', 30)->nullable(); // moneroo | orange_money | mtn_momo | wave | moov | bank_transfer | cash
            $table->string('status', 30)->default('pending_payment');
            // draft | pending_payment | payment_initiated | proof_submitted | under_review
            // | missing_info | paid | expired | cancelled | rejected | refunded
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->decimal('payment_risk_score', 5, 2)->default(0);
            $table->decimal('churn_score', 5, 2)->default(0);
            $table->decimal('avg_payment_days', 8, 2)->nullable();
            $table->integer('late_payments_count')->default(0);
            $table->integer('total_invoices')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->date('last_order_date')->nullable();
            $table->integer('days_since_last_order')->nullable();
            $table->decimal('avg_order_frequency_days', 8, 2)->nullable();
            $table->string('risk_label')->default('faible');
            $table->string('churn_label')->default('stable');
            $table->json('factors')->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_scores');
    }
};

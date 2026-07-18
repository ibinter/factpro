<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('period_type', ['month', 'quarter', 'year'])->default('month');
            $table->tinyInteger('period_month')->nullable(); // 1-12
            $table->smallInteger('period_year');
            $table->decimal('target_amount', 15, 2);
            $table->integer('target_invoices')->nullable();
            $table->integer('target_customers')->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'period_year', 'period_month']);
        });

        Schema::create('forecast_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->tinyInteger('period_month');
            $table->smallInteger('period_year');
            $table->decimal('actual_revenue', 15, 2);
            $table->decimal('forecasted_revenue', 15, 2);
            $table->string('method', 50)->default('linear_trend');
            $table->decimal('accuracy_pct', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['company_id', 'period_year', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_snapshots');
        Schema::dropIfExists('sales_targets');
    }
};

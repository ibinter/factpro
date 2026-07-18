<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // déclarant
            $table->string('category', 30); // transport | repas | hebergement | fournitures | carburant | communication | autre
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('XOF');
            $table->date('expense_date');
            $table->string('receipt_path')->nullable(); // stockage PRIVÉ (disk factpro.proofs.disk)
            $table->string('receipt_original_name')->nullable();
            $table->string('receipt_mime', 100)->nullable();
            $table->string('status', 15)->default('submitted'); // draft | submitted | approved | rejected | reimbursed
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->string('review_note')->nullable();
            $table->date('reimbursed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['user_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

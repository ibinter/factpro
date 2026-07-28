<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('iban')->nullable();
            $table->string('swift_bic')->nullable();
            $table->string('currency')->default('XOF');
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->date('value_date')->nullable();
            $table->string('description', 500);
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['credit', 'debit']);
            $table->string('reference')->nullable();
            $table->foreignId('matched_payment_id')
                ->nullable()
                ->constrained('document_payments')
                ->nullOnDelete();
            $table->timestamp('matched_at')->nullable();
            $table->boolean('is_reconciled')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_accounts');
    }
};

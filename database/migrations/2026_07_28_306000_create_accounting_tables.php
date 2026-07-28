<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20);
            $table->string('name');
            $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense'])->default('asset');
            $table->string('category')->nullable();
            $table->foreignId('parent_id')->nullable()->references('id')->on('accounts')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('reference')->nullable();
            $table->text('description');
            $table->enum('type', ['manual', 'auto_invoice', 'auto_payment', 'auto_purchase'])->default('manual');
            $table->foreignId('document_id')->nullable()->references('id')->on('documents')->nullOnDelete();
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
    }
};

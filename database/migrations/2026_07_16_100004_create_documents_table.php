<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // identifiant public de vérification QR
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('documents')->nullOnDelete(); // conversion devis→facture…

            // Type de document (cahier des charges §4 — 16 types)
            $table->string('type', 30); // quote | proforma | sales_order | purchase_order | delivery_note |
                                        // invoice | credit_note | payment_receipt | discharge | rma |
                                        // deposit_invoice | balance_invoice | recurring_invoice |
                                        // remittance_slip | work_order | pos_ticket
            $table->string('number', 40);
            $table->string('reference')->nullable(); // référence client / commande

            $table->string('status', 20)->default('draft');
            // draft | sent | viewed | accepted | rejected | partial | paid | overdue | cancelled | converted

            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->decimal('exchange_rate', 15, 6)->default(1);

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->string('discount_type', 10)->nullable();   // percent | fixed
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('amount_paid', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->string('template_key', 40)->nullable();

            // Anti-falsification (cahier des charges §5)
            $table->string('integrity_hash', 64)->nullable(); // SHA-256 du contenu
            $table->timestamp('finalized_at')->nullable();    // horodatage certifié
            $table->boolean('trial_watermark')->default(false);

            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'type', 'number']);
            $table->index(['company_id', 'type', 'status']);
            $table->index(['company_id', 'issue_date']);
        });

        Schema::create('document_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description');
            $table->decimal('quantity', 12, 2)->default(1);
            $table->string('unit', 20)->default('unité');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('line_total', 15, 2)->default(0); // HT après remise ligne
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('document_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('XOF');
            $table->string('method', 30)->default('cash'); // cash | mobile_money | card | bank_transfer | cheque | credit
            $table->string('reference')->nullable();
            $table->date('paid_at');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 30);
            $table->string('prefix', 10);
            $table->unsignedInteger('next_number')->default(1);
            $table->unsignedTinyInteger('padding')->default(4);
            $table->unsignedSmallInteger('year');
            $table->timestamps();

            $table->unique(['company_id', 'document_type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
        Schema::dropIfExists('document_payments');
        Schema::dropIfExists('document_lines');
        Schema::dropIfExists('documents');
    }
};

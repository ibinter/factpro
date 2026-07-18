<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Achats fournisseurs (cahier IBIG §10.1 « Journal des achats ») — répertoire
 * fournisseurs + factures d'achat (HT/TVA/TTC, échéance, paiement, justificatif
 * privé). Réservé BUSINESS/ENTERPRISE (§22.1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->default('CI');
            $table->string('tax_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'name']);
        });

        Schema::create('supplier_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->string('number');
            $table->string('reference')->nullable();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->decimal('amount_ht', 15, 2);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('amount_ttc', 15, 2);
            $table->string('currency', 3)->default('XOF');
            $table->string('category', 30)->default('marchandises'); // marchandises | services | fournitures | loyer | energie | transport | autre
            $table->string('status', 15)->default('unpaid'); // unpaid | partial | paid
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->date('paid_at')->nullable();
            $table->string('receipt_path')->nullable(); // stockage PRIVÉ (disk factpro.proofs.disk)
            $table->string('receipt_original_name')->nullable();
            $table->string('receipt_mime', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'invoice_date']);
            $table->index(['company_id', 'status']);
            $table->unique(['company_id', 'supplier_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_invoices');
        Schema::dropIfExists('suppliers');
    }
};

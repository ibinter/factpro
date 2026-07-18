<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Acomptes & plans de paiement échelonnés (cahier IBIG §12).
 * Un plan décompose le total d'un devis/facture en échéances datées ;
 * chaque échéance se matérialise en facture d'acompte puis de solde.
 * Réservé PRO+ (§22.1 « Avoir, reçu, acompte » dès PRO).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            // Devis / facture d'origine (peut disparaître sans casser le plan)
            $table->foreignId('source_document_id')->nullable()->constrained('documents')->nullOnDelete();

            $table->string('name');
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 3)->default('XOF');
            $table->string('status', 20)->default('active'); // active | completed | cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('payment_plan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_plan_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->date('due_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('percentage', 5, 2)->nullable();
            // Facture d'acompte / de solde générée pour cette échéance
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('status', 20)->default('pending'); // pending | invoiced | paid
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['payment_plan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_plan_installments');
        Schema::dropIfExists('payment_plans');
    }
};

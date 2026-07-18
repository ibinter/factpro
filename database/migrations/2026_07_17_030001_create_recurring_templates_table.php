<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Factures récurrentes (cahier des charges §3 — « Abonnements automatiques »).
 * Un gabarit décrit une facture type (lignes JSON) et sa planification :
 * le scheduler la matérialise en Document à chaque échéance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name'); // libellé interne ("Abonnement maintenance mensuel")

            // Planification
            $table->string('frequency', 20); // weekly | monthly | quarterly | semiannual | yearly
            $table->unsignedTinyInteger('interval')->default(1);       // toutes les N périodes
            $table->unsignedTinyInteger('day_of_month')->nullable();   // jour d'émission souhaité (1..28)
            $table->date('next_run_date');                             // prochaine émission
            $table->date('last_run_date')->nullable();                 // dernière émission
            $table->date('end_date')->nullable();                      // fin optionnelle par date
            $table->unsignedSmallInteger('occurrences_limit')->nullable(); // fin optionnelle par nombre
            $table->unsignedSmallInteger('occurrences_done')->default(0);

            // Contenu de la facture générée
            $table->string('currency', 3)->default('XOF');
            $table->unsignedSmallInteger('due_days')->default(30);  // échéance = émission + N jours
            $table->boolean('auto_finalize')->default(true);        // sceller automatiquement
            $table->boolean('auto_send')->default(false);           // envoyer par email au client
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->json('lines'); // gabarit des lignes (product_id, description, quantity, ...)

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
            $table->index(['next_run_date', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_templates');
    }
};

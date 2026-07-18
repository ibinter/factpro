<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Time tracking & projets (cahier §9) — projets clients avec budget,
 * saisie des heures et conversion en facture. Réservé BUSINESS/ENTERPRISE (§22.1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active'); // active | paused | completed | archived
            $table->decimal('hourly_rate', 15, 2)->nullable();   // taux horaire par défaut du projet
            $table->decimal('budget_hours', 8, 2)->nullable();   // budget en heures
            $table->decimal('budget_amount', 15, 2)->nullable(); // budget en montant
            $table->string('currency', 3)->default('XOF');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->unsignedInteger('duration_minutes'); // source de vérité de la durée
            $table->decimal('hourly_rate', 15, 2)->nullable(); // copie au moment de la saisie (sinon taux projet)
            $table->boolean('is_billable')->default(true);
            $table->boolean('is_billed')->default(false);
            $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete(); // facture générée
            $table->date('entry_date');
            $table->timestamps();

            $table->index(['project_id', 'is_billed']);
            $table->index(['company_id', 'entry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('projects');
    }
};

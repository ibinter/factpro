<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 15 — Ajoute les colonnes budget avancées sur projects :
 * devise budget, seuil d'alerte, date de dernier envoi d'alerte.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('budget_currency', 3)->default('XOF')->after('budget_amount');
            $table->tinyInteger('alert_threshold_pct')->default(80)->after('budget_currency');
            $table->timestamp('budget_alert_sent_at')->nullable()->after('alert_threshold_pct');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['budget_currency', 'alert_threshold_pct', 'budget_alert_sent_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Commissions vendeurs (cahier IBIG §3 CMD « Calcul automatique des commissions
 * par commercial ou agent ») — répertoire de vendeurs/commerciaux, affectation
 * d'un vendeur à un client (colonne customers.sales_agent_id) et décomptes de
 * commission sur les factures payées. Réservé BUSINESS/ENTERPRISE (§22.1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->decimal('commission_rate', 5, 2)->default(0); // taux % par défaut du vendeur
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
        });

        Schema::create('commission_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_agent_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('base_amount', 15, 2)->default(0);   // CA commissionnable de la période
            $table->decimal('rate', 5, 2)->default(0);           // taux appliqué (%)
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->string('status', 15)->default('pending');    // pending | paid
            $table->date('paid_at')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'sales_agent_id']);
        });

        // Affectation vendeur ↔ client : une colonne sur customers (jamais modifiée ailleurs).
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_agent_id')->nullable()->after('company_id');
            $table->index('sales_agent_id');

            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign('sales_agent_id')
                    ->references('id')->on('sales_agents')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // 1) Détacher la colonne de customers (FK + index + colonne) AVANT de
        //    supprimer les tables qu'elle référence.
        Schema::table('customers', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['sales_agent_id']);
            }
            $table->dropIndex(['sales_agent_id']);
            $table->dropColumn('sales_agent_id');
        });

        // 2) commission_payouts référence sales_agents → à supprimer en premier.
        Schema::dropIfExists('commission_payouts');
        Schema::dropIfExists('sales_agents');
    }
};

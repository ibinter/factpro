<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Phase 15 — Suivi du budget projet et facturation des jalons.
 */
class ProjectBillingService
{
    public function __construct(
        private DocumentService $documents,
    ) {
    }

    /**
     * Calcule la consommation du budget (heures ET montant).
     */
    public function getBudgetStatus(Project $project): array
    {
        $hoursLogged = $project->timeEntries()->sum('duration_minutes') / 60;
        $amountBilled = $project->milestones()
            ->where('status', 'invoiced')
            ->sum('billing_amount');

        return [
            'budget_hours' => $project->budget_hours,
            'hours_logged' => round($hoursLogged, 2),
            'hours_remaining' => $project->budget_hours ? max(0, $project->budget_hours - $hoursLogged) : null,
            'hours_pct' => $project->budget_hours ? min(100, round($hoursLogged / $project->budget_hours * 100)) : null,
            'budget_amount' => $project->budget_amount,
            'amount_billed' => $amountBilled,
            'amount_remaining' => $project->budget_amount ? max(0, $project->budget_amount - $amountBilled) : null,
            'amount_pct' => $project->budget_amount ? min(100, round($amountBilled / $project->budget_amount * 100)) : null,
            'over_budget' => ($project->budget_hours && $hoursLogged > $project->budget_hours)
                          || ($project->budget_amount && $amountBilled > $project->budget_amount),
        ];
    }

    /**
     * Génère une facture d'acompte pour un jalon.
     * Crée le Document directement (sans passer par DocumentService qui peut être immutable).
     */
    public function billMilestone(ProjectMilestone $milestone, User $user): Document
    {
        if ($milestone->status === 'invoiced') {
            throw new \LogicException('Ce jalon est déjà facturé.');
        }

        $project = $milestone->project()->with('customer')->first();

        if (! $project->customer_id) {
            throw new \LogicException('Le projet n\'a pas de client : associez un client avant de facturer un jalon.');
        }

        $company = $project->company;

        return DB::transaction(function () use ($milestone, $project, $company, $user) {
            $document = $this->documents->create($company, $user, [
                'type' => 'deposit_invoice',
                'customer_id' => $project->customer_id,
                'currency' => $project->currency ?? $company->currency ?? 'XOF',
                'issue_date' => now()->toDateString(),
                'reference' => 'Jalon : ' . $milestone->name . ' — ' . $project->name,
            ], [
                [
                    'product_id' => null,
                    'description' => $milestone->name . ($milestone->description ? ' — ' . $milestone->description : ''),
                    'quantity' => 1,
                    'unit' => 'forfait',
                    'unit_price' => (float) ($milestone->billing_amount ?? 0),
                    'tax_rate' => (float) $company->default_tax_rate,
                    'discount_percent' => 0,
                ],
            ]);

            $milestone->update([
                'status' => 'invoiced',
                'document_id' => $document->id,
                'invoiced_at' => now(),
            ]);

            return $document;
        });
    }

    /**
     * Vérifie si le budget est dépassé (ou seuil atteint) et marque l'alerte.
     * Retourne true si une alerte a été déclenchée.
     */
    public function checkBudgetAlert(Project $project): bool
    {
        $status = $this->getBudgetStatus($project);
        $threshold = $project->alert_threshold_pct ?? 80;

        $triggered = (
            ($status['hours_pct'] !== null && $status['hours_pct'] >= $threshold) ||
            ($status['amount_pct'] !== null && $status['amount_pct'] >= $threshold)
        );

        if ($triggered && $project->budget_alert_sent_at === null) {
            $project->update(['budget_alert_sent_at' => now()]);
            return true;
        }

        return false;
    }

    /**
     * Retourne le % de complétion global du projet (moyenne des jalons).
     */
    public function getCompletionPct(Project $project): int
    {
        $milestones = $project->milestones()->get(['completion_pct', 'status']);

        if ($milestones->isEmpty()) {
            return 0;
        }

        // Les jalons 'invoiced' comptent comme 100%
        $total = $milestones->sum(function ($m) {
            return $m->status === 'invoiced' ? 100 : $m->completion_pct;
        });

        return (int) round($total / $milestones->count());
    }
}

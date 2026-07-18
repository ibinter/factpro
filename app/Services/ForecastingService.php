<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Document;
use App\Models\ForecastSnapshot;
use App\Models\SalesTarget;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ForecastingService
{
    /**
     * Calcule la prévision CA pour le mois en cours.
     * Méthodes : linear_trend (12 mois), moving_average (3 mois), last_year
     */
    public function forecastCurrentMonth(int $companyId): array
    {
        $historicalData = $this->getMonthlyRevenue($companyId, 12);
        $currentMonthActual = $this->getCurrentMonthRevenue($companyId);
        $daysElapsed = (int) now()->format('j');
        $daysInMonth = (int) now()->format('t');
        $dailyRate = $daysElapsed > 0 ? $currentMonthActual / $daysElapsed : 0;

        return [
            'actual_so_far' => $currentMonthActual,
            'days_elapsed' => $daysElapsed,
            'days_remaining' => $daysInMonth - $daysElapsed,
            'daily_rate' => $dailyRate,
            'forecasts' => [
                'linear_projection' => round($dailyRate * $daysInMonth, 0),
                'moving_average' => $this->movingAverage($historicalData, 3),
                'last_year' => $this->sameMonthLastYear($companyId),
            ],
        ];
    }

    /**
     * Compare l'objectif du mois avec le CA réel.
     *
     * @return array{target: float, actual: float, pct_achieved: float, on_track: bool, gap: float, currency: string}
     */
    public function compareWithTarget(int $companyId, ?int $userId = null): array
    {
        $target = SalesTarget::where('company_id', $companyId)
            ->where('period_year', now()->year)
            ->where('period_month', now()->month)
            ->where('period_type', 'month')
            ->when($userId, fn ($q) => $q->where('assigned_to_id', $userId))
            ->when(! $userId, fn ($q) => $q->whereNull('assigned_to_id'))
            ->first();

        $actual = $this->getCurrentMonthRevenue($companyId, $userId);
        $targetAmount = (float) ($target?->target_amount ?? 0);
        $pct = $targetAmount > 0 ? round($actual / $targetAmount * 100, 1) : 0;

        return [
            'target' => $targetAmount,
            'actual' => $actual,
            'pct_achieved' => $pct,
            'on_track' => $pct >= 70,
            'gap' => $targetAmount - $actual,
            'currency' => $target?->currency ?? 'XOF',
        ];
    }

    /**
     * CA mensuel sur N mois (pour graphique historique).
     */
    public function getMonthlyRevenue(int $companyId, int $months = 12): array
    {
        return collect(range($months - 1, 0))->map(function ($i) use ($companyId) {
            $month = now()->subMonths($i);

            $revenue = (float) Document::where('company_id', $companyId)
                ->where('type', 'invoice')
                ->whereNotIn('status', ['cancelled', 'draft'])
                ->whereYear('issue_date', $month->year)
                ->whereMonth('issue_date', $month->month)
                ->sum('total');

            return [
                'month' => $month->format('Y-m'),
                'label' => $month->translatedFormat('M Y'),
                'revenue' => $revenue,
                'year' => (int) $month->year,
                'month_num' => (int) $month->month,
            ];
        })->values()->all();
    }

    /**
     * CA du mois en cours (optionnel filtré par vendeur via documents).
     */
    public function getCurrentMonthRevenue(int $companyId, ?int $userId = null): float
    {
        $query = Document::where('company_id', $companyId)
            ->where('type', 'invoice')
            ->whereNotIn('status', ['cancelled', 'draft'])
            ->whereYear('issue_date', now()->year)
            ->whereMonth('issue_date', now()->month);

        // Filtrer par vendeur si spécifié (via customer.sales_agent_id)
        if ($userId !== null) {
            $query->whereHas('customer', function ($q) use ($userId, $companyId) {
                $q->whereHas('salesAgent', fn ($sq) => $sq->where('company_id', $companyId));
                // On filtre sur l'user_id via la table sales_agents
                $q->whereExists(function ($sub) use ($userId) {
                    $sub->select(DB::raw(1))
                        ->from('sales_agents')
                        ->whereColumn('sales_agents.id', 'customers.sales_agent_id')
                        ->whereExists(function ($inner) use ($userId) {
                            // SalesAgent lié à un user
                            $inner->select(DB::raw(1))
                                ->from('users')
                                ->whereColumn('users.id', 'sales_agents.user_id')
                                ->where('users.id', $userId);
                        });
                });
            });
        }

        return (float) $query->sum('total');
    }

    /**
     * Moyenne mobile sur N derniers mois.
     */
    private function movingAverage(array $data, int $periods): float
    {
        $slice = array_slice($data, -$periods);
        if (empty($slice)) {
            return 0.0;
        }
        $sum = array_sum(array_column($slice, 'revenue'));

        return round($sum / count($slice), 0);
    }

    /**
     * CA du même mois l'année précédente.
     */
    private function sameMonthLastYear(int $companyId): float
    {
        return (float) Document::where('company_id', $companyId)
            ->where('type', 'invoice')
            ->whereNotIn('status', ['cancelled', 'draft'])
            ->whereYear('issue_date', now()->year - 1)
            ->whereMonth('issue_date', now()->month)
            ->sum('total');
    }

    /**
     * Génère une alerte si un vendeur est en sous-performance (< 50% objectif à mi-mois).
     */
    public function checkUnderperformance(int $companyId): array
    {
        $daysElapsed = (int) now()->format('j');
        $daysInMonth = (int) now()->format('t');
        $monthProgress = $daysInMonth > 0 ? $daysElapsed / $daysInMonth : 0;

        // Chercher uniquement si on est au moins à mi-mois
        if ($monthProgress < 0.4) {
            return [];
        }

        $targets = SalesTarget::where('company_id', $companyId)
            ->where('period_year', now()->year)
            ->where('period_month', now()->month)
            ->where('period_type', 'month')
            ->whereNotNull('assigned_to_id')
            ->with('assignedTo:id,name,email')
            ->get();

        $underperformers = [];

        foreach ($targets as $target) {
            $actual = $this->getCurrentMonthRevenue($companyId, $target->assigned_to_id);
            $pct = $target->target_amount > 0 ? ($actual / $target->target_amount * 100) : 0;

            if ($pct < 50) {
                $underperformers[] = [
                    'user_id' => $target->assigned_to_id,
                    'name' => $target->assignedTo?->name ?? 'Inconnu',
                    'email' => $target->assignedTo?->email ?? '',
                    'target' => (float) $target->target_amount,
                    'actual' => $actual,
                    'pct_achieved' => round($pct, 1),
                    'gap' => (float) $target->target_amount - $actual,
                    'currency' => $target->currency,
                ];
            }
        }

        return $underperformers;
    }

    /**
     * Sauvegarde un snapshot prévisionnel pour une company.
     */
    public function saveSnapshot(int $companyId): ForecastSnapshot
    {
        $forecast = $this->forecastCurrentMonth($companyId);
        $forecasts = $forecast['forecasts'];

        // Meilleure prévision = moyenne des 3 méthodes (non nulles)
        $values = array_filter([
            $forecasts['linear_projection'],
            $forecasts['moving_average'],
            $forecasts['last_year'],
        ]);
        $forecastedRevenue = ! empty($values) ? array_sum($values) / count($values) : 0;

        // Calculer la précision du snapshot précédent pour ce mois si le mois est terminé
        $accuracyPct = null;
        $previousSnapshot = ForecastSnapshot::where('company_id', $companyId)
            ->where('period_year', now()->year)
            ->where('period_month', now()->month)
            ->latest()
            ->first();

        if ($previousSnapshot && $previousSnapshot->forecasted_revenue > 0) {
            $accuracyPct = round(
                abs($forecast['actual_so_far'] - $previousSnapshot->forecasted_revenue)
                    / $previousSnapshot->forecasted_revenue * 100,
                2
            );
        }

        return ForecastSnapshot::create([
            'company_id' => $companyId,
            'snapshot_date' => now()->toDateString(),
            'period_month' => now()->month,
            'period_year' => now()->year,
            'actual_revenue' => $forecast['actual_so_far'],
            'forecasted_revenue' => round($forecastedRevenue, 2),
            'method' => 'combined',
            'accuracy_pct' => $accuracyPct,
        ]);
    }

    /**
     * Calcule la précision des prévisions passées (N derniers mois).
     */
    public function forecastAccuracy(int $companyId, int $months = 6): array
    {
        return ForecastSnapshot::where('company_id', $companyId)
            ->whereNotNull('accuracy_pct')
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->limit($months)
            ->get()
            ->map(fn ($s) => [
                'period' => $s->period_year.'-'.str_pad($s->period_month, 2, '0', STR_PAD_LEFT),
                'actual_revenue' => (float) $s->actual_revenue,
                'forecasted_revenue' => (float) $s->forecasted_revenue,
                'accuracy_pct' => (float) $s->accuracy_pct,
                'method' => $s->method,
            ])
            ->all();
    }
}

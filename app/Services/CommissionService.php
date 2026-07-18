<?php

namespace App\Services;

use App\Models\CommissionPayout;
use App\Models\Company;
use App\Models\Document;
use App\Models\SalesAgent;
use Carbon\Carbon;

/**
 * Calcul automatique des commissions vendeurs (cahier IBIG §3 CMD).
 *
 * Le CA commissionnable d'un vendeur = Σ total des factures PAYÉES et
 * finalisées des clients qui lui sont affectés sur une période (par issue_date),
 * les avoirs (credit_note) venant en déduction.
 */
class CommissionService
{
    /** Familles de documents facturables comptées positivement. */
    public const BILLABLE_TYPES = ['invoice', 'deposit_invoice', 'balance_invoice', 'pos_ticket'];

    /**
     * CA commissionnable d'un vendeur sur la période [from, to] (bornes incluses).
     * Factures payées + finalisées comptées positivement, avoirs finalisés déduits.
     */
    public function commissionableRevenue(SalesAgent $agent, Carbon $from, Carbon $to): float
    {
        $customerIds = $agent->customers()->pluck('customers.id');

        if ($customerIds->isEmpty()) {
            return 0.0;
        }

        $fromDate = $from->copy()->startOfDay()->toDateString();
        $toDate = $to->copy()->endOfDay()->toDateString();

        $base = fn () => Document::where('company_id', $agent->company_id)
            ->whereIn('customer_id', $customerIds)
            ->whereNotNull('finalized_at')
            ->whereBetween('issue_date', [$fromDate, $toDate]);

        $positive = (float) $base()
            ->whereIn('type', self::BILLABLE_TYPES)
            ->where('status', 'paid')
            ->sum('total');

        // Avoirs émis (finalisés) sur la période → déduction du CA commissionnable.
        $credits = (float) $base()
            ->where('type', 'credit_note')
            ->sum('total');

        return round($positive - $credits, 2);
    }

    /**
     * Aperçu des commissions de tous les vendeurs actifs d'une société sur la période.
     * Retourne les lignes (base, taux, commission) + les totaux généraux.
     */
    public function preview(Company $company, Carbon $from, Carbon $to): array
    {
        $agents = SalesAgent::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $rows = [];
        $totalBase = 0.0;
        $totalCommission = 0.0;

        foreach ($agents as $agent) {
            $base = $this->commissionableRevenue($agent, $from, $to);
            $rate = (float) $agent->commission_rate;
            $commission = round($base * $rate / 100, 2);

            $rows[] = [
                'agent_id' => $agent->id,
                'name' => $agent->name,
                'rate' => $rate,
                'base' => $base,
                'commission' => $commission,
            ];

            $totalBase += $base;
            $totalCommission += $commission;
        }

        return [
            'rows' => $rows,
            'total_base' => round($totalBase, 2),
            'total_commission' => round($totalCommission, 2),
        ];
    }

    /**
     * Génère (ou met à jour) le décompte de commission d'un vendeur sur une période.
     * Anti-doublon : un décompte existant pour le même vendeur + période exacte est
     * recalculé plutôt que dupliqué.
     */
    public function generatePayout(SalesAgent $agent, Carbon $from, Carbon $to, ?float $rateOverride = null, ?int $createdBy = null): CommissionPayout
    {
        $base = $this->commissionableRevenue($agent, $from, $to);
        $rate = $rateOverride !== null ? (float) $rateOverride : (float) $agent->commission_rate;
        $commission = round($base * $rate / 100, 2);

        $periodStart = $from->copy()->startOfDay()->toDateString();
        $periodEnd = $to->copy()->startOfDay()->toDateString();

        $existing = CommissionPayout::where('company_id', $agent->company_id)
            ->where('sales_agent_id', $agent->id)
            ->whereDate('period_start', $periodStart)
            ->whereDate('period_end', $periodEnd)
            ->first();

        $attributes = [
            'base_amount' => $base,
            'rate' => $rate,
            'commission_amount' => $commission,
        ];

        if ($existing) {
            // On ne réécrase pas un décompte déjà réglé : on le renvoie tel quel.
            if ($existing->status !== 'paid') {
                $existing->update($attributes);
            }

            return $existing->fresh();
        }

        return CommissionPayout::create([
            'company_id' => $agent->company_id,
            'sales_agent_id' => $agent->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => 'pending',
            'created_by' => $createdBy,
            ...$attributes,
        ]);
    }

    /** Marque un décompte comme payé (statut + date de règlement). */
    public function markPaid(CommissionPayout $payout): CommissionPayout
    {
        if ($payout->status !== 'paid') {
            $payout->update([
                'status' => 'paid',
                'paid_at' => now()->toDateString(),
            ]);
        }

        return $payout->fresh();
    }
}

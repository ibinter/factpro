<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerScore;
use App\Models\Document;
use Carbon\Carbon;

class CustomerScoringService
{
    public function computeForCompany(Company $company): void
    {
        Customer::where('company_id', $company->id)->each(fn ($c) => $this->compute($c, $company));
    }

    public function compute(Customer $customer, Company $company): CustomerScore
    {
        $invoices = Document::where('company_id', $company->id)
            ->where('customer_id', $customer->id)
            ->whereIn('type', ['invoice', 'simple_invoice', 'export_invoice'])
            ->get();

        $total        = $invoices->count();
        $totalRevenue = $invoices->sum('total_ttc');
        $lastOrder    = $invoices->max('issue_date');
        $daysSinceLast = $lastOrder ? Carbon::parse($lastOrder)->diffInDays(now()) : null;

        // Délai paiement moyen & retards
        $paidInvoices = $invoices->where('status', 'paid');
        $lateCount    = 0;
        $totalPayDays = 0;
        $paidCount    = 0;

        foreach ($paidInvoices as $inv) {
            if ($inv->due_date && $inv->updated_at) {
                $days          = Carbon::parse($inv->issue_date)->diffInDays(Carbon::parse($inv->updated_at));
                $totalPayDays += $days;
                $paidCount++;
                if ($inv->due_date && Carbon::parse($inv->updated_at)->gt(Carbon::parse($inv->due_date))) {
                    $lateCount++;
                }
            }
        }

        $avgPayDays = $paidCount > 0 ? round($totalPayDays / $paidCount, 1) : null;

        // Fréquence moyenne commandes
        $dates  = $invoices->pluck('issue_date')->sort()->values();
        $avgFreq = null;
        if ($dates->count() > 1) {
            $gaps = [];
            for ($i = 1; $i < $dates->count(); $i++) {
                $gaps[] = Carbon::parse($dates[$i - 1])->diffInDays(Carbon::parse($dates[$i]));
            }
            $avgFreq = count($gaps) > 0 ? round(array_sum($gaps) / count($gaps), 1) : null;
        }

        // Score risque paiement (0=bon, 100=mauvais)
        $riskScore = 0;
        if ($total > 0) {
            $lateRatio  = $lateCount / max($total, 1);
            $riskScore += $lateRatio * 50;
            if ($avgPayDays) {
                $riskScore += min(($avgPayDays / 90) * 30, 30);
            }
            $overdueCount = $invoices->whereIn('status', ['overdue'])->count();
            $riskScore   += min(($overdueCount / max($total, 1)) * 20, 20);
        }
        $riskScore = min(round($riskScore, 2), 100);

        // Score churn (0=stable, 100=churné)
        $churnScore = 0;
        if ($daysSinceLast !== null) {
            $expectedCycle = $avgFreq ?? 30;
            $ratio         = $daysSinceLast / max($expectedCycle * 3, 90);
            $churnScore    = min(round($ratio * 100, 2), 100);
        } elseif ($total === 0) {
            $churnScore = 50;
        }

        $riskLabel  = $riskScore >= 60 ? 'élevé' : ($riskScore >= 30 ? 'modéré' : 'faible');
        $churnLabel = $churnScore >= 70 ? 'churné' : ($churnScore >= 40 ? 'à risque' : 'stable');

        return CustomerScore::updateOrCreate(
            ['customer_id' => $customer->id, 'company_id' => $company->id],
            [
                'total_invoices'           => $total,
                'total_revenue'            => $totalRevenue,
                'avg_payment_days'         => $avgPayDays,
                'late_payments_count'      => $lateCount,
                'days_since_last_order'    => $daysSinceLast,
                'avg_order_frequency_days' => $avgFreq,
                'payment_risk_score'       => $riskScore,
                'churn_score'              => $churnScore,
                'risk_label'               => $riskLabel,
                'churn_label'              => $churnLabel,
                'last_order_date'          => $lastOrder,
                'computed_at'              => now(),
                'factors'                  => [
                    'late_ratio'      => $total > 0 ? round($lateCount / $total * 100) : 0,
                    'avg_pay_days'    => $avgPayDays,
                    'days_since_last' => $daysSinceLast,
                    'avg_freq_days'   => $avgFreq,
                    'total_invoices'  => $total,
                ],
            ]
        );
    }
}

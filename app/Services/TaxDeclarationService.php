<?php

namespace App\Services;

use App\Models\Company;
use App\Models\TaxConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaxDeclarationService
{
    /**
     * Génère un tableau récapitulatif TVA pour la période donnée.
     */
    public function vatSummary(Company $company, Carbon $from, Carbon $to): array
    {
        /** @var TaxConfig|null $config */
        $config = TaxConfig::where('company_id', $company->id)->first();

        $regime = $config?->tax_regime ?? 'custom';

        // Documents finalisés sur la période (factures, avoirs, dépôts...)
        $docs = DB::table('documents')
            ->where('company_id', $company->id)
            ->whereNotNull('finalized_at')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereIn('type', ['invoice', 'deposit_invoice', 'balance_invoice', 'pos_ticket', 'credit_note'])
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $docsCount = $docs->count();

        // Regroupement TVA par taux depuis les lignes de documents
        $tvaByRate = [];

        foreach ($docs as $doc) {
            $sign = $doc->type === 'credit_note' ? -1 : 1;

            // Récupérer les lignes du document
            $lines = DB::table('document_lines')
                ->where('document_id', $doc->id)
                ->get();

            if ($lines->isEmpty()) {
                // Fallback: utiliser subtotal/tax_amount du document
                $rate = $doc->tax_amount > 0
                    ? round(($doc->tax_amount / max($doc->subtotal, 1)) * 100, 2)
                    : 0;
                $key = (string) $rate;
                if (! isset($tvaByRate[$key])) {
                    $tvaByRate[$key] = ['rate' => $rate, 'base' => 0, 'tva' => 0];
                }
                $tvaByRate[$key]['base'] += $sign * (float) $doc->subtotal;
                $tvaByRate[$key]['tva'] += $sign * (float) $doc->tax_amount;
            } else {
                foreach ($lines as $line) {
                    $taxRate = (float) ($line->tax_rate ?? 0);
                    $key = (string) $taxRate;
                    if (! isset($tvaByRate[$key])) {
                        $tvaByRate[$key] = ['rate' => $taxRate, 'base' => 0, 'tva' => 0];
                    }
                    $qty = (float) ($line->quantity ?? 1);
                    $up = (float) ($line->unit_price ?? 0);
                    $base = $qty * $up;
                    $tva = $base * $taxRate / 100;
                    $tvaByRate[$key]['base'] += $sign * $base;
                    $tvaByRate[$key]['tva'] += $sign * $tva;
                }
            }
        }

        $tvaCollected = array_values($tvaByRate);
        $totalTva = array_sum(array_column($tvaCollected, 'tva'));

        // TPS (Taxe sur Prestation de Services)
        $tpsCollected = 0.0;
        if ($config?->has_tps) {
            $tpsRate = (float) $config->tps_rate;
            foreach ($docs as $doc) {
                $sign = $doc->type === 'credit_note' ? -1 : 1;
                $tpsCollected += $sign * (float) $doc->subtotal * $tpsRate / 100;
            }
        }

        // OCA (Contribution Communautaire de l'UEMOA)
        $ocaCollected = 0.0;
        if ($config?->has_oca) {
            $ocaRate = (float) $config->oca_rate;
            foreach ($docs as $doc) {
                $sign = $doc->type === 'credit_note' ? -1 : 1;
                $ocaCollected += $sign * (float) $doc->subtotal * $ocaRate / 100;
            }
        }

        // Timbre fiscal
        $timbreTotal = 0.0;
        if ($config?->has_timbre) {
            $timbreTotal = $docsCount * (float) $config->timbre_amount;
        }

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'regime' => $regime,
            'tva_collected' => $tvaCollected,
            'tps_collected' => round($tpsCollected, 2),
            'oca_collected' => round($ocaCollected, 2),
            'timbre_total' => round($timbreTotal, 2),
            'total_tax_due' => round($totalTva + $tpsCollected + $ocaCollected + $timbreTotal, 2),
            'documents_count' => $docsCount,
        ];
    }

    /**
     * Calcule la déclaration TVA mensuelle Sénégal (formulaire DGID).
     *
     * @return array{ca_ttc: float, tva_collectee: float, tva_deductible: float, tva_nette: float, ras_prestataires: float}
     */
    public function generateSenegalDeclaration(int $companyId, int $month, int $year): array
    {
        $from = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $to   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $docs = DB::table('documents')
            ->where('company_id', $companyId)
            ->whereNotNull('finalized_at')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereIn('type', ['invoice', 'deposit_invoice', 'balance_invoice', 'pos_ticket', 'credit_note'])
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $caTtc        = 0.0;
        $tvaCollectee = 0.0;

        foreach ($docs as $doc) {
            $sign          = $doc->type === 'credit_note' ? -1 : 1;
            $taxRate       = (float) ($doc->tax_amount > 0
                ? round(($doc->tax_amount / max($doc->subtotal, 1)) * 100, 2)
                : 0);
            // Only count TVA at 18% (standard SN rate); 0% lines contribute 0 TVA
            $caTtc        += $sign * (float) $doc->total;
            $tvaCollectee += $sign * (float) $doc->tax_amount;
        }

        // RAS prestataires non-résidents (20%) — nécessite un champ métier dédié.
        // En l'absence de marquage explicite, on retourne 0 (le métier peut surcharger).
        $rasPrestataires = 0.0;

        // TVA déductible sur achats (hors périmètre documents ventes)
        $tvaDeductible = 0.0;

        $tvaNette = $tvaCollectee - $tvaDeductible;

        return [
            'country'          => 'SN',
            'period'           => ['month' => $month, 'year' => $year],
            'ca_ttc'           => round($caTtc, 2),
            'tva_collectee'    => round($tvaCollectee, 2),
            'tva_deductible'   => round($tvaDeductible, 2),
            'tva_nette'        => round($tvaNette, 2),
            'ras_prestataires' => round($rasPrestataires, 2),
        ];
    }

    /**
     * Calcule la déclaration DGI-CI trimestrielle (Côte d'Ivoire).
     *
     * @return array{ca_ht: float, tva_collectee: float, tps: float, oca: float, tva_nette: float, total_a_payer: float}
     */
    public function generateCoteIvoireDeclaration(int $companyId, int $quarter, int $year): array
    {
        // Trimestre → mois de début/fin
        $startMonth = ($quarter - 1) * 3 + 1;
        $from = Carbon::createFromDate($year, $startMonth, 1)->startOfMonth();
        $to   = Carbon::createFromDate($year, $startMonth + 2, 1)->endOfMonth();

        /** @var TaxConfig|null $config */
        $config = TaxConfig::where('company_id', $companyId)->first();

        $docs = DB::table('documents')
            ->where('company_id', $companyId)
            ->whereNotNull('finalized_at')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereIn('type', ['invoice', 'deposit_invoice', 'balance_invoice', 'pos_ticket', 'credit_note'])
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $caHt         = 0.0;
        $tvaCollectee = 0.0;

        foreach ($docs as $doc) {
            $sign          = $doc->type === 'credit_note' ? -1 : 1;
            $caHt         += $sign * (float) $doc->subtotal;
            $tvaCollectee += $sign * (float) $doc->tax_amount;
        }

        $tpsRate = $config?->has_tps ? (float) $config->tps_rate : 1.0;
        $ocaRate = $config?->has_oca ? (float) $config->oca_rate : 0.5;

        $tps = round($caHt * $tpsRate / 100, 2);
        $oca = round($caHt * $ocaRate / 100, 2);

        $tvaNette   = $tvaCollectee; // TVA déductible sur achats non incluse (hors périmètre)
        $totalAPayer = round($tvaNette + $tps + $oca, 2);

        return [
            'country'      => 'CI',
            'period'       => ['quarter' => $quarter, 'year' => $year],
            'ca_ht'        => round($caHt, 2),
            'tva_collectee'=> round($tvaCollectee, 2),
            'tps'          => $tps,
            'oca'          => $oca,
            'tva_nette'    => round($tvaNette, 2),
            'total_a_payer'=> $totalAPayer,
        ];
    }

    /**
     * Calcule la déclaration G50 mensuelle Algérie (DGI-DZ).
     *
     * @return array{ca_ht: float, tva_collectee: float, tva_deductible: float, tap: float, tva_nette: float, tap_a_payer: float, total: float}
     */
    public function generateAlgerieDeclaration(int $companyId, int $month, int $year): array
    {
        $from = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $to   = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        $docs = DB::table('documents')
            ->where('company_id', $companyId)
            ->whereNotNull('finalized_at')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereIn('type', ['invoice', 'deposit_invoice', 'balance_invoice', 'pos_ticket', 'credit_note'])
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $caHt         = 0.0;
        $tvaCollectee = 0.0;

        foreach ($docs as $doc) {
            $sign          = $doc->type === 'credit_note' ? -1 : 1;
            $caHt         += $sign * (float) $doc->subtotal;
            $tvaCollectee += $sign * (float) $doc->tax_amount;
        }

        // TAP : 2% sur CA HT (hors exportations) — taux standard DZ
        $tapRate     = 2.0;
        $tapAPayer   = round($caHt * $tapRate / 100, 2);

        // TVA déductible sur achats (hors périmètre documents ventes)
        $tvaDeductible = 0.0;
        $tvaNette      = round($tvaCollectee - $tvaDeductible, 2);

        $total = round($tvaNette + $tapAPayer, 2);

        return [
            'country'        => 'DZ',
            'period'         => ['month' => $month, 'year' => $year],
            'ca_ht'          => round($caHt, 2),
            'tva_collectee'  => round($tvaCollectee, 2),
            'tva_deductible' => round($tvaDeductible, 2),
            'tap'            => $tapAPayer,
            'tva_nette'      => $tvaNette,
            'tap_a_payer'    => $tapAPayer,
            'total'          => $total,
        ];
    }

    /**
     * Retourne un CSV des documents avec détail TVA.
     */
    public function exportCsv(Company $company, Carbon $from, Carbon $to): string
    {
        $config = TaxConfig::where('company_id', $company->id)->first();

        $docs = DB::table('documents')
            ->leftJoin('customers', 'customers.id', '=', 'documents.customer_id')
            ->where('documents.company_id', $company->id)
            ->whereNotNull('documents.finalized_at')
            ->whereNotIn('documents.status', ['draft', 'cancelled'])
            ->whereIn('documents.type', ['invoice', 'deposit_invoice', 'balance_invoice', 'pos_ticket', 'credit_note'])
            ->whereBetween('documents.issue_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('documents.issue_date')
            ->get([
                'documents.number',
                'documents.issue_date',
                'customers.name as customer_name',
                'documents.subtotal',
                'documents.tax_amount',
                'documents.total',
                'documents.type',
            ]);

        $bom = "\xEF\xBB\xBF";
        $headers = ['N° doc', 'Date', 'Client', 'Base HT', 'TVA (taux%)', 'Montant TVA', 'TPS', 'OCA', 'Total TTC'];
        $rows = [$headers];

        foreach ($docs as $doc) {
            $sign = $doc->type === 'credit_note' ? -1 : 1;
            $base = $sign * (float) $doc->subtotal;
            $tva = $sign * (float) $doc->tax_amount;
            $total = $sign * (float) $doc->total;
            $taxRate = $base > 0 ? round($tva / $base * 100, 2) : 0;

            $tps = 0.0;
            if ($config?->has_tps) {
                $tps = round($base * (float) $config->tps_rate / 100, 2);
            }
            $oca = 0.0;
            if ($config?->has_oca) {
                $oca = round($base * (float) $config->oca_rate / 100, 2);
            }

            $rows[] = [
                $doc->number,
                $doc->issue_date,
                $doc->customer_name ?? '',
                number_format($base, 2, '.', ''),
                $taxRate.'%',
                number_format($tva, 2, '.', ''),
                number_format($tps, 2, '.', ''),
                number_format($oca, 2, '.', ''),
                number_format($total, 2, '.', ''),
            ];
        }

        $csv = $bom;
        foreach ($rows as $row) {
            $csv .= implode(';', array_map(fn ($v) => '"'.str_replace('"', '""', (string) $v).'"', $row))."\r\n";
        }

        return $csv;
    }
}

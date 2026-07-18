<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Document;
use App\Models\SupplierInvoice;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Comptabilité simplifiée (cahier IBIG §10) — journal des ventes, balance
 * âgée clients, récapitulatif TVA et compte de résultat simplifié.
 *
 * Seuls les documents FINALISÉS (finalized_at non nul, hors draft/cancelled)
 * sont comptabilisés. Un avoir (credit_note) s'enregistre en NÉGATIF.
 */
class AccountingService
{
    /** Types de documents facturables comptabilisés dans le journal des ventes. */
    public const BILLABLE_TYPES = [
        'invoice', 'deposit_invoice', 'balance_invoice', 'pos_ticket', 'credit_note',
    ];

    /** Signe comptable d'un document (avoir = négatif). */
    public static function sign(string $type): int
    {
        return $type === 'credit_note' ? -1 : 1;
    }

    /** Requête de base : documents facturables finalisés d'une société. */
    private function billableQuery(Company $company): Builder
    {
        return Document::query()
            ->where('company_id', $company->id)
            ->whereIn('type', self::BILLABLE_TYPES)
            ->whereNotNull('finalized_at')
            ->whereNotIn('status', ['draft', 'cancelled']);
    }

    /**
     * Journal des ventes automatique : une ligne signée par document
     * facturable finalisé de la période, triée par date puis numéro.
     */
    public function salesJournal(Company $company, CarbonInterface $from, CarbonInterface $to): array
    {
        $documents = $this->billableQuery($company)
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->with('customer:id,name')
            ->orderBy('issue_date')
            ->orderBy('number')
            ->get();

        $lines = $documents->map(function (Document $document) {
            $sign = self::sign($document->type);

            return [
                'date' => $document->issue_date->toDateString(),
                'piece' => $document->number,
                'client' => $document->customer?->name ?? '—',
                'ht' => round($sign * ((float) $document->subtotal - (float) $document->discount_amount), 2),
                'tva' => round($sign * (float) $document->tax_amount, 2),
                'ttc' => round($sign * (float) $document->total, 2),
                'type_label' => $document->type_label,
                'status' => $document->status,
            ];
        })->values();

        return [
            'lines' => $lines->all(),
            'totals' => [
                'ht' => round($lines->sum('ht'), 2),
                'tva' => round($lines->sum('tva'), 2),
                'ttc' => round($lines->sum('ttc'), 2),
            ],
        ];
    }

    /**
     * Balance âgée clients : encours (total − encaissé) des documents
     * facturables finalisés non soldés, avoirs déduits, ventilé par
     * ancienneté depuis la date d'échéance (sinon date d'émission).
     */
    public function agedBalance(Company $company): array
    {
        $documents = $this->billableQuery($company)
            ->with('customer:id,name')
            ->get();

        $today = now()->startOfDay();
        $byCustomer = [];

        foreach ($documents as $document) {
            $sign = self::sign($document->type);
            $balance = round($sign * ((float) $document->total - (float) $document->amount_paid), 2);

            if (abs($balance) < 0.005) {
                continue; // document soldé
            }

            $reference = $document->due_date ?? $document->issue_date;
            $overdueDays = $reference->startOfDay()->lessThan($today)
                ? (int) $reference->startOfDay()->diffInDays($today)
                : 0;

            $bucket = match (true) {
                $overdueDays <= 0 => 'current',
                $overdueDays <= 30 => 'b0_30',
                $overdueDays <= 60 => 'b31_60',
                $overdueDays <= 90 => 'b61_90',
                default => 'b90_plus',
            };

            $key = $document->customer_id ?? 0;
            $byCustomer[$key] ??= [
                'client' => $document->customer?->name ?? '—',
                'current' => 0.0,
                'b0_30' => 0.0,
                'b31_60' => 0.0,
                'b61_90' => 0.0,
                'b90_plus' => 0.0,
                'total' => 0.0,
            ];

            $byCustomer[$key][$bucket] = round($byCustomer[$key][$bucket] + $balance, 2);
            $byCustomer[$key]['total'] = round($byCustomer[$key]['total'] + $balance, 2);
        }

        // Clients à solde nul : ignorés.
        $rows = collect($byCustomer)
            ->filter(fn (array $row) => abs($row['total']) >= 0.005)
            ->sortBy('client', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $totals = [];
        foreach (['current', 'b0_30', 'b31_60', 'b61_90', 'b90_plus', 'total'] as $column) {
            $totals[$column] = round($rows->sum($column), 2);
        }

        return ['rows' => $rows->all(), 'totals' => $totals];
    }

    /**
     * Récapitulatif TVA : base HT, TVA collectée (ventes) et TVA déductible
     * (achats fournisseurs) par mois — TVA nette = collectée − déductible.
     * Prépare la déclaration. La TVA déductible n'est intégrée que si le
     * module achats est installé (guard Schema::hasTable).
     */
    public function vatSummary(Company $company, CarbonInterface $from, CarbonInterface $to): array
    {
        $documents = $this->billableQuery($company)
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('issue_date')
            ->get();

        $byMonth = [];
        $blank = fn (string $month) => [
            'month' => $month, 'ht' => 0.0, 'tva' => 0.0, 'ttc' => 0.0,
            'vat_deductible' => 0.0, 'vat_net' => 0.0,
        ];

        foreach ($documents as $document) {
            $sign = self::sign($document->type);
            $month = $document->issue_date->format('Y-m');

            $byMonth[$month] ??= $blank($month);
            $byMonth[$month]['ht'] = round($byMonth[$month]['ht'] + $sign * ((float) $document->subtotal - (float) $document->discount_amount), 2);
            $byMonth[$month]['tva'] = round($byMonth[$month]['tva'] + $sign * (float) $document->tax_amount, 2);
            $byMonth[$month]['ttc'] = round($byMonth[$month]['ttc'] + $sign * (float) $document->total, 2);
        }

        // TVA déductible : Σ vat_amount des factures d'achat de la période.
        if (Schema::hasTable('supplier_invoices')) {
            $purchases = DB::table('supplier_invoices')
                ->where('company_id', $company->id)
                ->whereNull('deleted_at')
                ->whereBetween('invoice_date', [$from->toDateString(), $to->toDateString()])
                ->get(['invoice_date', 'vat_amount']);

            foreach ($purchases as $purchase) {
                $month = substr((string) $purchase->invoice_date, 0, 7);
                $byMonth[$month] ??= $blank($month);
                $byMonth[$month]['vat_deductible'] = round($byMonth[$month]['vat_deductible'] + (float) $purchase->vat_amount, 2);
            }
        }

        // TVA nette par mois : collectée − déductible.
        foreach ($byMonth as $month => $row) {
            $byMonth[$month]['vat_net'] = round($row['tva'] - $row['vat_deductible'], 2);
        }

        ksort($byMonth);
        $rows = array_values($byMonth);

        return [
            'rows' => $rows,
            'totals' => [
                'ht' => round(array_sum(array_column($rows, 'ht')), 2),
                'tva' => round(array_sum(array_column($rows, 'tva')), 2),
                'ttc' => round(array_sum(array_column($rows, 'ttc')), 2),
                'vat_deductible' => round(array_sum(array_column($rows, 'vat_deductible')), 2),
                'vat_net' => round(array_sum(array_column($rows, 'vat_net')), 2),
            ],
        ];
    }

    /**
     * Journal des achats (cahier IBIG §10.1) : une ligne par facture d'achat
     * fournisseur de la période, triée par date puis pièce. Renvoie une liste
     * vide si le module achats n'est pas installé (guard Schema::hasTable).
     */
    public function purchasesJournal(Company $company, CarbonInterface $from, CarbonInterface $to): array
    {
        $empty = ['lines' => [], 'totals' => ['ht' => 0.0, 'tva' => 0.0, 'ttc' => 0.0]];

        if (! Schema::hasTable('supplier_invoices')) {
            return $empty;
        }

        $invoices = DB::table('supplier_invoices as si')
            ->leftJoin('suppliers as s', 's.id', '=', 'si.supplier_id')
            ->where('si.company_id', $company->id)
            ->whereNull('si.deleted_at')
            ->whereBetween('si.invoice_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('si.invoice_date')
            ->orderBy('si.number')
            ->get([
                'si.invoice_date', 'si.number', 'si.category', 'si.status',
                'si.amount_ht', 'si.vat_amount', 'si.amount_ttc', 's.name as supplier_name',
            ]);

        $lines = $invoices->map(fn ($invoice) => [
            'date' => substr((string) $invoice->invoice_date, 0, 10),
            'piece' => $invoice->number,
            'fournisseur' => $invoice->supplier_name ?? '—',
            'categorie' => SupplierInvoice::CATEGORIES[$invoice->category] ?? $invoice->category,
            'ht' => round((float) $invoice->amount_ht, 2),
            'tva' => round((float) $invoice->vat_amount, 2),
            'ttc' => round((float) $invoice->amount_ttc, 2),
            'statut' => $invoice->status,
        ])->values();

        return [
            'lines' => $lines->all(),
            'totals' => [
                'ht' => round($lines->sum('ht'), 2),
                'tva' => round($lines->sum('tva'), 2),
                'ttc' => round($lines->sum('ttc'), 2),
            ],
        ];
    }

    /**
     * Compte de résultat simplifié : CA HT (ventes signées) − charges
     * (dépenses approuvées/remboursées si le module dépenses est installé).
     */
    public function profitAndLoss(Company $company, CarbonInterface $from, CarbonInterface $to): array
    {
        $documents = $this->billableQuery($company)
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->get(['type', 'subtotal', 'discount_amount']);

        $revenue = round($documents->sum(
            fn (Document $document) => self::sign($document->type) * ((float) $document->subtotal - (float) $document->discount_amount)
        ), 2);

        // Charges = notes de frais approuvées/remboursées (module dépenses)
        // + achats fournisseurs comptabilisés en HT sur leur date de facture
        // (comptabilité d'engagement : la charge naît à réception de la facture,
        // indépendamment de son règlement). Chaque source est gardée par un
        // Schema::hasTable pour rester optionnelle.
        $expensesAvailable = Schema::hasTable('expenses');
        $purchasesAvailable = Schema::hasTable('supplier_invoices');
        $expenses = 0.0;

        if ($expensesAvailable) {
            $expenses += round((float) DB::table('expenses')
                ->where('company_id', $company->id)
                ->whereNull('deleted_at')
                ->whereIn('status', ['approved', 'reimbursed'])
                ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
                ->sum('amount'), 2);
        }

        if ($purchasesAvailable) {
            $expenses += round((float) DB::table('supplier_invoices')
                ->where('company_id', $company->id)
                ->whereNull('deleted_at')
                ->whereBetween('invoice_date', [$from->toDateString(), $to->toDateString()])
                ->sum('amount_ht'), 2);
        }

        $expenses = round($expenses, 2);
        $result = round($revenue - $expenses, 2);

        return [
            'revenue' => $revenue,
            'expenses' => $expenses,
            'expenses_available' => $expensesAvailable || $purchasesAvailable,
            'result' => $result,
            'margin' => $revenue != 0.0 ? round($result / $revenue * 100, 1) : null,
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentPayment;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Rapports & analytiques (cahier des charges §3 RPT).
 *
 * Conventions :
 *  - "réalisé" = documents FINALISÉS (finalized_at non null) hors annulés ;
 *  - famille facturable : invoice, deposit_invoice, balance_invoice, pos_ticket
 *    comptés positivement, credit_note (avoir) déduit (signe négatif).
 */
class ReportService
{
    /** Types comptés positivement dans le chiffre d'affaires. */
    public const REVENUE_TYPES = ['invoice', 'deposit_invoice', 'balance_invoice', 'pos_ticket'];

    /** Famille facturable complète (avoirs inclus, en négatif). */
    public const BILLABLE_TYPES = [...self::REVENUE_TYPES, 'credit_note'];

    /** Libellés FR des moyens de paiement. */
    public const METHOD_LABELS = [
        'cash' => 'Espèces',
        'mobile_money' => 'Mobile Money',
        'card' => 'Carte bancaire',
        'bank_transfer' => 'Virement',
        'cheque' => 'Chèque',
        'credit' => 'Crédit client',
    ];

    /** Expression SQL du total TTC signé (avoirs en négatif). */
    private function signedTotal(): string
    {
        return "CASE WHEN documents.type = 'credit_note' THEN -documents.total ELSE documents.total END";
    }

    /** Documents facturables finalisés (hors annulés) d'une société sur la période. */
    private function billable(Company $company, CarbonInterface $from, CarbonInterface $to): Builder
    {
        return Document::query()
            ->where('documents.company_id', $company->id)
            ->whereNotNull('documents.finalized_at')
            ->where('documents.status', '!=', 'cancelled')
            ->whereIn('documents.type', self::BILLABLE_TYPES)
            ->whereBetween('documents.issue_date', [$from->toDateString(), $to->toDateString()]);
    }

    /** CA TTC signé par mois de la période + total. */
    public function revenueByMonth(Company $company, CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = $this->billable($company, $from, $to)
            ->get(['documents.issue_date', 'documents.type', 'documents.total']);

        $byMonth = $rows->groupBy(fn (Document $d) => $d->issue_date->format('Y-m'))
            ->map(fn ($docs) => round($docs->sum(
                fn (Document $d) => ($d->type === 'credit_note' ? -1 : 1) * (float) $d->total
            ), 2));

        $months = [];
        $cursor = $from->copy()->startOfMonth();
        $end = $to->copy()->startOfMonth();
        while ($cursor->lessThanOrEqualTo($end)) {
            $key = $cursor->format('Y-m');
            $months[] = [
                'key' => $key,
                'label' => $cursor->translatedFormat('M Y'),
                'total' => (float) ($byMonth[$key] ?? 0),
            ];
            $cursor->addMonth();
        }

        return [
            'months' => $months,
            'total' => round(array_sum(array_column($months, 'total')), 2),
        ];
    }

    /** Top clients : CA TTC signé, nb documents, encours (total - payé) — trié CA desc. */
    public function topCustomers(Company $company, CarbonInterface $from, CarbonInterface $to, int $limit = 10): array
    {
        $signed = $this->signedTotal();

        return $this->billable($company, $from, $to)
            ->whereNotNull('documents.customer_id')
            ->join('customers', 'customers.id', '=', 'documents.customer_id')
            ->groupBy('documents.customer_id', 'customers.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get([
                'documents.customer_id',
                'customers.name',
                DB::raw("SUM({$signed}) as revenue"),
                DB::raw('COUNT(*) as documents_count'),
                DB::raw("SUM(CASE WHEN documents.type = 'credit_note' THEN -(documents.total - documents.amount_paid) ELSE documents.total - documents.amount_paid END) as outstanding"),
            ])
            ->map(fn ($row) => [
                'customer_id' => (int) $row->customer_id,
                'name' => $row->name,
                'revenue' => round((float) $row->revenue, 2),
                'documents_count' => (int) $row->documents_count,
                'outstanding' => round((float) $row->outstanding, 2),
            ])
            ->values()
            ->all();
    }

    /** Top produits (lignes des documents facturables finalisés) : quantité + CA HT signés. */
    public function topProducts(Company $company, CarbonInterface $from, CarbonInterface $to, int $limit = 10): array
    {
        return DB::table('document_lines')
            ->join('documents', 'documents.id', '=', 'document_lines.document_id')
            ->join('products', 'products.id', '=', 'document_lines.product_id')
            ->where('documents.company_id', $company->id)
            ->whereNotNull('documents.finalized_at')
            ->where('documents.status', '!=', 'cancelled')
            ->whereIn('documents.type', self::BILLABLE_TYPES)
            ->whereNull('documents.deleted_at')
            ->whereBetween('documents.issue_date', [$from->toDateString(), $to->toDateString()])
            ->whereNotNull('document_lines.product_id')
            ->groupBy('document_lines.product_id', 'products.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get([
                'document_lines.product_id',
                'products.name',
                DB::raw("SUM(CASE WHEN documents.type = 'credit_note' THEN -document_lines.quantity ELSE document_lines.quantity END) as quantity"),
                DB::raw("SUM(CASE WHEN documents.type = 'credit_note' THEN -document_lines.line_total ELSE document_lines.line_total END) as revenue"),
            ])
            ->map(fn ($row) => [
                'product_id' => (int) $row->product_id,
                'name' => $row->name,
                'quantity' => round((float) $row->quantity, 2),
                'revenue' => round((float) $row->revenue, 2),
            ])
            ->values()
            ->all();
    }

    /** Ventes par type de document (tous types finalisés de la période) : count + total. */
    public function salesByType(Company $company, CarbonInterface $from, CarbonInterface $to): array
    {
        return Document::query()
            ->where('company_id', $company->id)
            ->whereNotNull('finalized_at')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('type')
            ->orderByDesc(DB::raw('SUM(total)'))
            ->get([
                'type',
                DB::raw('COUNT(*) as documents_count'),
                DB::raw('SUM(total) as total'),
            ])
            ->map(fn ($row) => [
                'type' => $row->type,
                'label' => Document::TYPES[$row->type]['label'] ?? $row->type,
                'documents_count' => (int) $row->documents_count,
                'total' => round((float) $row->total, 2),
            ])
            ->values()
            ->all();
    }

    /** Conversion des devis finalisés : total, convertis, taux %, délai moyen (jours). */
    public function quoteConversion(Company $company, CarbonInterface $from, CarbonInterface $to): array
    {
        $quotes = Document::query()
            ->where('company_id', $company->id)
            ->where('type', 'quote')
            ->whereNotNull('finalized_at')
            ->where('status', '!=', 'cancelled')
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->get(['id', 'issue_date', 'status']);

        $total = $quotes->count();
        $convertedQuotes = $quotes->whereIn('status', ['converted', 'accepted']);
        $converted = $convertedQuotes->count();

        // Délai moyen : issue_date du devis → created_at de la 1re facture enfant (parent_id)
        $avgDays = null;
        if ($converted > 0) {
            $children = Document::query()
                ->whereIn('parent_id', $convertedQuotes->pluck('id'))
                ->where('type', 'invoice')
                ->orderBy('created_at')
                ->get(['id', 'parent_id', 'created_at'])
                ->unique('parent_id')
                ->keyBy('parent_id');

            $delays = $convertedQuotes
                ->map(function (Document $quote) use ($children) {
                    $child = $children->get($quote->id);

                    return $child ? $quote->issue_date->diffInDays($child->created_at) : null;
                })
                ->filter(fn ($d) => $d !== null);

            if ($delays->isNotEmpty()) {
                $avgDays = round($delays->avg(), 1);
            }
        }

        return [
            'total' => $total,
            'converted' => $converted,
            'rate' => $total > 0 ? round($converted / $total * 100, 1) : 0.0,
            'avg_days' => $avgDays,
        ];
    }

    /** Encaissements par moyen de paiement (libellés FR) : count + montant. */
    public function paymentsByMethod(Company $company, CarbonInterface $from, CarbonInterface $to): array
    {
        return DocumentPayment::query()
            ->where('company_id', $company->id)
            ->whereBetween('paid_at', [$from->toDateString(), $to->toDateString()])
            ->groupBy('method')
            ->orderByDesc(DB::raw('SUM(amount)'))
            ->get([
                'method',
                DB::raw('COUNT(*) as payments_count'),
                DB::raw('SUM(amount) as amount'),
            ])
            ->map(fn ($row) => [
                'method' => $row->method,
                'label' => self::METHOD_LABELS[$row->method] ?? $row->method,
                'payments_count' => (int) $row->payments_count,
                'amount' => round((float) $row->amount, 2),
            ])
            ->values()
            ->all();
    }

    /** KPIs : CA, encaissé, encours global, panier moyen, nouveaux clients. */
    public function kpis(Company $company, CarbonInterface $from, CarbonInterface $to): array
    {
        $signed = $this->signedTotal();

        $revenue = (float) $this->billable($company, $from, $to)
            ->selectRaw("COALESCE(SUM({$signed}), 0) as revenue")
            ->value('revenue');

        // Panier moyen = CA signé / nb documents "positifs" (les avoirs ne sont pas des paniers)
        $billableCount = (int) $this->billable($company, $from, $to)
            ->whereIn('documents.type', self::REVENUE_TYPES)
            ->count();

        $collected = (float) DocumentPayment::query()
            ->where('company_id', $company->id)
            ->whereBetween('paid_at', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');

        // Encours global (toutes périodes) : reste à payer des documents de vente finalisés
        $outstanding = (float) Document::query()
            ->where('company_id', $company->id)
            ->whereNotNull('finalized_at')
            ->where('status', '!=', 'cancelled')
            ->whereIn('type', self::REVENUE_TYPES)
            ->selectRaw('COALESCE(SUM(total - amount_paid), 0) as due')
            ->value('due');

        $newCustomers = Customer::query()
            ->where('company_id', $company->id)
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->count();

        return [
            'revenue' => round($revenue, 2),
            'collected' => round($collected, 2),
            'outstanding' => round($outstanding, 2),
            'average_basket' => $billableCount > 0 ? round($revenue / $billableCount, 2) : 0.0,
            'new_customers' => $newCustomers,
        ];
    }
}

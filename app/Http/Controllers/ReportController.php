<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentPayment;
use App\Models\Product;
use App\Services\LicenseService;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /** Libellés FR des statuts (exports). */
    private const STATUS_LABELS = [
        'draft' => 'Brouillon', 'sent' => 'Envoyé', 'viewed' => 'Vu',
        'accepted' => 'Accepté', 'rejected' => 'Refusé', 'partial' => 'Partiel',
        'paid' => 'Payé', 'overdue' => 'En retard', 'cancelled' => 'Annulé',
        'converted' => 'Converti',
    ];

    public function __construct(
        private readonly ReportService $reports,
        private readonly LicenseService $licenses,
    ) {}

    /** Page Rapports & analytiques (cahier §3 RPT). */
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;
        [$from, $to] = $this->period($request);

        return Inertia::render('Reports/Index', [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'canExport' => $this->canExport($request),
            'currency' => $company->currency,
            'kpis' => $this->reports->kpis($company, $from, $to),
            'revenueByMonth' => $this->reports->revenueByMonth($company, $from, $to),
            'topCustomers' => $this->reports->topCustomers($company, $from, $to),
            'topProducts' => $this->reports->topProducts($company, $from, $to),
            'salesByType' => $this->reports->salesByType($company, $from, $to),
            'quoteConversion' => $this->reports->quoteConversion($company, $from, $to),
            'paymentsByMethod' => $this->reports->paymentsByMethod($company, $from, $to),
        ]);
    }

    /** Export CSV (PRO+ uniquement — cahier §22.1 "Export Excel / CSV"). */
    public function export(Request $request, string $dataset): StreamedResponse
    {
        abort_unless($this->canExport($request), 403, 'Les exports sont disponibles à partir du forfait PRO.');

        $company = $request->user()->currentCompany;
        [$from, $to] = $this->period($request);

        [$headers, $rows] = match ($dataset) {
            'documents' => $this->documentsDataset($company->id, $from, $to),
            'customers' => $this->customersDataset($company->id),
            'products' => $this->productsDataset($company->id),
            'payments' => $this->paymentsDataset($company->id, $from, $to),
            default => abort(404),
        };

        $filename = 'factpro_'.$dataset.'_'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($headers, $rows) {
            echo "\xEF\xBB\xBF"; // BOM UTF-8 (compatibilité Excel)
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** Période demandée (?from&to) — défaut : 12 derniers mois. */
    private function period(Request $request): array
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->subMonths(11)->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfMonth();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    /** Les exports CSV sont réservés aux forfaits ≠ starter (l'essai = fonctionnalités PRO). */
    private function canExport(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null && $license->plan?->code !== 'starter';
    }

    /** Montant au format FR (virgule décimale, sans séparateur de milliers). */
    private function amount(float|string|null $value): string
    {
        return number_format((float) $value, 2, ',', '');
    }

    private function documentsDataset(int $companyId, Carbon $from, Carbon $to): array
    {
        $headers = ['Type', 'Numéro', 'Client', 'Date', 'Échéance', 'Statut', 'HT', 'TVA', 'TTC', 'Payé', 'Reste', 'Devise', 'Finalisé'];

        $rows = Document::query()
            ->where('company_id', $companyId)
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->with('customer:id,name')
            ->orderBy('issue_date')
            ->orderBy('id')
            ->get()
            ->map(fn (Document $d) => [
                Document::TYPES[$d->type]['label'] ?? $d->type,
                $d->number,
                $d->customer?->name ?? '',
                $d->issue_date?->format('d/m/Y') ?? '',
                $d->due_date?->format('d/m/Y') ?? '',
                self::STATUS_LABELS[$d->status] ?? $d->status,
                $this->amount($d->subtotal),
                $this->amount($d->tax_amount),
                $this->amount($d->total),
                $this->amount($d->amount_paid),
                $this->amount((float) $d->total - (float) $d->amount_paid),
                $d->currency,
                $d->isFinalized() ? 'Oui' : 'Non',
            ]);

        return [$headers, $rows];
    }

    private function customersDataset(int $companyId): array
    {
        $headers = ['Nom', 'Type', 'Email', 'Téléphone', 'Ville', 'Pays', 'N° fiscal', 'Documents', 'CA TTC', 'Encours'];

        $signed = "CASE WHEN documents.type = 'credit_note' THEN -documents.total ELSE documents.total END";
        $stats = Document::query()
            ->where('documents.company_id', $companyId)
            ->whereNotNull('documents.finalized_at')
            ->where('documents.status', '!=', 'cancelled')
            ->whereIn('documents.type', ReportService::BILLABLE_TYPES)
            ->whereNotNull('documents.customer_id')
            ->groupBy('documents.customer_id')
            ->get([
                'documents.customer_id',
                DB::raw('COUNT(*) as documents_count'),
                DB::raw("SUM({$signed}) as revenue"),
                DB::raw("SUM(CASE WHEN documents.type = 'credit_note' THEN -(documents.total - documents.amount_paid) ELSE documents.total - documents.amount_paid END) as outstanding"),
            ])
            ->keyBy('customer_id');

        $rows = Customer::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get()
            ->map(function (Customer $c) use ($stats) {
                $stat = $stats->get($c->id);

                return [
                    $c->name,
                    $c->type === 'individual' ? 'Particulier' : 'Entreprise',
                    $c->email ?? '',
                    $c->phone ?? '',
                    $c->city ?? '',
                    $c->country ?? '',
                    $c->tax_id ?? '',
                    (int) ($stat->documents_count ?? 0),
                    $this->amount($stat->revenue ?? 0),
                    $this->amount($stat->outstanding ?? 0),
                ];
            });

        return [$headers, $rows];
    }

    private function productsDataset(int $companyId): array
    {
        $headers = ['Nom', 'SKU', 'Code-barres', 'Type', 'Unité', 'Prix', 'Coût', 'TVA %', 'Stock', 'Suivi stock', 'Actif'];

        $rows = Product::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => [
                $p->name,
                $p->sku ?? '',
                $p->barcode ?? '',
                $p->type === 'service' ? 'Service' : 'Produit',
                $p->unit,
                $this->amount($p->price),
                $this->amount($p->cost),
                $this->amount($p->tax_rate),
                $this->amount($p->stock_quantity),
                $p->track_stock ? 'Oui' : 'Non',
                $p->is_active ? 'Oui' : 'Non',
            ]);

        return [$headers, $rows];
    }

    private function paymentsDataset(int $companyId, Carbon $from, Carbon $to): array
    {
        $headers = ['Date', 'Facture', 'Client', 'Moyen', 'Référence', 'Montant', 'Devise'];

        $rows = DocumentPayment::query()
            ->where('company_id', $companyId)
            ->whereBetween('paid_at', [$from->toDateString(), $to->toDateString()])
            ->with(['document:id,number,customer_id', 'document.customer:id,name'])
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get()
            ->map(fn (DocumentPayment $p) => [
                $p->paid_at?->format('d/m/Y') ?? '',
                $p->document?->number ?? '',
                $p->document?->customer?->name ?? '',
                ReportService::METHOD_LABELS[$p->method] ?? $p->method,
                $p->reference ?? '',
                $this->amount($p->amount),
                $p->currency,
            ]);

        return [$headers, $rows];
    }
}

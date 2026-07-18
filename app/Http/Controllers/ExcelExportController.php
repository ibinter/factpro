<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Product;
use App\Services\AccountingService;
use App\Services\ExcelExportService;
use App\Services\FecExportService;
use App\Services\LicenseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exports Excel (.xlsx) — Phase 12 IBIG FactPro.
 * Middleware : auth + license (définis dans les routes).
 * Les exports détaillés (clients, produits, documents, FEC) sont réservés
 * aux forfaits BUSINESS+.
 */
class ExcelExportController extends Controller
{
    public function __construct(
        private readonly ExcelExportService $excel,
        private readonly LicenseService $licenses,
        private readonly FecExportService $fec,
    ) {}

    /** Export clients en .xlsx */
    public function customers(Request $request): StreamedResponse
    {
        $this->authorizeExport($request);

        $company   = $request->user()->currentCompany;
        $customers = Customer::query()
            ->where('company_id', $company->id)
            ->withCount('documents')
            ->orderBy('name')
            ->get();

        $spreadsheet = $this->excel->exportCustomers($customers, $company->name);
        $filename    = 'factpro_clients_'.now()->format('Y-m-d').'.xlsx';

        return $this->excel->download($spreadsheet, $filename);
    }

    /** Export produits en .xlsx */
    public function products(Request $request): StreamedResponse
    {
        $this->authorizeExport($request);

        $company  = $request->user()->currentCompany;
        $products = Product::query()
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get();

        $spreadsheet = $this->excel->exportProducts($products, $company->name);
        $filename    = 'factpro_produits_'.now()->format('Y-m-d').'.xlsx';

        return $this->excel->download($spreadsheet, $filename);
    }

    /** Export documents en .xlsx (filtrable par type et date) */
    public function documents(Request $request): StreamedResponse
    {
        $this->authorizeExport($request);

        $request->validate([
            'type' => ['nullable', 'string'],
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date'],
        ]);

        $company = $request->user()->currentCompany;
        [$from, $to] = $this->period($request);
        $type = $request->input('type', 'all');

        $query = Document::query()
            ->where('company_id', $company->id)
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->with('customer:id,name')
            ->orderBy('issue_date')
            ->orderBy('id');

        if ($type !== 'all' && array_key_exists($type, Document::TYPES)) {
            $query->where('type', $type);
        }

        $documents   = $query->get();
        $spreadsheet = $this->excel->exportDocuments($documents, $company->name, $type);
        $filename    = 'factpro_documents_'.now()->format('Y-m-d').'.xlsx';

        return $this->excel->download($spreadsheet, $filename);
    }

    /** Rapport CA mensuel en .xlsx */
    public function monthlyRevenue(Request $request): StreamedResponse
    {
        $this->authorizeExport($request);

        $request->validate(['year' => ['nullable', 'integer', 'min:2000', 'max:2100']]);

        $company = $request->user()->currentCompany;
        $year    = (int) $request->input('year', now()->year);

        // Construction des données mensuelles
        $monthlyData = [];
        $monthLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        $monthExpr = $driver === 'sqlite'
            ? "CAST(strftime('%m', issue_date) AS INTEGER)"
            : 'MONTH(issue_date)';

        $rows = Document::query()
            ->where('company_id', $company->id)
            ->whereIn('type', AccountingService::BILLABLE_TYPES)
            ->whereNotNull('finalized_at')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('issue_date', ["{$year}-01-01", "{$year}-12-31"])
            ->selectRaw("{$monthExpr} as month")
            ->selectRaw('SUM(subtotal) as subtotal')
            ->selectRaw('SUM(tax_amount) as tax_amount')
            ->selectRaw('SUM(total) as total')
            ->selectRaw('COUNT(*) as invoices_count')
            ->selectRaw('COUNT(DISTINCT customer_id) as customers_count')
            ->groupByRaw($monthExpr)
            ->get()
            ->keyBy('month');

        for ($m = 1; $m <= 12; $m++) {
            $row = $rows->get($m);
            $monthlyData[] = [
                'label'           => $monthLabels[$m - 1],
                'subtotal'        => $row?->subtotal ?? 0,
                'tax_amount'      => $row?->tax_amount ?? 0,
                'total'           => $row?->total ?? 0,
                'invoices_count'  => $row?->invoices_count ?? 0,
                'customers_count' => $row?->customers_count ?? 0,
            ];
        }

        $spreadsheet = $this->excel->exportMonthlyRevenue($monthlyData, $company->name, $year);
        $filename    = 'factpro_ca_mensuel_'.$year.'.xlsx';

        return $this->excel->download($spreadsheet, $filename);
    }

    /** FEC en .xlsx (en plus du .txt existant) */
    public function fec(Request $request): StreamedResponse
    {
        $this->authorizeExport($request);

        $request->validate(['year' => ['nullable', 'integer', 'min:2000', 'max:2100']]);

        $company = $request->user()->currentCompany;
        $year    = (int) $request->input('year', now()->year);

        // Récupère les lignes FEC depuis FecExportService (sous forme d'array)
        $rawContent = $this->fec->generate($company, $year);

        // Parse les lignes tabulées (format : tab séparateur, saut CRLF)
        $lines   = explode("\r\n", $rawContent);
        $header  = count($lines) > 0 ? str_getcsv($lines[0], "\t") : FecExportService::HEADER;
        $entries = collect();

        foreach (array_slice($lines, 1) as $line) {
            if (trim($line) === '') {
                continue;
            }
            $values = str_getcsv($line, "\t");
            $entry  = [];
            foreach ($header as $i => $col) {
                $entry[$col] = $values[$i] ?? '';
            }
            $entries->push($entry);
        }

        $spreadsheet = $this->excel->exportFecXlsx($entries, $company->name);
        $filename    = 'factpro_fec_'.$year.'.xlsx';

        return $this->excel->download($spreadsheet, $filename);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /** Vérifie que l'utilisateur a un forfait BUSINESS ou ENTERPRISE. */
    private function authorizeExport(Request $request): void
    {
        $license = $this->licenses->currentFor($request->user());

        abort_unless(
            $license !== null && in_array($license->plan?->code, ['business', 'enterprise'], true),
            403,
            'Les exports Excel sont disponibles à partir du forfait BUSINESS.'
        );
    }

    private function period(Request $request): array
    {
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
}

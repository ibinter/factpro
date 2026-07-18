<?php

namespace App\Http\Controllers;

use App\Services\AccountingService;
use App\Services\FecExportService;
use App\Services\LicenseService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Comptabilité simplifiée (cahier IBIG §10) — journal des ventes, balance
 * âgée, récap TVA, compte de résultat + exports FEC/CSV.
 * Réservé BUSINESS/ENTERPRISE (§22.1).
 */
class AccountingController extends Controller
{
    /** Plans autorisés à accéder au module comptabilité. */
    private const ALLOWED_PLANS = ['business', 'enterprise'];

    private const TABS = ['journal', 'purchases', 'aged', 'vat', 'pnl'];

    public function __construct(
        private LicenseService $licenses,
        private AccountingService $accounting,
        private FecExportService $fec,
    ) {
    }

    /** Le forfait courant donne-t-il accès à la comptabilité ? */
    private function hasAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && in_array($license->plan?->code, self::ALLOWED_PLANS, true);
    }

    /** Période demandée [from, to] — défaut : année civile en cours. */
    private function period(Request $request): array
    {
        $request->validate([
            'from' => ['sometimes', 'nullable', 'date'],
            'to' => ['sometimes', 'nullable', 'date'],
        ]);

        $from = $request->filled('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->startOfYear();
        $to = $request->filled('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfYear();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    /** Dashboard comptabilité (onglets journal | aged | vat | pnl). */
    public function index(Request $request): Response
    {
        $hasAccess = $this->hasAccess($request);

        $tab = (string) $request->query('tab', 'journal');
        if (! in_array($tab, self::TABS, true)) {
            $tab = 'journal';
        }

        [$from, $to] = $this->period($request);
        $company = $request->user()->currentCompany;

        $data = null;
        if ($hasAccess && $company) {
            $data = match ($tab) {
                'journal' => $this->accounting->salesJournal($company, $from, $to),
                'purchases' => $this->accounting->purchasesJournal($company, $from, $to),
                'aged' => $this->accounting->agedBalance($company),
                'vat' => $this->accounting->vatSummary($company, $from, $to),
                'pnl' => $this->accounting->profitAndLoss($company, $from, $to),
            };
        }

        return Inertia::render('Accounting/Index', [
            'hasAccess' => $hasAccess,
            'tab' => $tab,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'data' => $data,
            'currency' => $company?->currency ?? 'XOF',
            'taxRate' => (float) ($company?->default_tax_rate ?? 18),
            'fecYear' => $to->year,
        ]);
    }

    /** Export FEC (Fichier des Écritures Comptables) de l'exercice. */
    public function fecExport(Request $request): StreamedResponse
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'La comptabilité est réservée aux forfaits BUSINESS et ENTERPRISE.'
        );

        $request->validate([
            'year' => ['sometimes', 'nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        $year = (int) ($request->query('year') ?: now()->year);
        $company = $request->user()->currentCompany;
        abort_unless($company !== null, 404);

        $content = $this->fec->generate($company, $year);
        $filename = $this->fec->filename($company, $year);

        return response()->streamDownload(
            fn () => print $content,
            $filename,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }

    /** Export CSV du journal des ventes (séparateur ; UTF-8 BOM — Excel FR). */
    public function journalCsv(Request $request): StreamedResponse
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'La comptabilité est réservée aux forfaits BUSINESS et ENTERPRISE.'
        );

        [$from, $to] = $this->period($request);
        $company = $request->user()->currentCompany;
        abort_unless($company !== null, 404);

        $journal = $this->accounting->salesJournal($company, $from, $to);

        $csv = "\xEF\xBB\xBF"; // BOM UTF-8 pour Excel FR
        $csv .= "Date;Pièce;Client;Type;Statut;HT;TVA;TTC\r\n";

        $format = fn (float $value): string => number_format($value, 2, ',', '');
        $escape = fn (string $value): string => '"'.str_replace('"', '""', $value).'"';

        foreach ($journal['lines'] as $line) {
            $csv .= implode(';', [
                $line['date'],
                $escape($line['piece']),
                $escape($line['client']),
                $escape($line['type_label']),
                $line['status'],
                $format($line['ht']),
                $format($line['tva']),
                $format($line['ttc']),
            ])."\r\n";
        }

        $csv .= implode(';', [
            'TOTAL', '', '', '', '',
            $format($journal['totals']['ht']),
            $format($journal['totals']['tva']),
            $format($journal['totals']['ttc']),
        ])."\r\n";

        $filename = 'journal-ventes_'.$from->toDateString().'_'.$to->toDateString().'.csv';

        return response()->streamDownload(
            fn () => print $csv,
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\AccountingExportService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exports comptables tiers : Sage 100, QuickBooks IIF, Pennylane JSON.
 * Phase 14 — IBIG FactPro.
 */
class AccountingExportController extends Controller
{
    public function __construct(private AccountingExportService $exportService) {}

    // -----------------------------------------------------------------------
    // Index — formulaire Vue
    // -----------------------------------------------------------------------

    public function index(Request $request): InertiaResponse
    {
        $now  = now();
        $from = $request->input('from', $now->copy()->startOfMonth()->toDateString());
        $to   = $request->input('to', $now->toDateString());

        return Inertia::render('Accounting/Export', [
            'from'    => $from,
            'to'      => $to,
            'formats' => [
                ['value' => 'sage',       'label' => 'Sage 100 (CSV/TXT)'],
                ['value' => 'quickbooks', 'label' => 'QuickBooks (IIF)'],
                ['value' => 'pennylane',  'label' => 'Pennylane (JSON)'],
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // Téléchargements
    // -----------------------------------------------------------------------

    public function exportSage(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ]);

        $companyId = $request->user()->currentCompany->id;
        $from      = Carbon::parse($request->input('from'))->startOfDay();
        $to        = Carbon::parse($request->input('to'))->endOfDay();
        $content   = $this->exportService->exportSage($companyId, $from, $to);
        $filename  = 'sage_export_'.$from->format('Ymd').'_'.$to->format('Ymd').'.txt';

        return response()->streamDownload(
            fn () => print($content),
            $filename,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }

    public function exportQuickBooks(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ]);

        $companyId = $request->user()->currentCompany->id;
        $from      = Carbon::parse($request->input('from'))->startOfDay();
        $to        = Carbon::parse($request->input('to'))->endOfDay();
        $content   = $this->exportService->exportQuickBooks($companyId, $from, $to);
        $filename  = 'quickbooks_export_'.$from->format('Ymd').'_'.$to->format('Ymd').'.iif';

        return response()->streamDownload(
            fn () => print($content),
            $filename,
            ['Content-Type' => 'text/plain; charset=UTF-8'],
        );
    }

    public function exportPennylane(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => ['required', 'date'],
            'to'   => ['required', 'date', 'after_or_equal:from'],
        ]);

        $companyId = $request->user()->currentCompany->id;
        $from      = Carbon::parse($request->input('from'))->startOfDay();
        $to        = Carbon::parse($request->input('to'))->endOfDay();
        $data      = $this->exportService->exportPennylane($companyId, $from, $to);
        $content   = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename  = 'pennylane_export_'.$from->format('Ymd').'_'.$to->format('Ymd').'.json';

        return response()->streamDownload(
            fn () => print($content),
            $filename,
            ['Content-Type' => 'application/json; charset=UTF-8'],
        );
    }

    // -----------------------------------------------------------------------
    // Aperçu (10 premières lignes)
    // -----------------------------------------------------------------------

    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'from'   => ['required', 'date'],
            'to'     => ['required', 'date', 'after_or_equal:from'],
            'format' => ['required', 'in:sage,quickbooks,pennylane'],
        ]);

        $companyId = $request->user()->currentCompany->id;
        $from      = Carbon::parse($request->input('from'))->startOfDay();
        $to        = Carbon::parse($request->input('to'))->endOfDay();
        $format    = $request->input('format');

        $lines = $this->exportService->preview($companyId, $from, $to, $format);

        return response()->json(['lines' => $lines]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\AbcAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AbcAnalysisController extends Controller
{
    public function __construct(private AbcAnalysisService $abc) {}

    /** Page d'analyse ABC (Inertia). */
    public function index(Request $request): Response
    {
        $months = (int) $request->query('months', 12);
        $months = in_array($months, [3, 6, 12, 24]) ? $months : 12;

        $result = $this->abc->analyze($request->user()->current_company_id, $months);

        return Inertia::render('Stock/AbcAnalysis', [
            'analysis' => $result,
            'months' => $months,
        ]);
    }

    /** API JSON pour refresh sans rechargement. */
    public function data(Request $request): JsonResponse
    {
        $months = (int) $request->query('months', 12);
        $months = in_array($months, [3, 6, 12, 24]) ? $months : 12;

        $result = $this->abc->analyze($request->user()->current_company_id, $months);

        return response()->json($result);
    }

    /** Export CSV de l'analyse ABC. */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $months = (int) $request->query('months', 12);
        $months = in_array($months, [3, 6, 12, 24]) ? $months : 12;

        $result = $this->abc->analyze($request->user()->current_company_id, $months);

        $filename = 'analyse-abc-'.$months.'mois-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($result) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 pour Excel
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Classe', 'Produit', 'SKU', 'CA Période', '% CA', '% Cumulé', 'Stock', 'Recommandations'], ';');

            foreach ($result['products'] as $p) {
                fputcsv($handle, [
                    $p['class'],
                    $p['product_name'],
                    $p['product_sku'] ?? '',
                    number_format($p['revenue'], 2, ',', ' '),
                    number_format($p['revenue_pct'], 2, ',', ' ').' %',
                    number_format($p['cumulative_pct'], 2, ',', ' ').' %',
                    number_format($p['stock_quantity'], 2, ',', ' '),
                    implode(' | ', $p['recommendations']),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

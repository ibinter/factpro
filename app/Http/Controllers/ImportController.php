<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImportJob;
use App\Services\ImportService;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class ImportController extends Controller
{
    private const ALLOWED_PLANS = ['business', 'enterprise'];

    public function __construct(
        private readonly ImportService $importer,
        private readonly LicenseService $licenses,
    ) {}

    // -------------------------------------------------------------------------
    // Guard
    // -------------------------------------------------------------------------

    private function hasBusinessPlan(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());
        return $license !== null
            && in_array($license->plan?->code, self::ALLOWED_PLANS, true);
    }

    private function guardPlan(Request $request): ?JsonResponse
    {
        if (! $this->hasBusinessPlan($request)) {
            return response()->json(
                ['error' => "L'import CSV est réservé au forfait BUSINESS+."],
                403
            );
        }
        return null;
    }

    // -------------------------------------------------------------------------
    // Pages
    // -------------------------------------------------------------------------

    public function index(Request $request): \Inertia\Response
    {
        return Inertia::render('Import/Index', [
            'hasBusiness' => $this->hasBusinessPlan($request),
        ]);
    }

    // -------------------------------------------------------------------------
    // Customers
    // -------------------------------------------------------------------------

    public function uploadCustomers(Request $request): JsonResponse
    {
        if ($guard = $this->guardPlan($request)) {
            return $guard;
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path   = $request->file('file')->store('imports/tmp');
        $parsed = $this->importer->parseCsv(storage_path("app/{$path}"));

        // Retourner prévisualisation (20 premières lignes)
        return response()->json([
            'headers'  => $parsed['headers'],
            'preview'  => array_slice($parsed['rows'], 0, 20),
            'total'    => count($parsed['rows']),
            'errors'   => $parsed['errors'],
            'tmp_path' => $path,
        ]);
    }

    public function importCustomers(Request $request): JsonResponse
    {
        if ($guard = $this->guardPlan($request)) {
            return $guard;
        }

        $request->validate([
            'tmp_path'   => 'required|string',
            'column_map' => 'required|array',
        ]);

        $fullPath  = storage_path('app/' . $request->input('tmp_path'));
        $parsed    = $this->importer->parseCsv($fullPath);
        $columnMap = $request->input('column_map');
        $company   = $request->user()->currentCompany;

        if (count($parsed['rows']) > 100) {
            ProcessImportJob::dispatch($company, 'customers', $parsed['rows'], $columnMap);
            return response()->json([
                'queued'  => true,
                'message' => 'Import en cours de traitement en arrière-plan.',
            ]);
        }

        $result = $this->importer->importCustomers($company, $parsed['rows'], $columnMap);

        return response()->json($result);
    }

    // -------------------------------------------------------------------------
    // Products
    // -------------------------------------------------------------------------

    public function uploadProducts(Request $request): JsonResponse
    {
        if ($guard = $this->guardPlan($request)) {
            return $guard;
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path   = $request->file('file')->store('imports/tmp');
        $parsed = $this->importer->parseCsv(storage_path("app/{$path}"));

        return response()->json([
            'headers'  => $parsed['headers'],
            'preview'  => array_slice($parsed['rows'], 0, 20),
            'total'    => count($parsed['rows']),
            'errors'   => $parsed['errors'],
            'tmp_path' => $path,
        ]);
    }

    public function importProducts(Request $request): JsonResponse
    {
        if ($guard = $this->guardPlan($request)) {
            return $guard;
        }

        $request->validate([
            'tmp_path'   => 'required|string',
            'column_map' => 'required|array',
        ]);

        $fullPath  = storage_path('app/' . $request->input('tmp_path'));
        $parsed    = $this->importer->parseCsv($fullPath);
        $columnMap = $request->input('column_map');
        $company   = $request->user()->currentCompany;

        if (count($parsed['rows']) > 100) {
            ProcessImportJob::dispatch($company, 'products', $parsed['rows'], $columnMap);
            return response()->json([
                'queued'  => true,
                'message' => 'Import en cours de traitement en arrière-plan.',
            ]);
        }

        $result = $this->importer->importProducts($company, $parsed['rows'], $columnMap);

        return response()->json($result);
    }

    // -------------------------------------------------------------------------
    // Templates
    // -------------------------------------------------------------------------

    public function downloadCustomerTemplate(): Response
    {
        return response($this->importer->customerCsvTemplate(), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_clients.csv"',
        ]);
    }

    public function downloadProductTemplate(): Response
    {
        return response($this->importer->productCsvTemplate(), 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_produits.csv"',
        ]);
    }
}

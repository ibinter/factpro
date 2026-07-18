<?php

namespace App\Http\Controllers;

use App\Services\OssVatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OssVatController extends Controller
{
    public function __construct(private OssVatService $service) {}

    /**
     * Display the OSS VAT declaration form.
     */
    public function index(): Response
    {
        return Inertia::render('TaxConfig/OssDeclaration', [
            'euCountries' => array_keys(OssVatService::EU_VAT_RATES),
            'vatRates'    => OssVatService::EU_VAT_RATES,
        ]);
    }

    /**
     * Calculate OSS declaration for a given quarter/year.
     */
    public function declaration(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quarter'    => ['required', 'integer', 'between:1,4'],
            'year'       => ['required', 'integer', 'min:2020', 'max:2099'],
            'company_id' => ['sometimes', 'integer'],
        ]);

        $companyId = $validated['company_id']
            ?? $request->user()?->currentCompany?->id;

        if (! $companyId) {
            return response()->json(['error' => 'Company not found'], 422);
        }

        $result = $this->service->calculateOssDeclaration(
            (int) $companyId,
            (int) $validated['quarter'],
            (int) $validated['year'],
        );

        $result['below_threshold'] = $this->service->isBelowThreshold(
            (int) $companyId,
            (int) $validated['year'],
        );

        return response()->json($result);
    }

    /**
     * Validate an EU VAT number.
     */
    public function validateVatNumber(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vat_number'   => ['required', 'string', 'max:20'],
            'country_code' => ['required', 'string', 'size:2'],
        ]);

        $valid = $this->service->validateVatNumber(
            $validated['vat_number'],
            $validated['country_code'],
        );

        return response()->json([
            'valid'        => $valid,
            'vat_number'   => strtoupper($validated['vat_number']),
            'country_code' => strtoupper($validated['country_code']),
        ]);
    }
}

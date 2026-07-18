<?php

namespace App\Http\Controllers;

use App\Models\TaxConfig;
use App\Services\TaxDeclarationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TaxConfigController extends Controller
{
    public function __construct(private TaxDeclarationService $taxService) {}

    public function index(Request $request): InertiaResponse
    {
        $company = $request->user()->currentCompany;
        $taxConfig = TaxConfig::where('company_id', $company->id)->first();

        $from = Carbon::now()->startOfMonth();
        $to = Carbon::now()->endOfMonth();

        $vatSummary = $this->taxService->vatSummary($company, $from, $to);

        return Inertia::render('TaxConfig/Index', [
            'taxConfig' => $taxConfig,
            'regimes' => TaxConfig::REGIMES,
            'vatSummary' => $vatSummary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tax_regime' => 'required|string|max:30',
            'country' => 'required|string|max:2',
            'tva_rates' => 'required|array',
            'tva_rates.*.rate' => 'required|numeric|min:0|max:100',
            'tva_rates.*.label' => 'required|string|max:50',
            'has_tps' => 'boolean',
            'tps_rate' => 'numeric|min:0|max:100',
            'has_oca' => 'boolean',
            'oca_rate' => 'numeric|min:0|max:100',
            'has_timbre' => 'boolean',
            'timbre_amount' => 'numeric|min:0',
            'declaration_frequency' => 'in:monthly,quarterly',
        ]);

        $company = $request->user()->currentCompany;
        $validated['company_id'] = $company->id;

        TaxConfig::create($validated);

        return redirect()->route('tax-config.index')->with('success', 'Configuration fiscale créée.');
    }

    public function update(Request $request, TaxConfig $taxConfig): RedirectResponse
    {
        $company = $request->user()->currentCompany;
        abort_if($taxConfig->company_id !== $company->id, 403);

        $validated = $request->validate([
            'tax_regime' => 'required|string|max:30',
            'country' => 'required|string|max:2',
            'tva_rates' => 'required|array',
            'tva_rates.*.rate' => 'required|numeric|min:0|max:100',
            'tva_rates.*.label' => 'required|string|max:50',
            'has_tps' => 'boolean',
            'tps_rate' => 'numeric|min:0|max:100',
            'has_oca' => 'boolean',
            'oca_rate' => 'numeric|min:0|max:100',
            'has_timbre' => 'boolean',
            'timbre_amount' => 'numeric|min:0',
            'declaration_frequency' => 'in:monthly,quarterly',
        ]);

        $taxConfig->update($validated);

        return redirect()->route('tax-config.index')->with('success', 'Configuration fiscale mise à jour.');
    }

    public function export(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $from = $request->get('from')
            ? Carbon::parse($request->get('from'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->get('to')
            ? Carbon::parse($request->get('to'))->endOfDay()
            : Carbon::now()->endOfMonth();

        $csv = $this->taxService->exportCsv($company, $from, $to);
        $filename = 'declaration-tva-'.$from->format('Y-m').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Returns default field values for a given regime (used by frontend).
     */
    public function applyRegime(string $regime): array
    {
        return TaxConfig::defaultsForRegime($regime);
    }

    /** Vue Sénégal déclaration mensuelle DGID */
    public function senegalDeclaration(Request $request): InertiaResponse
    {
        $company = $request->user()->currentCompany;
        $month   = (int) $request->get('month', now()->month);
        $year    = (int) $request->get('year', now()->year);

        $declaration = $this->taxService->generateSenegalDeclaration($company->id, $month, $year);

        return Inertia::render('TaxConfig/SenegalDeclaration', [
            'declaration' => $declaration,
            'month'       => $month,
            'year'        => $year,
        ]);
    }

    /** Vue Algérie déclaration G50 mensuelle */
    public function algerieDeclaration(Request $request): InertiaResponse
    {
        $company = $request->user()->currentCompany;
        $month   = (int) $request->get('month', now()->month);
        $year    = (int) $request->get('year', now()->year);

        $declaration = $this->taxService->generateAlgerieDeclaration($company->id, $month, $year);

        return Inertia::render('TaxConfig/AlgerieDeclaration', [
            'declaration' => $declaration,
            'month'       => $month,
            'year'        => $year,
        ]);
    }

    /** API JSON — déclaration Sénégal */
    public function apiSenegalDeclaration(Request $request): \Illuminate\Http\JsonResponse
    {
        $company = $request->user()->currentCompany;
        $month   = (int) $request->get('month', now()->month);
        $year    = (int) $request->get('year', now()->year);

        return response()->json(
            $this->taxService->generateSenegalDeclaration($company->id, $month, $year)
        );
    }

    /** API JSON — déclaration Côte d'Ivoire trimestrielle */
    public function apiCoteIvoireDeclaration(Request $request): \Illuminate\Http\JsonResponse
    {
        $company = $request->user()->currentCompany;
        $quarter = (int) $request->get('quarter', (int) ceil(now()->month / 3));
        $year    = (int) $request->get('year', now()->year);

        return response()->json(
            $this->taxService->generateCoteIvoireDeclaration($company->id, $quarter, $year)
        );
    }

    /** API JSON — déclaration Algérie G50 */
    public function apiAlgerieDeclaration(Request $request): \Illuminate\Http\JsonResponse
    {
        $company = $request->user()->currentCompany;
        $month   = (int) $request->get('month', now()->month);
        $year    = (int) $request->get('year', now()->year);

        return response()->json(
            $this->taxService->generateAlgerieDeclaration($company->id, $month, $year)
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SalesTarget;
use App\Services\ForecastingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ForecastingController extends Controller
{
    public function __construct(private ForecastingService $service) {}

    public function dashboard(Request $request): InertiaResponse
    {
        $company = $request->user()->currentCompany;

        return Inertia::render('Forecasting/Dashboard', [
            'forecast' => $this->service->forecastCurrentMonth($company->id),
            'comparison' => $this->service->compareWithTarget($company->id),
            'history' => $this->service->getMonthlyRevenue($company->id, 12),
            'underperformance' => $this->service->checkUnderperformance($company->id),
            'accuracy' => $this->service->forecastAccuracy($company->id, 6),
        ]);
    }

    public function storeTarget(Request $request): JsonResponse
    {
        $data = $request->validate([
            'period_type' => 'required|in:month,quarter,year',
            'period_month' => 'nullable|integer|between:1,12',
            'period_year' => 'required|integer|min:2020|max:2100',
            'target_amount' => 'required|numeric|min:0',
            'target_invoices' => 'nullable|integer|min:0',
            'target_customers' => 'nullable|integer|min:0',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string|max:500',
            'assigned_to_id' => 'nullable|exists:users,id',
        ]);

        $company = $request->user()->currentCompany;

        $target = SalesTarget::updateOrCreate(
            [
                'company_id' => $company->id,
                'period_type' => $data['period_type'],
                'period_year' => $data['period_year'],
                'period_month' => $data['period_month'] ?? null,
                'assigned_to_id' => $data['assigned_to_id'] ?? null,
            ],
            array_merge($data, [
                'company_id' => $company->id,
                'currency' => $data['currency'] ?? 'XOF',
            ])
        );

        return response()->json(['target' => $target, 'message' => 'Objectif enregistré.'], 201);
    }

    public function forecast(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany;

        return response()->json($this->service->forecastCurrentMonth($company->id));
    }

    public function comparison(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany;
        $userId = $request->integer('user_id') ?: null;

        return response()->json($this->service->compareWithTarget($company->id, $userId));
    }

    public function underperformance(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany;

        return response()->json($this->service->checkUnderperformance($company->id));
    }

    public function history(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany;
        $months = min(24, max(1, (int) ($request->input('months', 12))));

        $history = $this->service->getMonthlyRevenue($company->id, $months);

        // Enrichir avec les objectifs
        $targets = SalesTarget::where('company_id', $company->id)
            ->where('period_type', 'month')
            ->whereNull('assigned_to_id')
            ->get()
            ->keyBy(fn ($t) => $t->period_year.'-'.str_pad($t->period_month, 2, '0', STR_PAD_LEFT));

        $history = array_map(function ($row) use ($targets) {
            $key = $row['month'];
            $row['target'] = $targets->has($key) ? (float) $targets[$key]->target_amount : null;

            return $row;
        }, $history);

        return response()->json([
            'history' => $history,
            'accuracy' => $this->service->forecastAccuracy($company->id, 6),
        ]);
    }

    public function exportReport(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $forecast = $this->service->forecastCurrentMonth($company->id);
        $comparison = $this->service->compareWithTarget($company->id);
        $history = $this->service->getMonthlyRevenue($company->id, 12);
        $underperformance = $this->service->checkUnderperformance($company->id);

        $pdf = Pdf::loadView('pdf.forecasting-report', compact(
            'company',
            'forecast',
            'comparison',
            'history',
            'underperformance'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('rapport-direction-'.now()->format('Y-m').'.pdf');
    }
}

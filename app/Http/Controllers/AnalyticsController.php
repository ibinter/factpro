<?php

namespace App\Http\Controllers;

use App\Models\DashboardWidget;
use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Expense;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public const AVAILABLE_WIDGETS = [
        'kpi_summary'    => ['label' => 'KPIs Résumé',           'icon' => 'chart-bar',    'defaultWidth' => 4, 'defaultHeight' => 1],
        'revenue_chart'  => ['label' => 'Chiffre d\'affaires',   'icon' => 'trending-up',  'defaultWidth' => 3, 'defaultHeight' => 2],
        'cashflow'       => ['label' => 'Trésorerie',            'icon' => 'arrow-path',   'defaultWidth' => 3, 'defaultHeight' => 2],
        'invoice_status' => ['label' => 'Statut des factures',   'icon' => 'document',     'defaultWidth' => 2, 'defaultHeight' => 2],
        'top_clients'    => ['label' => 'Top clients',           'icon' => 'users',        'defaultWidth' => 2, 'defaultHeight' => 2],
        'top_products'   => ['label' => 'Top produits',          'icon' => 'shopping-bag', 'defaultWidth' => 2, 'defaultHeight' => 2],
        'recovery_rate'  => ['label' => 'Taux de recouvrement',  'icon' => 'currency',     'defaultWidth' => 2, 'defaultHeight' => 1],
        'stock_alert'    => ['label' => 'Alertes stock',         'icon' => 'exclamation',  'defaultWidth' => 2, 'defaultHeight' => 2],
    ];

    // -------------------------------------------------------------------------
    // Pages
    // -------------------------------------------------------------------------

    public function dashboard(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $widgets = DashboardWidget::where('company_id', $company->id)
            ->where(function ($q) use ($request) {
                $q->whereNull('user_id')->orWhere('user_id', $request->user()->id);
            })
            ->where('is_visible', true)
            ->orderBy('position_y')
            ->orderBy('position_x')
            ->get();

        return Inertia::render('Analytics/Dashboard', [
            'widgets'          => $widgets,
            'availableWidgets' => self::AVAILABLE_WIDGETS,
        ]);
    }

    // -------------------------------------------------------------------------
    // CRUD Widgets
    // -------------------------------------------------------------------------

    public function widgets(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany;

        $widgets = DashboardWidget::where('company_id', $company->id)
            ->where(function ($q) use ($request) {
                $q->whereNull('user_id')->orWhere('user_id', $request->user()->id);
            })
            ->where('is_visible', true)
            ->orderBy('position_y')
            ->orderBy('position_x')
            ->get();

        return response()->json($widgets);
    }

    public function saveWidgets(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany;
        $items   = $request->validate(['widgets' => 'required|array', 'widgets.*.id' => 'required|integer']);

        foreach ($items['widgets'] as $item) {
            DashboardWidget::where('id', $item['id'])
                ->where('company_id', $company->id)
                ->update([
                    'position_x' => $item['position_x'] ?? 0,
                    'position_y' => $item['position_y'] ?? 0,
                    'width'      => $item['width']      ?? 1,
                    'height'     => $item['height']     ?? 1,
                    'config'     => $item['config']     ?? null,
                ]);
        }

        return response()->json(['success' => true]);
    }

    public function addWidget(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'widget_type' => 'required|string|in:' . implode(',', array_keys(self::AVAILABLE_WIDGETS)),
            'position_x'  => 'integer|min:0',
            'position_y'  => 'integer|min:0',
            'width'       => 'integer|min:1|max:4',
            'height'      => 'integer|min:1|max:3',
            'config'      => 'nullable|array',
        ]);

        $defaults = self::AVAILABLE_WIDGETS[$data['widget_type']];

        $widget = DashboardWidget::create([
            'company_id'  => $company->id,
            'user_id'     => $request->user()->id,
            'widget_type' => $data['widget_type'],
            'position_x'  => $data['position_x']  ?? 0,
            'position_y'  => $data['position_y']  ?? 0,
            'width'       => $data['width']        ?? $defaults['defaultWidth'],
            'height'      => $data['height']       ?? $defaults['defaultHeight'],
            'config'      => $data['config']       ?? null,
            'is_visible'  => true,
        ]);

        return response()->json($widget, 201);
    }

    public function removeWidget(Request $request, DashboardWidget $widget): JsonResponse
    {
        $company = $request->user()->currentCompany;

        abort_unless($widget->company_id === $company->id, 403);

        $widget->delete();

        return response()->json(['success' => true]);
    }

    // -------------------------------------------------------------------------
    // Data endpoints
    // -------------------------------------------------------------------------

    public function data(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany;
        $type    = $request->get('type', 'kpi_summary');
        $period  = $request->get('period', '30d');
        $compare = filter_var($request->get('compare', false), FILTER_VALIDATE_BOOLEAN);
        $days    = $this->periodDays($period);
        $from    = $this->dateFrom($days);

        $data = match ($type) {
            'revenue_chart'  => $this->revenueChart($company->id, $from, $days, $compare),
            'top_clients'    => $this->topClients($company->id, $from),
            'top_products'   => $this->topProducts($company->id, $from),
            'recovery_rate'  => $this->recoveryRate($company->id, $from),
            'invoice_status' => $this->invoiceStatus($company->id, $from),
            'cashflow'       => $this->cashflow($company->id),
            'kpi_summary'    => $this->kpiSummary($company->id),
            'stock_alert'    => $this->stockAlerts($company->id),
            default          => [],
        };

        return response()->json($data);
    }

    // -------------------------------------------------------------------------
    // AI Insights
    // -------------------------------------------------------------------------

    public function aiInsights(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany;
        $kpis    = $this->kpiSummary($company->id);
        $rate    = $this->recoveryRate($company->id, $this->dateFrom(30));

        $summary = sprintf(
            "Entreprise: %s\n" .
            "CA mois courant: %s\n" .
            "CA mois précédent: %s\n" .
            "Croissance: %s%%\n" .
            "Nombre de factures (30j): %s\n" .
            "Panier moyen: %s\n" .
            "Taux de recouvrement: %s%%\n" .
            "Montant en retard: %s",
            $company->name ?? 'N/A',
            number_format($kpis['current_month_revenue'] ?? 0, 0, ',', ' '),
            number_format($kpis['prev_month_revenue'] ?? 0, 0, ',', ' '),
            number_format($kpis['growth_pct'] ?? 0, 1),
            $kpis['invoice_count'] ?? 0,
            number_format($kpis['avg_invoice_value'] ?? 0, 0, ',', ' '),
            number_format($rate['rate'] ?? 0, 1),
            number_format($rate['overdue_amount'] ?? 0, 0, ',', ' ')
        );

        $apiKey = config('services.anthropic.key');

        if ($apiKey) {
            try {
                $response = Http::withHeaders([
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])->post('https://api.anthropic.com/v1/messages', [
                    'model'      => 'claude-haiku-4-5',
                    'max_tokens' => 512,
                    'system'     => 'Tu es un analyste financier expert. Réponds en français. Sois concis et actionnable.',
                    'messages'   => [[
                        'role'    => 'user',
                        'content' => $summary . "\n\nDonne 3-5 insights clés sur la santé financière de cette entreprise, en bullet points. Détecte les anomalies.",
                    ]],
                ]);

                if ($response->successful()) {
                    $text     = $response->json('content.0.text', '');
                    $insights = array_filter(array_map('trim', explode("\n", $text)));

                    return response()->json(['insights' => array_values($insights)]);
                }
            } catch (\Throwable $e) {
                // Fallback ci-dessous
            }
        }

        // Fallback : insights basiques
        $insights = $this->basicInsights($kpis, $rate);

        return response()->json(['insights' => $insights]);
    }

    // -------------------------------------------------------------------------
    // Export PDF
    // -------------------------------------------------------------------------

    public function exportReport(Request $request)
    {
        $company  = $request->user()->currentCompany;
        $period   = $request->get('period', '30d');
        $days     = $this->periodDays($period);
        $from     = $this->dateFrom($days);

        $kpis       = $this->kpiSummary($company->id);
        $topClients = $this->topClients($company->id, $from);
        $topProducts= $this->topProducts($company->id, $from);
        $recovery   = $this->recoveryRate($company->id, $from);

        $pdf = Pdf::loadView('analytics.report', compact(
            'company', 'kpis', 'topClients', 'topProducts', 'recovery', 'period', 'from'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("rapport-analytique-{$period}.pdf");
    }

    // -------------------------------------------------------------------------
    // Private helpers — data builders
    // -------------------------------------------------------------------------

    private function revenueChart(int $companyId, Carbon $from, int $days, bool $compare): array
    {
        $current = Document::where('company_id', $companyId)
            ->where('type', 'invoice')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->where('issue_date', '>=', $from)
            ->selectRaw('DATE(issue_date) as day, SUM(total) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $labels  = [];
        $curr    = [];
        $prev    = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date     = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::now()->subDays($i)->format('d/m');
            $curr[]   = (float) ($current[$date] ?? 0);
        }

        if ($compare) {
            $prevFrom = (clone $from)->subDays($days);
            $previous = Document::where('company_id', $companyId)
                ->where('type', 'invoice')
                ->whereNotIn('status', ['draft', 'cancelled'])
                ->where('issue_date', '>=', $prevFrom)
                ->where('issue_date', '<', $from)
                ->selectRaw('DATE(issue_date) as day, SUM(total) as total')
                ->groupBy('day')
                ->orderBy('day')
                ->pluck('total', 'day');

            for ($i = $days - 1; $i >= 0; $i--) {
                $date   = (clone $from)->subDays($days - ($days - 1 - $i))->format('Y-m-d');
                $prev[] = (float) ($previous[$date] ?? 0);
            }
        }

        return ['labels' => $labels, 'current' => $curr, 'previous' => $prev];
    }

    private function topClients(int $companyId, Carbon $from): array
    {
        $rows = Document::where('company_id', $companyId)
            ->where('type', 'invoice')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->where('issue_date', '>=', $from)
            ->whereNotNull('customer_id')
            ->selectRaw('customer_id, customer_name, SUM(total) as total')
            ->groupBy('customer_id', 'customer_name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'labels' => $rows->pluck('customer_name')->toArray(),
            'values' => $rows->pluck('total')->map(fn ($v) => (float) $v)->toArray(),
            'ids'    => $rows->pluck('customer_id')->toArray(),
        ];
    }

    private function topProducts(int $companyId, Carbon $from): array
    {
        $rows = DocumentLine::whereHas('document', fn ($q) => $q
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->where('issue_date', '>=', $from))
            ->selectRaw('description, SUM(quantity * unit_price) as total')
            ->groupBy('description')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'labels' => $rows->pluck('description')->toArray(),
            'values' => $rows->pluck('total')->map(fn ($v) => (float) $v)->toArray(),
        ];
    }

    private function recoveryRate(int $companyId, Carbon $from): array
    {
        $base      = Document::where('company_id', $companyId)
            ->where('type', 'invoice')
            ->where('issue_date', '>=', $from);

        $total     = (clone $base)->count();
        $paid      = (clone $base)->where('status', 'paid')->count();
        $overdue   = (float) (clone $base)
            ->whereIn('status', ['sent', 'viewed', 'partial', 'overdue'])
            ->selectRaw('COALESCE(SUM(total - amount_paid), 0) as due')
            ->value('due');

        $rate = $total > 0 ? round($paid / $total * 100, 1) : 0;

        return [
            'rate'           => $rate,
            'paid'           => $paid,
            'total'          => $total,
            'overdue_amount' => $overdue,
        ];
    }

    private function invoiceStatus(int $companyId, Carbon $from): array
    {
        $rows = Document::where('company_id', $companyId)
            ->where('type', 'invoice')
            ->where('issue_date', '>=', $from)
            ->selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $colorMap = [
            'draft'     => '#94a3b8',
            'sent'      => '#3b82f6',
            'viewed'    => '#8b5cf6',
            'paid'      => '#22c55e',
            'partial'   => '#f59e0b',
            'overdue'   => '#ef4444',
            'cancelled' => '#6b7280',
        ];

        $labels = [];
        $values = [];
        $colors = [];

        foreach ($rows as $status => $cnt) {
            $labels[] = $status;
            $values[] = (int) $cnt;
            $colors[] = $colorMap[$status] ?? '#94a3b8';
        }

        return compact('labels', 'values', 'colors');
    }

    private function cashflow(int $companyId): array
    {
        $labels   = [];
        $inflows  = [];
        $outflows = [];

        for ($i = 11; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd   = Carbon::now()->subMonths($i)->endOfMonth();

            $labels[] = $monthStart->format('M Y');

            $inflow = Document::where('company_id', $companyId)
                ->where('type', 'invoice')
                ->where('status', 'paid')
                ->whereBetween('issue_date', [$monthStart, $monthEnd])
                ->sum('total');

            $inflows[] = (float) $inflow;

            // Dépenses si table existe
            try {
                $outflow = Expense::where('company_id', $companyId)
                    ->whereBetween('expense_date', [$monthStart, $monthEnd])
                    ->sum('amount');
            } catch (\Throwable) {
                $outflow = 0;
            }

            $outflows[] = (float) $outflow;
        }

        return compact('labels', 'inflows', 'outflows');
    }

    private function kpiSummary(int $companyId): array
    {
        $monthStart     = Carbon::now()->startOfMonth();
        $prevMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $prevMonthEnd   = Carbon::now()->subMonth()->endOfMonth();

        $base = fn () => Document::where('company_id', $companyId)
            ->where('type', 'invoice')
            ->whereNotIn('status', ['draft', 'cancelled']);

        $currRevenue = (float) ($base)()
            ->where('issue_date', '>=', $monthStart)
            ->sum('total');

        $prevRevenue = (float) ($base)()
            ->whereBetween('issue_date', [$prevMonthStart, $prevMonthEnd])
            ->sum('total');

        $growth = $prevRevenue > 0
            ? round(($currRevenue - $prevRevenue) / $prevRevenue * 100, 1)
            : 0;

        $invoiceCount = ($base)()
            ->where('issue_date', '>=', $monthStart)
            ->count();

        $avgValue = $invoiceCount > 0 ? $currRevenue / $invoiceCount : 0;

        return [
            'current_month_revenue' => $currRevenue,
            'prev_month_revenue'    => $prevRevenue,
            'growth_pct'            => $growth,
            'invoice_count'         => $invoiceCount,
            'avg_invoice_value'     => $avgValue,
        ];
    }

    private function stockAlerts(int $companyId): array
    {
        try {
            $alerts = DB::table('products')
                ->where('company_id', $companyId)
                ->whereColumn('stock_quantity', '<=', 'min_stock')
                ->select('id', 'name', 'stock_quantity', 'min_stock')
                ->orderBy('stock_quantity')
                ->limit(10)
                ->get();

            return ['alerts' => $alerts->toArray(), 'count' => $alerts->count()];
        } catch (\Throwable) {
            return ['alerts' => [], 'count' => 0];
        }
    }

    private function basicInsights(array $kpis, array $rate): array
    {
        $insights = [];

        $growth = $kpis['growth_pct'] ?? 0;
        if ($growth > 10) {
            $insights[] = "• Croissance positive : CA en hausse de {$growth}% par rapport au mois précédent.";
        } elseif ($growth < -10) {
            $insights[] = "• Attention : CA en baisse de " . abs($growth) . "% par rapport au mois précédent.";
        } else {
            $insights[] = "• CA stable : variation de {$growth}% par rapport au mois précédent.";
        }

        $recoveryRate = $rate['rate'] ?? 0;
        if ($recoveryRate < 60) {
            $insights[] = "• Taux de recouvrement faible ({$recoveryRate}%) : relancer les clients en retard.";
        } elseif ($recoveryRate >= 85) {
            $insights[] = "• Excellent taux de recouvrement ({$recoveryRate}%) : vos clients paient bien.";
        } else {
            $insights[] = "• Taux de recouvrement à améliorer ({$recoveryRate}%).";
        }

        $overdue = $rate['overdue_amount'] ?? 0;
        if ($overdue > 0) {
            $insights[] = "• Montant en souffrance : " . number_format($overdue, 0, ',', ' ') . " FCFA à recouvrer.";
        }

        $avg = $kpis['avg_invoice_value'] ?? 0;
        if ($avg > 0) {
            $insights[] = "• Panier moyen : " . number_format($avg, 0, ',', ' ') . " FCFA par facture.";
        }

        return $insights;
    }

    // -------------------------------------------------------------------------
    // Period helpers
    // -------------------------------------------------------------------------

    private function periodDays(string $period): int
    {
        return match ($period) {
            '7d'   => 7,
            '30d'  => 30,
            '90d'  => 90,
            '365d' => 365,
            default => 30,
        };
    }

    private function dateFrom(int $days): Carbon
    {
        return Carbon::now()->subDays($days)->startOfDay();
    }
}

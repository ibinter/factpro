<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Document;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $company = $request->user()->currentCompany;
        $user = $request->user();

        $base = Document::where('company_id', $company->id);

        $invoices = (clone $base)->where('type', 'invoice');
        $monthStart = now()->startOfMonth();

        $prevMonthStart = now()->subMonth()->startOfMonth();
        $prevMonthEnd   = now()->subMonth()->endOfMonth();

        $revenueMonth = (float) (clone $invoices)->where('issue_date', '>=', $monthStart)
            ->whereNotIn('status', ['cancelled', 'draft'])->sum('total');
        $revenuePrev  = (float) (clone $invoices)
            ->whereBetween('issue_date', [$prevMonthStart, $prevMonthEnd])
            ->whereNotIn('status', ['cancelled', 'draft'])->sum('total');

        $invoicesMonth = (clone $invoices)->where('issue_date', '>=', $monthStart)->count();
        $invoicesPrev  = (clone $invoices)->whereBetween('issue_date', [$prevMonthStart, $prevMonthEnd])->count();

        $stats = [
            'revenue_month'      => $revenueMonth,
            'revenue_prev'       => $revenuePrev,
            'revenue_trend'      => $revenuePrev > 0 ? round((($revenueMonth - $revenuePrev) / $revenuePrev) * 100, 1) : null,
            'outstanding'        => (float) (clone $invoices)->whereIn('status', ['sent', 'partial', 'overdue', 'viewed'])
                ->selectRaw('COALESCE(SUM(total - amount_paid), 0) as due')->value('due'),
            'invoices_month'     => $invoicesMonth,
            'invoices_prev'      => $invoicesPrev,
            'invoices_trend'     => $invoicesPrev > 0 ? round((($invoicesMonth - $invoicesPrev) / $invoicesPrev) * 100, 1) : null,
            'quotes_pending'     => (clone $base)->where('type', 'quote')
                ->whereIn('status', ['draft', 'sent', 'viewed'])->count(),
            'customers'          => $company->customers()->count(),
            'products'           => $company->products()->count(),
            'paid_count'         => (clone $invoices)->where('status', 'paid')->count(),
            'total_invoices'     => (clone $invoices)->whereNotIn('status', ['draft'])->count(),
        ];

        // Répartition des statuts des documents récents (30 jours)
        $statusBreakdown = (clone $base)
            ->where('issue_date', '>=', now()->subDays(30))
            ->whereNotIn('type', ['pos_ticket'])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $recentDocuments = (clone $base)
            ->with('customer:id,name')
            ->latest()
            ->take(8)
            ->get(['id', 'type', 'number', 'status', 'customer_id', 'issue_date', 'total', 'currency']);

        // CA des 6 derniers mois pour le graphique (ancien)
        $chart = collect(range(5, 0))->map(function ($i) use ($invoices) {
            $month = now()->subMonths($i);

            return [
                'month' => $month->translatedFormat('M Y'),
                'total' => (float) (clone $invoices)
                    ->whereYear('issue_date', $month->year)
                    ->whereMonth('issue_date', $month->month)
                    ->whereNotIn('status', ['cancelled', 'draft'])
                    ->sum('total'),
            ];
        })->values();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'statusBreakdown' => $statusBreakdown,
            'recentDocuments' => $recentDocuments,
            'chart' => $chart,
            'monthlyRevenue' => CacheService::rememberForCompany(
                $company->id,
                'monthly_revenue',
                CacheService::TTL_DASHBOARD,
                fn () => $this->monthlyRevenue($company)
            ),
            'topCustomers' => CacheService::rememberForCompany(
                $company->id,
                'top_customers',
                CacheService::TTL_DASHBOARD,
                fn () => $this->topCustomers($company)
            ),
            'topProducts' => CacheService::rememberForCompany(
                $company->id,
                'top_products',
                CacheService::TTL_DASHBOARD,
                fn () => $this->topProducts($company)
            ),
            'alerts' => CacheService::rememberForCompany(
                $company->id,
                'active_alerts',
                CacheService::TTL_DASHBOARD,
                fn () => $this->activeAlerts($company, $user)
            ),
            'conversionRate' => $this->quoteConversionRate($company),
        ]);
    }

    private function monthlyRevenue(Company $company): array
    {
        return collect(range(11, 0))->map(function ($i) use ($company) {
            $month = now()->subMonths($i);

            $query = Document::where('company_id', $company->id)
                ->where('type', 'invoice')
                ->whereNotIn('status', ['cancelled', 'draft'])
                ->whereYear('issue_date', $month->year)
                ->whereMonth('issue_date', $month->month);

            return [
                'month' => $month->translatedFormat('M Y'),
                'revenue' => (float) $query->sum('total'),
                'invoices_count' => $query->count(),
            ];
        })->values()->all();
    }

    private function topCustomers(Company $company): array
    {
        $since = now()->subMonths(12);

        return Document::where('documents.company_id', $company->id)
            ->where('documents.type', 'invoice')
            ->whereNotIn('documents.status', ['cancelled', 'draft'])
            ->where('documents.issue_date', '>=', $since)
            ->whereNotNull('documents.customer_id')
            ->join('customers', 'customers.id', '=', 'documents.customer_id')
            ->selectRaw('customers.name, SUM(documents.total) as total, COUNT(documents.id) as invoices_count')
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'name' => $row->name,
                'total' => (float) $row->total,
                'invoices_count' => (int) $row->invoices_count,
            ])
            ->all();
    }

    private function topProducts(Company $company): array
    {
        $since = now()->subMonths(12);

        return \DB::table('document_lines')
            ->join('documents', 'documents.id', '=', 'document_lines.document_id')
            ->where('documents.company_id', $company->id)
            ->where('documents.type', 'invoice')
            ->whereNotIn('documents.status', ['cancelled', 'draft'])
            ->where('documents.issue_date', '>=', $since)
            ->whereNull('documents.deleted_at')
            ->selectRaw('document_lines.description as name, SUM(document_lines.quantity) as quantity, SUM(document_lines.line_total) as revenue')
            ->groupBy('document_lines.description')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get()
            ->map(fn($row) => [
                'name' => $row->name,
                'quantity' => (float) $row->quantity,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }

    private function activeAlerts(Company $company, User $user): array
    {
        $alerts = [];

        // Stock faible
        $lowStock = \App\Models\Product::where('company_id', $company->id)
            ->where('track_stock', true)
            ->whereColumn('stock_quantity', '<=', 'stock_alert_threshold')
            ->where('stock_alert_threshold', '>', 0)
            ->count();

        if ($lowStock > 0) {
            $alerts[] = [
                'type' => 'stock_low',
                'message' => "{$lowStock} produit(s) sous seuil d'alerte de stock",
                'severity' => 'danger',
                'link' => '/products',
            ];
        }

        // Factures en retard >7 jours
        $overdueCount = Document::where('company_id', $company->id)
            ->where('type', 'invoice')
            ->whereIn('status', ['sent', 'partial', 'overdue', 'viewed'])
            ->where('due_date', '<', now()->subDays(7))
            ->count();

        if ($overdueCount > 0) {
            $alerts[] = [
                'type' => 'invoices_overdue',
                'message' => "{$overdueCount} facture(s) en retard de paiement de plus de 7 jours",
                'severity' => 'warning',
                'link' => '/documents?type=invoice&status=overdue',
            ];
        }

        // Licence essai qui expire dans 3 jours
        $license = $user->licenses()
            ->where('status', 'trial')
            ->where('trial_ends_at', '>=', now())
            ->where('trial_ends_at', '<=', now()->addDays(3))
            ->first();

        if ($license) {
            $daysLeft = (int) now()->diffInDays($license->trial_ends_at);
            $alerts[] = [
                'type' => 'trial_ending',
                'message' => "Votre période d'essai expire dans {$daysLeft} jour(s)",
                'severity' => 'info',
                'link' => '/billing',
            ];
        }

        // Grosses factures impayées >500k XOF
        $largeUnpaid = Document::where('company_id', $company->id)
            ->where('type', 'invoice')
            ->whereIn('status', ['sent', 'partial', 'overdue', 'viewed'])
            ->where('total', '>', 500000)
            ->count();

        if ($largeUnpaid > 0) {
            $alerts[] = [
                'type' => 'large_unpaid',
                'message' => "{$largeUnpaid} facture(s) impayée(s) de plus de 500 000 XOF",
                'severity' => 'warning',
                'link' => '/documents?type=invoice&status=sent',
            ];
        }

        return $alerts;
    }

    private function quoteConversionRate(Company $company): float
    {
        $since = now()->subDays(30);

        $total = Document::where('company_id', $company->id)
            ->where('type', 'quote')
            ->where('issue_date', '>=', $since)
            ->count();

        if ($total === 0) {
            return 0.0;
        }

        $converted = Document::where('company_id', $company->id)
            ->where('type', 'quote')
            ->where('status', 'converted')
            ->where('issue_date', '>=', $since)
            ->count();

        return round(($converted / $total) * 100, 1);
    }
}

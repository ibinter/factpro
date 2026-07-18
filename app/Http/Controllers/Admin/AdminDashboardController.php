<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Console Superadmin — tableau de bord financier (script §16.1).
 */
class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $paidOrders = Order::where('status', 'paid')->whereNotNull('paid_at');

        // Chiffre d'affaires jour / mois / année (commandes payées)
        $revenue = [
            'day' => (float) (clone $paidOrders)->where('paid_at', '>=', now()->startOfDay())->sum('total_amount'),
            'month' => (float) (clone $paidOrders)->where('paid_at', '>=', now()->startOfMonth())->sum('total_amount'),
            'year' => (float) (clone $paidOrders)->where('paid_at', '>=', now()->startOfYear())->sum('total_amount'),
        ];

        // MRR simple : Σ price_monthly des plans des licences payantes actives / grâce
        $mrr = (float) License::whereIn('licenses.status', ['active', 'grace_period'])
            ->where('licenses.type', 'paid')
            ->join('plans', 'plans.id', '=', 'licenses.plan_id')
            ->sum('plans.price_monthly');

        // Répartition des licences par statut
        $licensesByStatus = License::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->pluck('total', 'status');

        // Répartition des revenus par forfait (mois en cours + total)
        $planNames = Plan::orderBy('sort_order')->get(['id', 'code', 'name']);
        $revenueRows = Order::where('status', 'paid')
            ->whereNotNull('paid_at')
            ->selectRaw(
                'plan_id, SUM(total_amount) as total, '
                .'SUM(CASE WHEN paid_at >= ? THEN total_amount ELSE 0 END) as month_total',
                [now()->startOfMonth()]
            )
            ->groupBy('plan_id')
            ->get()
            ->keyBy('plan_id');

        $revenueByPlan = $planNames->map(fn (Plan $plan) => [
            'code' => $plan->code,
            'name' => $plan->name,
            'month' => (float) ($revenueRows[$plan->id]->month_total ?? 0),
            'total' => (float) ($revenueRows[$plan->id]->total ?? 0),
        ])->values();

        // Licences expirant sous 7 jours
        $expiringSoon = License::with(['user:id,name,email', 'plan:id,code,name'])
            ->whereIn('status', ['trial', 'provisional', 'active', 'grace_period'])
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), now()->addDays(7)])
            ->orderBy('ends_at')
            ->take(15)
            ->get()
            ->map(fn (License $l) => [
                'id' => $l->id,
                'license_key' => $l->license_key,
                'status' => $l->status,
                'user' => $l->user?->only(['name', 'email']),
                'plan' => $l->plan?->only(['code', 'name']),
                'ends_at' => $l->ends_at?->format('d/m/Y'),
                'days_remaining' => $l->daysRemaining(),
            ]);

        // 10 derniers paiements validés
        $recentPayments = PaymentTransaction::with(['user:id,name,email', 'order.plan:id,code,name'])
            ->whereIn('status', ['manually_validated', 'succeeded'])
            ->orderByDesc('confirmed_at')
            ->orderByDesc('updated_at')
            ->take(10)
            ->get()
            ->map(fn (PaymentTransaction $t) => [
                'id' => $t->id,
                'internal_reference' => $t->internal_reference,
                'amount' => (float) ($t->amount_received ?? $t->amount_expected),
                'currency' => $t->currency,
                'provider' => $t->payment_provider,
                'status' => $t->status,
                'user' => $t->user?->only(['name', 'email']),
                'plan' => $t->order?->plan?->name,
                'confirmed_at' => $t->confirmed_at?->format('d/m/Y H:i'),
            ]);

        // Utilisateurs
        $users = [
            'total' => User::count(),
            'new_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        // CA des 6 derniers mois (graphe barres CSS — pattern Dashboard.vue)
        $chart = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i);

            return [
                'month' => $month->translatedFormat('M Y'),
                'total' => (float) Order::where('status', 'paid')
                    ->whereYear('paid_at', $month->year)
                    ->whereMonth('paid_at', $month->month)
                    ->sum('total_amount'),
            ];
        })->values();

        return Inertia::render('Admin/Dashboard', [
            'revenue' => $revenue,
            'mrr' => $mrr,
            'licensesByStatus' => $licensesByStatus,
            'revenueByPlan' => $revenueByPlan,
            'expiringSoon' => $expiringSoon,
            'recentPayments' => $recentPayments,
            'users' => $users,
            'chart' => $chart,
        ]);
    }

    /**
     * Tableau de bord financier enrichi (FinancialDashboard.vue) — script §16.1 étendu.
     */
    public function financialDashboard(Request $request): Response
    {
        $paidOrders = Order::where('status', 'paid')->whereNotNull('paid_at');

        // KPIs
        $today = now()->startOfDay();
        $startMonth = now()->startOfMonth();
        $startYear = now()->startOfYear();

        $kpis = [
            'revenue_total' => (float) (clone $paidOrders)->sum('total_amount'),
            'revenue_today' => (float) (clone $paidOrders)->where('paid_at', '>=', $today)->sum('total_amount'),
            'payments_today_count' => (clone $paidOrders)->where('paid_at', '>=', $today)->count(),
            'revenue_month' => (float) (clone $paidOrders)->where('paid_at', '>=', $startMonth)->sum('total_amount'),
            'payments_month_count' => (clone $paidOrders)->where('paid_at', '>=', $startMonth)->count(),
            'orders_total' => Order::count(),
            'orders_paid' => Order::where('status', 'paid')->count(),
            'conversion_rate' => Order::count() > 0
                ? round(Order::where('status', 'paid')->count() / Order::count() * 100, 1)
                : 0,
            'pending_validation_count' => PaymentTransaction::whereIn('status', ['pending', 'under_review', 'proof_submitted'])->count(),
            'pending_validation_amount' => (float) PaymentTransaction::whereIn('status', ['pending', 'under_review', 'proof_submitted'])->sum('amount_expected'),
            'licenses_expiring_30d' => License::whereIn('status', ['active', 'trial', 'provisional', 'grace_period'])
                ->whereNotNull('ends_at')
                ->whereBetween('ends_at', [now(), now()->addDays(30)])
                ->count(),
            'licenses_provisional' => License::where('status', 'provisional')->count(),
        ];

        // CA mensuel 12 mois
        $chart12m = collect(range(11, 0))->map(function ($i) {
            $month = now()->subMonths($i);
            return [
                'month' => $month->translatedFormat('M Y'),
                'total' => (float) Order::where('status', 'paid')
                    ->whereYear('paid_at', $month->year)
                    ->whereMonth('paid_at', $month->month)
                    ->sum('total_amount'),
            ];
        })->values();

        // Répartition par méthode de paiement
        $byMethod = PaymentTransaction::where('status', 'manually_validated')
            ->selectRaw('payment_provider, COUNT(*) as count, SUM(amount_received) as total')
            ->groupBy('payment_provider')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'method' => $r->payment_provider,
                'count' => (int) $r->count,
                'total' => (float) $r->total,
            ]);

        // Répartition par pays (top 10)
        $byCountry = Order::where('status', 'paid')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.country, COUNT(*) as count, SUM(orders.total_amount) as total')
            ->groupBy('users.country')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'country' => $r->country ?? 'N/A',
                'count' => (int) $r->count,
                'total' => (float) $r->total,
            ]);

        // Répartition par forfait
        $byPlan = Plan::orderBy('sort_order')->get(['id', 'code', 'name'])->map(function (Plan $plan) use ($startMonth) {
            $orders = Order::where('status', 'paid')->where('plan_id', $plan->id);
            return [
                'plan' => $plan->name,
                'code' => $plan->code,
                'total' => (float) (clone $orders)->sum('total_amount'),
                'month' => (float) (clone $orders)->where('paid_at', '>=', $startMonth)->sum('total_amount'),
                'count' => (clone $orders)->count(),
            ];
        });

        // Revenus par devise
        $byCurrency = PaymentTransaction::where('status', 'manually_validated')
            ->selectRaw('currency, COUNT(*) as count, SUM(amount_received) as total')
            ->groupBy('currency')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['currency' => $r->currency, 'count' => (int) $r->count, 'total' => (float) $r->total]);

        // Top 10 clients par CA
        $topClients = Order::where('status', 'paid')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->selectRaw('users.id, users.name, users.email, COUNT(*) as orders_count, SUM(orders.total_amount) as total')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->name,
                'email' => $r->email,
                'orders_count' => (int) $r->orders_count,
                'total' => (float) $r->total,
            ]);

        return Inertia::render('Admin/FinancialDashboard', [
            'kpis' => $kpis,
            'chart12m' => $chart12m,
            'byMethod' => $byMethod,
            'byCountry' => $byCountry,
            'byPlan' => $byPlan,
            'byCurrency' => $byCurrency,
            'topClients' => $topClients,
        ]);
    }
}

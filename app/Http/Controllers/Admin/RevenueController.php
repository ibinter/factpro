<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RevenueController extends Controller
{
    public function index(Request $request): Response
    {
        $now = now();
        $thisMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // MRR = somme (total_amount / duration_months) des licences actives ayant un order
        $mrr = (float) Order::where('orders.status', 'paid')
            ->join('licenses', 'licenses.order_id', '=', 'orders.id')
            ->where('licenses.status', 'active')
            ->where('licenses.ends_at', '>', $now)
            ->selectRaw('SUM(orders.total_amount / GREATEST(orders.duration_months, 1)) as mrr')
            ->value('mrr') ?? 0;

        $arr = $mrr * 12;

        // Revenus du mois en cours
        $revenueThisMonth = (float) Order::where('status', 'paid')
            ->whereBetween('paid_at', [$thisMonth, $now])
            ->sum('total_amount');

        // Revenus du mois précédent
        $revenueLastMonth = (float) Order::where('status', 'paid')
            ->whereBetween('paid_at', [$lastMonth, $lastMonthEnd])
            ->sum('total_amount');

        // Croissance MoM
        $growth = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 1)
            : 0;

        // Nouveaux clients ce mois
        $newUsersThisMonth = User::whereBetween('created_at', [$thisMonth, $now])->count();

        // Licences actives
        $totalActive = License::where('status', 'active')->where('ends_at', '>', $now)->count();

        // Licences expirées ce mois (churn)
        $churnedThisMonth = License::where('status', 'expired')
            ->whereBetween('ends_at', [$thisMonth, $now])
            ->count();

        $churnRate = ($totalActive + $churnedThisMonth) > 0
            ? round(($churnedThisMonth / ($totalActive + $churnedThisMonth)) * 100, 1)
            : 0;

        // Revenus des 12 derniers mois (courbe)
        $monthly = collect(range(11, 0))->map(function ($i) use ($now) {
            $start = $now->copy()->subMonths($i)->startOfMonth();
            $end   = $now->copy()->subMonths($i)->endOfMonth();
            return [
                'month'   => $start->translatedFormat('M Y'),
                'revenue' => (float) Order::where('status', 'paid')
                    ->whereBetween('paid_at', [$start, $end])
                    ->sum('total_amount'),
                'orders'  => Order::where('status', 'paid')
                    ->whereBetween('paid_at', [$start, $end])
                    ->count(),
            ];
        });

        // Revenus par méthode de paiement
        $byGateway = Order::where('status', 'paid')
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get()
            ->map(fn ($r) => [
                'gateway' => $r->payment_method ?? 'manuel',
                'total'   => (float) $r->total,
                'count'   => (int) $r->count,
            ]);

        // Top plans
        $byPlan = Order::where('orders.status', 'paid')
            ->join('plans', 'orders.plan_id', '=', 'plans.id')
            ->select('plans.name as plan', DB::raw('SUM(orders.total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('plans.name')
            ->get()
            ->map(fn ($r) => [
                'plan'  => $r->plan,
                'total' => (float) $r->total,
                'count' => (int) $r->count,
            ]);

        // Dernières 10 transactions payées
        $recentOrders = Order::with(['user:id,name', 'plan:id,name'])
            ->where('status', 'paid')
            ->latest('paid_at')
            ->limit(10)
            ->get()
            ->map(fn ($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'user'         => $o->user?->name,
                'plan'         => $o->plan?->name,
                'amount'       => (float) $o->total_amount,
                'currency'     => $o->currency,
                'gateway'      => $o->payment_method ?? 'manuel',
                'paid_at'      => $o->paid_at?->toDateTimeString(),
            ]);

        $totalUsers   = User::count();
        $totalRevenue = (float) Order::where('status', 'paid')->sum('total_amount');

        return Inertia::render('Admin/Revenue', [
            'kpis' => [
                'mrr'                => round($mrr),
                'arr'                => round($arr),
                'revenue_this_month' => round($revenueThisMonth),
                'revenue_last_month' => round($revenueLastMonth),
                'growth'             => $growth,
                'total_revenue'      => round($totalRevenue),
                'active_licenses'    => $totalActive,
                'new_users'          => $newUsersThisMonth,
                'churn_rate'         => $churnRate,
                'total_users'        => $totalUsers,
            ],
            'monthly'       => $monthly,
            'by_gateway'    => $byGateway,
            'by_plan'       => $byPlan,
            'recent_orders' => $recentOrders,
        ]);
    }
}

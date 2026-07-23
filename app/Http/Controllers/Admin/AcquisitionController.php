<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AcquisitionController extends Controller
{
    public function index(): Response
    {
        $period = 30;

        // Inscriptions totales
        $totalSignups = User::where('created_at', '>=', now()->subDays($period))->count();

        // Conversions essai→payant (users avec licence non-trial active)
        $conversions = User::whereHas('licenses', fn ($q) =>
            $q->where('type', '!=', 'trial')->where('status', 'active')
        )->where('created_at', '>=', now()->subDays($period))->count();

        $conversionRate = $totalSignups > 0
            ? round(($conversions / $totalSignups) * 100, 1)
            : 0;

        // Par source UTM
        $bySource = User::where('created_at', '>=', now()->subDays($period))
            ->whereNotNull('utm_source')
            ->selectRaw('utm_source, COUNT(*) as count')
            ->groupBy('utm_source')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Par medium
        $byMedium = User::where('created_at', '>=', now()->subDays($period))
            ->whereNotNull('utm_medium')
            ->selectRaw('utm_medium, COUNT(*) as count')
            ->groupBy('utm_medium')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Par campagne
        $byCampaign = User::where('created_at', '>=', now()->subDays($period))
            ->whereNotNull('utm_campaign')
            ->selectRaw('utm_campaign, COUNT(*) as count')
            ->groupBy('utm_campaign')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Inscriptions par jour (30j)
        $signupsByDay = User::where('created_at', '>=', now()->subDays($period))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top référents
        $topReferrers = User::where('created_at', '>=', now()->subDays($period))
            ->whereNotNull('referrer_url')
            ->selectRaw('referrer_url, COUNT(*) as count')
            ->groupBy('referrer_url')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'url'    => $r->referrer_url,
                'count'  => $r->count,
                'domain' => parse_url($r->referrer_url, PHP_URL_HOST) ?? $r->referrer_url,
            ]);

        // Inscriptions directes / sans UTM
        $directCount = User::where('created_at', '>=', now()->subDays($period))
            ->whereNull('utm_source')
            ->count();

        return Inertia::render('Admin/Acquisition', [
            'stats' => [
                'total_signups'   => $totalSignups,
                'conversions'     => $conversions,
                'conversion_rate' => $conversionRate,
                'direct_count'    => $directCount,
                'period_days'     => $period,
            ],
            'by_source'      => $bySource,
            'by_medium'      => $byMedium,
            'by_campaign'    => $byCampaign,
            'signups_by_day' => $signupsByDay,
            'top_referrers'  => $topReferrers,
        ]);
    }
}

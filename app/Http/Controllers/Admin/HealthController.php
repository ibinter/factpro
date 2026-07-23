<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HealthController extends Controller
{
    public function index(): Response
    {
        // Récupère tous les utilisateurs ayant au moins une licence,
        // avec leur licence la plus récente et leur company courante.
        $users = User::with([
                'licenses' => fn ($q) => $q->with('plan')->latest('created_at'),
                'currentCompany',
            ])
            ->whereHas('licenses')
            ->get()
            ->map(function ($user) {
                $license = $user->licenses->first(); // la plus récente
                $company = $user->currentCompany;

                // Nombre de documents créés (30 / 90 derniers jours)
                $docs30 = 0;
                $docs90 = 0;
                $lastDocDate = null;

                if ($company) {
                    $docs30 = DB::table('documents')
                        ->where('company_id', $company->id)
                        ->where('created_at', '>=', now()->subDays(30))
                        ->count();

                    $docs90 = DB::table('documents')
                        ->where('company_id', $company->id)
                        ->where('created_at', '>=', now()->subDays(90))
                        ->count();

                    $lastDocDate = DB::table('documents')
                        ->where('company_id', $company->id)
                        ->max('created_at');
                }

                // Score de santé 0-100
                $score = 0;

                // Activité (50 pts max)
                if ($docs30 >= 20)     $score += 50;
                elseif ($docs30 >= 10) $score += 40;
                elseif ($docs30 >= 5)  $score += 30;
                elseif ($docs30 >= 1)  $score += 15;
                elseif ($docs90 >= 1)  $score += 5;

                // Ancienneté (20 pts max)
                $months = (int) $user->created_at->diffInMonths(now());
                if ($months >= 12)     $score += 20;
                elseif ($months >= 6)  $score += 15;
                elseif ($months >= 3)  $score += 10;
                elseif ($months >= 1)  $score += 5;

                // Plan (30 pts max) — on utilise le nom du plan lié
                $planName = strtolower($license?->plan?->name ?? 'free');
                if (str_contains($planName, 'business'))    $score += 30;
                elseif (str_contains($planName, 'pro'))     $score += 20;
                elseif (str_contains($planName, 'starter')) $score += 10;

                // Statut risque
                $risk = match (true) {
                    $score >= 70 => 'healthy',
                    $score >= 40 => 'at_risk',
                    default      => 'churning',
                };

                $lastDoc = $lastDocDate
                    ? \Carbon\Carbon::parse($lastDocDate)->diffForHumans()
                    : 'Jamais';

                return [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'company'      => $company?->name ?? '—',
                    'plan'         => $license?->plan?->name ?? 'free',
                    'plan_status'  => $license?->status ?? 'active',
                    'trial'        => $license?->type === 'trial',
                    'score'        => $score,
                    'risk'         => $risk,
                    'docs_30'      => $docs30,
                    'docs_90'      => $docs90,
                    'last_doc'     => $lastDoc,
                    'member_since' => $user->created_at->format('d/m/Y'),
                    'months'       => $months,
                ];
            });

        $stats = [
            'healthy'   => $users->where('risk', 'healthy')->count(),
            'at_risk'   => $users->where('risk', 'at_risk')->count(),
            'churning'  => $users->where('risk', 'churning')->count(),
            'total'     => $users->count(),
            'avg_score' => $users->count() > 0 ? round($users->avg('score')) : 0,
        ];

        return Inertia::render('Admin/Health', [
            'users' => $users->sortBy('score')->values(),
            'stats' => $stats,
        ]);
    }
}

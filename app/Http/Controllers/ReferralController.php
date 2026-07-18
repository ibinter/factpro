<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Dashboard du programme ambassadeur (cahier IBIG §22 Phase 8).
 */
class ReferralController extends Controller
{
    public function __construct(private ReferralService $referrals)
    {
    }

    /**
     * Affiche le tableau de bord de parrainage de l'utilisateur connecté.
     */
    public function index(Request $request): Response
    {
        $user  = $request->user();
        $stats = $this->referrals->getStats($user);

        $referrals = Referral::where('referrer_id', $user->id)
            ->with('referred:id,name,email')
            ->latest()
            ->take(20)
            ->get()
            ->map(fn (Referral $r) => [
                'id'           => $r->id,
                'status'       => $r->status,
                'reward_months' => $r->reward_months,
                'converted_at' => $r->converted_at?->format('d/m/Y'),
                'rewarded_at'  => $r->rewarded_at?->format('d/m/Y'),
                'created_at'   => $r->created_at?->format('d/m/Y'),
                // Nom masqué : "J*** D***"
                'referred_name' => $r->referred
                    ? $this->maskName($r->referred->name)
                    : null,
            ]);

        return Inertia::render('Referral/Index', [
            'stats'     => $stats,
            'referrals' => $referrals,
        ]);
    }

    /** Masque le nom : "Jean Dupont" → "J*** D***" */
    private function maskName(string $name): string
    {
        return implode(' ', array_map(function (string $part) {
            $first = mb_substr($part, 0, 1);
            return $first !== '' ? $first . '***' : '***';
        }, explode(' ', $name)));
    }
}

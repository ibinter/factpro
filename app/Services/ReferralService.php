<?php

namespace App\Services;

use App\Models\License;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Gestion du programme ambassadeur / parrainage (cahier IBIG §22 Phase 8).
 */
class ReferralService
{
    /**
     * Retourne le code de parrainage de l'utilisateur.
     * Le génère et le persiste s'il n'existe pas encore.
     */
    public function getOrCreateCode(User $user): string
    {
        if ($user->referral_code) {
            return $user->referral_code;
        }

        $code = 'IBG-' . strtoupper(Str::random(6));

        // Garantit l'unicité
        while (User::where('referral_code', $code)->exists()) {
            $code = 'IBG-' . strtoupper(Str::random(6));
        }

        $user->forceFill(['referral_code' => $code])->save();

        return $code;
    }

    /**
     * Enregistre un parrainage lors de l'inscription d'un nouvel utilisateur.
     * Appelé si le paramètre ?ref= est présent dans l'URL d'inscription.
     */
    public function registerReferral(User $newUser, string $code): void
    {
        $referrer = User::where('referral_code', $code)->first();

        if (! $referrer || $referrer->id === $newUser->id) {
            return;
        }

        // Évite les doublons
        if (Referral::where('referred_id', $newUser->id)->exists()) {
            return;
        }

        Referral::create([
            'referrer_id'   => $referrer->id,
            'referred_id'   => $newUser->id,
            'referral_code' => $code,
            'status'        => 'pending',
            'reward_months' => 1,
        ]);

        $newUser->forceFill(['referred_by_id' => $referrer->id])->save();
    }

    /**
     * Récompense le parrain lorsque son filleul souscrit un abonnement payant.
     * Idempotent : ne récompense qu'une seule fois.
     */
    public function rewardReferrer(User $referredUser): void
    {
        if (! $referredUser->referred_by_id) {
            return;
        }

        $referral = Referral::where('referred_id', $referredUser->id)
            ->where('status', 'pending')
            ->first();

        if (! $referral) {
            return;
        }

        // Marque comme converti
        $referral->update([
            'status'       => 'converted',
            'converted_at' => now(),
        ]);

        // Prolonge la licence du parrain
        $referrer = User::find($referral->referrer_id);
        if ($referrer) {
            $this->extendLicense($referrer, $referral->reward_months);
        }

        // Marque comme récompensé
        $referral->update([
            'status'      => 'rewarded',
            'rewarded_at' => now(),
        ]);
    }

    /**
     * Retourne les statistiques de parrainage pour un utilisateur.
     *
     * @return array{total: int, converted: int, rewarded: int, months_earned: int, code: string, link: string}
     */
    public function getStats(User $user): array
    {
        $code = $this->getOrCreateCode($user);

        $referrals = Referral::where('referrer_id', $user->id)->get();

        $total        = $referrals->count();
        $converted    = $referrals->whereIn('status', ['converted', 'rewarded'])->count();
        $rewarded     = $referrals->where('status', 'rewarded')->count();
        $monthsEarned = $referrals->where('status', 'rewarded')->sum('reward_months');

        $link = url('/register') . '?ref=' . $code;

        return [
            'total'         => $total,
            'converted'     => $converted,
            'rewarded'      => $rewarded,
            'months_earned' => (int) $monthsEarned,
            'code'          => $code,
            'link'          => $link,
        ];
    }

    /**
     * Prolonge la licence active du parrain de N mois.
     * Si aucune licence active, ne fait rien.
     */
    private function extendLicense(User $user, int $months): void
    {
        $license = $user->licenses()
            ->whereIn('status', ['trial', 'provisional', 'active', 'grace_period'])
            ->orderByDesc('ends_at')
            ->first();

        if (! $license) {
            return;
        }

        $newEnd = ($license->ends_at && $license->ends_at->isFuture())
            ? $license->ends_at->copy()->addMonths($months)
            : now()->addMonths($months);

        $license->update(['ends_at' => $newEnd]);
    }
}

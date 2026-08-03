<?php

namespace App\Services;

use App\Models\License;
use App\Models\Order;
use App\Models\PaymentAuditLog;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\PaymentValidatedNotification;
use App\Notifications\TrialStartedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LicenseService
{
    /** Génère une clé unique FP-XXXX-XXXX-XXXX-XXXX */
    public function generateKey(): string
    {
        do {
            $key = 'FP-'.implode('-', array_map(
                fn () => strtoupper(Str::random(4)),
                range(1, 4)
            ));
        } while (License::where('license_key', $key)->exists());

        return $key;
    }

    /** Licence courante (la plus favorable) d'un utilisateur. */
    public function currentFor(User $user): ?License
    {
        return cache()->remember(
            "license_current_user_{$user->id}",
            now()->addMinutes(5),
            fn () => $user->licenses()
                ->whereIn('status', ['trial', 'provisional', 'active', 'grace_period'])
                ->orderByDesc('ends_at')
                ->first()
        );
    }

    /** Invalide le cache de licence d'un utilisateur (après paiement, expiration...). */
    public function forgetCache(User $user): void
    {
        cache()->forget("license_current_user_{$user->id}");
    }

    /** Démarre l'essai gratuit 7 jours (à l'inscription). Idempotent. */
    public function startTrial(User $user): License
    {
        $existing = $user->licenses()->where('type', 'trial')->first();
        if ($existing) {
            return $existing;
        }

        $plan = Plan::where('code', 'pro')->firstOrFail(); // essai = fonctionnalités PRO
        $days = config('factpro.trial.duration_days', 7);

        $license = License::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'license_key' => $this->generateKey(),
            'type' => 'trial',
            'status' => 'trial',
            'starts_at' => now(),
            'ends_at' => now()->addDays($days),
            'trial_ends_at' => now()->addDays($days),
            'limits' => $plan->limits,
            'activation_source' => 'trial',
        ]);

        PaymentAuditLog::record('trial_started', 'license', $license->id, null, [
            'plan' => $plan->code, 'days' => $days,
        ]);

        // E-mail #5 : "Essai gratuit démarré" (§20.1 cahier des charges)
        $user->notify(new TrialStartedNotification($license));

        $this->forgetCache($user);

        return $license;
    }

    /**
     * Active (ou prolonge) une licence après paiement confirmé. IDEMPOTENT :
     * une même transaction ne peut jamais activer/prolonger deux fois (script §11).
     */
    public function activateFromOrder(Order $order, PaymentTransaction $transaction, ?int $adminId = null): License
    {
        $license = DB::transaction(function () use ($order, $transaction, $adminId) {
            // Idempotence : transaction déjà consommée → renvoyer la licence existante
            $existing = License::where('transaction_id', $transaction->id)->first();
            if ($existing) {
                return $existing;
            }

            $user = $order->user;
            $plan = $order->plan;
            $months = $order->duration_months;

            $current = $this->currentFor($user);

            // Renouvellement (script §12) : licence active → prolonger la date de fin
            if ($current && $current->type !== 'trial' && $current->plan_id === $plan->id && $current->ends_at->isFuture()) {
                $old = ['ends_at' => $current->ends_at->toDateTimeString()];
                $current->update([
                    'ends_at' => $current->ends_at->copy()->addMonths($months),
                    'status' => 'active',
                    'type' => 'paid',
                    'order_id' => $order->id,
                    'transaction_id' => $transaction->id,
                    'grace_period_ends_at' => null,
                    'activated_by' => $adminId,
                ]);
                PaymentAuditLog::record('license_extended', 'license', $current->id, $old, [
                    'ends_at' => $current->ends_at->toDateTimeString(),
                ], adminId: $adminId);

                // E-mail : confirmation renouvellement
                $user->notify(new PaymentValidatedNotification($transaction));

                return $current;
            }

            // Expiration de l'essai / changement de plan : la licence d'essai est terminée
            if ($current && $current->type === 'trial') {
                $current->update(['status' => 'terminated']);
            }

            $starts = now();
            $license = License::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'order_id' => $order->id,
                'transaction_id' => $transaction->id,
                'license_key' => $this->generateKey(),
                'type' => 'paid',
                'status' => 'active',
                'starts_at' => $starts,
                'ends_at' => $starts->copy()->addMonths($months),
                'limits' => $plan->limits,
                'activation_source' => $adminId ? 'manual' : 'payment',
                'activated_by' => $adminId,
            ]);

            $order->update(['status' => 'paid', 'paid_at' => now()]);

            PaymentAuditLog::record('license_activated', 'license', $license->id, null, [
                'plan' => $plan->code,
                'months' => $months,
                'order' => $order->order_number,
            ], adminId: $adminId);

            // E-mail : confirmation activation licence payante
            $user->notify(new PaymentValidatedNotification($transaction));

            return $license;
        });

        $this->forgetCache($order->user);

        return $license;
    }

    /**
     * Active une licence à titre provisoire (virement ou preuve en attente de vérification).
     * Appelée uniquement par un administrateur autorisé.
     */
    public function activateProvisionally(Order $order, PaymentTransaction $transaction, User $admin, string $motif, int $days = 7): License
    {
        return DB::transaction(function () use ($order, $transaction, $admin, $motif, $days) {
            $user = $order->user;

            $existing = License::where('user_id', $user->id)
                ->whereIn('status', ['active', 'provisional'])
                ->where('ends_at', '>', now())
                ->first();

            if ($existing && $existing->status === 'active') {
                throw new \RuntimeException('Une licence active existe déjà pour cet utilisateur.');
            }

            if ($existing && $existing->status === 'provisional') {
                $existing->update([
                    'ends_at'  => now()->addDays($days),
                    'metadata' => array_merge($existing->metadata ?? [], [
                        'provisional_reason'     => $motif,
                        'provisional_granted_by' => $admin->id,
                        'provisional_granted_at' => now()->toISOString(),
                    ]),
                ]);
                PaymentAuditLog::record('provisional_extended', 'license', $existing->id, null, [
                    'admin_id' => $admin->id,
                    'motif'    => $motif,
                    'days'     => $days,
                ], adminId: $admin->id);
                return $existing;
            }

            $plan = $order->plan;

            $license = License::create([
                'user_id'           => $user->id,
                'plan_id'           => $plan->id,
                'order_id'          => $order->id,
                'transaction_id'    => $transaction->id,
                'license_key'       => $this->generateKey(),
                'type'              => 'paid',
                'status'            => 'provisional',
                'starts_at'         => now(),
                'ends_at'           => now()->addDays($days),
                'limits'            => $plan->limits,
                'activation_source' => 'provisional',
                'activated_by'      => $admin->id,
                'metadata'          => [
                    'provisional_reason'     => $motif,
                    'provisional_granted_by' => $admin->id,
                    'provisional_granted_at' => now()->toISOString(),
                    'provisional_days'       => $days,
                ],
            ]);

            $order->update(['status' => 'proof_submitted']);

            PaymentAuditLog::record('provisional_license_activated', 'license', $license->id, null, [
                'admin_id'   => $admin->id,
                'motif'      => $motif,
                'expires_at' => $license->ends_at->toISOString(),
            ], adminId: $admin->id);

            return $license;
        });
    }

    /**
     * Convertit une licence provisoire en licence définitive après confirmation des fonds.
     */
    public function confirmProvisional(License $license, PaymentTransaction $transaction, User $admin): License
    {
        return DB::transaction(function () use ($license, $transaction, $admin) {
            if ($license->status !== 'provisional') {
                throw new \RuntimeException("Cette licence n'est pas en statut provisoire.");
            }

            $order  = $license->order;
            $months = $order ? $order->duration_months : 1;

            $license->update([
                'status'  => 'active',
                'ends_at' => now()->addMonths($months),
                'metadata' => array_merge($license->metadata ?? [], [
                    'provisional_confirmed_at' => now()->toISOString(),
                    'provisional_confirmed_by' => $admin->id,
                ]),
            ]);

            $transaction->update([
                'status'       => 'manually_validated',
                'validated_by' => $admin->id,
                'confirmed_at' => now(),
            ]);

            $license->refresh();

            PaymentAuditLog::record('provisional_confirmed_to_active', 'license', $license->id, null, [
                'admin_id'       => $admin->id,
                'new_expires_at' => $license->ends_at->toISOString(),
            ], adminId: $admin->id);

            // E-mail : licence provisoire confirmée → active
            $license->user->notify(new PaymentValidatedNotification($transaction));

            return $license;
        });
    }

    /**
     * Licence applicable dans le contexte courant d'un utilisateur.
     * Un membre d'équipe (non propriétaire) bénéficie de la licence du propriétaire
     * de sa société courante — il n'a pas besoin de son propre abonnement.
     */
    public function currentForContext(User $user): ?License
    {
        $own = $this->currentFor($user);
        if ($own) {
            return $own;
        }

        $company = $user->currentCompany;
        if ($company && $company->owner_id && $company->owner_id !== $user->id) {
            $owner = $company->owner;
            if ($owner) {
                return $this->currentFor($owner);
            }
        }

        return null;
    }

    /** L'utilisateur a-t-il une licence utilisable (essai ou payante) ? */
    public function isActive(User $user): bool
    {
        return (bool) $this->currentForContext($user)?->isUsable();
    }

    /** Le filigrane essai doit-il être appliqué aux documents ? */
    public function needsTrialWatermark(User $user): bool
    {
        // Les comptes démo portent toujours un filigrane, quelle que soit leur licence.
        if ($user->isDemoAccount()) {
            return true;
        }

        $license = $this->currentFor($user);

        return $license === null || $license->isTrial();
    }

    /** Vérifie une limite du plan (null = illimité). Retourne true si la limite est atteinte. */
    public function limitReached(User $user, string $key, int $currentCount): bool
    {
        $license = $this->currentForContext($user);
        if (! $license) {
            return true;
        }

        $limit = $license->limit($key);

        return $limit !== null && $currentCount >= $limit;
    }
}

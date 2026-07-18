<?php

namespace App\Services;

use App\Models\FraudAlert;
use App\Models\License;
use App\Models\Order;
use App\Models\PaymentProof;
use App\Models\PaymentTransaction;
use App\Mail\FraudAlertMail;
use Illuminate\Support\Facades\Mail;

class FraudDetectionService
{
    /** Poids de chaque signal de fraude. */
    public const SIGNALS = [
        'duplicate_reference'    => 3, // Référence déjà utilisée sur une autre commande
        'duplicate_proof_hash'   => 3, // Même fichier de preuve déjà soumis
        'amount_mismatch'        => 2, // Montant déclaré différent de >5 % du total attendu
        'suspicious_new_account' => 1, // Compte créé il y a moins de 24 h
        'multiple_pending'       => 2, // ≥ 2 autres commandes en attente simultanées
        'repeated_provisional'   => 2, // ≥ 1 activation provisoire ce mois pour cet utilisateur
        'country_mismatch'       => 1, // Pays déclaré ≠ pays du compte
        'expired_transaction'    => 2, // Transaction déclarée > 7 jours après initiation
        'illegible_proof'        => 1, // Preuve illisible (signal manuel uniquement)
        'name_mismatch'          => 1, // Nom expéditeur très différent du nom du compte
    ];

    /**
     * Analyse une commande + données de preuve et crée une FraudAlert si nécessaire.
     *
     * @param  Order  $order        La commande en cours de vérification.
     * @param  array  $proofData    Données saisies par le client (declared_amount, transaction_reference…).
     * @param  string|null $proofHash  SHA-256 du fichier de preuve uploadé.
     * @return array{score: int, flags: list<string>, risk_level: string}
     */
    public function analyze(Order $order, array $proofData, ?string $proofHash = null): array
    {
        $flags = [];
        $score = 0;

        // 1. Référence de transaction déjà utilisée sur une autre commande
        if (! empty($proofData['transaction_reference'])) {
            $refExists = PaymentTransaction::where('provider_reference', $proofData['transaction_reference'])
                ->where('status', '!=', 'failed')
                ->where('order_id', '!=', $order->id)
                ->exists();

            if ($refExists) {
                $flags[] = 'duplicate_reference';
                $score  += self::SIGNALS['duplicate_reference'];
            }
        }

        // 2. Hash de la preuve déjà utilisé
        if ($proofHash) {
            $hashExists = PaymentProof::where('file_hash', $proofHash)->exists();

            if ($hashExists) {
                $flags[] = 'duplicate_proof_hash';
                $score  += self::SIGNALS['duplicate_proof_hash'];
            }
        }

        // 3. Montant déclaré vs montant attendu (tolérance 5 %)
        $declaredAmount = (float) ($proofData['declared_amount'] ?? 0);
        $expectedAmount = (float) $order->total_amount;

        if ($declaredAmount > 0 && $expectedAmount > 0) {
            $deviation = abs($declaredAmount - $expectedAmount) / $expectedAmount;
            if ($deviation > 0.05) {
                $flags[] = 'amount_mismatch';
                $score  += self::SIGNALS['amount_mismatch'];
            }
        }

        // 4. Compte utilisateur créé il y a moins de 24 h
        $user = $order->user;
        if ($user && $user->created_at?->isAfter(now()->subHours(24))) {
            $flags[] = 'suspicious_new_account';
            $score  += self::SIGNALS['suspicious_new_account'];
        }

        // 5. Autres commandes en attente pour le même utilisateur (≥ 2)
        $pendingCount = Order::where('user_id', $order->user_id)
            ->whereIn('status', ['pending_payment', 'proof_submitted', 'under_review'])
            ->where('id', '!=', $order->id)
            ->count();

        if ($pendingCount >= 2) {
            $flags[] = 'multiple_pending';
            $score  += self::SIGNALS['multiple_pending'];
        }

        // 6. Activations provisoires répétées ce mois (même utilisateur)
        $provisionalCount = License::where('user_id', $order->user_id)
            ->where('status', 'provisional')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        if ($provisionalCount > 0) {
            $flags[] = 'repeated_provisional';
            $score  += self::SIGNALS['repeated_provisional'];
        }

        // 7. Créer une alerte si score > 1
        if ($score > 1) {
            FraudAlert::create([
                'order_id'    => $order->id,
                'alert_type'  => implode(',', $flags),
                'score'       => $score,
                'flags'       => $flags,
                'description' => "Score fraude : {$score}. Signaux : " . implode(', ', $flags),
                'status'      => 'open',
            ]);

            // Notifier l'admin par e-mail si score critique (≥ 3)
            if ($score >= 3 && config('factpro.fraud_alert_email')) {
                Mail::to(config('factpro.fraud_alert_email'))
                    ->send(new FraudAlertMail($order, $score, $flags));
            }
        }

        return [
            'score'      => $score,
            'flags'      => $flags,
            'risk_level' => $this->riskLevel($score),
        ];
    }

    public function riskLevel(int $score): string
    {
        return match (true) {
            $score === 0 => 'low',
            $score <= 2  => 'medium',
            default      => 'high',
        };
    }
}

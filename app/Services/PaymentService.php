<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentAuditLog;
use App\Models\PaymentProof;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private LicenseService $licenses,
    ) {
    }

    private function notifications(): PaymentNotificationService
    {
        return app(PaymentNotificationService::class);
    }

    /** Référence interne unique — format FP-{YYYYMMDD}-{RANDOM6} (script §4.1) */
    public function internalReference(): string
    {
        do {
            $ref = sprintf('FP-%s-%s', now()->format('Ymd'), strtoupper(Str::random(6)));
        } while (PaymentTransaction::where('internal_reference', $ref)->exists());

        return $ref;
    }

    /** Numéro de commande unique — format FP-{YYYY}-{XXXXXX} */
    public function orderNumber(): string
    {
        do {
            $number = sprintf('FP-%d-%s', now()->year, strtoupper(Str::random(6)));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }

    /** Crée une commande d'abonnement (statut pending_payment, expire sous 72h). */
    public function createOrder(User $user, Plan $plan, int $months, string $country = 'CI'): Order
    {
        // Anti double-clic : réutiliser une commande identique encore payable
        $existing = Order::where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->where('duration_months', $months)
            ->where('status', 'pending_payment')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return $existing;
        }

        $amount = $plan->priceFor($months);

        $order = Order::create([
            'order_number' => $this->orderNumber(),
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'duration_months' => $months,
            'amount' => $amount,
            'discount_amount' => $months === 12 ? round((float) $plan->price_monthly * 2, 2) : 0,
            'tax_amount' => 0,
            'total_amount' => $amount,
            'currency' => $plan->currency,
            'country' => $country,
            'status' => 'pending_payment',
            'expires_at' => now()->addHours(72),
        ]);

        PaymentAuditLog::record('order_created', 'order', $order->id, null, [
            'plan' => $plan->code, 'months' => $months, 'total' => $amount,
        ]);

        return $order;
    }

    /** Déclare un paiement manuel (Mobile Money / virement / espèces) avec preuve optionnelle. */
    public function submitManualPayment(
        Order $order,
        string $provider,
        array $declaration, // sender_name, sender_number, provider_reference, amount_declared
        ?UploadedFile $proofFile,
    ): PaymentTransaction {
        return DB::transaction(function () use ($order, $provider, $declaration, $proofFile) {
            $transaction = PaymentTransaction::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'payment_provider' => $provider,
                'provider_reference' => $declaration['provider_reference'] ?? null,
                'internal_reference' => $this->internalReference(),
                'amount_expected' => $order->total_amount,
                'amount_declared' => $declaration['amount_declared'] ?? null,
                'currency' => $order->currency,
                'status' => 'under_review',
                'sender_name' => $declaration['sender_name'] ?? null,
                'sender_number' => $declaration['sender_number'] ?? null,
                'initiated_at' => now(),
            ]);

            // Stockage privé de la preuve (script §8) — optionnel pour les paiements espèces
            if ($proofFile !== null) {
                $storedName = Str::random(40).'.'.strtolower($proofFile->getClientOriginalExtension());
                $path = $proofFile->storeAs('private/proofs', $storedName, config('factpro.proofs.disk'));

                PaymentProof::create([
                    'transaction_id' => $transaction->id,
                    'original_filename' => $proofFile->getClientOriginalName(),
                    'stored_filename' => $storedName,
                    'file_path' => $path,
                    'mime_type' => (string) $proofFile->getMimeType(),
                    'file_size' => $proofFile->getSize(),
                    'file_hash' => hash_file('sha256', $proofFile->getRealPath()),
                    'uploaded_by' => $order->user_id,
                    'verification_status' => 'pending',
                ]);
            }

            $order->update(['status' => 'proof_submitted']);

            PaymentAuditLog::record('proof_submitted', 'transaction', $transaction->id, null, [
                'provider' => $provider,
                'amount_declared' => $declaration['amount_declared'] ?? null,
            ]);

            // Accusé de réception au client
            $this->notifications()->sendProofReceived($order, $transaction);

            return $transaction;
        });
    }

    /** Validation admin d'un paiement manuel → activation de licence (script §9). */
    public function validateManualPayment(
        PaymentTransaction $transaction,
        User $admin,
        float $amountReceived,
        ?string $note = null,
    ): void {
        DB::transaction(function () use ($transaction, $admin, $amountReceived, $note) {
            $old = ['status' => $transaction->status];

            $transaction->update([
                'status' => 'manually_validated',
                'amount_received' => $amountReceived,
                'confirmed_at' => now(),
                'validated_by' => $admin->id,
                'notes' => $note,
            ]);

            $transaction->proofs()->update([
                'verification_status' => 'approved',
                'verified_by' => $admin->id,
            ]);

            PaymentAuditLog::record('payment_validated', 'transaction', $transaction->id, $old, [
                'status' => 'manually_validated',
                'amount_received' => $amountReceived,
            ], reason: $note, adminId: $admin->id);

            $license = $this->licenses->activateFromOrder($transaction->order, $transaction, $admin->id);

            // Notification email + reçu PDF
            $this->notifications()->sendPaymentValidated($transaction->order, $transaction, $license);
        });
    }

    /** Rejet admin (motif obligatoire — script §9). */
    public function rejectManualPayment(PaymentTransaction $transaction, User $admin, string $reason): void
    {
        DB::transaction(function () use ($transaction, $admin, $reason) {
            $old = ['status' => $transaction->status];

            $transaction->update([
                'status' => 'rejected',
                'validated_by' => $admin->id,
                'rejection_reason' => $reason,
            ]);

            $transaction->proofs()->update([
                'verification_status' => 'rejected',
                'verified_by' => $admin->id,
                'internal_comment' => $reason,
            ]);

            $transaction->order->update(['status' => 'rejected']);

            PaymentAuditLog::record('payment_rejected', 'transaction', $transaction->id, $old, [
                'status' => 'rejected',
            ], reason: $reason, adminId: $admin->id);

            // Notification rejet au client
            $this->notifications()->sendPaymentRejected($transaction->order, $transaction, $reason);
        });
    }

    /** Demande un complément d'information au client pour finaliser la validation. */
    public function requestComplement(PaymentTransaction $transaction, User $admin, string $note): void
    {
        $transaction->update(['status' => 'pending']);
        $transaction->order->update(['status' => 'missing_info']);

        PaymentAuditLog::record('complement_requested', 'transaction', $transaction->id, null, [
            'note' => $note,
        ], adminId: $admin->id);

        $this->notifications()->sendComplementRequested($transaction->order, $note);
    }

    /** Détection d'anomalies simples (script §23) — renvoie un niveau de risque. */
    public function riskLevel(PaymentTransaction $transaction): string
    {
        $alerts = 0;

        // Référence déjà utilisée ailleurs
        if ($transaction->provider_reference && PaymentTransaction::where('provider_reference', $transaction->provider_reference)
            ->where('id', '!=', $transaction->id)->exists()) {
            $alerts += 2;
        }

        // Même preuve (hash identique) utilisée sur une autre transaction
        $hashes = $transaction->proofs()->pluck('file_hash')->filter();
        if ($hashes->isNotEmpty() && PaymentProof::whereIn('file_hash', $hashes)
            ->where('transaction_id', '!=', $transaction->id)->exists()) {
            $alerts += 2;
        }

        // Montant déclaré hors tolérance
        $tolerance = config('factpro.fraud.amount_tolerance_percent', 5) / 100;
        if ($transaction->amount_declared !== null) {
            $expected = (float) $transaction->amount_expected;
            if ($expected > 0 && abs((float) $transaction->amount_declared - $expected) / $expected > $tolerance) {
                $alerts += 1;
            }
        }

        // Compte créé il y a moins de 24h
        if ($transaction->user?->created_at?->gt(now()->subDay())) {
            $alerts += 1;
        }

        return match (true) {
            $alerts >= 4 => 'CRITICAL',
            $alerts >= 2 => 'HIGH',
            $alerts >= 1 => 'MEDIUM',
            default => 'LOW',
        };
    }
}

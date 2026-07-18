<?php

namespace App\Services;

use App\Models\License;
use App\Models\Order;
use App\Models\PaymentAuditLog;
use App\Models\PaymentTransaction;
use App\Models\PrepaidVoucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Gestion des codes prépayés revendeurs (cahier §Voucher).
 * Génération en lot, vérification, activation instantanée.
 */
class VoucherService
{
    /**
     * Génère un lot de codes prépayés.
     *
     * Params : quantity, plan_id, duration_months, currency, face_value,
     *          reseller_price, reseller_name, expires_at, created_by
     */
    public function generateBatch(array $params): array
    {
        $batchRef = 'BATCH-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        $vouchers = [];

        for ($i = 0; $i < $params['quantity']; $i++) {
            $code = $this->generateUniqueCode();
            $voucher = PrepaidVoucher::create([
                'code'           => $code,
                'batch_ref'      => $batchRef,
                'plan_id'        => $params['plan_id'] ?? null,
                'duration_months'=> $params['duration_months'] ?? 1,
                'currency'       => $params['currency'] ?? 'XOF',
                'face_value'     => $params['face_value'] ?? 0,
                'reseller_price' => $params['reseller_price'] ?? 0,
                'reseller_name'  => $params['reseller_name'] ?? null,
                'expires_at'     => isset($params['expires_at']) ? Carbon::parse($params['expires_at']) : null,
                'created_by'     => $params['created_by'],
                'status'         => 'available',
            ]);
            $vouchers[] = $voucher;
        }

        PaymentAuditLog::record('voucher_batch_generated', 'prepaid_voucher', $batchRef, null, [
            'batch_ref' => $batchRef,
            'quantity'  => $params['quantity'],
            'created_by'=> $params['created_by'],
        ]);

        return ['batch_ref' => $batchRef, 'vouchers' => $vouchers];
    }

    /**
     * Vérifie un code et retourne les infos sans l'activer.
     */
    public function verify(string $code): array
    {
        $voucher = PrepaidVoucher::where('code', strtoupper(trim($code)))->with('plan')->first();

        if (! $voucher) {
            return ['valid' => false, 'error' => 'Code introuvable.'];
        }

        if (! $voucher->isUsable()) {
            $reason = match ($voucher->status) {
                'used'      => 'Code déjà utilisé.',
                'expired'   => 'Code expiré.',
                'cancelled' => 'Code annulé.',
                default     => 'Code non disponible.',
            };

            // Expiration par date même si statut toujours 'available'
            if ($voucher->status === 'available' && $voucher->expires_at !== null && $voucher->expires_at->isPast()) {
                $reason = 'Code expiré.';
            }

            return ['valid' => false, 'error' => $reason];
        }

        return [
            'valid'           => true,
            'voucher'         => $voucher,
            'plan'            => $voucher->plan,
            'duration_months' => $voucher->duration_months,
            'face_value'      => (float) $voucher->face_value,
            'currency'        => $voucher->currency,
        ];
    }

    /**
     * Active un code prépayé — crée une commande + transaction + licence instantanément.
     */
    public function redeem(string $code, int $companyId, int $userId): License
    {
        return DB::transaction(function () use ($code, $companyId, $userId) {
            $voucher = PrepaidVoucher::where('code', strtoupper(trim($code)))
                ->lockForUpdate()
                ->firstOrFail();

            if (! $voucher->isUsable()) {
                throw new \RuntimeException('Ce code n\'est pas disponible : ' . $voucher->status);
            }

            // Créer une commande représentant l'activation
            $order = Order::create([
                'order_number'    => 'VCH-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'user_id'         => $userId,
                'plan_id'         => $voucher->plan_id,
                'duration_months' => $voucher->duration_months,
                'amount'          => $voucher->face_value,
                'discount_amount' => 0,
                'tax_amount'      => 0,
                'total_amount'    => $voucher->face_value,
                'currency'        => $voucher->currency,
                'country'         => 'CI',
                'status'          => 'paid',
                'paid_at'         => now(),
                'metadata'        => ['voucher_code' => $voucher->code, 'batch_ref' => $voucher->batch_ref],
            ]);

            // Créer une transaction « voucher »
            $transaction = PaymentTransaction::create([
                'order_id'           => $order->id,
                'user_id'            => $userId,
                'payment_provider'   => 'voucher',
                'provider_reference' => $voucher->code,
                'internal_reference' => 'VCH-' . strtoupper(Str::uuid()),
                'amount_expected'    => $voucher->face_value,
                'amount_declared'    => $voucher->face_value,
                'amount_received'    => $voucher->face_value,
                'currency'           => $voucher->currency,
                'status'             => 'manually_validated',
                'confirmed_at'       => now(),
                'initiated_at'       => now(),
            ]);

            // Activer la licence via LicenseService (idempotent)
            $license = app(LicenseService::class)->activateFromOrder($order, $transaction);

            // Marquer le voucher utilisé
            $voucher->update([
                'status'               => 'used',
                'used_at'              => now(),
                'used_by_user_id'      => $userId,
                'used_by_company_id'   => $companyId,
                'activated_license_id' => $license->id,
                'order_id'             => $order->id,
            ]);

            PaymentAuditLog::record('voucher_redeemed', 'prepaid_voucher', (string) $voucher->id, null, [
                'code'       => $voucher->code,
                'company_id' => $companyId,
                'license_id' => $license->id,
            ]);

            return $license;
        });
    }

    /**
     * Exporte un lot en CSV.
     */
    public function exportBatchCsv(string $batchRef): string
    {
        $vouchers = PrepaidVoucher::where('batch_ref', $batchRef)->with('plan')->get();

        $csv = "Code,Forfait,Durée,Valeur,Devise,Statut,Expiration\n";

        foreach ($vouchers as $v) {
            $csv .= implode(',', [
                $v->code,
                $v->plan?->name ?? 'Tous',
                $v->duration_months . ' mois',
                $v->face_value,
                $v->currency,
                $v->status,
                $v->expires_at?->format('Y-m-d') ?? 'Sans expiration',
            ]) . "\n";
        }

        return $csv;
    }

    /** Annule un code individuel. */
    public function cancel(PrepaidVoucher $voucher): void
    {
        if ($voucher->status === 'used') {
            throw new \RuntimeException('Impossible d\'annuler un code déjà utilisé.');
        }

        $voucher->update(['status' => 'cancelled']);

        PaymentAuditLog::record('voucher_cancelled', 'prepaid_voucher', (string) $voucher->id, null, [
            'code'      => $voucher->code,
            'batch_ref' => $voucher->batch_ref,
        ]);
    }

    /** Génère un code unique au format IBIG-XXXX-XXXX-XXXX. */
    private function generateUniqueCode(): string
    {
        do {
            $code = 'IBIG-'
                . strtoupper(Str::random(4)) . '-'
                . strtoupper(Str::random(4)) . '-'
                . strtoupper(Str::random(4));
        } while (PrepaidVoucher::where('code', $code)->exists());

        return $code;
    }
}

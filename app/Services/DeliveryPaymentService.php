<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Models\License;
use App\Models\Order;
use App\Models\PaymentAuditLog;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeliveryPaymentService
{
    /**
     * Le client soumet une commande paiement à la livraison.
     */
    public function createDeliveryOrder(Order $order, array $deliveryData): DeliveryOrder
    {
        $confirmationCode = strtoupper(Str::random(6));

        $delivery = DeliveryOrder::create([
            'order_id'         => $order->id,
            'delivery_address' => $deliveryData['address'],
            'delivery_city'    => $deliveryData['city'],
            'delivery_country' => $deliveryData['country'] ?? 'CI',
            'contact_phone'    => $deliveryData['phone'],
            'contact_name'     => $deliveryData['contact_name'],
            'delivery_notes'   => $deliveryData['notes'] ?? null,
            'cod_amount'       => $order->total_amount,
            'cod_currency'     => $order->currency ?? 'XOF',
            'status'           => 'pending',
            'confirmation_code' => $confirmationCode,
        ]);

        $order->update(['status' => 'awaiting_delivery', 'payment_method' => 'cod']);

        PaymentAuditLog::record('delivery_order_created', 'delivery_order', (string) $delivery->id, null, [
            'delivery_id' => $delivery->id,
            'city'        => $deliveryData['city'],
        ]);

        return $delivery;
    }

    /**
     * L'agent confirme la réception du paiement.
     * Déclenche l'activation de la licence.
     */
    public function confirmPaymentReceived(
        DeliveryOrder $delivery,
        float $amountReceived,
        int $confirmedBy,
        string $agentNotes = '',
    ): License {
        return DB::transaction(function () use ($delivery, $amountReceived, $confirmedBy, $agentNotes) {
            $delivery->update([
                'status'               => 'payment_received',
                'amount_received'      => $amountReceived,
                'payment_confirmed_at' => now(),
                'confirmed_by'         => $confirmedBy,
                'agent_notes'          => $agentNotes,
            ]);

            $order = $delivery->order;
            $order->update(['status' => 'paid', 'paid_at' => now()]);

            $transaction = PaymentTransaction::create([
                'order_id'           => $order->id,
                'user_id'            => $order->user_id,
                'payment_provider'   => 'cod',
                'internal_reference' => 'COD-' . strtoupper(Str::random(8)),
                'provider_reference' => 'COD-DEL-' . $delivery->id,
                'amount_expected'    => $delivery->cod_amount,
                'amount_declared'    => $amountReceived,
                'amount_received'    => $amountReceived,
                'currency'           => $delivery->cod_currency,
                'status'             => 'manually_validated',
                'validated_by'       => $confirmedBy,
                'confirmed_at'       => now(),
                'initiated_at'       => now(),
            ]);

            $license = app(LicenseService::class)->activateFromOrder($order, $transaction, $confirmedBy);

            PaymentAuditLog::record('cod_payment_confirmed', 'delivery_order', (string) $delivery->id, null, [
                'amount_received' => $amountReceived,
                'confirmed_by'    => $confirmedBy,
                'license_id'      => $license->id,
            ]);

            return $license;
        });
    }

    /**
     * Confirme via le code à 6 caractères envoyé au client.
     */
    public function confirmByCode(string $confirmationCode, int $companyId): License
    {
        $delivery = DeliveryOrder::where('confirmation_code', strtoupper($confirmationCode))
            ->whereHas('order', fn ($q) => $q->where('user_id', function ($sub) use ($companyId) {
                $sub->select('user_id')
                    ->from('company_user')
                    ->where('company_id', $companyId)
                    ->limit(1);
            }))
            ->where('status', 'out_for_delivery')
            ->firstOrFail();

        return $this->confirmPaymentReceived(
            $delivery,
            (float) $delivery->cod_amount,
            auth()->id(),
            'Confirmé par code client',
        );
    }
}

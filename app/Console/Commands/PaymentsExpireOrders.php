<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\PaymentAuditLog;
use Illuminate\Console\Command;

class PaymentsExpireOrders extends Command
{
    protected $signature = 'payments:expire-orders';

    protected $description = 'Expire les commandes en attente de paiement dont la date limite est dépassée';

    public function handle(): int
    {
        $orders = Order::query()
            ->whereIn('status', ['pending_payment', 'payment_initiated'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Aucune commande à expirer.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($orders as $order) {
            $oldStatus = $order->status;
            $order->update(['status' => 'expired']);

            PaymentAuditLog::record(
                'order_expired',
                'order',
                (string) $order->id,
                ['status' => $oldStatus],
                ['status' => 'expired'],
                'Expiration automatique de la commande (délai de paiement dépassé — scheduler)',
            );

            $count++;
        }

        $this->info("{$count} commande(s) expirée(s).");

        return self::SUCCESS;
    }
}

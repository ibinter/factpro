<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Alerte stock bas (cahier des charges §8) — envoyée au propriétaire de la
 * société lorsqu'un produit suivi passe sous son seuil d'alerte.
 */
class LowStockAlert extends Notification
{
    use Queueable;

    public function __construct(
        public Product $product,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $qty = rtrim(rtrim(number_format((float) $this->product->stock_quantity, 2, ',', ' '), '0'), ',');
        $seuil = rtrim(rtrim(number_format((float) $this->product->stock_alert_threshold, 2, ',', ' '), '0'), ',');

        return (new MailMessage)
            ->subject("⚠ Stock bas : {$this->product->name} — {$qty} restant(s) (seuil : {$seuil})")
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line("Le stock du produit « {$this->product->name} » est passé sous son seuil d'alerte.")
            ->line("Quantité restante : {$qty} {$this->product->unit} (seuil d'alerte : {$seuil}).")
            ->line('Pensez à réapprovisionner ce produit pour éviter toute rupture.')
            ->action('Voir la gestion des stocks', url('/stock'))
            ->salutation("Cordialement,\nL'équipe IBIG FactPro — factpro.ibigsoft.com");
    }
}

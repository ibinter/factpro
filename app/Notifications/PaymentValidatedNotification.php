<?php

namespace App\Notifications;

use App\Models\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentValidatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PaymentTransaction $transaction,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = $this->transaction->amount_received
            ?? $this->transaction->amount_declared
            ?? $this->transaction->amount_expected;
        $currency = $this->transaction->currency ?? 'XOF';

        $message = (new MailMessage)
            ->subject('Votre paiement IBIG FactPro a été validé')
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
            ->line('Bonne nouvelle : votre paiement a été vérifié et validé par notre équipe.');

        if ($amount !== null) {
            $message->line('Montant confirmé : ' . number_format((float) $amount, 0, ',', ' ') . ' ' . $currency . '.');
        }

        return $message
            ->line('Votre licence est désormais active et vous pouvez profiter pleinement de votre espace de facturation.')
            ->action('Accéder à mon espace', url('/billing'))
            ->line('Merci de votre confiance.')
            ->salutation("Cordialement,\nL'équipe IBIG FactPro — factpro.ibigsoft.com");
    }
}

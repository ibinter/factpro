<?php

namespace App\Notifications;

use App\Models\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PaymentTransaction $transaction,
        public ?string $reason = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Votre paiement IBIG FactPro n\'a pas pu être validé')
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
            ->line('Après vérification, votre paiement n\'a malheureusement pas pu être validé par notre équipe.');

        if ($this->reason) {
            $message->line('Motif : ' . $this->reason);
        }

        return $message
            ->line('Nous vous invitons à vérifier les informations transmises (référence de transaction, justificatif, montant) et à soumettre une nouvelle preuve de paiement, ou à effectuer un nouveau règlement.')
            ->action('Régulariser mon paiement', url('/billing'))
            ->line('Notre équipe reste à votre disposition pour toute question.')
            ->salutation("Cordialement,\nL'équipe IBIG FactPro — factpro.ibigsoft.com");
    }
}

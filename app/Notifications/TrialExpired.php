<?php

namespace App\Notifications;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialExpired extends Notification
{
    use Queueable;

    public function __construct(
        public License $license,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre essai gratuit IBIG FactPro est arrivé à échéance')
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
            ->line("Votre période d'essai gratuite d'IBIG FactPro est arrivée à échéance.")
            ->line("Vos données (clients, produits, documents) sont conservées en toute sécurité. Pour retrouver un accès complet à votre espace de facturation, souscrivez à l'une de nos formules.")
            ->action('Souscrire à un abonnement', url('/billing/plans'))
            ->line('Nous espérons que cet essai vous a convaincu et nous serions ravis de vous compter parmi nos clients.')
            ->salutation("Cordialement,\nL'équipe IBIG FactPro — factpro.ibigsoft.com");
    }
}

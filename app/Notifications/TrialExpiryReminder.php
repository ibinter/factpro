<?php

namespace App\Notifications;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialExpiryReminder extends Notification
{
    use Queueable;

    public function __construct(
        public License $license,
        public int $days,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date = optional($this->license->trial_ends_at)->format('d/m/Y');
        $jours = $this->days > 1 ? "{$this->days} jours" : '1 jour';

        return (new MailMessage)
            ->subject("Votre essai gratuit IBIG FactPro expire dans {$jours}")
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
            ->line("Votre période d'essai gratuite d'IBIG FactPro arrive à échéance dans {$jours}" . ($date ? " (le {$date})." : '.'))
            ->line("Pour continuer à profiter de toutes les fonctionnalités de facturation sans interruption, choisissez dès maintenant la formule adaptée à votre activité.")
            ->action('Choisir mon abonnement', url('/billing/plans'))
            ->line("Après l'expiration de l'essai, l'accès aux fonctionnalités sera restreint jusqu'à la souscription d'un abonnement.")
            ->salutation("Cordialement,\nL'équipe IBIG FactPro — factpro.ibigsoft.com");
    }
}

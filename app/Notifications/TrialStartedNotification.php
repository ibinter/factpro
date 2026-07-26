<?php

namespace App\Notifications;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialStartedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public License $license,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $endsAt = optional($this->license->trial_ends_at)->format('d/m/Y');
        $days   = config('factpro.trial.duration_days', 7);

        return (new MailMessage)
            ->subject('Votre essai gratuit IBIG FactPro a commencé !')
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
            ->line("Votre essai gratuit de {$days} jours vient de démarrer" . ($endsAt ? " et expire le **{$endsAt}**." : '.'))
            ->line('Pendant cette période, vous avez accès à toutes les fonctionnalités PRO :')
            ->line('✔ Factures & devis illimités — ✔ Gestion clients & produits — ✔ Tableau de bord & rapports')
            ->action('Accéder à mon espace FactPro', url('/dashboard'))
            ->line('Si vous avez la moindre question, notre équipe est disponible par email ou téléphone.')
            ->salutation("Cordialement,\nL'équipe IBIG FactPro — factpro.ibigsoft.com\nSupport : support@ibigsoft.com | +225 27 22 27 60 14");
    }
}

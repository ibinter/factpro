<?php

namespace App\Notifications;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LicenseSuspended extends Notification
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
            ->subject('Votre licence IBIG FactPro a été suspendue')
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
            ->line("Votre licence IBIG FactPro (clé {$this->license->license_key}) a été suspendue à l'issue de la période de tolérance.")
            ->line("L'accès à votre espace de facturation est désormais restreint. Soyez rassuré : l'ensemble de vos données (clients, produits, documents) est conservé en toute sécurité.")
            ->action('Réactiver mon abonnement', url('/billing/plans'))
            ->line("Dès la validation de votre paiement, votre licence sera réactivée et vous retrouverez l'intégralité de vos données.")
            ->salutation("Cordialement,\nL'équipe IBIG FactPro — factpro.ibigsoft.com");
    }
}

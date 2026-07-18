<?php

namespace App\Notifications;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LicenseGracePeriod extends Notification
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
        $date = optional($this->license->grace_period_ends_at)->format('d/m/Y');

        return (new MailMessage)
            ->subject('Votre licence IBIG FactPro est expirée — période de tolérance activée')
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
            ->line("Votre licence IBIG FactPro (clé {$this->license->license_key}) est arrivée à échéance.")
            ->line("Une période de tolérance vous est accordée" . ($date ? " jusqu'au {$date}" : '') . " : votre accès reste actif durant ce délai afin de vous permettre de régulariser votre situation.")
            ->action('Renouveler mon abonnement', url('/billing/plans'))
            ->line("Passé ce délai, votre licence sera automatiquement suspendue et l'accès à votre espace sera restreint.")
            ->salutation("Cordialement,\nL'équipe IBIG FactPro — factpro.ibigsoft.com");
    }
}

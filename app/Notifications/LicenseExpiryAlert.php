<?php

namespace App\Notifications;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LicenseExpiryAlert extends Notification
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
        $date = optional($this->license->ends_at)->format('d/m/Y');
        $jours = $this->days > 1 ? "{$this->days} jours" : '1 jour';

        return (new MailMessage)
            ->subject("Votre licence IBIG FactPro expire dans {$jours}")
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
            ->line("Votre licence IBIG FactPro (clé {$this->license->license_key}) arrive à échéance dans {$jours}" . ($date ? " (le {$date})." : '.'))
            ->line('Pour éviter toute interruption de service, nous vous invitons à renouveler votre abonnement dès maintenant.')
            ->action('Renouveler mon abonnement', url('/billing/plans'))
            ->line("À l'échéance, une période de tolérance vous sera accordée avant la suspension de l'accès.")
            ->salutation("Cordialement,\nL'équipe IBIG FactPro — factpro.ibigsoft.com");
    }
}

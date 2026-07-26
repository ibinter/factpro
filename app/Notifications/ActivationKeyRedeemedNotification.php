<?php

namespace App\Notifications;

use App\Models\ActivationKey;
use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivationKeyRedeemedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ActivationKey $key,
        public License $license,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ends = $this->license->ends_at?->format('d/m/Y') ?? '—';

        return (new MailMessage)
            ->subject('Votre clé d\'activation IBIG FactPro a été activée')
            ->greeting('Bonjour ' . ($notifiable->name ?? '') . ',')
            ->line('Votre clé d\'activation **' . $this->key->code . '** a bien été enregistrée.')
            ->line('Plan activé : **' . ($this->key->plan->name ?? '—') . '**')
            ->line('Valide jusqu\'au : **' . $ends . '**')
            ->action('Accéder à mon compte', url('/billing'))
            ->line('Merci de votre confiance — L\'équipe IBIG Soft.');
    }
}

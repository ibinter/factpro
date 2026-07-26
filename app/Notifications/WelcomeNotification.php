<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenue sur IBIG FactPro !')
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Votre compte IBIG FactPro est prêt. Vous disposez d'un essai gratuit de 7 jours pour découvrir toutes les fonctionnalités.")
            ->line('**Ce que vous pouvez faire dès maintenant :**')
            ->line('✅ Créer votre première facture')
            ->line('✅ Importer vos clients existants')
            ->line('✅ Configurer votre logo et vos informations légales')
            ->action('Accéder à mon espace FactPro', url('/dashboard'))
            ->line('Notre assistante SARA est disponible 24h/24 pour vous aider.')
            ->salutation("L'équipe IBIG Soft\nsupport@ibigsoft.com | +225 27 22 27 60 14");
    }
}

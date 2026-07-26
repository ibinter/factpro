<?php

namespace App\Notifications;

use App\Models\AppVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewVersionReleased extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly AppVersion $version) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    // ── Mail ─────────────────────────────────────────────────────────────────

    public function toMail(object $notifiable): MailMessage
    {
        $planLabel = null;
        if (! is_null($this->version->target_plans)) {
            $planLabel = 'votre formule';
        }

        return (new MailMessage)
            ->subject("IBIG FactPro {$this->version->version} est disponible — Nouveautés")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("**{$this->version->title}**")
            ->line("Une nouvelle version de IBIG FactPro est maintenant disponible" . ($planLabel ? " pour {$planLabel}" : '') . " :")
            ->line($this->version->description)
            ->action('Ouvrir mon espace', url('/'))
            ->line('Retrouvez le détail complet des changements dans les [notes de version](' . url('/nouveautes') . ').')
            ->salutation("L'équipe IBIG Soft");
    }

    // ── Database ─────────────────────────────────────────────────────────────

    public function toDatabase(object $notifiable): array
    {
        return [
            'version' => $this->version->version,
            'title'   => $this->version->title,
            'message' => "IBIG FactPro {$this->version->version} est disponible : {$this->version->title}",
            'link'    => '/nouveautes',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}

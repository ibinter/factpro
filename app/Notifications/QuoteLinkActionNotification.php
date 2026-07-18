<?php

namespace App\Notifications;

use App\Models\QuoteLink;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuoteLinkActionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly QuoteLink $link,
        public readonly string $event, // 'signed' | 'declined'
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $doc  = $this->link->document;
        $name = $this->link->client_name ?? 'Un client';

        if ($this->event === 'signed') {
            return (new MailMessage)
                ->subject('✅ Devis '.$doc->number.' accepté par '.$name)
                ->greeting('Bonne nouvelle !')
                ->line($name.' a accepté le devis '.$doc->number.'.')
                ->line('Date : '.now()->format('d/m/Y H:i'));
        }

        return (new MailMessage)
            ->subject('❌ Devis '.$doc->number.' refusé par '.$name)
            ->greeting('Refus reçu.')
            ->line($name.' a refusé le devis '.$doc->number.'.')
            ->line('Motif : '.($this->link->decline_reason ?? '—'));
    }

    public function toArray(object $notifiable): array
    {
        $doc = $this->link->document;

        return [
            'event'          => $this->event,
            'document_id'    => $doc->id,
            'document_number'=> $doc->number,
            'client_name'    => $this->link->client_name,
            'client_email'   => $this->link->client_email,
            'decline_reason' => $this->link->decline_reason,
        ];
    }
}

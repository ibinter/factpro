<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class QuoteSignedNotification extends Notification
{
    use Queueable;

    public function __construct(private Document $document) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $customerName = $this->document->customer?->name ?? 'Client inconnu';

        return [
            'type' => 'quote_signed',
            'title' => "Devis {$this->document->number} signé",
            'message' => "Le client {$customerName} a signé le devis",
            'url' => "/documents/{$this->document->id}",
            'document_id' => $this->document->id,
            'icon' => '✍️',
        ];
    }
}

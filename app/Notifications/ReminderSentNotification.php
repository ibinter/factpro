<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReminderSentNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Document $document,
        private int $days,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $customerName = $this->document->customer?->name ?? 'Client inconnu';

        return [
            'type' => 'reminder_sent',
            'title' => 'Relance envoyée',
            'message' => "Relance J+{$this->days} pour {$customerName} ({$this->document->number})",
            'url' => "/documents/{$this->document->id}",
            'document_id' => $this->document->id,
            'icon' => '📧',
        ];
    }
}

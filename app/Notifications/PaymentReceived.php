<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;

    public function __construct(
        private Document $document,
        private float $amount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $amount = number_format($this->amount, 2, ',', ' ').' '.$this->document->currency;

        return [
            'type' => 'payment_received',
            'title' => "Paiement reçu — {$this->document->number}",
            'message' => "{$amount} reçu le ".now()->format('d/m/Y'),
            'url' => "/documents/{$this->document->id}",
            'document_id' => $this->document->id,
            'icon' => '💰',
        ];
    }
}

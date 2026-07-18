<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentFinalized extends Notification
{
    use Queueable;

    public function __construct(private Document $document) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $total = number_format((float) $this->document->total, 2, ',', ' ').' '.$this->document->currency;

        return [
            'type' => 'document_finalized',
            'title' => "Facture {$this->document->number} finalisée",
            'message' => "Montant : {$total}",
            'url' => "/documents/{$this->document->id}",
            'document_id' => $this->document->id,
            'icon' => '📄',
        ];
    }
}

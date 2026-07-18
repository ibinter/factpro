<?php

namespace App\Mail;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Relance d'une facture impayée — 3 tons selon le niveau d'escalade :
 * 1 courtois, 2 ferme, 3 mise en demeure (cahier des charges §13).
 * Le sujet ({number} déjà remplacé) et le ton arrivent via $levelConfig.
 */
class InvoiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array{days:int, tone:string, subject:string} $levelConfig
     */
    public function __construct(
        public Document $document,
        public int $level,
        public array $levelConfig,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->levelConfig['subject'],
        );
    }

    public function content(): Content
    {
        $document = $this->document->loadMissing(['customer', 'company']);

        $daysLate = $document->due_date
            ? max(0, (int) $document->due_date->startOfDay()->diffInDays(today()))
            : 0;

        return new Content(
            view: 'emails.reminder',
            with: [
                'document' => $document,
                'company' => $document->company,
                'level' => $this->level,
                'tone' => $this->levelConfig['tone'],
                'daysLate' => $daysLate,
            ],
        );
    }
}

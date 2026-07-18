<?php

namespace App\Mail;

use App\Models\Document;
use App\Services\QrCodeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Envoi d'un document commercial (devis, facture…) au client,
 * avec le PDF scellé (QR anti-falsification) en pièce jointe.
 * Envoi synchrone pour l'instant (pas de ShouldQueue).
 */
class DocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Document $document,
        public ?string $messageText = null,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->document->type_label.' '.$this->document->number.' — '.$this->document->company->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document',
            with: [
                'document' => $this->document,
                'company' => $this->document->company,
                'messageText' => $this->messageText,
            ],
        );
    }

    /**
     * PDF généré exactement comme DocumentController@pdf (même vue, mêmes paramètres).
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $document = $this->document->loadMissing(['lines', 'customer', 'company']);

        $pdf = Pdf::loadView('pdf.document', [
            'document' => $document,
            'company' => $document->company,
            'qrDataUri' => app(QrCodeService::class)->forDocument($document),
            'watermark' => $document->trial_watermark ? config('factpro.trial.watermark_text') : null,
        ])->setPaper('a4');

        return [
            Attachment::fromData(fn () => $pdf->output(), $document->number.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}

<?php

namespace App\Mail;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProvisionalLicenseActivated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public License $license,
    ) {}

    public function envelope(): Envelope
    {
        $date = $this->license->ends_at?->format('d/m/Y') ?? '—';

        return new Envelope(
            subject: "⚡ Accès provisoire accordé jusqu'au {$date}",
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlView: 'emails.payment.provisional-activated',
            textView: 'emails.payment.provisional-activated-text',
        );
    }
}

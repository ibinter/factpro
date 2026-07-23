<?php

namespace App\Mail;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LicenseExpiringSoon extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public License $license,
        public int $daysLeft,
    ) {}

    public function envelope(): Envelope
    {
        $jours = $this->daysLeft > 1 ? "{$this->daysLeft} jours" : '1 jour';

        return new Envelope(
            subject: "â° Votre licence expire dans {$jours}",
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlView: 'emails.payment.license-expiring-soon',
            textView: 'emails.payment.license-expiring-soon-text',
        );
    }
}

<?php

namespace App\Mail;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LicenseExpired extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public License $license,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔴 Votre licence FactPro a expiré',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlView: 'emails.payment.license-expired',
            textView: 'emails.payment.license-expired-text',
        );
    }
}

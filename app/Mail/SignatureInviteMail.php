<?php

namespace App\Mail;

use App\Models\QualifiedSignature;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SignatureInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly QualifiedSignature $signature,
        public readonly string $documentName,
        public readonly string $emitterName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Invitation à signer un document — {$this->documentName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.signature-invite',
        );
    }
}

<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountLockedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $reason = 'multiple_failed_logins',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔒 Votre compte a été temporairement verrouillé',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-locked',
        );
    }
}

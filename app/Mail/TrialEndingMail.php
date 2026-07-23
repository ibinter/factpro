<?php

namespace App\Mail;

use App\Models\License;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialEndingMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public License $license,
        public int $daysLeft,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⏳ Votre essai gratuit se termine dans {$this->daysLeft} jours",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trial-ending',
        );
    }
}

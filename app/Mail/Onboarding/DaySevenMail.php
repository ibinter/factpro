<?php

namespace App\Mail\Onboarding;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DaySevenMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚀 7 jours avec FactPro — avez-vous essayé ces fonctionnalités ?',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.onboarding.day-seven',
        );
    }
}

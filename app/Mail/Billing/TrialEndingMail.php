<?php

namespace App\Mail\Billing;

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
        public int $days_left,
        public string $expires_at,
        public int $docs_count = 0,
        public int $customers_count = 0
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "[FactPro] Votre essai gratuit expire dans {$this->days_left} jour(s)");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.billing.trial-ending', with: [
            'user'            => $this->user,
            'days_left'       => $this->days_left,
            'expires_at'      => $this->expires_at,
            'docs_count'      => $this->docs_count,
            'customers_count' => $this->customers_count,
        ]);
    }
}

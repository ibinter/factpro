<?php

namespace App\Mail;

use App\Models\FraudAlert;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FraudAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly int $score,
        public readonly array $flags,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[FRAUDE] Score ' . $this->score . ' â€” commande #' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.fraud-alert',
        );
    }

    /** Fallback si la vue n'existe pas (tests). */
    public function build(): static
    {
        return $this
            ->subject('[FRAUDE] Score ' . $this->score . ' â€” commande #' . $this->order->order_number)
            ->text('emails.fraud-alert')
            ->with([
                'order'  => $this->order,
                'score'  => $this->score,
                'flags'  => $this->flags,
            ]);
    }
}

<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProofComplementRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $complementNote,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Complément d\'information requis — ' . $this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlView: 'emails.payment.complement-requested',
            textView: 'emails.payment.complement-requested-text',
        );
    }
}

<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentProofReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public PaymentTransaction $transaction,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Preuve de paiement reçue — ' . ($this->order->plan?->name ?? $this->order->order_number),
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlView: 'emails.payment.proof-received',
            textView: 'emails.payment.proof-received-text',
        );
    }
}

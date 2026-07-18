<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public PaymentTransaction $transaction,
        public string $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Preuve de paiement refusée',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlView: 'emails.payment.payment-rejected',
            textView: 'emails.payment.payment-rejected-text',
        );
    }
}

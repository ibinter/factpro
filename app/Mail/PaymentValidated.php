<?php

namespace App\Mail;

use App\Models\License;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentValidated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public License $license,
        public PaymentTransaction $transaction,
        public ?string $receiptPath = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Paiement confirmé — Licence activée !',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlView: 'emails.payment.payment-validated',
            textView: 'emails.payment.payment-validated-text',
        );
    }

    public function attachments(): array
    {
        if ($this->receiptPath && file_exists($this->receiptPath)) {
            return [
                Attachment::fromPath($this->receiptPath)
                    ->as('recu-' . $this->order->order_number . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }

        return [];
    }
}

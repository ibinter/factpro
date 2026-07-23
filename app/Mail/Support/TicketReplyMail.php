<?php

namespace App\Mail\Support;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketReplyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public SupportTicket $ticket, public string $reply) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "[FactPro Support] Réponse à votre ticket #{$this->ticket->ticket_number}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.support.ticket-reply');
    }
}

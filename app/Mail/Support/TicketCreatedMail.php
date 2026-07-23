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

class TicketCreatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public SupportTicket $ticket) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "[FactPro Support] Ticket #{$this->ticket->ticket_number} créé");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.support.ticket-created');
    }
}

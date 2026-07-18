<?php

namespace App\Mail;

use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Invitation à rejoindre l'équipe d'une société sur IBIG FactPro (design marine/or).
 */
class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public TeamInvitation $invitation,
    ) {
    }

    public function envelope(): Envelope
    {
        $company = $this->invitation->company;

        return new Envelope(
            subject: 'Vous êtes invité(e) à rejoindre '.$company->name.' sur IBIG FactPro',
        );
    }

    public function content(): Content
    {
        $invitation = $this->invitation->loadMissing(['company', 'inviter']);

        $roleLabels = [
            'admin' => 'Administrateur',
            'member' => 'Membre',
            'cashier' => 'Caissier',
        ];

        return new Content(
            view: 'emails.team-invitation',
            with: [
                'invitation' => $invitation,
                'company' => $invitation->company,
                'roleLabel' => $roleLabels[$invitation->role] ?? $invitation->role,
                'acceptUrl' => route('team.join', $invitation->token),
            ],
        );
    }
}

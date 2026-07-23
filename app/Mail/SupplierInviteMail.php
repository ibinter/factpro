<?php

namespace App\Mail;

use App\Models\SupplierPortalToken;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupplierInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SupplierPortalToken $token) {}

    public function build(): static
    {
        return $this
            ->subject("Demande de prix — {$this->token->company->name}")
            ->view('emails.supplier-invite');
    }
}

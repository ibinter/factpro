@extends('emails.layouts.base', ['preheader' => 'Félicitations ! Vous avez créé votre première facture avec IBIG FactPro.'])

@section('body')
    {{-- Titre --}}
    <h1 style="margin:0 0 8px; font-size:24px; font-weight:700; color:#002D5B;">
        🎊 Félicitations, {{ $user->name }} !
    </h1>
    <p style="margin:0 0 24px; font-size:15px; color:#6b7280;">
        Vous venez de créer votre première facture sur IBIG FactPro. C'est une étape importante !
    </p>

    {{-- CTA --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 32px;">
        <tr>
            <td align="center">
                <a href="{{ url('/invoices') }}"
                   style="display:inline-block; background-color:#0062CC; color:#ffffff; font-size:15px; font-weight:700; text-decoration:none; padding:13px 32px; border-radius:6px;">
                    Voir mes factures →
                </a>
            </td>
        </tr>
    </table>

    {{-- Prochaines étapes --}}
    <p style="margin:0 0 16px; font-size:15px; font-weight:600; color:#1f2937;">Et maintenant ? Voici vos prochaines étapes :</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
        <tr>
            <td style="padding:12px 0; border-bottom:1px solid #e5e7eb; vertical-align:top; width:40px;">
                <span style="display:inline-block; background-color:#dcfce7; color:#16a34a; font-size:16px; width:32px; height:32px; line-height:32px; text-align:center; border-radius:50%;">📤</span>
            </td>
            <td style="padding:12px 0 12px 12px; border-bottom:1px solid #e5e7eb; vertical-align:top;">
                <p style="margin:0 0 2px; font-size:14px; font-weight:600; color:#1f2937;">Envoyez-la à votre client</p>
                <p style="margin:0; font-size:13px; color:#6b7280;">Par e-mail directement depuis FactPro, avec PDF joint et QR code d'authenticité.</p>
            </td>
        </tr>
        <tr>
            <td style="padding:12px 0; border-bottom:1px solid #e5e7eb; vertical-align:top; width:40px;">
                <span style="display:inline-block; background-color:#dbeafe; color:#1d4ed8; font-size:16px; width:32px; height:32px; line-height:32px; text-align:center; border-radius:50%;">💰</span>
            </td>
            <td style="padding:12px 0 12px 12px; border-bottom:1px solid #e5e7eb; vertical-align:top;">
                <p style="margin:0 0 2px; font-size:14px; font-weight:600; color:#1f2937;">Suivez le paiement</p>
                <p style="margin:0; font-size:13px; color:#6b7280;">Marquez la facture comme payée dès réception du règlement. Votre tableau de bord se met à jour en temps réel.</p>
            </td>
        </tr>
        <tr>
            <td style="padding:12px 0; vertical-align:top; width:40px;">
                <span style="display:inline-block; background-color:#fef3c7; color:#d97706; font-size:16px; width:32px; height:32px; line-height:32px; text-align:center; border-radius:50%;">🔔</span>
            </td>
            <td style="padding:12px 0 0 12px; vertical-align:top;">
                <p style="margin:0 0 2px; font-size:14px; font-weight:600; color:#1f2937;">Activez les relances automatiques</p>
                <p style="margin:0; font-size:13px; color:#6b7280;">FactPro peut relancer vos clients automatiquement à J+3, J+7 et J+15 après l'échéance.</p>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:13px; color:#9ca3af; line-height:1.6;">
        Besoin d'aide ? Consultez notre
        <a href="{{ url('/help') }}" style="color:#0062CC; text-decoration:none;">centre d'aide</a>
        ou contactez <a href="mailto:support@ibigsoft.com" style="color:#0062CC; text-decoration:none;">support@ibigsoft.com</a>.
    </p>
@endsection

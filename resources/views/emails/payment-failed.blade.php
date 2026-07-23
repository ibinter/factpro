@extends('emails.layouts.base', ['preheader' => 'Action requise : le renouvellement de votre abonnement a échoué.'])

@section('body')
    {{-- Alerte --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
        <tr>
            <td style="background-color:#fffbeb; border:2px solid #d97706; border-radius:8px; padding:20px 24px;">
                <p style="margin:0 0 6px; font-size:18px; font-weight:700; color:#92400e;">
                    ⚠️ Échec du paiement — Action requise
                </p>
                <p style="margin:0; font-size:14px; color:#78350f;">
                    Votre paiement d'abonnement IBIG FactPro n'a pas pu être traité.
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 16px; font-size:15px; color:#1f2937;">Bonjour {{ $user->name }},</p>

    <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#374151;">
        Nous n'avons pas pu débiter votre moyen de paiement pour le renouvellement de votre abonnement
        <strong>IBIG FactPro</strong>. Sans action de votre part, votre accès pourrait être suspendu.
    </p>

    {{-- Détails licence --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; border:1px solid #e5e7eb; border-radius:6px;">
        <tr>
            <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Licence</td>
            <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#002D5B; border-bottom:1px solid #e5e7eb;">
                {{ $license->plan?->name ?? 'Abonnement FactPro' }}
            </td>
        </tr>
        <tr>
            <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Statut</td>
            <td align="right" style="padding:12px 18px;">
                <span style="display:inline-block; background-color:#fee2e2; color:#dc2626; font-size:12px; font-weight:600; padding:3px 10px; border-radius:12px;">Paiement échoué</span>
            </td>
        </tr>
        @if ($license->ends_at)
        <tr>
            <td style="padding:12px 18px; font-size:14px; color:#6b7280;">Expiration</td>
            <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#dc2626;">
                {{ $license->ends_at->format('d/m/Y') }}
            </td>
        </tr>
        @endif
    </table>

    {{-- CTA --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 28px;">
        <tr>
            <td align="center">
                <a href="{{ url('/billing') }}"
                   style="display:inline-block; background-color:#F0C040; color:#002D5B; font-size:15px; font-weight:700; text-decoration:none; padding:13px 32px; border-radius:6px;">
                    Mettre à jour mon moyen de paiement →
                </a>
            </td>
        </tr>
    </table>

    {{-- Info retentatives --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="background-color:#f8fafc; border-left:4px solid #6b7280; padding:14px 18px; border-radius:0 6px 6px 0;">
                <p style="margin:0; font-size:13px; color:#374151; line-height:1.6;">
                    🔄 Nous effectuerons <strong>3 nouvelles tentatives</strong> automatiques de débit dans les prochains jours.
                    Pour éviter toute interruption de service, veuillez mettre à jour votre moyen de paiement dès que possible.
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:16px 0 0; font-size:13px; color:#9ca3af; line-height:1.6;">
        Des questions ? Contactez <a href="mailto:support@ibigsoft.com" style="color:#0062CC; text-decoration:none;">support@ibigsoft.com</a>.
    </p>
@endsection

@extends('emails.layouts.base', ['preheader' => 'Alerte sécurité : votre compte a été temporairement verrouillé.'])

@section('body')
    {{-- Alerte --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
        <tr>
            <td style="background-color:#fff7ed; border:2px solid #ea580c; border-radius:8px; padding:20px 24px;">
                <p style="margin:0 0 6px; font-size:18px; font-weight:700; color:#c2410c;">
                    🔒 Compte temporairement verrouillé
                </p>
                <p style="margin:0; font-size:14px; color:#7c2d12;">
                    Une activité suspecte a été détectée sur votre compte IBIG FactPro.
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 16px; font-size:15px; color:#1f2937;">Bonjour {{ $user->name }},</p>

    <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#374151;">
        Votre compte a été <strong>temporairement verrouillé</strong> suite à
        @if ($reason === 'multiple_failed_logins')
            <strong>5 tentatives de connexion échouées consécutives</strong>.
        @else
            une activité inhabituelle détectée sur votre compte.
        @endif
    </p>

    <p style="margin:0 0 24px; font-size:15px; line-height:1.6; color:#374151;">
        Ce verrouillage est automatique et vise à protéger votre compte contre les accès non autorisés.
        Pour y accéder à nouveau, réinitialisez votre mot de passe.
    </p>

    {{-- CTA --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 28px;">
        <tr>
            <td align="center">
                <a href="{{ route('password.request') }}"
                   style="display:inline-block; background-color:#ea580c; color:#ffffff; font-size:15px; font-weight:700; text-decoration:none; padding:13px 32px; border-radius:6px;">
                    Réinitialiser mon mot de passe →
                </a>
            </td>
        </tr>
    </table>

    {{-- Avertissement --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 8px;">
        <tr>
            <td style="background-color:#fef2f2; border-left:4px solid #dc2626; padding:14px 18px; border-radius:0 6px 6px 0;">
                <p style="margin:0; font-size:13px; color:#7f1d1d; line-height:1.6;">
                    ⚠️ <strong>Si vous n'êtes pas à l'origine de cette tentative</strong>, contactez-nous immédiatement à
                    <a href="mailto:security@ibigsoft.com" style="color:#dc2626; text-decoration:none; font-weight:600;">security@ibigsoft.com</a>.
                    Nous vous aiderons à sécuriser votre compte.
                </p>
            </td>
        </tr>
    </table>

    <p style="margin:16px 0 0; font-size:12px; color:#9ca3af; line-height:1.6;">
        Cet e-mail a été envoyé automatiquement le {{ now()->format('d/m/Y à H:i') }} suite à une activité détectée sur votre compte.
    </p>
@endsection

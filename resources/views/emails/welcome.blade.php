@extends('emails.layouts.base', ['preheader' => 'Bienvenue ! Votre compte est prêt. Commencez dès maintenant.'])

@section('body')
    {{-- Titre de bienvenue --}}
    <h1 style="margin:0 0 8px; font-size:24px; font-weight:700; color:#002D5B;">
        Bienvenue, {{ $user->name }} ! 🎉
    </h1>
    <p style="margin:0 0 24px; font-size:15px; color:#6b7280;">Votre compte IBIG FactPro est prêt.</p>

    {{-- Bannière essai --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 28px;">
        <tr>
            <td style="background-color:#fffbeb; border:1px solid #F0C040; border-radius:8px; padding:16px 20px;">
                <p style="margin:0; font-size:14px; color:#92400e; font-weight:600;">
                    ⏱ Votre essai gratuit de 7 jours est actif. Pas de carte bancaire requise.
                </p>
            </td>
        </tr>
    </table>

    {{-- CTA principal --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 32px;">
        <tr>
            <td align="center">
                <a href="{{ url('/dashboard') }}"
                   style="display:inline-block; background-color:#0062CC; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; padding:14px 36px; border-radius:6px; letter-spacing:0.2px;">
                    Accéder à mon espace →
                </a>
            </td>
        </tr>
    </table>

    {{-- 3 étapes de démarrage --}}
    <p style="margin:0 0 16px; font-size:15px; font-weight:600; color:#1f2937;">Pour démarrer en 3 étapes :</p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 28px;">
        {{-- Étape 1 --}}
        <tr>
            <td style="padding:12px 0; border-bottom:1px solid #e5e7eb; vertical-align:top; width:40px;">
                <span style="display:inline-block; background-color:#002D5B; color:#F0C040; font-size:13px; font-weight:700; width:28px; height:28px; line-height:28px; text-align:center; border-radius:50%;">1</span>
            </td>
            <td style="padding:12px 0 12px 12px; border-bottom:1px solid #e5e7eb; vertical-align:top;">
                <p style="margin:0 0 2px; font-size:14px; font-weight:600; color:#1f2937;">🏢 Configurez votre société</p>
                <p style="margin:0; font-size:13px; color:#6b7280;">Nom, logo, adresse, coordonnées — en moins de 2 minutes.</p>
            </td>
        </tr>
        {{-- Étape 2 --}}
        <tr>
            <td style="padding:12px 0; border-bottom:1px solid #e5e7eb; vertical-align:top; width:40px;">
                <span style="display:inline-block; background-color:#002D5B; color:#F0C040; font-size:13px; font-weight:700; width:28px; height:28px; line-height:28px; text-align:center; border-radius:50%;">2</span>
            </td>
            <td style="padding:12px 0 12px 12px; border-bottom:1px solid #e5e7eb; vertical-align:top;">
                <p style="margin:0 0 2px; font-size:14px; font-weight:600; color:#1f2937;">👥 Créez votre premier client</p>
                <p style="margin:0; font-size:13px; color:#6b7280;">Importez depuis un fichier ou saisissez manuellement.</p>
            </td>
        </tr>
        {{-- Étape 3 --}}
        <tr>
            <td style="padding:12px 0; vertical-align:top; width:40px;">
                <span style="display:inline-block; background-color:#002D5B; color:#F0C040; font-size:13px; font-weight:700; width:28px; height:28px; line-height:28px; text-align:center; border-radius:50%;">3</span>
            </td>
            <td style="padding:12px 0 0 12px; vertical-align:top;">
                <p style="margin:0 0 2px; font-size:14px; font-weight:600; color:#1f2937;">📄 Émettez votre première facture</p>
                <p style="margin:0; font-size:13px; color:#6b7280;">Envoyez-la par e-mail avec un QR code d'authenticité inclus.</p>
            </td>
        </tr>
    </table>

    {{-- Support --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="background-color:#f8fafc; border-left:4px solid #0062CC; padding:14px 18px; border-radius:0 6px 6px 0;">
                <p style="margin:0; font-size:13px; color:#374151; line-height:1.6;">
                    💬 <strong>Une question ?</strong> Écrivez à
                    <a href="mailto:support@ibigsoft.com" style="color:#0062CC; text-decoration:none;">support@ibigsoft.com</a>
                    ou ouvrez un chat avec <strong>SARA</strong>, votre assistante IA intégrée.
                </p>
            </td>
        </tr>
    </table>
@endsection

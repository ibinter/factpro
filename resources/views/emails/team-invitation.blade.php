<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation à rejoindre {{ $company->name }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:'Segoe UI', Helvetica, Arial, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.1);">

                    {{-- En-tête bleu marine IBIG --}}
                    <tr>
                        <td style="background-color:#002D5B; padding:28px 32px; border-bottom:4px solid #F0C040;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="color:#ffffff; font-size:20px; font-weight:bold;">
                                        {{ $company->name }}
                                    </td>
                                    <td align="right" style="color:#F0C040; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">
                                        Invitation
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Corps --}}
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                Bonjour,
                            </p>

                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                @if ($invitation->inviter)<strong>{{ $invitation->inviter->name }}</strong> vous invite@else Vous êtes invité(e) @endif
                                à rejoindre l'équipe de <strong>{{ $company->name }}</strong> sur IBIG FactPro
                                en tant que <strong style="color:#0062CC;">{{ $roleLabel }}</strong>.
                            </p>

                            {{-- Bouton d'acceptation --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $acceptUrl }}"
                                           style="display:inline-block; background-color:#0062CC; color:#ffffff; font-size:15px; font-weight:600; text-decoration:none; padding:14px 34px; border-radius:6px;">
                                            Rejoindre l'équipe
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px; font-size:13px; line-height:1.6; color:#6b7280;">
                                Cette invitation expire le
                                <strong>{{ $invitation->expires_at->format('d/m/Y') }}</strong>
                                (7 jours). Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :
                            </p>
                            <p style="margin:0; font-size:12px; line-height:1.6; word-break:break-all;">
                                <a href="{{ $acceptUrl }}" style="color:#0062CC;">{{ $acceptUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    {{-- Pied de page --}}
                    <tr>
                        <td style="background-color:#002D5B; padding:18px 32px;" align="center">
                            <p style="margin:0; font-size:12px; color:#93b3d4;">
                                Propulsé par <span style="color:#F0C040; font-weight:600;">IBIG FactPro</span>
                                — <a href="https://factpro.ibigsoft.com" style="color:#93b3d4; text-decoration:underline;">factpro.ibigsoft.com</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre licence FactPro a expiré</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:'Segoe UI', Helvetica, Arial, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.1);">

                    {{-- En-tête --}}
                    <tr>
                        <td style="background-color:#002D5B; padding:28px 32px; border-bottom:4px solid #dc2626;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="color:#ffffff; font-size:20px; font-weight:bold;">IBIG FactPro</td>
                                    <td align="right" style="color:#93b3d4; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">Licence expirée</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Bannière expiration --}}
                    <tr>
                        <td style="background-color:#fef2f2; padding:16px 32px; border-bottom:1px solid #fecaca;">
                            <p style="margin:0; font-size:15px; font-weight:600; color:#dc2626;">🔴 Votre licence FactPro a expiré</p>
                        </td>
                    </tr>

                    {{-- Corps --}}
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                Votre licence <strong>{{ $license->plan?->name ?? 'IBIG FactPro' }}</strong>
                                a expiré le <strong>{{ $license->ends_at?->format('d/m/Y') ?? '—' }}</strong>.
                            </p>

                            <p style="margin:0 0 24px; font-size:15px; line-height:1.6;">
                                Bonne nouvelle : <strong>vos données sont conservées</strong>.
                                Renouvelez votre abonnement pour retrouver l'accès complet à votre espace de facturation.
                            </p>

                            {{-- CTA --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/billing/plans') }}"
                                           style="display:inline-block; background-color:#0062CC; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; padding:14px 32px; border-radius:6px;">
                                            Renouveler mon abonnement
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px; font-size:13px; line-height:1.6; color:#6b7280;">
                                Si vous avez des questions, contactez notre support à
                                <a href="mailto:support@ibigsoft.com" style="color:#0062CC;">support@ibigsoft.com</a>.
                            </p>

                            <p style="margin:16px 0 0; font-size:14px; line-height:1.6; color:#374151;">
                                Cordialement,<br>
                                <strong>L'équipe IBIG FactPro</strong>
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

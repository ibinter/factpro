<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preuve de paiement refusée</title>
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
                                    <td align="right" style="color:#93b3d4; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">Paiement</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Bannière erreur --}}
                    <tr>
                        <td style="background-color:#fef2f2; padding:16px 32px; border-bottom:1px solid #fecaca;">
                            <p style="margin:0; font-size:15px; font-weight:600; color:#dc2626;">❌ Preuve de paiement refusée</p>
                        </td>
                    </tr>

                    {{-- Corps --}}
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                Après vérification, votre preuve de paiement pour la commande
                                <strong>{{ $order->order_number }}</strong> n'a pas pu être validée.
                            </p>

                            {{-- Motif --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; background-color:#fef2f2; border:1px solid #fecaca; border-radius:6px;">
                                <tr>
                                    <td style="padding:14px 18px;">
                                        <p style="margin:0 0 6px; font-size:13px; font-weight:700; color:#dc2626; text-transform:uppercase; letter-spacing:0.5px;">Motif du refus</p>
                                        <p style="margin:0; font-size:14px; color:#7f1d1d; line-height:1.6;">{{ $reason }}</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6; font-weight:600;">Que faire ?</p>
                            <ul style="margin:0 0 24px; padding-left:20px; font-size:14px; line-height:2;">
                                <li>Vérifiez les informations transmises (référence de transaction, montant, justificatif).</li>
                                <li>Soumettez une nouvelle preuve de paiement correcte.</li>
                                <li>Ou contactez notre support si vous avez des questions.</li>
                            </ul>

                            {{-- CTA --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/billing/checkout/' . $order->id) }}"
                                           style="display:inline-block; background-color:#0062CC; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; padding:14px 32px; border-radius:6px;">
                                            Soumettre une nouvelle preuve
                                        </a>
                                    </td>
                                </tr>
                            </table>

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

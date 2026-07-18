<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preuve de paiement reçue</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:'Segoe UI', Helvetica, Arial, sans-serif; color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.1);">

                    {{-- En-tête --}}
                    <tr>
                        <td style="background-color:#002D5B; padding:28px 32px; border-bottom:4px solid #0062CC;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="color:#ffffff; font-size:20px; font-weight:bold;">IBIG FactPro</td>
                                    <td align="right" style="color:#93b3d4; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">Paiement</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Bannière statut --}}
                    <tr>
                        <td style="background-color:#f0fdf4; padding:16px 32px; border-bottom:1px solid #bbf7d0;">
                            <p style="margin:0; font-size:15px; font-weight:600; color:#15803d;">✅ Preuve de paiement reçue</p>
                        </td>
                    </tr>

                    {{-- Corps --}}
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">Bonjour,</p>

                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                Nous avons bien reçu votre preuve de paiement pour l'abonnement
                                <strong>{{ $order->plan?->name ?? $order->order_number }}</strong>.
                                Notre équipe la vérifiera dans les <strong>24 à 48 heures ouvrables</strong>.
                            </p>

                            {{-- Encart important --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; background-color:#fefce8; border:1px solid #fde68a; border-radius:6px;">
                                <tr>
                                    <td style="padding:14px 18px; font-size:14px; color:#92400e; line-height:1.6;">
                                        ⚠️ <strong>NE PAS RENVOYER</strong> votre preuve — elle est déjà enregistrée dans notre système.
                                    </td>
                                </tr>
                            </table>

                            {{-- Récapitulatif --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; border:1px solid #e5e7eb; border-radius:6px;">
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Référence commande</td>
                                    <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#002D5B; border-bottom:1px solid #e5e7eb;">{{ $order->order_number }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Référence transaction</td>
                                    <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb;">{{ $transaction->internal_reference }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Forfait</td>
                                    <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb;">{{ $order->plan?->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280;">Montant déclaré</td>
                                    <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#002D5B;">
                                        {{ $transaction->amount_declared ? number_format((float) $transaction->amount_declared, 0, ',', ' ') . ' ' . $transaction->currency : '—' }}
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px; font-size:14px; line-height:1.6; color:#374151;">
                                Vous recevrez un email de confirmation dès que la validation sera effectuée.
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

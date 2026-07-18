<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement confirmé — Licence activée</title>
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
                                    <td align="right" style="color:#93b3d4; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:1px;">Confirmation</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Bannière succès --}}
                    <tr>
                        <td style="background-color:#f0fdf4; padding:20px 32px; border-bottom:1px solid #bbf7d0; text-align:center;">
                            <p style="margin:0; font-size:22px; font-weight:700; color:#15803d;">🎉 Félicitations !</p>
                            <p style="margin:6px 0 0; font-size:14px; color:#166534;">Votre paiement a été confirmé et votre licence est désormais active.</p>
                        </td>
                    </tr>

                    {{-- Corps --}}
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                Votre abonnement <strong>{{ $license->plan?->name ?? '—' }}</strong> est maintenant actif.
                            </p>

                            {{-- Récapitulatif licence --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; border:1px solid #e5e7eb; border-radius:6px;">
                                <tr>
                                    <td colspan="2" style="padding:12px 18px; font-size:13px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px; background-color:#f9fafb; border-bottom:1px solid #e5e7eb;">Détails de la licence</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Forfait</td>
                                    <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#002D5B; border-bottom:1px solid #e5e7eb;">{{ $license->plan?->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Clé de licence</td>
                                    <td align="right" style="padding:12px 18px; font-size:13px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb; font-family:monospace;">{{ $license->license_key }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Début</td>
                                    <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb;">{{ $license->starts_at?->format('d/m/Y') ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Expiration</td>
                                    <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb;">{{ $license->ends_at?->format('d/m/Y') ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280;">Référence commande</td>
                                    <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#002D5B;">{{ $order->order_number }}</td>
                                </tr>
                            </table>

                            {{-- CTA --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/billing') }}"
                                           style="display:inline-block; background-color:#0062CC; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; padding:14px 32px; border-radius:6px;">
                                            Accéder à mon espace client
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            @if($receiptPath && file_exists($receiptPath))
                            <p style="margin:0 0 8px; font-size:13px; line-height:1.6; color:#6b7280;">
                                📎 Votre reçu de paiement est joint à cet email.
                            </p>
                            @endif

                            <p style="margin:16px 0 0; font-size:14px; line-height:1.6; color:#374151;">
                                Merci de votre confiance.<br>
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

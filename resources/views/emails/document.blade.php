<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->type_label }} {{ $document->number }}</title>
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
                                        {{ $document->type_label }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Corps --}}
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                Bonjour{{ $document->customer?->name ? ' '.$document->customer->name : '' }},
                            </p>

                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                Veuillez trouver ci-joint votre document
                                <strong>{{ strtolower($document->type_label) }} n° {{ $document->number }}</strong>
                                émis par {{ $company->name }}.
                            </p>

                            @if (!empty($messageText))
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
                                    <tr>
                                        <td style="background-color:#f8fafc; border-left:4px solid #F0C040; padding:14px 18px; font-size:14px; line-height:1.6; color:#374151; border-radius:0 6px 6px 0;">
                                            {!! nl2br(e($messageText)) !!}
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            {{-- Récapitulatif --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; border:1px solid #e5e7eb; border-radius:6px;">
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Document</td>
                                    <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#002D5B; border-bottom:1px solid #e5e7eb;">
                                        {{ $document->type_label }} {{ $document->number }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Date d'émission</td>
                                    <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb;">
                                        {{ $document->issue_date?->format('d/m/Y') }}
                                    </td>
                                </tr>
                                @if ($document->due_date)
                                    <tr>
                                        <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Échéance</td>
                                        <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb;">
                                            {{ $document->due_date->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:14px 18px; font-size:15px; font-weight:bold; color:#002D5B; background-color:#f8fafc;">Total TTC</td>
                                    <td align="right" style="padding:14px 18px; font-size:18px; font-weight:bold; color:#002D5B; background-color:#f8fafc;">
                                        {{ number_format((float) $document->total, 0, ',', ' ') }} {{ $document->currency }}
                                    </td>
                                </tr>
                            </table>

                            {{-- Bouton de vérification d'authenticité --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $document->verificationUrl() }}"
                                           style="display:inline-block; background-color:#0062CC; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; padding:12px 28px; border-radius:6px;">
                                            🔒 Vérifier l'authenticité du document
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px; font-size:13px; line-height:1.6; color:#6b7280;">
                                Ce document est scellé numériquement (empreinte SHA-256). Le bouton ci-dessus,
                                comme le QR code figurant sur le PDF, permet d'en vérifier l'authenticité à tout moment.
                            </p>

                            @if (!empty($company->invoice_footer))
                                <p style="margin:16px 0 0; font-size:12px; line-height:1.6; color:#9ca3af;">
                                    {{ $company->invoice_footer }}
                                </p>
                            @endif
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

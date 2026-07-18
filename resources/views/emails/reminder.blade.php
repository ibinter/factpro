<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relance — Facture {{ $document->number }}</title>
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
                                        @if ($level === 1) Rappel de paiement
                                        @elseif ($level === 2) Relance de paiement
                                        @else Mise en demeure
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Bandeau rouge — mise en demeure (niveau 3) --}}
                    @if ($level === 3)
                        <tr>
                            <td style="background-color:#dc2626; padding:14px 32px;" align="center">
                                <p style="margin:0; color:#ffffff; font-size:15px; font-weight:bold; text-transform:uppercase; letter-spacing:1px;">
                                    ⚠ Mise en demeure de payer
                                </p>
                            </td>
                        </tr>
                    @endif

                    {{-- Corps --}}
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                Bonjour{{ $document->customer?->name ? ' '.$document->customer->name : '' }},
                            </p>

                            @if ($level === 1)
                                <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                    Sauf erreur de notre part, la facture
                                    <strong>{{ $document->number }}</strong> émise par {{ $company->name }}
                                    demeure impayée à ce jour. Il s'agit peut-être d'un simple oubli —
                                    nous nous permettons donc de vous adresser ce rappel amical.
                                </p>
                                <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                    Si votre règlement est déjà en cours, merci de ne pas tenir compte de ce message.
                                </p>
                            @elseif ($level === 2)
                                <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                    Malgré notre précédent rappel, la facture
                                    <strong>{{ $document->number }}</strong> émise par {{ $company->name }}
                                    reste impayée à ce jour.
                                </p>
                                <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                    Nous vous remercions de bien vouloir <strong>régulariser votre situation sous 72&nbsp;heures</strong>.
                                    Si un règlement a été effectué entre-temps, merci de nous en transmettre la référence.
                                </p>
                            @else
                                <p style="margin:0 0 16px; font-size:15px; line-height:1.6;">
                                    Malgré nos relances successives, la facture
                                    <strong>{{ $document->number }}</strong> émise par {{ $company->name }}
                                    demeure impayée. Par la présente, nous vous mettons formellement
                                    <strong>en demeure de procéder au règlement intégral</strong> du montant restant dû.
                                </p>
                                <p style="margin:0 0 16px; font-size:15px; line-height:1.6; color:#b91c1c; font-weight:600;">
                                    Sans règlement sous 8 jours, nous nous réservons le droit d'engager
                                    toute procédure de recouvrement.
                                </p>
                            @endif

                            {{-- Récapitulatif --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; border:1px solid #e5e7eb; border-radius:6px;">
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Facture</td>
                                    <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#002D5B; border-bottom:1px solid #e5e7eb;">
                                        {{ $document->number }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Date d'émission</td>
                                    <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#1f2937; border-bottom:1px solid #e5e7eb;">
                                        {{ $document->issue_date?->format('d/m/Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 18px; font-size:14px; color:#6b7280; border-bottom:1px solid #e5e7eb;">Échéance</td>
                                    <td align="right" style="padding:12px 18px; font-size:14px; font-weight:600; color:#dc2626; border-bottom:1px solid #e5e7eb;">
                                        {{ $document->due_date?->format('d/m/Y') }}
                                        — dépassée de {{ $daysLate }} jour{{ $daysLate > 1 ? 's' : '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 18px; font-size:15px; font-weight:bold; color:#002D5B; background-color:#f8fafc;">Montant restant dû</td>
                                    <td align="right" style="padding:14px 18px; font-size:18px; font-weight:bold; color:{{ $level === 3 ? '#dc2626' : '#002D5B' }}; background-color:#f8fafc;">
                                        {{ number_format((float) $document->balance_due, 0, ',', ' ') }} {{ $document->currency }}
                                    </td>
                                </tr>
                            </table>

                            {{-- Bouton de consultation / vérification --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $document->verificationUrl() }}"
                                           style="display:inline-block; background-color:#0062CC; color:#ffffff; font-size:14px; font-weight:600; text-decoration:none; padding:12px 28px; border-radius:6px;">
                                            Voir et vérifier la facture
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            {{-- Coordonnées de la société émettrice --}}
                            <p style="margin:0 0 4px; font-size:13px; line-height:1.6; color:#6b7280;">
                                Pour toute question ou pour nous transmettre votre justificatif de règlement&nbsp;:
                            </p>
                            <p style="margin:0; font-size:13px; line-height:1.6; color:#374151;">
                                <strong>{{ $company->name }}</strong>
                                @if (!empty($company->email))<br>Email : {{ $company->email }}@endif
                                @if (!empty($company->phone))<br>Téléphone : {{ $company->phone }}@endif
                                @if (!empty($company->address))<br>{{ $company->address }}@endif
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

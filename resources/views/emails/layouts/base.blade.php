<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IBIG FactPro</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:'Segoe UI', Helvetica, Arial, sans-serif; color:#1f2937;">

    @isset($preheader)
    {{-- Texte de prévisualisation masqué --}}
    <div style="display:none; max-height:0; overflow:hidden; mso-hide:all; visibility:hidden; opacity:0; font-size:1px; color:#f3f4f6;">
        {{ $preheader }}
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>
    @endisset

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f4f6; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,0.1);">

                    {{-- En-tête bleu marine IBIG --}}
                    <tr>
                        <td style="background-color:#002D5B; padding:28px 32px; border-bottom:4px solid #F0C040;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="color:#ffffff; font-size:20px; font-weight:bold; letter-spacing:-0.3px;">
                                        IBIG FactPro
                                        @isset($companyName)
                                            <span style="color:#93b3d4; font-size:14px; font-weight:400; margin-left:8px;">— {{ $companyName }}</span>
                                        @endisset
                                    </td>
                                    <td align="right">
                                        <span style="color:#F0C040; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1.5px;">FactPro</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Corps --}}
                    <tr>
                        <td style="padding:32px;">
                            @yield('body')
                        </td>
                    </tr>

                    {{-- Pied de page --}}
                    <tr>
                        <td style="background-color:#f8fafc; border-top:1px solid #e5e7eb; padding:20px 32px;" align="center">
                            <p style="margin:0 0 8px; font-size:12px; color:#6b7280;">
                                © {{ date('Y') }} IBIG FactPro by IBIG Soft — Tous droits réservés
                            </p>
                            <p style="margin:0; font-size:12px; color:#9ca3af;">
                                <a href="{{ url('/legal/confidentialite') }}" style="color:#6b7280; text-decoration:underline;">Politique de confidentialité</a>
                                &nbsp;·&nbsp;
                                <a href="{{ url('/unsubscribe') }}" style="color:#6b7280; text-decoration:underline;">Se désabonner</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

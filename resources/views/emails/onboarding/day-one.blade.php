@extends('emails.layouts.base')

@section('body')

{{-- Titre --}}
<h1 style="margin:0 0 16px; font-size:22px; font-weight:700; color:#002D5B; line-height:1.3;">
    Bienvenue dans FactPro, {{ $user->name }} ! 🎉
</h1>

<p style="margin:0 0 20px; font-size:15px; color:#374151; line-height:1.6;">
    Vous avez créé votre compte hier. Voici comment créer votre première facture professionnelle en <strong>2 minutes</strong> :
</p>

{{-- Étapes numérotées --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
    <tr>
        <td>
            <!-- Étape 1 -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px;">
                <tr>
                    <td width="40" valign="top">
                        <div style="width:32px; height:32px; background-color:#002D5B; border-radius:50%; text-align:center; line-height:32px; color:#F0C040; font-weight:700; font-size:15px;">1</div>
                    </td>
                    <td valign="top" style="padding-left:12px;">
                        <p style="margin:4px 0 0; font-size:15px; color:#1f2937; line-height:1.5;">
                            <strong>Ajoutez un client</strong> — saisissez son nom, email et adresse en quelques secondes.
                        </p>
                    </td>
                </tr>
            </table>
            <!-- Étape 2 -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px;">
                <tr>
                    <td width="40" valign="top">
                        <div style="width:32px; height:32px; background-color:#002D5B; border-radius:50%; text-align:center; line-height:32px; color:#F0C040; font-weight:700; font-size:15px;">2</div>
                    </td>
                    <td valign="top" style="padding-left:12px;">
                        <p style="margin:4px 0 0; font-size:15px; color:#1f2937; line-height:1.5;">
                            <strong>Ajoutez vos produits / services</strong> — avec prix unitaire, quantité et TVA.
                        </p>
                    </td>
                </tr>
            </table>
            <!-- Étape 3 -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="40" valign="top">
                        <div style="width:32px; height:32px; background-color:#002D5B; border-radius:50%; text-align:center; line-height:32px; color:#F0C040; font-weight:700; font-size:15px;">3</div>
                    </td>
                    <td valign="top" style="padding-left:12px;">
                        <p style="margin:4px 0 0; font-size:15px; color:#1f2937; line-height:1.5;">
                            <strong>Générez la facture en PDF</strong> — téléchargez ou envoyez directement par email à votre client.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- CTA --}}
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:28px 0;">
    <tr>
        <td style="border-radius:6px; background-color:#F0C040;">
            <a href="{{ config('app.url') }}/documents/create?type=invoice"
               style="display:inline-block; padding:14px 28px; font-size:15px; font-weight:700; color:#001d3d; text-decoration:none; border-radius:6px;">
                Créer ma première facture &rarr;
            </a>
        </td>
    </tr>
</table>

{{-- Le saviez-vous ? --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td style="background-color:#f0f7ff; border-left:4px solid #0062CC; border-radius:0 6px 6px 0; padding:16px 20px;">
            <p style="margin:0; font-size:14px; color:#1f2937; line-height:1.6;">
                <strong>💡 Le saviez-vous ?</strong><br>
                FactPro génère automatiquement les numéros de facture selon la norme <strong>OHADA</strong>, garantissant la conformité de vos documents comptables.
            </p>
        </td>
    </tr>
</table>

@endsection

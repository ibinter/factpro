@extends('emails.layouts.base')

@section('body')

{{-- Titre --}}
<h1 style="margin:0 0 16px; font-size:22px; font-weight:700; color:#002D5B; line-height:1.3;">
    Gagnez du temps : importez vos clients en masse 📂
</h1>

<p style="margin:0 0 20px; font-size:15px; color:#374151; line-height:1.6;">
    Vous avez déjà des clients ? Importez-les en un clic depuis <strong>Excel</strong> ou <strong>CSV</strong> et évitez les saisies fastidieuses.
</p>

{{-- Avantages --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px; background-color:#f8fafc; border-radius:8px; padding:20px;">
    <tr>
        <td style="padding:20px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding:8px 0; font-size:15px; color:#1f2937;">
                        <span style="color:#16a34a; font-weight:700; margin-right:8px;">✓</span>
                        <strong>Import CSV / Excel en 1 clic</strong> — aucune installation requise
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 0; font-size:15px; color:#1f2937; border-top:1px solid #e5e7eb;">
                        <span style="color:#16a34a; font-weight:700; margin-right:8px;">✓</span>
                        <strong>Détection automatique des doublons</strong> — votre base reste propre
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 0; font-size:15px; color:#1f2937; border-top:1px solid #e5e7eb;">
                        <span style="color:#16a34a; font-weight:700; margin-right:8px;">✓</span>
                        <strong>Mapping des colonnes intelligent</strong> — FactPro reconnaît vos en-têtes automatiquement
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
            <a href="{{ config('app.url') }}/customers/import"
               style="display:inline-block; padding:14px 28px; font-size:15px; font-weight:700; color:#001d3d; text-decoration:none; border-radius:6px;">
                Importer mes clients &rarr;
            </a>
        </td>
    </tr>
</table>

{{-- Astuce bonus --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td style="background-color:#fff7e6; border-left:4px solid #F0C040; border-radius:0 6px 6px 0; padding:16px 20px;">
            <p style="margin:0; font-size:14px; color:#1f2937; line-height:1.6;">
                <strong>🎯 Astuce pro</strong><br>
                Vous pouvez aussi créer des <strong>devis</strong> et les convertir en facture en <strong>1 clic</strong> — sans ressaisir les informations.
            </p>
        </td>
    </tr>
</table>

@endsection

@extends('emails.layouts.base')

@section('body')

{{-- Titre --}}
<h1 style="margin:0 0 10px; font-size:22px; font-weight:700; color:#002D5B; line-height:1.3;">
    ⏰ Votre essai gratuit se termine dans quelques jours
</h1>

<p style="margin:0 0 24px; font-size:16px; font-weight:700; color:#dc2626;">
    Ne perdez pas accès à vos données !
</p>

{{-- Ce que l'utilisateur perd --}}
<p style="margin:0 0 10px; font-size:14px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.5px;">Sans abonnement, vous perdez :</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px; background-color:#fff5f5; border-radius:8px;">
    <tr>
        <td style="padding:16px 20px;">
            @foreach(['Accès à vos factures et devis existants', 'Export PDF de vos documents', 'Historique de vos clients et transactions', 'Relances automatiques impayées'] as $item)
            <p style="margin:0 0 8px; font-size:14px; color:#7f1d1d; line-height:1.5;">
                <span style="color:#dc2626; font-weight:700; margin-right:8px;">❌</span>{{ $item }}
            </p>
            @endforeach
        </td>
    </tr>
</table>

{{-- Ce qu'il garde avec Starter --}}
<p style="margin:0 0 10px; font-size:14px; font-weight:700; color:#374151; text-transform:uppercase; letter-spacing:0.5px;">Avec le plan Starter (4 900 FCFA/mois) :</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px; background-color:#f0fdf4; border-radius:8px;">
    <tr>
        <td style="padding:16px 20px;">
            @foreach(['Factures et devis illimités', 'Export PDF professionnel', 'Gestion de jusqu\'à 500 clients', 'Relances automatiques et suivi des paiements', 'Support prioritaire par email'] as $item)
            <p style="margin:0 0 8px; font-size:14px; color:#14532d; line-height:1.5;">
                <span style="color:#16a34a; font-weight:700; margin-right:8px;">✅</span>{{ $item }}
            </p>
            @endforeach
        </td>
    </tr>
</table>

{{-- Offre de bienvenue --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px; background-color:#002D5B; border-radius:8px;">
    <tr>
        <td style="padding:20px 24px; text-align:center;">
            <p style="margin:0 0 8px; font-size:18px; font-weight:700; color:#F0C040;">
                💰 Offre de bienvenue
            </p>
            <p style="margin:0; font-size:15px; color:#e2e8f0; line-height:1.6;">
                Utilisez le code <strong style="color:#F0C040; font-size:18px; letter-spacing:1px;">IBIG10</strong> pour bénéficier de <strong style="color:#F0C040;">-10%</strong> sur votre premier mois.
            </p>
        </td>
    </tr>
</table>

{{-- CTA principal --}}
<table role="presentation" cellpadding="0" cellspacing="0" style="margin-bottom:12px;">
    <tr>
        <td style="border-radius:6px; background-color:#F0C040;">
            <a href="{{ config('app.url') }}/billing/plans"
               style="display:inline-block; padding:14px 28px; font-size:15px; font-weight:700; color:#001d3d; text-decoration:none; border-radius:6px;">
                Passer au plan Starter &rarr;
            </a>
        </td>
    </tr>
</table>

{{-- CTA secondaire --}}
<table role="presentation" cellpadding="0" cellspacing="0">
    <tr>
        <td style="border-radius:6px; background-color:#6b7280;">
            <a href="{{ config('app.url') }}/pricing"
               style="display:inline-block; padding:12px 24px; font-size:14px; font-weight:600; color:#ffffff; text-decoration:none; border-radius:6px;">
                Voir tous les plans
            </a>
        </td>
    </tr>
</table>

@endsection

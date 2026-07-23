@extends('emails.layouts.base')

@section('body')

{{-- Titre --}}
<h1 style="margin:0 0 16px; font-size:22px; font-weight:700; color:#002D5B; line-height:1.3;">
    7 jours avec FactPro 🚀 — découvrez ces fonctionnalités cachées
</h1>

<p style="margin:0 0 24px; font-size:15px; color:#374151; line-height:1.6;">
    La plupart de nos utilisateurs découvrent ces fonctionnalités après <strong>plusieurs semaines</strong>. Vous, vous les avez dès aujourd'hui :
</p>

{{-- 4 fonctionnalités cards --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
    <tr>
        <td>
            <!-- Card 1 -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px; background-color:#002D5B; border-radius:8px;">
                <tr>
                    <td style="padding:18px 20px;">
                        <p style="margin:0 0 4px; font-size:17px; color:#F0C040; font-weight:700;">📱 POS Caisse tactile</p>
                        <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.5;">
                            Vendez en magasin avec notre interface tactile optimisée — idéale pour les points de vente et boutiques.
                        </p>
                    </td>
                </tr>
            </table>
            <!-- Card 2 -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px; background-color:#002D5B; border-radius:8px;">
                <tr>
                    <td style="padding:18px 20px;">
                        <p style="margin:0 0 4px; font-size:17px; color:#F0C040; font-weight:700;">🤖 SARA IA</p>
                        <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.5;">
                            Posez vos questions à notre assistante IA intégrée — analyse de données, conseils comptables, réponses instantanées.
                        </p>
                    </td>
                </tr>
            </table>
            <!-- Card 3 -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:12px; background-color:#002D5B; border-radius:8px;">
                <tr>
                    <td style="padding:18px 20px;">
                        <p style="margin:0 0 4px; font-size:17px; color:#F0C040; font-weight:700;">📊 Tableau de bord KPI</p>
                        <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.5;">
                            Suivez vos KPIs en temps réel — chiffre d'affaires, taux de recouvrement, clients actifs et bien plus.
                        </p>
                    </td>
                </tr>
            </table>
            <!-- Card 4 -->
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#002D5B; border-radius:8px;">
                <tr>
                    <td style="padding:18px 20px;">
                        <p style="margin:0 0 4px; font-size:17px; color:#F0C040; font-weight:700;">🔗 Devis interactif</p>
                        <p style="margin:0; font-size:14px; color:#cbd5e1; line-height:1.5;">
                            Envoyez des devis signables en ligne — votre client accepte et signe en un clic, depuis n'importe quel appareil.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- CTA --}}
<table role="presentation" cellpadding="0" cellspacing="0">
    <tr>
        <td style="border-radius:6px; background-color:#F0C040;">
            <a href="{{ config('app.url') }}/dashboard"
               style="display:inline-block; padding:14px 28px; font-size:15px; font-weight:700; color:#001d3d; text-decoration:none; border-radius:6px;">
                Explorer toutes les fonctionnalités &rarr;
            </a>
        </td>
    </tr>
</table>

@endsection

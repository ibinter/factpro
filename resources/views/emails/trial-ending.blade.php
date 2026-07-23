@extends('emails.layouts.base', ['preheader' => 'Votre essai gratuit arrive à expiration. Choisissez votre abonnement pour continuer.'])

@section('body')
    {{-- Headline urgence --}}
    @if ($daysLeft <= 1)
        <h1 style="margin:0 0 8px; font-size:24px; font-weight:700; color:#dc2626;">
            ⏳ Dernier jour !
        </h1>
        <p style="margin:0 0 24px; font-size:15px; color:#6b7280;">
            Votre essai gratuit IBIG FactPro se termine <strong>aujourd'hui</strong>.
        </p>
    @elseif ($daysLeft <= 3)
        <h1 style="margin:0 0 8px; font-size:24px; font-weight:700; color:#d97706;">
            ⏳ Plus que {{ $daysLeft }} jours !
        </h1>
        <p style="margin:0 0 24px; font-size:15px; color:#6b7280;">
            Votre essai gratuit IBIG FactPro expire dans <strong>{{ $daysLeft }} jours</strong>.
        </p>
    @else
        <h1 style="margin:0 0 8px; font-size:24px; font-weight:700; color:#002D5B;">
            ⏳ Votre essai se termine bientôt
        </h1>
        <p style="margin:0 0 24px; font-size:15px; color:#6b7280;">
            Il vous reste <strong>{{ $daysLeft }} jours</strong> pour profiter de l'essai gratuit IBIG FactPro.
        </p>
    @endif

    {{-- Ce que vous perdrez --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px; border:1px solid #fee2e2; border-radius:8px; background-color:#fff5f5;">
        <tr>
            <td style="padding:16px 20px;">
                <p style="margin:0 0 10px; font-size:14px; font-weight:600; color:#991b1b;">Sans abonnement, vous perdrez accès à :</p>
                <ul style="margin:0; padding:0 0 0 20px; color:#7f1d1d; font-size:13px; line-height:1.8;">
                    <li>Création et envoi de factures illimitées</li>
                    <li>Suivi des paiements et relances automatiques</li>
                    <li>Devis, bons de commande et livraisons</li>
                    <li>Gestion des clients et fournisseurs</li>
                    <li>Rapports financiers et tableau de bord</li>
                    <li>Signatures électroniques et QR codes d'authenticité</li>
                </ul>
            </td>
        </tr>
    </table>

    {{-- CTA --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 28px;">
        <tr>
            <td align="center">
                <a href="{{ url('/billing') }}"
                   style="display:inline-block; background-color:#F0C040; color:#002D5B; font-size:16px; font-weight:700; text-decoration:none; padding:14px 36px; border-radius:6px;">
                    Choisir mon abonnement →
                </a>
            </td>
        </tr>
    </table>

    {{-- Rappel tarif --}}
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 8px; border:1px solid #e5e7eb; border-radius:8px;">
        <tr>
            <td style="padding:16px 20px;">
                <p style="margin:0 0 6px; font-size:14px; font-weight:600; color:#002D5B;">💡 Nos abonnements démarrent à partir de :</p>
                <p style="margin:0; font-size:22px; font-weight:700; color:#0062CC;">
                    4 900 FCFA <span style="font-size:14px; font-weight:400; color:#6b7280;">/ mois</span>
                </p>
                <p style="margin:4px 0 0; font-size:12px; color:#9ca3af;">Sans engagement — résiliable à tout moment.</p>
            </td>
        </tr>
    </table>
@endsection

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }
        .header { background: #1a1a2e; color: #fff; padding: 24px 32px; margin-bottom: 24px; }
        .header h1 { font-size: 20px; letter-spacing: 2px; margin-bottom: 4px; }
        .header p { font-size: 10px; opacity: 0.75; }
        .section { margin: 0 32px 20px; }
        .section-title { font-size: 13px; font-weight: bold; color: #1a1a2e; border-bottom: 2px solid #4f46e5; padding-bottom: 4px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th { background: #f1f0ff; color: #4f46e5; padding: 6px 8px; text-align: left; font-size: 10px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 9px; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .kpi-row { display: flex; gap: 16px; margin-bottom: 16px; }
        .kpi { flex: 1; background: #f8f9ff; border: 1px solid #e0e0f0; border-radius: 6px; padding: 12px; text-align: center; }
        .kpi .val { font-size: 24px; font-weight: bold; color: #4f46e5; }
        .kpi .lbl { font-size: 9px; color: #6b7280; margin-top: 2px; }
        .rec { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 8px 12px; margin-top: 6px; border-radius: 0 4px 4px 0; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; background: #f1f0ff; padding: 6px 32px; font-size: 9px; color: #6b7280; border-top: 1px solid #ddd; }
    </style>
</head>
<body>

<div class="header">
    <h1>RAPPORT DE CONFORMITÉ RGPD</h1>
    <p>
        Généré le : {{ $generatedAt->format('d/m/Y à H:i') }}
        &nbsp;|&nbsp;
        Société : {{ $company->name ?? 'N/A' }}
        &nbsp;|&nbsp;
        Référence : RGPD-{{ $generatedAt->format('Ymd') }}
    </p>
</div>

{{-- Section 1 : Inventaire des données --}}
<div class="section">
    <div class="section-title">1. Inventaire des données personnelles traitées</div>
    <table>
        <tr>
            <th>Catégorie</th>
            <th>Finalité</th>
            <th>Base légale</th>
            <th>Durée de conservation</th>
        </tr>
        <tr><td>Données clients (nom, email, téléphone, adresse)</td><td>Facturation</td><td>Contrat (Art. 6.1.b)</td><td>10 ans</td></tr>
        <tr><td>Données employés (RH, paie)</td><td>Gestion RH</td><td>Obligation légale (Art. 6.1.c)</td><td>5 ans après départ</td></tr>
        <tr><td>Logs d'accès</td><td>Sécurité</td><td>Intérêt légitime (Art. 6.1.f)</td><td>12 mois</td></tr>
        <tr><td>Données de navigation</td><td>Analytique</td><td>Consentement (Art. 6.1.a)</td><td>13 mois</td></tr>
    </table>
</div>

{{-- Section 2 : Consentements --}}
<div class="section">
    <div class="section-title">2. État des consentements</div>
    <table>
        <tr>
            <th>Métrique</th>
            <th>Valeur</th>
            <th>Statut</th>
        </tr>
        <tr>
            <td>Consentements actifs</td>
            <td>{{ $activeConsents }}</td>
            <td><span class="badge badge-green">Conforme</span></td>
        </tr>
        <tr>
            <td>Consentements révoqués</td>
            <td>{{ $revokedConsents }}</td>
            <td><span class="badge badge-yellow">Archivé</span></td>
        </tr>
        <tr>
            <td>Total enregistrements</td>
            <td>{{ $totalConsents }}</td>
            <td>—</td>
        </tr>
    </table>
</div>

{{-- Section 3 : Demandes traitées --}}
<div class="section">
    <div class="section-title">3. Demandes d'exercice des droits (Art. 15 à 22)</div>
    @if($requestsByType->isEmpty())
        <p style="color:#6b7280; margin-top:8px;">Aucune demande enregistrée pour cette période.</p>
    @else
    <table>
        <tr>
            <th>Type de demande</th>
            <th>Statut</th>
            <th>Nombre</th>
        </tr>
        @foreach($requestsByType as $row)
        <tr>
            <td>{{ ucfirst($row->type) }}</td>
            <td>
                @if($row->status === 'completed')
                    <span class="badge badge-green">Traité</span>
                @elseif($row->status === 'rejected')
                    <span class="badge badge-red">Refusé</span>
                @else
                    <span class="badge badge-yellow">{{ ucfirst($row->status) }}</span>
                @endif
            </td>
            <td>{{ $row->total }}</td>
        </tr>
        @endforeach
    </table>
    @if($overdueCount > 0)
    <p style="color:#dc2626; margin-top:8px; font-weight:bold;">
        &#9888; {{ $overdueCount }} demande(s) dépassent le délai légal de 30 jours.
    </p>
    @endif
    @endif
</div>

{{-- Section 4 : Accès & incidents --}}
<div class="section">
    <div class="section-title">4. Journal des accès et incidents (30 derniers jours)</div>
    @if($accessSummary->isEmpty())
        <p style="color:#6b7280; margin-top:8px;">Aucun accès enregistré. Activez "Journaliser tous les accès" dans la politique de sécurité.</p>
    @else
    <table>
        <tr>
            <th>Action</th>
            <th>Occurrences</th>
            <th>Succès</th>
        </tr>
        @foreach($accessSummary as $row)
        <tr>
            <td>{{ $row->action }}</td>
            <td>{{ $row->total }}</td>
            <td>{{ $row->successes }}/{{ $row->total }}</td>
        </tr>
        @endforeach
    </table>
    @endif
</div>

{{-- Section 5 : Recommandations --}}
<div class="section">
    <div class="section-title">5. Recommandations</div>
    @if($overdueCount > 0)
    <div class="rec">Traiter {{ $overdueCount }} demande(s) en retard pour rester dans les délais légaux (Art. 12.3 RGPD).</div>
    @endif
    @if($totalConsents === 0)
    <div class="rec">Aucun consentement enregistré. Mettre en place un mécanisme de collecte des consentements.</div>
    @endif
    @if($revokedConsents > 0 && $totalConsents > 0 && ($revokedConsents / $totalConsents) > 0.3)
    <div class="rec">Taux de révocation élevé ({{ round(($revokedConsents/$totalConsents)*100) }}%). Réviser les finalités de traitement.</div>
    @endif
    @if($overdueCount === 0 && $totalConsents > 0)
    <div class="rec" style="background:#d1fae5; border-color:#10b981; color:#065f46;">
        Aucune non-conformité critique détectée. Maintenir les bonnes pratiques.
    </div>
    @endif
</div>

<div class="footer">
    Document confidentiel — RGPD Art. 30 (Registre des activités de traitement) &nbsp;|&nbsp;
    {{ $company->name ?? '' }} &nbsp;|&nbsp; Page <span class="pagenum"></span>
</div>

</body>
</html>

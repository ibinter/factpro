<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Direction — {{ now()->translatedFormat('F Y') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; }
        .header { background: #1e40af; color: white; padding: 24px; margin-bottom: 24px; }
        .header h1 { font-size: 20px; font-weight: bold; }
        .header p { font-size: 12px; margin-top: 4px; opacity: 0.85; }
        .section { margin-bottom: 24px; padding: 0 24px; }
        .section-title { font-size: 13px; font-weight: bold; color: #1e40af; border-bottom: 2px solid #dbeafe; padding-bottom: 6px; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
        .kpi-grid { display: flex; gap: 12px; margin-bottom: 16px; }
        .kpi { flex: 1; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; text-align: center; }
        .kpi .value { font-size: 18px; font-weight: bold; color: #1e40af; }
        .kpi .label { font-size: 10px; color: #64748b; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; font-weight: 600; font-size: 10px; text-transform: uppercase; padding: 8px 10px; text-align: left; border: 1px solid #e2e8f0; }
        td { padding: 7px 10px; border: 1px solid #e2e8f0; font-size: 10px; }
        tr:nth-child(even) td { background: #f8fafc; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-orange { background: #ffedd5; color: #9a3412; }
        .alert-box { background: #fef3c7; border: 1px solid #fbbf24; border-radius: 4px; padding: 10px; margin-bottom: 8px; }
        .footer { margin-top: 32px; padding: 16px 24px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>

<div class="header">
    <h1>Rapport Direction — Forecasting & Objectifs</h1>
    <p>{{ $company->name }} &bull; Généré le {{ now()->format('d/m/Y à H:i') }} &bull; Période : {{ now()->translatedFormat('F Y') }}</p>
</div>

{{-- KPI résumé --}}
<div class="section">
    <div class="section-title">Résumé du mois en cours</div>
    <div class="kpi-grid">
        <div class="kpi">
            <div class="value">{{ number_format($comparison['actual'], 0, ',', ' ') }} {{ $comparison['currency'] }}</div>
            <div class="label">CA réalisé</div>
        </div>
        <div class="kpi">
            <div class="value">{{ number_format($comparison['target'], 0, ',', ' ') }} {{ $comparison['currency'] }}</div>
            <div class="label">Objectif mensuel</div>
        </div>
        <div class="kpi">
            <div class="value">{{ $comparison['pct_achieved'] }}%</div>
            <div class="label">Taux de réalisation</div>
        </div>
        <div class="kpi">
            <div class="value">{{ $forecast['days_elapsed'] }}/{{ $forecast['days_elapsed'] + $forecast['days_remaining'] }}j</div>
            <div class="label">Jours écoulés</div>
        </div>
    </div>
</div>

{{-- Prévisions --}}
<div class="section">
    <div class="section-title">Prévisions de fin de mois</div>
    <table>
        <tr>
            <th>Méthode</th>
            <th>Prévision ({{ $comparison['currency'] }})</th>
        </tr>
        <tr>
            <td>Projection linéaire (tendance journalière)</td>
            <td>{{ number_format($forecast['forecasts']['linear_projection'], 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td>Moyenne mobile (3 derniers mois)</td>
            <td>{{ number_format($forecast['forecasts']['moving_average'], 0, ',', ' ') }}</td>
        </tr>
        <tr>
            <td>Même mois — année précédente</td>
            <td>{{ number_format($forecast['forecasts']['last_year'], 0, ',', ' ') }}</td>
        </tr>
    </table>
</div>

{{-- Historique --}}
<div class="section">
    <div class="section-title">Historique CA 12 mois</div>
    <table>
        <tr>
            <th>Mois</th>
            <th>CA réalisé ({{ $comparison['currency'] }})</th>
        </tr>
        @foreach($history as $row)
        <tr>
            <td>{{ $row['label'] }}</td>
            <td>{{ number_format($row['revenue'], 0, ',', ' ') }}</td>
        </tr>
        @endforeach
    </table>
</div>

{{-- Sous-performance --}}
@if(count($underperformance) > 0)
<div class="section">
    <div class="section-title">⚠ Alertes Sous-performance</div>
    @foreach($underperformance as $agent)
    <div class="alert-box">
        <strong>{{ $agent['name'] }}</strong> — Réalisé : {{ number_format($agent['actual'], 0, ',', ' ') }} {{ $agent['currency'] }}
        / Objectif : {{ number_format($agent['target'], 0, ',', ' ') }} {{ $agent['currency'] }}
        — <span class="badge badge-red">{{ $agent['pct_achieved'] }}%</span>
    </div>
    @endforeach
</div>
@endif

<div class="footer">
    Rapport généré automatiquement par IBIG FactPro &bull; Confidentiel — usage interne
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; }
        .header { background: #1a56db; color: #fff; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 18px; font-weight: 700; margin-bottom: 2px; }
        .header p  { font-size: 10px; opacity: .85; }
        .header .company { text-align: right; font-size: 10px; }
        .content { padding: 20px 24px; }
        h2 { font-size: 13px; font-weight: 700; color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 4px; margin: 18px 0 10px; }
        .kpi-grid { display: flex; gap: 12px; margin-bottom: 4px; }
        .kpi-box { flex: 1; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; }
        .kpi-box .label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: .5px; }
        .kpi-box .value { font-size: 16px; font-weight: 700; color: #1a1a1a; margin-top: 2px; }
        .kpi-box .growth { font-size: 9px; margin-top: 2px; }
        .growth.pos { color: #16a34a; }
        .growth.neg { color: #dc2626; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        table thead th { background: #f3f4f6; padding: 6px 8px; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #d1d5db; }
        table tbody td { padding: 5px 8px; border-bottom: 1px solid #f3f4f6; color: #374151; }
        table tbody tr:nth-child(even) td { background: #fafafa; }
        .footer { margin-top: 24px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 9999px; font-size: 9px; font-weight: 600; }
        .badge-green { background: #dcfce7; color: #16a34a; }
        .badge-red   { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>

<div class="header">
    <div>
        <h1>RAPPORT ANALYTIQUE</h1>
        <p>Période : {{ strtoupper($period) }} — Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
    <div class="company">
        <strong>{{ $company->name ?? 'Entreprise' }}</strong><br>
        @if(!empty($company->email)) {{ $company->email }}<br> @endif
        @if(!empty($company->phone)) {{ $company->phone }}<br> @endif
        @if(!empty($company->address)) {{ $company->address }} @endif
    </div>
</div>

<div class="content">

    {{-- KPIs --}}
    <h2>Indicateurs clés de performance</h2>
    <div class="kpi-grid">
        <div class="kpi-box">
            <div class="label">CA Mois courant</div>
            <div class="value">{{ number_format($kpis['current_month_revenue'], 0, ',', ' ') }}</div>
            @php $g = $kpis['growth_pct']; @endphp
            <div class="growth {{ $g >= 0 ? 'pos' : 'neg' }}">
                {{ $g >= 0 ? '+' : '' }}{{ $g }}% vs mois préc.
            </div>
        </div>
        <div class="kpi-box">
            <div class="label">CA Mois précédent</div>
            <div class="value">{{ number_format($kpis['prev_month_revenue'], 0, ',', ' ') }}</div>
        </div>
        <div class="kpi-box">
            <div class="label">Nb Factures</div>
            <div class="value">{{ $kpis['invoice_count'] }}</div>
            <div class="growth">ce mois</div>
        </div>
        <div class="kpi-box">
            <div class="label">Panier moyen</div>
            <div class="value">{{ number_format($kpis['avg_invoice_value'], 0, ',', ' ') }}</div>
        </div>
        <div class="kpi-box">
            <div class="label">Taux recouvrement</div>
            <div class="value">{{ $recovery['rate'] }}%</div>
            <div class="growth {{ $recovery['rate'] >= 75 ? 'pos' : 'neg' }}">
                {{ $recovery['paid'] }}/{{ $recovery['total'] }} factures payées
            </div>
        </div>
    </div>

    @if($recovery['overdue_amount'] > 0)
    <p style="margin-top:8px;color:#dc2626;font-size:10px;">
        ⚠ Montant en souffrance : {{ number_format($recovery['overdue_amount'], 0, ',', ' ') }} FCFA
    </p>
    @endif

    {{-- Top Clients --}}
    <h2>Top 10 Clients</h2>
    @if(!empty($topClients['labels']))
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th style="text-align:right">Chiffre d'affaires</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topClients['labels'] as $i => $label)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $label }}</td>
                <td style="text-align:right">{{ number_format($topClients['values'][$i], 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color:#6b7280">Aucun client sur la période.</p>
    @endif

    {{-- Top Produits --}}
    <h2>Top 10 Produits / Services</h2>
    @if(!empty($topProducts['labels']))
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Produit / Service</th>
                <th style="text-align:right">Montant vendu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProducts['labels'] as $i => $label)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $label }}</td>
                <td style="text-align:right">{{ number_format($topProducts['values'][$i], 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p style="color:#6b7280">Aucun produit sur la période.</p>
    @endif

</div>

<div class="footer">
    Rapport généré automatiquement par FactPro — {{ config('app.name') }} — {{ now()->format('d/m/Y H:i') }}
</div>

</body>
</html>

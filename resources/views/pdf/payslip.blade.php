<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin de paie</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #222; }
        .page { padding: 20px 30px; }

        /* En-tête */
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #1a56db; padding-bottom: 12px; margin-bottom: 16px; }
        .header-left { font-size: 13px; font-weight: bold; }
        .header-left .company-name { font-size: 16px; color: #1a56db; }
        .header-center { text-align: center; }
        .header-center h1 { font-size: 18px; font-weight: bold; letter-spacing: 2px; color: #1a56db; }
        .header-center .period { font-size: 11px; color: #555; margin-top: 4px; }
        .header-right { text-align: right; font-size: 10px; color: #555; }

        /* Blocs employeur / employé */
        .info-row { display: flex; gap: 16px; margin-bottom: 16px; }
        .info-block { flex: 1; border: 1px solid #d1d5db; border-radius: 4px; padding: 10px; }
        .info-block h3 { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #6b7280; margin-bottom: 6px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .info-block p { margin: 2px 0; }
        .info-block .label { color: #6b7280; }

        /* Tableau cotisations */
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table th { background: #1a56db; color: white; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        table td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        table tr:nth-child(even) td { background: #f9fafb; }
        table .amount { text-align: right; font-family: monospace; }
        table .rate { text-align: center; }

        /* Totaux */
        .totals { border: 2px solid #1a56db; border-radius: 4px; padding: 12px; margin-bottom: 16px; }
        .totals-row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #e5e7eb; }
        .totals-row:last-child { border-bottom: none; }
        .totals-row.highlight { background: #eff6ff; font-weight: bold; padding: 6px 8px; border-radius: 2px; margin: 4px -8px; }
        .totals-row .label { color: #374151; }
        .totals-row .value { font-family: monospace; font-weight: bold; }
        .net-label { color: #1a56db; font-size: 13px; }
        .net-value { color: #1a56db; font-size: 14px; }

        /* Pied de page */
        .footer { border-top: 1px solid #d1d5db; padding-top: 10px; font-size: 8px; color: #9ca3af; text-align: center; }
        .footer p { margin: 2px 0; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .status-draft { background: #fef3c7; color: #92400e; }
        .status-validated { background: #d1fae5; color: #065f46; }
        .status-paid { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
<div class="page">

    {{-- En-tête --}}
    <div class="header">
        <div class="header-left">
            <div class="company-name">{{ $payslip->company->name }}</div>
            <div>{{ $payslip->company->address ?? '' }}</div>
            @if($payslip->company->rccm ?? false)
                <div>RCCM : {{ $payslip->company->rccm }}</div>
            @endif
        </div>
        <div class="header-center">
            <h1>BULLETIN DE PAIE</h1>
            <div class="period">
                {{ \Carbon\Carbon::createFromDate($payslip->period_year, $payslip->period_month, 1)->translatedFormat('F Y') }}
            </div>
            <div style="margin-top:4px">
                <span class="status-badge status-{{ $payslip->status }}">{{ ucfirst($payslip->status) }}</span>
            </div>
        </div>
        <div class="header-right">
            <div>Date d'édition : {{ now()->format('d/m/Y') }}</div>
            @if($payslip->payment_date)
                <div>Date de paiement : {{ $payslip->payment_date->format('d/m/Y') }}</div>
            @endif
            <div>N° bulletin : {{ $payslip->id }}</div>
        </div>
    </div>

    {{-- Blocs Employeur / Employé --}}
    <div class="info-row">
        <div class="info-block">
            <h3>Employeur</h3>
            <p><strong>{{ $payslip->company->name }}</strong></p>
            @if($payslip->company->address ?? false)
                <p>{{ $payslip->company->address }}</p>
            @endif
            @if($payslip->company->phone ?? false)
                <p><span class="label">Tél :</span> {{ $payslip->company->phone }}</p>
            @endif
            @if($payslip->company->email ?? false)
                <p><span class="label">Email :</span> {{ $payslip->company->email }}</p>
            @endif
            <p><span class="label">Régime social :</span>
                {{ \App\Services\PayrollService::REGIMES[$payslip->employee->social_security_regime]['name'] ?? $payslip->employee->social_security_regime }}
            </p>
        </div>
        <div class="info-block">
            <h3>Employé</h3>
            <p><strong>{{ $payslip->employee->full_name }}</strong></p>
            <p><span class="label">Poste :</span> {{ $payslip->employee->position }}</p>
            @if($payslip->employee->department)
                <p><span class="label">Département :</span> {{ $payslip->employee->department }}</p>
            @endif
            <p><span class="label">Date d'embauche :</span> {{ $payslip->employee->hire_date?->format('d/m/Y') }}</p>
            @if($payslip->employee->cnss_number)
                <p><span class="label">N° CNSS :</span> {{ $payslip->employee->cnss_number }}</p>
            @endif
            @if($payslip->contract)
                <p><span class="label">Type contrat :</span> {{ strtoupper($payslip->contract->type) }}</p>
            @endif
        </div>
    </div>

    {{-- Tableau des cotisations --}}
    <table>
        <thead>
            <tr>
                <th>Désignation</th>
                <th>Base de calcul</th>
                <th class="rate">Taux salarié</th>
                <th class="amount">Montant salarié</th>
                <th class="rate">Taux patronal</th>
                <th class="amount">Montant patronal</th>
            </tr>
        </thead>
        <tbody>
            @php
                $regime  = $payslip->employee->social_security_regime ?? 'cnss_ci';
                $rates   = \App\Services\PayrollService::REGIMES[$regime] ?? \App\Services\PayrollService::REGIMES['cnss_ci'];
                $gross   = (float) $payslip->gross_salary;
                $empC    = $payslip->employee_contributions;
                $emplC   = $payslip->employer_contributions;
                $currency = $payslip->currency;
            @endphp
            <tr>
                <td><strong>Salaire de base</strong></td>
                <td>{{ number_format($gross, 0, ',', ' ') }} {{ $currency }}</td>
                <td class="rate">—</td>
                <td class="amount">—</td>
                <td class="rate">—</td>
                <td class="amount">—</td>
            </tr>
            <tr>
                <td>Cotisation CNSS / Sécurité sociale</td>
                <td>{{ number_format($gross, 0, ',', ' ') }} {{ $currency }}</td>
                <td class="rate">{{ number_format($rates['employee_rate'] * 100, 1) }} %</td>
                <td class="amount">{{ number_format($empC['cnss'] ?? 0, 0, ',', ' ') }}</td>
                <td class="rate">{{ number_format($rates['employer_rate'] * 100, 1) }} %</td>
                <td class="amount">{{ number_format($emplC['cnss'] ?? 0, 0, ',', ' ') }}</td>
            </tr>
            @if(($empC['irpp'] ?? 0) > 0)
            <tr>
                <td>IRPP (Impôt sur le revenu)</td>
                <td>{{ number_format($gross, 0, ',', ' ') }} {{ $currency }}</td>
                <td class="rate">Barème</td>
                <td class="amount">{{ number_format($empC['irpp'], 0, ',', ' ') }}</td>
                <td class="rate">—</td>
                <td class="amount">—</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="background:#f3f4f6; font-weight:bold;">
                <td colspan="3"><strong>Total cotisations</strong></td>
                <td class="amount"><strong>{{ number_format($empC['total'] ?? 0, 0, ',', ' ') }}</strong></td>
                <td class="rate"></td>
                <td class="amount"><strong>{{ number_format($emplC['total'] ?? 0, 0, ',', ' ') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    {{-- Récapitulatif --}}
    <div class="totals">
        <div class="totals-row">
            <span class="label">Salaire brut</span>
            <span class="value">{{ number_format($gross, 0, ',', ' ') }} {{ $currency }}</span>
        </div>
        <div class="totals-row">
            <span class="label">Cotisations salariales totales</span>
            <span class="value">- {{ number_format($empC['total'] ?? 0, 0, ',', ' ') }} {{ $currency }}</span>
        </div>
        <div class="totals-row highlight">
            <span class="label net-label">NET À PAYER</span>
            <span class="value net-value">{{ number_format((float) $payslip->net_salary, 0, ',', ' ') }} {{ $currency }}</span>
        </div>
        <div class="totals-row" style="margin-top:8px">
            <span class="label">Charges patronales</span>
            <span class="value">{{ number_format($emplC['total'] ?? 0, 0, ',', ' ') }} {{ $currency }}</span>
        </div>
        <div class="totals-row">
            <span class="label"><strong>Coût total employeur</strong></span>
            <span class="value"><strong>{{ number_format((float) $payslip->total_employer_cost, 0, ',', ' ') }} {{ $currency }}</strong></span>
        </div>
    </div>

    {{-- Pied de page --}}
    <div class="footer">
        <p>Ce bulletin de paie est établi conformément à la législation sociale en vigueur.</p>
        <p>Conservez ce document sans limitation de durée.</p>
        @if($payslip->payment_date)
            <p>Payé le : {{ $payslip->payment_date->format('d/m/Y') }}</p>
        @endif
        <p style="margin-top:6px">{{ $payslip->company->name }} — Généré par IBIG FactPro le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>

</div>
</body>
</html>

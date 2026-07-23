{{-- Bulletin de paie — vue Document (lit $document->meta) --}}
@php
    $meta       = $document->meta ?? [];
    $gross      = (float)($meta['gross_salary'] ?? 0);
    $empC       = (float)($meta['employee_contributions'] ?? 0);
    $irpp       = (float)($meta['irpp'] ?? 0);
    $other      = (float)($meta['other_deductions'] ?? 0);
    $emplC      = (float)($meta['employer_contributions'] ?? 0);
    $net        = (float)($meta['net_salary'] ?? ($gross - $empC - $irpp - $other));
    $totalCost  = $gross + $emplC;
    $cur        = $document->currency ?? 'XOF';
    $pc         = $primaryColor ?? '#1a56db';
    $sc         = $secondaryColor ?? '#eff6ff';
    $ac         = $accentColor ?? '#f0c040';
    $fmt = fn($n) => number_format($n, 0, ',', ' ');
    $employee   = $document->customer;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Bulletin de paie</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:10px; color:#222; background:#fff; }
.page { padding:24px 32px; }

.header { display:flex; justify-content:space-between; align-items:center;
          border-bottom:3px solid {{ $pc }}; padding-bottom:14px; margin-bottom:18px; }
.header-left .cname { font-size:15px; font-weight:bold; color:{{ $pc }}; }
.header-left .cinfo { font-size:9px; color:#555; margin-top:2px; }
.header-center h1 { font-size:17px; font-weight:bold; letter-spacing:2px; color:{{ $pc }}; text-align:center; }
.header-center .period { font-size:10px; color:#555; text-align:center; margin-top:3px; }
.header-right { text-align:right; font-size:9px; color:#555; }

.badge { display:inline-block; padding:2px 9px; border-radius:10px; font-size:9px; font-weight:bold;
         background:{{ $sc }}; color:{{ $pc }}; margin-top:4px; }

.info-row { display:flex; gap:14px; margin-bottom:16px; }
.info-block { flex:1; border:1px solid #e5e7eb; border-radius:5px; padding:10px; }
.info-block h3 { font-size:8px; font-weight:bold; text-transform:uppercase; color:#9ca3af;
                 margin-bottom:6px; border-bottom:1px solid #f3f4f6; padding-bottom:4px; }
.info-block .row { display:flex; gap:4px; margin:2px 0; }
.info-block .lbl { color:#9ca3af; min-width:80px; }
.info-block .val { font-weight:500; }

table { width:100%; border-collapse:collapse; margin-bottom:16px; font-size:9.5px; }
thead th { background:{{ $pc }}; color:#fff; padding:6px 8px; text-align:left; font-size:9px; text-transform:uppercase; }
thead th.right { text-align:right; }
tbody td { padding:5px 8px; border-bottom:1px solid #f3f4f6; }
tbody tr:nth-child(even) td { background:#fafafa; }
td.right { text-align:right; font-family:monospace; }
td.center { text-align:center; }
tfoot td { padding:6px 8px; font-weight:bold; background:#f3f4f6; }

.totals { border:2px solid {{ $pc }}; border-radius:5px; padding:12px 14px; margin-bottom:16px; }
.trow { display:flex; justify-content:space-between; padding:4px 0;
        border-bottom:1px solid #f3f4f6; font-size:10px; }
.trow:last-child { border-bottom:none; }
.trow.hl { background:{{ $sc }}; padding:7px 10px; margin:4px -10px;
           border-radius:3px; font-weight:bold; font-size:12px; border:none; }
.trow .lbl { color:#374151; }
.trow .val { font-family:monospace; font-weight:bold; }
.trow.hl .lbl { color:{{ $pc }}; }
.trow.hl .val { color:{{ $pc }}; font-size:13px; }

.footer { border-top:1px solid #e5e7eb; padding-top:10px; font-size:8px; color:#9ca3af; text-align:center; }
.footer p { margin:2px 0; }
</style>
</head>
<body>
<div class="page">

{{-- En-tête --}}
<div class="header">
    <div class="header-left">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" style="max-height:40px;max-width:120px;margin-bottom:4px;" alt="logo">
        @endif
        <div class="cname">{{ $company->name }}</div>
        <div class="cinfo">{{ $company->address ?? '' }}</div>
        @if($company->rccm ?? false)<div class="cinfo">RCCM : {{ $company->rccm }}</div>@endif
    </div>
    <div class="header-center">
        <h1>BULLETIN DE PAIE</h1>
        <div class="period">{{ $meta['period'] ?? $document->issue_date?->translatedFormat('F Y') }}</div>
        <div><span class="badge">{{ strtoupper($document->status ?? 'brouillon') }}</span></div>
    </div>
    <div class="header-right">
        <div>Date d'édition : {{ now()->format('d/m/Y') }}</div>
        @if(!empty($meta['payment_date']))
            <div>Date de paiement : {{ \Carbon\Carbon::parse($meta['payment_date'])->format('d/m/Y') }}</div>
        @endif
        <div>N° : {{ $document->number }}</div>
    </div>
</div>

{{-- Employeur / Employé --}}
<div class="info-row">
    <div class="info-block">
        <h3>Employeur</h3>
        <div class="row"><span class="val">{{ $company->name }}</span></div>
        @if($company->address ?? false)<div class="row"><span class="lbl">Adresse :</span><span class="val">{{ $company->address }}</span></div>@endif
        @if($company->phone ?? false)<div class="row"><span class="lbl">Tél :</span><span class="val">{{ $company->phone }}</span></div>@endif
        @if($company->email ?? false)<div class="row"><span class="lbl">Email :</span><span class="val">{{ $company->email }}</span></div>@endif
    </div>
    <div class="info-block">
        <h3>Employé</h3>
        <div class="row"><span class="val">{{ $employee?->name ?? '—' }}</span></div>
        @if(!empty($meta['job_title']))<div class="row"><span class="lbl">Poste :</span><span class="val">{{ $meta['job_title'] }}</span></div>@endif
        @if(!empty($meta['department']))<div class="row"><span class="lbl">Département :</span><span class="val">{{ $meta['department'] }}</span></div>@endif
        @if(!empty($meta['contract_type']))<div class="row"><span class="lbl">Contrat :</span><span class="val">{{ $meta['contract_type'] }}</span></div>@endif
        @if(!empty($meta['cnss_number']))<div class="row"><span class="lbl">N° CNSS :</span><span class="val">{{ $meta['cnss_number'] }}</span></div>@endif
    </div>
</div>

{{-- Tableau cotisations --}}
<table>
    <thead>
        <tr>
            <th>Désignation</th>
            <th class="right">Base</th>
            <th class="right">Part salarié</th>
            <th class="right">Part patronale</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Salaire brut</strong></td>
            <td class="right">{{ $fmt($gross) }} {{ $cur }}</td>
            <td class="right">—</td>
            <td class="right">—</td>
        </tr>
        @if($empC > 0)
        <tr>
            <td>Cotisations sociales (CNSS / Sécurité sociale)</td>
            <td class="right">{{ $fmt($gross) }} {{ $cur }}</td>
            <td class="right">{{ $fmt($empC) }} {{ $cur }}</td>
            <td class="right">{{ $fmt($emplC) }} {{ $cur }}</td>
        </tr>
        @endif
        @if($irpp > 0)
        <tr>
            <td>IRPP / Impôt sur le revenu</td>
            <td class="right">{{ $fmt($gross) }} {{ $cur }}</td>
            <td class="right">{{ $fmt($irpp) }} {{ $cur }}</td>
            <td class="right">—</td>
        </tr>
        @endif
        @if($other > 0)
        <tr>
            <td>Autres retenues</td>
            <td class="right">—</td>
            <td class="right">{{ $fmt($other) }} {{ $cur }}</td>
            <td class="right">—</td>
        </tr>
        @endif
    </tbody>
    <tfoot>
        <tr>
            <td><strong>Total retenues</strong></td>
            <td></td>
            <td class="right"><strong>{{ $fmt($empC + $irpp + $other) }} {{ $cur }}</strong></td>
            <td class="right"><strong>{{ $fmt($emplC) }} {{ $cur }}</strong></td>
        </tr>
    </tfoot>
</table>

{{-- Récapitulatif --}}
<div class="totals">
    <div class="trow">
        <span class="lbl">Salaire brut</span>
        <span class="val">{{ $fmt($gross) }} {{ $cur }}</span>
    </div>
    <div class="trow">
        <span class="lbl">Total retenues salariales</span>
        <span class="val">— {{ $fmt($empC + $irpp + $other) }} {{ $cur }}</span>
    </div>
    <div class="trow hl">
        <span class="lbl">NET À PAYER</span>
        <span class="val">{{ $fmt($net) }} {{ $cur }}</span>
    </div>
    <div class="trow" style="margin-top:6px">
        <span class="lbl">Charges patronales</span>
        <span class="val">{{ $fmt($emplC) }} {{ $cur }}</span>
    </div>
    <div class="trow">
        <span class="lbl"><strong>Coût total employeur</strong></span>
        <span class="val"><strong>{{ $fmt($totalCost) }} {{ $cur }}</strong></span>
    </div>
    @if(!empty($meta['payment_method']))
    <div class="trow">
        <span class="lbl">Mode de paiement</span>
        <span class="val">{{ $meta['payment_method'] }}</span>
    </div>
    @endif
</div>

{{-- Notes --}}
@if($document->notes)
<div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:4px;padding:10px;margin-bottom:14px;font-size:9.5px;">
    <strong>Observations :</strong> {{ $document->notes }}
</div>
@endif

{{-- Signature --}}
<div style="display:flex;justify-content:space-between;margin-top:20px;font-size:9px;">
    <div style="text-align:center;width:45%;">
        <div style="border-top:1px solid #d1d5db;padding-top:4px;color:#6b7280;">Signature employeur</div>
    </div>
    <div style="text-align:center;width:45%;">
        <div style="border-top:1px solid #d1d5db;padding-top:4px;color:#6b7280;">Signature employé (bon pour accord)</div>
    </div>
</div>

{{-- Pied de page --}}
<div class="footer" style="margin-top:16px;">
    <p>Ce bulletin de paie est établi conformément à la législation sociale en vigueur.</p>
    <p>Conservez ce document sans limitation de durée.</p>
    <p style="margin-top:5px;">{{ $company->name }} — Généré par IBIG FactPro le {{ now()->format('d/m/Y à H:i') }}</p>
</div>

</div>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
@@page { margin: 22mm 18mm 28mm 18mm; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; background: #fff; }

@if($watermark)
#watermark {
    position: fixed;
    top: 70mm; left: 20mm;
    font-size: 52px; font-weight: bold;
    color: rgba(200,0,0,.08);
    transform: rotate(-35deg);
    white-space: nowrap;
    z-index: 0;
}
@endif

#footer {
    position: fixed;
    bottom: -20mm; left: 0; right: 0;
    height: 18mm;
    background: {{ $primaryColor }};
    color: #fff;
    font-size: 7px;
    padding: 3mm 4mm 0 4mm;
}
#footer table { width: 100%; }
#footer td { display: table-cell; }
#footer td:last-child { text-align: right; }
#footer td:nth-child(2) { text-align: center; }

/* HEADER avec coin diagonal simulé */
#diag-header {
    background: {{ $primaryColor }};
    margin: -22mm -18mm 0 -18mm;
    padding: 6mm 10mm 10mm 10mm;
    width: calc(100% + 36mm);
    position: relative;
}
/* Coin bas-droit coupé : triangle blanc superposé */
#diag-corner {
    position: absolute;
    bottom: 0; right: 0;
    width: 0; height: 0;
    border-style: solid;
    border-width: 0 0 20mm 40mm;
    border-color: transparent transparent #fff transparent;
}
#diag-inner { display: table; width: 100%; }
#diag-logo { display: table-cell; vertical-align: middle; }
#diag-logo img { max-height: 16mm; max-width: 38mm; }
#diag-logo .co-name { color: #fff; font-size: 13px; font-weight: bold; margin-top: 2mm; }
#diag-title { display: table-cell; vertical-align: middle; text-align: right; padding-right: 35mm; }
#diag-title .doc-type {
    color: #fff;
    font-size: 24px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 3px;
}

/* Bande accent inverse diagonale */
#accent-band {
    background: {{ $accentColor }};
    margin: 0 -18mm 5mm -18mm;
    padding: 2mm 10mm;
    width: calc(100% + 36mm);
    position: relative;
}
#accent-corner {
    position: absolute;
    bottom: 0; left: 0;
    width: 0; height: 0;
    border-style: solid;
    border-width: 12mm 28mm 0 0;
    border-color: #fff transparent transparent transparent;
}
#accent-band-inner { display: table; width: 100%; }
#accent-band-inner td { display: table-cell; font-size: 8px; font-weight: bold; color: #333; vertical-align: middle; padding-left: 25mm; }
#accent-band-inner td:last-child { text-align: right; padding-right: 4mm; padding-left: 0; }

/* Labels de section en pastille */
.section-label {
    display: inline-block;
    background: {{ $primaryColor }};
    color: #fff;
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 1mm 3mm;
    margin-bottom: 2mm;
    border-radius: 2px;
}

/* Parties */
.parties-grid { display: table; width: 100%; margin-bottom: 5mm; }
.party-cell { display: table-cell; width: 50%; padding: 3mm 4mm; background: #f8f8f8; }
.party-cell .name { font-size: 11px; font-weight: bold; color: #111; margin-bottom: 1mm; }
.party-cell .detail { font-size: 8px; color: #555; line-height: 1.5; }
.party-spacer { display: table-cell; width: 4mm; }

/* Items */
.items-table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
.items-table thead tr { background: {{ $primaryColor }}; }
.items-table thead th { padding: 3mm 2mm; font-size: 8px; color: #fff; text-align: left; }
.items-table thead th.r { text-align: right; }
.items-table tbody tr:nth-child(even) { background: #f9f9f9; }
.items-table tbody td { padding: 2.5mm 2mm; font-size: 8px; border-bottom: 1px solid #eee; }
.items-table tbody td.r { text-align: right; }

/* Totaux */
.totaux-wrap { display: table; width: 100%; margin-bottom: 4mm; }
.totaux-qr { display: table-cell; vertical-align: bottom; width: 26mm; padding-right: 4mm; }
.totaux-qr img { width: 22mm; height: 22mm; }
.totaux-qr .ql { font-size: 6.5px; color: #aaa; text-align: center; margin-top: 1mm; }
.totaux-fill { display: table-cell; }
.totaux-right { display: table-cell; width: 85mm; vertical-align: top; }
.totaux-table { width: 100%; font-size: 8.5px; border-collapse: collapse; }
.totaux-table td { padding: 1.5mm 2mm; }
.totaux-table td:last-child { text-align: right; font-weight: bold; }
.totaux-table .ttc-row td { font-size: 13px; color: {{ $primaryColor }}; border-top: 2px solid {{ $primaryColor }}; padding-top: 2mm; }
.totaux-table .reste-row td { color: #c00; font-size: 8.5px; }

.notes { font-size: 7.5px; color: #666; margin-top: 4mm; border-top: 1px solid #ddd; padding-top: 3mm; }

.sig-row { display: table; width: 100%; margin-top: 8mm; }
.sig-col { display: table-cell; width: 50%; padding: 0 3mm; }
.sig-line { border-bottom: 1px solid #333; height: 12mm; margin-bottom: 2mm; }
.sig-label { font-size: 7.5px; color: #666; text-align: center; }

.legal { font-size: 6.5px; color: #999; margin-top: 4mm; border-top: 1px solid #ddd; padding-top: 2mm; }
</style>
</head>
<body>

@if($watermark)
<div id="watermark">{{ $watermark }}</div>
@endif

<div id="footer">
    <table><tr>
        <td>{{ $document->number }}</td>
        <td>{{ $document->verification_url ?? '' }}</td>
        <td>Propulsé par IBIG FactPro</td>
    </tr></table>
</div>

<div id="diag-header">
    <div id="diag-inner">
        <div id="diag-logo">
            @if($logoBase64)<img src="{{ $logoBase64 }}" alt="logo"><br>@endif
            <div class="co-name">{{ $company->name }}</div>
        </div>
        <div id="diag-title">
            <div class="doc-type">{{ $document->type_label }}</div>
        </div>
    </div>
    <div id="diag-corner"></div>
</div>

<div id="accent-band">
    <div id="accent-band-inner">
        <table style="width:100%"><tr>
            <td style="padding-left:25mm">N° {{ $document->number }}</td>
            <td style="text-align:center">{{ \Carbon\Carbon::parse($document->date)->format('d/m/Y') }}</td>
            <td style="text-align:right;padding-right:4mm">@if($document->due_date)Échéance : {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}@endif</td>
        </tr></table>
    </div>
    <div id="accent-corner"></div>
</div>

<!-- Émetteur -->
<div class="section-label">Émetteur</div>
<div class="parties-grid">
    <div class="party-cell">
        <div class="name">{{ $company->name }}</div>
        <div class="detail">
            {{ $company->address ?? '' }}<br>
            {{ $company->city ?? '' }}@if($company->country), {{ $company->country }}@endif<br>
            @if($company->trade_register)RCCM : {{ $company->trade_register }}<br>@endif
            @if($company->tax_id)NIF : {{ $company->tax_id }}@endif
        </div>
    </div>
    <div class="party-spacer"></div>
    <div class="party-cell">
        <div class="section-label">Facturé à</div><br>
        @if($document->customer)
        <div class="name">{{ $document->customer->name }}</div>
        <div class="detail">
            {{ $document->customer->address ?? '' }}<br>
            {{ $document->customer->city ?? '' }} {{ $document->customer->country ?? '' }}
        </div>
        @endif
    </div>
</div>

<div class="section-label">Détail</div>

<table class="items-table">
    <thead><tr>
        <th style="width:42%">Description</th>
        <th class="r" style="width:10%">Qté</th>
        <th style="width:8%">Unité</th>
        <th class="r" style="width:14%">P.U. HT</th>
        <th class="r" style="width:10%">TVA %</th>
        <th class="r" style="width:16%">Total HT</th>
    </tr></thead>
    <tbody>
    @foreach($document->lines as $line)
    <tr>
        <td>{{ $line->description }}</td>
        <td class="r">{{ number_format((float)($line->quantity ?? 0), 2, ',', ' ') }}</td>
        <td>{{ $line->unit ?? '' }}</td>
        <td class="r">{{ number_format((float)($line->unit_price ?? 0), 0, ',', ' ') }}</td>
        <td class="r">{{ number_format((float)($line->tax_rate ?? 0), 0, ',', ' ') }} %</td>
        <td class="r">{{ number_format((float)($line->total ?? 0), 0, ',', ' ') }}</td>
    </tr>
    @endforeach
    </tbody>
</table>

<div class="totaux-wrap">
    <div class="totaux-qr">
        @if($qrDataUri)
        <img src="{{ $qrDataUri }}" alt="QR">
        <div class="ql">Vérification</div>
        @endif
    </div>
    <div class="totaux-fill"></div>
    <div class="totaux-right">
        <table class="totaux-table">
            <tr><td>Sous-total HT</td><td>{{ number_format((float)($document->subtotal ?? 0), 0, ',', ' ') }} {{ $document->currency }}</td></tr>
            <tr><td>TVA</td><td>{{ number_format((float)($document->tax_amount ?? 0), 0, ',', ' ') }} {{ $document->currency }}</td></tr>
            <tr class="ttc-row"><td>TOTAL TTC</td><td>{{ number_format((float)($document->total ?? 0), 0, ',', ' ') }} {{ $document->currency }}</td></tr>
            @if(($document->paid_amount ?? 0) > 0)
            <tr><td>Payé</td><td>{{ number_format((float)$document->paid_amount, 0, ',', ' ') }} {{ $document->currency }}</td></tr>
            <tr class="reste-row"><td>Reste à payer</td><td>{{ number_format((float)(($document->total ?? 0) - ($document->paid_amount ?? 0)), 0, ',', ' ') }} {{ $document->currency }}</td></tr>
            @endif
        </table>
    </div>
</div>

@if($document->notes)
<div class="notes"><strong>Notes :</strong> {{ $document->notes }}</div>
@endif

<div class="sig-row">
    <div class="sig-col">
        <div class="sig-line"></div>
        <div class="sig-label">{{ $signatureLabels[0] ?? 'Signature émetteur' }}</div>
    </div>
    <div class="sig-col">
        <div class="sig-line"></div>
        <div class="sig-label">{{ $signatureLabels[1] ?? 'Signature client' }}</div>
    </div>
</div>

<div class="legal">Pénalités de retard applicables au taux légal. IBIG FactPro.</div>

</body>
</html>

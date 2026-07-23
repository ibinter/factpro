<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
@@page { margin: 22mm 18mm 28mm 18mm; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, serif; font-size: 11px; color: #1a1a1a; background: #fff; }

@if($watermark)
#watermark {
    position: fixed;
    top: 60mm; left: 15mm;
    font-size: 52px; font-weight: bold;
    color: rgba(0,0,0,.04);
    transform: rotate(-35deg);
    white-space: nowrap;
    z-index: 0;
}
@endif

/* Logo filigrane en fond */
@if($logoBase64)
#logo-bg {
    position: fixed;
    top: 70mm; left: 50mm;
    width: 100mm; height: 100mm;
    opacity: 0.04;
    z-index: 0;
}
#logo-bg img { width: 100%; }
@endif

#footer {
    position: fixed;
    bottom: -20mm; left: 0; right: 0;
    height: 18mm;
    border-top: 2px solid {{ $accentColor }};
    padding: 2mm 0 0 0;
    font-size: 7px;
    color: #999;
}
#footer table { width: 100%; }
#footer td { display: table-cell; }
#footer td:last-child { text-align: right; }
#footer td:nth-child(2) { text-align: center; }

/* TITRE CENTRÉ avec décoration */
.doc-title-wrap { text-align: center; margin-bottom: 6mm; padding-bottom: 4mm; border-bottom: 1px solid {{ $accentColor }}; }
.doc-title-decor { display: table; width: 100%; margin-bottom: 3mm; }
.doc-title-decor-line { display: table-cell; border-top: 2px solid {{ $accentColor }}; vertical-align: middle; }
.doc-title-decor-text {
    display: table-cell;
    white-space: nowrap;
    padding: 0 4mm;
    font-size: 18px;
    font-weight: bold;
    letter-spacing: 6px;
    text-transform: uppercase;
    color: {{ $primaryColor }};
}
.doc-number { font-size: 9px; color: #666; letter-spacing: 2px; margin-bottom: 2mm; }
.doc-dates { font-size: 8.5px; color: #888; }
.doc-dates span { margin: 0 3mm; }

/* Logo centré */
.logo-center { text-align: center; margin-bottom: 3mm; }
.logo-center img { max-height: 18mm; max-width: 50mm; }
.co-name-center { text-align: center; font-size: 13px; font-weight: bold; color: {{ $primaryColor }}; letter-spacing: 2px; margin-bottom: 1mm; }
.co-name-center .sub { font-size: 8px; color: #888; font-weight: normal; letter-spacing: 0; }

/* Parties en 2 colonnes avec bordures stylisées */
.parties-luxury { display: table; width: 100%; margin: 5mm 0; }
.party-lux { display: table-cell; width: 46%; padding: 4mm; border: 1.5px solid {{ $accentColor }}; }
.party-lux-sep { display: table-cell; width: 8%; }
.party-lux .label {
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: {{ $accentColor }};
    border-bottom: 1px solid {{ $accentColor }};
    padding-bottom: 1.5mm;
    margin-bottom: 2mm;
}
.party-lux .name { font-size: 12px; font-weight: bold; color: #111; margin-bottom: 1mm; }
.party-lux .detail { font-size: 8.5px; color: #555; line-height: 1.6; }

/* Items avec bordures latérales épaisses */
.items-table { width: 100%; border-collapse: collapse; margin: 4mm 0; border-left: 3px solid {{ $primaryColor }}; border-right: 3px solid {{ $primaryColor }}; }
.items-table thead tr { background: #f9f9f9; }
.items-table thead th {
    padding: 3.5mm 2mm;
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: {{ $primaryColor }};
    border-bottom: 2px solid {{ $primaryColor }};
    text-align: left;
}
.items-table thead th.r { text-align: right; }
.items-table tbody tr:nth-child(even) { background: #fffef8; }
.items-table tbody td { padding: 3mm 2mm; font-size: 9px; border-bottom: 1px solid #f0e8d0; }
.items-table tbody td.r { text-align: right; }

/* Totaux double bordure */
.totaux-section { display: table; width: 100%; margin-top: 4mm; }
.totaux-qr-lux { display: table-cell; width: 30mm; vertical-align: bottom; padding-right: 4mm; }
.totaux-qr-lux .qr-frame {
    border: 2px solid {{ $accentColor }};
    padding: 3mm;
    display: inline-block;
}
.totaux-qr-lux img { width: 20mm; height: 20mm; display: block; }
.totaux-qr-lux .ql { font-size: 6.5px; color: #aaa; text-align: center; margin-top: 1mm; }
.totaux-fill { display: table-cell; }
.totaux-bloc-lux { display: table-cell; width: 85mm; vertical-align: top; }
.totaux-dbl {
    border: 2px solid {{ $accentColor }};
    outline: 4px solid #fff;
    box-shadow: 0 0 0 5px {{ $accentColor }};
    padding: 5mm;
    margin: 2mm;
}
.totaux-dbl table { width: 100%; font-size: 9px; border-collapse: collapse; }
.totaux-dbl td { padding: 2mm 1mm; color: #333; }
.totaux-dbl td:last-child { text-align: right; font-weight: bold; }
.totaux-dbl .ttc-row td {
    font-size: 14px;
    color: {{ $primaryColor }};
    border-top: 2px solid {{ $accentColor }};
    padding-top: 3mm;
    font-weight: bold;
}
.totaux-dbl .reste-row td { color: #c00; font-size: 9px; }

.notes { font-size: 8px; color: #666; margin-top: 5mm; border-top: 1px solid #e0d0b0; padding-top: 3mm; }

/* Signatures stylisées */
.sig-row { display: table; width: 100%; margin-top: 10mm; }
.sig-col { display: table-cell; width: 50%; padding: 0 5mm; }
.sig-line-lux {
    border-bottom: 2px solid {{ $accentColor }};
    height: 14mm;
    margin-bottom: 2mm;
    position: relative;
}
.sig-label { font-size: 8px; color: #888; text-align: center; letter-spacing: 1px; }

.legal { font-size: 7px; color: #bbb; margin-top: 5mm; border-top: 1px double {{ $accentColor }}; padding-top: 2mm; }
</style>
</head>
<body>

@if($watermark)
<div id="watermark">{{ $watermark }}</div>
@endif

@if($logoBase64)
<div id="logo-bg"><img src="{{ $logoBase64 }}" alt=""></div>
@endif

<div id="footer">
    <table><tr>
        <td>{{ $document->number }}</td>
        <td>{{ $document->verification_url ?? '' }}</td>
        <td>Propulsé par IBIG FactPro</td>
    </tr></table>
</div>

<!-- Logo et nom société -->
@if($logoBase64)
<div class="logo-center"><img src="{{ $logoBase64 }}" alt="logo"></div>
@endif
<div class="co-name-center">
    {{ $company->name }}
    <div class="sub">
        @if($company->trade_register)RCCM : {{ $company->trade_register }}@endif
        @if($company->tax_id) &nbsp;|&nbsp; NIF : {{ $company->tax_id }}@endif
    </div>
</div>

<!-- Titre document -->
<div class="doc-title-wrap">
    <div class="doc-title-decor">
        <div class="doc-title-decor-line"></div>
        <div class="doc-title-decor-text">{{ $document->type_label }}</div>
        <div class="doc-title-decor-line"></div>
    </div>
    <div class="doc-number">N° {{ $document->number }}</div>
    <div class="doc-dates">
        <span>Émis le {{ \Carbon\Carbon::parse($document->date)->format('d/m/Y') }}</span>
        @if($document->due_date)<span>Échéance : {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}</span>@endif
    </div>
</div>

<!-- Parties -->
<div class="parties-luxury">
    <div class="party-lux">
        <div class="label">Émetteur</div>
        <div class="name">{{ $company->name }}</div>
        <div class="detail">
            {{ $company->address ?? '' }}<br>
            {{ $company->city ?? '' }}@if($company->country), {{ $company->country }}@endif<br>
            @if($company->phone){{ $company->phone }}<br>@endif
            @if($company->email){{ $company->email }}@endif
        </div>
    </div>
    <div class="party-lux-sep"></div>
    <div class="party-lux">
        <div class="label">Facturé à</div>
        @if($document->customer)
        <div class="name">{{ $document->customer->name }}</div>
        <div class="detail">
            {{ $document->customer->address ?? '' }}<br>
            {{ $document->customer->city ?? '' }} {{ $document->customer->country ?? '' }}
        </div>
        @endif
    </div>
</div>

<!-- Items -->
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

<!-- Totaux -->
<div class="totaux-section">
    <div class="totaux-qr-lux">
        @if($qrDataUri)
        <div class="qr-frame"><img src="{{ $qrDataUri }}" alt="QR"></div>
        <div class="ql">Vérification</div>
        @endif
    </div>
    <div class="totaux-fill"></div>
    <div class="totaux-bloc-lux">
        <div class="totaux-dbl">
            <table>
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
</div>

@if($document->notes)
<div class="notes"><em>{{ $document->notes }}</em></div>
@endif

<div class="sig-row">
    <div class="sig-col">
        <div class="sig-line-lux"></div>
        <div class="sig-label">{{ $signatureLabels[0] ?? 'Signature émetteur' }}</div>
    </div>
    <div class="sig-col">
        <div class="sig-line-lux"></div>
        <div class="sig-label">{{ $signatureLabels[1] ?? 'Signature client' }}</div>
    </div>
</div>

<div class="legal">Tout retard de paiement entraîne des pénalités de retard au taux légal en vigueur. IBIG FactPro.</div>

</body>
</html>

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
    background: #1a1a2e;
    color: rgba(255,255,255,.7);
    font-size: 7px;
    padding: 3mm 4mm 0 4mm;
}
#footer table { width: 100%; }
#footer td { display: table-cell; }
#footer td:last-child { text-align: right; }
#footer td:nth-child(2) { text-align: center; }

/* HEADER SOMBRE pleine largeur */
#dark-header {
    background: #1a1a2e;
    margin: -22mm -18mm 0 -18mm;
    padding: 7mm 10mm;
    display: table;
    width: calc(100% + 36mm);
}
#dh-left { display: table-cell; vertical-align: middle; }
#dh-left .logo img { max-height: 16mm; max-width: 35mm; margin-bottom: 2mm; }
#dh-left .co-name { color: #fff; font-size: 14px; font-weight: bold; }
#dh-left .co-sub { color: rgba(255,255,255,.6); font-size: 7.5px; margin-top: 1mm; }
#dh-right { display: table-cell; vertical-align: middle; text-align: right; }
#dh-right .doc-type {
    color: {{ $accentColor }};
    font-size: 22px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
}

/* Sous-bandeau $primaryColor */
#sub-band {
    background: {{ $primaryColor }};
    margin: 0 -18mm 5mm -18mm;
    padding: 2.5mm 10mm;
    display: table;
    width: calc(100% + 36mm);
}
#sub-band td { display: table-cell; color: #fff; font-size: 8.5px; }
#sub-band td:last-child { text-align: right; }

/* Parties */
.parties-grid { display: table; width: 100%; margin-bottom: 5mm; }
.party-cell { display: table-cell; width: 50%; padding: 4mm; background: #f9f9f9; }
.party-cell .label { font-size: 7px; text-transform: uppercase; color: #aaa; margin-bottom: 2mm; }
.party-cell .name { font-size: 11px; font-weight: bold; color: #111; }
.party-cell .detail { font-size: 8px; color: #555; margin-top: 1mm; line-height: 1.5; }
.party-spacer { display: table-cell; width: 4mm; }

/* Items sombres */
.items-table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
.items-table thead tr { background: #2d3748; }
.items-table thead th { padding: 3mm 2mm; font-size: 8px; color: #fff; text-align: left; }
.items-table thead th.r { text-align: right; }
.items-table tbody tr:nth-child(even) { background: #f8f9fa; }
.items-table tbody td { padding: 2.5mm 2mm; font-size: 8px; border-bottom: 1px solid #e9e9e9; }
.items-table tbody td.r { text-align: right; }

/* Totaux dark */
.totaux-wrap { display: table; width: 100%; margin-bottom: 5mm; }
.totaux-qr { display: table-cell; vertical-align: bottom; width: 28mm; padding-right: 4mm; }
.totaux-qr img { width: 22mm; height: 22mm; }
.totaux-qr .ql { font-size: 6.5px; color: #999; text-align: center; margin-top: 1mm; }
.totaux-fill { display: table-cell; }
.totaux-dark { display: table-cell; width: 85mm; vertical-align: top; }
.totaux-box {
    background: #1a1a2e;
    color: #fff;
    padding: 5mm;
    border-radius: 3px;
}
.totaux-box table { width: 100%; font-size: 8.5px; border-collapse: collapse; }
.totaux-box td { padding: 1.5mm 1mm; color: rgba(255,255,255,.8); }
.totaux-box td:last-child { text-align: right; font-weight: bold; }
.totaux-box .ttc-row td {
    font-size: 13px;
    color: {{ $accentColor }};
    border-top: 1px solid rgba(255,255,255,.2);
    padding-top: 2.5mm;
}
.totaux-box .reste-row td { color: #fc8181; }

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

<div id="dark-header">
    <div id="dh-left">
        @if($logoBase64)<div class="logo"><img src="{{ $logoBase64 }}" alt="logo"></div>@endif
        <div class="co-name">{{ $company->name }}</div>
        <div class="co-sub">
            @if($company->phone){{ $company->phone }}@endif
            @if($company->email) · {{ $company->email }}@endif
        </div>
    </div>
    <div id="dh-right">
        <div class="doc-type">{{ $document->type_label }}</div>
    </div>
</div>

<div id="sub-band">
    <table style="width:100%"><tr>
        <td>N° {{ $document->number }}</td>
        <td style="text-align:center">Émis le {{ \Carbon\Carbon::parse($document->date)->format('d/m/Y') }}</td>
        <td style="text-align:right">@if($document->due_date)Échéance : {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}@endif</td>
    </tr></table>
</div>

<div class="parties-grid">
    <div class="party-cell">
        <div class="label">Émetteur</div>
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
    <div class="totaux-dark">
        <div class="totaux-box">
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

<div class="legal">Pénalités de retard applicables au taux légal. Document généré par IBIG FactPro.</div>

</body>
</html>

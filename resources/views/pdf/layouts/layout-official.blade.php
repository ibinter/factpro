<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
@@page { margin: 22mm 18mm 28mm 18mm; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; color: #111; background: #fff; }

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
    border-top: 2px solid #333;
    padding: 2mm 0 0 0;
    font-size: 7px;
    color: #666;
}
#footer table { width: 100%; }
#footer td { display: table-cell; text-align: center; }
#footer td:first-child { text-align: left; }
#footer td:last-child { text-align: right; }

/* HEADER CENTRÉ OFFICIEL */
#official-header { text-align: center; margin-bottom: 5mm; padding-bottom: 4mm; border-bottom: 2px solid {{ $primaryColor }}; }
#official-header .logo-wrap { margin-bottom: 3mm; }
#official-header .logo-wrap img { max-height: 18mm; max-width: 50mm; }
#official-header .co-name {
    font-size: 16px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 4px;
    color: #111;
    margin-bottom: 2mm;
}
#official-header .co-coords { font-size: 8px; color: #555; line-height: 1.8; }
#official-header .co-coords span { margin: 0 3mm; }

/* Titre document centré style officiel */
.official-doc-title {
    text-align: center;
    margin: 4mm 0;
}
.official-doc-title .title-decor {
    font-size: 13px;
    font-weight: bold;
    color: {{ $primaryColor }};
    letter-spacing: 3px;
    text-transform: uppercase;
}
.official-doc-title .title-decor::before,
.official-doc-title .title-decor::after {
    content: ' ══ ';
    color: {{ $primaryColor }};
}
.official-doc-title .doc-number {
    font-size: 10px;
    color: #333;
    margin-top: 2mm;
    letter-spacing: 1px;
}
.official-doc-title .doc-dates { font-size: 8.5px; color: #666; margin-top: 1mm; }
.official-doc-title .doc-dates span { margin: 0 3mm; }

/* Encadrés émetteur / client en 2 colonnes */
.parties-official { display: table; width: 100%; margin: 4mm 0; }
.party-off { display: table-cell; width: 48%; border: 1px solid #333; padding: 4mm; }
.party-off-sep { display: table-cell; width: 4%; }
.party-off .label {
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: bold;
    color: {{ $primaryColor }};
    border-bottom: 1px solid #ccc;
    padding-bottom: 1.5mm;
    margin-bottom: 2mm;
}
.party-off .name { font-size: 11px; font-weight: bold; color: #111; margin-bottom: 1mm; }
.party-off .detail { font-size: 8.5px; color: #444; line-height: 1.6; }

/* Items sobre */
.items-table { width: 100%; border-collapse: collapse; margin: 4mm 0; }
.items-table thead th {
    padding: 3mm 2mm;
    font-size: 8px;
    text-align: left;
    border-bottom: 2px solid #333;
    color: #333;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.items-table thead th.r { text-align: right; }
.items-table tbody tr:nth-child(even) { background: #f8f8f8; }
.items-table tbody td { padding: 2.5mm 2mm; font-size: 9px; border-bottom: 1px solid #e0e0e0; color: #222; }
.items-table tbody td.r { text-align: right; }
.items-table tfoot td { border-top: 1px solid #999; font-size: 8px; color: #666; padding: 1.5mm 2mm; }

/* Totaux centrés dans encadré simple */
.totaux-section { display: table; width: 100%; margin-top: 4mm; }
.totaux-qr-off { display: table-cell; vertical-align: bottom; width: 30mm; padding-right: 4mm; }
.totaux-qr-off img { width: 22mm; height: 22mm; display: block; border: 1px solid #ddd; padding: 1mm; }
.totaux-qr-off .ql { font-size: 6px; color: #aaa; text-align: center; margin-top: 1mm; }
.totaux-fill { display: table-cell; }
.totaux-box-off { display: table-cell; width: 80mm; vertical-align: top; }
.totaux-encadre {
    border: 1px solid #333;
    padding: 4mm;
}
.totaux-encadre table { width: 100%; font-size: 9px; border-collapse: collapse; }
.totaux-encadre td { padding: 1.5mm 1mm; color: #333; }
.totaux-encadre td:last-child { text-align: right; font-weight: bold; }
.totaux-encadre .ttc-row td {
    font-size: 13px;
    font-weight: bold;
    color: {{ $primaryColor }};
    border-top: 2px solid #333;
    padding-top: 2mm;
}
.totaux-encadre .reste-row td { color: #c00; font-size: 9px; }

.notes { font-size: 8px; color: #555; margin-top: 4mm; border-top: 1px solid #ddd; padding-top: 3mm; }

/* Signatures équilibrées 2 colonnes */
.sig-row { display: table; width: 100%; margin-top: 10mm; }
.sig-col { display: table-cell; width: 50%; padding: 0 5mm; text-align: center; }
.sig-label-top { font-size: 8px; color: #555; margin-bottom: 10mm; }
.sig-line { border-bottom: 1px solid #333; height: 1px; margin-bottom: 2mm; }
.sig-label-bot { font-size: 7.5px; color: #666; }

.legal {
    font-size: 6.5px;
    color: #888;
    margin-top: 5mm;
    border-top: 1px solid #999;
    padding-top: 2mm;
    text-align: center;
}
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

<!-- En-tête officiel centré -->
<div id="official-header">
    @if($logoBase64)
    <div class="logo-wrap"><img src="{{ $logoBase64 }}" alt="logo"></div>
    @endif
    <div class="co-name">{{ $company->name }}</div>
    <div class="co-coords">
        @if($company->address)<span>{{ $company->address }}</span>@endif
        @if($company->city)<span>{{ $company->city }}@if($company->country), {{ $company->country }}@endif</span>@endif
        @if($company->phone)<span>Tél : {{ $company->phone }}</span>@endif
        @if($company->email)<span>{{ $company->email }}</span>@endif
        @if($company->trade_register)<span>RCCM : {{ $company->trade_register }}</span>@endif
        @if($company->tax_id)<span>NIF : {{ $company->tax_id }}</span>@endif
        @if($company->capital)<span>Capital : {{ $company->capital }}</span>@endif
    </div>
</div>

<!-- Titre document officiel -->
<div class="official-doc-title">
    <div class="title-decor">{{ $document->type_label }}</div>
    <div class="doc-number">N° {{ $document->number }}</div>
    <div class="doc-dates">
        <span>Émis le {{ \Carbon\Carbon::parse($document->date)->format('d/m/Y') }}</span>
        @if($document->due_date)<span>Échéance : {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}</span>@endif
        <span>{{ $document->currency }}</span>
    </div>
</div>

<!-- Parties 2 colonnes -->
<div class="parties-official">
    <div class="party-off">
        <div class="label">Émetteur</div>
        <div class="name">{{ $company->name }}</div>
        <div class="detail">
            {{ $company->address ?? '' }}<br>
            {{ $company->city ?? '' }}@if($company->country), {{ $company->country }}@endif<br>
            @if($company->phone){{ $company->phone }}<br>@endif
            @if($company->email){{ $company->email }}<br>@endif
            @if($company->trade_register)RCCM : {{ $company->trade_register }}<br>@endif
            @if($company->tax_id)NIF : {{ $company->tax_id }}@endif
        </div>
    </div>
    <div class="party-off-sep"></div>
    <div class="party-off">
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

<!-- Table items sobre -->
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

<!-- Totaux + QR -->
<div class="totaux-section">
    <div class="totaux-qr-off">
        @if($qrDataUri)
        <img src="{{ $qrDataUri }}" alt="QR">
        <div class="ql">Vérification</div>
        @endif
    </div>
    <div class="totaux-fill"></div>
    <div class="totaux-box-off">
        <div class="totaux-encadre">
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

<!-- Signatures 2 colonnes équilibrées -->
<div class="sig-row">
    <div class="sig-col">
        <div class="sig-label-top">{{ $signatureLabels[0] ?? 'Signature et cachet de l\'émetteur' }}</div>
        <div class="sig-line"></div>
        <div class="sig-label-bot">Date et signature</div>
    </div>
    <div class="sig-col">
        <div class="sig-label-top">{{ $signatureLabels[1] ?? 'Bon pour accord — Signature client' }}</div>
        <div class="sig-line"></div>
        <div class="sig-label-bot">Date et signature</div>
    </div>
</div>

<div class="legal">
    Tout retard de paiement entraîne des pénalités de retard au taux légal en vigueur, plus une indemnité forfaitaire de recouvrement.
    Document authentique généré par IBIG FactPro.
</div>

</body>
</html>

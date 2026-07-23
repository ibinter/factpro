<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
@@page { margin: 22mm 18mm 28mm 18mm; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }

/* Filigrane */
@if($watermark)
#watermark {
    position: fixed;
    top: 70mm; left: 20mm;
    font-size: 52px; font-weight: bold;
    color: rgba(200,0,0,.10);
    transform: rotate(-35deg);
    white-space: nowrap;
    z-index: 0;
}
@endif

/* Footer fixe */
#footer {
    position: fixed;
    bottom: -20mm; left: 0; right: 0;
    height: 18mm;
    background: {{ $primaryColor }};
    color: #fff;
    font-size: 7px;
    padding: 2mm 4mm;
    display: table; width: 100%;
}
#footer table { width: 100%; }
#footer td { display: table-cell; vertical-align: middle; }
#footer td:last-child { text-align: right; }
#footer td:nth-child(2) { text-align: center; }

/* HERO HEADER */
#hero {
    background: {{ $primaryColor }};
    margin: -22mm -18mm 5mm -18mm;
    padding: 8mm 10mm;
    min-height: 55mm;
    display: table;
    width: calc(100% + 36mm);
}
#hero-logo { display: table-cell; vertical-align: middle; width: 50mm; }
#hero-logo img { max-width: 40mm; max-height: 22mm; }
#hero-logo .co-name { color: #fff; font-size: 11px; font-weight: bold; margin-top: 2mm; }
#hero-title { display: table-cell; vertical-align: middle; text-align: center; }
#hero-title .doc-type { color: #fff; font-size: 26px; font-weight: bold; text-transform: uppercase; letter-spacing: 3px; }
#hero-title .co-main { color: rgba(255,255,255,.8); font-size: 9px; margin-top: 2mm; }
#hero-docinfo { display: table-cell; vertical-align: middle; text-align: right; width: 45mm; }
#hero-docinfo .doc-number { color: #fff; font-size: 13px; font-weight: bold; }
#hero-docinfo .doc-date { color: rgba(255,255,255,.8); font-size: 8px; margin-top: 2mm; }

/* Grille émetteur | client */
.parties-grid { display: table; width: 100%; margin-bottom: 5mm; border-spacing: 3mm 0; }
.party-cell { display: table-cell; width: 50%; padding: 4mm; background: #f7f7f7; border-radius: 3px; }
.party-cell .label { font-size: 7px; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 2mm; }
.party-cell .name { font-size: 11px; font-weight: bold; color: #111; margin-bottom: 1mm; }
.party-cell .detail { font-size: 8px; color: #555; line-height: 1.5; }
.party-spacer { display: table-cell; width: 4mm; }

/* Items table */
.items-table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
.items-table thead tr { background: {{ $secondaryColor }}; }
.items-table thead th { padding: 3mm 2mm; font-size: 8px; text-align: left; color: #fff; }
.items-table thead th.r { text-align: right; }
.items-table tbody tr:nth-child(even) { background: #fafafa; }
.items-table tbody td { padding: 2.5mm 2mm; font-size: 8px; border-bottom: 1px solid #eee; }
.items-table tbody td.r { text-align: right; }

/* Totaux */
.totaux-wrap { display: table; width: 100%; margin-bottom: 5mm; }
.totaux-left { display: table-cell; vertical-align: bottom; }
.totaux-right { display: table-cell; width: 80mm; vertical-align: top; }
.totaux-card {
    background: {{ $accentColor }};
    border-radius: 4px;
    padding: 5mm;
}
.totaux-card table { width: 100%; font-size: 8.5px; border-collapse: collapse; }
.totaux-card td { padding: 1.5mm 1mm; }
.totaux-card td:last-child { text-align: right; font-weight: bold; }
.totaux-card .ttc-row td { font-size: 12px; font-weight: bold; border-top: 2px solid {{ $primaryColor }}; color: {{ $primaryColor }}; padding-top: 2mm; }
.totaux-card .reste-row td { color: #c00; }
.qr-block { text-align: center; }
.qr-block img { width: 22mm; height: 22mm; }
.qr-block .qr-label { font-size: 6.5px; color: #777; margin-top: 1mm; }

/* Notes */
.notes { font-size: 7.5px; color: #555; margin-top: 4mm; border-top: 1px solid #ddd; padding-top: 3mm; }

/* Signatures */
.sig-row { display: table; width: 100%; margin-top: 8mm; }
.sig-col { display: table-cell; width: 50%; padding: 0 3mm; }
.sig-line { border-bottom: 1px solid #333; height: 12mm; margin-bottom: 2mm; }
.sig-label { font-size: 7.5px; color: #555; text-align: center; }

.legal { font-size: 6.5px; color: #888; margin-top: 4mm; border-top: 1px solid #ddd; padding-top: 2mm; }
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

<!-- HERO -->
<div id="hero">
    <div id="hero-logo">
        @if($logoBase64)
        <img src="{{ $logoBase64 }}" alt="logo">
        @endif
        <div class="co-name">{{ $company->name }}</div>
    </div>
    <div id="hero-title">
        <div class="doc-type">{{ $document->type_label }}</div>
        <div class="co-main">
            @if($company->city){{ $company->city }}@endif
            @if($company->phone) · {{ $company->phone }}@endif
            @if($company->email) · {{ $company->email }}@endif
        </div>
    </div>
    <div id="hero-docinfo">
        <div class="doc-number">N° {{ $document->number }}</div>
        <div class="doc-date">
            Émis le {{ \Carbon\Carbon::parse($document->date)->format('d/m/Y') }}
            @if($document->due_date)<br>Échéance {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}@endif
        </div>
    </div>
</div>

<!-- Émetteur | Client -->
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

<!-- Totaux + QR -->
<div class="totaux-wrap">
    <div class="totaux-left">
        <div class="qr-block">
            @if($qrDataUri)
            <img src="{{ $qrDataUri }}" alt="QR">
            <div class="qr-label">Scanner pour vérifier</div>
            @endif
        </div>
    </div>
    <div class="totaux-right">
        <div class="totaux-card">
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

<div class="legal">Tout retard de paiement entraîne des pénalités de retard au taux légal en vigueur. Document généré par IBIG FactPro.</div>

</body>
</html>

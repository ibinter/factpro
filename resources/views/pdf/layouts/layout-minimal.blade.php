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
    color: rgba(0,0,0,.05);
    transform: rotate(-35deg);
    white-space: nowrap;
    z-index: 0;
}
@endif

#footer {
    position: fixed;
    bottom: -20mm; left: 0; right: 0;
    height: 18mm;
    border-top: 1px solid #ccc;
    padding: 3mm 0 0 0;
    font-size: 7px;
    color: #aaa;
}
#footer table { width: 100%; }
#footer td { display: table-cell; }
#footer td:last-child { text-align: right; }
#footer td:nth-child(2) { text-align: center; }

/* HEADER ultra-minimaliste */
#hdr { display: table; width: 100%; margin-bottom: 3mm; }
#hdr-left { display: table-cell; vertical-align: bottom; }
#hdr-left .co-name {
    font-size: 9px;
    text-transform: lowercase;
    letter-spacing: 2px;
    color: #999;
    font-variant: small-caps;
}
#hdr-left .logo img { max-height: 10mm; max-width: 28mm; margin-bottom: 2mm; }
#hdr-right { display: table-cell; vertical-align: bottom; text-align: right; }
#hdr-right .doc-type {
    font-size: 30px;
    font-weight: bold;
    color: #2d2d2d;
    text-transform: uppercase;
    letter-spacing: 1px;
    line-height: 1;
}
.hdr-rule { border: none; border-top: 1px solid #ddd; margin: 2mm 0 4mm 0; }

/* Meta doc */
.doc-meta { font-size: 8px; color: #777; margin-bottom: 4mm; }
.doc-meta span { margin-right: 6mm; }

/* Client minimaliste */
.client-box {
    border: 1px dashed #ccc;
    padding: 3mm 4mm;
    margin-bottom: 5mm;
    display: inline-block;
    min-width: 60mm;
}
.client-box .label { font-size: 7px; color: #bbb; text-transform: uppercase; margin-bottom: 1mm; }
.client-box .name { font-size: 10px; color: #111; font-weight: bold; }
.client-box .detail { font-size: 7.5px; color: #666; margin-top: 1mm; line-height: 1.4; }

/* Items — ZERO border latérale */
.items-table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
.items-table thead th {
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #888;
    border-bottom: 1.5px solid #333;
    padding: 2mm 2mm 1.5mm 2mm;
    text-align: left;
}
.items-table thead th.r { text-align: right; }
.items-table tbody td {
    padding: 2mm 2mm;
    font-size: 8px;
    border-bottom: 1px solid #eee;
    color: #333;
}
.items-table tbody td.r { text-align: right; }

/* Totaux — aucune couleur sauf TTC */
.totaux-section { display: table; width: 100%; }
.qr-mini { display: table-cell; vertical-align: bottom; width: 18mm; }
.qr-mini img { width: 15mm; height: 15mm; }
.qr-mini .ql { font-size: 5.5px; color: #bbb; text-align: center; margin-top: 1mm; }
.totaux-space { display: table-cell; }
.totaux-right { display: table-cell; width: 75mm; vertical-align: top; }
.totaux-table { width: 100%; font-size: 8px; border-collapse: collapse; }
.totaux-table td { padding: 1.5mm 1mm; color: #555; }
.totaux-table td:last-child { text-align: right; }
.totaux-table .ttc-row td {
    font-size: 16px;
    font-weight: bold;
    color: #111;
    border-top: 1.5px solid #333;
    padding-top: 2mm;
}
.totaux-table .reste-row td { font-size: 8px; color: #c00; }

.notes { font-size: 7.5px; color: #888; margin-top: 4mm; padding-top: 3mm; border-top: 1px solid #eee; }

.sig-row { display: table; width: 100%; margin-top: 10mm; }
.sig-col { display: table-cell; width: 50%; padding: 0 4mm; }
.sig-line { border-bottom: 1px solid #888; height: 14mm; margin-bottom: 2mm; }
.sig-label { font-size: 7px; color: #999; text-align: center; }

.legal { font-size: 6px; color: #bbb; margin-top: 5mm; border-top: 1px solid #eee; padding-top: 2mm; }
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

<div id="hdr">
    <div id="hdr-left">
        @if($logoBase64)<div class="logo"><img src="{{ $logoBase64 }}" alt="logo"></div>@endif
        <div class="co-name">{{ $company->name }}</div>
    </div>
    <div id="hdr-right">
        <div class="doc-type">{{ $document->type_label }}</div>
    </div>
</div>
<hr class="hdr-rule">

<div class="doc-meta">
    <span>N° <strong>{{ $document->number }}</strong></span>
    <span>{{ \Carbon\Carbon::parse($document->date)->format('d/m/Y') }}</span>
    @if($document->due_date)<span>Échéance : {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}</span>@endif
    <span>{{ $document->currency }}</span>
</div>

@if($document->customer)
<div class="client-box">
    <div class="label">Destinataire</div>
    <div class="name">{{ $document->customer->name }}</div>
    <div class="detail">
        {{ $document->customer->address ?? '' }}<br>
        {{ $document->customer->city ?? '' }} {{ $document->customer->country ?? '' }}
    </div>
</div>
@endif

<table class="items-table">
    <thead><tr>
        <th style="width:44%">Description</th>
        <th class="r" style="width:9%">Qté</th>
        <th style="width:8%">Unité</th>
        <th class="r" style="width:14%">P.U. HT</th>
        <th class="r" style="width:9%">TVA</th>
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

<div class="totaux-section">
    <div class="qr-mini">
        @if($qrDataUri)
        <img src="{{ $qrDataUri }}" alt="QR">
        <div class="ql">vérifier</div>
        @endif
    </div>
    <div class="totaux-space"></div>
    <div class="totaux-right">
        <table class="totaux-table">
            <tr><td>Sous-total HT</td><td>{{ number_format((float)($document->subtotal ?? 0), 0, ',', ' ') }} {{ $document->currency }}</td></tr>
            <tr><td>TVA</td><td>{{ number_format((float)($document->tax_amount ?? 0), 0, ',', ' ') }} {{ $document->currency }}</td></tr>
            <tr class="ttc-row"><td>Total TTC</td><td>{{ number_format((float)($document->total ?? 0), 0, ',', ' ') }} {{ $document->currency }}</td></tr>
            @if(($document->paid_amount ?? 0) > 0)
            <tr><td>Payé</td><td>{{ number_format((float)$document->paid_amount, 0, ',', ' ') }} {{ $document->currency }}</td></tr>
            <tr class="reste-row"><td>Reste dû</td><td>{{ number_format((float)(($document->total ?? 0) - ($document->paid_amount ?? 0)), 0, ',', ' ') }} {{ $document->currency }}</td></tr>
            @endif
        </table>
    </div>
</div>

@if($document->notes)
<div class="notes">{{ $document->notes }}</div>
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

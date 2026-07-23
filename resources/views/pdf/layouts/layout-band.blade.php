<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
@@page { margin: 22mm 18mm 28mm 90mm; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; }

/* Bande latérale gauche fixe */
#band {
    position: fixed;
    top: -22mm; left: -18mm;
    width: 72mm;
    bottom: -28mm;
    background: {{ $primaryColor }};
    color: #fff;
    padding: 20mm 8mm 30mm 8mm;
}
#band .logo { text-align: center; margin-bottom: 8mm; }
#band .logo img { max-width: 40mm; max-height: 18mm; }
#band .co-name { font-size: 13px; font-weight: bold; text-align: center; margin-bottom: 4mm; word-wrap: break-word; }
#band .co-info { font-size: 8px; line-height: 1.6; opacity: .85; }
#band .co-info p { margin-bottom: 2px; }
#band .band-footer { position: absolute; bottom: 32mm; left: 8mm; right: 8mm; font-size: 7px; opacity: .7; border-top: 1px solid rgba(255,255,255,.3); padding-top: 4mm; }

/* Filigrane */
@if($watermark)
#watermark {
    position: fixed;
    top: 60mm; left: 30mm;
    font-size: 52px; font-weight: bold;
    color: rgba(200,0,0,.10);
    transform: rotate(-35deg);
    white-space: nowrap;
    z-index: 0;
}
@endif

/* Footer fixe bas */
#footer {
    position: fixed;
    bottom: -20mm; left: 0; right: 0;
    height: 18mm;
    border-top: 2px solid {{ $primaryColor }};
    padding: 2mm 4mm 0 4mm;
    font-size: 7px; color: #555;
    display: table; width: 100%;
}
#footer td { display: table-cell; vertical-align: middle; width: 33%; }
#footer td:last-child { text-align: right; }
#footer td:nth-child(2) { text-align: center; }

/* Contenu */
#main { }

#doc-title {
    font-size: 28px;
    font-weight: bold;
    color: {{ $primaryColor }};
    text-transform: uppercase;
    letter-spacing: 2px;
    margin-bottom: 3mm;
}
#doc-meta { font-size: 8.5px; color: #555; margin-bottom: 5mm; }
#doc-meta span { margin-right: 8mm; }

/* Bloc client */
.client-card {
    background: #f5f5f5;
    border-left: 3px solid {{ $primaryColor }};
    padding: 5mm 6mm;
    margin-bottom: 5mm;
    border-radius: 2px;
}
.client-card .label { font-size: 7.5px; text-transform: uppercase; color: #888; letter-spacing: 1px; margin-bottom: 2mm; }
.client-card .name { font-size: 11px; font-weight: bold; color: #111; }
.client-card .addr { font-size: 8px; color: #444; margin-top: 1mm; line-height: 1.5; }

/* Table items */
.items-table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
.items-table thead tr { background: {{ $primaryColor }}; color: #fff; }
.items-table thead th { padding: 3mm 2mm; font-size: 8px; text-align: left; }
.items-table thead th.r { text-align: right; }
.items-table tbody tr:nth-child(even) { background: {{ $secondaryColor }}; }
.items-table tbody td { padding: 2.5mm 2mm; font-size: 8px; border-bottom: 1px solid #e8e8e8; }
.items-table tbody td.r { text-align: right; }

/* Totaux + QR */
.totaux-row { display: table; width: 100%; }
.totaux-qr { display: table-cell; width: 25mm; vertical-align: bottom; padding-right: 4mm; }
.totaux-qr img { width: 22mm; height: 22mm; }
.totaux-qr .qr-label { font-size: 6px; color: #777; text-align: center; margin-top: 1mm; }
.totaux-bloc { display: table-cell; vertical-align: top; }
.totaux-table { width: 100%; font-size: 8.5px; }
.totaux-table tr td { padding: 1.5mm 2mm; }
.totaux-table tr td:last-child { text-align: right; font-weight: bold; }
.totaux-table .ttc-row td { font-size: 12px; color: {{ $primaryColor }}; border-top: 2px solid {{ $primaryColor }}; padding-top: 2mm; }
.totaux-table .reste-row td { color: #c00; }

/* Notes */
.notes { font-size: 7.5px; color: #555; margin-top: 4mm; border-top: 1px solid #ddd; padding-top: 3mm; }

/* Signatures */
.sig-row { display: table; width: 100%; margin-top: 8mm; }
.sig-col { display: table-cell; width: 50%; padding: 0 3mm; }
.sig-line { border-bottom: 1px solid #333; height: 12mm; margin-bottom: 2mm; }
.sig-label { font-size: 7.5px; color: #555; text-align: center; }

/* Footer légal */
.legal { font-size: 6.5px; color: #888; margin-top: 4mm; border-top: 1px solid #ddd; padding-top: 2mm; }
</style>
</head>
<body>

@if($watermark)
<div id="watermark">{{ $watermark }}</div>
@endif

<!-- Bande latérale -->
<div id="band">
    @if($logoBase64)
    <div class="logo"><img src="{{ $logoBase64 }}" alt="logo"></div>
    @endif
    <div class="co-name">{{ $company->name }}</div>
    <div class="co-info">
        @if($company->address)<p>{{ $company->address }}</p>@endif
        @if($company->city)<p>{{ $company->city }}@if($company->country), {{ $company->country }}@endif</p>@endif
        @if($company->phone)<p>Tél : {{ $company->phone }}</p>@endif
        @if($company->email)<p>{{ $company->email }}</p>@endif
        @if($company->trade_register)<p>RCCM : {{ $company->trade_register }}</p>@endif
        @if($company->tax_id)<p>NIF : {{ $company->tax_id }}</p>@endif
        @if($company->capital)<p>Cap. : {{ $company->capital }}</p>@endif
    </div>
    <div class="band-footer">{{ $company->website ?? '' }}</div>
</div>

<!-- Footer fixe bas -->
<div id="footer">
    <table style="width:100%"><tr>
        <td>{{ $document->number }}</td>
        <td style="text-align:center">{{ $document->verification_url ?? '' }}</td>
        <td style="text-align:right">Propulsé par IBIG FactPro</td>
    </tr></table>
</div>

<!-- Contenu principal -->
<div id="main">
    <div id="doc-title">{{ $document->type_label }}</div>
    <div id="doc-meta">
        <span>N° {{ $document->number }}</span>
        <span>Date : {{ \Carbon\Carbon::parse($document->date)->format('d/m/Y') }}</span>
        @if($document->due_date)<span>Échéance : {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}</span>@endif
    </div>

    <!-- Client -->
    @if($document->customer)
    <div class="client-card">
        <div class="label">Facturé à</div>
        <div class="name">{{ $document->customer->name }}</div>
        <div class="addr">
            {{ $document->customer->address ?? '' }}<br>
            {{ $document->customer->city ?? '' }} {{ $document->customer->country ?? '' }}
        </div>
    </div>
    @endif

    <!-- Items -->
    <table class="items-table">
        <thead><tr>
            <th style="width:40%">Description</th>
            <th class="r" style="width:10%">Qté</th>
            <th style="width:8%">Unité</th>
            <th class="r" style="width:14%">P.U. HT</th>
            <th class="r" style="width:10%">TVA %</th>
            <th class="r" style="width:18%">Total HT</th>
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
    <div class="totaux-row">
        <div class="totaux-qr">
            @if($qrDataUri)
            <img src="{{ $qrDataUri }}" alt="QR">
            <div class="qr-label">Vérification</div>
            @endif
        </div>
        <div class="totaux-bloc">
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

    <!-- Signatures -->
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

    <!-- Mentions légales -->
    <div class="legal">
        Tout retard de paiement entraîne des pénalités de retard au taux légal en vigueur. Document généré par IBIG FactPro.
    </div>
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
@@page { margin: 22mm 18mm 28mm 18mm; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; background: #f0f2f5; }

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
    background: #fff;
    border-top: 1px solid #ddd;
    font-size: 7px;
    color: #888;
    padding: 3mm 4mm 0 4mm;
}
#footer table { width: 100%; }
#footer td { display: table-cell; }
#footer td:last-child { text-align: right; }
#footer td:nth-child(2) { text-align: center; }

/* Card générique */
.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,.1);
    padding: 12px;
    margin-bottom: 10px;
}
.card-accent {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,.12);
    padding: 12px;
    margin-bottom: 10px;
    border-top: 3px solid {{ $primaryColor }};
}

/* Card header */
#hdr-card { display: table; width: 100%; }
#hdr-left { display: table-cell; vertical-align: middle; }
#hdr-left .logo img { max-height: 14mm; max-width: 35mm; margin-bottom: 2mm; }
#hdr-left .co-name { font-size: 13px; font-weight: bold; color: #111; }
#hdr-left .co-sub { font-size: 7.5px; color: #888; margin-top: 1mm; }
#hdr-right { display: table-cell; vertical-align: middle; text-align: right; }
#hdr-right .doc-info-card {
    background: {{ $primaryColor }};
    color: #fff;
    border-radius: 6px;
    padding: 4mm 6mm;
    display: inline-block;
}
#hdr-right .doc-type { font-size: 14px; font-weight: bold; text-transform: uppercase; }
#hdr-right .doc-number { font-size: 8px; margin-top: 1mm; opacity: .85; }
#hdr-right .doc-date { font-size: 8px; opacity: .85; }

/* Card client */
.client-card-inner { display: table; width: 100%; }
.client-label { display: table-cell; width: 20mm; vertical-align: top; }
.client-label span {
    background: {{ $primaryColor }};
    color: #fff;
    font-size: 6.5px;
    text-transform: uppercase;
    padding: 1mm 2mm;
    border-radius: 3px;
    display: inline-block;
}
.client-data { display: table-cell; vertical-align: top; }
.client-data .name { font-size: 11px; font-weight: bold; color: #111; margin-bottom: 1mm; }
.client-data .detail { font-size: 8px; color: #555; line-height: 1.5; }

/* Card items */
.items-table { width: 100%; border-collapse: collapse; }
.items-table thead tr { background: #f8f8f8; }
.items-table thead th {
    padding: 2.5mm 2mm;
    font-size: 7.5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #888;
    border-bottom: 1.5px solid #eee;
    text-align: left;
}
.items-table thead th.r { text-align: right; }
.items-table tbody tr:nth-child(even) { background: #fafafa; }
.items-table tbody td { padding: 2mm; font-size: 8px; border-bottom: 1px solid #f0f0f0; color: #333; }
.items-table tbody td.r { text-align: right; }

/* Card totaux — fond primaryColor */
.card-totaux {
    background: {{ $primaryColor }};
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,.12);
    padding: 12px;
    margin-bottom: 10px;
    display: table;
    width: 100%;
}
.totaux-qr-cell { display: table-cell; width: 28mm; vertical-align: bottom; }
.totaux-qr-cell .qr-card {
    background: rgba(255,255,255,.15);
    border-radius: 4px;
    padding: 3mm;
    display: inline-block;
}
.totaux-qr-cell img { width: 18mm; height: 18mm; display: block; }
.totaux-qr-cell .ql { font-size: 6px; color: rgba(255,255,255,.7); text-align: center; margin-top: 1mm; }
.totaux-data-cell { display: table-cell; vertical-align: top; padding-left: 4mm; }
.totaux-data-cell table { width: 100%; font-size: 8.5px; border-collapse: collapse; }
.totaux-data-cell td { padding: 1.5mm 1mm; color: rgba(255,255,255,.85); }
.totaux-data-cell td:last-child { text-align: right; font-weight: bold; }
.totaux-data-cell .ttc-row td {
    font-size: 14px;
    color: #fff;
    border-top: 1px solid rgba(255,255,255,.3);
    padding-top: 2mm;
}
.totaux-data-cell .reste-row td { color: #ffd0d0; }

/* Card émetteur */
.emetteur-card { display: table; width: 100%; }
.emetteur-left { display: table-cell; width: 50%; }
.emetteur-right { display: table-cell; width: 50%; padding-left: 4mm; }
.emetteur-label { font-size: 7px; text-transform: uppercase; color: #aaa; margin-bottom: 1mm; }
.emetteur-name { font-size: 10px; font-weight: bold; color: #111; }
.emetteur-detail { font-size: 7.5px; color: #666; line-height: 1.5; }

.notes { font-size: 7.5px; color: #666; }

.sig-row { display: table; width: 100%; }
.sig-col { display: table-cell; width: 50%; padding: 0 3mm; }
.sig-line { border-bottom: 1px solid #aaa; height: 12mm; margin-bottom: 2mm; }
.sig-label { font-size: 7.5px; color: #888; text-align: center; }

.legal { font-size: 6.5px; color: #bbb; margin-top: 4mm; }
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

<!-- Card Header -->
<div class="card-accent">
    <div id="hdr-card">
        <div id="hdr-left">
            @if($logoBase64)<div class="logo"><img src="{{ $logoBase64 }}" alt="logo"></div>@endif
            <div class="co-name">{{ $company->name }}</div>
            <div class="co-sub">
                @if($company->phone){{ $company->phone }}@endif
                @if($company->email) · {{ $company->email }}@endif
            </div>
        </div>
        <div id="hdr-right">
            <div class="doc-info-card">
                <div class="doc-type">{{ $document->type_label }}</div>
                <div class="doc-number">N° {{ $document->number }}</div>
                <div class="doc-date">{{ \Carbon\Carbon::parse($document->date)->format('d/m/Y') }}
                    @if($document->due_date) · Éch. {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}@endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card émetteur / client -->
<div class="card">
    <div class="emetteur-card">
        <div class="emetteur-left">
            <div class="emetteur-label">Émetteur</div>
            <div class="emetteur-name">{{ $company->name }}</div>
            <div class="emetteur-detail">
                {{ $company->address ?? '' }}<br>
                {{ $company->city ?? '' }}@if($company->country), {{ $company->country }}@endif<br>
                @if($company->trade_register)RCCM : {{ $company->trade_register }}<br>@endif
                @if($company->tax_id)NIF : {{ $company->tax_id }}@endif
            </div>
        </div>
        <div class="emetteur-right">
            <div class="emetteur-label">Facturé à</div>
            @if($document->customer)
            <div class="emetteur-name">{{ $document->customer->name }}</div>
            <div class="emetteur-detail">
                {{ $document->customer->address ?? '' }}<br>
                {{ $document->customer->city ?? '' }} {{ $document->customer->country ?? '' }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Card Items -->
<div class="card">
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
</div>

<!-- Card Totaux -->
<div class="card-totaux">
    <div class="totaux-qr-cell">
        @if($qrDataUri)
        <div class="qr-card"><img src="{{ $qrDataUri }}" alt="QR"></div>
        <div class="ql">Vérification</div>
        @endif
    </div>
    <div class="totaux-data-cell">
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

@if($document->notes)
<div class="card">
    <div class="notes"><strong>Notes :</strong> {{ $document->notes }}</div>
</div>
@endif

<!-- Card Signatures -->
<div class="card">
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
</div>

<div class="legal">Pénalités de retard applicables au taux légal. Document généré par IBIG FactPro.</div>

</body>
</html>

{{-- Collection Centenniale · Eco Nature · Vert forêt #2D6A4F + Émeraude #40916C --}}
@php
    $primary   = '#2D6A4F';
    $secondary = '#1B4332';
    $accent    = '#40916C';
    $lines     = $document->lines ?? collect();
    $totalHT   = collect($lines)->sum(fn($l) => (float)($l->line_total ?? 0));
    $cur       = $document->currency ?? 'XOF';
    $fmt       = fn($n) => number_format((float)$n, 0, ',', ' ');
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->type_label ?? 'Document' }} {{ $document->number ?? '' }}</title>
<style>
    @@page { margin: 40mm 13mm 20mm 13mm; }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #24352c; }
    #header { position: fixed; top: -40mm; left: -13mm; right: -13mm; height: 38mm;
        background: {{ $primary }}; color: #fff; padding: 6mm 13mm; }
    #header table { width: 100%; }
    .co-name { font-size: 16px; font-weight: bold; color: #fff; letter-spacing: .5px; }
    .co-info { font-size: 7.5px; color: #d9ece2; line-height: 1.6; margin-top: 1mm; }
    .doc-type { font-size: 21px; font-weight: bold; color: #fff; text-transform: uppercase; text-align: right; letter-spacing: 1px; }
    .doc-num  { font-size: 8.5px; color: #cfe7da; text-align: right; margin-top: 1mm; }
    #footer { position: fixed; bottom: -18mm; left: -13mm; right: -13mm; height: 16mm;
        border-top: 2px solid {{ $accent }}; padding: 2mm 13mm 0; font-size: 7px; color: #7d8f85; }
    #footer table { width: 100%; }
    .ft-brand { color: {{ $primary }}; font-weight: bold; }
    #watermark { position: fixed; top: 62mm; left: 28mm; font-size: 52px; font-weight: bold;
        color: rgba(45,106,79,.10); transform: rotate(-35deg); white-space: nowrap; }
    .leaf-rule { height: 4px; background: {{ $accent }}; margin-bottom: 4mm; }
    .addr-table { width: 100%; margin-bottom: 4mm; }
    .info-card { background: #eef6f1; border-top: 2px solid {{ $accent }}; padding: 3mm 4mm; }
    .client-card { background: #fff; border-left: 3px solid {{ $primary }}; padding: 3mm 4mm; }
    .sect-label { font-size: 7px; font-weight: bold; text-transform: uppercase; color: {{ $primary }};
        letter-spacing: .8px; border-bottom: 1px solid #cfe3d7; padding-bottom: 1mm; margin-bottom: 2mm; }
    .client-name { font-size: 11px; font-weight: bold; color: {{ $primary }}; margin-bottom: 1mm; }
    .client-detail { font-size: 8px; color: #556; line-height: 1.6; }
    .meta-label { color: #8aa397; font-size: 8px; } .meta-value { font-weight: bold; font-size: 8.5px; color: {{ $primary }}; }
    .lines-table { width: 100%; border-collapse: collapse; margin-bottom: 4mm; }
    .lines-table thead tr { background: {{ $primary }}; color: #fff; }
    .lines-table thead th { padding: 2.2mm 2mm; font-size: 7.5px; text-align: left; text-transform: uppercase; letter-spacing: .5px; }
    .lines-table thead th.r { text-align: right; }
    .lines-table tbody tr.even { background: #f2f8f4; }
    .lines-table tbody td { padding: 2mm; font-size: 8px; border-bottom: 1px solid #dcebe2; vertical-align: top; }
    .lines-table tbody td.r { text-align: right; }
    .totals-table { width: 100%; font-size: 8.5px; }
    .totals-table td { padding: 1mm 2mm; } .totals-table td:last-child { text-align: right; font-weight: bold; }
    .row-total td { font-size: 12px; color: #fff; background: {{ $primary }};
        border-top: 2px solid {{ $secondary }}; padding: 2mm; }
    .row-due td { color: #b00; font-size: 10px; }
    .bottom-row { display: table; width: 100%; margin-bottom: 4mm; }
    .bottom-qr { display: table-cell; width: 28mm; vertical-align: bottom; padding-right: 4mm; }
    .bottom-qr img { width: 24mm; height: 24mm; } .qr-lbl { font-size: 6px; color: #8aa397; text-align: center; margin-top: 1mm; }
    .bottom-totals { display: table-cell; vertical-align: top; }
    .notes-box { font-size: 8px; color: #556; margin-bottom: 3mm; border-top: 1px solid #dcebe2; padding-top: 2mm; }
    .notes-title { font-weight: bold; color: {{ $primary }}; margin-bottom: 1mm; }
    .eco-badge { background: #eef6f1; border: 1px solid {{ $accent }}; color: {{ $primary }};
        font-size: 7px; padding: 1.5mm 3mm; margin-bottom: 3mm; }
    .sig-row { display: table; width: 100%; margin-top: 6mm; }
    .sig-col { display: table-cell; width: 50%; padding: 0 4mm; }
    .sig-line { border-bottom: 1px solid {{ $primary }}; height: 8mm; margin-bottom: 1.5mm; }
    .sig-meta { font-size: 7px; color: #556; line-height: 1.7; }
</style>
</head>
<body>

<div id="header">
    <table><tr>
        <td style="width:60%;vertical-align:middle;">
            @if($logoBase64 ?? null)<img src="{{ $logoBase64 }}" style="max-height:13mm;max-width:42mm;display:block;margin-bottom:1.5mm;" alt="Logo"/>@endif
            <div class="co-name">{{ $company->name ?? '' }}</div>
            <div class="co-info">
                @if(!empty($company->address)){{ $company->address }}<br/>@endif
                @if(!empty($company->city)){{ $company->city }}@if(!empty($company->country)), {{ $company->country }}@endif<br/>@endif
                @if(!empty($company->phone))Tél : {{ $company->phone }}@endif @if(!empty($company->email)) | {{ $company->email }}@endif<br/>
                @if(!empty($company->tax_id))N° Fiscal : {{ $company->tax_id }}@endif
            </div>
        </td>
        <td style="width:40%;vertical-align:middle;">
            <div class="doc-type">{{ $document->type_label ?? 'Document' }}</div>
            <div class="doc-num">N° {{ $document->number ?? '—' }}</div>
        </td>
    </tr></table>
</div>

@if($watermark ?? false)<div id="watermark">{{ $watermark }}</div>@endif

<div id="footer"><table><tr>
    <td>{{ $company->name ?? '' }}</td>
    <td style="text-align:center;">{{ $document->number ?? '' }}</td>
    <td style="text-align:right;"><span class="ft-brand">IBIG FactPro</span></td>
</tr></table></div>

<div class="leaf-rule"></div>
<div class="eco-badge">🌿 Document éco-responsable — merci de privilégier l'archivage numérique à l'impression.</div>

<table class="addr-table"><tr>
    <td style="width:45%;vertical-align:top;padding-right:4mm;">
        <div class="info-card">
            <div class="sect-label">Informations</div>
            <table style="width:100%;">
                <tr><td class="meta-label">Émission</td><td class="meta-value">{{ $document->issue_date ? \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') : '—' }}</td></tr>
                @if(!empty($document->due_date))<tr><td class="meta-label">Échéance</td><td class="meta-value">{{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}</td></tr>@endif
                <tr><td class="meta-label">Devise</td><td class="meta-value">{{ $cur }}</td></tr>
            </table>
        </div>
    </td>
    <td style="width:55%;vertical-align:top;padding-left:4mm;">
        <div class="client-card">
            <div class="sect-label">Destinataire</div>
            <div class="client-name">{{ $document->customer->name ?? '—' }}</div>
            <div class="client-detail">
                @if(!empty($document->customer->address)){{ $document->customer->address }}<br/>@endif
                @if(!empty($document->customer->city)){{ $document->customer->city }}<br/>@endif
                @if(!empty($document->customer->phone))Tél : {{ $document->customer->phone }}<br/>@endif
                @if(!empty($document->customer->email)){{ $document->customer->email }}@endif
            </div>
        </div>
    </td>
</tr></table>

@if(!empty($document->subject))<div class="notes-box"><strong>Objet :</strong> {{ $document->subject }}</div>@endif

<table class="lines-table">
    <thead><tr>
        <th style="width:5%">#</th><th style="width:41%">Désignation</th>
        <th class="r" style="width:10%">Qté</th><th class="r" style="width:15%">P.U.</th>
        <th class="r" style="width:12%">TVA</th><th class="r" style="width:17%">Total HT</th>
    </tr></thead>
    <tbody>
    @foreach($lines as $i => $line)
        <tr class="{{ $i % 2 === 0 ? 'even' : '' }}">
            <td>{{ $i + 1 }}</td>
            <td><strong>{{ $line->description ?? $line->name ?? '—' }}</strong></td>
            <td class="r">{{ number_format((float)($line->quantity ?? 1), 2) }}</td>
            <td class="r">{{ $fmt($line->unit_price ?? 0) }}</td>
            <td class="r">@if(!empty($line->tax_rate)){{ number_format((float)$line->tax_rate, 0) }}%@else —@endif</td>
            <td class="r">{{ $fmt($line->line_total ?? 0) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="bottom-row">
    <div class="bottom-qr">
        @if($qrDataUri ?? null)<img src="{{ $qrDataUri }}" alt="QR"/><div class="qr-lbl">Vérification</div>@endif
    </div>
    <div class="bottom-totals">
        <table class="totals-table">
            <tr><td>Sous-total HT</td><td>{{ $fmt($document->subtotal ?? $totalHT) }} {{ $cur }}</td></tr>
            @if((float)($document->tax_amount ?? 0) > 0)<tr><td>TVA</td><td>{{ $fmt($document->tax_amount) }} {{ $cur }}</td></tr>@endif
            <tr class="row-total"><td>TOTAL TTC</td><td>{{ $fmt($document->total ?? 0) }} {{ $cur }}</td></tr>
            @if((float)($document->amount_paid ?? 0) > 0)
                <tr><td>Payé</td><td>- {{ $fmt($document->amount_paid) }} {{ $cur }}</td></tr>
                <tr class="row-due"><td>RESTE À PAYER</td><td>{{ $fmt(($document->total ?? 0) - ($document->amount_paid ?? 0)) }} {{ $cur }}</td></tr>
            @endif
        </table>
    </div>
</div>

@if(!empty($document->notes))<div class="notes-box"><div class="notes-title">Notes</div>{{ $document->notes }}</div>@endif
@if(!empty($document->terms))<div class="notes-box"><div class="notes-title">Conditions générales</div>{{ $document->terms }}</div>@endif

<div class="sig-row">
    <div class="sig-col"><div class="sect-label">Émetteur</div><div class="sig-line"></div><div class="sig-meta">Nom : _______________<br/>Date : _______________</div></div>
    <div class="sig-col"><div class="sect-label">Client</div><div class="sig-line"></div><div class="sig-meta">Nom : _______________<br/>Date : _______________</div></div>
</div>

</body>
</html>

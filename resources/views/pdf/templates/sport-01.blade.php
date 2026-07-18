<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @page { margin: 105px 50px 90px 50px; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #F3F4F6; margin: 0; background: #111; }
    .watermark { position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 42px; font-weight: bold;
        color: rgba(220,38,38,0.18); z-index: 1000; letter-spacing: 4px; }
    header { position: fixed; top: -75px; left: 0; right: 0; height: 65px;
        background: #111; border-left: 6px solid #DC2626; border-bottom: 2px solid #DC2626; padding: 8px 14px; }
    .company-name { font-size: 18px; font-weight: bold; color: #fff; text-transform: uppercase; letter-spacing: 2px; }
    .company-meta { font-size: 8px; color: #9CA3AF; line-height: 1.5; }
    .doc-title { font-size: 16px; font-weight: bold; color: #DC2626; text-align: right; text-transform: uppercase; letter-spacing: 2px; }
    .doc-number { font-size: 10px; color: #D1D5DB; text-align: right; }
    .sport-tag { font-size: 8px; color: #DC2626; text-align: right; text-transform: uppercase; letter-spacing: 2px; font-weight: bold; }
    footer { position: fixed; bottom: -70px; left: 0; right: 0; height: 60px;
        font-size: 8px; color: #9CA3AF; border-top: 2px solid #DC2626; padding-top: 6px; background: #111; }
    .addresses { width: 100%; margin-bottom: 18px; }
    .addresses td { vertical-align: top; width: 50%; }
    .badge-box { border: 1px solid #374151; border-left: 5px solid #DC2626; padding: 10px 12px; background: #1F2937; }
    .badge-box .label { font-size: 8px; text-transform: uppercase; color: #DC2626; letter-spacing: 1px; font-weight: bold; }
    .badge-box .name { font-size: 12px; font-weight: bold; color: #F9FAFB; margin: 3px 0; }
    .meta-table td { padding: 2px 8px 2px 0; font-size: 9px; color: #D1D5DB; }
    .meta-table .k { color: #DC2626; font-weight: bold; }
    table.lines { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.lines thead th { background: #DC2626; color: #fff; padding: 7px 8px;
        font-size: 9px; text-transform: uppercase; letter-spacing: 1px; text-align: left; font-weight: bold; }
    table.lines thead th.num, table.lines td.num { text-align: right; }
    table.lines tbody td { padding: 7px 8px; border-bottom: 1px solid #374151; font-size: 9.5px; color: #E5E7EB; }
    table.lines tbody tr:nth-child(even) td { background: #1F2937; }
    table.totals { width: 42%; margin-left: 58%; margin-top: 12px; border-collapse: collapse; }
    table.totals td { padding: 5px 8px; font-size: 10px; border-bottom: 1px solid #374151; color: #D1D5DB; }
    table.totals .k { color: #9CA3AF; }
    table.totals .v { text-align: right; font-weight: bold; color: #F9FAFB; }
    table.totals tr.grand td { background: #DC2626; color: #fff; font-size: 13px; font-weight: bold; border: none; text-transform: uppercase; }
    table.totals tr.paid td { color: #10B981; }
    table.totals tr.due td { color: #F87171; }
    .qr-section { margin-top: 25px; width: 100%; }
    .qr-section td { vertical-align: top; }
    .qr-box { text-align: center; width: 110px; }
    .qr-box img { width: 90px; height: 90px; }
    .qr-caption { font-size: 7px; color: #9CA3AF; margin-top: 2px; }
    .notes { font-size: 9px; color: #D1D5DB; padding-right: 20px; }
    .notes .title { font-weight: bold; color: #DC2626; margin-bottom: 3px; text-transform: uppercase; }
    .hash { font-size: 6.5px; color: #6B7280; word-break: break-all; margin-top: 4px; }
</style>
</head>
<body>
@if ($watermark)<div class="watermark">{{ $watermark }}</div>@endif
<header>
    <table style="width:100%">
        <tr>
            <td style="width:55%">
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-meta">
                    {{ $company->address }}@if($company->city), {{ $company->city }}@endif — {{ $company->country }}<br>
                    @if($company->phone){{ $company->phone }} · @endif{{ $company->email }}
                    @if($company->tax_id)<br>N° : {{ $company->tax_id }}@endif
                </div>
            </td>
            <td style="width:45%">
                <div class="sport-tag">Salle de Sport &amp; Fitness</div>
                <div class="doc-title">{{ $document->type_label }}</div>
                <div class="doc-number">#{{ $document->number }}</div>
            </td>
        </tr>
    </table>
</header>
<footer>
    <table style="width:100%">
        <tr>
            <td style="width:70%; color:#9CA3AF">{{ $company->invoice_footer ?? 'No pain, no gain. Merci de votre confiance.' }}<br>Vérification : {{ $document->verificationUrl() }}</td>
            <td style="width:30%; text-align:right; color:#DC2626; font-weight:bold;">IBIG FactPro<br><span style="color:#9CA3AF;font-weight:normal">factpro.ibigsoft.com</span></td>
        </tr>
    </table>
</footer>
<main>
    <table class="addresses">
        <tr>
            <td>
                <table class="meta-table">
                    <tr><td class="k">Date :</td><td><b style="color:#fff">{{ $document->issue_date->format('d/m/Y') }}</b></td></tr>
                    @if ($document->due_date)<tr><td class="k">Échéance :</td><td><b style="color:#fff">{{ $document->due_date->format('d/m/Y') }}</b></td></tr>@endif
                    @if ($document->reference)<tr><td class="k">Réf. :</td><td>{{ $document->reference }}</td></tr>@endif
                    <tr><td class="k">Devise :</td><td>{{ $document->currency }}</td></tr>
                </table>
            </td>
            <td>
                @if ($document->customer)
                    <div class="badge-box">
                        <div class="label">Membre / Client</div>
                        <div class="name">{{ $document->customer->name }}</div>
                        <div style="font-size:8.5px;color:#9CA3AF;line-height:1.5">
                            @if($document->customer->address){{ $document->customer->address }}<br>@endif
                            @if($document->customer->city){{ $document->customer->city }} — @endif{{ $document->customer->country }}<br>
                            @if($document->customer->phone){{ $document->customer->phone }}@endif
                            @if($document->customer->email) · {{ $document->customer->email }}@endif
                            @if($document->customer->tax_id)<br>N° Fiscal : {{ $document->customer->tax_id }}@endif
                        </div>
                    </div>
                @endif
            </td>
        </tr>
    </table>
    <table class="lines">
        <thead>
            <tr>
                <th style="width:42%">Désignation / Abonnement</th>
                <th class="num" style="width:10%">Qté</th>
                <th style="width:9%">Unité</th>
                <th class="num" style="width:14%">P.U. HT</th>
                <th class="num" style="width:8%">Rem. %</th>
                <th class="num" style="width:7%">TVA %</th>
                <th class="num" style="width:14%">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->lines as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td class="num">{{ number_format((float)$line->quantity,2,',',' ') }}</td>
                    <td>{{ $line->unit }}</td>
                    <td class="num">{{ number_format((float)$line->unit_price,0,',',' ') }}</td>
                    <td class="num">{{ number_format((float)$line->discount_percent,0) }}</td>
                    <td class="num">{{ number_format((float)$line->tax_rate,0) }}</td>
                    <td class="num">{{ number_format((float)$line->line_total,0,',',' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <table class="totals">
        <tr><td class="k">Sous-total HT</td><td class="v">{{ number_format((float)$document->subtotal,0,',',' ') }} {{ $document->currency }}</td></tr>
        @if ((float)$document->discount_amount > 0)
            <tr><td class="k">Remise</td><td class="v">−{{ number_format((float)$document->discount_amount,0,',',' ') }} {{ $document->currency }}</td></tr>
        @endif
        <tr><td class="k">TVA</td><td class="v">{{ number_format((float)$document->tax_amount,0,',',' ') }} {{ $document->currency }}</td></tr>
        <tr class="grand"><td>TOTAL TTC</td><td class="v">{{ number_format((float)$document->total,0,',',' ') }} {{ $document->currency }}</td></tr>
        @if ((float)$document->amount_paid > 0)
            <tr class="paid"><td class="k">Payé</td><td class="v">{{ number_format((float)$document->amount_paid,0,',',' ') }} {{ $document->currency }}</td></tr>
            <tr class="due"><td class="k">Reste à payer</td><td class="v">{{ number_format((float)$document->total-(float)$document->amount_paid,0,',',' ') }} {{ $document->currency }}</td></tr>
        @endif
    </table>
    <table class="qr-section">
        <tr>
            <td class="notes">
                @if ($document->notes)<div class="title">Notes</div><div>{!! nl2br(e($document->notes)) !!}</div>@endif
                @if ($document->terms)<div class="title" style="margin-top:8px">Règlement intérieur</div><div>{!! nl2br(e($document->terms)) !!}</div>@endif
            </td>
            <td class="qr-box">
                <img src="{{ $qrDataUri }}" alt="QR">
                <div class="qr-caption"><b style="color:#DC2626">AUTHENTIFIABLE</b><br>Scannez pour vérifier</div>
                @if ($document->integrity_hash)<div class="hash">SHA-256 : {{ substr($document->integrity_hash,0,32) }}…</div>@endif
            </td>
        </tr>
    </table>
    @include('pdf.partials.signature')
</main>
</body>
</html>

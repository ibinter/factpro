<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @@page { margin: 100px 45px 90px 45px; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0F2540; margin: 0; background: #fff; }
    .watermark { position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 42px; font-weight: bold;
        color: rgba(30,58,95,0.15); z-index: 1000; letter-spacing: 4px; }
    header { position: fixed; top: -70px; left: 0; right: 0; height: 60px;
        background: #1E3A5F; padding: 8px 12px; border-bottom: 3px solid #F0C040; }
    .company-name { font-size: 16px; font-weight: bold; color: #F0C040; }
    .company-meta { font-size: 8px; color: #93C5FD; line-height: 1.5; }
    .doc-title { font-size: 17px; font-weight: bold; color: #fff; text-align: right; text-transform: uppercase; }
    .doc-number { font-size: 10px; color: #F0C040; text-align: right; }
    footer { position: fixed; bottom: -70px; left: 0; right: 0; height: 60px;
        font-size: 8px; color: #1E3A5F; border-top: 3px solid #1E3A5F; padding-top: 6px; }
    .addresses { width: 100%; margin-bottom: 18px; }
    .addresses td { vertical-align: top; width: 50%; }
    .badge-box { border: 1px solid #1E3A5F; border-left: 4px solid #F0C040; padding: 10px 12px; background: #EFF6FF; }
    .badge-box .label { font-size: 8px; text-transform: uppercase; color: #1E3A5F; letter-spacing: 1px; }
    .badge-box .name { font-size: 12px; font-weight: bold; color: #0F2540; margin: 3px 0; }
    .meta-table td { padding: 2px 8px 2px 0; font-size: 9px; }
    .meta-table .k { color: #1E3A5F; font-weight: bold; }
    table.lines { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.lines thead th { background: #1E3A5F; color: #F0C040; padding: 7px 8px;
        font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; }
    table.lines thead th.num, table.lines td.num { text-align: right; }
    table.lines tbody td { padding: 7px 8px; border-bottom: 1px solid #DBEAFE; font-size: 9.5px; }
    table.lines tbody tr:nth-child(even) td { background: #EFF6FF; }
    table.totals { width: 42%; margin-left: 58%; margin-top: 12px; border-collapse: collapse; }
    table.totals td { padding: 5px 8px; font-size: 10px; border-bottom: 1px solid #DBEAFE; }
    table.totals .k { color: #1E3A5F; }
    table.totals .v { text-align: right; font-weight: bold; color: #0F2540; }
    table.totals tr.grand td { background: #1E3A5F; color: #F0C040; font-size: 12px; font-weight: bold; border: none; }
    table.totals tr.paid td { color: #166534; }
    table.totals tr.due td { color: #9B1C1C; }
    .qr-section { margin-top: 25px; width: 100%; }
    .qr-section td { vertical-align: top; }
    .qr-box { text-align: center; width: 110px; }
    .qr-box img { width: 90px; height: 90px; }
    .qr-caption { font-size: 7px; color: #9CA3AF; margin-top: 2px; }
    .notes { font-size: 9px; color: #0F2540; padding-right: 20px; }
    .notes .title { font-weight: bold; color: #1E3A5F; margin-bottom: 3px; }
    .hash { font-size: 6.5px; color: #BFDBFE; word-break: break-all; margin-top: 4px; }
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
                    @if($company->phone)Tél/Tel : {{ $company->phone }} · @endif{{ $company->email }}
                    @if($company->tax_id)<br>N° Fiscal / Tax ID : {{ $company->tax_id }}@endif
                </div>
            </td>
            <td style="width:45%">
                <div class="doc-title">{{ $document->type_label }}</div>
                <div class="doc-number">N° {{ $document->number }}</div>
            </td>
        </tr>
    </table>
</header>
<footer>
    <table style="width:100%">
        <tr>
            <td style="width:70%">{{ $company->invoice_footer ?? 'Merci de votre confiance. / Thank you for your business.' }}<br>{{ $document->verificationUrl() }}</td>
            <td style="width:30%; text-align:right;">Propulsé par / Powered by <b style="color:#1E3A5F">IBIG FactPro</b><br>factpro.ibigsoft.com</td>
        </tr>
    </table>
</footer>
<main>
    <table class="addresses">
        <tr>
            <td>
                <table class="meta-table">
                    <tr><td class="k">Date / Date :</td><td><b>{{ $document->issue_date->format('d/m/Y') }}</b></td></tr>
                    @if ($document->due_date)<tr><td class="k">Échéance / Due :</td><td><b>{{ $document->due_date->format('d/m/Y') }}</b></td></tr>@endif
                    @if ($document->reference)<tr><td class="k">Réf / Ref :</td><td>{{ $document->reference }}</td></tr>@endif
                    <tr><td class="k">Devise / Currency :</td><td>{{ $document->currency }}</td></tr>
                </table>
            </td>
            <td>
                    <div class="badge-box">
                        <div class="label">Facturé à / Bill to</div>
                    @if ($document->customer)
                        <div class="name">{{ $document->customer->name }}</div>
                        <div style="font-size:8.5px; color:#374151; line-height:1.5">
                            @if($document->customer->address){{ $document->customer->address }}<br>@endif
                            @if($document->customer->city){{ $document->customer->city }} — @endif{{ $document->customer->country }}<br>
                            @if($document->customer->phone){{ $document->customer->phone }}@endif
                            @if($document->customer->email) · {{ $document->customer->email }}@endif
                            @if($document->customer->tax_id)<br>N° Fiscal : {{ $document->customer->tax_id }}@endif
                        </div>
                    </div>
                    @else
                        <div class="name" style="color:#aaa;font-style:italic;">&#8212; Non renseign&eacute; &#8212;</div>
                    @endif
                    </div>
            </td>
        </tr>
    </table>
    <table class="lines">
        <thead>
            <tr>
                <th style="width:42%">Désignation / Description</th>
                <th class="num" style="width:10%">Qté / Qty</th>
                <th style="width:9%">Unité / Unit</th>
                <th class="num" style="width:14%">P.U. HT / Unit Price</th>
                <th class="num" style="width:8%">Rem. %</th>
                <th class="num" style="width:7%">TVA / Tax %</th>
                <th class="num" style="width:14%">Total HT / Subtotal</th>
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
        <tr><td class="k">Sous-total HT / Subtotal</td><td class="v">{{ number_format((float)$document->subtotal,0,',',' ') }} {{ $document->currency }}</td></tr>
        @if ((float)$document->discount_amount > 0)
            <tr><td class="k">Remise / Discount</td><td class="v">−{{ number_format((float)$document->discount_amount,0,',',' ') }} {{ $document->currency }}</td></tr>
        @endif
        <tr><td class="k">TVA / Tax</td><td class="v">{{ number_format((float)$document->tax_amount,0,',',' ') }} {{ $document->currency }}</td></tr>
        <tr class="grand"><td>TOTAL TTC / GRAND TOTAL</td><td class="v">{{ number_format((float)$document->total,0,',',' ') }} {{ $document->currency }}</td></tr>
        @if ((float)$document->amount_paid > 0)
            <tr class="paid"><td class="k">Payé / Paid</td><td class="v">{{ number_format((float)$document->amount_paid,0,',',' ') }} {{ $document->currency }}</td></tr>
            <tr class="due"><td class="k">Reste / Balance due</td><td class="v">{{ number_format((float)$document->total-(float)$document->amount_paid,0,',',' ') }} {{ $document->currency }}</td></tr>
        @endif
    </table>
    <table class="qr-section">
        <tr>
            <td class="notes">
                @if ($document->notes)<div class="title">Notes</div><div>{!! nl2br(e($document->notes)) !!}</div>@endif
                @if ($document->terms)<div class="title" style="margin-top:8px">Conditions / Terms</div><div>{!! nl2br(e($document->terms)) !!}</div>@endif
            </td>
            <td class="qr-box">
                <img src="{{ $qrDataUri }}" alt="QR">
                <div class="qr-caption"><b>AUTHENTIC DOCUMENT</b><br>Scan to verify / Scannez pour vérifier</div>
                @if ($document->integrity_hash)<div class="hash">SHA-256 : {{ substr($document->integrity_hash,0,32) }}…</div>@endif
            </td>
        </tr>
    </table>
    @include('pdf.partials.signature')
</main>
</body>
</html>

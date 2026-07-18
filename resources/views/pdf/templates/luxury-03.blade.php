<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @page { margin: 100px 45px 90px 45px; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Serif, serif; font-size: 10px; color: #111; margin: 0; background: #fff; }
    .watermark { position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 42px; font-weight: bold;
        color: rgba(156,163,175,0.2); z-index: 1000; letter-spacing: 4px; }
    header { position: fixed; top: -70px; left: 0; right: 0; height: 60px;
        background: #fff; padding: 8px 12px;
        border-top: 3px solid #9CA3AF; border-bottom: 3px solid #9CA3AF; }
    .company-name { font-size: 16px; font-weight: bold; color: #111; letter-spacing: 2px; text-transform: uppercase; }
    .company-meta { font-size: 8px; color: #9CA3AF; line-height: 1.5; }
    .doc-title { font-size: 17px; font-weight: bold; color: #9CA3AF; text-align: right; text-transform: uppercase; letter-spacing: 4px; }
    .doc-number { font-size: 10px; color: #555; text-align: right; }
    footer { position: fixed; bottom: -70px; left: 0; right: 0; height: 60px;
        font-size: 8px; color: #9CA3AF; border-top: 1px solid #E5E7EB; border-bottom: 1px solid #E5E7EB; padding-top: 6px; }
    .addresses { width: 100%; margin-bottom: 18px; }
    .addresses td { vertical-align: top; width: 50%; }
    .badge-box { border: 1px solid #E5E7EB; border-top: 2px solid #9CA3AF; padding: 12px; }
    .badge-box .label { font-size: 8px; text-transform: uppercase; color: #9CA3AF; letter-spacing: 2px; }
    .badge-box .name { font-size: 12px; font-weight: bold; color: #111; margin: 3px 0; }
    .meta-table td { padding: 2px 8px 2px 0; font-size: 9px; }
    .meta-table .k { color: #9CA3AF; }
    table.lines { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.lines thead th { background: #fff; color: #111; padding: 7px 8px;
        font-size: 9px; text-transform: uppercase; letter-spacing: 1px; text-align: left;
        border-top: 2px solid #111; border-bottom: 1px solid #E5E7EB; }
    table.lines thead th.num, table.lines td.num { text-align: right; }
    table.lines tbody td { padding: 8px 8px; border-bottom: 1px solid #F3F4F6; font-size: 9.5px; }
    table.lines tbody tr:last-child td { border-bottom: 2px solid #111; }
    table.totals { width: 42%; margin-left: 58%; margin-top: 12px; border-collapse: collapse; }
    table.totals td { padding: 5px 8px; font-size: 10px; }
    table.totals .k { color: #9CA3AF; }
    table.totals .v { text-align: right; font-weight: bold; }
    table.totals tr.grand td { border-top: 2px solid #111; border-bottom: 2px solid #111; font-size: 12px; font-weight: bold; color: #111; }
    table.totals tr.paid td { color: #059669; }
    table.totals tr.due td { color: #DC2626; }
    .qr-section { margin-top: 25px; width: 100%; }
    .qr-section td { vertical-align: top; }
    .qr-box { text-align: center; width: 110px; }
    .qr-box img { width: 90px; height: 90px; }
    .qr-caption { font-size: 7px; color: #9CA3AF; margin-top: 2px; }
    .notes { font-size: 9px; color: #444; padding-right: 20px; }
    .notes .title { font-weight: bold; color: #111; margin-bottom: 3px; letter-spacing: 1px; text-transform: uppercase; }
    .hash { font-size: 6.5px; color: #D1D5DB; word-break: break-all; margin-top: 4px; }
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
                    @if($company->phone)Tél : {{ $company->phone }} · @endif{{ $company->email }}
                    @if($company->tax_id)<br>N° Fiscal : {{ $company->tax_id }}@endif
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
            <td style="width:70%">{{ $company->invoice_footer ?? 'Merci de votre confiance.' }}<br>Document vérifiable : {{ $document->verificationUrl() }}</td>
            <td style="width:30%; text-align:right;">Propulsé par <b>IBIG FactPro</b><br>factpro.ibigsoft.com</td>
        </tr>
    </table>
</footer>
<main>
    <table class="addresses">
        <tr>
            <td>
                <table class="meta-table">
                    <tr><td class="k">Date d'émission :</td><td><b>{{ $document->issue_date->format('d/m/Y') }}</b></td></tr>
                    @if ($document->due_date)<tr><td class="k">Échéance :</td><td><b>{{ $document->due_date->format('d/m/Y') }}</b></td></tr>@endif
                    @if ($document->reference)<tr><td class="k">Référence :</td><td>{{ $document->reference }}</td></tr>@endif
                    <tr><td class="k">Devise :</td><td>{{ $document->currency }}</td></tr>
                </table>
            </td>
            <td>
                @if ($document->customer)
                    <div class="badge-box">
                        <div class="label">Facturé à</div>
                        <div class="name">{{ $document->customer->name }}</div>
                        <div style="font-size:8.5px; color:#6B7280; line-height:1.5">
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
                <th style="width:42%">Désignation</th>
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
                @if ($document->terms)<div class="title" style="margin-top:8px">Conditions</div><div>{!! nl2br(e($document->terms)) !!}</div>@endif
            </td>
            <td class="qr-box">
                <img src="{{ $qrDataUri }}" alt="QR">
                <div class="qr-caption"><b>DOCUMENT AUTHENTIFIABLE</b><br>Scannez pour vérifier</div>
                @if ($document->integrity_hash)<div class="hash">SHA-256 : {{ substr($document->integrity_hash,0,32) }}…</div>@endif
            </td>
        </tr>
    </table>
    @include('pdf.partials.signature')
</main>
</body>
</html>

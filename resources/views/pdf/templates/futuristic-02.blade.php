<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @page { margin: 100px 45px 90px 45px; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans Mono, Courier New, monospace; font-size: 10px; color: #00FF41; background: #0A0A0A; margin: 0; }
    .watermark { position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 42px; font-weight: bold;
        color: rgba(0,255,65,0.12); z-index: 1000; letter-spacing: 4px; }
    header { position: fixed; top: -70px; left: 0; right: 0; height: 60px;
        background: #0A0A0A; color: #00FF41; padding: 8px 12px; border-bottom: 2px solid #00FF41; }
    .company-name { font-size: 15px; font-weight: bold; color: #00FF41; letter-spacing: 2px; }
    .company-meta { font-size: 8px; color: #007A1F; line-height: 1.5; }
    .doc-title { font-size: 16px; font-weight: bold; color: #00FF41; text-align: right; text-transform: uppercase; letter-spacing: 3px; }
    .doc-number { font-size: 9px; color: #007A1F; text-align: right; }
    footer { position: fixed; bottom: -70px; left: 0; right: 0; height: 60px;
        font-size: 8px; color: #007A1F; border-top: 1px solid #00FF41; padding-top: 6px; background: #0A0A0A; }
    .addresses { width: 100%; margin-bottom: 18px; }
    .addresses td { vertical-align: top; width: 50%; }
    .badge-box { background: #001200; border-left: 3px solid #00FF41; padding: 10px 12px; }
    .badge-box .label { font-size: 8px; text-transform: uppercase; color: #007A1F; letter-spacing: 1px; }
    .badge-box .name { font-size: 12px; font-weight: bold; color: #00FF41; margin: 3px 0; }
    .meta-table td { padding: 2px 8px 2px 0; font-size: 9px; color: #007A1F; }
    .meta-table .k { color: #003B00; }
    table.lines { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.lines thead th { background: #001200; color: #00FF41; padding: 7px 8px;
        font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; text-align: left; border: 1px solid #00FF41; }
    table.lines thead th.num, table.lines td.num { text-align: right; }
    table.lines tbody td { padding: 7px 8px; border-bottom: 1px solid #003B00; font-size: 9.5px; color: #00FF41; }
    table.lines tbody tr:nth-child(even) td { background: #010D01; }
    table.totals { width: 42%; margin-left: 58%; margin-top: 12px; border-collapse: collapse; }
    table.totals td { padding: 5px 8px; font-size: 10px; color: #00FF41; }
    table.totals .k { color: #007A1F; }
    table.totals .v { text-align: right; font-weight: bold; }
    table.totals tr.grand td { background: #001200; color: #00FF41; font-size: 12px; font-weight: bold; border: 1px solid #00FF41; }
    table.totals tr.paid td { color: #00FF41; }
    table.totals tr.due td { color: #FF3131; }
    .qr-section { margin-top: 25px; width: 100%; }
    .qr-section td { vertical-align: top; }
    .qr-box { text-align: center; width: 110px; }
    .qr-box img { width: 90px; height: 90px; border: 2px solid #00FF41; }
    .qr-caption { font-size: 7px; color: #007A1F; margin-top: 2px; }
    .notes { font-size: 9px; color: #007A1F; padding-right: 20px; }
    .notes .title { font-weight: bold; color: #00FF41; margin-bottom: 3px; }
    .hash { font-size: 6.5px; color: #003B00; word-break: break-all; margin-top: 4px; }
    .prompt { color: #007A1F; }
</style>
</head>
<body>
@if ($watermark)<div class="watermark">{{ $watermark }}</div>@endif
<header>
    <table style="width:100%">
        <tr>
            <td style="width:55%">
                <div class="company-name"><span class="prompt">&gt;&gt; </span>{{ $company->name }}</div>
                <div class="company-meta">
                    {{ $company->address }}@if($company->city), {{ $company->city }}@endif — {{ $company->country }}<br>
                    @if($company->phone)TEL:{{ $company->phone }} · @endif{{ $company->email }}
                    @if($company->tax_id)<br>FISCAL:{{ $company->tax_id }}@endif
                </div>
            </td>
            <td style="width:45%">
                <div class="doc-title">{{ $document->type_label }}</div>
                <div class="doc-number">REF# {{ $document->number }}</div>
            </td>
        </tr>
    </table>
</header>
<footer>
    <table style="width:100%">
        <tr>
            <td style="width:70%">{{ $company->invoice_footer ?? 'Merci de votre confiance.' }}<br><span class="prompt">&gt;</span> {{ $document->verificationUrl() }}</td>
            <td style="width:30%; text-align:right;">Propulsé par <b style="color:#00FF41">IBIG FactPro</b></td>
        </tr>
    </table>
</footer>
<main>
    <table class="addresses">
        <tr>
            <td>
                <table class="meta-table">
                    <tr><td class="k">DATE_EMISSION:</td><td><b>{{ $document->issue_date->format('d/m/Y') }}</b></td></tr>
                    @if ($document->due_date)<tr><td class="k">ECHEANCE:</td><td><b>{{ $document->due_date->format('d/m/Y') }}</b></td></tr>@endif
                    @if ($document->reference)<tr><td class="k">REF:</td><td>{{ $document->reference }}</td></tr>@endif
                    <tr><td class="k">DEVISE:</td><td>{{ $document->currency }}</td></tr>
                </table>
            </td>
            <td>
                @if ($document->customer)
                    <div class="badge-box">
                        <div class="label">CLIENT_TARGET</div>
                        <div class="name">{{ $document->customer->name }}</div>
                        <div class="company-meta">
                            @if($document->customer->address){{ $document->customer->address }}<br>@endif
                            @if($document->customer->city){{ $document->customer->city }} — @endif{{ $document->customer->country }}<br>
                            @if($document->customer->phone){{ $document->customer->phone }}@endif
                            @if($document->customer->email) · {{ $document->customer->email }}@endif
                            @if($document->customer->tax_id)<br>FISCAL:{{ $document->customer->tax_id }}@endif
                        </div>
                    </div>
                @endif
            </td>
        </tr>
    </table>
    <table class="lines">
        <thead>
            <tr>
                <th style="width:42%">DESIGNATION</th>
                <th class="num" style="width:10%">QTE</th>
                <th style="width:9%">UNITE</th>
                <th class="num" style="width:14%">PU HT</th>
                <th class="num" style="width:8%">REM%</th>
                <th class="num" style="width:7%">TVA%</th>
                <th class="num" style="width:14%">TOTAL HT</th>
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
        <tr><td class="k">SOUS_TOTAL HT</td><td class="v">{{ number_format((float)$document->subtotal,0,',',' ') }} {{ $document->currency }}</td></tr>
        @if ((float)$document->discount_amount > 0)
            <tr><td class="k">REMISE</td><td class="v">-{{ number_format((float)$document->discount_amount,0,',',' ') }} {{ $document->currency }}</td></tr>
        @endif
        <tr><td class="k">TVA</td><td class="v">{{ number_format((float)$document->tax_amount,0,',',' ') }} {{ $document->currency }}</td></tr>
        <tr class="grand"><td>TOTAL_TTC</td><td class="v">{{ number_format((float)$document->total,0,',',' ') }} {{ $document->currency }}</td></tr>
        @if ((float)$document->amount_paid > 0)
            <tr class="paid"><td class="k">PAYE</td><td class="v">{{ number_format((float)$document->amount_paid,0,',',' ') }} {{ $document->currency }}</td></tr>
            <tr class="due"><td class="k">SOLDE</td><td class="v">{{ number_format((float)$document->total-(float)$document->amount_paid,0,',',' ') }} {{ $document->currency }}</td></tr>
        @endif
    </table>
    <table class="qr-section">
        <tr>
            <td class="notes">
                @if ($document->notes)<div class="title">// NOTES</div><div>{!! nl2br(e($document->notes)) !!}</div>@endif
                @if ($document->terms)<div class="title" style="margin-top:8px">// CONDITIONS</div><div>{!! nl2br(e($document->terms)) !!}</div>@endif
            </td>
            <td class="qr-box">
                <img src="{{ $qrDataUri }}" alt="QR">
                <div class="qr-caption"><b>VERIFIER_DOC</b><br>SCAN_QR</div>
                @if ($document->integrity_hash)<div class="hash">SHA256:{{ substr($document->integrity_hash,0,32) }}…</div>@endif
            </td>
        </tr>
    </table>
    @include('pdf.partials.signature')
</main>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @@page { margin: 120px 50px 90px 50px; }

    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #e8d5a3; background: #ffffff; margin: 0; }

    .watermark {
        position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 48px; font-weight: bold;
        color: rgba(201, 168, 76, 0.15); z-index: 1000; letter-spacing: 6px;
    }

    header {
        position: fixed; top: -100px; left: 0; right: 0; height: 92px;
        background: #0a0a0a;
    }
    .header-inner { padding: 12px 20px 0 20px; }
    .gold-rule { height: 3px; background: #c9a84c; margin: 0; }
    .gold-rule-thin { height: 1px; background: #c9a84c; margin-top: 2px; }

    .company-name { font-family: DejaVu Serif, serif; font-size: 18px; font-weight: bold; color: #c9a84c; letter-spacing: 3px; text-transform: uppercase; }
    .company-meta { font-size: 7.5px; color: #a08040; line-height: 1.6; margin-top: 3px; }
    .doc-title { font-family: DejaVu Serif, serif; font-size: 20px; font-weight: bold; color: #c9a84c; text-align: right; text-transform: uppercase; letter-spacing: 4px; }
    .doc-number { font-size: 9px; color: #a08040; text-align: right; letter-spacing: 2px; margin-top: 2px; }

    footer {
        position: fixed; bottom: -70px; left: 0; right: 0; height: 62px;
        background: #0a0a0a;
    }
    .footer-inner {
        padding: 8px 20px; font-size: 7.5px; color: #a08040;
    }
    .footer-brand { color: #c9a84c; font-weight: bold; }

    /* Bloc client */
    .addresses { width: 100%; margin-bottom: 18px; margin-top: 10px; }
    .addresses td { vertical-align: top; width: 50%; }

    .meta-table td { padding: 3px 10px 3px 0; font-size: 9px; color: #333; }
    .meta-table .k { color: #c9a84c; text-transform: uppercase; font-size: 7.5px; letter-spacing: 2px; font-weight: bold; }

    .client-box {
        background: #0a0a0a; border: 2px solid #c9a84c;
        padding: 12px 14px;
    }
    .client-box .label { font-size: 7px; text-transform: uppercase; color: #c9a84c; letter-spacing: 3px; }
    .client-box .name { font-family: DejaVu Serif, serif; font-size: 13px; font-weight: bold; color: #e8d5a3; margin: 4px 0 3px; }
    .client-box .meta { font-size: 8px; color: #a08040; line-height: 1.5; }

    /* Tableau lignes */
    table.lines { width: 100%; border-collapse: collapse; margin-top: 12px; }
    table.lines thead th {
        background: #0a0a0a; color: #c9a84c;
        padding: 9px 8px; font-size: 8px;
        text-transform: uppercase; letter-spacing: 1.5px; text-align: left;
        border-top: 2px solid #c9a84c; border-bottom: 2px solid #c9a84c;
    }
    table.lines thead th.num, table.lines td.num { text-align: right; }
    table.lines tbody td {
        padding: 8px 8px; border-bottom: 1px solid #2a2a2a; font-size: 9.5px; color: #1a1a1a;
    }
    table.lines tbody tr:nth-child(even) td { background: #f5f0e8; }
    table.lines tbody tr:nth-child(odd) td { background: #fdfaf3; }

    /* Totaux */
    table.totals { width: 42%; margin-left: 58%; margin-top: 16px; border-collapse: collapse; }
    table.totals td { padding: 5px 10px; font-size: 10px; color: #333; }
    table.totals .k { color: #666; }
    table.totals .v { text-align: right; font-weight: bold; }
    table.totals tr.grand td {
        background: #0a0a0a; color: #c9a84c; font-size: 13px; font-weight: bold;
        font-family: DejaVu Serif, serif;
        border-top: 2px solid #c9a84c; border-bottom: 2px solid #c9a84c;
    }
    table.totals tr.paid td { color: #166534; }
    table.totals tr.due td { color: #991b1b; font-weight: bold; }

    /* QR / notes */
    .qr-section { margin-top: 28px; width: 100%; }
    .qr-section td { vertical-align: top; }
    .qr-box { text-align: center; width: 110px; }
    .qr-box img { width: 88px; height: 88px; border: 2px solid #c9a84c; padding: 2px; }
    .qr-caption { font-size: 7px; color: #666; margin-top: 3px; }
    .notes { font-size: 9px; color: #333; padding-right: 20px; }
    .notes .title { font-family: DejaVu Serif, serif; font-weight: bold; color: #c9a84c; margin-bottom: 4px; font-size: 9px; letter-spacing: 1px; text-transform: uppercase; }
    .hash { font-size: 6.5px; color: #a08040; word-break: break-all; margin-top: 4px; }
</style>
</head>
<body>

@if ($watermark)
    <div class="watermark">{{ $watermark }}</div>
@endif

<header>
    <div class="gold-rule"></div>
    <div class="header-inner">
        <table style="width:100%">
            <tr>
                <td style="width:55%">
                    <div class="company-name">{{ $company->name }}</div>
                    <div class="company-meta">
                        {{ $company->address }}@if($company->city), {{ $company->city }}@endif — {{ $company->country }}<br>
                        @if($company->phone)Tél : {{ $company->phone }} · @endif{{ $company->email }}
                        @if($company->tax_id) · N° Fiscal : {{ $company->tax_id }}@endif
                    </div>
                </td>
                <td style="width:45%">
                    <div class="doc-title">{{ $document->type_label }}</div>
                    <div class="doc-number">N° {{ $document->number }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="gold-rule-thin"></div>
</header>

<footer>
    <div class="gold-rule-thin" style="background:#c9a84c;height:1px;"></div>
    <div class="footer-inner">
        <table style="width:100%">
            <tr>
                <td style="width:70%">
                    {{ $company->invoice_footer ?? 'Merci de votre confiance.' }}<br>
                    Vérification : {{ $document->verificationUrl() }}
                </td>
                <td style="width:30%; text-align:right;">
                    Propulsé par <span class="footer-brand">IBIG FactPro</span><br>
                    Page <span class="page"></span> / <span class="topage"></span>
                </td>
            </tr>
        </table>
    </div>
</footer>

<main>
    <table class="addresses">
        <tr>
            <td>
                <table class="meta-table">
                    <tr><td class="k">Émission</td><td><b>{{ $document->issue_date->format('d/m/Y') }}</b></td></tr>
                    @if ($document->due_date)
                        <tr><td class="k">Échéance</td><td><b>{{ $document->due_date->format('d/m/Y') }}</b></td></tr>
                    @endif
                    @if ($document->reference)
                        <tr><td class="k">Référence</td><td>{{ $document->reference }}</td></tr>
                    @endif
                    <tr><td class="k">Devise</td><td>{{ $document->currency }}</td></tr>
                </table>
            </td>
            <td>
                <div class="client-box">
                    <div class="label">Facturé à</div>
                    @if ($document->customer)
                        <div class="name">{{ $document->customer->name }}</div>
                        <div class="meta">
                            @if($document->customer->address){{ $document->customer->address }}<br>@endif
                            @if($document->customer->city){{ $document->customer->city }} — @endif{{ $document->customer->country }}<br>
                            @if($document->customer->phone){{ $document->customer->phone }}@endif
                            @if($document->customer->email) · {{ $document->customer->email }}@endif
                            @if($document->customer->tax_id)<br>N° Fiscal : {{ $document->customer->tax_id }}@endif
                        </div>
                    @else
                        <div class="name" style="color:#555;font-style:italic;">— Non renseigné —</div>
                    @endif
                </div>
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
                <th class="num" style="width:10%">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($document->lines as $line)
                <tr>
                    <td>{{ $line->description }}</td>
                    <td class="num">{{ number_format((float) $line->quantity, 2, ',', ' ') }}</td>
                    <td>{{ $line->unit }}</td>
                    <td class="num">{{ number_format((float) $line->unit_price, 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format((float) $line->discount_percent, 0) }}</td>
                    <td class="num">{{ number_format((float) $line->tax_rate, 0) }}</td>
                    <td class="num">{{ number_format((float) $line->line_total, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="k">Sous-total HT</td>
            <td class="v">{{ number_format((float) $document->subtotal, 0, ',', ' ') }} {{ $document->currency }}</td>
        </tr>
        @if ((float) $document->discount_amount > 0)
            <tr>
                <td class="k">Remise</td>
                <td class="v">−{{ number_format((float) $document->discount_amount, 0, ',', ' ') }} {{ $document->currency }}</td>
            </tr>
        @endif
        <tr>
            <td class="k">TVA</td>
            <td class="v">{{ number_format((float) $document->tax_amount, 0, ',', ' ') }} {{ $document->currency }}</td>
        </tr>
        <tr class="grand">
            <td>TOTAL TTC</td>
            <td class="v">{{ number_format((float) $document->total, 0, ',', ' ') }} {{ $document->currency }}</td>
        </tr>
        @if ((float) $document->amount_paid > 0)
            <tr class="paid">
                <td class="k">Payé</td>
                <td class="v">{{ number_format((float) $document->amount_paid, 0, ',', ' ') }} {{ $document->currency }}</td>
            </tr>
            <tr class="due">
                <td class="k">Reste à payer</td>
                <td class="v">{{ number_format((float) $document->total - (float) $document->amount_paid, 0, ',', ' ') }} {{ $document->currency }}</td>
            </tr>
        @endif
    </table>

    <table class="qr-section">
        <tr>
            <td class="notes">
                @if ($document->notes)
                    <div class="title">Notes</div>
                    <div>{!! nl2br(e($document->notes)) !!}</div>
                @endif
                @if ($document->terms)
                    <div class="title" style="margin-top:10px">Conditions</div>
                    <div>{!! nl2br(e($document->terms)) !!}</div>
                @endif
            </td>
            <td class="qr-box">
                <img src="{{ $qrDataUri }}" alt="QR de vérification">
                <div class="qr-caption">
                    <b>DOCUMENT AUTHENTIFIABLE</b><br>
                    Scannez pour vérifier
                </div>
                @if ($document->integrity_hash)
                    <div class="hash">SHA-256 : {{ substr($document->integrity_hash, 0, 32) }}…</div>
                @endif
            </td>
        </tr>
    </table>
    @include('pdf.partials.signature')
</main>

</body>
</html>

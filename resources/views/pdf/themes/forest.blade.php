<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @@page { margin: 118px 50px 88px 50px; }

    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1c2e1c; margin: 0; background: #ffffff; }

    .watermark {
        position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 48px; font-weight: bold;
        color: rgba(22, 101, 52, 0.12); z-index: 1000; letter-spacing: 6px;
    }

    header {
        position: fixed; top: -98px; left: 0; right: 0; height: 90px;
        background: #166534;
    }
    .header-cream { background: #fefce8; height: 4px; }
    .header-inner { padding: 12px 22px 0 22px; }

    .company-name { font-family: DejaVu Serif, serif; font-size: 17px; font-weight: bold; color: #fefce8; letter-spacing: 1px; }
    .company-meta { font-size: 7.5px; color: #bbf7d0; line-height: 1.6; margin-top: 3px; }
    .doc-title { font-family: DejaVu Serif, serif; font-size: 19px; font-weight: bold; color: #fefce8; text-align: right; text-transform: uppercase; letter-spacing: 2px; }
    .doc-number { font-size: 9px; color: #bbf7d0; text-align: right; margin-top: 3px; }

    footer {
        position: fixed; bottom: -68px; left: 0; right: 0; height: 60px;
        background: #fefce8; border-top: 3px solid #166534;
        padding: 8px 22px; font-size: 7.5px; color: #4b7c53;
    }
    .footer-brand { color: #166534; font-weight: bold; }

    /* Corps */
    .section-divider { height: 2px; background: #dcfce7; margin: 14px 0; }

    .addresses { width: 100%; margin-top: 10px; margin-bottom: 18px; }
    .addresses td { vertical-align: top; width: 50%; }

    .meta-table td { padding: 3px 12px 3px 0; font-size: 9px; }
    .meta-table .k { color: #4b7c53; text-transform: uppercase; font-size: 7px; letter-spacing: 1.5px; font-weight: bold; }
    .meta-table .v { color: #1c2e1c; }

    .client-box {
        background: #f0fdf4; border: 1px solid #86efac;
        border-left: 5px solid #166534;
        padding: 11px 14px;
    }
    .client-box .label { font-size: 7px; text-transform: uppercase; color: #166534; letter-spacing: 2px; margin-bottom: 4px; }
    .client-box .name { font-family: DejaVu Serif, serif; font-size: 12px; font-weight: bold; color: #14532d; margin-bottom: 3px; }
    .client-box .meta { font-size: 8px; color: #4b7c53; line-height: 1.5; }

    table.lines { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.lines thead th {
        background: #166534; color: #fefce8;
        padding: 9px 8px; font-size: 8px;
        text-transform: uppercase; letter-spacing: 1px; text-align: left;
    }
    table.lines thead th.num, table.lines td.num { text-align: right; }
    table.lines tbody td {
        padding: 8px 8px; border-bottom: 1px solid #dcfce7; font-size: 9.5px; color: #1c2e1c;
    }
    table.lines tbody tr:nth-child(even) td { background: #f0fdf4; }
    table.lines tbody tr:nth-child(odd) td { background: #fefce8; }

    table.totals { width: 42%; margin-left: 58%; margin-top: 16px; border-collapse: collapse; }
    table.totals td { padding: 5px 10px; font-size: 10px; }
    table.totals .k { color: #4b7c53; }
    table.totals .v { text-align: right; font-weight: bold; }
    table.totals tr.sep td { border-top: 1px solid #dcfce7; }
    table.totals tr.grand td {
        background: #166534; color: #fefce8; font-size: 12px; font-weight: bold;
        font-family: DejaVu Serif, serif;
    }
    table.totals tr.paid td { color: #166534; font-weight: bold; }
    table.totals tr.due td { color: #991b1b; font-weight: bold; }

    .qr-section { margin-top: 26px; width: 100%; }
    .qr-section td { vertical-align: top; }
    .qr-box { text-align: center; width: 110px; }
    .qr-box img { width: 88px; height: 88px; border: 2px solid #86efac; padding: 2px; }
    .qr-caption { font-size: 7px; color: #4b7c53; margin-top: 3px; }
    .notes { font-size: 9px; color: #374151; padding-right: 20px; }
    .notes .title { font-family: DejaVu Serif, serif; font-weight: bold; color: #166534; margin-bottom: 4px; font-size: 9px; }
    .hash { font-size: 6.5px; color: #86efac; word-break: break-all; margin-top: 4px; }
</style>
</head>
<body>

@if ($watermark)
    <div class="watermark">{{ $watermark }}</div>
@endif

<header>
    <div class="header-inner">
        <table style="width:100%">
            <tr>
                <td style="width:55%">
                    <div class="company-name">{{ $company->name }}</div>
                    <div class="company-meta">
                        {{ $company->address }}
@if($company->city), {{ $company->city }}
@endif — {{ $company->country }}<br>
                        @if($company->phone)Tél : {{ $company->phone }} · @endif{{ $company->email }}
                        @if($company->tax_id) · N° Fiscal : {{ $company->tax_id }}
@endif
                    </div>
                </td>
                <td style="width:45%">
                    <div class="doc-title">{{ $document->type_label }}</div>
                    <div class="doc-number">N° {{ $document->number }}</div>
                </td>
            </tr>
        </table>
    </div>
    <div class="header-cream"></div>
</header>

<footer>
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
</footer>

<main>
    <table class="addresses">
        <tr>
            <td>
                <table class="meta-table">
                    <tr><td class="k">Date d'émission</td><td class="v"><b>{{ $document->issue_date->format('d/m/Y') }}</b></td></tr>
                    @if ($document->due_date)
                        <tr><td class="k">Échéance</td><td class="v"><b>{{ $document->due_date->format('d/m/Y') }}</b></td></tr>
                    @endif
                    @if ($document->reference)
                        <tr><td class="k">Référence</td><td class="v">{{ $document->reference }}</td></tr>
                    @endif
                    <tr><td class="k">Devise</td><td class="v">{{ $document->currency }}</td></tr>
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
                            @if($document->customer->phone){{ $document->customer->phone }}
@endif
                            @if($document->customer->email) · {{ $document->customer->email }}
@endif
                            @if($document->customer->tax_id)<br>N° Fiscal : {{ $document->customer->tax_id }}
@endif
                        </div>
                    @else
                        <div class="name" style="color:#aaa;font-style:italic;">— Non renseigné —</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="section-divider"></div>

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
        <tr class="sep">
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

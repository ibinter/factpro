<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @@page { margin: 115px 55px 85px 55px; }

    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; margin: 0; }

    .watermark {
        position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 48px; font-weight: bold;
        color: rgba(3, 105, 161, 0.12); z-index: 1000; letter-spacing: 6px;
    }

    header {
        position: fixed; top: -95px; left: 0; right: 0; height: 86px;
        border-bottom: 3px solid #0369a1;
    }
    .header-inner { padding: 14px 0 10px 0; }
    .accent-bar { width: 48px; height: 3px; background: #0369a1; margin-bottom: 6px; }

    .company-name { font-size: 17px; font-weight: bold; color: #0369a1; letter-spacing: 0.5px; }
    .company-meta { font-size: 8px; color: #64748b; line-height: 1.6; margin-top: 2px; }
    .doc-title { font-size: 22px; font-weight: bold; color: #0369a1; text-align: right; text-transform: uppercase; letter-spacing: 2px; }
    .doc-number { font-size: 9px; color: #94a3b8; text-align: right; margin-top: 3px; letter-spacing: 1px; }

    footer {
        position: fixed; bottom: -65px; left: 0; right: 0; height: 56px;
        border-top: 1px solid #e2e8f0; padding-top: 10px;
        font-size: 7.5px; color: #94a3b8;
    }
    .footer-brand { color: #0369a1; font-weight: bold; }

    /* Corps */
    .addresses { width: 100%; margin-top: 12px; margin-bottom: 22px; }
    .addresses td { vertical-align: top; width: 50%; }

    .meta-table td { padding: 3px 12px 3px 0; font-size: 9px; }
    .meta-table .k { color: #94a3b8; text-transform: uppercase; font-size: 7px; letter-spacing: 1.5px; }
    .meta-table .v { color: #1e293b; font-weight: bold; }

    .client-box {
        border-left: 4px solid #0369a1;
        padding: 10px 14px;
        background: #f0f9ff;
    }
    .client-box .label { font-size: 7px; text-transform: uppercase; color: #0369a1; letter-spacing: 2px; margin-bottom: 5px; }
    .client-box .name { font-size: 13px; font-weight: bold; color: #0c4a6e; margin-bottom: 3px; }
    .client-box .meta { font-size: 8px; color: #475569; line-height: 1.5; }

    table.lines { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.lines thead th {
        color: #0369a1; padding: 8px 8px 10px;
        font-size: 7.5px; text-transform: uppercase; letter-spacing: 1px;
        text-align: left; border-bottom: 2px solid #0369a1;
        background: #ffffff;
    }
    table.lines thead th.num, table.lines td.num { text-align: right; }
    table.lines tbody td {
        padding: 9px 8px; border-bottom: 1px solid #e2e8f0; font-size: 9.5px; color: #334155;
    }
    table.lines tbody tr:nth-child(even) td { background: #f8fafc; }

    table.totals { width: 40%; margin-left: 60%; margin-top: 16px; border-collapse: collapse; }
    table.totals td { padding: 5px 10px; font-size: 10px; }
    table.totals .k { color: #94a3b8; }
    table.totals .v { text-align: right; font-weight: bold; color: #1e293b; }
    table.totals tr.sep td { border-top: 1px solid #e2e8f0; }
    table.totals tr.grand td {
        background: #0369a1; color: #ffffff; font-size: 13px; font-weight: bold;
        border-top: 0;
    }
    table.totals tr.paid td { color: #166534; }
    table.totals tr.due td { color: #991b1b; font-weight: bold; }

    .qr-section { margin-top: 28px; width: 100%; }
    .qr-section td { vertical-align: top; }
    .qr-box { text-align: center; width: 110px; }
    .qr-box img { width: 88px; height: 88px; }
    .qr-caption { font-size: 7px; color: #94a3b8; margin-top: 3px; }
    .notes { font-size: 9px; color: #475569; padding-right: 20px; }
    .notes .title { font-weight: bold; color: #0369a1; margin-bottom: 4px; font-size: 8.5px; text-transform: uppercase; letter-spacing: 1px; }
    .hash { font-size: 6.5px; color: #94a3b8; word-break: break-all; margin-top: 4px; }
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
                    <div class="accent-bar"></div>
                    <div class="company-name">{{ $company->name }}</div>
                    <div class="company-meta">
                        {{ $company->address }}
@if($company->city), {{ $company->city }}
@endif — {{ $company->country }}<br>
                        @if($company->phone){{ $company->phone }} · @endif{{ $company->email }}
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
</header>

<footer>
    <table style="width:100%">
        <tr>
            <td style="width:70%">
                {{ $company->invoice_footer ?? 'Merci de votre confiance.' }}<br>
                Vérification : {{ $document->verificationUrl() }}
            </td>
            <td style="width:30%; text-align:right;">
                <span class="footer-brand">IBIG FactPro</span> · factpro.ibigsoft.com<br>
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
                    <tr><td class="k">Date d'émission</td><td class="v">{{ $document->issue_date->format('d/m/Y') }}</td></tr>
                    @if ($document->due_date)
                        <tr><td class="k">Date d'échéance</td><td class="v">{{ $document->due_date->format('d/m/Y') }}</td></tr>
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

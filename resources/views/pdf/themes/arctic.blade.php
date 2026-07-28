<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @@page { margin: 112px 60px 84px 60px; }

    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; margin: 0; background: #ffffff; }

    .watermark {
        position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 48px; font-weight: bold;
        color: rgba(71, 85, 105, 0.10); z-index: 1000; letter-spacing: 8px;
    }

    header {
        position: fixed; top: -94px; left: 0; right: 0; height: 86px;
        border-bottom: 1px solid #cbd5e1;
    }
    .header-rule-top { height: 3px; background: #475569; }
    .header-inner { padding: 12px 0 8px 0; }

    .company-name { font-size: 15px; font-weight: bold; color: #0f172a; letter-spacing: 0.5px; text-transform: uppercase; }
    .company-meta { font-size: 7.5px; color: #94a3b8; line-height: 1.6; margin-top: 3px; }
    .doc-title { font-size: 11px; font-weight: bold; color: #475569; text-align: right; text-transform: uppercase; letter-spacing: 4px; }
    .doc-number { font-size: 20px; font-weight: bold; color: #0f172a; text-align: right; letter-spacing: -0.5px; margin-top: 1px; }

    footer {
        position: fixed; bottom: -64px; left: 0; right: 0; height: 56px;
        border-top: 1px solid #e2e8f0; padding-top: 8px;
        font-size: 7.5px; color: #94a3b8;
    }
    .footer-brand { color: #475569; font-weight: bold; }

    /* Corps — ultra-minimal */
    .addresses { width: 100%; margin-top: 14px; margin-bottom: 24px; }
    .addresses td { vertical-align: top; width: 50%; }

    .meta-table td { padding: 2px 14px 2px 0; font-size: 9px; }
    .meta-table .k { color: #94a3b8; text-transform: uppercase; font-size: 7px; letter-spacing: 2px; }
    .meta-table .v { color: #1e293b; }

    .client-block { padding-left: 0; }
    .client-block .label { font-size: 7px; text-transform: uppercase; color: #94a3b8; letter-spacing: 3px; margin-bottom: 5px; }
    .client-block .name { font-size: 14px; font-weight: bold; color: #0f172a; margin-bottom: 2px; }
    .client-block .meta { font-size: 8px; color: #64748b; line-height: 1.6; }
    .client-rule { height: 2px; background: #475569; width: 32px; margin-bottom: 8px; }

    table.lines { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.lines thead th {
        color: #475569; padding: 7px 8px 9px;
        font-size: 7px; text-transform: uppercase; letter-spacing: 2px;
        text-align: left; border-bottom: 1px solid #1e293b;
        background: #ffffff;
    }
    table.lines thead th.num, table.lines td.num { text-align: right; }
    table.lines tbody td {
        padding: 9px 8px; border-bottom: 1px solid #f1f5f9;
        font-size: 9.5px; color: #334155;
    }
    table.lines tfoot td {
        padding: 6px 8px; border-top: 1px solid #cbd5e1;
        font-size: 8.5px; color: #64748b; font-style: italic;
    }

    table.totals { width: 38%; margin-left: 62%; margin-top: 18px; border-collapse: collapse; }
    table.totals td { padding: 5px 0; font-size: 10px; border-bottom: 1px solid #f1f5f9; }
    table.totals .k { color: #94a3b8; }
    table.totals .v { text-align: right; font-weight: bold; color: #1e293b; }
    table.totals tr.grand td {
        border-top: 2px solid #475569; border-bottom: 2px solid #475569;
        color: #0f172a; font-size: 13px; font-weight: bold;
        padding-top: 8px; padding-bottom: 8px;
    }
    table.totals tr.grand td.k { color: #475569; }
    table.totals tr.paid td { color: #166534; border-bottom: none; }
    table.totals tr.due td { color: #991b1b; font-weight: bold; border-bottom: none; }

    .qr-section { margin-top: 30px; width: 100%; }
    .qr-section td { vertical-align: top; }
    .qr-box { text-align: center; width: 110px; }
    .qr-box img { width: 88px; height: 88px; }
    .qr-caption { font-size: 7px; color: #94a3b8; margin-top: 3px; }
    .notes { font-size: 9px; color: #475569; padding-right: 24px; }
    .notes .title { font-weight: bold; color: #1e293b; margin-bottom: 4px; font-size: 8.5px; text-transform: uppercase; letter-spacing: 1.5px; }
    .hash { font-size: 6.5px; color: #cbd5e1; word-break: break-all; margin-top: 4px; }
</style>
</head>
<body>

@if ($watermark)
    <div class="watermark">{{ $watermark }}</div>
@endif

<header>
    <div class="header-rule-top"></div>
    <div class="header-inner">
        <table style="width:100%">
            <tr>
                <td style="width:55%">
                    <div class="company-name">{{ $company->name }}</div>
                    <div class="company-meta">
                        {{ $company->address }}@if($company->city), {{ $company->city }}@endif — {{ $company->country }}<br>
                        @if($company->phone){{ $company->phone }} · @endif{{ $company->email }}
                        @if($company->tax_id) · N° Fiscal : {{ $company->tax_id }}@endif
                    </div>
                </td>
                <td style="width:45%">
                    <div class="doc-title">{{ $document->type_label }}</div>
                    <div class="doc-number">{{ $document->number }}</div>
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
                {{ $document->verificationUrl() }}
            </td>
            <td style="width:30%; text-align:right;">
                <span class="footer-brand">IBIG FactPro</span><br>
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
                    <tr><td class="k">Émission</td><td class="v">{{ $document->issue_date->format('d/m/Y') }}</td></tr>
                    @if ($document->due_date)
                        <tr><td class="k">Échéance</td><td class="v">{{ $document->due_date->format('d/m/Y') }}</td></tr>
                    @endif
                    @if ($document->reference)
                        <tr><td class="k">Référence</td><td class="v">{{ $document->reference }}</td></tr>
                    @endif
                    <tr><td class="k">Devise</td><td class="v">{{ $document->currency }}</td></tr>
                </table>
            </td>
            <td>
                <div class="client-block">
                    <div class="label">Facturé à</div>
                    <div class="client-rule"></div>
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
        <tr>
            <td class="k">TVA</td>
            <td class="v">{{ number_format((float) $document->tax_amount, 0, ',', ' ') }} {{ $document->currency }}</td>
        </tr>
        <tr class="grand">
            <td class="k">Total TTC</td>
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
                    <div class="title" style="margin-top:12px">Conditions</div>
                    <div>{!! nl2br(e($document->terms)) !!}</div>
                @endif
            </td>
            <td class="qr-box">
                <img src="{{ $qrDataUri }}" alt="QR de vérification">
                <div class="qr-caption">
                    <b>VÉRIFICATION</b><br>
                    Scannez pour authentifier
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

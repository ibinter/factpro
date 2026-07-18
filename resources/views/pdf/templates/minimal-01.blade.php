<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @page { margin: 115px 55px 90px 55px; }

    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9.5px; font-weight: normal; color: #212121; margin: 0; }

    /* Filigrane VERSION ESSAI */
    .watermark {
        position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 42px; font-weight: bold;
        color: rgba(220, 38, 38, 0.18); z-index: 1000; letter-spacing: 4px;
    }

    /* En-tête couture : société centrée, filet fin */
    header { position: fixed; top: -85px; left: 0; right: 0; height: 76px; text-align: center; }
    .company-name { font-size: 16px; font-weight: normal; color: #212121; letter-spacing: 5px; text-transform: uppercase; }
    .company-meta { font-size: 7.5px; color: #9E9E9E; line-height: 1.6; letter-spacing: 1px; margin-top: 3px; }
    .hairline { border-bottom: 0.5pt solid #9E9E9E; margin-top: 8px; }
    .doc-title { font-size: 13px; font-weight: normal; color: #212121; letter-spacing: 6px; text-transform: uppercase; margin-top: 6px; }
    .doc-number { font-size: 8.5px; color: #9E9E9E; letter-spacing: 2px; }

    /* Pied de page */
    footer {
        position: fixed; bottom: -70px; left: 0; right: 0; height: 60px;
        font-size: 7.5px; color: #9E9E9E; border-top: 0.5pt solid #9E9E9E; padding-top: 7px; letter-spacing: 0.5px;
    }

    .addresses { width: 100%; margin-bottom: 26px; }
    .addresses td { vertical-align: top; width: 50%; }
    .badge-box { border-top: 0.5pt solid #9E9E9E; padding: 12px 2px 0 0; }
    .badge-box .label { font-size: 7px; text-transform: uppercase; color: #9E9E9E; letter-spacing: 3px; }
    .badge-box .name { font-size: 11.5px; color: #212121; margin: 5px 0; letter-spacing: 1px; }
    .badge-box .company-meta { text-align: left; }

    .meta-table { margin-top: 12px; }
    .meta-table td { padding: 3px 10px 3px 0; font-size: 8.5px; letter-spacing: 0.5px; }
    .meta-table .k { color: #9E9E9E; text-transform: uppercase; font-size: 7px; letter-spacing: 2px; }

    table.lines { width: 100%; border-collapse: collapse; margin-top: 14px; }
    table.lines thead th {
        color: #9E9E9E; padding: 8px 8px; font-size: 7.5px;
        text-transform: uppercase; letter-spacing: 2px; text-align: left; font-weight: normal;
        border-top: 0.5pt solid #9E9E9E; border-bottom: 0.5pt solid #9E9E9E;
    }
    table.lines thead th.num, table.lines td.num { text-align: right; }
    table.lines tbody td { padding: 9px 8px; border-bottom: 0.5pt solid #E0E0E0; font-size: 9px; }

    table.totals { width: 42%; margin-left: 58%; margin-top: 18px; border-collapse: collapse; }
    table.totals td { padding: 6px 8px; font-size: 9.5px; }
    table.totals .k { color: #9E9E9E; letter-spacing: 1px; }
    table.totals .v { text-align: right; }
    table.totals tr.grand td {
        color: #212121; font-size: 11.5px; letter-spacing: 1px;
        border-top: 1pt solid #212121; border-bottom: 1pt solid #212121;
    }
    table.totals tr.paid td { color: #388E3C; }
    table.totals tr.due td { color: #B71C1C; }

    .qr-section { margin-top: 30px; width: 100%; }
    .qr-section td { vertical-align: top; }
    .qr-box { text-align: center; width: 110px; }
    .qr-box img { width: 84px; height: 84px; }
    .qr-caption { font-size: 6.5px; color: #9E9E9E; margin-top: 3px; letter-spacing: 1px; }
    .notes { font-size: 8.5px; color: #424242; padding-right: 24px; line-height: 1.6; }
    .notes .title { color: #9E9E9E; margin-bottom: 3px; font-size: 7px; text-transform: uppercase; letter-spacing: 3px; }
    .hash { font-size: 6px; color: #BDBDBD; word-break: break-all; margin-top: 4px; }
</style>
</head>
<body>

@if ($watermark)
    <div class="watermark">{{ $watermark }}</div>
@endif

<header>
    <div class="company-name">{{ $company->name }}</div>
    <div class="company-meta">
        {{ $company->address }}@if($company->city), {{ $company->city }}@endif — {{ $company->country }}
        @if($company->phone) · Tél : {{ $company->phone }}@endif · {{ $company->email }}
        @if($company->tax_id) · N° Fiscal : {{ $company->tax_id }}@endif
    </div>
    <div class="hairline"></div>
    <div class="doc-title">{{ $document->type_label }}</div>
    <div class="doc-number">N° {{ $document->number }}</div>
</header>

<footer>
    <table style="width:100%">
        <tr>
            <td style="width:70%">
                {{ $company->invoice_footer ?? 'Merci de votre confiance.' }}<br>
                Document vérifiable : {{ $document->verificationUrl() }}
            </td>
            <td style="width:30%; text-align:right;">
                Propulsé par <b style="color:#212121">IBIG FactPro</b><br>
                factpro.ibigsoft.com
            </td>
        </tr>
    </table>
</footer>

<main>
    <table class="addresses">
        <tr>
            <td>
                <table class="meta-table">
                    <tr><td class="k">Émission</td><td>{{ $document->issue_date->format('d/m/Y') }}</td></tr>
                    @if ($document->due_date)
                        <tr><td class="k">Échéance</td><td>{{ $document->due_date->format('d/m/Y') }}</td></tr>
                    @endif
                    @if ($document->reference)
                        <tr><td class="k">Référence</td><td>{{ $document->reference }}</td></tr>
                    @endif
                    <tr><td class="k">Devise</td><td>{{ $document->currency }}</td></tr>
                </table>
            </td>
            <td>
                @if ($document->customer)
                    <div class="badge-box">
                        <div class="label">Facturé à</div>
                        <div class="name">{{ $document->customer->name }}</div>
                        <div class="company-meta">
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
                    Scannez pour vérifier l'authenticité
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

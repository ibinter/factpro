<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @@page { margin: 110px 50px 90px 50px; }

    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111111; margin: 0; }

    /* Filigrane VERSION ESSAI */
    .watermark {
        position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 42px; font-weight: bold;
        color: rgba(220, 38, 38, 0.18); z-index: 1000; letter-spacing: 4px;
    }

    /* En-tête luxe : titres serif, double filet or */
    header { position: fixed; top: -82px; left: 0; right: 0; height: 74px; }
    .double-rule { border-top: 2px solid #C9A227; border-bottom: 1px solid #C9A227; height: 3px; margin-top: 8px; }
    .company-name { font-family: DejaVu Serif, serif; font-size: 19px; font-weight: bold; color: #111111; letter-spacing: 2px; }
    .company-meta { font-size: 8px; color: #6E6A5E; line-height: 1.5; }
    .doc-title { font-family: DejaVu Serif, serif; font-size: 19px; font-weight: bold; color: #C9A227; text-align: right; text-transform: uppercase; letter-spacing: 3px; }
    .doc-number { font-size: 10px; color: #6E6A5E; text-align: right; letter-spacing: 1px; }

    /* Pied de page */
    footer {
        position: fixed; bottom: -70px; left: 0; right: 0; height: 60px;
        font-size: 8px; color: #6E6A5E; border-top: 2px solid #C9A227; padding-top: 6px;
    }

    .addresses { width: 100%; margin-bottom: 20px; }
    .addresses td { vertical-align: top; width: 50%; }
    .badge-box { background: #111111; border: 1px solid #C9A227; padding: 10px 12px; }
    .badge-box .label { font-size: 8px; text-transform: uppercase; color: #C9A227; letter-spacing: 3px; }
    .badge-box .name { font-family: DejaVu Serif, serif; font-size: 12px; font-weight: bold; color: #EDE3C8; margin: 4px 0; }
    .badge-box .company-meta { color: #B7B09A; }

    .meta-table { margin-top: 8px; }
    .meta-table td { padding: 2px 8px 2px 0; font-size: 9px; }
    .meta-table .k { color: #C9A227; text-transform: uppercase; font-size: 7.5px; letter-spacing: 2px; }

    table.lines { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.lines thead th {
        background: #111111; color: #C9A227; padding: 8px 8px; font-size: 8.5px;
        text-transform: uppercase; letter-spacing: 1.5px; text-align: left;
    }
    table.lines thead th.num, table.lines td.num { text-align: right; }
    table.lines tbody td { padding: 8px 8px; border-bottom: 1px solid #E6DFC9; font-size: 9.5px; }
    table.lines tbody tr:nth-child(even) td { background: #FBF8EF; }

    table.totals { width: 42%; margin-left: 58%; margin-top: 14px; border-collapse: collapse; }
    table.totals td { padding: 5px 8px; font-size: 10px; }
    table.totals .k { color: #6E6A5E; }
    table.totals .v { text-align: right; font-weight: bold; }
    table.totals tr.grand td {
        background: #111111; color: #C9A227; font-size: 12px; font-weight: bold;
        font-family: DejaVu Serif, serif; border-top: 2px solid #C9A227; border-bottom: 2px solid #C9A227;
    }
    table.totals tr.paid td { color: #2E7D32; }
    table.totals tr.due td { color: #B71C1C; }

    .qr-section { margin-top: 26px; width: 100%; }
    .qr-section td { vertical-align: top; }
    .qr-box { text-align: center; width: 110px; }
    .qr-box img { width: 90px; height: 90px; }
    .qr-caption { font-size: 7px; color: #6E6A5E; margin-top: 2px; }
    .notes { font-size: 9px; color: #333; padding-right: 20px; }
    .notes .title { font-family: DejaVu Serif, serif; font-weight: bold; color: #C9A227; margin-bottom: 3px; font-size: 9px; letter-spacing: 1px; }
    .hash { font-size: 6.5px; color: #B7B09A; word-break: break-all; margin-top: 4px; }
</style>
</head>
<body>

@if ($watermark)
    <div class="watermark">{{ $watermark }}</div>
@endif

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
    <div class="double-rule"></div>
</header>

<footer>
    <table style="width:100%">
        <tr>
            <td style="width:70%">
                {{ $company->invoice_footer ?? 'Merci de votre confiance.' }}<br>
                Document vérifiable : {{ $document->verificationUrl() }}
            </td>
            <td style="width:30%; text-align:right;">
                Propulsé par <b style="color:#C9A227">IBIG FactPro</b><br>
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
                <div class="badge-box">
                        <div class="label">Facturé à</div>
                    @if ($document->customer)
                        <div class="name">{{ $document->customer->name }}</div>
                        <div class="company-meta">
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
                    <div class="title" style="margin-top:8px">Conditions</div>
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

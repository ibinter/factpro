<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @@page { margin: 108px 45px 90px 45px; }

    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a1a; margin: 0; }

    .watermark {
        position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 42px; font-weight: bold;
        color: rgba(220, 38, 38, 0.18); z-index: 1000; letter-spacing: 4px;
    }

    /* En-tête noir luxe avec bordure or */
    header {
        position: fixed; top: -108px; left: -45px; right: -45px; height: 98px;
        background: #1a1a1a;
        border-bottom: 3px solid #F0C040;
        padding: 18px 45px 0 45px;
    }
    .company-name { font-size: 20px; font-weight: bold; color: #F0C040; letter-spacing: 2px; text-transform: uppercase; }
    .company-meta { font-size: 8px; color: #aaaaaa; line-height: 1.6; margin-top: 3px; }
    .doc-title { font-size: 18px; font-weight: bold; color: #ffffff; text-align: right; text-transform: uppercase; letter-spacing: 2px; }
    .doc-number { font-size: 10px; color: #F0C040; text-align: right; margin-top: 4px; }

    /* Bordure or latérale gauche sur toute la page */
    .side-border {
        position: fixed; top: -108px; bottom: -90px; left: -45px; width: 6px;
        background: #F0C040;
    }

    /* Pied de page */
    footer {
        position: fixed; bottom: -70px; left: 0; right: 0; height: 60px;
        font-size: 8px; color: #555555;
        border-top: 2px solid #F0C040; padding-top: 6px;
    }

    .addresses { width: 100%; margin-bottom: 18px; }
    .addresses td { vertical-align: top; width: 50%; }

    .badge-box {
        background: #f9f6ee; border: 1px solid #F0C040;
        border-left: 4px solid #F0C040; padding: 10px 12px;
    }
    .badge-box .label { font-size: 8px; text-transform: uppercase; color: #888; letter-spacing: 1px; }
    .badge-box .name { font-size: 12px; font-weight: bold; color: #1a1a1a; margin: 3px 0; }
    .badge-box .meta { font-size: 8.5px; color: #555555; line-height: 1.5; }

    .meta-table { margin-top: 8px; }
    .meta-table td { padding: 2px 8px 2px 0; font-size: 9px; }
    .meta-table .k { color: #888888; }

    /* Séparateur or */
    .gold-rule { border: none; border-top: 1px solid #F0C040; margin: 12px 0; }

    table.lines { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.lines thead th {
        background: #1a1a1a; color: #F0C040; padding: 8px 8px;
        font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px;
        text-align: left; border-bottom: 2px solid #F0C040;
    }
    table.lines thead th.num, table.lines td.num { text-align: right; }
    table.lines tbody td { padding: 7px 8px; border-bottom: 1px solid #eeeeee; font-size: 9.5px; }
    table.lines tbody tr:nth-child(even) td { background: #fafaf8; }

    table.totals { width: 46%; margin-left: 54%; margin-top: 12px; border-collapse: collapse; }
    table.totals td { padding: 5px 8px; font-size: 10px; }
    table.totals .k { color: #888888; }
    table.totals .v { text-align: right; font-weight: bold; }
    table.totals tr.grand td {
        background: #1a1a1a; color: #F0C040; font-size: 11.5px; font-weight: bold;
        border-top: 2px solid #F0C040;
    }
    table.totals tr.paid td { color: #2d7a2d; }
    table.totals tr.due td { color: #b32020; }

    .qr-section { margin-top: 25px; width: 100%; }
    .qr-section td { vertical-align: top; }
    .qr-box { text-align: center; width: 110px; }
    .qr-box img { width: 90px; height: 90px; border: 1px solid #F0C040; }
    .qr-caption { font-size: 7px; color: #888888; margin-top: 2px; }
    .notes { font-size: 9px; color: #333333; padding-right: 20px; }
    .notes .title { font-weight: bold; color: #1a1a1a; margin-bottom: 3px; font-size: 9px; }
    .hash { font-size: 6.5px; color: #aaaaaa; word-break: break-all; margin-top: 4px; }
</style>
</head>
<body>

@if ($watermark)
    <div class="watermark">{{ $watermark }}</div>
@endif

<div class="side-border"></div>

<header>
    <table style="width:100%">
        <tr>
            <td style="width:52%; vertical-align: middle;">
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-meta">
                    {{ $company->address }}@if($company->city), {{ $company->city }}@endif — {{ $company->country }}<br>
                    @if($company->phone){{ $company->phone }} · @endif{{ $company->email }}
                    @if($company->tax_id)<br>N° Fiscal : {{ $company->tax_id }}@endif
                </div>
            </td>
            <td style="width:48%; vertical-align: middle;">
                <div class="doc-title">{{ mb_strtoupper($document->type_label) }}</div>
                <div class="doc-number">◆ {{ $document->number }}</div>
            </td>
        </tr>
    </table>
</header>

<footer>
    <table style="width:100%">
        <tr>
            <td style="width:70%">
                {{ $company->invoice_footer ?? 'Merci de votre confiance.' }}<br>
                Vérification : {{ $document->verificationUrl() }}
            </td>
            <td style="width:30%; text-align:right;">
                <span style="color:#F0C040; font-weight:bold;">◆</span>
                <b>IBIG FactPro</b> · Corporate Diamond
            </td>
        </tr>
    </table>
</footer>

<main>
    <table class="addresses">
        <tr>
            <td>
                <table class="meta-table">
                    <tr><td class="k">Date d'émission :</td><td><b>{{ $document->issue_date->format('d/m/Y') }}</b></td></tr>
                    @if ($document->due_date)
                        <tr><td class="k">Échéance :</td><td><b>{{ $document->due_date->format('d/m/Y') }}</b></td></tr>
                    @endif
                    @if ($document->reference)
                        <tr><td class="k">Référence :</td><td>{{ $document->reference }}</td></tr>
                    @endif
                    <tr><td class="k">Devise :</td><td>{{ $document->currency }}</td></tr>
                </table>
            </td>
            <td>
                    <div class="badge-box">
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
                    </div>
                    @else
                        <div class="name" style="color:#aaa;font-style:italic;">&#8212; Non renseign&eacute; &#8212;</div>
                    @endif
                    </div>
            </td>
        </tr>
    </table>

    <hr class="gold-rule">

    <table class="lines">
        <thead>
            <tr>
                <th style="width:40%">Désignation</th>
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
                    <div class="title" style="margin-top:8px">Conditions générales</div>
                    <div>{!! nl2br(e($document->terms)) !!}</div>
                @endif
            </td>
            <td class="qr-box">
                <img src="{{ $qrDataUri }}" alt="QR de vérification">
                <div class="qr-caption">
                    <b>DOCUMENT VÉRIFIABLE</b><br>
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

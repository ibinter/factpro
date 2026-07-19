<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @page { margin: 118px 45px 95px 45px; }

    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a1a; margin: 0; background: #ffffff; }

    .watermark {
        position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 42px; font-weight: bold;
        color: rgba(220, 38, 38, 0.18); z-index: 1000; letter-spacing: 4px;
    }

    /* ═══ En-tête avec motif Kente géométrique ═══ */
    header {
        position: fixed; top: -118px; left: -45px; right: -45px; height: 108px;
        background: #ffffff;
        border-bottom: 3px solid #F0C040;
        padding: 0;
    }

    /* Bandeau kente en haut : losanges or/rouge/vert */
    .kente-band {
        height: 18px;
        background: #F0C040;
        overflow: hidden;
        position: relative;
    }

    /* Contenu de l'en-tête sous le bandeau */
    .header-content {
        padding: 8px 45px 0 45px;
    }

    .company-name { font-size: 19px; font-weight: bold; color: #1a1a1a; letter-spacing: 1px; }
    .company-meta { font-size: 8.5px; color: #555555; line-height: 1.6; margin-top: 2px; }
    .doc-title { font-size: 17px; font-weight: bold; color: #C0392B; text-align: right; text-transform: uppercase; letter-spacing: 1.5px; }
    .doc-number { font-size: 10px; color: #27AE60; text-align: right; margin-top: 4px; font-weight: bold; }

    /* Pied de page */
    footer {
        position: fixed; bottom: -75px; left: -45px; right: -45px; height: 65px;
        font-size: 8px; color: #555555;
        border-top: 3px solid #F0C040;
        padding: 6px 45px 0 45px;
        background: #ffffff;
    }
    /* Bandeau kente en bas */
    .footer-kente {
        position: fixed; bottom: -75px; left: -45px; right: -45px; height: 6px;
        background: #27AE60;
    }

    /* Badge Template #100 */
    .centennial-badge {
        font-size: 7px; color: #C0392B; border: 1px solid #C0392B;
        padding: 1px 4px; letter-spacing: 0.5px; text-transform: uppercase;
        display: inline-block;
    }

    .addresses { width: 100%; margin-bottom: 18px; }
    .addresses td { vertical-align: top; width: 50%; }

    .badge-box {
        background: #FFFDF0; border: 1px solid #F0C040;
        border-left: 4px solid #C0392B; padding: 10px 12px;
    }
    .badge-box .label { font-size: 8px; text-transform: uppercase; color: #C0392B; letter-spacing: 1px; }
    .badge-box .name { font-size: 12px; font-weight: bold; color: #1a1a1a; margin: 3px 0; }
    .badge-box .meta { font-size: 8.5px; color: #555555; line-height: 1.5; }

    .meta-table { margin-top: 8px; }
    .meta-table td { padding: 2px 8px 2px 0; font-size: 9px; }
    .meta-table .k { color: #888888; }

    /* Filet décoratif tri-couleur */
    .kente-rule {
        height: 4px; margin: 12px 0;
        background: #F0C040;
        border: none;
    }

    table.lines { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.lines thead th {
        background: #1a1a1a; color: #F0C040; padding: 8px 8px;
        font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px;
        text-align: left; border-bottom: 3px solid #C0392B;
    }
    table.lines thead th.num, table.lines td.num { text-align: right; }
    table.lines tbody td { padding: 7px 8px; border-bottom: 1px solid #eeeecc; font-size: 9.5px; color: #1a1a1a; }
    table.lines tbody tr:nth-child(even) td { background: #FFFDF0; }

    table.totals { width: 46%; margin-left: 54%; margin-top: 12px; border-collapse: collapse; }
    table.totals td { padding: 5px 8px; font-size: 10px; }
    table.totals .k { color: #888888; }
    table.totals .v { text-align: right; font-weight: bold; }
    table.totals tr.grand td {
        background: #1a1a1a; color: #F0C040; font-size: 11.5px; font-weight: bold;
        border-top: 3px solid #F0C040; border-bottom: 2px solid #C0392B;
    }
    table.totals tr.paid td { color: #27AE60; }
    table.totals tr.due td { color: #C0392B; }

    .qr-section { margin-top: 25px; width: 100%; }
    .qr-section td { vertical-align: top; }
    .qr-box { text-align: center; width: 110px; }
    .qr-box img { width: 90px; height: 90px; border: 2px solid #F0C040; }
    .qr-caption { font-size: 7px; color: #555555; margin-top: 2px; }
    .notes { font-size: 9px; color: #1a1a1a; padding-right: 20px; }
    .notes .title { font-weight: bold; color: #C0392B; margin-bottom: 3px; font-size: 9px; }
    .hash { font-size: 6.5px; color: #aaaaaa; word-break: break-all; margin-top: 4px; }
</style>
</head>
<body>

@if ($watermark)
    <div class="watermark">{{ $watermark }}</div>
@endif

<header>
    {{-- Bandeau Kente : motif losanges or/rouge/vert en SVG --}}
    <div class="kente-band">
        <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="18" preserveAspectRatio="none">
            <defs>
                <pattern id="kente" x="0" y="0" width="54" height="18" patternUnits="userSpaceOnUse">
                    <!-- Or (fond) -->
                    <rect width="54" height="18" fill="#F0C040"/>
                    <!-- Rouge : losange gauche -->
                    <polygon points="0,9 9,0 18,9 9,18" fill="#C0392B"/>
                    <!-- Vert : losange centre -->
                    <polygon points="18,9 27,0 36,9 27,18" fill="#27AE60"/>
                    <!-- Rouge : losange droite -->
                    <polygon points="36,9 45,0 54,9 45,18" fill="#C0392B"/>
                    <!-- Trait or horizontal -->
                    <rect y="8" width="54" height="2" fill="#F0C040" opacity="0.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="18" fill="url(#kente)"/>
        </svg>
    </div>
    <div class="header-content">
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
                    <div class="doc-number">N° {{ $document->number }}</div>
                </td>
            </tr>
        </table>
    </div>
</header>

<div class="footer-kente"></div>
<footer>
    <table style="width:100%">
        <tr>
            <td style="width:65%">
                {{ $company->invoice_footer ?? 'Merci de votre confiance.' }}<br>
                Vérification : {{ $document->verificationUrl() }}
            </td>
            <td style="width:35%; text-align:right;">
                <b style="color:#C0392B">IBIG FactPro</b> <span style="color:#F0C040">◆</span> Africa Kente<br>
                <span class="centennial-badge">Collection 100</span>
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

    <hr class="kente-rule">

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

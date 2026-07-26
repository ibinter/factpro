<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @@page { margin: 110px 45px 90px 45px; }

    * { box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 10px;
        color: #1a2332;
        margin: 0;
    }

    /* ─── Filigrane ─── */
    .watermark {
        position: fixed;
        top: 40%;
        left: 5%;
        width: 90%;
        text-align: center;
        transform: rotate(-35deg);
        font-size: 42px;
        font-weight: bold;
        color: rgba(220, 38, 38, 0.18);
        z-index: 1000;
        letter-spacing: 4px;
    }

    /* ─── En-tête fixe ─── */
    header {
        position: fixed;
        top: -90px;
        left: 0;
        right: 0;
        height: 80px;
        border-bottom: 3px solid {{ $primaryColor }};
        padding-bottom: 8px;
    }
    .company-name { font-size: 18px; font-weight: bold; color: #002D5B; }
    .company-meta { font-size: 8.5px; color: #6B7C93; line-height: 1.6; margin-top: 3px; }
    .doc-title {
        font-size: 22px;
        font-weight: bold;
        color: {{ $primaryColor }};
        text-align: right;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .doc-number { font-size: 10px; color: #6B7C93; text-align: right; margin-top: 2px; }

    /* ─── Pied de page fixe ─── */
    footer {
        position: fixed;
        bottom: -70px;
        left: 0;
        right: 0;
        height: 58px;
        font-size: 8px;
        color: #6B7C93;
        border-top: 2px solid {{ $accentColor }};
        padding-top: 7px;
    }

    /* ─── Contenu principal : respiration depuis l'en-tête ─── */
    main {
        margin-top: 14px;
    }

    /* ─── Bande colorée de séparation sous l'en-tête ─── */
    .header-accent {
        background: {{ $primaryColor }};
        height: 4px;
        margin-bottom: 16px;
        border-radius: 2px;
    }

    /* ─── Blocs Émetteur / Client ─── */
    .addresses { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
    .addresses td { vertical-align: top; width: 50%; padding: 0 6px 0 0; }
    .addresses td:last-child { padding: 0 0 0 6px; }

    .info-block { padding: 8px 10px; }
    .info-block .label {
        font-size: 7.5px;
        text-transform: uppercase;
        color: #6B7C93;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }
    .info-block .name { font-size: 11px; font-weight: bold; color: #002D5B; margin-bottom: 3px; }
    .info-block .detail { font-size: 8.5px; color: #4a5568; line-height: 1.6; }

    .badge-box {
        background: #f4f7fb;
        border-left: 3px solid {{ $primaryColor }};
        border-radius: 3px;
        padding: 8px 10px;
    }
    .badge-box .label { font-size: 7.5px; text-transform: uppercase; color: #6B7C93; letter-spacing: 1px; margin-bottom: 4px; }
    .badge-box .name { font-size: 11px; font-weight: bold; color: #002D5B; margin-bottom: 3px; }
    .badge-box .detail { font-size: 8.5px; color: #4a5568; line-height: 1.6; }

    .meta-table td { padding: 3px 10px 3px 0; font-size: 9px; }
    .meta-table .k { color: #6B7C93; white-space: nowrap; }
    .meta-table .v { font-weight: bold; color: #1a2332; }

    /* ─── Séparateur ─── */
    .divider { border: none; border-top: 1px solid #e2e8f0; margin: 10px 0; }

    /* ─── Tableau des lignes ─── */
    table.lines { width: 100%; border-collapse: collapse; margin-top: 0; }
    table.lines thead th {
        background: #002D5B;
        color: #ffffff;
        padding: 8px 8px;
        font-size: 8.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-align: left;
    }
    table.lines thead th.num { text-align: right; }
    table.lines td.num { text-align: right; }
    table.lines tbody td {
        padding: 7px 8px;
        border-bottom: 1px solid #e5eaf1;
        font-size: 9.5px;
    }
    table.lines tbody tr:nth-child(even) td { background: #f9fbfd; }

    /* ─── Zone totaux + QR ─── */
    .bottom-section {
        display: table;
        width: 100%;
        margin-top: 16px;
        border-collapse: collapse;
    }
    .bottom-left {
        display: table-cell;
        width: 42%;
        vertical-align: bottom;
        padding-right: 12px;
    }
    .bottom-right {
        display: table-cell;
        width: 58%;
        vertical-align: top;
    }

    /* QR code bloc ─ gauche, aligné bas */
    .qr-block {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 10px 12px;
        background: #fafbfc;
        display: table;
        width: 100%;
    }
    .qr-img-cell {
        display: table-cell;
        vertical-align: middle;
        width: 52px;
        padding-right: 10px;
    }
    .qr-img-cell img { width: 48px; height: 48px; display: block; }
    .qr-text-cell { display: table-cell; vertical-align: middle; }
    .qr-text-cell .qr-title { font-size: 7.5px; font-weight: bold; color: #002D5B; margin-bottom: 3px; }
    .qr-text-cell .qr-sub { font-size: 6.5px; color: #6B7C93; line-height: 1.5; }
    .qr-hash { font-size: 6px; color: #9aa7b8; word-break: break-all; margin-top: 5px; }

    /* Tableau des totaux ─ droite */
    table.totals {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #e2e8f0;
        border-radius: 4px;
    }
    table.totals td { padding: 6px 10px; font-size: 10px; }
    table.totals .k { color: #6B7C93; }
    table.totals .v { text-align: right; font-weight: bold; }
    table.totals tr.sep td { border-top: 1px solid #e2e8f0; }
    table.totals tr.grand td {
        background: #002D5B;
        color: #ffffff;
        font-size: 12px;
        font-weight: bold;
    }
    table.totals tr.paid td { color: #16a34a; }
    table.totals tr.due td { color: #dc2626; font-weight: bold; }

    /* ─── Notes ─── */
    .notes-block { margin-top: 14px; }
    .notes-block .title { font-weight: bold; color: #002D5B; font-size: 9px; margin-bottom: 4px; }
    .notes-block .body { font-size: 9px; color: #4a5568; line-height: 1.6; }
</style>
</head>
<body>

@if ($watermark)
    <div class="watermark">{{ $watermark }}</div>
@endif

{{-- ═══ EN-TÊTE FIXE (répété sur chaque page) ═══ --}}
<header>
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:55%; vertical-align:bottom; padding-bottom:6px;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="max-height:20px; max-width:60px; margin-bottom:4px; display:block;" alt="logo">
                @endif
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-meta">
                    @if($company->address){{ $company->address }}@if($company->city), {{ $company->city }}@endif@endif
                    @if($company->country) — {{ $company->country }}@endif<br>
                    @if($company->phone)Tél : {{ $company->phone }}@if($company->email) · @endif@endif
                    @if($company->email){{ $company->email }}@endif
                    @if($company->tax_id)<br>N° Fiscal : {{ $company->tax_id }}@endif
                </div>
            </td>
            <td style="width:45%; vertical-align:bottom; padding-bottom:6px;">
                <div class="doc-title">{{ $document->type_label }}</div>
                <div class="doc-number">N° {{ $document->number }}</div>
            </td>
        </tr>
    </table>
</header>

{{-- ═══ PIED DE PAGE FIXE ═══ --}}
<footer>
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:70%;">
                {{ $company->invoice_footer ?? 'Merci de votre confiance.' }}
            </td>
            <td style="width:30%; text-align:right;">
                Propulsé par <b style="color:{{ $primaryColor }}">IBIG FactPro</b>
            </td>
        </tr>
    </table>
</footer>

{{-- ═══ CONTENU PRINCIPAL ═══ --}}
<main>

    {{-- Bande accent + respiration --}}
    <div class="header-accent"></div>

    {{-- Bloc info document + client --}}
    <table class="addresses">
        <tr>
            <td>
                <div class="info-block">
                    <div class="label">Informations document</div>
                    <table class="meta-table">
                        <tr>
                            <td class="k">Date d'émission</td>
                            <td class="v">{{ $document->issue_date->format('d/m/Y') }}</td>
                        </tr>
                        @if ($document->due_date)
                        <tr>
                            <td class="k">Date d'échéance</td>
                            <td class="v">{{ $document->due_date->format('d/m/Y') }}</td>
                        </tr>
                        @endif
                        @if ($document->reference)
                        <tr>
                            <td class="k">Référence</td>
                            <td class="v">{{ $document->reference }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="k">Devise</td>
                            <td class="v">{{ $document->currency }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td>
                <div class="badge-box">
                    <div class="label">Facturé à</div>
                    @if ($document->customer)
                        <div class="name">{{ $document->customer->name }}</div>
                        <div class="detail">
                            @if($document->customer->address){{ $document->customer->address }}<br>@endif
                            @if($document->customer->city){{ $document->customer->city }}@if($document->customer->country) — {{ $document->customer->country }}@endif<br>@endif
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

    {{-- Tableau des lignes --}}
    <table class="lines">
        <thead>
            <tr>
                <th style="width:40%">Désignation</th>
                <th class="num" style="width:9%">Qté</th>
                <th style="width:8%">Unité</th>
                <th class="num" style="width:13%">P.U. HT</th>
                <th class="num" style="width:7%">Rem.%</th>
                <th class="num" style="width:7%">TVA%</th>
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
                <td class="num">{{ number_format((float) ($line->discount_percent ?? 0), 0) }}</td>
                <td class="num">{{ number_format((float) $line->tax_rate, 0) }}</td>
                <td class="num">{{ number_format((float) $line->line_total, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Zone bas : QR à gauche | Totaux à droite --}}
    <div class="bottom-section">

        {{-- QR code : authentification document --}}
        <div class="bottom-left">
            @if($qrDataUri)
            <div class="qr-block">
                <div class="qr-img-cell">
                    <img src="{{ $qrDataUri }}" alt="QR">
                </div>
                <div class="qr-text-cell">
                    <div class="qr-title">Document certifié</div>
                    <div class="qr-sub">
                        Scannez pour vérifier<br>
                        l'authenticité de ce document.
                    </div>
                </div>
            </div>
            @if($document->integrity_hash)
            <div class="qr-hash">SHA-256 : {{ substr($document->integrity_hash, 0, 40) }}…</div>
            @endif
            @endif
        </div>

        {{-- Totaux --}}
        <div class="bottom-right">
            <table class="totals">
                <tr>
                    <td class="k">Sous-total HT</td>
                    <td class="v">{{ number_format((float) $document->subtotal, 0, ',', ' ') }} {{ $document->currency }}</td>
                </tr>
                @if ((float) ($document->discount_amount ?? 0) > 0)
                <tr class="sep">
                    <td class="k">Remise</td>
                    <td class="v" style="color:#dc2626;">−{{ number_format((float) $document->discount_amount, 0, ',', ' ') }} {{ $document->currency }}</td>
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
                @if ((float) ($document->amount_paid ?? 0) > 0)
                <tr class="paid">
                    <td class="k">Montant payé</td>
                    <td class="v">{{ number_format((float) $document->amount_paid, 0, ',', ' ') }} {{ $document->currency }}</td>
                </tr>
                <tr class="due">
                    <td class="k">Reste à payer</td>
                    <td class="v">{{ number_format(max(0, (float) $document->total - (float) $document->amount_paid), 0, ',', ' ') }} {{ $document->currency }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- Notes / conditions --}}
    @if ($document->notes || $document->terms)
    <div class="notes-block">
        @if ($document->notes)
            <div class="title">Notes</div>
            <div class="body">{!! nl2br(e($document->notes)) !!}</div>
        @endif
        @if ($document->terms)
            <div class="title" style="margin-top:8px">Conditions</div>
            <div class="body">{!! nl2br(e($document->terms)) !!}</div>
        @endif
    </div>
    @endif

    @include('pdf.partials.signature')

</main>
</body>
</html>

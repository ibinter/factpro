<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @@page { margin: 145px 45px 110px 45px; }

    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a2332; margin: 0; }

    .watermark {
        position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 42px; font-weight: bold;
        color: rgba(220, 38, 38, 0.18); z-index: 1000; letter-spacing: 4px;
    }

    header {
        position: fixed; top: -125px; left: 0; right: 0; height: 110px;
        border-bottom: 3px solid {{ $primaryColor }}; padding-bottom: 8px;
    }
    .company-name { font-size: 17px; font-weight: bold; color: #002D5B; }
    .company-meta { font-size: 8.5px; color: #6B7C93; line-height: 1.6; margin-top: 3px; }
    .doc-title { font-size: 22px; font-weight: bold; color: {{ $primaryColor }}; text-align: right; text-transform: uppercase; letter-spacing: 1px; }
    .doc-number { font-size: 10px; color: #6B7C93; text-align: right; margin-top: 2px; }

    footer {
        position: fixed; bottom: -92px; left: 0; right: 0; height: 78px;
        font-size: 8px; color: #6B7C93; border-top: 2px solid {{ $accentColor }}; padding-top: 8px;
    }

    main { margin-top: 22px; }

    .header-accent {
        background: {{ $primaryColor }}; height: 4px; margin-bottom: 22px; border-radius: 2px;
    }

    .addresses { width: 100%; margin-bottom: 18px; border-collapse: collapse; }
    .addresses td { vertical-align: top; width: 50%; }

    .info-block { padding: 0 12px 0 0; }
    .info-block .label { font-size: 7.5px; text-transform: uppercase; color: #6B7C93; letter-spacing: 1px; margin-bottom: 5px; }

    .badge-box {
        background: #f4f7fb; border-left: 3px solid {{ $primaryColor }};
        border-radius: 3px; padding: 9px 12px;
    }
    .badge-box .label { font-size: 7.5px; text-transform: uppercase; color: #6B7C93; letter-spacing: 1px; margin-bottom: 4px; }
    .badge-box .name { font-size: 12px; font-weight: bold; color: #002D5B; margin-bottom: 3px; }
    .badge-box .detail { font-size: 8.5px; color: #4a5568; line-height: 1.6; }

    .meta-table td { padding: 3px 10px 3px 0; font-size: 9px; }
    .meta-table .k { color: #6B7C93; white-space: nowrap; }
    .meta-table .v { font-weight: bold; color: #1a2332; }

    table.lines { width: 100%; border-collapse: collapse; }
    table.lines thead th {
        background: #002D5B; color: #fff; padding: 8px; font-size: 8.5px;
        text-transform: uppercase; letter-spacing: 0.5px; text-align: left;
    }
    table.lines thead th.num { text-align: right; }
    table.lines td.num { text-align: right; }
    table.lines tbody td { padding: 7px 8px; border-bottom: 1px solid #e5eaf1; font-size: 9.5px; }
    table.lines tbody tr:nth-child(even) td { background: #f9fbfd; }

    /* Zone bas : QR à gauche, totaux à droite */
    .bottom-row { width: 100%; border-collapse: collapse; margin-top: 18px; }
    .bottom-row td { vertical-align: top; }
    .qr-cell { width: 42%; padding-right: 14px; vertical-align: bottom; }
    .totals-cell { width: 58%; }

    .qr-block {
        border: 1px solid #e2e8f0; border-radius: 6px;
        padding: 10px 12px; background: #fafbfc;
    }
    .qr-inner { display: table; width: 100%; border-collapse: collapse; }
    .qr-img { display: table-cell; width: 54px; vertical-align: middle; padding-right: 10px; }
    .qr-img img { width: 50px; height: 50px; display: block; }
    .qr-txt { display: table-cell; vertical-align: middle; }
    .qr-title { font-size: 7.5px; font-weight: bold; color: #002D5B; margin-bottom: 3px; }
    .qr-sub { font-size: 6.5px; color: #6B7C93; line-height: 1.6; }
    .qr-hash { font-size: 6px; color: #9aa7b8; word-break: break-all; margin-top: 5px; }

    table.totals { width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; }
    table.totals td { padding: 6px 10px; font-size: 10px; }
    table.totals .k { color: #6B7C93; }
    table.totals .v { text-align: right; font-weight: bold; }
    table.totals .sep td { border-top: 1px solid #e2e8f0; }
    table.totals .grand td { background: #002D5B; color: #fff; font-size: 12px; font-weight: bold; }
    table.totals .paid td { color: #16a34a; }
    table.totals .due td { color: #dc2626; font-weight: bold; }

    .notes-block { margin-top: 14px; font-size: 9px; color: #4a5568; }
    .notes-block .title { font-weight: bold; color: #002D5B; margin-bottom: 4px; }

    /* Zone de signatures */
    .sig-section { margin-top: 28px; }
    .sig-section .sig-heading {
        font-size: 7.5px; text-transform: uppercase; color: #6B7C93;
        letter-spacing: 1px; margin-bottom: 10px;
        border-bottom: 1px solid #e2e8f0; padding-bottom: 4px;
    }
    .sig-row { width: 100%; border-collapse: collapse; }
    .sig-row td { width: 50%; vertical-align: top; padding: 0 8px; }
    .sig-row td:first-child { padding-left: 0; border-right: 1px solid #e2e8f0; }
    .sig-row td:last-child { padding-right: 0; }
    .sig-label { font-size: 8px; color: #6B7C93; text-align: center; margin-bottom: 6px; }
    .sig-box {
        border: 1px solid #c8d3e0; border-radius: 4px;
        height: 55px; background: #fafbfc;
        position: relative;
    }
    .sig-box-inner { padding: 4px 8px; }
    .sig-name-line {
        border-bottom: 1px dashed #c8d3e0; margin: 0 8px;
        position: absolute; bottom: 16px; left: 0; right: 0;
    }
    .sig-name-label { font-size: 6.5px; color: #aaa; position: absolute; bottom: 4px; left: 8px; }
</style>
</head>
<body>

@if($watermark)
<div class="watermark">{{ $watermark }}</div>
@endif

<header>
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:55%; vertical-align:bottom; padding-bottom:6px;">
                @if($logoBase64)
                <img src="{{ $logoBase64 }}" style="max-height:20px; max-width:60px; margin-bottom:4px; display:block;" alt="logo">
                @endif
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-meta">
                    @php
                        $parts = array_filter([
                            $company->address ?? null,
                            $company->city ?? null,
                            $company->country ?? null,
                        ]);
                    @endphp
                    {{ implode(', ', $parts) }}<br>
                    @if($company->phone)Tél : {{ $company->phone }}@if($company->email) · @endif
                    @endif
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

<footer>
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:70%;">{{ $company->invoice_footer ?? 'Merci de votre confiance.' }}</td>
            <td style="width:30%; text-align:right;">Propulsé par <b style="color:{{ $primaryColor }}">IBIG FactPro</b></td>
        </tr>
    </table>
</footer>

<main>
    <div class="header-accent"></div>

    {{-- En-tête info document + client --}}
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
                        @if($document->due_date)
                        <tr>
                            <td class="k">Date d'échéance</td>
                            <td class="v">{{ $document->due_date->format('d/m/Y') }}</td>
                        </tr>
                        @endif
                        @if($document->reference)
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
                    @if($document->customer)
                        <div class="name">{{ $document->customer->name }}</div>
                        <div class="detail">
                            @php
                                $cParts = array_filter([
                                    $document->customer->address ?? null,
                                    trim(($document->customer->city ?? '') . ' ' . ($document->customer->country ?? '')),
                                ]);
                            @endphp
                            {{ implode('<br>', $cParts) }}<br>
                            @if($document->customer->phone){{ $document->customer->phone }}@endif
                            @if($document->customer->tax_id) · NIF : {{ $document->customer->tax_id }}@endif
                        </div>
                    @else
                        <div class="name" style="color:#aaa;font-style:italic;">— Non renseigné —</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Lignes --}}
    <table class="lines">
        <thead>
            <tr>
                <th style="width:40%">Désignation</th>
                <th class="num" style="width:9%">Qté</th>
                <th style="width:8%">Unité</th>
                <th class="num" style="width:13%">P.U. HT</th>
                <th class="num" style="width:7%">Rem.%</th>
                <th class="num" style="width:7%">TVA%</th>
                <th class="num" style="width:16%">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->lines as $line)
            <tr>
                <td>{{ $line->description }}</td>
                <td class="num">{{ number_format((float)$line->quantity, 2, ',', ' ') }}</td>
                <td>{{ $line->unit }}</td>
                <td class="num">{{ number_format((float)$line->unit_price, 0, ',', ' ') }}</td>
                <td class="num">{{ number_format((float)($line->discount_percent ?? 0), 0) }}</td>
                <td class="num">{{ number_format((float)$line->tax_rate, 0) }}</td>
                <td class="num">{{ number_format((float)$line->line_total, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- QR à gauche | Totaux à droite --}}
    <table class="bottom-row">
        <tr>
            <td class="qr-cell">
                @if($qrDataUri)
                <div class="qr-block">
                    <div class="qr-inner">
                        <div class="qr-img"><img src="{{ $qrDataUri }}" alt="QR"></div>
                        <div class="qr-txt">
                            <div class="qr-title">Document certifié</div>
                            <div class="qr-sub">Scannez pour vérifier<br>l'authenticité de ce document.</div>
                        </div>
                    </div>
                    @if($document->integrity_hash)
                    <div class="qr-hash">SHA-256 : {{ substr($document->integrity_hash, 0, 40) }}…</div>
                    @endif
                </div>
                @endif
            </td>
            <td class="totals-cell">
                <table class="totals">
                    <tr>
                        <td class="k">Sous-total HT</td>
                        <td class="v">{{ number_format((float)$document->subtotal, 0, ',', ' ') }} {{ $document->currency }}</td>
                    </tr>
                    @if((float)($document->discount_amount ?? 0) > 0)
                    <tr class="sep">
                        <td class="k">Remise</td>
                        <td class="v" style="color:#dc2626;">−{{ number_format((float)$document->discount_amount, 0, ',', ' ') }} {{ $document->currency }}</td>
                    </tr>
                    @endif
                    <tr class="sep">
                        <td class="k">TVA</td>
                        <td class="v">{{ number_format((float)$document->tax_amount, 0, ',', ' ') }} {{ $document->currency }}</td>
                    </tr>
                    <tr class="grand">
                        <td>TOTAL TTC</td>
                        <td class="v">{{ number_format((float)$document->total, 0, ',', ' ') }} {{ $document->currency }}</td>
                    </tr>
                    @if((float)($document->amount_paid ?? 0) > 0)
                    <tr class="paid">
                        <td class="k">Montant payé</td>
                        <td class="v">{{ number_format((float)$document->amount_paid, 0, ',', ' ') }} {{ $document->currency }}</td>
                    </tr>
                    <tr class="due">
                        <td class="k">Reste à payer</td>
                        <td class="v">{{ number_format(max(0, (float)$document->total - (float)$document->amount_paid), 0, ',', ' ') }} {{ $document->currency }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    @if($document->notes || $document->terms)
    <div class="notes-block">
        @if($document->notes)
        <div class="title">Notes</div>
        <div>{!! nl2br(e($document->notes)) !!}</div>
        @endif
        @if($document->terms)
        <div class="title" style="margin-top:8px">Conditions</div>
        <div>{!! nl2br(e($document->terms)) !!}</div>
        @endif
    </div>
    @endif

    {{-- Zone de signatures --}}
    <div class="sig-section">
        <div class="sig-heading">Signatures</div>
        <table class="sig-row">
            <tr>
                <td>
                    <div class="sig-label">{{ $signatureLabels[0] ?? 'Signature et cachet de l\'émetteur' }}</div>
                    <div class="sig-box">
                        @if(!empty($document->signature_path))
                        <div class="sig-box-inner">
                            <img src="{{ $signatureBase64 ?? '' }}" style="max-height:40px; max-width:90%; display:block; margin:0 auto;" alt="Signature">
                        </div>
                        @endif
                        <div class="sig-name-line"></div>
                        <div class="sig-name-label">Nom, date et cachet</div>
                    </div>
                </td>
                <td>
                    <div class="sig-label">{{ $signatureLabels[1] ?? 'Bon pour accord — Signature du client' }}</div>
                    <div class="sig-box">
                        <div class="sig-name-line"></div>
                        <div class="sig-name-label">Nom, date et cachet</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Mention légale --}}
    <div style="margin-top:16px; font-size:7px; color:#9aa7b8; border-top:1px solid #e2e8f0; padding-top:6px; text-align:center;">
        Tout retard de paiement entraîne des pénalités de retard au taux légal en vigueur.
        Document généré et certifié par <strong>IBIG FactPro</strong>.
    </div>

</main>
</body>
</html>

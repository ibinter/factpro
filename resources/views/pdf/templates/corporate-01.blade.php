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
        position: fixed; top: -130px; left: 0; right: 0; height: 115px;
        border-bottom: 3px solid {{ $primaryColor }}; padding-bottom: 8px;
    }
    .company-name { font-size: 17px; font-weight: bold; color: #002D5B; line-height: 1.2; }
    .company-sub  { font-size: 7.5px; color: #9aa7b8; font-style: italic; margin-top: 1px; }
    .company-meta { font-size: 8.5px; color: #6B7C93; line-height: 1.7; margin-top: 4px; }
    .doc-title  { font-size: 22px; font-weight: bold; color: {{ $primaryColor }}; text-align: right; text-transform: uppercase; letter-spacing: 1px; }
    .doc-number { font-size: 10px; color: #6B7C93; text-align: right; margin-top: 2px; }
    .doc-status { text-align: right; margin-top: 4px; }
    .doc-status span { font-size: 8px; font-weight: bold; padding: 2px 8px; border-radius: 10px; background: {{ $primaryColor }}; color: #fff; }

    footer {
        position: fixed; bottom: -92px; left: 0; right: 0; height: 78px;
        font-size: 7.5px; color: #6B7C93; border-top: 2px solid {{ $accentColor ?? $primaryColor }}; padding-top: 8px;
    }

    main { margin-top: 22px; }

    .header-accent {
        background: {{ $primaryColor }}; height: 4px; margin-bottom: 20px; border-radius: 2px;
    }

    .addresses { width: 100%; margin-bottom: 18px; border-collapse: collapse; }
    .addresses td { vertical-align: top; width: 50%; }

    .info-block {
        background: #f8f9fb; border: 1px solid #e2e8f0; border-radius: 3px; padding: 9px 12px; margin-right: 12px;
    }
    .info-block .label { font-size: 7.5px; text-transform: uppercase; color: #9aa7b8; letter-spacing: 1px; margin-bottom: 6px; font-weight: bold; }

    .badge-box {
        background: #f4f7fb; border: 1px solid #e2e8f0; border-left: 3px solid {{ $primaryColor }};
        border-radius: 3px; padding: 9px 12px;
    }
    .badge-box .label { font-size: 7.5px; text-transform: uppercase; color: #9aa7b8; letter-spacing: 1px; margin-bottom: 4px; font-weight: bold; }
    .badge-box .name  { font-size: 12px; font-weight: bold; color: #002D5B; margin-bottom: 3px; line-height: 1.2; }
    .badge-box .detail { font-size: 8.5px; color: #4a5568; line-height: 1.7; margin-top: 5px; }

    .meta-table td { padding: 3px 10px 3px 0; font-size: 9px; }
    .meta-table .k { color: #6B7C93; white-space: nowrap; }
    .meta-table .v { font-weight: bold; color: #1a2332; }

    table.lines { width: 100%; border-collapse: collapse; }
    table.lines thead th {
        background: #002D5B; color: #fff; padding: 8px 10px; font-size: 7.5px;
        text-transform: uppercase; letter-spacing: 0.6px; text-align: left;
    }
    table.lines thead th.num { text-align: right; }
    table.lines td.num { text-align: right; }
    table.lines tbody td { padding: 8px 10px; border-bottom: 1px solid #e5eaf1; font-size: 9.5px; }
    table.lines tbody tr:nth-child(even) td { background: #f8f9fb; }
    table.lines tfoot td { padding: 6px 10px; font-size: 9px; background: #f1f3f5; border-top: 2px solid #e2e8f0; font-weight: bold; color: #1a2332; }

    /* Zone bas : QR à gauche, totaux à droite */
    .bottom-row { width: 100%; border-collapse: collapse; margin-top: 18px; }
    .bottom-row td { vertical-align: top; }
    .qr-cell   { width: 42%; padding-right: 14px; vertical-align: bottom; }
    .totals-cell { width: 58%; }

    .qr-block {
        border: 1px solid #e2e8f0; border-top: 3px solid {{ $primaryColor }};
        border-radius: 4px; padding: 10px 12px; background: #fafbfc;
    }
    .qr-label { font-size: 7px; font-weight: bold; text-transform: uppercase; color: {{ $primaryColor }}; letter-spacing: 0.8px; margin-bottom: 8px; }
    .qr-inner { display: table; width: 100%; border-collapse: collapse; }
    .qr-img   { display: table-cell; width: 54px; vertical-align: middle; padding-right: 10px; }
    .qr-img img { width: 50px; height: 50px; display: block; border: 1px solid #e2e8f0; }
    .qr-txt   { display: table-cell; vertical-align: middle; }
    .qr-title { font-size: 8px; font-weight: bold; color: #002D5B; margin-bottom: 3px; }
    .qr-sub   { font-size: 7px; color: #6B7C93; line-height: 1.6; }
    .qr-hash  { font-size: 6px; color: #9aa7b8; word-break: break-all; margin-top: 5px; }
    .qr-foot  { font-size: 6.5px; color: #9aa7b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 4px; margin-top: 6px; letter-spacing: 0.3px; }

    table.totals { width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; }
    table.totals td { padding: 7px 12px; font-size: 10px; }
    table.totals .k { color: #6B7C93; }
    table.totals .v { text-align: right; font-weight: bold; }
    table.totals .sep td { border-top: 1px solid #e2e8f0; }
    table.totals .grand td { background: #002D5B; color: #fff; font-size: 13px; font-weight: bold; }
    table.totals .paid td { color: #16a34a; background: #f0fdf4; }
    table.totals .due  td { color: #dc2626; font-weight: bold; background: #fff5f5; }
    table.totals .ech  td { color: #6B7C93; font-size: 8px; border-top: 1px dashed #e2e8f0; background: #fafbfc; }

    .pay-block {
        border: 1px solid #e2e8f0; border-top: 3px solid {{ $primaryColor }};
        border-radius: 4px; padding: 10px 14px; margin-top: 14px; background: #f9fafb;
    }
    .pay-label { font-size: 7.5px; font-weight: bold; text-transform: uppercase; color: {{ $primaryColor }}; letter-spacing: 0.7px; margin-bottom: 8px; }

    .notes-block { margin-top: 14px; font-size: 9px; color: #4a5568; }
    .notes-block .title { font-weight: bold; color: #002D5B; margin-bottom: 4px; }

    /* Zone de signatures */
    .sig-section { margin-top: 28px; }
    .sig-section .sig-heading {
        font-size: 7.5px; text-transform: uppercase; color: #6B7C93;
        letter-spacing: 1px; margin-bottom: 12px;
        border-bottom: 1px solid #e2e8f0; padding-bottom: 5px;
    }
    .sig-row td { width: 50%; vertical-align: top; padding: 0 10px; }
    .sig-row td:first-child { padding-left: 0; border-right: 1px solid #e5eaf1; }
    .sig-row td:last-child  { padding-right: 0; }
    .sig-label { font-size: 8.5px; color: #4a5568; text-align: center; margin-bottom: 8px; font-weight: bold; }
    .sig-box {
        border: 1px solid #c8d3e0; border-radius: 5px;
        height: 110px; background: #fafbfc; position: relative;
    }
    .sig-photo-zone { position: absolute; top: 8px; left: 0; right: 0; min-height: 55px; text-align: center; }
    .sig-photo-zone img { max-height: 50px; max-width: 80%; display: block; margin: 0 auto; }
    .sig-name-line  { border-bottom: 1px dashed #c8d3e0; position: absolute; bottom: 26px; left: 10px; right: 10px; }
    .sig-date-label { font-size: 6.5px; color: #9aa7b8; position: absolute; bottom: 14px; left: 10px; }
    .sig-name-label { font-size: 6.5px; color: #9aa7b8; position: absolute; bottom: 4px; left: 10px; }

    .legal-foot {
        margin-top: 16px; background: #f8f9fb; border: 1px solid #e2e8f0;
        border-radius: 3px; padding: 7px 12px; text-align: center;
    }
    .legal-foot-text { font-size: 7px; color: #9aa7b8; line-height: 1.9; }
</style>
</head>
<body>

@if($watermark)
<div class="watermark">{{ $watermark }}</div>
@endif

<header>
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:55%; vertical-align:middle; padding-bottom:4px;">
                @if($logoBase64)
                <div style="margin-bottom:5px;">
                    <img src="{{ $logoBase64 }}" style="max-height:45px; max-width:110px; display:block; border:1px solid #e5e7eb; border-radius:3px; padding:3px; background:#fff;" alt="logo">
                </div>
                @endif
                <div class="company-name">{{ $company->name }}</div>
                @if(!empty($company->tagline) || (!empty($company->legal_name) && $company->legal_name !== $company->name))
                <div class="company-sub">{{ $company->tagline ?? $company->legal_name }}</div>
                @endif
                <div class="company-meta">
                    @php
                        $parts = array_filter([$company->address ?? null, $company->city ?? null, $company->country ?? null]);
                    @endphp
                    {{ implode(', ', $parts) }}<br>
                    @if($company->phone)Tél : {{ $company->phone }}@if($company->email) &nbsp;·&nbsp; @endif@endif
                    @if($company->email){{ $company->email }}@endif
                    @if($company->tax_id)<br>N° Fiscal : {{ $company->tax_id }}@endif
                    @php
                      $rccm = $company->trade_register ?? $company->rccm ?? null;
                    @endphp
                    @if($rccm) &nbsp;·&nbsp; RCCM : {{ $rccm }}@endif
                    @if(!empty($company->capital)) &nbsp;·&nbsp; Capital : {{ $company->capital }}@endif
                </div>
            </td>
            <td style="width:45%; vertical-align:middle; padding-bottom:4px; text-align:right;">
                <div class="doc-title">{{ $document->type_label }}</div>
                <div class="doc-number">N° {{ $document->number }}</div>
                @if(!empty($document->status_label ?? $document->status))
                <div class="doc-status"><span>{{ $document->status_label ?? strtoupper($document->status ?? '') }}</span></div>
                @endif
            </td>
        </tr>
    </table>
</header>

<footer>
    <table style="width:100%; border-collapse:collapse;">
        <tr>
            <td style="width:70%;">{{ $company->invoice_footer ?? 'Merci de votre confiance.' }}</td>
            <td style="width:30%; text-align:right; font-size:7px; color:#9aa7b8;">
                Document certifié n° {{ $document->number }} &nbsp;·&nbsp; Propulsé par <b style="color:{{ $primaryColor }}">IBIG FactPro</b>
            </td>
        </tr>
    </table>
</footer>

<main>
    <div class="header-accent"></div>

    {{-- Infos document + client --}}
    <table class="addresses">
        <tr>
            <td>
                <div class="info-block">
                    <div class="label">Informations document</div>
                    <table class="meta-table">
                        <tr>
                            <td class="k">Date d'émission</td>
                            <td class="v">{{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}</td>
                        </tr>
                        @if($document->due_date)
                        <tr>
                            <td class="k">@if(($document->type ?? '') === 'quote')Valide jusqu'au@else Date d'échéance@endif</td>
                            <td class="v">{{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}</td>
                        </tr>
                        @endif
                        @if($document->reference)
                        <tr>
                            <td class="k">Référence</td>
                            <td class="v">{{ $document->reference }}</td>
                        </tr>
                        @endif
                        @if(!empty($document->subject ?? $document->object ?? null))
                        <tr>
                            <td class="k">Objet</td>
                            <td class="v">{{ $document->subject ?? $document->object }}</td>
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
                            @if(!empty($document->customer->address)){{ $document->customer->address }}<br>@endif
                            @php
                                $cCity = trim(($document->customer->city ?? '') . ($document->customer->country ? ' · '.$document->customer->country : ''));
                            @endphp
                            @if($cCity){{ $cCity }}<br>@endif
                            @if($document->customer->phone)Tél : {{ $document->customer->phone }}<br>@endif
                            @if($document->customer->email){{ $document->customer->email }}<br>@endif
                            @if(!empty($document->customer->tax_number ?? $document->customer->tax_id ?? null))
                              N° Fiscal : {{ $document->customer->tax_number ?? $document->customer->tax_id }}
                            @endif
                        </div>
                    @else
                        <div class="name" style="color:#aaa;font-style:italic;">— Non renseigné —</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- Tableau des lignes --}}
    @php
      $totalHT = collect($document->lines)->sum(fn($l) => (float)($l->line_total ?? 0));
    @endphp
    <table class="lines">
        <thead>
            <tr>
                <th style="width:40%">Désignation</th>
                <th class="num" style="width:8%">Qté</th>
                <th style="width:8%">Unité</th>
                <th class="num" style="width:13%">P.U. HT</th>
                <th class="num" style="width:8%">Remise</th>
                <th class="num" style="width:7%">TVA</th>
                <th class="num" style="width:16%">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->lines as $line)
            <tr>
                <td>
                    <div style="font-weight:bold;color:#111827;line-height:1.3;">{{ $line->description }}</div>
                    @if(!empty($line->detail))
                    <div style="font-size:7.5px;color:#6b7280;margin-top:2px;">{{ $line->detail }}</div>
                    @endif
                </td>
                <td class="num">{{ number_format((float)$line->quantity, 2, ',', ' ') }}</td>
                <td style="color:#6b7280;font-size:8.5px;">{{ $line->unit }}</td>
                <td class="num">{{ number_format((float)$line->unit_price, 0, ',', ' ') }}</td>
                <td class="num">
                    @if((float)($line->discount_percent ?? 0) > 0)
                        <span style="background:#fef3c7;color:#92400e;padding:1px 5px;border-radius:3px;font-size:8px;">{{ number_format((float)$line->discount_percent, 0) }}%</span>
                    @else
                        <span style="color:#d1d5db;">—</span>
                    @endif
                </td>
                <td class="num">{{ number_format((float)$line->tax_rate, 0) }}%</td>
                <td class="num" style="font-weight:bold;">{{ number_format((float)$line->line_total, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align:right;text-transform:uppercase;letter-spacing:0.5px;font-size:8px;color:#6b7280;">Total Hors Taxes</td>
                <td class="num">{{ number_format($totalHT, 0, ',', ' ') }} {{ $document->currency }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- QR à gauche | Totaux à droite --}}
    <table class="bottom-row">
        <tr>
            <td class="qr-cell">
                @if($qrDataUri)
                <div class="qr-block">
                    <div class="qr-label">Authenticité certifiée</div>
                    <div class="qr-inner">
                        <div class="qr-img"><img src="{{ $qrDataUri }}" alt="QR"></div>
                        <div class="qr-txt">
                            <div class="qr-title">Document certifié</div>
                            <div class="qr-sub">Scannez pour vérifier<br>l'authenticité de ce document.</div>
                            @if(!empty($document->verification_url))
                            <div class="qr-hash">{{ $document->verification_url }}</div>
                            @endif
                        </div>
                    </div>
                    @if(!empty($document->integrity_hash))
                    <div class="qr-hash" style="margin-top:5px;">SHA-256 : {{ substr($document->integrity_hash, 0, 40) }}…</div>
                    @endif
                    <div class="qr-foot">Anti-falsification · Certifié IBIG FactPro</div>
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
                        <td class="k">Remise accordée</td>
                        <td class="v" style="color:#dc2626;">−{{ number_format((float)$document->discount_amount, 0, ',', ' ') }} {{ $document->currency }}</td>
                    </tr>
                    @endif
                    <tr class="sep">
                        @php $taxRate = $document->tax_rate ?? null; @endphp
                        <td class="k">TVA@if($taxRate) ({{ $taxRate }}%)@endif</td>
                        <td class="v">{{ number_format((float)$document->tax_amount, 0, ',', ' ') }} {{ $document->currency }}</td>
                    </tr>
                    <tr class="grand">
                        <td>TOTAL TTC</td>
                        <td class="v">{{ number_format((float)$document->total, 0, ',', ' ') }} {{ $document->currency }}</td>
                    </tr>
                    @if((float)($document->amount_paid ?? 0) > 0)
                    <tr class="paid">
                        <td class="k">Montant réglé</td>
                        <td class="v">{{ number_format((float)$document->amount_paid, 0, ',', ' ') }} {{ $document->currency }}</td>
                    </tr>
                    @php $remaining = max(0, (float)$document->total - (float)$document->amount_paid); @endphp
                    <tr class="{{ $remaining > 0 ? 'due' : 'paid' }}">
                        <td class="k">{{ $remaining > 0 ? 'Reste à payer' : '✓ Soldé' }}</td>
                        <td class="v">{{ number_format($remaining, 0, ',', ' ') }} {{ $document->currency }}</td>
                    </tr>
                    @endif
                    @if($document->due_date && (float)($document->amount_paid ?? 0) <= 0)
                    <tr class="ech">
                        <td class="k">Échéance de paiement</td>
                        <td class="v" style="font-weight:bold;font-size:9px;color:#374151;">{{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- Moyens de paiement --}}
    @include('pdf.engine.blocks._payment-info', ['company' => $company, 'document' => $document, 'primaryColor' => $primaryColor])

    {{-- Notes & Conditions --}}
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

    @php
        $sigShowEmitter = $sigConfig['show_emitter'] ?? true;
        $sigShowClient  = $sigConfig['show_client']  ?? true;
        $sigMode        = $sigConfig['mode']          ?? 'manual';
        $sigMention     = $sigConfig['mention']       ?? null;
        $emitterLabel   = $sigConfig['emitter_label'] ?? 'Signature et cachet de l\'émetteur';
        $clientLabel    = $sigConfig['client_label']  ?? 'Bon pour accord — Signature du client';
        $showSigSection = $sigShowEmitter || $sigShowClient;
    @endphp

    @if($showSigSection)
    <div class="sig-section">
        <div class="sig-heading">Signatures</div>
        <table class="sig-row" style="width:100%;border-collapse:collapse;">
            <tr>
                @if($sigShowEmitter)
                <td @if(!$sigShowClient) style="padding-right:0; border-right:none;" @endif>
                    <div class="sig-label">{{ $emitterLabel }}</div>
                    <div class="sig-box">
                        @if(in_array($sigMode, ['digital','both']) && !empty($sigDigitalBase64))
                        <div class="sig-photo-zone">
                            <img src="{{ $sigDigitalBase64 }}" alt="Signature">
                        </div>
                        @endif
                        @if(!empty($sigStampBase64))
                        <div style="position:absolute; top:8px; right:8px;">
                            <img src="{{ $sigStampBase64 }}" style="height:45px; width:45px; opacity:0.8;" alt="Cachet">
                        </div>
                        @endif
                        <div class="sig-name-line"></div>
                        <div class="sig-date-label">Date :</div>
                        <div class="sig-name-label">Nom et cachet</div>
                    </div>
                </td>
                @endif
                @if($sigShowClient)
                <td @if(!$sigShowEmitter) style="padding-left:0; border-right:none;" @endif>
                    <div class="sig-label">{{ $clientLabel }}</div>
                    <div class="sig-box">
                        <div class="sig-name-line"></div>
                        <div class="sig-date-label">Date :</div>
                        <div class="sig-name-label">Nom et cachet</div>
                    </div>
                </td>
                @endif
            </tr>
        </table>
    </div>
    @endif

    {{-- Pied légal --}}
    <div class="legal-foot">
        <div class="legal-foot-text">
            @if($sigMention)
                {!! nl2br(e($sigMention)) !!}
            @else
                Tout retard de paiement entraîne des pénalités de retard au taux légal en vigueur
                ainsi qu'une indemnité forfaitaire de 40 000 FCFA / 40 € pour frais de recouvrement.<br>
            @endif
            @if(!empty($company->name))<strong>{{ $company->name }}</strong>@endif
            @php $ids2 = array_filter([!empty($company->trade_register ?? $company->rccm ?? null) ? 'RCCM '.($company->trade_register ?? $company->rccm) : null, !empty($company->tax_id ?? $company->tax_number ?? null) ? 'N°Fiscal '.($company->tax_id ?? $company->tax_number) : null]); @endphp
            @if(count($ids2)) &nbsp;·&nbsp; {{ implode(' · ', $ids2) }}@endif
            &nbsp;·&nbsp; Document certifié n° {{ $document->number }}
            &nbsp;·&nbsp; Généré par <strong>IBIG FactPro</strong> — factpro.ibigsoft.com
        </div>
    </div>

</main>
</body>
</html>

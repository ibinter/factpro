<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $document->type_label ?? 'Document' }} {{ $document->number ?? '' }}</title>
    <style>
        @@page {
            margin: 20px 45px 20px 80px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #333333;
            background: #ffffff;
        }

        /* â•â• SIDEBAR LATÃ‰RALE FIXE â•â• */
        #sidebar {
            position: fixed;
            top: 0;
            left: -75px;
            width: 60px;
            height: 100%;
            background: {{ $primaryColor }};
            padding: 16px 0;
            z-index: 999;
        }

        #sidebar .sb-inner {
            display: table;
            width: 100%;
            height: 100%;
        }

        #sidebar .sb-top {
            display: table-row;
            vertical-align: top;
        }

        #sidebar .sb-top-cell {
            display: table-cell;
            text-align: center;
            padding-top: 8px;
            vertical-align: top;
        }

        #sidebar .sb-logo {
            max-width: 32px;
            max-height: 32px;
            display: block;
            margin: 0 auto 10px auto;
        }

        #sidebar .sb-company-name {
            display: block;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-height: 160px;
            margin: 0 auto;
        }

        #sidebar .sb-bottom {
            display: table-row;
            vertical-align: bottom;
        }

        #sidebar .sb-bottom-cell {
            display: table-cell;
            text-align: center;
            padding-bottom: 8px;
            vertical-align: bottom;
        }

        #sidebar .sb-page {
            color: rgba(255,255,255,0.75);
            font-size: 6.5px;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            display: block;
            margin: 0 auto;
        }

        /* â•â• WATERMARK â•â• */
        .watermark {
            position: fixed;
            top: 40%;
            left: 5%;
            width: 90%;
            transform: rotate(-35deg);
            font-size: 42px;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.18);
            z-index: 1000;
            text-align: center;
            pointer-events: none;
        }

        /* â•â• EN-TÃŠTE DU DOCUMENT â•â• */
        .doc-title-row {
            display: table;
            width: 100%;
            margin-bottom: 14px;
            border-bottom: 2px solid {{ $primaryColor }};
            padding-bottom: 8px;
        }

        .doc-title-left {
            display: table-cell;
            vertical-align: bottom;
            width: 60%;
        }

        .doc-title-right {
            display: table-cell;
            vertical-align: bottom;
            text-align: right;
            width: 40%;
        }

        .doc-type-label {
            font-size: 20px;
            font-weight: bold;
            color: {{ $primaryColor }};
            text-transform: uppercase;
            line-height: 1.2;
        }

        .doc-number-label {
            font-size: 9px;
            color: #888888;
            margin-top: 2px;
        }

        .doc-status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            @php
                $statusColors = [
                    'paid'      => ['bg' => '#D1FAE5', 'color' => '#065F46'],
                    'draft'     => ['bg' => '#F3F4F6', 'color' => '#6B7280'],
                    'sent'      => ['bg' => '#DBEAFE', 'color' => '#1E40AF'],
                    'overdue'   => ['bg' => '#FEE2E2', 'color' => '#991B1B'],
                    'cancelled' => ['bg' => '#FEF3C7', 'color' => '#92400E'],
                    'partial'   => ['bg' => '#EDE9FE', 'color' => '#5B21B6'],
                ];
                $sc = $statusColors[$document->status ?? ''] ?? ['bg' => '#F3F4F6', 'color' => '#374151'];
            @endphp
            background: {{ $sc['bg'] }};
            color: {{ $sc['color'] }};
            border: 1px solid {{ $sc['color'] }}33;
        }

        /* â•â• ROW MÃ‰TA (3 CARDS) â•â• */
        .meta-row {
            display: table;
            width: 100%;
            margin-bottom: 14px;
        }

        .meta-card {
            display: table-cell;
            border: 1px solid #E5EAF1;
            background: #ffffff;
            padding: 7px 10px;
            vertical-align: top;
        }

        .meta-card + .meta-card {
            border-left: none;
        }

        .meta-card-title {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #888888;
            margin-bottom: 3px;
        }

        .meta-card-value {
            font-size: 9px;
            font-weight: bold;
            color: #1a2332;
        }

        /* â•â• ADRESSES â•â• */
        .addresses {
            display: table;
            width: 100%;
            margin-bottom: 14px;
        }

        .col-doc-info {
            display: table-cell;
            width: 42%;
            vertical-align: top;
            padding-right: 10px;
        }

        .col-client {
            display: table-cell;
            width: 58%;
            vertical-align: top;
            padding-left: 10px;
        }

        .addr-card {
            background: #F8F9FA;
            border-radius: 3px;
            padding: 9px 11px;
            height: 100%;
        }

        .addr-card-client {
            background: #ffffff;
            border-left: 3px solid {{ $primaryColor }};
            border-radius: 0 3px 3px 0;
            padding: 9px 11px;
        }

        .addr-card-title {
            font-size: 7.5px;
            font-weight: bold;
            color: {{ $primaryColor }};
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
        }

        .addr-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
        }

        .addr-label {
            display: table-cell;
            font-size: 8px;
            color: #888888;
            width: 40%;
        }

        .addr-value {
            display: table-cell;
            font-size: 8.5px;
            color: #222222;
            font-weight: bold;
        }

        .client-name {
            font-size: 11px;
            font-weight: bold;
            color: #002D5B;
            margin-bottom: 4px;
        }

        .client-detail {
            font-size: 8.5px;
            color: #555555;
            line-height: 1.6;
        }

        /* â•â• OBJET â•â• */
        .subject-bar {
            background: #EFF6FF;
            border-left: 3px solid {{ $primaryColor }};
            padding: 5px 10px;
            margin-bottom: 12px;
            font-size: 8.5px;
            color: #1e3a5f;
        }

        .subject-bar span {
            font-weight: bold;
        }

        /* â•â• TABLEAU LIGNES â•â• */
        table.lines-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        table.lines-table thead tr {
            background: {{ $primaryColor }}1F;
            border-bottom: 2px solid {{ $primaryColor }};
        }

        table.lines-table thead th {
            padding: 6px 7px;
            font-size: 8px;
            font-weight: bold;
            color: {{ $primaryColor }};
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        table.lines-table thead th.text-right {
            text-align: right;
        }

        table.lines-table tbody tr.row-even {
            background: #F8F9FA;
        }

        table.lines-table tbody tr.row-odd {
            background: #ffffff;
        }

        table.lines-table tbody td {
            padding: 5px 7px;
            font-size: 8.5px;
            color: #333333;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        table.lines-table tbody td.text-right {
            text-align: right;
        }

        table.lines-table tbody td.text-center {
            text-align: center;
        }

        table.lines-table tbody td.line-desc {
            color: #555555;
            font-size: 8px;
            font-style: italic;
        }

        table.lines-table tfoot tr {
            background: #F0F4FA;
        }

        table.lines-table tfoot td {
            padding: 5px 7px;
            font-size: 8.5px;
            font-weight: bold;
            color: #002D5B;
        }

        table.lines-table tfoot td.text-right {
            text-align: right;
        }

        .discount-badge {
            display: inline-block;
            background: #FEF3C7;
            color: #92400E;
            font-size: 7px;
            padding: 1px 4px;
            border-radius: 2px;
            margin-left: 3px;
        }

        /* â•â• QR + TOTAUX â•â• */
        .bottom-section {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }

        .bottom-left {
            display: table-cell;
            width: 35%;
            vertical-align: top;
            padding-right: 10px;
        }

        .bottom-right {
            display: table-cell;
            width: 65%;
            vertical-align: top;
        }

        .qr-block {
            text-align: center;
        }

        .qr-block img {
            width: 80px;
            height: 80px;
            border: 1px solid #e5e7eb;
            padding: 3px;
        }

        .qr-label {
            font-size: 7px;
            color: #888888;
            margin-top: 3px;
            text-align: center;
        }

        table.totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.totals-table tr td {
            padding: 4px 8px;
            font-size: 8.5px;
            border-bottom: 1px solid #e5e7eb;
        }

        table.totals-table tr td:first-child {
            color: #555555;
            width: 55%;
        }

        table.totals-table tr td:last-child {
            text-align: right;
            font-weight: bold;
            color: #222222;
        }

        table.totals-table tr.total-grand td {
            background: {{ $primaryColor }};
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            border-bottom: none;
        }

        table.totals-table tr.total-paid td {
            background: #D1FAE5;
            color: #065F46;
            font-size: 9px;
            font-weight: bold;
            border-bottom: none;
        }

        table.totals-table tr.total-due td {
            background: {{ $accentColor }};
            color: #ffffff;
            font-size: 10.5px;
            font-weight: bold;
            border-bottom: none;
        }

        /* â•â• NOTES / CONDITIONS â•â• */
        .notes-section {
            margin-bottom: 10px;
        }

        .notes-title {
            font-size: 8px;
            font-weight: bold;
            color: {{ $primaryColor }};
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }

        .notes-content {
            font-size: 8px;
            color: #555555;
            line-height: 1.6;
            background: #F8F9FA;
            border-left: 2px solid #e5e7eb;
            padding: 5px 8px;
            border-radius: 0 2px 2px 0;
        }

        /* â•â• SIGNATURES â•â• */
        .sig-section {
            margin-top: 16px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            margin-bottom: 14px;
        }

        .sig-section-title {
            font-size: 8px;
            font-weight: bold;
            color: {{ $primaryColor }};
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 8px;
        }

        .sig-columns {
            display: table;
            width: 100%;
        }

        .sig-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 8px;
        }

        .sig-box:first-child {
            padding-left: 0;
            border-right: 1px solid #e5e7eb;
            padding-right: 16px;
        }

        .sig-box:last-child {
            padding-right: 0;
            padding-left: 16px;
        }

        .sig-box-label {
            font-size: 8px;
            font-weight: bold;
            color: #002D5B;
            margin-bottom: 5px;
        }

        .sig-photo-zone {
            height: 70px;
            border: 1px dashed #d1d5db;
            border-radius: 3px;
            background: #F8F9FA;
            display: block;
            text-align: center;
            margin-bottom: 5px;
            overflow: hidden;
        }

        .sig-photo-zone img {
            max-height: 68px;
            max-width: 100%;
        }

        .sig-photo-placeholder {
            height: 70px;
            line-height: 70px;
            font-size: 7.5px;
            color: #aaaaaa;
            text-align: center;
        }

        .sig-line {
            border-bottom: 1px solid #333333;
            margin-bottom: 3px;
        }

        .sig-meta {
            font-size: 7.5px;
            color: #888888;
        }

        .sig-mention {
            font-size: 7.5px;
            color: #555555;
            font-style: italic;
            margin-top: 3px;
        }

        /* â•â• PIED DE PAGE â•â• */
        .doc-footer {
            margin-top: 14px;
            border-top: 2px solid {{ $accentColor }};
            padding-top: 6px;
        }

        .doc-footer-inner {
            display: table;
            width: 100%;
        }

        .doc-footer-left {
            display: table-cell;
            vertical-align: middle;
            font-size: 7.5px;
            color: #888888;
            width: 60%;
        }

        .doc-footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-size: 7.5px;
            color: #888888;
            width: 40%;
        }

        .doc-footer-right strong {
            color: {{ $primaryColor }};
        }

        /* â•â• UTILITAIRES â•â• */
        main { display: block; }
        .mt-8  { margin-top: 8px; }
        .mb-8  { margin-bottom: 8px; }
        .mb-12 { margin-bottom: 12px; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>

{{-- â•â• SIDEBAR LATÃ‰RALE FIXE â•â• --}}
<div id="sidebar">
    <div class="sb-inner">
        <div class="sb-top">
            <div class="sb-top-cell">
                @if($logoBase64 ?? null)
                    <img src="{{ $logoBase64 }}" class="sb-logo" alt="Logo"/>
                @endif
                <span class="sb-company-name">{{ $company->name ?? '' }}</span>
            </div>
        </div>
        <div class="sb-bottom">
            <div class="sb-bottom-cell">
                <span class="sb-page">Page <span class="pagenum"></span></span>
            </div>
        </div>
    </div>
</div>

{{-- â•â• WATERMARK â•â• --}}
@if($watermark ?? false)
<div class="watermark">{{ $watermark }}</div>
@endif

{{-- â•â• CONTENU PRINCIPAL â•â• --}}
<div id="main-content">

    {{-- â”€â”€ EN-TÃŠTE DU DOCUMENT â”€â”€ --}}
    <div class="doc-title-row">
        <div class="doc-title-left">
            <div class="doc-type-label">{{ $document->type_label ?? 'Document' }}</div>
            <div class="doc-number-label">NÂ° {{ $document->number ?? 'â€”' }}</div>
        </div>
        <div class="doc-title-right">
            <span class="doc-status-badge">{{ $document->status_label ?? '' }}</span>
        </div>
    </div>

    {{-- â”€â”€ ROW MÃ‰TA : 3 CARDS CÃ”TE Ã€ CÃ”TE â”€â”€ --}}
    <div class="meta-row">
        <div class="meta-card">
            <div class="meta-card-title">Date d'Ã©mission</div>
            <div class="meta-card-value">
                {{ $document->issue_date ? \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') : 'â€”' }}
            </div>
        </div>
        <div class="meta-card">
            <div class="meta-card-title">Date d'Ã©chÃ©ance</div>
            <div class="meta-card-value">
                {{ !empty($document->due_date) ? \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') : 'â€”' }}
            </div>
        </div>
        <div class="meta-card">
            <div class="meta-card-title">Devise</div>
            <div class="meta-card-value">{{ $document->currency ?? 'XOF' }}</div>
        </div>
    </div>

    {{-- â”€â”€ ADRESSES â”€â”€ --}}
    <div class="addresses">
        <div class="col-doc-info">
            <div class="addr-card">
                <div class="addr-card-title">Ã‰metteur</div>
                <div style="font-size:10px; font-weight:bold; color:#002D5B; margin-bottom:4px;">
                    {{ $company->name ?? '' }}
                </div>
                <div style="font-size:8.5px; color:#555555; line-height:1.6;">
                    @if(!empty($company->address)){{ $company->address }}<br/>@endif
                    @if(!empty($company->city)){{ $company->city }}@if(!empty($company->country)), {{ $company->country }}@endif<br/>@endif
                    @if(!empty($company->phone))TÃ©l : {{ $company->phone }}<br/>@endif
                    @if(!empty($company->email)){{ $company->email }}<br/>@endif
                    @if(!empty($company->tax_id))NÂ° Fiscal : {{ $company->tax_id }}<br/>@endif
                    @if(!empty($company->rccm))RCCM : {{ $company->rccm }}@endif
                </div>
                @if(!empty($document->reference))
                <div class="addr-row" style="margin-top:6px;">
                    <span class="addr-label">RÃ©fÃ©rence</span>
                    <span class="addr-value">{{ $document->reference }}</span>
                </div>
                @endif
            </div>
        </div>
        <div class="col-client">
            <div class="addr-card-client">
                <div class="addr-card-title">Destinataire</div>
                <div class="client-name">{{ $document->customer->name ?? 'â€”' }}</div>
                <div class="client-detail">
                    @if(!empty($document->customer->address)){{ $document->customer->address }}<br/>@endif
                    @if(!empty($document->customer->city)){{ $document->customer->city }}@if(!empty($document->customer->country)), {{ $document->customer->country }}@endif<br/>@endif
                    @if(!empty($document->customer->phone))TÃ©l : {{ $document->customer->phone }}<br/>@endif
                    @if(!empty($document->customer->email)){{ $document->customer->email }}<br/>@endif
                    @if(!empty($document->customer->tax_number))NÂ° Fiscal : {{ $document->customer->tax_number }}@endif
                </div>
            </div>
        </div>
    </div>

    {{-- â”€â”€ OBJET â”€â”€ --}}
    @if(!empty($document->subject))
    <div class="subject-bar">
        <span>Objet :</span> {{ $document->subject }}
    </div>
    @endif

    {{-- â”€â”€ TABLEAU DES LIGNES â”€â”€ --}}
    @php
        $totalHT = collect($document->lines)->sum(fn($l) => (float)($l->line_total ?? 0));
    @endphp
    <table class="lines-table">
        <thead>
            <tr>
                <th style="width:4%;">#</th>
                <th style="width:38%;">DÃ©signation</th>
                <th class="text-right" style="width:10%;">QtÃ©</th>
                <th class="text-right" style="width:13%;">P.U. HT</th>
                <th class="text-right" style="width:10%;">Remise</th>
                <th class="text-right" style="width:13%;">TVA</th>
                <th class="text-right" style="width:12%;">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->lines as $index => $line)
            <tr class="{{ $index % 2 === 0 ? 'row-even' : 'row-odd' }}">
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $line->description ?? $line->name ?? 'â€”' }}</strong>
                    @if(!empty($line->detail))
                        <br/><span class="line-desc">{{ $line->detail }}</span>
                    @endif
                </td>
                <td class="text-right">
                    {{ number_format((float)($line->quantity ?? 1), 2) }}
                    @if(!empty($line->unit))<br/><span style="font-size:7px;color:#aaa;">{{ $line->unit }}</span>@endif
                </td>
                <td class="text-right">{{ number_format((float)($line->unit_price ?? 0), 0, ',', ' ') }}</td>
                <td class="text-right">
                    @if((float)($line->discount_percent ?? 0) > 0)
                        <span class="discount-badge">{{ number_format((float)$line->discount_percent, 1) }}%</span>
                    @else
                        â€”
                    @endif
                </td>
                <td class="text-right">
                    @if(!empty($line->tax_rate))
                        {{ number_format((float)$line->tax_rate, 0) }}%
                    @else
                        â€”
                    @endif
                </td>
                <td class="text-right">{{ number_format((float)($line->line_total ?? 0), 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right">Total HT</td>
                <td class="text-right">{{ number_format($totalHT, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- â”€â”€ QR + TOTAUX â”€â”€ --}}
    <div class="bottom-section">
        <div class="bottom-left">
            @if($qrDataUri ?? null)
            <div class="qr-block">
                <img src="{{ $qrDataUri }}" alt="QR Code"/>
                <div class="qr-label">Scanner pour vÃ©rifier</div>
            </div>
            @endif
        </div>
        <div class="bottom-right">
            <table class="totals-table">
                <tr>
                    <td>Sous-total HT</td>
                    <td>{{ number_format((float)($document->subtotal ?? 0), 0, ',', ' ') }} {{ $document->currency ?? 'XOF' }}</td>
                </tr>
                @if((float)($document->discount_amount ?? 0) > 0)
                <tr>
                    <td>Remise globale</td>
                    <td>- {{ number_format((float)$document->discount_amount, 0, ',', ' ') }} {{ $document->currency ?? 'XOF' }}</td>
                </tr>
                @endif
                @if((float)($document->tax_amount ?? 0) > 0)
                <tr>
                    <td>TVA ({{ number_format((float)($document->tax_rate ?? 0), 0) }}%)</td>
                    <td>{{ number_format((float)$document->tax_amount, 0, ',', ' ') }} {{ $document->currency ?? 'XOF' }}</td>
                </tr>
                @endif
                <tr class="total-grand">
                    <td>TOTAL TTC</td>
                    <td>{{ number_format((float)($document->total ?? 0), 0, ',', ' ') }} {{ $document->currency ?? 'XOF' }}</td>
                </tr>
                @if((float)($document->amount_paid ?? 0) > 0)
                <tr class="total-paid">
                    <td>Montant payÃ©</td>
                    <td>- {{ number_format((float)$document->amount_paid, 0, ',', ' ') }} {{ $document->currency ?? 'XOF' }}</td>
                </tr>
                @php $amountDue = (float)($document->total ?? 0) - (float)($document->amount_paid ?? 0); @endphp
                @if($amountDue > 0)
                <tr class="total-due">
                    <td>RESTE Ã€ PAYER</td>
                    <td>{{ number_format($amountDue, 0, ',', ' ') }} {{ $document->currency ?? 'XOF' }}</td>
                </tr>
                @endif
                @endif
            </table>
        </div>
    </div>

    {{-- â”€â”€ INFORMATIONS DE PAIEMENT â”€â”€ --}}
    @include('pdf.engine.blocks._payment-info', [
        'company'      => $company,
        'document'     => $document,
        'primaryColor' => $primaryColor,
    ])

    {{-- â”€â”€ NOTES â”€â”€ --}}
    @if(!empty($document->notes))
    <div class="notes-section mb-12">
        <div class="notes-title">Notes</div>
        <div class="notes-content">{{ $document->notes }}</div>
    </div>
    @endif

    {{-- â”€â”€ CONDITIONS â”€â”€ --}}
    @if(!empty($document->terms))
    <div class="notes-section mb-12">
        <div class="notes-title">Conditions gÃ©nÃ©rales</div>
        <div class="notes-content">{{ $document->terms }}</div>
    </div>
    @endif

    {{-- â”€â”€ SIGNATURES â”€â”€ --}}
    @php
        $sigShowEmitter  = $sigConfig['show_emitter'] ?? true;
        $sigShowClient   = $sigConfig['show_client']  ?? true;
        $showSigSection  = $sigShowEmitter || $sigShowClient;
        $sigMode         = $sigConfig['mode']          ?? 'photo';
        $sigMention      = $sigConfig['mention']       ?? 'Lu et approuvÃ©';
        $sigEmitterLabel = $sigConfig['emitter_label'] ?? 'Ã‰metteur';
        $sigClientLabel  = $sigConfig['client_label']  ?? 'Client';
    @endphp

    @if($showSigSection)
    <div class="sig-section">
        <div class="sig-section-title">Signatures</div>
        <div class="sig-columns">

            @if($sigShowEmitter)
            <div class="sig-box">
                <div class="sig-box-label">{{ $sigEmitterLabel }}</div>
                <div class="sig-photo-zone">
                    @if($sigDigitalBase64 ?? null)
                        <img src="{{ $sigDigitalBase64 }}" alt="Signature Ã©metteur"/>
                    @elseif($sigStampBase64 ?? null)
                        <img src="{{ $sigStampBase64 }}" alt="Cachet"/>
                    @else
                        <div class="sig-photo-placeholder">Signature / Cachet</div>
                    @endif
                </div>
                @if($sigMode === 'mention')
                <div class="sig-mention">{{ $sigMention }}</div>
                @endif
                <div class="sig-line"></div>
                <div class="sig-meta">
                    Nom : ___________________________<br/>
                    Date : __________________________
                </div>
            </div>
            @endif

            @if($sigShowClient)
            <div class="sig-box">
                <div class="sig-box-label">{{ $sigClientLabel }}</div>
                <div class="sig-photo-zone">
                    <div class="sig-photo-placeholder">Signature / Cachet</div>
                </div>
                @if($sigMode === 'mention')
                <div class="sig-mention">{{ $sigMention }}</div>
                @endif
                <div class="sig-line"></div>
                <div class="sig-meta">
                    Nom : ___________________________<br/>
                    Date : __________________________
                </div>
            </div>
            @endif

        </div>
    </div>
    @endif

    {{-- â”€â”€ PIED DE PAGE â”€â”€ --}}
    <div class="doc-footer">
        <div class="doc-footer-inner">
            <div class="doc-footer-left">
                @if(!empty($company->invoice_footer))
                    {{ $company->invoice_footer }}
                @else
                    {{ $company->legal_name ?? $company->name ?? '' }}
                    @if(!empty($company->capital)) â€” Capital : {{ $company->capital }}@endif
                @endif
            </div>
            <div class="doc-footer-right">
                @if(!empty($company->rccm))RCCM : {{ $company->rccm }} â€” @endif
                @if(!empty($company->trade_register))RC : {{ $company->trade_register }} â€” @endif
                <strong>IBIG FactPro</strong>
            </div>
        </div>
    </div>

</div>
</body>
</html>

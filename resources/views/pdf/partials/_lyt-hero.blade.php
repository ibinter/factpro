{{-- ============================================================
     _lyt-hero.blade.php — Layout PDF Hero Bold (DomPDF)
     Grand bandeau héro couleur pleine + cercles décoratifs
     ============================================================ --}}
@php
    $totalHT = collect($document->lines)->sum(fn($l) => (float)($l->line_total ?? 0));

    $sigShowEmitter = $sigConfig['show_emitter'] ?? true;
    $sigShowClient  = $sigConfig['show_client']  ?? true;
    $showSigSection = $sigShowEmitter || $sigShowClient;

    $primary = $primaryColor ?? '#1B4FA8';
    $accent  = $accentColor  ?? '#F0C040';

    $hasWatermark     = !empty($watermark);
    $hasLogo          = !empty($logoBase64);
    $hasQr            = !empty($qrDataUri);
    $hasSigDigital    = !empty($sigDigitalBase64);
    $hasSigStamp      = !empty($sigStampBase64);

    $customer = $document->customer ?? null;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
/* ── Reset & page ─────────────────────────────────────────── */
* { margin:0; padding:0; box-sizing:border-box; }

@@page {
    margin: 165px 45px 30px 45px;
}

body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 8px;
    color: #2c2c2c;
    line-height: 1.45;
}

/* ── Watermark ────────────────────────────────────────────── */
.watermark {
    position: fixed;
    top: 38%;
    left: 50%;
    transform: translateX(-50%) translateY(-50%) rotate(-35deg);
    font-size: 72px;
    font-weight: 900;
    color: rgba(0,0,0,0.045);
    letter-spacing: 6px;
    text-transform: uppercase;
    z-index: 0;
    white-space: nowrap;
}

/* ── Header hero fixé ────────────────────────────────────── */
.hero-header {
    position: fixed;
    top: -150px;
    left: 0;
    right: 0;
    height: 135px;
    background-color: {{ $primary }};
    overflow: hidden;
}

/* Cercles décoratifs */
.hero-circle-1 {
    position: absolute;
    right: -30px;
    top: -30px;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background-color: rgba(255,255,255,0.07);
}
.hero-circle-2 {
    position: absolute;
    right: 60px;
    top: 50px;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background-color: rgba(255,255,255,0.04);
}
.hero-circle-3 {
    position: absolute;
    left: -20px;
    bottom: -20px;
    width: 75px;
    height: 75px;
    border-radius: 50%;
    background-color: rgba(255,255,255,0.05);
}

/* Contenu interne du hero */
.hero-inner {
    position: relative;
    z-index: 2;
    padding: 15px 22px 12px 22px;
    height: 100%;
}

/* Ligne supérieure : logo + infos doc */
.hero-top {
    width: 100%;
    display: table;
    margin-bottom: 10px;
}
.hero-top-left {
    display: table-cell;
    vertical-align: middle;
    width: 60px;
}
.hero-top-center {
    display: table-cell;
    vertical-align: middle;
    padding-left: 14px;
}
.hero-top-right {
    display: table-cell;
    vertical-align: middle;
    text-align: right;
    width: 120px;
}

/* Logo */
.hero-logo-wrap {
    display: inline-block;
    background-color: rgba(255,255,255,0.15);
    border-radius: 5px;
    padding: 4px 6px;
    line-height: 0;
}
.hero-logo-wrap img {
    max-height: 40px;
    max-width: 52px;
}
.hero-company-name {
    font-size: 8px;
    font-weight: 700;
    color: rgba(255,255,255,0.90);
    letter-spacing: 0.3px;
}

/* Badge type document */
.hero-doc-label {
    font-size: 6px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: rgba(255,255,255,0.55);
    margin-bottom: 3px;
}
.hero-doc-type {
    font-size: 24px;
    font-weight: 900;
    color: #ffffff;
    letter-spacing: -1px;
    line-height: 1;
}
.hero-doc-number {
    font-size: 7px;
    color: rgba(255,255,255,0.45);
    margin-top: 2px;
}

/* Status badge */
.hero-status-badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 6px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    background-color: rgba(255,255,255,0.18);
    color: #ffffff;
    margin-top: 4px;
}

/* Ligne meta : émetteur / destinataire / dates */
.hero-meta {
    display: table;
    width: 100%;
    margin-top: 8px;
    border-top: 1px solid rgba(255,255,255,0.12);
    padding-top: 8px;
}
.hero-meta-cell {
    display: table-cell;
    vertical-align: top;
    padding-right: 14px;
}
.hero-meta-cell:last-child { padding-right: 0; text-align: right; }
.hero-meta-label {
    font-size: 4.5px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: rgba(255,255,255,0.40);
    margin-bottom: 2px;
}
.hero-meta-value {
    font-size: 6px;
    font-weight: 700;
    color: #ffffff;
}

/* ── Filet accent ─────────────────────────────────────────── */
.hero-accent-bar {
    height: 3px;
    background-color: {{ $accent }};
    margin-bottom: 0;
}

/* ── Corps principal ──────────────────────────────────────── */
.main-content {
    margin-top: 10px;
}

/* Référence / objet */
.doc-ref-row {
    display: table;
    width: 100%;
    margin-bottom: 10px;
}
.doc-ref-cell {
    display: table-cell;
    vertical-align: middle;
}
.doc-ref-cell:last-child { text-align: right; }
.ref-label {
    font-size: 6px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.7px;
}
.ref-value {
    font-size: 8px;
    font-weight: 700;
    color: #333;
}

/* Adresses en 2 colonnes */
.addr-table {
    display: table;
    width: 100%;
    margin-bottom: 14px;
}
.addr-col {
    display: table-cell;
    vertical-align: top;
    width: 50%;
    padding: 9px 11px;
    background-color: #f7f8fa;
}
.addr-col:first-child {
    border-right: 3px solid #ffffff;
}
.addr-col-label {
    font-size: 5.5px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #aaa;
    margin-bottom: 4px;
}
.addr-name {
    font-size: 9px;
    font-weight: 700;
    color: #222;
    margin-bottom: 2px;
}
.addr-line {
    font-size: 7px;
    color: #555;
    line-height: 1.55;
}
.addr-meta {
    font-size: 6.5px;
    color: #777;
    margin-top: 3px;
}

/* Objet / référence externe */
.subject-bar {
    background-color: #f0f2f5;
    border-left: 3px solid {{ $primary }};
    padding: 5px 10px;
    margin-bottom: 12px;
    font-size: 7.5px;
    color: #333;
}
.subject-bar span {
    font-weight: 700;
}

/* ── Tableau des lignes ───────────────────────────────────── */
.lines-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 4px;
    font-size: 7.5px;
}
.lines-table thead tr {
    background-color: {{ $primary }};
    color: #ffffff;
}
.lines-table thead th {
    padding: 6px 7px;
    font-size: 6.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.lines-table thead th.text-right { text-align: right; }
.lines-table thead th.text-center { text-align: center; }

.lines-table tbody tr { border-bottom: 1px solid #ebebeb; }
.lines-table tbody tr:nth-child(even) { background-color: #fafbfc; }
.lines-table tbody td {
    padding: 5px 7px;
    vertical-align: top;
    color: #333;
}
.lines-table tbody td.text-right { text-align: right; }
.lines-table tbody td.text-center { text-align: center; }

.line-desc { font-weight: 600; color: #222; }
.line-detail { font-size: 6.5px; color: #777; margin-top: 1px; }

/* ── Bloc totaux ──────────────────────────────────────────── */
.totals-wrap {
    display: table;
    width: 100%;
    margin-top: 8px;
    margin-bottom: 14px;
}
.totals-spacer { display: table-cell; width: 55%; }
.totals-block  { display: table-cell; width: 45%; vertical-align: top; }

.totals-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 7.5px;
}
.totals-table tr td {
    padding: 4px 8px;
    border-bottom: 1px solid #eee;
}
.totals-table tr td:last-child { text-align: right; font-weight: 600; }
.totals-table .total-grand td {
    background-color: {{ $primary }};
    color: #ffffff;
    font-size: 9px;
    font-weight: 900;
    border-bottom: none;
    padding: 7px 8px;
}
.totals-table .total-paid td {
    color: #2e7d32;
    font-weight: 700;
}
.totals-table .total-due td {
    background-color: #fff3cd;
    font-weight: 800;
    font-size: 8.5px;
    border-bottom: none;
}

/* ── Montant en lettres ───────────────────────────────────── */
.amount-words {
    background-color: #f7f8fa;
    border: 1px solid #e8e8e8;
    padding: 5px 10px;
    margin-bottom: 12px;
    font-size: 7px;
    color: #555;
}
.amount-words strong { color: #222; }

/* ── Section signatures ───────────────────────────────────── */
.sig-section {
    display: table;
    width: 100%;
    margin-bottom: 14px;
    margin-top: 10px;
}
.sig-col {
    display: table-cell;
    width: 50%;
    vertical-align: top;
    padding: 8px 10px;
    border: 1px solid #e8e8e8;
}
.sig-col:first-child { border-right: none; }
.sig-col-label {
    font-size: 5.5px;
    text-transform: uppercase;
    letter-spacing: 0.9px;
    color: #aaa;
    margin-bottom: 6px;
}
.sig-col-name {
    font-size: 7.5px;
    font-weight: 700;
    color: #333;
    margin-bottom: 4px;
}
.sig-img-wrap {
    height: 42px;
    line-height: 42px;
    text-align: center;
}
.sig-img-wrap img { max-height: 40px; max-width: 100%; }
.sig-line {
    border-top: 1px solid #ccc;
    margin-top: 8px;
    padding-top: 3px;
    font-size: 6px;
    color: #aaa;
    text-align: center;
}

/* ── Notes / conditions ───────────────────────────────────── */
.notes-terms-wrap {
    display: table;
    width: 100%;
    margin-bottom: 12px;
}
.notes-col {
    display: table-cell;
    vertical-align: top;
    width: 50%;
    padding-right: 10px;
}
.notes-col:last-child { padding-right: 0; padding-left: 10px; }
.notes-label {
    font-size: 6px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #aaa;
    border-bottom: 1px solid #e5e5e5;
    padding-bottom: 3px;
    margin-bottom: 5px;
}
.notes-body {
    font-size: 7px;
    color: #555;
    line-height: 1.5;
}

/* ── QR & hash ────────────────────────────────────────────── */
.verify-row {
    display: table;
    width: 100%;
    margin-bottom: 10px;
}
.verify-qr { display: table-cell; vertical-align: middle; width: 55px; }
.verify-qr img { width: 48px; height: 48px; }
.verify-info {
    display: table-cell;
    vertical-align: middle;
    padding-left: 8px;
}
.verify-url { font-size: 6.5px; color: #555; word-break: break-all; }
.verify-hash {
    font-size: 5.5px;
    color: #aaa;
    word-break: break-all;
    margin-top: 2px;
    font-family: monospace;
}

/* ── Pied de page (flux normal) ───────────────────────────── */
.doc-footer {
    border-top: 2px solid {{ $accent }};
    padding-top: 6px;
    margin-top: 10px;
    font-size: 6px;
    color: #888;
    text-align: center;
    line-height: 1.6;
}

/* ── Utilitaires ──────────────────────────────────────────── */
.text-right  { text-align: right; }
.text-center { text-align: center; }
.fw-700 { font-weight: 700; }
.mt-6  { margin-top: 6px; }
.mb-6  { margin-bottom: 6px; }
.page-break { page-break-after: always; }
</style>
</head>
<body>

{{-- ── Watermark ──────────────────────────────────────────── --}}
@if($hasWatermark)
<div class="watermark">{{ $watermark }}</div>
@endif

{{-- ── Header hero fixé ──────────────────────────────────── --}}
<div class="hero-header">
    {{-- Cercles décoratifs (DomPDF ne supporte pas ::before/::after) --}}
    <div class="hero-circle-1"></div>
    <div class="hero-circle-2"></div>
    <div class="hero-circle-3"></div>

    <div class="hero-inner">
        {{-- Ligne supérieure : logo | type doc | numéro + statut --}}
        <div class="hero-top">
            <div class="hero-top-left">
                @if($hasLogo)
                <div class="hero-logo-wrap">
                    <img src="{{ $logoBase64 }}" alt="logo">
                </div>
                @else
                <div class="hero-company-name">{{ $company->name ?? '' }}</div>
                @endif
            </div>
            <div class="hero-top-center">
                @if($hasLogo)
                <div class="hero-company-name">{{ $company->name ?? '' }}</div>
                @endif
            </div>
            <div class="hero-top-right">
                <div class="hero-doc-label">{{ $document->type_label ?? 'Document' }}</div>
                <div class="hero-doc-type">{{ strtoupper($document->type_label ?? 'DOC') }}</div>
                <div class="hero-doc-number">{{ $document->number ?? '' }}</div>
                @if(!empty($document->status_label))
                <div class="hero-status-badge">{{ $document->status_label }}</div>
                @endif
            </div>
        </div>

        {{-- Ligne meta : émetteur / destinataire / date / échéance --}}
        <div class="hero-meta">
            <div class="hero-meta-cell">
                <div class="hero-meta-label">Émetteur</div>
                <div class="hero-meta-value">{{ $company->name ?? '—' }}</div>
            </div>
            <div class="hero-meta-cell">
                <div class="hero-meta-label">Destinataire</div>
                <div class="hero-meta-value">{{ $customer->name ?? '—' }}</div>
            </div>
            <div class="hero-meta-cell">
                <div class="hero-meta-label">Date d'émission</div>
                <div class="hero-meta-value">{{ $document->issue_date ?? '—' }}</div>
            </div>
            @if(!empty($document->due_date))
            <div class="hero-meta-cell">
                <div class="hero-meta-label">Échéance</div>
                <div class="hero-meta-value">{{ $document->due_date }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Filet accent (dans le flux, juste sous le header) ─── --}}
<div class="hero-accent-bar"></div>

{{-- ══════════════════════════════════════════════════════════
     CORPS DU DOCUMENT
     ══════════════════════════════════════════════════════════ --}}
<div class="main-content">

    {{-- Référence externe / objet --}}
    @if(!empty($document->reference) || !empty($document->subject))
    <div class="doc-ref-row">
        @if(!empty($document->reference))
        <div class="doc-ref-cell">
            <div class="ref-label">Référence</div>
            <div class="ref-value">{{ $document->reference }}</div>
        </div>
        @endif
        @if(!empty($document->subject))
        <div class="doc-ref-cell">
            <div class="ref-label">Objet</div>
            <div class="ref-value">{{ $document->subject }}</div>
        </div>
        @endif
    </div>
    @endif

    {{-- Bloc objet seul si pas de référence --}}
    @if(empty($document->reference) && !empty($document->subject))
    <div class="subject-bar">Objet : <span>{{ $document->subject }}</span></div>
    @endif

    {{-- Adresses : émetteur et client --}}
    <div class="addr-table">
        {{-- Émetteur --}}
        <div class="addr-col">
            <div class="addr-col-label">Émetteur</div>
            <div class="addr-name">{{ $company->name ?? '' }}</div>
            @if(!empty($company->legal_name) && $company->legal_name !== $company->name)
            <div class="addr-line">{{ $company->legal_name }}</div>
            @endif
            @if(!empty($company->address))
            <div class="addr-line">{{ $company->address }}</div>
            @endif
            @if(!empty($company->city) || !empty($company->country))
            <div class="addr-line">{{ implode(', ', array_filter([$company->city ?? null, $company->country ?? null])) }}</div>
            @endif
            <div class="addr-meta">
                @if(!empty($company->phone)) Tél : {{ $company->phone }}<br>@endif
                @if(!empty($company->email)) {{ $company->email }}<br>@endif
                @if(!empty($company->tax_id)) NIF : {{ $company->tax_id }}<br>@endif
                @if(!empty($company->trade_register)) RC : {{ $company->trade_register }}<br>@endif
                @if(!empty($company->rccm)) RCCM : {{ $company->rccm }}<br>@endif
                @if(!empty($company->capital)) Capital : {{ $company->capital }}@endif
            </div>
        </div>

        {{-- Client --}}
        <div class="addr-col">
            <div class="addr-col-label">Destinataire</div>
            @if($customer)
            <div class="addr-name">{{ $customer->name ?? '' }}</div>
            @if(!empty($customer->address))
            <div class="addr-line">{{ $customer->address }}</div>
            @endif
            @if(!empty($customer->city) || !empty($customer->country))
            <div class="addr-line">{{ implode(', ', array_filter([$customer->city ?? null, $customer->country ?? null])) }}</div>
            @endif
            <div class="addr-meta">
                @if(!empty($customer->phone)) Tél : {{ $customer->phone }}<br>@endif
                @if(!empty($customer->email)) {{ $customer->email }}<br>@endif
                @if(!empty($customer->tax_number)) NIF : {{ $customer->tax_number }}@endif
            </div>
            @endif
        </div>
    </div>

    {{-- ── Tableau des lignes ─────────────────────────────── --}}
    <table class="lines-table">
        <thead>
            <tr>
                <th style="width:38%">Description</th>
                <th class="text-center" style="width:9%">Qté</th>
                <th class="text-center" style="width:8%">Unité</th>
                <th class="text-right" style="width:13%">Prix unit.</th>
                <th class="text-right" style="width:9%">Rem.%</th>
                <th class="text-right" style="width:9%">TVA%</th>
                <th class="text-right" style="width:14%">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->lines as $line)
            <tr>
                <td>
                    <div class="line-desc">{{ $line->description ?? '' }}</div>
                    @if(!empty($line->detail))
                    <div class="line-detail">{{ $line->detail }}</div>
                    @endif
                </td>
                <td class="text-center">{{ $line->quantity ?? '' }}</td>
                <td class="text-center">{{ $line->unit ?? '' }}</td>
                <td class="text-right">{{ number_format((float)($line->unit_price ?? 0), 2, ',', ' ') }}</td>
                <td class="text-right">
                    @if(!empty($line->discount_percent) && (float)$line->discount_percent > 0)
                    {{ number_format((float)$line->discount_percent, 1, ',', '') }}%
                    @else
                    —
                    @endif
                </td>
                <td class="text-right">
                    {{ number_format((float)($line->tax_rate ?? 0), 0, ',', '') }}%
                </td>
                <td class="text-right fw-700">
                    {{ number_format((float)($line->line_total ?? 0), 2, ',', ' ') }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Totaux ──────────────────────────────────────────── --}}
    <div class="totals-wrap">
        <div class="totals-spacer"></div>
        <div class="totals-block">
            <table class="totals-table">
                <tr>
                    <td>Sous-total HT</td>
                    <td>{{ number_format($totalHT, 2, ',', ' ') }} {{ $document->currency ?? '' }}</td>
                </tr>
                @if(!empty($document->discount_amount) && (float)$document->discount_amount > 0)
                <tr>
                    <td>Remise</td>
                    <td>- {{ number_format((float)$document->discount_amount, 2, ',', ' ') }} {{ $document->currency ?? '' }}</td>
                </tr>
                @endif
                @if(!empty($document->tax_amount))
                <tr>
                    <td>TVA ({{ $document->tax_rate ?? 0 }}%)</td>
                    <td>{{ number_format((float)$document->tax_amount, 2, ',', ' ') }} {{ $document->currency ?? '' }}</td>
                </tr>
                @endif
                <tr class="total-grand">
                    <td>TOTAL TTC</td>
                    <td>{{ number_format((float)($document->total ?? 0), 2, ',', ' ') }} {{ $document->currency ?? '' }}</td>
                </tr>
                @if(!empty($document->amount_paid) && (float)$document->amount_paid > 0)
                <tr class="total-paid">
                    <td>Déjà réglé</td>
                    <td>- {{ number_format((float)$document->amount_paid, 2, ',', ' ') }} {{ $document->currency ?? '' }}</td>
                </tr>
                <tr class="total-due">
                    <td>Reste à payer</td>
                    <td>{{ number_format(max(0, (float)($document->total ?? 0) - (float)$document->amount_paid), 2, ',', ' ') }} {{ $document->currency ?? '' }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- ── Infos de paiement (partial) ─────────────────────── --}}
    @includeIf('pdf.partials._payment-info')

    {{-- ── Notes & Conditions ──────────────────────────────── --}}
    @if(!empty($document->notes) || !empty($document->terms))
    <div class="notes-terms-wrap">
        @if(!empty($document->notes))
        <div class="notes-col">
            <div class="notes-label">Notes</div>
            <div class="notes-body">{{ $document->notes }}</div>
        </div>
        @endif
        @if(!empty($document->terms))
        <div class="notes-col">
            <div class="notes-label">Conditions</div>
            <div class="notes-body">{{ $document->terms }}</div>
        </div>
        @endif
    </div>
    @endif

    {{-- ── Signatures ───────────────────────────────────────── --}}
    @if($showSigSection)
    <div class="sig-section">
        @if($sigShowEmitter)
        <div class="sig-col">
            <div class="sig-col-label">Signature émetteur</div>
            <div class="sig-col-name">{{ $company->name ?? '' }}</div>
            <div class="sig-img-wrap">
                @if($hasSigDigital)
                <img src="{{ $sigDigitalBase64 }}" alt="signature">
                @elseif($hasSigStamp)
                <img src="{{ $sigStampBase64 }}" alt="cachet">
                @endif
            </div>
            <div class="sig-line">Signature &amp; cachet</div>
        </div>
        @endif
        @if($sigShowClient)
        <div class="sig-col">
            <div class="sig-col-label">Signature client</div>
            <div class="sig-col-name">{{ $customer->name ?? '' }}</div>
            <div class="sig-img-wrap"></div>
            <div class="sig-line">Lu et approuvé</div>
        </div>
        @endif
    </div>
    @endif

    {{-- ── QR code & hash de vérification ─────────────────── --}}
    @if($hasQr || !empty($verification_url) || !empty($integrity_hash))
    <div class="verify-row">
        @if($hasQr)
        <div class="verify-qr">
            <img src="{{ $qrDataUri }}" alt="QR vérification">
        </div>
        @endif
        <div class="verify-info">
            @if(!empty($verification_url))
            <div class="verify-url">Vérification : {{ $verification_url }}</div>
            @endif
            @if(!empty($integrity_hash))
            <div class="verify-hash">Hash : {{ $integrity_hash }}</div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Pied de page dans le flux ───────────────────────── --}}
    <div class="doc-footer">
        @if(!empty($company->invoice_footer))
        {{ $company->invoice_footer }}
        @else
        {{ $company->legal_name ?? $company->name ?? '' }}
        @if(!empty($company->address)) — {{ $company->address }}@endif
        @if(!empty($company->city)) {{ $company->city }}@endif
        @if(!empty($company->phone)) — Tél : {{ $company->phone }}@endif
        @if(!empty($company->email)) — {{ $company->email }}@endif
        @if(!empty($company->tax_id)) | NIF : {{ $company->tax_id }}@endif
        @if(!empty($company->trade_register)) | RC : {{ $company->trade_register }}@endif
        @endif
    </div>

</div>{{-- .main-content --}}

</body>
</html>

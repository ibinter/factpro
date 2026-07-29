<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
@@page { margin: 120px 50px 30px 50px; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #222; background: #fff; }

/* ── HEADER FIXE ─────────────────────────────────────────────────── */
#hdr {
    position: fixed;
    top: -105px; left: 0; right: 0;
    height: 90px;
    background: #fff;
    border-bottom: 1px solid #111;
    display: table;
    width: 100%;
    padding: 0 50px;
}
#hdr-left  { display: table-cell; vertical-align: middle; width: 60%; }
#hdr-right { display: table-cell; vertical-align: middle; text-align: right; width: 40%; }

#hdr-left .hdr-logo img { max-height: 35px; max-width: 80px; margin-bottom: 4px; display: block; }
#hdr-left .co-name {
    font-size: 13px;
    font-weight: 800;
    color: #111;
    line-height: 1.1;
}
#hdr-left .co-coords {
    font-size: 7px;
    color: #9CA3AF;
    margin-top: 3px;
    line-height: 1.5;
}

#hdr-right .doc-type {
    font-size: 18px;
    font-weight: 900;
    color: {{ $accentColor }};
    text-transform: uppercase;
    letter-spacing: -0.5px;
    line-height: 1;
}
#hdr-right .doc-number {
    font-size: 7px;
    color: #9CA3AF;
    margin-top: 4px;
}

/* ── WATERMARK ───────────────────────────────────────────────────── */
@if($watermark)
#watermark {
    position: fixed;
    top: 80mm; left: 15mm;
    font-size: 54px;
    font-weight: bold;
    color: rgba(0,0,0,.05);
    transform: rotate(-35deg);
    white-space: nowrap;
    z-index: 0;
}
@endif

/* ── CORPS PRINCIPAL ─────────────────────────────────────────────── */
#main { margin-top: 12px; }

/* ── SECTION ADRESSES ────────────────────────────────────────────── */
.addr-row { display: table; width: 100%; margin-bottom: 12px; }
.addr-col  { display: table-cell; width: 50%; vertical-align: top; padding-right: 16px; }
.addr-col:last-child { padding-right: 0; padding-left: 16px; }
.addr-sep  { border-bottom: 1px solid #E5E7EB; margin-bottom: 6px; }

.addr-label {
    font-size: 6px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #9CA3AF;
    margin-bottom: 4px;
}
.addr-name {
    font-size: 9.5px;
    font-weight: 800;
    color: #111;
    margin-bottom: 3px;
}
.addr-detail {
    font-size: 7.5px;
    color: #4B5563;
    line-height: 1.55;
}

/* ── INFOS DOCUMENT ──────────────────────────────────────────────── */
.doc-info { display: table; width: 100%; margin-bottom: 12px; }
.doc-info-col { display: table-cell; width: 50%; vertical-align: top; padding-right: 16px; }
.doc-info-col:last-child { padding-right: 0; }

.di-row { display: table; width: 100%; margin-bottom: 3px; }
.di-key {
    display: table-cell;
    font-size: 8.5px;
    color: #6B7280;
    width: 45%;
}
.di-val {
    display: table-cell;
    font-size: 8.5px;
    color: #111;
    font-weight: 600;
}
.di-sep { border-bottom: 1px solid #F3F4F6; margin: 2px 0; }

/* ── SUJET ───────────────────────────────────────────────────────── */
.doc-subject {
    font-size: 8px;
    color: #374151;
    margin-bottom: 10px;
    padding: 5px 0;
    border-bottom: 1px solid #F3F4F6;
}
.doc-subject strong { color: #111; }

/* ── TABLEAU LIGNES ──────────────────────────────────────────────── */
.items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }

.items-table thead th {
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: {{ $primaryColor }};
    border-bottom: 2px solid #111;
    padding: 4px 4px 4px 4px;
    text-align: left;
    font-weight: 700;
    background: none;
}
.items-table thead th.r { text-align: right; }

.items-table tbody td {
    padding: 5px 4px;
    font-size: 8px;
    color: #374151;
    border-bottom: 1px solid #F3F4F6;
    vertical-align: top;
}
.items-table tbody td.r { text-align: right; }
.items-table tbody .line-detail {
    font-size: 7px;
    color: #9CA3AF;
    margin-top: 2px;
}

.items-table tfoot td {
    font-size: 8px;
    font-weight: 700;
    color: #111;
    border-top: 2px solid #111;
    padding: 4px 4px;
}
.items-table tfoot td.r { text-align: right; }

/* ── ZONE TOTAUX + QR ────────────────────────────────────────────── */
.totaux-section { display: table; width: 100%; margin-bottom: 10px; }
.totaux-qr    { display: table-cell; vertical-align: bottom; width: 22mm; }
.totaux-qr img {
    width: 18mm; height: 18mm;
    border: 1px solid #E5E7EB;
}
.totaux-qr-label { font-size: 5.5px; color: #9CA3AF; text-align: center; margin-top: 2px; }
.totaux-space { display: table-cell; }
.totaux-right { display: table-cell; width: 78mm; vertical-align: top; }

.tot-table { width: 100%; border-collapse: collapse; }
.tot-table td {
    padding: 3px 2px;
    font-size: 8px;
    color: #4B5563;
}
.tot-table td:last-child { text-align: right; }
.tot-table .sep-row td { border-top: 1px solid #F3F4F6; padding-top: 0; padding-bottom: 0; }
.tot-table .total-row td {
    font-size: 14px;
    font-weight: 700;
    color: {{ $accentColor }};
    border-top: 2px solid #111;
    padding-top: 5px;
}
.tot-table .paid-row td { font-size: 8px; color: #6B7280; }
.tot-table .due-row td  { font-size: 9px; font-weight: 700; color: #DC2626; }

/* ── NOTES / CGV ─────────────────────────────────────────────────── */
.notes-section {
    margin-top: 8px;
    padding-top: 6px;
    border-top: 1px solid #F3F4F6;
    font-size: 7.5px;
    color: #6B7280;
    line-height: 1.5;
}
.notes-label {
    font-size: 6.5px;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: #9CA3AF;
    margin-bottom: 3px;
}
.terms-section {
    margin-top: 6px;
    font-size: 7px;
    color: #9CA3AF;
    line-height: 1.5;
}

/* ── SIGNATURES ──────────────────────────────────────────────────── */
.sig-section {
    margin-top: 14px;
    border-top: 1px solid #111;
    padding-top: 10px;
}
.sig-row   { display: table; width: 100%; }
.sig-col   { display: table-cell; width: 50%; vertical-align: top; padding: 0 12px; }
.sig-col:first-child { padding-left: 0; }
.sig-col:last-child  { padding-right: 0; }

.sig-title {
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #6B7280;
    margin-bottom: 6px;
}
.sig-box {
    border: 1px solid #E5E7EB;
    height: 42px;
    margin-bottom: 5px;
    position: relative;
}
.sig-box img { max-height: 40px; max-width: 100%; display: block; margin: 0 auto; }
.sig-stamp   { margin-top: 4px; text-align: center; }
.sig-stamp img { max-height: 30px; max-width: 55px; }
.sig-date  { font-size: 7px; color: #9CA3AF; margin-top: 4px; }

/* ── PIED DE PAGE ────────────────────────────────────────────────── */
.page-footer {
    margin-top: 14px;
    padding-top: 5px;
    border-top: 1px solid #E5E7EB;
    font-size: 7px;
    color: #9CA3AF;
}
.pf-table { width: 100%; border-collapse: collapse; }
.pf-table td { padding: 0; vertical-align: top; }
.pf-table td:last-child { text-align: right; }
.pf-table td:nth-child(2) { text-align: center; }
</style>
</head>
<body>

@php
    $totalHT = collect($document->lines)->sum(fn($l) => (float)($l->line_total ?? 0));

    $sigShowEmitter = $sigConfig['show_emitter'] ?? true;
    $sigShowClient  = $sigConfig['show_client']  ?? true;
    $showSigSection = $sigShowEmitter || $sigShowClient;

    $issueDate = $document->issue_date ?? ($document->date ?? null);
    $remainingDue = (float)($document->total ?? 0) - (float)($document->amount_paid ?? 0);
@endphp

@if($watermark)
<div id="watermark">{{ $watermark }}</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     HEADER FIXE
═══════════════════════════════════════════════════════════════ --}}
<div id="hdr">
    <div id="hdr-left">
        @if($logoBase64)
            <div class="hdr-logo"><img src="{{ $logoBase64 }}" alt="logo"></div>
        @endif
        <div class="co-name">{{ $company->name }}</div>
        <div class="co-coords">
            {{ $company->address ?? '' }}@if($company->city ?? null), {{ $company->city }}@endif
            @if($company->phone ?? null) &nbsp;·&nbsp; {{ $company->phone }}@endif
            @if($company->email ?? null) &nbsp;·&nbsp; {{ $company->email }}@endif
            @if($company->tax_id ?? null) &nbsp;·&nbsp; NIF : {{ $company->tax_id }}@endif
        </div>
    </div>
    <div id="hdr-right">
        <div class="doc-type">{{ $document->type_label }}</div>
        <div class="doc-number">N° {{ $document->number }}</div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     CORPS
═══════════════════════════════════════════════════════════════ --}}
<div id="main">

    {{-- ── Adresses ─────────────────────────────────────────────── --}}
    <div class="addr-row">
        {{-- Émetteur --}}
        <div class="addr-col">
            <div class="addr-label">Émetteur</div>
            <div class="addr-sep"></div>
            <div class="addr-name">{{ $company->legal_name ?? $company->name }}</div>
            <div class="addr-detail">
                @if($company->address ?? null){{ $company->address }}<br>@endif
                @if($company->city ?? null){{ $company->city }}@if($company->country ?? null), {{ $company->country }}@endif<br>@endif
                @if($company->trade_register ?? null)RC : {{ $company->trade_register }}<br>@endif
                @if($company->rccm ?? null)RCCM : {{ $company->rccm }}<br>@endif
                @if($company->capital ?? null)Capital : {{ $company->capital }}@endif
            </div>
        </div>

        {{-- Client --}}
        @if($document->customer ?? null)
        <div class="addr-col">
            <div class="addr-label">Destinataire</div>
            <div class="addr-sep"></div>
            <div class="addr-name">{{ $document->customer->name }}</div>
            <div class="addr-detail">
                @if($document->customer->address ?? null){{ $document->customer->address }}<br>@endif
                @if($document->customer->city ?? null){{ $document->customer->city }}@if($document->customer->country ?? null), {{ $document->customer->country }}@endif<br>@endif
                @if($document->customer->phone ?? null)Tél : {{ $document->customer->phone }}<br>@endif
                @if($document->customer->email ?? null){{ $document->customer->email }}<br>@endif
                @if($document->customer->tax_number ?? null)NIF : {{ $document->customer->tax_number }}@endif
            </div>
        </div>
        @endif
    </div>

    {{-- ── Infos document ──────────────────────────────────────── --}}
    <div class="doc-info">
        <div class="doc-info-col">
            <div class="di-row">
                <div class="di-key">Date d'émission</div>
                <div class="di-val">@if($issueDate){{ \Carbon\Carbon::parse($issueDate)->format('d/m/Y') }}@endif</div>
            </div>
            <div class="di-sep"></div>
            @if($document->due_date ?? null)
            <div class="di-row">
                <div class="di-key">Échéance</div>
                <div class="di-val">{{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}</div>
            </div>
            <div class="di-sep"></div>
            @endif
            <div class="di-row">
                <div class="di-key">Statut</div>
                <div class="di-val">{{ $document->status_label ?? $document->status ?? '' }}</div>
            </div>
        </div>
        <div class="doc-info-col">
            @if($document->reference ?? null)
            <div class="di-row">
                <div class="di-key">Référence</div>
                <div class="di-val">{{ $document->reference }}</div>
            </div>
            <div class="di-sep"></div>
            @endif
            <div class="di-row">
                <div class="di-key">Devise</div>
                <div class="di-val">{{ $document->currency }}</div>
            </div>
            @if($document->integrity_hash ?? null)
            <div class="di-sep"></div>
            <div class="di-row">
                <div class="di-key">Hash</div>
                <div class="di-val" style="font-size:6.5px;color:#9CA3AF;word-break:break-all;">{{ substr($document->integrity_hash, 0, 24) }}…</div>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Objet du document ───────────────────────────────────── --}}
    @if($document->subject ?? null)
    <div class="doc-subject">
        <strong>Objet :</strong> {{ $document->subject }}
    </div>
    @endif

    {{-- ── Tableau des lignes ──────────────────────────────────── --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:42%">Description</th>
                <th class="r" style="width:9%">Qté</th>
                <th style="width:7%">Unité</th>
                <th class="r" style="width:14%">P.U. HT</th>
                <th class="r" style="width:8%">Rem.</th>
                <th class="r" style="width:8%">TVA</th>
                <th class="r" style="width:12%">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->lines as $line)
            <tr>
                <td>
                    {{ $line->description }}
                    @if($line->detail ?? null)
                    <div class="line-detail">{{ $line->detail }}</div>
                    @endif
                </td>
                <td class="r">{{ number_format((float)($line->quantity ?? 0), 2, ',', ' ') }}</td>
                <td>{{ $line->unit ?? '' }}</td>
                <td class="r">{{ number_format((float)($line->unit_price ?? 0), 0, ',', ' ') }}</td>
                <td class="r">
                    @if(($line->discount_percent ?? 0) > 0)
                        {{ number_format((float)$line->discount_percent, 0, ',', ' ') }}&nbsp;%
                    @else
                        —
                    @endif
                </td>
                <td class="r">{{ number_format((float)($line->tax_rate ?? 0), 0, ',', ' ') }}&nbsp;%</td>
                <td class="r">{{ number_format((float)($line->line_total ?? 0), 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">Total HT</td>
                <td class="r">{{ number_format($totalHT, 0, ',', ' ') }} {{ $document->currency }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ── Totaux + QR ─────────────────────────────────────────── --}}
    <div class="totaux-section">
        <div class="totaux-qr">
            @if($qrDataUri)
                <img src="{{ $qrDataUri }}" alt="QR vérification">
                <div class="totaux-qr-label">Vérifier</div>
            @endif
        </div>
        <div class="totaux-space"></div>
        <div class="totaux-right">
            <table class="tot-table">
                <tr>
                    <td>Sous-total HT</td>
                    <td>{{ number_format((float)($document->subtotal ?? $totalHT), 0, ',', ' ') }} {{ $document->currency }}</td>
                </tr>
                @if(($document->discount_amount ?? 0) > 0)
                <tr>
                    <td>Remise</td>
                    <td>- {{ number_format((float)$document->discount_amount, 0, ',', ' ') }} {{ $document->currency }}</td>
                </tr>
                @endif
                <tr class="sep-row"><td colspan="2"></td></tr>
                <tr>
                    <td>TVA ({{ number_format((float)($document->tax_rate ?? 0), 0, ',', ' ') }}&nbsp;%)</td>
                    <td>{{ number_format((float)($document->tax_amount ?? 0), 0, ',', ' ') }} {{ $document->currency }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total TTC</td>
                    <td>{{ number_format((float)($document->total ?? 0), 0, ',', ' ') }} {{ $document->currency }}</td>
                </tr>
                @if(($document->amount_paid ?? 0) > 0)
                <tr class="paid-row">
                    <td>Déjà réglé</td>
                    <td>{{ number_format((float)$document->amount_paid, 0, ',', ' ') }} {{ $document->currency }}</td>
                </tr>
                <tr class="due-row">
                    <td>Reste dû</td>
                    <td>{{ number_format($remainingDue, 0, ',', ' ') }} {{ $document->currency }}</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    {{-- ── Infos de paiement ───────────────────────────────────── --}}
    @include('pdf.engine.blocks._payment-info')

    {{-- ── Notes ──────────────────────────────────────────────── --}}
    @if($document->notes ?? null)
    <div class="notes-section">
        <div class="notes-label">Notes</div>
        {{ $document->notes }}
    </div>
    @endif

    {{-- ── Conditions générales ────────────────────────────────── --}}
    @if($document->terms ?? null)
    <div class="terms-section">
        <div class="notes-label" style="margin-bottom:3px;">Conditions</div>
        {{ $document->terms }}
    </div>
    @endif

    {{-- ── Signatures ──────────────────────────────────────────── --}}
    @if($showSigSection)
    <div class="sig-section">
        <div class="sig-row">
            @if($sigShowEmitter)
            <div class="sig-col">
                <div class="sig-title">Signature émetteur</div>
                <div class="sig-box">
                    @if($sigDigitalBase64)
                        <img src="{{ $sigDigitalBase64 }}" alt="Signature numérique">
                    @endif
                </div>
                @if($sigStampBase64)
                <div class="sig-stamp"><img src="{{ $sigStampBase64 }}" alt="Cachet"></div>
                @endif
                <div class="sig-date">Date : ____________________</div>
            </div>
            @endif

            @if($sigShowClient)
            <div class="sig-col">
                <div class="sig-title">Signature client &mdash; Lu et approuvé</div>
                <div class="sig-box"></div>
                <div class="sig-date">Date : ____________________</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Pied de page ────────────────────────────────────────── --}}
    <div class="page-footer">
        <table class="pf-table">
            <tr>
                <td>
                    {{ $company->invoice_footer ?? ($company->name . ($company->legal_name ? ' — ' . $company->legal_name : '')) }}
                </td>
                <td>
                    @if($document->verification_url ?? null)
                        {{ $document->verification_url }}
                    @endif
                </td>
                <td>IBIG FactPro</td>
            </tr>
        </table>
    </div>

</div>{{-- /#main --}}
</body>
</html>

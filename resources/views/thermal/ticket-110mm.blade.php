<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $document->type_label }} {{ $document->number }} — Ticket 110mm</title>
@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $balance = round((float) $document->total - (float) $document->amount_paid, 2);
    $methodLabels = [
        'cash' => 'Espèces', 'mobile_money' => 'Mobile Money', 'card' => 'Carte',
        'bank_transfer' => 'Virement', 'cheque' => 'Chèque', 'credit' => 'Crédit',
    ];
    // Regroupement TVA par taux
    $taxGroups = [];
    foreach ($document->lines as $line) {
        $rate = (string) $line->tax_rate;
        if (!isset($taxGroups[$rate])) {
            $taxGroups[$rate] = ['base' => 0, 'tax' => 0];
        }
        $base = (float) $line->line_total / (1 + (float) $line->tax_rate / 100);
        $taxGroups[$rate]['base'] += $base;
        $taxGroups[$rate]['tax']  += (float) $line->line_total - $base;
    }

    // Calcul de la hauteur côté PHP (Chrome ignore @page auto et les mises à jour JS)
    $lh110 = 4.5; // ligne en mm sur 110mm
    $oneTicket110 = 12
        + 5 * $lh110  // en-tête société
        + 3 * $lh110  // n° doc + date + client
        + ($document->lines->count() * 2 * $lh110)
        + (count($taxGroups) + 3) * $lh110  // TVA détail + totaux
        + ($document->payments->count() * $lh110)
        + 32  // QR
        + 15  // pied
        + ($watermark ? $lh110 : 0);
    $ticketH110 = (int) ceil($oneTicket110 * $copies + ($copies - 1) * 10 + 8);
    $ticketH110 = max(150, $ticketH110);
@endphp
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { background: #e5e7eb; }
    body { font-family: 'Courier New', Courier, monospace; color: #000; }

    .ticket {
        width: 104mm;
        background: #fff;
        color: #000;
        font-size: 9pt;
        line-height: 1.4;
        padding: 3mm 3mm;
        margin: 12px auto;
        box-shadow: 0 2px 10px rgba(0,0,0,.25);
    }

    .center   { text-align: center; }
    .right    { text-align: right; }
    .bold     { font-weight: bold; }
    .upper    { text-transform: uppercase; }
    .small    { font-size: 80%; }
    .company-name { font-size: 140%; font-weight: bold; text-transform: uppercase; }
    .sep      { overflow: hidden; white-space: nowrap; border-bottom: 1px dashed #000; margin: 3px 0; }
    .total-line { font-size: 140%; font-weight: bold; }
    .watermark-box { font-size: 75%; text-align: center; margin: 2px 0; opacity: 0.6; }
    .cut      { text-align: center; margin: 8px auto; width: 104mm; color: #000; font-size: 9pt; }
    .section-title { font-weight: bold; text-transform: uppercase; background: #000; color: #fff; padding: 1px 3px; margin: 3px 0 2px; font-size: 8pt; }
    .url      { word-break: break-all; }

    /* Deux colonnes articles */
    table { width: 100%; border-collapse: collapse; }
    td { vertical-align: top; padding: 0 1px; }

    .col-desc  { width: 55%; }
    .col-qty   { width: 20%; text-align: center; }
    .col-price { width: 25%; text-align: right; }

    .items-head td { font-weight: bold; border-bottom: 1px solid #000; font-size: 8pt; text-transform: uppercase; }
    .items-foot td { border-top: 1px solid #000; }

    .qr img { display: block; margin: 4px auto 2px; width: 30mm; height: auto; }

    /* Client block */
    .client-block { border: 1px solid #000; padding: 2px 4px; margin: 3px 0; font-size: 8.5pt; }
    .client-block .client-title { font-weight: bold; text-transform: uppercase; font-size: 8pt; border-bottom: 1px solid #ccc; margin-bottom: 2px; }

    /* TVA détail */
    .tva-table td { font-size: 8pt; }
    .tva-table .tva-head td { font-weight: bold; border-bottom: 1px dotted #000; }

    /* Toolbar */
    .toolbar {
        position: sticky; top: 0; z-index: 10;
        display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: center;
        background: #1f2937; color: #fff; padding: 10px 12px;
        font-family: Arial, Helvetica, sans-serif; font-size: 14px;
        box-shadow: 0 1px 4px rgba(0,0,0,.3);
    }
    .toolbar button, .toolbar a {
        font-size: 14px; padding: 6px 12px; border-radius: 4px; border: 1px solid #4b5563;
        background: #fff; color: #111; cursor: pointer; text-decoration: none;
    }
    .toolbar button.print { background: #059669; color: #fff; border-color: #059669; font-weight: bold; }

    @media print {
        .no-print { display: none !important; }
        html, body {
            background: #fff;
            margin: 0; padding: 0;
            width: 104mm;
        }
        .ticket { margin: 0; box-shadow: none; page-break-inside: avoid; }
        .cut { margin: 1mm 0; page-break-before: always; }
    }
</style>
<style>
    @page { size: 104mm {{ $ticketH110 }}mm; margin: 0; }
</style>
</head>
<body>

<div class="toolbar no-print">
    <button type="button" class="print" onclick="window.print()">🖨 Imprimer 110mm</button>
    <a href="#" onclick="history.back(); return false;">← Retour</a>
</div>

@for ($copy = 1; $copy <= $copies; $copy++)
    @if ($copy > 1)
        <div class="cut">✂ - - - - - - - - - - - - - - - - - - - - -</div>
    @endif

    <div class="ticket">

        {{-- En-tête société --}}
        <div class="center">
            <div class="company-name">{{ $company->name }}</div>
            @if ($company->address)<div>{{ $company->address }}</div>@endif
            @if ($company->city)<div>{{ $company->city }}</div>@endif
            @if ($company->phone)<div>TÉL : {{ $company->phone }}</div>@endif
            @if ($company->email)<div class="small">{{ $company->email }}</div>@endif
            @if ($company->tax_id)<div class="small">N° FISCAL : {{ $company->tax_id }}</div>@endif
        </div>

        <div class="sep"></div>

        {{-- Référence document --}}
        <div class="center bold upper">{{ $document->type_label }} {{ $document->number }}</div>
        <div class="center small">{{ $document->issue_date?->format('d/m/Y') }} — {{ now()->format('H:i') }}</div>
        @if ($document->reference)
            <div class="center small">Réf : {{ $document->reference }}</div>
        @endif

        {{-- Informations client (hôtel, service…) --}}
        @if ($document->customer)
            <div class="client-block">
                <div class="client-title">Client</div>
                <div>{{ $document->customer->name }}</div>
                @if ($document->customer->phone)<div>Tél : {{ $document->customer->phone }}</div>@endif
                @if ($document->customer->email)<div class="small">{{ $document->customer->email }}</div>@endif
                @if ($document->customer->address)<div class="small">{{ $document->customer->address }}</div>@endif
            </div>
        @endif

        <div class="sep"></div>

        {{-- Tableau articles 2 colonnes --}}
        <table>
            <tr class="items-head">
                <td class="col-desc">Désignation</td>
                <td class="col-qty">Qté</td>
                <td class="col-price">Total</td>
            </tr>
            @foreach ($document->lines as $line)
                <tr>
                    <td class="col-desc upper">{{ $line->description }}</td>
                    <td class="col-qty">{{ rtrim(rtrim(number_format((float) $line->quantity, 2, ',', ' '), '0'), ',') }}<br><span class="small">× {{ $fmt($line->unit_price) }}</span></td>
                    <td class="col-price">{{ $fmt($line->line_total) }}</td>
                </tr>
            @endforeach
        </table>

        <div class="sep"></div>

        {{-- Totaux --}}
        <table>
            <tr><td>SOUS-TOTAL HT</td><td class="right">{{ $fmt($document->subtotal) }}</td></tr>
            @if ((float) $document->discount_amount > 0)
                <tr><td>REMISE</td><td class="right">-{{ $fmt($document->discount_amount) }}</td></tr>
            @endif
            <tr><td>TVA</td><td class="right">{{ $fmt($document->tax_amount) }}</td></tr>
        </table>
        <table class="total-line">
            <tr><td>TOTAL TTC</td><td class="right">{{ $fmt($document->total) }} {{ $document->currency }}</td></tr>
        </table>

        @if ($document->payments->isNotEmpty())
            <table>
                @foreach ($document->payments as $payment)
                    <tr>
                        <td>{{ strtoupper($methodLabels[$payment->method] ?? $payment->method) }}@if ($payment->paid_at) {{ $payment->paid_at->format('d/m') }}@endif</td>
                        <td class="right">{{ $fmt($payment->amount) }}</td>
                    </tr>
                @endforeach
                @if ($balance > 0)
                    <tr class="bold"><td>RESTE À PAYER</td><td class="right">{{ $fmt($balance) }} {{ $document->currency }}</td></tr>
                @endif
            </table>
        @elseif ($balance > 0)
            <table>
                <tr class="bold"><td>RESTE À PAYER</td><td class="right">{{ $fmt($balance) }} {{ $document->currency }}</td></tr>
            </table>
        @endif

        {{-- Détail TVA par taux --}}
        @if (count($taxGroups) > 0)
            <div class="sep"></div>
            <div class="section-title">Détail TVA</div>
            <table class="tva-table">
                <tr class="tva-head">
                    <td>Taux</td><td class="right">Base HT</td><td class="right">Montant TVA</td>
                </tr>
                @foreach ($taxGroups as $rate => $grp)
                    <tr>
                        <td>{{ $rate }}%</td>
                        <td class="right">{{ $fmt($grp['base']) }}</td>
                        <td class="right">{{ $fmt($grp['tax']) }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        {{-- QR code centré --}}
        <div class="sep"></div>
        <div class="qr center">
            <img src="{{ $qrDataUri }}" alt="QR authenticité">
            <div class="small">Vérifiez ce document :</div>
            <div class="small url">{{ $document->verificationUrl() }}</div>
        </div>

        <div class="sep"></div>

        {{-- Pied légal --}}
        <div class="center">
            <div class="bold">MERCI DE VOTRE CONFIANCE !</div>
            @if ($company->invoice_footer)
                <div class="small">{{ \Illuminate\Support\Str::limit($company->invoice_footer, 200) }}</div>
            @endif
            <div class="small">Document authentifié — IBIG FactPro</div>
            <div class="small">Conservation : 10 ans (Art. L123-22 C. com.)</div>
        </div>
        @if ($watermark)
            <div class="watermark-box">[ {{ $watermark }} ]</div>
        @endif

    </div>
@endfor

@if ($autoprint)
<script>window.addEventListener('load', () => setTimeout(() => window.print(), 600));</script>
@endif
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $document->type_label }} {{ $document->number }} — Ticket {{ $width }}mm</title>
@php
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $fontSize = $width <= 58 ? '10px' : ($width <= 80 ? '11px' : '12px');
    $methodLabels = [
        'cash' => 'Espèces', 'mobile_money' => 'Mobile Money', 'card' => 'Carte',
        'bank_transfer' => 'Virement', 'cheque' => 'Chèque', 'credit' => 'Crédit',
    ];
    $balance = round((float) $document->total - (float) $document->amount_paid, 2);
    $sep = str_repeat('-', $width <= 58 ? 32 : ($width <= 80 ? 42 : 58));
@endphp
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html, body { background: #e5e7eb; }
    body { font-family: 'Courier New', Courier, monospace; color: #000; }

    .ticket {
        width: {{ $width }}mm;
        background: #fff;
        color: #000;
        font-size: {{ $fontSize }};
        line-height: 1.35;
        padding: 3mm 2mm;
        margin: 12px auto;
        box-shadow: 0 2px 10px rgba(0,0,0,.25);
    }

    .center { text-align: center; }
    .right { text-align: right; }
    .bold { font-weight: bold; }
    .upper { text-transform: uppercase; }
    .small { font-size: 90%; }
    .company-name { font-size: 135%; font-weight: bold; text-transform: uppercase; }
    .sep { overflow: hidden; white-space: nowrap; }
    .total-line { font-size: 140%; font-weight: bold; }
    .watermark-box { font-size: 75%; text-align: center; margin: 2px 0; opacity: 0.6; }
    .cut { text-align: center; margin: 8px auto; width: {{ $width }}mm; color: #000; font-size: {{ $fontSize }}; }

    table { width: 100%; border-collapse: collapse; }
    td { vertical-align: top; padding: 0; }
    .qr img { display: block; margin: 4px auto 2px; width: {{ $width <= 58 ? '22mm' : '28mm' }}; height: auto; }
    .url { word-break: break-all; }

    /* Barre d'outils de prévisualisation (écran uniquement) */
    .toolbar {
        position: sticky; top: 0; z-index: 10;
        display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: center;
        background: #1f2937; color: #fff; padding: 10px 12px;
        font-family: Arial, Helvetica, sans-serif; font-size: 14px;
        box-shadow: 0 1px 4px rgba(0,0,0,.3);
    }
    .toolbar button, .toolbar select, .toolbar a {
        font-size: 14px; padding: 6px 12px; border-radius: 4px; border: 1px solid #4b5563;
        background: #fff; color: #111; cursor: pointer; text-decoration: none;
    }
    .toolbar button.print { background: #059669; color: #fff; border-color: #059669; font-weight: bold; }
    .toolbar label { font-size: 13px; color: #d1d5db; }

    @page { size: {{ $width }}mm auto; margin: 0; }
    @media print {
        .no-print { display: none !important; }
        html, body { background: #fff; margin: 0; padding: 0; }
        .ticket { margin: 0; box-shadow: none; width: {{ $width }}mm; }
        .cut { margin: 2mm auto; }
    }
</style>
</head>
<body>

<div class="toolbar no-print">
    <button type="button" class="print" onclick="window.print()">🖨 Imprimer</button>
    <label>Largeur
        <select onchange="reloadWith('width', this.value)">
            <option value="58" @selected($width === 58)>58 mm</option>
            <option value="80" @selected($width === 80)>80 mm</option>
            <option value="110" @selected($width === 110)>110 mm</option>
        </select>
    </label>
    <label>Copies
        <select onchange="reloadWith('copies', this.value)">
            <option value="1" @selected($copies === 1)>1</option>
            <option value="2" @selected($copies === 2)>2</option>
            <option value="3" @selected($copies === 3)>3</option>
        </select>
    </label>
    <a href="#" onclick="history.back(); return false;">← Retour</a>
</div>

@for ($copy = 1; $copy <= $copies; $copy++)
    @if ($copy > 1)
        <div class="cut">✂ - - - - - - - - - - - - - - - -</div>
    @endif

    <div class="ticket">
        {{-- En-tête société --}}
        <div class="center">
            <div class="company-name">{{ $company->name }}</div>
            @if ($company->address)<div>{{ $company->address }}</div>@endif
            @if ($company->city)<div>{{ $company->city }}</div>@endif
            @if ($company->phone)<div>TÉL : {{ $company->phone }}</div>@endif
            @if ($company->tax_id)<div class="small">N° FISCAL : {{ $company->tax_id }}</div>@endif
        </div>

        <div class="sep">{{ $sep }}</div>

        {{-- Document --}}
        <div class="center bold upper">{{ $document->type_label }} {{ $document->number }}</div>
        <div class="center small">{{ $document->issue_date?->format('d/m/Y') }} {{ now()->format('H:i') }}</div>
        @if ($document->customer)
            <div class="small">CLIENT : {{ $document->customer->name }}@if ($document->customer->phone) — {{ $document->customer->phone }}@endif</div>
        @endif

        <div class="sep">{{ $sep }}</div>

        {{-- Lignes articles --}}
        <table>
            @foreach ($document->lines as $line)
                <tr><td colspan="2" class="upper">{{ $line->description }}</td></tr>
                <tr>
                    <td>&nbsp;&nbsp;{{ rtrim(rtrim(number_format((float) $line->quantity, 2, ',', ' '), '0'), ',') }} x {{ $fmt($line->unit_price) }}</td>
                    <td class="right">{{ $fmt($line->line_total) }}</td>
                </tr>
            @endforeach
        </table>

        <div class="sep">{{ $sep }}</div>

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

        {{-- QR d'authenticité --}}
        <div class="qr center">
            <img src="{{ $qrDataUri }}" alt="QR d'authenticité">
            <div class="small">Vérifiez ce document :</div>
            <div class="small url">{{ $document->verificationUrl() }}</div>
        </div>

        <div class="sep">{{ $sep }}</div>

        {{-- Pied --}}
        <div class="center">
            <div class="bold">MERCI DE VOTRE VISITE !</div>
            @if ($company->invoice_footer)
                <div class="small">{{ \Illuminate\Support\Str::limit($company->invoice_footer, 160) }}</div>
            @endif
            <div class="small">— Propulsé par IBIG FactPro —</div>
        </div>
        @if ($watermark)
            <div class="watermark-box">[ {{ $watermark }} ]</div>
        @endif
    </div>
@endfor

<script>
    function reloadWith(param, value) {
        const url = new URL(window.location.href);
        url.searchParams.set(param, value);
        url.searchParams.set('autoprint', '0');
        window.location.href = url.toString();
    }
</script>
@if ($autoprint)
<script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>
@endif
</body>
</html>

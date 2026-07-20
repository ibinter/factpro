@php
    // Libellés bilingues FR/EN (famille Afrique & Export)
    $enLabels = [
        'quote' => 'QUOTATION',
        'proforma' => 'PROFORMA INVOICE',
        'sales_order' => 'SALES ORDER',
        'purchase_order' => 'PURCHASE ORDER',
        'delivery_note' => 'DELIVERY NOTE',
        'invoice' => 'INVOICE',
        'credit_note' => 'CREDIT NOTE',
        'payment_receipt' => 'PAYMENT RECEIPT',
        'deposit_invoice' => 'DEPOSIT INVOICE',
        'balance_invoice' => 'BALANCE INVOICE',
        'work_order' => 'WORK ORDER',
        'pos_ticket' => 'POS TICKET',
    ];
    $enTitle = $enLabels[$document->type] ?? strtoupper($document->type_label);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>{{ $document->number }}</title>
<style>
    @@page { margin: 108px 45px 90px 45px; }

    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1D2B1D; margin: 0; }

    /* Filigrane VERSION ESSAI */
    .watermark {
        position: fixed; top: 40%; left: 5%; width: 90%; text-align: center;
        transform: rotate(-35deg); font-size: 42px; font-weight: bold;
        color: rgba(220, 38, 38, 0.18); z-index: 1000; letter-spacing: 4px;
    }

    /* Bandeau tricolore fin en haut de chaque page */
    .tricolor { position: fixed; top: -108px; left: -45px; right: -45px; height: 6px; }
    .tricolor td { height: 6px; padding: 0; }
    .tri-green { background: #1B5E20; }
    .tri-gold { background: #FBC02D; }
    .tri-red { background: #C62828; }

    /* En-tête panafricain */
    header { position: fixed; top: -78px; left: 0; right: 0; height: 68px; border-bottom: 2px solid #FBC02D; }
    .company-name { font-size: 18px; font-weight: bold; color: #1B5E20; }
    .company-meta { font-size: 8.5px; color: #6B7A6B; line-height: 1.5; }
    .doc-title { font-size: 16px; font-weight: bold; color: #1B5E20; text-align: right; text-transform: uppercase; letter-spacing: 1px; }
    .doc-title .en { color: #C62828; }
    .doc-number { font-size: 11px; color: #6B7A6B; text-align: right; }

    /* Pied de page */
    footer {
        position: fixed; bottom: -70px; left: 0; right: 0; height: 60px;
        font-size: 8px; color: #6B7A6B; border-top: 2px solid #C62828; padding-top: 6px;
    }

    .addresses { width: 100%; margin-bottom: 18px; }
    .addresses td { vertical-align: top; width: 50%; }
    .badge-box { background: #F3F7EE; border-left: 4px solid #1B5E20; border-right: 2px solid #FBC02D; padding: 10px 12px; }
    .badge-box .label { font-size: 8px; text-transform: uppercase; color: #C62828; letter-spacing: 1px; font-weight: bold; }
    .badge-box .name { font-size: 12px; font-weight: bold; color: #1B5E20; margin: 3px 0; }

    .meta-table { margin-top: 8px; }
    .meta-table td { padding: 2px 8px 2px 0; font-size: 9px; }
    .meta-table .k { color: #6B7A6B; }

    table.lines { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.lines thead th {
        background: #1B5E20; color: #FFF8E1; padding: 7px 8px; font-size: 8px;
        text-transform: uppercase; letter-spacing: 0.5px; text-align: left;
        border-bottom: 2px solid #FBC02D;
    }
    table.lines thead th.num, table.lines td.num { text-align: right; }
    table.lines tbody td { padding: 7px 8px; border-bottom: 1px solid #DCE5D6; font-size: 9.5px; }
    table.lines tbody tr:nth-child(even) td { background: #F7FAF2; }

    table.totals { width: 46%; margin-left: 54%; margin-top: 12px; border-collapse: collapse; }
    table.totals td { padding: 5px 8px; font-size: 10px; }
    table.totals .k { color: #6B7A6B; }
    table.totals .v { text-align: right; font-weight: bold; }
    table.totals tr.grand td {
        background: #1B5E20; color: #FFF8E1; font-size: 11.5px; font-weight: bold;
        border-top: 2px solid #FBC02D; border-bottom: 2px solid #C62828;
    }
    table.totals tr.paid td { color: #1B5E20; }
    table.totals tr.due td { color: #C62828; }

    .qr-section { margin-top: 25px; width: 100%; }
    .qr-section td { vertical-align: top; }
    .qr-box { text-align: center; width: 110px; }
    .qr-box img { width: 90px; height: 90px; }
    .qr-caption { font-size: 7px; color: #6B7A6B; margin-top: 2px; }
    .notes { font-size: 9px; color: #33402F; padding-right: 20px; }
    .notes .title { font-weight: bold; color: #1B5E20; margin-bottom: 3px; font-size: 9px; }
    .hash { font-size: 6.5px; color: #9CAB96; word-break: break-all; margin-top: 4px; }
</style>
</head>
<body>

@if ($watermark)
    <div class="watermark">{{ $watermark }}</div>
@endif

<table class="tricolor">
    <tr>
        <td class="tri-green" style="width:33.4%"></td>
        <td class="tri-gold" style="width:33.3%"></td>
        <td class="tri-red" style="width:33.3%"></td>
    </tr>
</table>

<header>
    <table style="width:100%">
        <tr>
            <td style="width:52%">
                <div class="company-name">{{ $company->name }}</div>
                <div class="company-meta">
                    {{ $company->address }}@if($company->city), {{ $company->city }}@endif — {{ $company->country }}<br>
                    @if($company->phone)Tél : {{ $company->phone }} · @endif{{ $company->email }}
                    @if($company->tax_id)<br>N° Fiscal / Tax ID : {{ $company->tax_id }}@endif
                </div>
            </td>
            <td style="width:48%">
                <div class="doc-title">{{ mb_strtoupper($document->type_label) }} <span class="en">/ {{ $enTitle }}</span></div>
                <div class="doc-number">N° / No. {{ $document->number }}</div>
            </td>
        </tr>
    </table>
</header>

<footer>
    <table style="width:100%">
        <tr>
            <td style="width:70%">
                {{ $company->invoice_footer ?? 'Merci de votre confiance. / Thank you for your trust.' }}<br>
                Document vérifiable / Verify at : {{ $document->verificationUrl() }}
            </td>
            <td style="width:30%; text-align:right;">
                Propulsé par <b style="color:#1B5E20">IBIG FactPro</b><br>
                factpro.ibigsoft.com
            </td>
        </tr>
    </table>
</footer>

<main>
    <table class="addresses">
        <tr>
            <td>
                <table class="meta-table">
                    <tr><td class="k">Date d'émission / Issue date :</td><td><b>{{ $document->issue_date->format('d/m/Y') }}</b></td></tr>
                    @if ($document->due_date)
                        <tr><td class="k">Échéance / Due date :</td><td><b>{{ $document->due_date->format('d/m/Y') }}</b></td></tr>
                    @endif
                    @if ($document->reference)
                        <tr><td class="k">Référence / Reference :</td><td>{{ $document->reference }}</td></tr>
                    @endif
                    <tr><td class="k">Devise / Currency :</td><td>{{ $document->currency }}</td></tr>
                </table>
            </td>
            <td>
                <div class="badge-box">
                        <div class="label">Facturé à / Bill to</div>
                    @if ($document->customer)
                        <div class="name">{{ $document->customer->name }}</div>
                        <div class="company-meta">
                            @if($document->customer->address){{ $document->customer->address }}<br>@endif
                            @if($document->customer->city){{ $document->customer->city }} — @endif{{ $document->customer->country }}<br>
                            @if($document->customer->phone){{ $document->customer->phone }}@endif
                            @if($document->customer->email) · {{ $document->customer->email }}@endif
                            @if($document->customer->tax_id)<br>N° Fiscal / Tax ID : {{ $document->customer->tax_id }}@endif
                        </div>
                    @else
                        <div class="name" style="color:#aaa;font-style:italic;">— Non renseigné —</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="lines">
        <thead>
            <tr>
                <th style="width:40%">Désignation / Description</th>
                <th class="num" style="width:10%">Qté / Qty</th>
                <th style="width:9%">Unité / Unit</th>
                <th class="num" style="width:14%">P.U. HT / Unit price</th>
                <th class="num" style="width:8%">Rem. % / Disc.</th>
                <th class="num" style="width:7%">TVA % / VAT</th>
                <th class="num" style="width:14%">Total HT / Total</th>
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
            <td class="k">Sous-total HT / Subtotal</td>
            <td class="v">{{ number_format((float) $document->subtotal, 0, ',', ' ') }} {{ $document->currency }}</td>
        </tr>
        @if ((float) $document->discount_amount > 0)
            <tr>
                <td class="k">Remise / Discount</td>
                <td class="v">−{{ number_format((float) $document->discount_amount, 0, ',', ' ') }} {{ $document->currency }}</td>
            </tr>
        @endif
        <tr>
            <td class="k">TVA / VAT</td>
            <td class="v">{{ number_format((float) $document->tax_amount, 0, ',', ' ') }} {{ $document->currency }}</td>
        </tr>
        <tr class="grand">
            <td>TOTAL TTC / TOTAL</td>
            <td class="v">{{ number_format((float) $document->total, 0, ',', ' ') }} {{ $document->currency }}</td>
        </tr>
        @if ((float) $document->amount_paid > 0)
            <tr class="paid">
                <td class="k">Payé / Paid</td>
                <td class="v">{{ number_format((float) $document->amount_paid, 0, ',', ' ') }} {{ $document->currency }}</td>
            </tr>
            <tr class="due">
                <td class="k">Reste à payer / Balance due</td>
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
                    <div class="title" style="margin-top:8px">Conditions / Terms</div>
                    <div>{!! nl2br(e($document->terms)) !!}</div>
                @endif
            </td>
            <td class="qr-box">
                <img src="{{ $qrDataUri }}" alt="QR de vérification">
                <div class="qr-caption">
                    <b>DOCUMENT AUTHENTIFIABLE / VERIFIABLE DOCUMENT</b><br>
                    Scannez pour vérifier l'authenticité / Scan to verify
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

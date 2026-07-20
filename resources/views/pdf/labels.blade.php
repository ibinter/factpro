<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Étiquettes produits</title>
    <style>
        @@page { margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #111; }

        .sheet {
            position: relative;
            width: 210mm;
            height: 296mm; /* légèrement < 297 pour éviter une page blanche DomPDF */
            page-break-after: always;
        }
        .sheet:last-child { page-break-after: avoid; }

        .cell {
            position: absolute;
            overflow: hidden;
            padding: 1.5mm;
            text-align: center;
            @if (!empty($options['guides']))
            border: 0.2mm dashed #9ca3af;
            @endif
        }

        .name {
            font-size: 8px;
            line-height: 1.2;
            max-height: 20px; /* ~2 lignes */
            overflow: hidden;
            font-weight: normal;
        }
        .name-lg { font-size: 9px; max-height: 33px; /* ~3 lignes */ }
        .price { font-weight: bold; font-size: 11px; line-height: 1.25; }
        .price-lg { font-size: 13px; }
        .sku { font-size: 6px; color: #444; line-height: 1.1; }
        .barcode-img { width: 85%; height: 10mm; }
        .barcode-img-lg { width: 85%; height: 12mm; }
        .qr-img { width: 14mm; height: 14mm; }
        table.split { width: 100%; border-collapse: collapse; }
        table.split td { vertical-align: middle; padding: 0; }
    </style>
</head>
<body>
@php
    /** @var array $format */
    $w = $format['width_mm'];
    $h = $format['height_mm'];
    $cols = (int) $format['cols'];
    $rows = (int) $format['rows'];
    $mt = $format['margin_top_mm'];
    $ml = $format['margin_left_mm'];
    $gh = $format['gutter_h_mm'] ?? 0;
    $gv = $format['gutter_v_mm'] ?? 0;

    // Étiquette "large" (assez de place pour un QR à côté du texte) et "haute".
    $isWide = $w >= 90;
    $isTall = $h >= 55;
    // Petites étiquettes (ex. L7160) : pas de QR même si demandé.
    $qrFits = $isWide && !empty($options['show_qr']);
@endphp

@foreach ($pages as $page)
    <div class="sheet">
        @foreach ($page as $i => $label)
            @php
                $col = $i % $cols;
                $row = intdiv($i, $cols);
                $left = $ml + $col * ($w + $gh);
                $top = $mt + $row * ($h + $gv);
            @endphp
            <div class="cell" style="left: {{ number_format($left, 2, '.', '') }}mm; top: {{ number_format($top, 2, '.', '') }}mm; width: {{ number_format($w, 2, '.', '') }}mm; height: {{ number_format($h, 2, '.', '') }}mm;">
                @if ($qrFits && $label['qr'] !== '')
                    {{-- Format large : texte à gauche, QR à droite --}}
                    <table class="split">
                        <tr>
                            <td style="width: 72%; text-align: center;">
                                @if ($options['show_name'])
                                    <div class="{{ $isTall ? 'name name-lg' : 'name' }}">{{ $label['name'] }}</div>
                                @endif
                                @if ($options['show_price'])
                                    <div class="{{ $isTall ? 'price price-lg' : 'price' }}">{{ $label['price'] }}</div>
                                @endif
                                @if ($options['show_barcode'] && $label['barcode'] !== '')
                                    <img class="{{ $isTall ? 'barcode-img barcode-img-lg' : 'barcode-img' }}" src="{{ $label['barcode'] }}" alt="">
                                    @if ($options['show_sku'] && $label['sku'])
                                        <div class="sku">{{ $label['barcode_text'] }}</div>
                                    @endif
                                @elseif ($options['show_sku'] && $label['sku'])
                                    <div class="sku">{{ $label['sku'] }}</div>
                                @endif
                            </td>
                            <td style="width: 28%; text-align: center;">
                                <img class="qr-img" src="{{ $label['qr'] }}" alt="">
                            </td>
                        </tr>
                    </table>
                @else
                    {{-- Format compact : empilement vertical nom / prix / code-barres --}}
                    @if ($options['show_name'])
                        <div class="{{ $isTall ? 'name name-lg' : 'name' }}">{{ $label['name'] }}</div>
                    @endif
                    @if ($options['show_price'])
                        <div class="{{ $isTall ? 'price price-lg' : 'price' }}">{{ $label['price'] }}</div>
                    @endif
                    @if ($options['show_barcode'] && $label['barcode'] !== '')
                        <img class="{{ $isTall ? 'barcode-img barcode-img-lg' : 'barcode-img' }}" src="{{ $label['barcode'] }}" alt="">
                        @if ($options['show_sku'] && $label['sku'])
                            <div class="sku">{{ $label['barcode_text'] }}</div>
                        @endif
                    @elseif ($options['show_sku'] && $label['sku'])
                        <div class="sku">{{ $label['sku'] }}</div>
                    @endif
                @endif
            </div>
        @endforeach
    </div>
@endforeach
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8pt;
        color: #000;
        background: #fff;
    }
    .label {
        width: 81mm;
        height: 51mm;
        border: 2px solid #1a56db;
        border-radius: 2mm;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    /* En-tête coloré */
    .label-header {
        background: #1a56db;
        color: #fff;
        padding: 2mm 3mm 1.5mm;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .label-header .warranty-title {
        font-size: 14pt;
        font-weight: bold;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .label-header .warranty-duration {
        font-size: 11pt;
        font-weight: bold;
        background: #fff;
        color: #1a56db;
        padding: 1mm 2.5mm;
        border-radius: 1mm;
    }

    /* Corps */
    .label-body {
        flex: 1;
        display: flex;
        gap: 3mm;
        padding: 2mm 3mm;
    }
    .label-info { flex: 1; }
    .company-name { font-weight: bold; font-size: 9pt; color: #1a56db; margin-bottom: 1mm; }
    .product-name { font-weight: bold; font-size: 8pt; text-transform: uppercase; margin-bottom: 1.5mm; }
    .info-row { font-size: 7.5pt; line-height: 1.5; }
    .info-row span { color: #555; }

    .label-qr { display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .label-qr img { width: 16mm; height: 16mm; display: block; }
    .label-qr .qr-label { font-size: 5.5pt; color: #555; text-align: center; margin-top: 0.5mm; }

    /* Pied */
    .label-footer {
        background: #f0f4ff;
        border-top: 1px solid #c7d7f5;
        padding: 1mm 3mm;
        text-align: center;
        font-size: 6.5pt;
        color: #555;
        font-style: italic;
    }

    @page { size: 85mm 55mm; margin: 2mm; }
    @media print { body { background: #fff; } }
</style>
</head>
<body>
<div class="label">

    <div class="label-header">
        <div class="warranty-title">Garantie</div>
        <div class="warranty-duration">{{ $warrantyYears }} an{{ $warrantyYears > 1 ? 's' : '' }}</div>
    </div>

    <div class="label-body">
        <div class="label-info">
            <div class="company-name">{{ $company->name }}</div>
            <div class="product-name">{{ $productName }}</div>
            @if ($serialNumber)
                <div class="info-row"><span>N° série :</span> {{ $serialNumber }}</div>
            @endif
            <div class="info-row"><span>Achat :</span> {{ $purchaseDate->format('d/m/Y') }}</div>
            <div class="info-row"><span>Fin garantie :</span> <strong>{{ $warrantyEnd->format('d/m/Y') }}</strong></div>
            <div class="info-row"><span>Réf :</span> {{ $document->number }}</div>
        </div>
        <div class="label-qr">
            <img src="{{ $qrDataUri }}" alt="SAV">
            <div class="qr-label">SAV /<br>Contact</div>
        </div>
    </div>

    <div class="label-footer">
        Conserver cette étiquette — Document justificatif de garantie
    </div>

</div>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 9pt;
        color: #000;
        background: #fff;
    }
    .sticker {
        width: 140mm;
        height: 97mm;
        border: 2px solid #000;
        padding: 4mm;
        position: relative;
        overflow: hidden;
        page-break-after: always;
    }

    /* Code-barres en haut (simulé en texte, librecode128 n'est pas disponible) */
    .barcode-area {
        text-align: center;
        font-size: 7pt;
        border-bottom: 1px dashed #000;
        padding-bottom: 2mm;
        margin-bottom: 3mm;
    }
    .barcode-text {
        font-family: 'Courier New', monospace;
        font-size: 11pt;
        letter-spacing: 2px;
        font-weight: bold;
    }
    .barcode-label { font-size: 7pt; color: #333; }

    /* Zones expéditeur / destinataire */
    .layout { display: flex; gap: 4mm; height: 54mm; }

    .sender {
        width: 38mm;
        border-right: 1px dotted #999;
        padding-right: 3mm;
        font-size: 7.5pt;
    }
    .sender .sender-title {
        font-size: 6.5pt;
        text-transform: uppercase;
        color: #555;
        border-bottom: 1px solid #ccc;
        margin-bottom: 2mm;
        padding-bottom: 1mm;
    }
    .sender .sender-name { font-weight: bold; font-size: 8pt; }

    .recipient {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .recipient .to-label {
        font-size: 7pt;
        text-transform: uppercase;
        color: #555;
        margin-bottom: 2mm;
    }
    .recipient .recipient-name {
        font-size: 14pt;
        font-weight: bold;
        text-transform: uppercase;
        line-height: 1.2;
    }
    .recipient .recipient-addr1 { font-size: 10pt; margin-top: 2mm; }
    .recipient .recipient-addr2 { font-size: 10pt; }
    .recipient .recipient-addr3 { font-size: 10pt; }

    /* Pied de sticker */
    .sticker-footer {
        position: absolute;
        bottom: 3mm;
        left: 4mm;
        right: 4mm;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        border-top: 1px dashed #000;
        padding-top: 2mm;
    }
    .ref-block { font-size: 7.5pt; }
    .ref-block .ref-num { font-weight: bold; font-size: 9pt; }
    .qr-block { text-align: center; }
    .qr-block img { width: 18mm; height: 18mm; display: block; }
    .qr-block .qr-label { font-size: 6pt; color: #555; }

    @page { size: 148mm 105mm; margin: 3mm; }
    @media print { body { background: #fff; } }
</style>
</head>
<body>
<div class="sticker">

    {{-- Code-barres du numéro de document --}}
    <div class="barcode-area">
        <div class="barcode-text">|| {{ $document->number }} ||</div>
        <div class="barcode-label">Réf. expédition</div>
    </div>

    {{-- Expéditeur + Destinataire --}}
    <div class="layout">
        <div class="sender">
            <div class="sender-title">Expéditeur</div>
            <div class="sender-name">{{ $company->name }}</div>
            @if ($company->address)<div>{{ $company->address }}</div>@endif
            @if ($company->city)<div>{{ $company->city }}</div>@endif
            @if ($company->phone)<div>{{ $company->phone }}</div>@endif
        </div>

        <div class="recipient">
            <div class="to-label">Destinataire / Ship To :</div>
            <div class="recipient-name">{{ $customer?->name ?? 'CLIENT' }}</div>
            @if ($customer?->address)
                <div class="recipient-addr1">{{ $customer->address }}</div>
            @endif
            @if ($customer?->city)
                <div class="recipient-addr2">{{ $customer->city }}</div>
            @endif
            @if ($customer?->phone)
                <div class="recipient-addr3">Tél : {{ $customer->phone }}</div>
            @endif
        </div>
    </div>

    {{-- Pied : référence + QR --}}
    <div class="sticker-footer">
        <div class="ref-block">
            <div>Document : <span class="ref-num">{{ $document->number }}</span></div>
            <div>Date : {{ $document->issue_date?->format('d/m/Y') }}</div>
            <div>Type : {{ $document->type_label }}</div>
        </div>
        <div class="qr-block">
            <img src="{{ $qrDataUri }}" alt="QR vérification">
            <div class="qr-label">Vérifier</div>
        </div>
    </div>

</div>
</body>
</html>

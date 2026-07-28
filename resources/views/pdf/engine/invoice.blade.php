<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->number }}</title>
<style>
  @@page { margin: 14mm 13mm 18mm 13mm; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a2332; margin: 0; padding: 0; }
  table { border-collapse: collapse; }

  /* Bande couleur supérieure — visible sur chaque page */
  .page-header-band {
    position: fixed;
    top: -14mm;
    left: -13mm;
    right: -13mm;
    height: 6px;
    background: {{ $primaryColor }};
  }

  /* Pied de page paginé */
  .page-footer {
    position: fixed;
    bottom: -15mm;
    left: -13mm;
    right: -13mm;
    font-size: 7px;
    color: #9ca3af;
    border-top: 1px solid #e5e7eb;
    padding: 4px 13mm 0;
    text-align: center;
  }
  .page-footer .page-num:after { content: " — Page " counter(page) " / " counter(pages); }
</style>
</head>
<body>

<div class="page-header-band"></div>

@include('pdf.engine.blocks._watermark', ['watermark' => $watermark ?? null])

<div class="page-footer">
  <span>Document certifié — {{ $document->number }} — {{ now()->format('d/m/Y') }}</span>
  <span class="page-num"></span>
</div>

{{-- HEADER 2 colonnes --}}
<table style="width:100%;margin-bottom:14px;">
  <tr>
    <td style="width:55%;vertical-align:top;padding-right:12px;">
      @include('pdf.engine.blocks._company', ['company' => $company, 'primaryColor' => $primaryColor])
    </td>
    <td style="width:45%;vertical-align:top;">
      @include('pdf.engine.blocks._document-title', ['document' => $document, 'primaryColor' => $primaryColor])
    </td>
  </tr>
</table>

<hr style="border:none;border-top:2px solid {{ $primaryColor }};margin-bottom:14px;">

{{-- Bloc client + métadonnées --}}
@include('pdf.engine.blocks._client', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- Tableau lignes --}}
@include('pdf.engine.blocks._items-financial', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- Totaux + QR anti-falsification --}}
@include('pdf.engine.blocks._totals', ['document' => $document, 'primaryColor' => $primaryColor, 'qrDataUri' => $qrDataUri ?? null])

{{-- Paiement --}}
@include('pdf.engine.blocks._payment-info', ['company' => $company, 'document' => $document, 'primaryColor' => $primaryColor])

{{-- Notes --}}
@include('pdf.engine.blocks._notes', ['document' => $document])

{{-- Signatures --}}
@include('pdf.engine.blocks._signature', ['document' => $document, 'signatureLabels' => $signatureLabels ?? ['Émetteur', 'Destinataire']])

{{-- Conditions + mentions légales --}}
@include('pdf.engine.blocks._legal', ['company' => $company, 'document' => $document])

</body>
</html>

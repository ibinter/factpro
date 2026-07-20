<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->number }}</title>
<style>
  @@page { margin: 15mm; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a2332; margin: 0; padding: 0; }
  table { border-collapse: collapse; }
  .page-footer {
    position: fixed;
    bottom: -10mm;
    left: 0; right: 0;
    font-size: 7.5px;
    color: #9ca3af;
    border-top: 1px solid #e5e7eb;
    padding-top: 4px;
    text-align: center;
  }
  .page-footer .page-num:after { content: " — Page " counter(page) " / " counter(pages); }
</style>
</head>
<body>

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

{{-- Bloc client --}}
@include('pdf.engine.blocks._client', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- Tableau lignes --}}
@include('pdf.engine.blocks._items-financial', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- Totaux --}}
@include('pdf.engine.blocks._totals', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- Paiement --}}
@include('pdf.engine.blocks._payment-info', ['company' => $company, 'document' => $document, 'primaryColor' => $primaryColor])

{{-- QR --}}
@include('pdf.engine.blocks._qr-auth', ['qrDataUri' => $qrDataUri ?? null, 'document' => $document])

{{-- Notes --}}
@include('pdf.engine.blocks._notes', ['document' => $document])

{{-- Signatures --}}
@include('pdf.engine.blocks._signature', ['document' => $document, 'signatureLabels' => $signatureLabels ?? ['Émetteur', 'Destinataire']])

{{-- Conditions --}}
@include('pdf.engine.blocks._legal', ['company' => $company, 'document' => $document])

</body>
</html>

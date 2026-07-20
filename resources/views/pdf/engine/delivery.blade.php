<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->number }}</title>
<style>
  @@page { margin: 15mm; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a2332; margin: 0; padding: 0; }
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
  <span>Bon de livraison — {{ $document->number }} — {{ now()->format('d/m/Y') }}</span>
  <span class="page-num"></span>
</div>

{{-- HEADER --}}
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

{{-- Bannière --}}
<div style="background:{{ $primaryColor }};color:#ffffff;padding:10px 14px;border-radius:4px;margin-bottom:14px;">
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="font-size:14px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;">
        {{ $document->type_label ?? 'BON DE LIVRAISON' }}
      </td>
      <td style="text-align:right;font-size:11px;font-weight:bold;">
        N° {{ $document->number }}
      </td>
    </tr>
  </table>
</div>

{{-- Bloc destinataire --}}
@include('pdf.engine.blocks._client', ['document' => $document, 'primaryColor' => $primaryColor, 'clientLabel' => 'Destinataire'])

{{-- Tableau lignes sans prix --}}
@include('pdf.engine.blocks._items-delivery', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- Bloc transporteur --}}
<div style="border:1px solid #e5e7eb;border-radius:4px;padding:10px 14px;margin-bottom:14px;background:#fff7ed;">
  <div style="font-size:8.5px;font-weight:bold;color:{{ $primaryColor }};text-transform:uppercase;margin-bottom:8px;letter-spacing:0.5px;">
    Informations de livraison
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="width:33%;font-size:8.5px;color:#374151;padding:2px 0;">
        <span style="color:#6b7280;">Livreur :</span> {{ $document->delivery_person ?? '____________________' }}
      </td>
      <td style="width:33%;font-size:8.5px;color:#374151;padding:2px 0;text-align:center;">
        <span style="color:#6b7280;">Véhicule :</span> {{ $document->vehicle ?? '____________________' }}
      </td>
      <td style="width:33%;font-size:8.5px;color:#374151;padding:2px 0;text-align:right;">
        <span style="color:#6b7280;">Date :</span>
        @if(!empty($document->delivery_date))
          {{ \Carbon\Carbon::parse($document->delivery_date)->format('d/m/Y') }}
        @else
          ____________________
        @endif
      </td>
    </tr>
    <tr>
      <td style="font-size:8.5px;color:#374151;padding:4px 0;">
        <span style="color:#6b7280;">Heure départ :</span> ________
      </td>
      <td style="font-size:8.5px;color:#374151;padding:4px 0;text-align:center;">
        <span style="color:#6b7280;">Heure arrivée :</span> ________
      </td>
      <td style="font-size:8.5px;color:#374151;padding:4px 0;text-align:right;">
        <span style="color:#6b7280;">Km parcourus :</span> ________
      </td>
    </tr>
  </table>
</div>

{{-- Notes --}}
@include('pdf.engine.blocks._notes', ['document' => $document])

{{-- Signatures --}}
@include('pdf.engine.blocks._signature', ['document' => $document, 'signatureLabels' => $signatureLabels ?? ['Signature Livreur', 'Signature Destinataire']])

{{-- QR --}}
@include('pdf.engine.blocks._qr-auth', ['qrDataUri' => $qrDataUri ?? null, 'document' => $document])

</body>
</html>

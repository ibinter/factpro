<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->number }}</title>
<style>
  @page { margin: 15mm; }
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
  <span>Bon de commande — {{ $document->number }} — {{ now()->format('d/m/Y') }}</span>
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
<div style="background:{{ $primaryColor }};color:#ffffff;padding:10px 14px;border-radius:4px;margin-bottom:14px;text-align:center;">
  <div style="font-size:14px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;">BON DE COMMANDE</div>
</div>

{{-- Bloc Fournisseur (à la place du client) --}}
@include('pdf.engine.blocks._client', ['document' => $document, 'primaryColor' => $primaryColor, 'clientLabel' => 'Fournisseur'])

{{-- Tableau lignes avec prix --}}
@include('pdf.engine.blocks._items-financial', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- Totaux --}}
@include('pdf.engine.blocks._totals', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- Conditions commande --}}
<div style="border:1px solid #e5e7eb;border-radius:4px;padding:10px 14px;margin-bottom:14px;background:#faf5ff;">
  <div style="font-size:8.5px;font-weight:bold;color:{{ $primaryColor }};text-transform:uppercase;margin-bottom:6px;letter-spacing:0.5px;">
    Conditions de la commande
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="width:50%;font-size:8.5px;color:#374151;padding:2px 0;">
        <span style="color:#6b7280;">Conditions de paiement :</span>
        {{ $document->payment_terms ?? 'À réception de facture' }}
      </td>
      <td style="width:50%;font-size:8.5px;color:#374151;padding:2px 0;text-align:right;">
        <span style="color:#6b7280;">Délai de livraison :</span>
        @if(!empty($document->due_date))
          {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}
        @else
          {{ $document->delivery_delay ?? 'À convenir' }}
        @endif
      </td>
    </tr>
    @if(!empty($document->delivery_address))
      <tr>
        <td colspan="2" style="font-size:8.5px;color:#374151;padding-top:4px;">
          <span style="color:#6b7280;">Adresse de livraison :</span> {{ $document->delivery_address }}
        </td>
      </tr>
    @endif
  </table>
</div>

{{-- Notes --}}
@include('pdf.engine.blocks._notes', ['document' => $document])

{{-- Signatures --}}
<table style="width:100%;border-collapse:collapse;margin-top:20px;">
  <tr>
    <td style="width:50%;vertical-align:top;padding-right:10px;text-align:center;">
      <div style="border-top:1px solid #9ca3af;padding-top:6px;">
        <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;">Commandeur</div>
        <div style="font-size:8px;color:#9ca3af;margin-top:2px;">Signature et cachet</div>
        <div style="height:40px;border-bottom:1px dotted #d1d5db;margin:8px 4px 0;"></div>
        <div style="font-size:7.5px;color:#9ca3af;margin-top:4px;">Date : ________________</div>
      </div>
    </td>
    <td style="width:50%;vertical-align:top;text-align:center;">
      <div style="border-top:1px solid #9ca3af;padding-top:6px;">
        <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;">Bon pour réception</div>
        <div style="font-size:8px;color:#9ca3af;margin-top:2px;">
          Date livraison prévue :
          @if(!empty($document->due_date)){{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}@else________________@endif
        </div>
        <div style="height:40px;border-bottom:1px dotted #d1d5db;margin:8px 4px 0;"></div>
        <div style="font-size:7.5px;color:#9ca3af;margin-top:4px;">Signature fournisseur : ________________</div>
      </div>
    </td>
  </tr>
</table>

{{-- Conditions --}}
@include('pdf.engine.blocks._legal', ['company' => $company, 'document' => $document])

</body>
</html>

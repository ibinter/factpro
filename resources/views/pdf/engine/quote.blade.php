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
  <span>Devis — {{ $document->number }} — {{ now()->format('d/m/Y') }}</span>
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

{{-- Bandeau commercial --}}
<div style="background:{{ $primaryColor }};color:#ffffff;padding:10px 14px;border-radius:4px;margin-bottom:14px;text-align:center;">
  <div style="font-size:14px;font-weight:bold;letter-spacing:2px;text-transform:uppercase;">DEVIS COMMERCIAL</div>
  @if(!empty($document->due_date))
    <div style="font-size:9px;margin-top:4px;opacity:0.9;">
      Ce devis est valable jusqu'au {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}
    </div>
  @endif
</div>

{{-- Bloc client --}}
@include('pdf.engine.blocks._client', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- Tableau lignes --}}
@include('pdf.engine.blocks._items-financial', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- Totaux --}}
@include('pdf.engine.blocks._totals', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- Conditions de validité --}}
<div style="border:1px solid #e5e7eb;border-radius:4px;padding:10px 14px;margin-bottom:14px;background:#f0fdf4;">
  <div style="font-size:8.5px;font-weight:bold;color:{{ $primaryColor }};text-transform:uppercase;margin-bottom:6px;letter-spacing:0.5px;">
    Conditions de validité
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="font-size:8.5px;color:#374151;padding:2px 0;width:33%;">
        <span style="color:#6b7280;">Durée de validité :</span>
        @if(!empty($document->validity_days)){{ $document->validity_days }} jours@else 30 jours@endif
      </td>
      <td style="font-size:8.5px;color:#374151;padding:2px 0;width:33%;text-align:center;">
        <span style="color:#6b7280;">Conditions de règlement :</span>
        {{ $document->payment_terms ?? 'À réception' }}
      </td>
      <td style="font-size:8.5px;color:#374151;padding:2px 0;width:33%;text-align:right;">
        <span style="color:#6b7280;">Délai d'exécution :</span>
        {{ $document->delivery_delay ?? 'À convenir' }}
      </td>
    </tr>
  </table>
</div>

{{-- Notes --}}
@include('pdf.engine.blocks._notes', ['document' => $document])

{{-- Section acceptation --}}
<div style="border:2px solid {{ $primaryColor }};border-radius:4px;padding:14px;margin-top:16px;">
  <div style="font-size:10px;font-weight:bold;color:{{ $primaryColor }};margin-bottom:10px;text-align:center;">
    BON POUR ACCORD
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="width:50%;vertical-align:top;padding-right:10px;">
        <div style="font-size:8.5px;color:#374151;">
          <div>Date de signature : _____________________</div>
          <div style="margin-top:6px;">Lieu : _____________________</div>
          <div style="margin-top:6px;font-size:7.5px;color:#6b7280;">Mention obligatoire : "Bon pour accord"</div>
        </div>
      </td>
      <td style="width:50%;vertical-align:top;text-align:center;">
        <div style="font-size:8.5px;font-weight:bold;color:#374151;margin-bottom:4px;">Signature et cachet client</div>
        <div style="height:50px;border:1px dashed #9ca3af;border-radius:3px;"></div>
      </td>
    </tr>
  </table>
</div>

{{-- Conditions --}}
@include('pdf.engine.blocks._legal', ['company' => $company, 'document' => $document])

</body>
</html>

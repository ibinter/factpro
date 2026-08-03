<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->number }}</title>
<style>
  @@page { margin: 38mm 13mm 24mm 13mm; }
  * { box-sizing: border-box; }
  body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 9.5px;
    color: #1a2332;
    margin: 0; padding: 0;
    line-height: 1.5;
  }

  #hdr {
    position: fixed;
    top: -38mm;
    left: -13mm;
    right: -13mm;
    height: 36mm;
    background: #ffffff;
  }
  #hdr-inner { height: 36mm; }
  #hdr-logo-cell {
    width: 52%;
    padding: 6mm 4mm 5mm 13mm;
    vertical-align: middle;
    border-bottom: 3px solid {{ $primaryColor }};
  }
  #hdr-doc-cell {
    width: 48%;
    background: {{ $primaryColor }};
    padding: 5mm 15mm 5mm 6mm;
    vertical-align: middle;
    text-align: right;
    border-bottom: 3px solid {{ $primaryColor }};
  }

  #ftr {
    position: fixed;
    bottom: -22mm;
    left: -13mm;
    right: -13mm;
    height: 20mm;
    background: #f8f9fb;
    border-top: 2px solid {{ $primaryColor }};
    padding: 3mm 13mm 0;
  }

  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  .items-table thead tr { background: {{ $primaryColor }}; color: #ffffff; }
  .items-table thead th { padding: 6px 8px; font-size: 7px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.6px; }
  .items-table tbody tr { border-bottom: 1px solid #eef0f3; }
  .items-table tbody tr.alt { background: #f8f9fb; }
  .items-table tbody td { padding: 7px 8px; font-size: 9px; vertical-align: top; }
  .items-table tfoot tr { background: #1a2332; }
  .items-table tfoot td { padding: 7px 8px; font-size: 9.5px; font-weight: bold; color: #ffffff; text-align: right; }
</style>
</head>
<body>
@include('pdf.engine.blocks._watermark', ['watermark' => $watermark ?? null])

{{-- EN-TÊTE --}}
<div id="hdr">
  <table id="hdr-inner" style="width:100%;height:36mm;border-collapse:collapse;">
    <tr>
      <td id="hdr-logo-cell">
        @if(!empty($logoBase64))
          <img src="{{ $logoBase64 }}" style="max-height:14mm;max-width:36mm;display:block;margin-bottom:3mm;">
        @endif
        <div style="font-size:14px;font-weight:bold;color:{{ $primaryColor }};line-height:1.1;">{{ $company->name }}</div>
        @if(!empty($company->tagline))
          <div style="font-size:7.5px;color:#6b7280;margin-top:1px;font-style:italic;">{{ $company->tagline }}</div>
        @endif
        <div style="font-size:7.5px;color:#374151;margin-top:2.5mm;line-height:1.7;">
          @if(!empty($company->address)){{ $company->address }}<br>@endif
          @if(!empty($company->city)){{ $company->city }}
@if(!empty($company->country)) · {{ $company->country }}
@endif<br>@endif
          @if(!empty($company->phone))Tél : {{ $company->phone }}
@if(!empty($company->email))  ·  {{ $company->email }}
@endif<br>@elseif(!empty($company->email)){{ $company->email }}<br>@endif
        </div>
      </td>
      <td id="hdr-doc-cell">
        <div style="font-size:9px;font-weight:bold;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:1mm;">BON DE COMMANDE</div>
        <div style="font-size:20px;font-weight:bold;color:#ffffff;line-height:1;letter-spacing:-0.5px;margin-bottom:2.5mm;">{{ $document->number }}</div>
        <div style="font-size:8px;color:rgba(255,255,255,0.85);line-height:2;">
          @if(!empty($document->issue_date))
            <span style="opacity:0.75;">Date :</span>&nbsp; {{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}<br>
          @endif
          @if(!empty($document->due_date))
            <span style="opacity:0.75;">Livraison :</span>&nbsp; {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}<br>
          @endif
          @if(!empty($document->reference))
            <span style="opacity:0.75;">Réf :</span>&nbsp; {{ $document->reference }}
          @endif
        </div>
      </td>
    </tr>
  </table>
</div>

{{-- PIED DE PAGE --}}
<div id="ftr">
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="font-size:6.5px;color:#6b7280;line-height:1.7;vertical-align:top;">
        <strong style="color:#374151;">{{ $company->name }}</strong>
        @if(!empty($company->address)) · {{ $company->address }}@endif
        @if(!empty($company->city)), {{ $company->city }}@endif
        @php $ftrIds = array_filter([
          !empty($company->rccm) ? 'RCCM '.$company->rccm : null,
          !empty($company->tax_id) ? 'N°Fiscal '.$company->tax_id : null,
          !empty($company->tax_number) ? 'N°Fiscal '.$company->tax_number : null,
        ]); @endphp
        @if(count($ftrIds)) · {{ implode(' · ', $ftrIds) }}@endif
      </td>
      <td style="font-size:6.5px;color:#9ca3af;text-align:right;vertical-align:top;white-space:nowrap;">
        Document certifié · IBIG FactPro<br>
        {{ $document->number }}
      </td>
    </tr>
  </table>
</div>

{{-- CORPS --}}

{{-- Fournisseur + conditions --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
  <tr>
    <td style="width:44%;vertical-align:top;padding-right:12px;">
      <div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:3px;padding:8px 10px;">
        <div style="font-size:7px;color:#7c3aed;text-transform:uppercase;letter-spacing:0.5px;font-weight:bold;margin-bottom:5px;">Conditions de commande</div>
        <div style="font-size:8px;color:#374151;line-height:1.9;">
          <span style="color:#6b7280;">Paiement :</span> {{ $document->payment_terms ?? 'À réception de facture' }}<br>
          @if(!empty($document->delivery_address))
            <span style="color:#6b7280;">Livraison :</span> {{ $document->delivery_address }}<br>
          @endif
          @php $curr = $document->currency ?? ''; @endphp
          @if(!empty($curr))<span style="color:#6b7280;">Devise :</span> {{ $curr }}@endif
        </div>
      </div>
    </td>
    <td style="width:56%;vertical-align:top;">
      <div style="border:1px solid #e5e7eb;border-top:3px solid {{ $primaryColor }};border-radius:3px;padding:8px 12px;background:#ffffff;">
        <div style="font-size:7px;font-weight:bold;text-transform:uppercase;letter-spacing:0.8px;color:#9ca3af;margin-bottom:5px;">Fournisseur</div>
        @if($document->customer)
          <div style="font-size:12px;font-weight:bold;color:#111827;line-height:1.1;margin-bottom:3px;">
            {{ $document->customer->name ?? $document->customer->company_name ?? '—' }}
          </div>
          <div style="font-size:8px;color:#374151;line-height:1.9;">
            @if(!empty($document->customer->address)){{ $document->customer->address }}<br>@endif
            @if(!empty($document->customer->city)){{ $document->customer->city }}
@if(!empty($document->customer->country)) · {{ $document->customer->country }}
@endif<br>@endif
            @if(!empty($document->customer->phone))<span style="color:#9ca3af;">Tél :</span> {{ $document->customer->phone }}<br>@endif
            @if(!empty($document->customer->email))<span style="color:#9ca3af;">Email :</span> {{ $document->customer->email }}<br>@endif
          </div>
        @else
          <div style="font-size:9px;color:#9ca3af;font-style:italic;">— Non renseigné —</div>
        @endif
      </div>
    </td>
  </tr>
</table>

{{-- Tableau lignes --}}
@include('pdf.engine.blocks._items-financial', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- Totaux --}}
@include('pdf.engine.blocks._totals', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- Notes --}}
@include('pdf.engine.blocks._notes', ['document' => $document])

{{-- Signatures --}}
<div style="margin-top:30px;page-break-inside:avoid;">
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="width:50%;vertical-align:top;padding:0 14px 0 0;text-align:center;">
        <div style="border:1px solid #d1d5db;border-radius:5px;padding:14px 16px 12px;background:#fafafa;">
          <div style="font-size:9.5px;font-weight:bold;color:#1a2332;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Commandeur</div>
          <div style="font-size:7.5px;color:#9ca3af;margin-bottom:14px;">Date : _____________________</div>
          <div style="height:120px;"></div>
          <div style="border-top:1px solid #9ca3af;padding-top:6px;">
            <div style="font-size:7px;color:#9ca3af;">Signature et cachet</div>
          </div>
        </div>
      </td>
      <td style="width:50%;vertical-align:top;padding:0 0 0 14px;text-align:center;">
        <div style="border:1px solid #d1d5db;border-radius:5px;padding:14px 16px 12px;background:#fafafa;">
          <div style="font-size:9.5px;font-weight:bold;color:#1a2332;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Bon pour réception</div>
          <div style="font-size:7.5px;color:#9ca3af;margin-bottom:14px;">Date livraison : {{ !empty($document->due_date) ? \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') : '_____________' }}</div>
          <div style="height:120px;"></div>
          <div style="border-top:1px solid #9ca3af;padding-top:6px;">
            <div style="font-size:7px;color:#9ca3af;">Signature fournisseur</div>
          </div>
        </div>
      </td>
    </tr>
  </table>
</div>

{{-- Légal --}}
@include('pdf.engine.blocks._legal', ['company' => $company, 'document' => $document])

</body>
</html>

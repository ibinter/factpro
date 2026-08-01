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

  /* ── EN-TÊTE FIXE ────────────────────────────────────────── */
  #hdr {
    position: fixed;
    top: -38mm;
    left: -13mm;
    right: -13mm;
    height: 36mm;
    background: #ffffff;
    border-bottom: 3px solid {{ $primaryColor }};
  }
  #hdr-inner { height: 36mm; }
  #hdr-logo-cell {
    width: 52%;
    padding: 6mm 4mm 5mm 13mm;
    vertical-align: middle;
  }
  #hdr-doc-cell {
    width: 48%;
    background: {{ $primaryColor }};
    padding: 5mm 13mm 5mm 6mm;
    vertical-align: middle;
    text-align: right;
  }

  /* ── PIED DE PAGE FIXE ───────────────────────────────────── */
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

  /* ── TABLEAU LIGNES ─────────────────────────────────────── */
  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  .items-table thead tr { background: {{ $primaryColor }}; color: #ffffff; }
  .items-table thead th {
    padding: 6px 8px;
    font-size: 7px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.6px;
  }
  .items-table tbody tr { border-bottom: 1px solid #eef0f3; }
  .items-table tbody tr.alt { background: #f8f9fb; }
  .items-table tbody td { padding: 7px 8px; font-size: 9px; vertical-align: top; }

  /* ── LIGNE DE SÉPARATION ────────────────────────────────── */
  .divider { border: none; border-top: 1px solid #e5e7eb; margin: 10px 0; }
</style>
</head>
<body>
<!-- DELIVERY-REDESIGN-V1 -->
@include('pdf.engine.blocks._watermark', ['watermark' => $watermark ?? null])

{{-- ═══════════════════════════════════════════════════════
     EN-TÊTE FIXE
     ═══════════════════════════════════════════════════════ --}}
<div id="hdr">
  <table id="hdr-inner" style="width:100%;height:36mm;border-collapse:collapse;">
    <tr>
      <td id="hdr-logo-cell">
        @if(!empty($logoBase64))
          <img src="{{ $logoBase64 }}"
               style="max-height:14mm;max-width:36mm;display:block;margin-bottom:3mm;">
        @endif
        <div style="font-size:14px;font-weight:bold;color:{{ $primaryColor }};line-height:1.1;letter-spacing:-0.3px;">
          {{ $company->name }}
        </div>
        @if(!empty($company->tagline))
          <div style="font-size:7.5px;color:#6b7280;margin-top:1px;font-style:italic;">{{ $company->tagline }}</div>
        @endif
        <div style="font-size:7.5px;color:#374151;margin-top:2.5mm;line-height:1.7;">
          @if(!empty($company->address)){{ $company->address }}<br>@endif
          @if(!empty($company->city)){{ $company->city }}
@if(!empty($company->postal_code)) {{ $company->postal_code }}
@endif
          @if(!empty($company->country)) · {{ $company->country }}
@endif<br>@endif
          @if(!empty($company->phone))Tél : {{ $company->phone }}
@if(!empty($company->email))  ·  {{ $company->email }}
@endif<br>@elseif(!empty($company->email)){{ $company->email }}<br>@endif
        </div>
      </td>
      <td id="hdr-doc-cell">
        <div style="font-size:9px;font-weight:bold;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:1mm;">
          BON DE LIVRAISON
        </div>
        <div style="font-size:20px;font-weight:bold;color:#ffffff;line-height:1;letter-spacing:-0.5px;margin-bottom:2.5mm;">
          {{ $document->number }}
        </div>
        <div style="font-size:8px;color:rgba(255,255,255,0.85);line-height:2;">
          @if(!empty($document->issue_date))
            <span style="opacity:0.75;">Date :</span>&nbsp; {{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}<br>
          @endif
          @if(!empty($document->delivery_date))
            <span style="opacity:0.75;">Livraison :</span>&nbsp; {{ \Carbon\Carbon::parse($document->delivery_date)->format('d/m/Y') }}<br>
          @endif
          @if(!empty($document->reference))
            <span style="opacity:0.75;">Réf :</span>&nbsp; {{ $document->reference }}
          @endif
        </div>
      </td>
    </tr>
  </table>
</div>

{{-- ═══════════════════════════════════════════════════════
     PIED DE PAGE FIXE
     ═══════════════════════════════════════════════════════ --}}
<div id="ftr">
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="font-size:6.5px;color:#6b7280;line-height:1.7;vertical-align:top;">
        <strong style="color:#374151;">{{ $company->name }}</strong>
        @if(!empty($company->address)) · {{ $company->address }}
@endif
        @if(!empty($company->city)), {{ $company->city }}
@endif
        @php
          $ftrIds = array_filter([
            !empty($company->trade_register) ? 'RCCM '.$company->trade_register : null,
            !empty($company->rccm)           ? 'RCCM '.$company->rccm           : null,
            !empty($company->tax_id)         ? 'N°Fiscal '.$company->tax_id     : null,
            !empty($company->tax_number)     ? 'N°Fiscal '.$company->tax_number : null,
          ]);
        @endphp
        @if(count($ftrIds)) · {{ implode(' · ', $ftrIds) }}
@endif
      </td>
      <td style="font-size:6.5px;color:#9ca3af;text-align:right;vertical-align:top;white-space:nowrap;">
        Document certifié · IBIG FactPro<br>
        Page {{ $document->number }}
      </td>
    </tr>
  </table>
</div>

{{-- ═══════════════════════════════════════════════════════
     CORPS DU DOCUMENT
     ═══════════════════════════════════════════════════════ --}}

{{-- ── BLOC DESTINATAIRE ────────────────────────────────── --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:12px;">
  <tr>
    {{-- Infos livraison --}}
    <td style="width:44%;vertical-align:top;padding-right:12px;">
      <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:3px;padding:8px 10px;">
        <div style="font-size:7px;color:#c2410c;text-transform:uppercase;letter-spacing:0.5px;font-weight:bold;margin-bottom:5px;">
          Informations de livraison
        </div>
        <div style="font-size:8px;color:#374151;line-height:1.9;">
          <span style="color:#6b7280;">Livreur :</span> {{ $document->delivery_person ?? '____________________' }}<br>
          <span style="color:#6b7280;">Véhicule :</span> {{ $document->vehicle ?? '____________________' }}<br>
          <span style="color:#6b7280;">Heure départ :</span> ________
          &nbsp;<span style="color:#6b7280;">Arrivée :</span> ________
        </div>
      </div>
    </td>

    {{-- Destinataire --}}
    <td style="width:56%;vertical-align:top;">
      <div style="border:1px solid #e5e7eb;border-top:3px solid {{ $primaryColor }};border-radius:3px;padding:8px 12px;background:#ffffff;">
        <div style="font-size:7px;font-weight:bold;text-transform:uppercase;letter-spacing:0.8px;color:#9ca3af;margin-bottom:5px;">
          Destinataire
        </div>
        @if($document->customer)
          <div style="font-size:12px;font-weight:bold;color:#111827;line-height:1.1;margin-bottom:3px;">
            {{ $document->customer->name ?? $document->customer->company_name ?? '—' }}
          </div>
          @if(!empty($document->customer->company_name) && !empty($document->customer->name) && $document->customer->company_name !== $document->customer->name)
            <div style="font-size:8px;color:#374151;margin-bottom:4px;">{{ $document->customer->company_name }}</div>
          @endif
          <div style="font-size:8px;color:#374151;line-height:1.9;">
            @if(!empty($document->customer->address)){{ $document->customer->address }}<br>@endif
            @if(!empty($document->customer->city)){{ $document->customer->city }}
@if(!empty($document->customer->postal_code)) {{ $document->customer->postal_code }}
@endif
            @if(!empty($document->customer->country)) · {{ $document->customer->country }}
@endif<br>@endif
            @if(!empty($document->customer->phone))<span style="color:#9ca3af;">Tél :</span> {{ $document->customer->phone }}<br>@endif
          </div>
        @else
          <div style="font-size:9px;color:#9ca3af;font-style:italic;">— Non renseigné —</div>
        @endif
      </div>
    </td>
  </tr>
</table>

{{-- ── TABLEAU DES LIGNES ───────────────────────────────── --}}
@include('pdf.engine.blocks._items-delivery', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- ── NOTES ────────────────────────────────────────────── --}}
@include('pdf.engine.blocks._notes', ['document' => $document])

{{-- ── SIGNATURES ───────────────────────────────────────── --}}
@include('pdf.engine.blocks._signature', ['document' => $document, 'signatureLabels' => $signatureLabels ?? ['Signature Livreur', 'Signature Destinataire']])

{{-- ── QR ──────────────────────────────────────────────── --}}
@include('pdf.engine.blocks._qr-auth', ['qrDataUri' => $qrDataUri ?? null, 'document' => $document])

</body>
</html>

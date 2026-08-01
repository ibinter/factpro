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
  #hdr-inner {
    height: 36mm;
  }
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
  .items-table tfoot tr { background: #1a2332; }
  .items-table tfoot td {
    padding: 7px 8px;
    font-size: 9.5px;
    font-weight: bold;
    color: #ffffff;
    text-align: right;
  }

  /* ── SECTION TITRE ──────────────────────────────────────── */
  .section-lbl {
    font-size: 7px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: {{ $primaryColor }};
    margin-bottom: 5px;
    padding-bottom: 3px;
    border-bottom: 1px solid {{ $primaryColor }};
  }

  /* ── LIGNE DE SÉPARATION ────────────────────────────────── */
  .divider { border: none; border-top: 1px solid #e5e7eb; margin: 10px 0; }
</style>
</head>
<body>
<!-- QUOTE-REDESIGN-V1 -->
@include('pdf.engine.blocks._watermark', ['watermark' => $watermark ?? null])

{{-- ═══════════════════════════════════════════════════════
     EN-TÊTE : logo + société | badge document
     ═══════════════════════════════════════════════════════ --}}
<div id="hdr">
  <table id="hdr-inner" style="width:100%;height:36mm;border-collapse:collapse;">
    <tr>
      {{-- Colonne gauche : logo + société --}}
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
        @elseif(!empty($company->legal_name) && $company->legal_name !== $company->name)
          <div style="font-size:7.5px;color:#6b7280;margin-top:1px;">{{ $company->legal_name }}</div>
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

      {{-- Colonne droite : identité du document --}}
      <td id="hdr-doc-cell">
        <div style="font-size:9px;font-weight:bold;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:1mm;">
          DEVIS
        </div>
        <div style="font-size:20px;font-weight:bold;color:#ffffff;line-height:1;letter-spacing:-0.5px;margin-bottom:2.5mm;">
          {{ $document->number }}
        </div>
        <div style="font-size:8px;color:rgba(255,255,255,0.85);line-height:2;">
          @if(!empty($document->issue_date))
            <span style="opacity:0.75;">Émission :</span>&nbsp; {{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}<br>
          @endif
          @if(!empty($document->due_date))
            <span style="opacity:0.75;">Valide jusqu'au :</span>&nbsp; {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}<br>
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
            !empty($company->capital)        ? 'Capital '.$company->capital     : null,
          ]);
        @endphp
        @if(count($ftrIds)) · {{ implode(' · ', $ftrIds) }}
@endif
      </td>
      <td style="font-size:6.5px;color:#9ca3af;text-align:right;vertical-align:top;white-space:nowrap;">
        Document certifié · IBIG FactPro<br>
        <span style="content:counter(page);">Page </span><span style="font-weight:bold;color:#374151;"></span>
        <span style="font-size:6px;"> · </span>{{ $document->number }}
      </td>
    </tr>
  </table>
</div>

{{-- ═══════════════════════════════════════════════════════
     CORPS DU DOCUMENT
     ═══════════════════════════════════════════════════════ --}}

{{-- ── BLOC CLIENT + INFOS ──────────────────────────────── --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:12px;">
  <tr>

    {{-- Informations complémentaires du document --}}
    <td style="width:44%;vertical-align:top;padding-right:12px;">
      @if(!empty($document->subject) || !empty($document->object) || !empty($document->project_name))
      <div style="background:#f8f9fb;border-left:3px solid #e5e7eb;padding:7px 10px;margin-bottom:8px;">
        @if(!empty($document->subject) || !empty($document->object))
          <div style="font-size:7px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.5px;">Objet</div>
          <div style="font-size:8.5px;color:#1a2332;font-weight:600;margin-top:1px;">{{ $document->subject ?? $document->object }}</div>
        @endif
        @if(!empty($document->project_name))
          <div style="font-size:7px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px;">Projet</div>
          <div style="font-size:8.5px;color:#1a2332;margin-top:1px;">{{ $document->project_name }}</div>
        @endif
      </div>
      @endif

      {{-- Conditions de validité --}}
      <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:3px;padding:8px 10px;">
        <div style="font-size:7px;color:#15803d;text-transform:uppercase;letter-spacing:0.5px;font-weight:bold;margin-bottom:5px;">
          Conditions de validité
        </div>
        <div style="font-size:8px;color:#374151;line-height:1.9;">
          <span style="color:#6b7280;">Durée :</span>
          {{ !empty($document->validity_days) ? $document->validity_days.' jours' : '30 jours' }}<br>
          <span style="color:#6b7280;">Règlement :</span>
          {{ $document->payment_terms ?? 'À réception' }}<br>
          @if(!empty($document->delivery_delay))
            <span style="color:#6b7280;">Livraison :</span> {{ $document->delivery_delay }}<br>
          @endif
          @php $curr = $document->currency ?? ''; @endphp
          @if(!empty($curr))
            <span style="color:#6b7280;">Devise :</span> {{ $curr }}
          @endif
        </div>
      </div>
    </td>

    {{-- Destinataire --}}
    <td style="width:56%;vertical-align:top;">
      <div style="border:1px solid #e5e7eb;border-top:3px solid {{ $primaryColor }};border-radius:3px;padding:8px 12px;background:#ffffff;">
        <div style="font-size:7px;font-weight:bold;text-transform:uppercase;letter-spacing:0.8px;color:#9ca3af;margin-bottom:5px;">
          Adressé à
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
            @if(!empty($document->customer->email))<span style="color:#9ca3af;">Email :</span> {{ $document->customer->email }}<br>@endif
            @if(!empty($document->customer->tax_number))<span style="color:#9ca3af;">N° fiscal :</span> {{ $document->customer->tax_number }}
@endif
          </div>
        @else
          <div style="font-size:9px;color:#9ca3af;font-style:italic;">— Non renseigné —</div>
        @endif
      </div>
    </td>

  </tr>
</table>

{{-- ── TABLEAU DES LIGNES ───────────────────────────────── --}}
@include('pdf.engine.blocks._items-financial', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- ── TOTAUX ────────────────────────────────────────────── --}}
@include('pdf.engine.blocks._totals', ['document' => $document, 'primaryColor' => $primaryColor])

{{-- ── NOTES ────────────────────────────────────────────── --}}
@include('pdf.engine.blocks._notes', ['document' => $document])

{{-- ── BON POUR ACCORD ──────────────────────────────────── --}}
<div style="border:2px solid {{ $primaryColor }};border-radius:4px;padding:14px;margin-top:16px;page-break-inside:avoid;">
  <div style="font-size:10px;font-weight:bold;color:{{ $primaryColor }};margin-bottom:10px;text-align:center;text-transform:uppercase;letter-spacing:1px;">
    Bon pour accord
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

{{-- ── MENTIONS LÉGALES ─────────────────────────────────── --}}
@include('pdf.engine.blocks._legal', ['company' => $company, 'document' => $document])

</body>
</html>

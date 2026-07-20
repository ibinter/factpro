<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->number }}</title>
<style>
  @@page { margin: 15mm; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #1a2332; margin: 0; padding: 0; }
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
  <span>{{ $document->type_label ?? 'État des lieux' }} — {{ $document->number }}</span>
  <span class="page-num"></span>
</div>

{{-- En-tête --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
  <tr>
    <td style="width:50%;vertical-align:top;padding-right:12px;">
      <div style="border:1px solid #e5e7eb;border-left:3px solid {{ $primaryColor }};border-radius:3px;padding:10px 12px;background:#f9fafb;">
        <div style="font-size:8px;text-transform:uppercase;color:#6b7280;margin-bottom:4px;">Bailleur / Propriétaire</div>
        <div style="font-size:11px;font-weight:bold;">{{ $company->name ?? '' }}</div>
        <div style="font-size:8.5px;color:#374151;margin-top:3px;line-height:1.6;">
          @if(!empty($company->address)){{ $company->address }}<br>@endif
          @if(!empty($company->phone))Tél : {{ $company->phone }}<br>@endif
          @if(!empty($company->email)){{ $company->email }}@endif
        </div>
      </div>
    </td>
    <td style="width:50%;vertical-align:top;">
      <div style="border:1px solid #e5e7eb;border-left:3px solid #6b7280;border-radius:3px;padding:10px 12px;background:#f9fafb;">
        <div style="font-size:8px;text-transform:uppercase;color:#6b7280;margin-bottom:4px;">Locataire / Occupant</div>
        @if($document->customer)
          <div style="font-size:11px;font-weight:bold;">{{ $document->customer->name ?? '' }}</div>
          <div style="font-size:8.5px;color:#374151;margin-top:3px;line-height:1.6;">
            @if(!empty($document->customer->address)){{ $document->customer->address }}<br>@endif
            @if(!empty($document->customer->phone))Tél : {{ $document->customer->phone }}<br>@endif
            @if(!empty($document->customer->email)){{ $document->customer->email }}@endif
          </div>
        @else
          <div style="font-size:9px;color:#9ca3af;font-style:italic;">— À renseigner —</div>
        @endif
      </div>
    </td>
  </tr>
</table>

{{-- Titre --}}
<div style="text-align:center;background:{{ $primaryColor }};color:#ffffff;padding:10px;border-radius:4px;margin-bottom:14px;">
  <div style="font-size:14px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">
    {{ $document->type_label ?? 'ÉTAT DES LIEUX' }}
  </div>
  <div style="font-size:9px;margin-top:3px;opacity:0.9;">
    {{ $document->subject ?? $document->reference ?? '' }}
    — {{ !empty($document->issue_date) ? \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') : '' }}
  </div>
</div>

{{-- Infos bien --}}
<div style="border:1px solid #e5e7eb;border-radius:4px;padding:10px 14px;margin-bottom:14px;background:#f0fdfa;">
  <div style="font-size:8.5px;font-weight:bold;color:{{ $primaryColor }};text-transform:uppercase;margin-bottom:6px;letter-spacing:0.5px;">
    Identification du bien
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="font-size:8.5px;width:50%;padding-right:10px;">
        <span style="color:#6b7280;">Adresse :</span> {{ $document->property_address ?? '____________________' }}
      </td>
      <td style="font-size:8.5px;width:25%;">
        <span style="color:#6b7280;">Type :</span> {{ $document->property_type ?? '____________________' }}
      </td>
      <td style="font-size:8.5px;width:25%;text-align:right;">
        <span style="color:#6b7280;">Surface :</span> {{ $document->property_surface ?? '____' }} m²
      </td>
    </tr>
  </table>
</div>

{{-- Tableau état des équipements --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
  <thead>
    <tr style="background:{{ $primaryColor }};color:#ffffff;">
      <th style="padding:6px 10px;text-align:left;font-size:8px;text-transform:uppercase;width:25%;">Pièce / Élément</th>
      <th style="padding:6px 10px;text-align:left;font-size:8px;text-transform:uppercase;width:30%;">Description / Équipement</th>
      <th style="padding:6px 10px;text-align:center;font-size:8px;text-transform:uppercase;width:15%;">État entrée</th>
      <th style="padding:6px 10px;text-align:center;font-size:8px;text-transform:uppercase;width:15%;">État sortie</th>
      <th style="padding:6px 10px;text-align:left;font-size:8px;text-transform:uppercase;width:15%;">Observations</th>
    </tr>
  </thead>
  <tbody>
    @forelse($document->lines ?? [] as $i => $line)
      <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#f0fdfa' }};border-bottom:1px solid #e5e7eb;">
        <td style="padding:6px 10px;font-size:9px;font-weight:bold;">{{ $line->unit ?? $line->reference ?? '' }}</td>
        <td style="padding:6px 10px;font-size:9px;">{{ $line->description ?? $line->label ?? '' }}</td>
        <td style="padding:6px 10px;text-align:center;font-size:8.5px;">{{ $line->state_in ?? '________' }}</td>
        <td style="padding:6px 10px;text-align:center;font-size:8.5px;">{{ $line->state_out ?? '________' }}</td>
        <td style="padding:6px 10px;font-size:8.5px;color:#6b7280;font-style:italic;">{{ $line->note ?? '' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="5" style="padding:14px;text-align:center;color:#9ca3af;font-style:italic;">Aucun élément enregistré</td>
      </tr>
    @endforelse
  </tbody>
</table>

{{-- Réserves --}}
<div style="border:1px solid #fbbf24;background:#fffbeb;border-radius:4px;padding:10px 14px;margin-bottom:16px;">
  <div style="font-size:8.5px;font-weight:bold;color:#92400e;text-transform:uppercase;margin-bottom:4px;letter-spacing:0.5px;">
    Réserves formulées
  </div>
  <div style="font-size:8.5px;color:#374151;min-height:30px;line-height:1.7;">
    @if(!empty($document->notes)){!! nl2br(e($document->notes)) !!}@else<span style="color:#9ca3af;font-style:italic;">Aucune réserve</span>@endif
  </div>
</div>

{{-- Signatures --}}
<table style="width:100%;border-collapse:collapse;margin-top:16px;">
  <tr>
    <td style="width:50%;text-align:center;padding-right:10px;">
      <div style="border-top:1px solid #9ca3af;padding-top:6px;">
        <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;">Le Bailleur</div>
        <div style="height:40px;border-bottom:1px dotted #d1d5db;margin:8px 4px 0;"></div>
        <div style="font-size:7.5px;color:#9ca3af;margin-top:4px;">Date et signature</div>
      </div>
    </td>
    <td style="width:50%;text-align:center;">
      <div style="border-top:1px solid #9ca3af;padding-top:6px;">
        <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;">Le Locataire</div>
        <div style="height:40px;border-bottom:1px dotted #d1d5db;margin:8px 4px 0;"></div>
        <div style="font-size:7.5px;color:#9ca3af;margin-top:4px;">Date et signature</div>
      </div>
    </td>
  </tr>
</table>

</body>
</html>

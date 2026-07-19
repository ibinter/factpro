<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->number }}</title>
<style>
  @page { margin: 20mm; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a2332; margin: 0; padding: 0; }
  .page-footer {
    position: fixed;
    bottom: -14mm;
    left: 0; right: 0;
    font-size: 7.5px;
    color: #9ca3af;
    border-top: 1px solid #e5e7eb;
    padding-top: 4px;
    text-align: center;
  }
  .page-footer .page-num:after { content: "Page " counter(page) " / " counter(pages); }
  .section { margin-bottom: 14px; }
  .section-title { font-size: 9.5px; font-weight: bold; text-transform: uppercase; color: {{ $primaryColor }}; border-bottom: 1px solid {{ $primaryColor }}; padding-bottom: 3px; margin-bottom: 8px; }
  .section-body { font-size: 9px; line-height: 1.7; color: #374151; }
</style>
</head>
<body>

@include('pdf.engine.blocks._watermark', ['watermark' => $watermark ?? null])

<div class="page-footer">
  <span>{{ $document->type_label ?? 'Procès-Verbal' }} — {{ $document->number }}</span>
  <span class="page-num"></span>
</div>

{{-- En-tête institutionnel centré --}}
<div style="text-align:center;margin-bottom:16px;">
  @if(!empty($company->logo_url))
    <img src="{{ $company->logo_url }}" style="max-height:50px;max-width:120px;margin-bottom:6px;"><br>
  @endif
  <div style="font-size:13px;font-weight:bold;color:{{ $primaryColor }};">{{ $company->name ?? '' }}</div>
  @if(!empty($company->address))
    <div style="font-size:8.5px;color:#6b7280;">{{ $company->address }}@if(!empty($company->city)), {{ $company->city }}@endif</div>
  @endif
</div>

<hr style="border:none;border-top:2px solid {{ $primaryColor }};margin-bottom:16px;">

{{-- Titre principal --}}
<div style="text-align:center;margin-bottom:16px;">
  <div style="font-size:18px;font-weight:bold;text-transform:uppercase;letter-spacing:2px;color:{{ $primaryColor }};">
    {{ $document->type_label ?? 'PROCÈS-VERBAL' }}
  </div>
  <div style="font-size:10px;color:#374151;margin-top:4px;">N° {{ $document->number }}</div>
</div>

{{-- Infos générales --}}
<div class="section">
  <div class="section-title">Informations générales</div>
  <table style="width:100%;border-collapse:collapse;background:#f9fafb;border:1px solid #e5e7eb;border-radius:4px;">
    <tr>
      <td style="padding:6px 10px;font-size:8.5px;width:33%;border-right:1px solid #e5e7eb;">
        <span style="color:#6b7280;display:block;margin-bottom:2px;">Date</span>
        <strong>@if(!empty($document->issue_date)){{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}@else________________@endif</strong>
      </td>
      <td style="padding:6px 10px;font-size:8.5px;width:33%;border-right:1px solid #e5e7eb;text-align:center;">
        <span style="color:#6b7280;display:block;margin-bottom:2px;">Heure</span>
        <strong>{{ $document->meeting_time ?? '__ h __' }}</strong>
      </td>
      <td style="padding:6px 10px;font-size:8.5px;width:33%;text-align:right;">
        <span style="color:#6b7280;display:block;margin-bottom:2px;">Lieu</span>
        <strong>{{ $document->location ?? $company->address ?? '________________' }}</strong>
      </td>
    </tr>
  </table>
</div>

{{-- Participants / Liste --}}
@if($document->lines && $document->lines->count() > 0)
<div class="section">
  <div class="section-title">Participants</div>
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:{{ $primaryColor }};color:#ffffff;">
        <th style="padding:5px 10px;text-align:left;font-size:8px;">#</th>
        <th style="padding:5px 10px;text-align:left;font-size:8px;">Nom / Entité</th>
        <th style="padding:5px 10px;text-align:left;font-size:8px;">Fonction / Qualité</th>
        <th style="padding:5px 10px;text-align:center;font-size:8px;">Présent</th>
      </tr>
    </thead>
    <tbody>
      @foreach($document->lines as $i => $line)
        <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#f9fafb' }};border-bottom:1px solid #e5e7eb;">
          <td style="padding:5px 10px;font-size:8.5px;color:#6b7280;">{{ $i + 1 }}</td>
          <td style="padding:5px 10px;font-size:8.5px;">{{ $line->description ?? $line->label ?? '' }}</td>
          <td style="padding:5px 10px;font-size:8.5px;color:#6b7280;">{{ $line->detail ?? '' }}</td>
          <td style="padding:5px 10px;text-align:center;font-size:8.5px;">
            <div style="width:12px;height:12px;border:1px solid #9ca3af;border-radius:2px;margin:0 auto;"></div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endif

{{-- Ordre du jour --}}
<div class="section">
  <div class="section-title">Ordre du jour / Points abordés</div>
  <div class="section-body">
    @if(!empty($document->agenda))
      {!! nl2br(e($document->agenda)) !!}
    @else
      <div style="color:#9ca3af;font-style:italic;">À compléter</div>
    @endif
  </div>
</div>

{{-- Décisions / Observations --}}
<div class="section">
  <div class="section-title">Décisions prises / Observations</div>
  <div class="section-body">
    @if(!empty($document->notes))
      {!! nl2br(e($document->notes)) !!}
    @else
      <div style="height:60px;border:1px dashed #e5e7eb;border-radius:3px;"></div>
    @endif
  </div>
</div>

<hr style="border:none;border-top:1px solid #e5e7eb;margin:16px 0;">

{{-- Signatures --}}
<table style="width:100%;border-collapse:collapse;margin-top:16px;">
  <tr>
    <td style="width:50%;text-align:center;padding-right:10px;">
      <div style="border-top:1px solid #9ca3af;padding-top:6px;">
        <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;">Président de séance</div>
        <div style="height:40px;border-bottom:1px dotted #d1d5db;margin:8px 4px 0;"></div>
        <div style="font-size:7.5px;color:#9ca3af;margin-top:4px;">Nom et signature</div>
      </div>
    </td>
    <td style="width:50%;text-align:center;">
      <div style="border-top:1px solid #9ca3af;padding-top:6px;">
        <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;">Secrétaire de séance</div>
        <div style="height:40px;border-bottom:1px dotted #d1d5db;margin:8px 4px 0;"></div>
        <div style="font-size:7.5px;color:#9ca3af;margin-top:4px;">Nom et signature</div>
      </div>
    </td>
  </tr>
</table>

</body>
</html>

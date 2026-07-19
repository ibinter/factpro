<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->number }}</title>
<style>
  @page { margin: 12mm; size: A4 landscape; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1a2332; margin: 0; padding: 0; }
  table { border-collapse: collapse; }
</style>
</head>
<body>

@include('pdf.engine.blocks._watermark', ['watermark' => $watermark ?? null])

{{-- En-tête école --}}
<table style="width:100%;margin-bottom:12px;">
  <tr>
    <td style="width:20%;text-align:center;vertical-align:middle;">
      @if(!empty($company->logo_url))
        <img src="{{ $company->logo_url }}" style="max-height:55px;max-width:80px;">
      @else
        <div style="width:60px;height:60px;border:1px solid #e5e7eb;border-radius:50%;background:#f3f4f6;margin:0 auto;line-height:60px;text-align:center;font-size:18px;color:#6b7280;">🏫</div>
      @endif
    </td>
    <td style="width:60%;text-align:center;vertical-align:middle;">
      <div style="font-size:15px;font-weight:bold;color:{{ $primaryColor }};">{{ $company->name ?? '' }}</div>
      @if(!empty($company->address))<div style="font-size:8.5px;color:#6b7280;margin-top:3px;">{{ $company->address }}@if(!empty($company->city)), {{ $company->city }}@endif</div>@endif
      <div style="font-size:11px;font-weight:bold;margin-top:6px;text-transform:uppercase;color:#374151;">
        {{ $document->type_label ?? 'BULLETIN DE NOTES' }}
      </div>
      @if(!empty($document->academic_year))<div style="font-size:9px;color:#6b7280;">Année scolaire : {{ $document->academic_year }}</div>@endif
    </td>
    <td style="width:20%;text-align:right;vertical-align:top;">
      @if(!empty($document->issue_date))
        <div style="font-size:8px;color:#6b7280;">Date : {{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}</div>
      @endif
      @if(!empty($document->period))<div style="font-size:8px;color:#6b7280;">Période : {{ $document->period }}</div>@endif
    </td>
  </tr>
</table>

<hr style="border:none;border-top:2px solid {{ $primaryColor }};margin-bottom:12px;">

{{-- Informations élève --}}
<div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:4px;padding:8px 14px;margin-bottom:12px;">
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="width:40%;font-size:9px;padding:2px 0;">
        <span style="color:#6b7280;">Élève :</span>
        <strong>{{ $document->customer->name ?? '____________________' }}</strong>
      </td>
      <td style="width:20%;font-size:9px;padding:2px 0;text-align:center;">
        <span style="color:#6b7280;">Classe :</span>
        <strong>{{ $document->class_name ?? $document->reference ?? '________' }}</strong>
      </td>
      <td style="width:20%;font-size:9px;padding:2px 0;text-align:center;">
        <span style="color:#6b7280;">Effectif :</span>
        <strong>{{ $document->class_size ?? '____' }}</strong>
      </td>
      <td style="width:20%;font-size:9px;padding:2px 0;text-align:right;">
        <span style="color:#6b7280;">Rang :</span>
        <strong>{{ $document->rank ?? '____' }} / {{ $document->class_size ?? '____' }}</strong>
      </td>
    </tr>
  </table>
</div>

{{-- Tableau de notes --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:12px;">
  <thead>
    <tr style="background:{{ $primaryColor }};color:#ffffff;">
      <th style="padding:6px 10px;text-align:left;font-size:8px;text-transform:uppercase;width:30%;">Matière</th>
      <th style="padding:6px 10px;text-align:center;font-size:8px;text-transform:uppercase;width:8%;">Coeff.</th>
      <th style="padding:6px 10px;text-align:center;font-size:8px;text-transform:uppercase;width:10%;">Note /20</th>
      <th style="padding:6px 10px;text-align:center;font-size:8px;text-transform:uppercase;width:10%;">Points</th>
      <th style="padding:6px 10px;text-align:center;font-size:8px;text-transform:uppercase;width:10%;">Moy. Classe</th>
      <th style="padding:6px 10px;text-align:center;font-size:8px;text-transform:uppercase;width:8%;">Rang</th>
      <th style="padding:6px 10px;text-align:left;font-size:8px;text-transform:uppercase;width:24%;">Appréciation</th>
    </tr>
  </thead>
  <tbody>
    @forelse($document->lines ?? [] as $i => $line)
      @php
        $note  = (float)($line->unit_price ?? $line->grade ?? 0);
        $coeff = (float)($line->quantity ?? $line->coefficient ?? 1);
        $pts   = round($note * $coeff, 2);
        $classAvg = $line->class_average ?? null;
      @endphp
      <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#f9fafb' }};border-bottom:1px solid #e5e7eb;">
        <td style="padding:5px 10px;font-size:9px;font-weight:bold;">{{ $line->description ?? $line->label ?? '' }}</td>
        <td style="padding:5px 10px;text-align:center;font-size:9px;">{{ $coeff }}</td>
        <td style="padding:5px 10px;text-align:center;font-size:9px;font-weight:bold;color:{{ $note >= 10 ? '#059669' : '#dc2626' }};">
          {{ number_format($note, 2, ',', '') }}
        </td>
        <td style="padding:5px 10px;text-align:center;font-size:9px;">{{ number_format($pts, 2, ',', '') }}</td>
        <td style="padding:5px 10px;text-align:center;font-size:9px;color:#6b7280;">
          {{ $classAvg !== null ? number_format((float)$classAvg, 2, ',', '') : '—' }}
        </td>
        <td style="padding:5px 10px;text-align:center;font-size:9px;color:#6b7280;">{{ $line->rank ?? '—' }}</td>
        <td style="padding:5px 10px;font-size:8.5px;color:#6b7280;font-style:italic;">{{ $line->note ?? $line->appreciation ?? '' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="7" style="padding:14px;text-align:center;color:#9ca3af;font-style:italic;">Aucune note saisie</td>
      </tr>
    @endforelse
  </tbody>
  @php
    $totalPts   = $document->lines ? $document->lines->sum(fn($l) => (float)($l->unit_price ?? $l->grade ?? 0) * (float)($l->quantity ?? $l->coefficient ?? 1)) : 0;
    $totalCoeff = $document->lines ? $document->lines->sum(fn($l) => (float)($l->quantity ?? $l->coefficient ?? 1)) : 0;
    $moyenne    = $totalCoeff > 0 ? round($totalPts / $totalCoeff, 2) : 0;
  @endphp
  <tfoot>
    <tr style="background:{{ $primaryColor }};color:#ffffff;font-weight:bold;">
      <td style="padding:6px 10px;font-size:9px;" colspan="2">MOYENNE GÉNÉRALE</td>
      <td style="padding:6px 10px;text-align:center;font-size:11px;">{{ number_format($moyenne, 2, ',', '') }}</td>
      <td style="padding:6px 10px;text-align:center;font-size:9px;">{{ number_format($totalPts, 2, ',', '') }}</td>
      <td colspan="3" style="padding:6px 10px;font-size:9px;">Total points : {{ number_format($totalPts, 2, ',', '') }} / {{ number_format($totalCoeff * 20, 0, ',', '') }}</td>
    </tr>
  </tfoot>
</table>

{{-- Décision et appréciations --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:12px;">
  <tr>
    <td style="width:50%;vertical-align:top;padding-right:10px;">
      <div style="border:1px solid #e5e7eb;border-radius:4px;padding:8px 12px;background:#f9fafb;">
        <div style="font-size:8px;font-weight:bold;text-transform:uppercase;color:{{ $primaryColor }};margin-bottom:4px;">Décision du conseil de classe</div>
        <div style="font-size:9px;font-weight:bold;color:#111827;">{{ $document->council_decision ?? '____________________' }}</div>
        @if(!empty($document->notes))
          <div style="font-size:8.5px;color:#374151;margin-top:4px;font-style:italic;">{{ $document->notes }}</div>
        @endif
      </div>
    </td>
    <td style="width:50%;vertical-align:top;">
      <div style="border:1px solid #e5e7eb;border-radius:4px;padding:8px 12px;background:#f9fafb;">
        <div style="font-size:8px;font-weight:bold;text-transform:uppercase;color:{{ $primaryColor }};margin-bottom:4px;">Appréciation générale</div>
        <div style="font-size:8.5px;color:#374151;font-style:italic;">{{ $document->general_appreciation ?? '____________________' }}</div>
      </div>
    </td>
  </tr>
</table>

{{-- Signatures --}}
<table style="width:100%;border-collapse:collapse;margin-top:14px;">
  <tr>
    @foreach(($signatureLabels ?? ['Prof. Principal', 'Directeur', 'Parent / Tuteur']) as $label)
      <td style="text-align:center;padding:0 6px;">
        <div style="border-top:1px solid #9ca3af;padding-top:5px;">
          <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;">{{ $label }}</div>
          <div style="height:35px;border-bottom:1px dotted #d1d5db;margin:6px 4px 0;"></div>
          <div style="font-size:7.5px;color:#9ca3af;margin-top:3px;">Signature</div>
        </div>
      </td>
    @endforeach
  </tr>
</table>

</body>
</html>

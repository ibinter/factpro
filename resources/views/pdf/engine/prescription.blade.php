<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->number }}</title>
<style>
  @@page { margin: 12mm; size: A5 portrait; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1a2332; margin: 0; padding: 0; }
</style>
</head>
<body>

@include('pdf.engine.blocks._watermark', ['watermark' => $watermark ?? null])

{{-- En-tête médecin --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
  <tr>
    <td style="width:60%;vertical-align:top;">
      <div style="font-size:13px;font-weight:bold;color:{{ $primaryColor }};">{{ $company->name ?? '' }}</div>
      @if(!empty($company->specialty))<div style="font-size:9px;color:#374151;">{{ $company->specialty }}</div>@endif
      <div style="font-size:8px;color:#6b7280;margin-top:4px;line-height:1.6;">
        @if(!empty($company->address)){{ $company->address }}<br>@endif
        @if(!empty($company->city)){{ $company->city }}<br>@endif
        @if(!empty($company->phone))Tél : {{ $company->phone }}<br>@endif
        @if(!empty($company->rpps))RPPS : {{ $company->rpps }}<br>@endif
        @if(!empty($company->ordre_number))N° Ordre : {{ $company->ordre_number }}@endif
      </div>
    </td>
    <td style="width:40%;vertical-align:top;text-align:right;">
      @if(!empty($company->logo_url))
        <img src="{{ $company->logo_url }}" style="max-height:45px;max-width:80px;">
      @endif
      <div style="font-size:8px;color:#6b7280;margin-top:4px;">
        @if(!empty($document->issue_date))Le {{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}@endif
      </div>
    </td>
  </tr>
</table>

<hr style="border:none;border-top:1px solid {{ $primaryColor }};margin-bottom:10px;">

{{-- Patient --}}
<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:4px;padding:8px 12px;margin-bottom:10px;">
  <div style="font-size:8px;text-transform:uppercase;color:#6b7280;margin-bottom:4px;">Patient</div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="font-size:8.5px;width:50%;padding-right:8px;">
        <span style="color:#6b7280;">Nom :</span> <strong>{{ $document->customer->name ?? '____________________' }}</strong>
      </td>
      <td style="font-size:8.5px;width:50%;">
        <span style="color:#6b7280;">Date de naissance :</span> {{ $document->customer->birth_date ?? '________________' }}
      </td>
    </tr>
    <tr>
      <td style="font-size:8.5px;padding-top:3px;">
        <span style="color:#6b7280;">Poids :</span> {{ $document->patient_weight ?? '____' }} kg
      </td>
      <td style="font-size:8.5px;padding-top:3px;">
        <span style="color:#6b7280;">Taille :</span> {{ $document->patient_height ?? '____' }} cm
      </td>
    </tr>
  </table>
</div>

{{-- Titre ORDONNANCE --}}
<div style="text-align:center;font-size:16px;font-weight:bold;text-transform:uppercase;letter-spacing:2px;color:{{ $primaryColor }};margin:10px 0;border-bottom:1px dashed {{ $primaryColor }};padding-bottom:6px;">
  {{ $document->type_label ?? 'ORDONNANCE' }}
</div>

{{-- Prescriptions (lignes) --}}
@if($document->lines && $document->lines->count() > 0)
  @foreach($document->lines as $i => $line)
    <div style="margin-bottom:8px;padding-left:10px;border-left:2px solid {{ $primaryColor }};">
      <div style="font-size:9.5px;font-weight:bold;color:#111827;">
        {{ $i + 1 }}. {{ $line->description ?? $line->label ?? '' }}
      </div>
      @if(!empty($line->detail))
        <div style="font-size:8.5px;color:#374151;margin-top:2px;">{{ $line->detail }}</div>
      @endif
      @if(!empty($line->note))
        <div style="font-size:8px;color:#6b7280;font-style:italic;">{{ $line->note }}</div>
      @endif
    </div>
  @endforeach
@else
  <div style="height:80px;border:1px dashed #e5e7eb;border-radius:3px;margin-bottom:10px;"></div>
@endif

@include('pdf.engine.blocks._notes', ['document' => $document])

{{-- Signature médecin --}}
<div style="margin-top:16px;text-align:right;">
  <div style="display:inline-block;border:1px solid #e5e7eb;border-radius:4px;padding:10px 16px;background:#f9fafb;min-width:140px;">
    <div style="font-size:8.5px;font-weight:bold;color:#374151;margin-bottom:4px;">Cachet et Signature</div>
    <div style="height:35px;"></div>
    <div style="font-size:8px;color:#6b7280;">Dr. {{ $company->name ?? '' }}</div>
  </div>
</div>

{{-- QR --}}
@include('pdf.engine.blocks._qr-auth', ['qrDataUri' => $qrDataUri ?? null, 'document' => $document])

{{-- Pied de page légal --}}
<div style="border-top:1px solid #e5e7eb;margin-top:10px;padding-top:6px;text-align:center;">
  <div style="font-size:7px;color:#9ca3af;">
    Ordonnance médicale confidentielle — À conserver par le patient — Valable 3 mois sauf mention contraire
  </div>
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->number }}</title>
<style>
  @@page { margin: 15mm 20mm; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a2332; margin: 0; padding: 0; }
  .field-row { margin-bottom: 10px; }
  .field-label { font-size: 8px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
  .field-value { font-size: 10px; font-weight: bold; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
</style>
</head>
<body>

@include('pdf.engine.blocks._watermark', ['watermark' => $watermark ?? null])

{{-- En-tête --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:16px;">
  <tr>
    <td style="width:55%;vertical-align:top;padding-right:12px;">
      @include('pdf.engine.blocks._company', ['company' => $company, 'primaryColor' => $primaryColor])
    </td>
    <td style="width:45%;vertical-align:top;text-align:right;">
      <div style="font-size:8.5px;color:#6b7280;line-height:1.8;">
        Réf. : <strong>{{ $document->number }}</strong><br>
        @if(!empty($document->issue_date))Date : <strong>{{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}</strong>@endif
      </div>
    </td>
  </tr>
</table>

<hr style="border:none;border-top:2px solid {{ $primaryColor }};margin-bottom:16px;">

{{-- Titre --}}
<div style="text-align:center;background:{{ $primaryColor }};color:#ffffff;padding:12px;border-radius:4px;margin-bottom:20px;">
  <div style="font-size:16px;font-weight:bold;text-transform:uppercase;letter-spacing:2px;">
    {{ $document->type_label ?? 'DEMANDE DE CONGÉ' }}
  </div>
  <div style="font-size:10px;margin-top:4px;">N° {{ $document->number }}</div>
</div>

{{-- Informations employé --}}
<div style="border:1px solid #e5e7eb;border-radius:4px;padding:14px;margin-bottom:16px;background:#f0f9ff;">
  <div style="font-size:9px;font-weight:bold;color:{{ $primaryColor }};text-transform:uppercase;margin-bottom:12px;letter-spacing:0.5px;">
    Informations de l'employé
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="width:50%;vertical-align:top;padding-right:14px;">
        <div class="field-row">
          <div class="field-label">Nom et Prénoms</div>
          <div class="field-value">{{ $document->customer->name ?? '____________________' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Service / Département</div>
          <div class="field-value">{{ $document->customer->department ?? '____________________' }}</div>
        </div>
      </td>
      <td style="width:50%;vertical-align:top;">
        <div class="field-row">
          <div class="field-label">Poste / Fonction</div>
          <div class="field-value">{{ $document->customer->job_title ?? '____________________' }}</div>
        </div>
        <div class="field-row">
          <div class="field-label">Matricule</div>
          <div class="field-value">{{ $document->customer->employee_id ?? '____________________' }}</div>
        </div>
      </td>
    </tr>
  </table>
</div>

{{-- Détails de la demande --}}
<div style="border:1px solid #e5e7eb;border-radius:4px;padding:14px;margin-bottom:16px;">
  <div style="font-size:9px;font-weight:bold;color:{{ $primaryColor }};text-transform:uppercase;margin-bottom:12px;letter-spacing:0.5px;">
    Détails de la demande
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="width:33%;padding-right:10px;">
        <div class="field-row">
          <div class="field-label">Type de congé</div>
          <div class="field-value">{{ $document->leave_type ?? $document->subject ?? '____________________' }}</div>
        </div>
      </td>
      <td style="width:33%;padding-right:10px;">
        <div class="field-row">
          <div class="field-label">Date de départ</div>
          <div class="field-value">
            @if(!empty($document->issue_date)){{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}@else____________________@endif
          </div>
        </div>
      </td>
      <td style="width:33%;">
        <div class="field-row">
          <div class="field-label">Date de reprise</div>
          <div class="field-value">
            @if(!empty($document->due_date)){{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}@else____________________@endif
          </div>
        </div>
      </td>
    </tr>
    <tr>
      <td style="padding-right:10px;">
        <div class="field-row">
          <div class="field-label">Durée (jours ouvrables)</div>
          <div class="field-value">
            @php
              $days = null;
              if (!empty($document->issue_date) && !empty($document->due_date)) {
                $days = \Carbon\Carbon::parse($document->issue_date)->diffInWeekdays(\Carbon\Carbon::parse($document->due_date));
              }
            @endphp
            {{ $days !== null ? $days . ' jour(s)' : '____________________' }}
          </div>
        </div>
      </td>
      <td colspan="2">
        <div class="field-row">
          <div class="field-label">Motif</div>
          <div class="field-value">{{ $document->notes ?? '____________________' }}</div>
        </div>
      </td>
    </tr>
  </table>
</div>

{{-- Solde de congés --}}
@if(!empty($document->leave_balance))
<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:4px;padding:8px 14px;margin-bottom:16px;">
  <div style="font-size:8.5px;color:#065f46;">
    <span style="font-weight:bold;">Solde de congés disponible :</span> {{ $document->leave_balance }} jours
  </div>
</div>
@endif

{{-- Visas --}}
<div style="margin-top:20px;">
  <div style="font-size:9px;font-weight:bold;color:{{ $primaryColor }};text-transform:uppercase;margin-bottom:12px;letter-spacing:0.5px;">
    Avis et approbations
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      @foreach(($signatureLabels ?? ['Responsable N+1', 'Responsable RH', 'Directeur Général']) as $label)
        <td style="text-align:center;padding:0 6px;">
          <div style="border:1px solid #e5e7eb;border-top:3px solid {{ $primaryColor }};border-radius:3px;padding:8px;background:#f9fafb;">
            <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;margin-bottom:4px;">{{ $label }}</div>
            <div style="font-size:8px;color:#9ca3af;margin-bottom:6px;">
              <span style="margin-right:10px;">☐ Favorable</span>
              <span>☐ Défavorable</span>
            </div>
            <div style="height:30px;"></div>
            <div style="border-top:1px dotted #d1d5db;padding-top:4px;font-size:7.5px;color:#9ca3af;">Signature / Date</div>
          </div>
        </td>
      @endforeach
    </tr>
  </table>
</div>

{{-- Signature demandeur --}}
<div style="margin-top:16px;text-align:right;">
  <div style="display:inline-block;border-top:1px solid #9ca3af;padding-top:6px;min-width:180px;text-align:center;">
    <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;">Signature du demandeur</div>
    <div style="height:35px;"></div>
    <div style="font-size:7.5px;color:#9ca3af;">Date : ________________</div>
  </div>
</div>

</body>
</html>

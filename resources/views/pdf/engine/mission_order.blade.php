<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->number }}</title>
<style>
  @page { margin: 15mm 20mm; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a2332; margin: 0; padding: 0; }
  .field-row { margin-bottom: 8px; font-size: 9px; }
  .field-label { color: #6b7280; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 2px; }
  .field-value { font-weight: bold; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; }
</style>
</head>
<body>

@include('pdf.engine.blocks._watermark', ['watermark' => $watermark ?? null])

{{-- En-tête --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:16px;">
  <tr>
    <td style="width:60%;vertical-align:top;">
      @include('pdf.engine.blocks._company', ['company' => $company, 'primaryColor' => $primaryColor])
    </td>
    <td style="width:40%;vertical-align:top;text-align:right;">
      <div style="font-size:8.5px;color:#6b7280;line-height:1.8;">
        Réf. : <strong>{{ $document->number }}</strong><br>
        @if(!empty($document->issue_date))
          Date : <strong>{{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}</strong>
        @endif
      </div>
    </td>
  </tr>
</table>

<hr style="border:none;border-top:2px solid {{ $primaryColor }};margin-bottom:16px;">

{{-- Titre principal --}}
<div style="text-align:center;background:{{ $primaryColor }};color:#ffffff;padding:12px;border-radius:4px;margin-bottom:18px;">
  <div style="font-size:16px;font-weight:bold;text-transform:uppercase;letter-spacing:2px;">ORDRE DE MISSION</div>
  <div style="font-size:10px;margin-top:4px;">N° {{ $document->number }}</div>
</div>

{{-- Informations de l'agent --}}
<div style="border:1px solid #e5e7eb;border-radius:4px;padding:14px;margin-bottom:16px;background:#f9fafb;">
  <div style="font-size:9px;font-weight:bold;color:{{ $primaryColor }};text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;">
    Informations de l'agent
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="width:50%;vertical-align:top;padding-right:12px;">
        <div class="field-row">
          <span class="field-label">Nom et Prénoms</span>
          <div class="field-value">{{ $document->customer->name ?? '____________________' }}</div>
        </div>
        <div class="field-row">
          <span class="field-label">Fonction / Poste</span>
          <div class="field-value">{{ $document->customer->job_title ?? '____________________' }}</div>
        </div>
      </td>
      <td style="width:50%;vertical-align:top;">
        <div class="field-row">
          <span class="field-label">Service / Département</span>
          <div class="field-value">{{ $document->customer->department ?? '____________________' }}</div>
        </div>
        <div class="field-row">
          <span class="field-label">Matricule</span>
          <div class="field-value">{{ $document->customer->employee_id ?? '____________________' }}</div>
        </div>
      </td>
    </tr>
  </table>
</div>

{{-- Détails de la mission --}}
<div style="border:1px solid #e5e7eb;border-radius:4px;padding:14px;margin-bottom:16px;">
  <div style="font-size:9px;font-weight:bold;color:{{ $primaryColor }};text-transform:uppercase;margin-bottom:10px;letter-spacing:0.5px;">
    Détails de la mission
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="width:100%;vertical-align:top;">
        <div class="field-row">
          <span class="field-label">Objet de la mission</span>
          <div class="field-value">{{ $document->subject ?? $document->notes ?? '____________________' }}</div>
        </div>
      </td>
    </tr>
    <tr>
      <td>
        <table style="width:100%;border-collapse:collapse;">
          <tr>
            <td style="width:33%;padding-right:10px;">
              <div class="field-row">
                <span class="field-label">Destination</span>
                <div class="field-value">{{ $document->destination ?? '____________________' }}</div>
              </div>
            </td>
            <td style="width:33%;padding-right:10px;">
              <div class="field-row">
                <span class="field-label">Date de départ</span>
                <div class="field-value">
                  @if(!empty($document->issue_date)){{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}@else____________________@endif
                </div>
              </div>
            </td>
            <td style="width:33%;">
              <div class="field-row">
                <span class="field-label">Date de retour</span>
                <div class="field-value">
                  @if(!empty($document->due_date)){{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}@else____________________@endif
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <td style="padding-right:10px;">
              <div class="field-row">
                <span class="field-label">Moyen de transport</span>
                <div class="field-value">{{ $document->transport_mode ?? '____________________' }}</div>
              </div>
            </td>
            <td colspan="2">
              <div class="field-row">
                <span class="field-label">Budget alloué</span>
                <div class="field-value">
                  @if(!empty($document->total) && (float)$document->total > 0)
                    {{ number_format((float)$document->total, 0, ',', ' ') }} {{ $document->currency ?? '' }}
                  @else
                    ____________________
                  @endif
                </div>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</div>

{{-- Notes --}}
@include('pdf.engine.blocks._notes', ['document' => $document])

{{-- Visas --}}
<div style="margin-top:20px;">
  <div style="font-size:9px;font-weight:bold;color:{{ $primaryColor }};text-transform:uppercase;margin-bottom:12px;letter-spacing:0.5px;">
    Visas et approbations
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="width:33%;text-align:center;padding:0 6px;">
        <div style="border:1px solid #e5e7eb;border-top:3px solid {{ $primaryColor }};border-radius:3px;padding:8px;background:#f9fafb;">
          <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;margin-bottom:6px;">Responsable RH</div>
          <div style="height:35px;"></div>
          <div style="border-top:1px dotted #d1d5db;padding-top:4px;font-size:7.5px;color:#9ca3af;">Signature / Date</div>
        </div>
      </td>
      <td style="width:33%;text-align:center;padding:0 6px;">
        <div style="border:1px solid #e5e7eb;border-top:3px solid {{ $primaryColor }};border-radius:3px;padding:8px;background:#f9fafb;">
          <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;margin-bottom:6px;">Directeur</div>
          <div style="height:35px;"></div>
          <div style="border-top:1px dotted #d1d5db;padding-top:4px;font-size:7.5px;color:#9ca3af;">Signature / Date</div>
        </div>
      </td>
      <td style="width:33%;text-align:center;padding:0 6px;">
        <div style="border:1px solid #e5e7eb;border-top:3px solid {{ $primaryColor }};border-radius:3px;padding:8px;background:#f9fafb;">
          <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;margin-bottom:6px;">Directeur Général</div>
          <div style="height:35px;"></div>
          <div style="border-top:1px dotted #d1d5db;padding-top:4px;font-size:7.5px;color:#9ca3af;">Signature / Date</div>
        </div>
      </td>
    </tr>
  </table>
</div>

{{-- Retour de mission --}}
<div style="margin-top:16px;border:1px dashed #9ca3af;border-radius:4px;padding:10px 14px;">
  <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;margin-bottom:6px;">
    Retour de mission (à compléter à l'arrivée)
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="font-size:8.5px;color:#374151;width:50%;padding-right:10px;">
        Date de retour effectif : ________________
      </td>
      <td style="font-size:8.5px;color:#374151;width:50%;">
        Rapport déposé le : ________________
      </td>
    </tr>
  </table>
</div>

</body>
</html>

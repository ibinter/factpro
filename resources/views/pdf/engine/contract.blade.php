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
  .article { margin-bottom: 12px; }
  .article-title { font-size: 10px; font-weight: bold; text-transform: uppercase; color: {{ $primaryColor }}; margin-bottom: 4px; }
  .article-body { font-size: 9px; line-height: 1.7; color: #374151; }
  .section-sep { border: none; border-top: 1px solid #e5e7eb; margin: 16px 0; }
</style>
</head>
<body>

@include('pdf.engine.blocks._watermark', ['watermark' => $watermark ?? null])

<div class="page-footer">
  <span>{{ $document->type_label ?? 'Contrat' }} — {{ $document->number }} — Confidentiel</span>
  <span class="page-num"></span>
</div>

{{-- En-tête institutionnel --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:16px;">
  <tr>
    <td style="width:50%;vertical-align:top;padding-right:12px;">
      @include('pdf.engine.blocks._company', ['company' => $company, 'primaryColor' => $primaryColor])
    </td>
    <td style="width:50%;vertical-align:top;text-align:right;">
      <div style="font-size:8.5px;color:#6b7280;line-height:1.8;">
        Réf. : {{ $document->number }}<br>
        @if(!empty($document->reference))Référence : {{ $document->reference }}<br>@endif
        @if(!empty($document->issue_date))Date : {{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}<br>@endif
        @if(!empty($document->due_date))Valide jusqu'au : {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}@endif
      </div>
    </td>
  </tr>
</table>

<hr class="section-sep" style="border-top:2px solid {{ $primaryColor }};">

{{-- Titre contrat --}}
<div style="text-align:center;margin-bottom:20px;">
  <div style="font-size:16px;font-weight:bold;text-decoration:underline;text-transform:uppercase;color:{{ $primaryColor }};">
    {{ $document->type_label ?? 'CONTRAT' }}
  </div>
  @if(!empty($document->subject))
    <div style="font-size:11px;color:#374151;margin-top:6px;">{{ $document->subject }}</div>
  @endif
</div>

{{-- ENTRE LES SOUSSIGNÉS --}}
<div class="article">
  <div class="article-title">Entre les soussignés :</div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="width:48%;vertical-align:top;padding-right:10px;">
        <div style="border:1px solid #e5e7eb;border-left:3px solid {{ $primaryColor }};border-radius:3px;padding:10px 12px;background:#f9fafb;">
          <div style="font-size:8px;text-transform:uppercase;color:#6b7280;margin-bottom:4px;">Partie A</div>
          <div style="font-size:10px;font-weight:bold;">{{ $company->name ?? '' }}</div>
          <div style="font-size:8.5px;color:#374151;margin-top:3px;line-height:1.6;">
            @if(!empty($company->address)){{ $company->address }}<br>@endif
            @if(!empty($company->tax_number))N° fiscal : {{ $company->tax_number }}<br>@endif
            @if(!empty($company->legal_form))Forme juridique : {{ $company->legal_form }}<br>@endif
            Représentée par : {{ $company->representative ?? '____________________' }}
          </div>
        </div>
      </td>
      <td style="width:4%;"></td>
      <td style="width:48%;vertical-align:top;">
        <div style="border:1px solid #e5e7eb;border-left:3px solid #6b7280;border-radius:3px;padding:10px 12px;background:#f9fafb;">
          <div style="font-size:8px;text-transform:uppercase;color:#6b7280;margin-bottom:4px;">Partie B</div>
          @if($document->customer)
            <div style="font-size:10px;font-weight:bold;">{{ $document->customer->name ?? $document->customer->company_name ?? '' }}</div>
            <div style="font-size:8.5px;color:#374151;margin-top:3px;line-height:1.6;">
              @if(!empty($document->customer->address)){{ $document->customer->address }}<br>@endif
              @if(!empty($document->customer->tax_number))N° fiscal : {{ $document->customer->tax_number }}<br>@endif
              Représentée par : ____________________
            </div>
          @else
            <div style="font-size:9px;color:#9ca3af;font-style:italic;">— À compléter —</div>
          @endif
        </div>
      </td>
    </tr>
  </table>
</div>

<hr class="section-sep">

{{-- OBJET --}}
<div class="article">
  <div class="article-title">Article 1 — Objet du contrat</div>
  <div class="article-body">
    @if(!empty($document->notes))
      {!! nl2br(e($document->notes)) !!}
    @else
      Le présent contrat a pour objet de définir les conditions et modalités de la prestation convenue entre les parties.
    @endif
  </div>
</div>

{{-- Articles issus des lignes --}}
@if($document->lines && $document->lines->count() > 0)
  @foreach($document->lines as $i => $line)
    <div class="article">
      <div class="article-title">Article {{ $i + 2 }} — {{ $line->description ?? $line->label ?? 'Disposition' }}</div>
      <div class="article-body">
        @if(!empty($line->detail))
          {!! nl2br(e($line->detail)) !!}
        @else
          {{ $line->description ?? $line->label ?? '' }}
        @endif
      </div>
    </div>
  @endforeach
@else
  <div class="article">
    <div class="article-title">Article 2 — Durée</div>
    <div class="article-body">
      Le présent contrat est conclu pour une durée de __________________ à compter de sa date de signature.
    </div>
  </div>
  <div class="article">
    <div class="article-title">Article 3 — Résiliation</div>
    <div class="article-body">
      Chaque partie peut résilier le présent contrat avec un préavis de __________________.
    </div>
  </div>
  <div class="article">
    <div class="article-title">Article 4 — Confidentialité</div>
    <div class="article-body">
      Les parties s'engagent à maintenir confidentielles toutes les informations échangées dans le cadre du présent contrat.
    </div>
  </div>
@endif

<hr class="section-sep">

{{-- Conditions --}}
@include('pdf.engine.blocks._legal', ['company' => $company, 'document' => $document])

{{-- Signatures --}}
<div style="margin-top:24px;">
  <div style="font-size:9px;color:#6b7280;margin-bottom:16px;text-align:center;">
    Fait en deux exemplaires originaux, à ____________________,
    le @if(!empty($document->issue_date)){{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}@else____________________@endif
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="width:50%;vertical-align:top;padding-right:12px;text-align:center;">
        <div style="border-top:1px solid #9ca3af;padding-top:6px;">
          <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;">Pour la Partie A</div>
          <div style="font-size:8px;color:#9ca3af;">Lu et approuvé</div>
          <div style="height:50px;border-bottom:1px dotted #d1d5db;margin:8px 4px 0;"></div>
          <div style="font-size:8px;color:#374151;margin-top:4px;">{{ $company->name ?? '' }}</div>
        </div>
      </td>
      <td style="width:50%;vertical-align:top;text-align:center;">
        <div style="border-top:1px solid #9ca3af;padding-top:6px;">
          <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;">Pour la Partie B</div>
          <div style="font-size:8px;color:#9ca3af;">Lu et approuvé</div>
          <div style="height:50px;border-bottom:1px dotted #d1d5db;margin:8px 4px 0;"></div>
          <div style="font-size:8px;color:#374151;margin-top:4px;">{{ $document->customer->name ?? '____________________' }}</div>
        </div>
      </td>
    </tr>
  </table>
</div>

</body>
</html>

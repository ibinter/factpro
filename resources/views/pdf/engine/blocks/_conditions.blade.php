{{-- Bloc conditions générales / pied de page légal --}}
@php
  $conditions = $document->terms ?? $company->default_terms ?? null;
@endphp

@if(!empty($conditions))
<div style="border-top:1px solid #e5e7eb;margin-top:14px;padding-top:8px;">
  <div style="font-size:7.5px;font-weight:bold;text-transform:uppercase;color:#6b7280;margin-bottom:4px;letter-spacing:0.5px;">
    Conditions générales
  </div>
  <div style="font-size:7.5px;color:#9ca3af;line-height:1.5;">
    {!! nl2br(e($conditions)) !!}
  </div>
</div>
@else
<div style="border-top:1px solid #e5e7eb;margin-top:14px;padding-top:6px;text-align:center;">
  <div style="font-size:7px;color:#9ca3af;">
    @if(!empty($company->name)){{ $company->name }} — @endif
    @if(!empty($company->address)){{ $company->address }}@if(!empty($company->city)), {{ $company->city }}@endif — @endif
    Merci de votre confiance.
  </div>
</div>
@endif

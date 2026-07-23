@php
  $conditions = $document->terms ?? $company->default_terms ?? null;
  $footer     = $company->invoice_footer ?? null;
  $isInvoice  = in_array($document->type ?? '', ['invoice','simple_invoice','proforma','deposit_invoice','export_invoice','tax_exempt_invoice','rectification_invoice','balance_invoice']);
@endphp

<div style="border-top:2px solid {{ $primaryColor ?? '#0062cc' }};margin-top:18px;padding-top:10px;">

  {{-- Mentions légales obligatoires (pénalités de retard, etc.) --}}
  @if($isInvoice)
  <div style="font-size:7px;color:#6b7280;line-height:1.8;margin-bottom:8px;">
    <span style="font-weight:bold;text-transform:uppercase;letter-spacing:0.4px;color:#374151;">Mentions légales :</span>
    En cas de retard de paiement, des pénalités de retard au taux légal en vigueur seront appliquées de plein droit,
    ainsi qu'une indemnité forfaitaire pour frais de recouvrement de 40 €/40 000 FCFA (art. L441-10 Code Commerce).
    Escompte pour règlement anticipé : néant.
    Tout litige est soumis à la juridiction compétente du ressort du siège social de l'émetteur.
  </div>
  @endif

  {{-- Conditions spécifiques --}}
  @if(!empty($conditions))
  <div style="margin-bottom:8px;">
    <div style="font-size:7.5px;font-weight:bold;text-transform:uppercase;color:#374151;margin-bottom:3px;letter-spacing:0.5px;">
      Conditions de règlement
    </div>
    <div style="font-size:7.5px;color:#6b7280;line-height:1.6;">
      {!! nl2br(e($conditions)) !!}
    </div>
  </div>
  @endif

  {{-- Footer société --}}
  @if(!empty($footer))
  <div style="font-size:7px;color:#9ca3af;margin-bottom:6px;">{!! nl2br(e($footer)) !!}</div>
  @endif

  {{-- Pied de page identité --}}
  <div style="text-align:center;font-size:7px;color:#9ca3af;border-top:1px solid #f3f4f6;padding-top:5px;">
    @if(!empty($company->name))<strong style="color:#6b7280;">{{ $company->name }}</strong>@endif
    @if(!empty($company->legal_name) && $company->legal_name !== $company->name) · {{ $company->legal_name }}@endif
    @if(!empty($company->address)) · {{ $company->address }}@endif
    @if(!empty($company->city)), {{ $company->city }}@endif
    @php
      $ids = array_filter([
        !empty($company->trade_register) ? 'RCCM '.$company->trade_register : null,
        !empty($company->rccm)           ? 'RCCM '.$company->rccm           : null,
        !empty($company->tax_id)         ? 'N°fiscal '.$company->tax_id     : null,
        !empty($company->tax_number)     ? 'N°fiscal '.$company->tax_number : null,
        !empty($company->capital)        ? 'Capital '.$company->capital     : null,
      ]);
    @endphp
    @if(count($ids)) · {{ implode(' · ', $ids) }}@endif
    <br>
    Généré par <strong>IBIG FactPro</strong> — factpro.ibigsoft.com · Document certifié n° {{ $document->number }}
  </div>

</div>

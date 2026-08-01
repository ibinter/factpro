@php
  $conditions = $document->terms ?? $company->default_terms ?? null;
  $footer     = $company->invoice_footer ?? null;
  $isInvoice  = in_array($document->type ?? '', ['invoice','simple_invoice','proforma','deposit_invoice','export_invoice','tax_exempt_invoice','rectification_invoice','balance_invoice']);
@endphp

<div style="margin-top:20px;border-top:2px solid {{ $primaryColor ?? '#0062cc' }};padding-top:12px;">

  {{-- Mentions légales (factures uniquement) --}}
  @if($isInvoice)
  <div style="background:#f8f9fb;border:1px solid #e5e7eb;border-radius:3px;padding:8px 12px;margin-bottom:10px;">
    <div style="font-size:7px;font-weight:bold;text-transform:uppercase;letter-spacing:0.5px;color:#374151;margin-bottom:4px;">
      Mentions légales
    </div>
    <div style="font-size:7px;color:#6b7280;line-height:1.9;">
      En cas de retard de paiement, des pénalités au taux légal en vigueur seront appliquées de plein droit,
      ainsi qu'une indemnité forfaitaire pour frais de recouvrement de 40 €/40 000 FCFA (art. L441-10 Code Commerce).
      Escompte pour règlement anticipé : néant.
      Tout litige est soumis à la juridiction compétente du ressort du siège social de l'émetteur.
    </div>
  </div>
  @endif

  {{-- Conditions de règlement personnalisées --}}
  @if(!empty($conditions))
  <div style="margin-bottom:10px;">
    <div style="font-size:7.5px;font-weight:bold;text-transform:uppercase;color:#374151;margin-bottom:3px;letter-spacing:0.5px;">
      Conditions de règlement
    </div>
    <div style="font-size:7.5px;color:#6b7280;line-height:1.65;">
      {!! nl2br(e($conditions)) !!}
    </div>
  </div>
  @endif

  {{-- Footer société --}}
  @if(!empty($footer))
  <div style="font-size:7px;color:#9ca3af;margin-bottom:8px;line-height:1.6;">{!! nl2br(e($footer)) !!}</div>
  @endif

  {{-- Pied de page identité + branding IBIG FactPro --}}
  <div style="background:#f1f3f5;border:1px solid #e5e7eb;border-radius:3px;padding:6px 12px;text-align:center;">
    <div style="font-size:7px;color:#6b7280;line-height:1.8;">
      @if(!empty($company->name))<strong style="color:#374151;">{{ $company->name }}</strong>@endif
      @if(!empty($company->legal_name) && $company->legal_name !== $company->name) · {{ $company->legal_name }}
@endif
      @if(!empty($company->address)) · {{ $company->address }}
@endif
      @if(!empty($company->city)), {{ $company->city }}
@endif
      @php
        $ids = array_filter([
          !empty($company->trade_register) ? 'RCCM '.$company->trade_register : null,
          !empty($company->rccm)           ? 'RCCM '.$company->rccm           : null,
          !empty($company->tax_id)         ? 'N°Fiscal '.$company->tax_id     : null,
          !empty($company->tax_number)     ? 'N°Fiscal '.$company->tax_number : null,
          !empty($company->capital)        ? 'Capital '.$company->capital     : null,
        ]);
      @endphp
      @if(count($ids)) · {{ implode(' · ', $ids) }}
@endif
    </div>
    <div style="font-size:6.5px;color:#9ca3af;margin-top:3px;border-top:1px solid #e5e7eb;padding-top:3px;">
      Généré par <strong style="color:#6b7280;">IBIG FactPro</strong> — factpro.ibigsoft.com
      &nbsp;·&nbsp; Document certifié n° {{ $document->number }}
    </div>
  </div>

</div>

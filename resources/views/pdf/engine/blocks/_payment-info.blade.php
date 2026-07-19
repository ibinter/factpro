@php
  $hasIban   = !empty($company->iban);
  $hasMobile = !empty($company->mobile_money) || !empty($company->orange_money) || !empty($company->wave);
@endphp

@if($hasIban || $hasMobile)
<div style="border:1px solid #e5e7eb;border-radius:4px;padding:10px 14px;margin-bottom:12px;background:#f9fafb;">
  <div style="font-size:8.5px;font-weight:bold;text-transform:uppercase;color:{{ $primaryColor }};margin-bottom:7px;letter-spacing:0.5px;">
    Informations de règlement
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      @if($hasIban)
        <td style="vertical-align:top;padding-right:16px;width:50%;">
          <div style="font-size:8px;color:#6b7280;margin-bottom:2px;">Virement bancaire</div>
          @if(!empty($company->bank_name))
            <div style="font-size:8.5px;color:#374151;">Banque : {{ $company->bank_name }}</div>
          @endif
          @if(!empty($company->iban))
            <div style="font-size:8.5px;color:#374151;">IBAN : {{ $company->iban }}</div>
          @endif
          @if(!empty($company->bic))
            <div style="font-size:8.5px;color:#374151;">BIC : {{ $company->bic }}</div>
          @endif
          @if(!empty($company->account_number))
            <div style="font-size:8.5px;color:#374151;">N° Compte : {{ $company->account_number }}</div>
          @endif
        </td>
      @endif

      @if($hasMobile)
        <td style="vertical-align:top;">
          <div style="font-size:8px;color:#6b7280;margin-bottom:2px;">Mobile Money</div>
          @if(!empty($company->orange_money))
            <div style="font-size:8.5px;color:#374151;">Orange Money : {{ $company->orange_money }}</div>
          @endif
          @if(!empty($company->wave))
            <div style="font-size:8.5px;color:#374151;">Wave : {{ $company->wave }}</div>
          @endif
          @if(!empty($company->mtn_money))
            <div style="font-size:8.5px;color:#374151;">MTN Money : {{ $company->mtn_money }}</div>
          @endif
          @if(!empty($company->mobile_money))
            <div style="font-size:8.5px;color:#374151;">Mobile Money : {{ $company->mobile_money }}</div>
          @endif
        </td>
      @endif
    </tr>
  </table>
</div>
@endif

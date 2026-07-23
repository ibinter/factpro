<table style="width:100%;border-collapse:collapse;">
  <tr>
    @if(!empty($logoBase64))
    <td style="vertical-align:middle;width:72px;padding-right:10px;">
      <img src="{{ $logoBase64 }}" style="max-width:65px;max-height:58px;object-fit:contain;display:block;">
    </td>
    @endif
    <td style="vertical-align:top;">
      <div style="font-size:15px;font-weight:bold;color:{{ $primaryColor }};line-height:1.2;letter-spacing:-0.3px;">
        {{ $company->name }}
      </div>
      @if(!empty($company->legal_name) && $company->legal_name !== $company->name)
        <div style="font-size:8px;color:#6b7280;font-style:italic;margin-top:1px;">{{ $company->legal_name }}</div>
      @endif
      @if(!empty($company->tagline))
        <div style="font-size:8px;color:#6b7280;font-style:italic;margin-top:1px;">{{ $company->tagline }}</div>
      @endif
      <div style="font-size:8.5px;color:#374151;margin-top:5px;line-height:1.7;">
        @if(!empty($company->address)){{ $company->address }}<br>@endif
        @if(!empty($company->city)){{ $company->city }}@if(!empty($company->postal_code)) – {{ $company->postal_code }}@endif<br>@endif
        @if(!empty($company->country)){{ $company->country }}<br>@endif
        @if(!empty($company->phone))<span style="color:#6b7280;">Tél :</span> {{ $company->phone }}<br>@endif
        @if(!empty($company->email))<span style="color:#6b7280;">Email :</span> {{ $company->email }}<br>@endif
        @if(!empty($company->website))<span style="color:#6b7280;">Web :</span> {{ $company->website }}<br>@endif
      </div>
      @php
        $regs = array_filter([
          !empty($company->tax_id)         ? 'N° Fiscal : '.$company->tax_id         : null,
          !empty($company->tax_number)     ? 'N° Fiscal : '.$company->tax_number     : null,
          !empty($company->trade_register) ? 'RCCM : '.$company->trade_register      : null,
          !empty($company->rccm)           ? 'RCCM : '.$company->rccm                : null,
          !empty($company->capital)        ? 'Capital : '.$company->capital          : null,
        ]);
      @endphp
      @if(count($regs))
        <div style="font-size:7.5px;color:#9ca3af;margin-top:4px;line-height:1.6;">
          {{ implode(' · ', $regs) }}
        </div>
      @endif
    </td>
  </tr>
</table>

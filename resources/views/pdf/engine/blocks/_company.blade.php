<table style="width:100%;border-collapse:collapse;">
  <tr>
    @if(!empty($logoBase64))
    <td style="vertical-align:middle;width:82px;padding-right:12px;">
      <div style="border:1px solid #e5e7eb;border-radius:4px;padding:5px;background:#ffffff;">
        <img src="{{ $logoBase64 }}" style="max-width:72px;max-height:72px;object-fit:contain;display:block;">
      </div>
    </td>
    @endif
    <td style="vertical-align:top;border-left:3px solid {{ $primaryColor }};padding-left:10px;">
      <div style="font-size:16px;font-weight:bold;color:{{ $primaryColor }};line-height:1.15;letter-spacing:-0.3px;">
        {{ $company->name }}
      </div>
      @if(!empty($company->tagline))
        <div style="font-size:8px;color:#6b7280;font-style:italic;margin-top:1px;letter-spacing:0.2px;">{{ $company->tagline }}</div>
      @elseif(!empty($company->legal_name) && $company->legal_name !== $company->name)
        <div style="font-size:8px;color:#6b7280;font-style:italic;margin-top:1px;">{{ $company->legal_name }}</div>
      @endif
      <div style="font-size:8.5px;color:#374151;margin-top:6px;line-height:1.75;">
        @if(!empty($company->address)){{ $company->address }}<br>@endif
        @if(!empty($company->city)){{ $company->city }}@if(!empty($company->postal_code)) – {{ $company->postal_code }}@endif@if(!empty($company->country)) · {{ $company->country }}@endif<br>@endif
        @if(!empty($company->phone))<span style="color:#9ca3af;">Tél :</span> {{ $company->phone }}@if(!empty($company->email)) &nbsp;·&nbsp; <span style="color:#9ca3af;">Email :</span> {{ $company->email }}@endif<br>@elseif(!empty($company->email))<span style="color:#9ca3af;">Email :</span> {{ $company->email }}<br>@endif
        @if(!empty($company->website))<span style="color:#9ca3af;">Web :</span> {{ $company->website }}<br>@endif
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
        <div style="font-size:7.5px;color:#9ca3af;margin-top:5px;padding-top:4px;border-top:1px dashed #e5e7eb;line-height:1.6;">
          {{ implode(' · ', $regs) }}
        </div>
      @endif
    </td>
  </tr>
</table>

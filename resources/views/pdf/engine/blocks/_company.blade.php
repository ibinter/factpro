<table style="width:100%;border-collapse:collapse;">
  <tr>
    <td style="vertical-align:top;width:70px;">
      @if(!empty($company->logo_url))
        <img src="{{ $company->logo_url }}" style="max-width:65px;max-height:55px;object-fit:contain;">
      @endif
    </td>
    <td style="vertical-align:top;padding-left:8px;">
      <div style="font-size:15px;font-weight:bold;color:{{ $primaryColor }};line-height:1.2;">
        {{ $company->name }}
      </div>
      @if(!empty($company->tagline))
        <div style="font-size:8px;color:#6b7280;font-style:italic;margin-top:1px;">{{ $company->tagline }}</div>
      @endif
      <div style="font-size:8.5px;color:#374151;margin-top:4px;line-height:1.6;">
        @if(!empty($company->address)){{ $company->address }}<br>@endif
        @if(!empty($company->city)){{ $company->city }}@if(!empty($company->postal_code)) – {{ $company->postal_code }}@endif<br>@endif
        @if(!empty($company->country)){{ $company->country }}<br>@endif
        @if(!empty($company->phone))<span style="color:#6b7280;">Tél:</span> {{ $company->phone }}<br>@endif
        @if(!empty($company->email))<span style="color:#6b7280;">Email:</span> {{ $company->email }}<br>@endif
        @if(!empty($company->website))<span style="color:#6b7280;">Web:</span> {{ $company->website }}<br>@endif
        @if(!empty($company->tax_number))<span style="color:#6b7280;">N° fiscal:</span> {{ $company->tax_number }}<br>@endif
        @if(!empty($company->rccm))<span style="color:#6b7280;">RCCM:</span> {{ $company->rccm }}<br>@endif
      </div>
    </td>
  </tr>
</table>

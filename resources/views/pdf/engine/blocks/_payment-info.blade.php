@php
  $hasIban   = !empty($company->iban);
  $hasMobile = !empty($company->mobile_money) || !empty($company->orange_money) || !empty($company->wave);

  $configuredMethods = collect($company->payment_methods ?? [])
      ->filter(fn($m) => !empty($m['enabled']));

  // Icônes SVG inline par type de paiement (encodées pour DomPDF)
  $icons = [
    'wave'          => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="12" fill="#1A73E8"/><path d="M5 14c1.5-3 3-4 5-4s3.5 2 5 2 3-2 5-2" stroke="#fff" stroke-width="2" stroke-linecap="round" fill="none"/></svg>',
    'orange_money'  => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="12" fill="#FF6200"/><text x="12" y="17" text-anchor="middle" fill="#fff" font-size="12" font-weight="bold">M</text></svg>',
    'mtn_momo'      => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="12" fill="#FFC400"/><text x="12" y="17" text-anchor="middle" fill="#000" font-size="9" font-weight="bold">MTN</text></svg>',
    'moov'          => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="12" cy="12" r="12" fill="#0077C8"/><text x="12" y="16" text-anchor="middle" fill="#fff" font-size="8" font-weight="bold">MV</text></svg>',
    'bank_transfer' => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="24" rx="4" fill="#374151"/><rect x="4" y="10" width="2" height="8" fill="#fff"/><rect x="8" y="10" width="2" height="8" fill="#fff"/><rect x="12" y="10" width="2" height="8" fill="#fff"/><rect x="16" y="10" width="2" height="8" fill="#fff"/><rect x="3" y="19" width="18" height="2" fill="#fff"/><polygon points="12,3 22,9 2,9" fill="#fff"/></svg>',
    'paypal'        => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="24" rx="4" fill="#003087"/><text x="12" y="17" text-anchor="middle" fill="#fff" font-size="10" font-weight="bold">PP</text></svg>',
    'cheque'        => '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="24" height="24" rx="4" fill="#6b7280"/><rect x="3" y="8" width="18" height="12" rx="1" fill="#fff" opacity="0.9"/><rect x="5" y="11" width="8" height="1.5" fill="#374151"/><rect x="5" y="14" width="5" height="1.5" fill="#374151"/></svg>',
  ];

  $methodLabels = [
    'wave'          => 'Wave',
    'orange_money'  => 'Orange Money',
    'mtn_momo'      => 'MTN MoMo',
    'moov'          => 'Moov Money',
    'bank_transfer' => 'Virement bancaire',
    'paypal'        => 'PayPal',
    'cheque'        => 'Chèque',
  ];
@endphp

{{-- ─── Moyens de paiement configurables ──────────────────────────── --}}
@if($configuredMethods->isNotEmpty())
<div style="border:1px solid #e5e7eb;border-top:3px solid {{ $primaryColor }};border-radius:4px;padding:11px 14px;margin-bottom:12px;background:#f9fafb;">
  <div style="font-size:7.5px;font-weight:bold;text-transform:uppercase;color:{{ $primaryColor }};margin-bottom:9px;letter-spacing:0.7px;">
    Moyens de paiement acceptés
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      @foreach($configuredMethods->values() as $idx => $method)
        @php
          $type = $method['type'] ?? '';
          $typeLabel = $method['label'] ?: ($methodLabels[$type] ?? ucfirst($type));
          $icon = $icons[$type] ?? $icons['bank_transfer'];
        @endphp
        <td style="vertical-align:top;padding-right:{{ $configuredMethods->count() > 1 ? '12px' : '0' }};padding-bottom:8px;width:{{ round(100 / min($configuredMethods->count(), 3)) }}%;">
          <div style="display:flex;align-items:center;margin-bottom:3px;">
            <span style="margin-right:5px;vertical-align:middle;">{!! $icon !!}</span>
            <span style="font-size:8px;font-weight:bold;color:#374151;vertical-align:middle;">{{ $typeLabel }}</span>
          </div>
          @if($type === 'bank_transfer')
            @if(!empty($method['bank_name']))
              <div style="font-size:8px;color:#374151;padding-left:19px;">{{ $method['bank_name'] }}</div>
            @endif
            @if(!empty($method['account_name']))
              <div style="font-size:8px;color:#374151;padding-left:19px;">Titulaire : {{ $method['account_name'] }}</div>
            @endif
            @if(!empty($method['iban']))
              <div style="font-size:8px;color:#374151;padding-left:19px;font-family:monospace;">{{ $method['iban'] }}</div>
            @endif
            @if(!empty($method['bic']))
              <div style="font-size:7.5px;color:#6b7280;padding-left:19px;">BIC : {{ $method['bic'] }}</div>
            @endif
          @elseif($type === 'paypal')
            @if(!empty($method['email']))
              <div style="font-size:8px;color:#374151;padding-left:19px;">{{ $method['email'] }}</div>
            @endif
          @else
            @if(!empty($method['number']))
              <div style="font-size:8.5px;color:#374151;font-weight:600;padding-left:19px;">{{ $method['number'] }}</div>
            @endif
          @endif
          @if(!empty($method['note']))
            <div style="font-size:7px;color:#9ca3af;margin-top:2px;padding-left:19px;">{{ $method['note'] }}</div>
          @endif
        </td>
        @if(($idx + 1) % 3 === 0 && $idx + 1 < $configuredMethods->count())
        </tr><tr>
        @endif
      @endforeach
    </tr>
  </table>
</div>
@endif

{{-- ─── Rétro-compatibilité (anciens champs plats) ────────────────── --}}
@if(($hasIban || $hasMobile) && $configuredMethods->isEmpty())
<div style="border:1px solid #e5e7eb;border-top:3px solid {{ $primaryColor }};border-radius:4px;padding:11px 14px;margin-bottom:12px;background:#f9fafb;">
  <div style="font-size:7.5px;font-weight:bold;text-transform:uppercase;color:{{ $primaryColor }};margin-bottom:9px;letter-spacing:0.7px;">
    Informations de règlement
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      @if($hasIban)
        <td style="vertical-align:top;padding-right:16px;width:50%;">
          <div style="display:flex;align-items:center;margin-bottom:3px;">
            <span style="margin-right:5px;">{!! $icons['bank_transfer'] !!}</span>
            <span style="font-size:8px;font-weight:bold;color:#374151;">Virement bancaire</span>
          </div>
          @if(!empty($company->bank_name))
            <div style="font-size:8px;color:#374151;padding-left:19px;">{{ $company->bank_name }}</div>
          @endif
          @if(!empty($company->iban))
            <div style="font-size:8px;color:#374151;font-family:monospace;padding-left:19px;">{{ $company->iban }}</div>
          @endif
          @if(!empty($company->bic))
            <div style="font-size:7.5px;color:#6b7280;padding-left:19px;">BIC : {{ $company->bic }}</div>
          @endif
        </td>
      @endif
      @if($hasMobile)
        <td style="vertical-align:top;">
          @if(!empty($company->orange_money))
            <div style="display:flex;align-items:center;margin-bottom:4px;">
              <span style="margin-right:5px;">{!! $icons['orange_money'] !!}</span>
              <span style="font-size:8px;color:#374151;"><strong>Orange Money :</strong> {{ $company->orange_money }}</span>
            </div>
          @endif
          @if(!empty($company->wave))
            <div style="display:flex;align-items:center;margin-bottom:4px;">
              <span style="margin-right:5px;">{!! $icons['wave'] !!}</span>
              <span style="font-size:8px;color:#374151;"><strong>Wave :</strong> {{ $company->wave }}</span>
            </div>
          @endif
          @if(!empty($company->mtn_money))
            <div style="display:flex;align-items:center;margin-bottom:4px;">
              <span style="margin-right:5px;">{!! $icons['mtn_momo'] !!}</span>
              <span style="font-size:8px;color:#374151;"><strong>MTN MoMo :</strong> {{ $company->mtn_money }}</span>
            </div>
          @endif
          @if(!empty($company->mobile_money))
            <div style="font-size:8px;color:#374151;margin-bottom:4px;">Mobile Money : {{ $company->mobile_money }}</div>
          @endif
        </td>
      @endif
    </tr>
  </table>
</div>
@endif

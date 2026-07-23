@extends('emails.layout')
@section('content')
<div style="text-align:center;margin-bottom:24px">
  <div style="font-size:48px">🎉</div>
  <div style="font-size:22px;font-weight:800;color:#002D5B;margin-top:8px">Paiement confirmé !</div>
</div>
<p class="greeting">Bonjour {{ $user->name }},</p>
<p class="text">Votre abonnement <strong>{{ $plan }}</strong> est maintenant actif. Merci pour votre confiance !</p>
<div class="highlight-box green">
  <div class="stats-row" style="margin:0">
    <div class="stat-box" style="background:#fff">
      <div class="value" style="color:#059669">✓</div>
      <div class="label">Plan activé</div>
    </div>
    <div class="stat-box" style="background:#fff">
      <div class="value" style="font-size:16px;color:#002D5B">{{ $plan }}</div>
      <div class="label">Votre plan</div>
    </div>
    <div class="stat-box" style="background:#fff">
      <div class="value" style="font-size:16px;color:#002D5B">{{ $amount }}</div>
      <div class="label">Montant</div>
    </div>
  </div>
</div>
<p class="text">Votre accès est immédiatement disponible. Commencez à créer vos factures !</p>
<div style="text-align:center;margin:28px 0">
  <a href="{{ url('/dashboard') }}" class="btn">Accéder à mon tableau de bord →</a>
</div>
@endsection
@section('footer_extra')
Référence de paiement : {{ $reference ?? 'N/A' }}
@endsection

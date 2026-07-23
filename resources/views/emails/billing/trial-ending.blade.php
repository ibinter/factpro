@extends('emails.layout')
@section('content')
<p class="greeting">Bonjour {{ $user->name }},</p>
<div class="highlight-box gold">
  <strong>⏰ Votre essai gratuit se termine dans {{ $days_left }} jour{{ $days_left > 1 ? 's' : '' }}</strong><br>
  <span style="font-size:13px">Date d'expiration : {{ $expires_at }}</span>
</div>
<p class="text">Ne perdez pas accès à vos données ! Passez à un plan payant pour continuer à utiliser IBIG FactPro sans interruption.</p>
<div class="highlight-box">
  <strong>Ce que vous perdrez si vous ne souscrivez pas :</strong>
  <ul style="margin-top:8px;padding-left:20px;font-size:14px;line-height:2;color:#374151">
    <li>Accès à vos {{ $docs_count ?? 0 }} documents créés</li>
    <li>Vos {{ $customers_count ?? 0 }} clients enregistrés</li>
    <li>Vos paramètres et configurations</li>
    <li>Vos modèles personnalisés</li>
  </ul>
</div>
<p class="text"><strong>Nos plans démarrent à 4 900 FCFA/mois.</strong> Aucun engagement, résiliation libre à tout moment.</p>
<div style="text-align:center;margin:28px 0">
  <a href="{{ url('/billing') }}" class="btn">Choisir mon plan →</a>
</div>
<p class="text" style="font-size:13px;color:#9ca3af">Des questions sur nos tarifs ? Répondez à cet email ou contactez-nous sur WhatsApp.</p>
@endsection

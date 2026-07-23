@extends('emails.layout')
@section('content')
<p class="greeting">Bonjour {{ $recipient_name }},</p>
<p class="text">{{ $company_name }} vous a envoyé un document via IBIG FactPro.</p>
<div class="highlight-box">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
    <div>
      <div style="font-size:13px;color:#6b7280">Document</div>
      <div style="font-size:18px;font-weight:800;color:#002D5B">{{ $document_type }} {{ $document_number }}</div>
    </div>
    <div style="text-align:right">
      <div style="font-size:13px;color:#6b7280">Montant</div>
      <div style="font-size:22px;font-weight:800;color:#002D5B">{{ $amount }}</div>
    </div>
  </div>
  @if($due_date)
  <div style="margin-top:12px;font-size:13px;color:#6b7280">
    Date d'échéance : <strong style="color:#e11d48">{{ $due_date }}</strong>
  </div>
  @endif
</div>
@if($message)
<div class="highlight-box" style="background:#f9fafb;border-left-color:#9ca3af">
  <div style="font-size:13px;color:#6b7280;margin-bottom:4px">Message de {{ $company_name }} :</div>
  <p style="font-size:14px;color:#374151">{{ $message }}</p>
</div>
@endif
<div style="text-align:center;margin:28px 0">
  @if($pdf_url)
  <a href="{{ $pdf_url }}" class="btn">Télécharger le PDF →</a>
  @endif
</div>
<p class="text" style="font-size:13px;color:#9ca3af">Ce document vous a été envoyé par {{ $company_name }}. Pour toute question, contactez directement votre prestataire.</p>
@endsection

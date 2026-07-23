@extends('emails.layout')
@section('content')
<p class="greeting">Bonjour {{ $user->name }} 👋</p>
<p class="text">Votre ticket de support a bien été créé. Notre équipe vous répondra dans les meilleurs délais.</p>
<div class="highlight-box">
  <div style="font-size:13px;color:#6b7280;margin-bottom:4px">Référence ticket</div>
  <div style="font-size:20px;font-weight:800;color:#002D5B">{{ $ticket->ticket_number }}</div>
  <div style="font-size:14px;color:#374151;margin-top:8px">{{ $ticket->subject }}</div>
  <span class="badge badge-blue" style="margin-top:8px">{{ ucfirst($ticket->category) }}</span>
</div>
<p class="text">Nous traitons les tickets dans l'ordre de réception. Priorité : <strong>{{ ucfirst($ticket->priority) }}</strong></p>
<div style="text-align:center;margin:28px 0">
  <a href="{{ url('/support/'.$ticket->id) }}" class="btn">Voir mon ticket →</a>
</div>
<p class="text" style="font-size:13px;color:#9ca3af">Besoin d'aide urgente ? Contactez-nous sur WhatsApp ou à support@ibigsoft.com</p>
@endsection

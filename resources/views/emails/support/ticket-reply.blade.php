@extends('emails.layout')
@section('content')
<p class="greeting">Bonjour {{ $user->name }},</p>
<p class="text">L'équipe IBIG a répondu à votre ticket <strong>{{ $ticket->ticket_number }}</strong>.</p>
<div class="highlight-box">
  <div style="font-size:12px;color:#6b7280;margin-bottom:8px">Réponse de l'équipe support :</div>
  <p style="font-size:15px;line-height:1.7;color:#1f2937">{{ $reply }}</p>
</div>
@if($ticket->status === 'resolved')
<div class="highlight-box green">
  <strong>✓ Votre ticket est marqué comme résolu.</strong><br>
  <span style="font-size:13px;color:#374151">Si votre problème persiste, vous pouvez rouvrir le ticket depuis votre espace.</span>
</div>
@endif
<div style="text-align:center;margin:28px 0">
  <a href="{{ url('/support/'.$ticket->id) }}" class="btn">Répondre ou consulter →</a>
</div>
@endsection

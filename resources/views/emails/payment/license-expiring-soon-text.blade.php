@php $jours = $daysLeft > 1 ? "{$daysLeft} jours" : '1 jour'; @endphp
IBIG FactPro — Votre licence expire dans {{ $jours }}
=======================================================

Bonjour,

Votre licence {{ $license->plan?->name ?? 'IBIG FactPro' }} expire le {{ $license->ends_at?->format('d/m/Y') ?? '—' }}.
Renouvelez maintenant pour ne pas perdre l'accès à votre espace de facturation.

Forfait : {{ $license->plan?->name ?? '—' }}
Clé de licence : {{ $license->license_key }}
Date d'expiration : {{ $license->ends_at?->format('d/m/Y') ?? '—' }}

Renouveler mon abonnement : {{ url('/billing/plans') }}

Après l'expiration, une période de tolérance vous sera accordée avant la suspension complète.

Cordialement,
L'équipe IBIG FactPro — factpro.ibigsoft.com

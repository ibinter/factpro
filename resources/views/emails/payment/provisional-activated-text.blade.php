IBIG FactPro — Accès provisoire accordé
========================================

Bonjour,

Un accès provisoire à votre espace {{ $license->plan?->name ?? 'IBIG FactPro' }}
a été activé pendant que nous finalisons la réception de votre virement.

IMPORTANT : L'accès sera suspendu si les fonds ne sont pas reçus avant le {{ $license->ends_at?->format('d/m/Y') ?? '—' }}.

Forfait : {{ $license->plan?->name ?? '—' }}
Clé de licence : {{ $license->license_key }}
Valide jusqu'au : {{ $license->ends_at?->format('d/m/Y') ?? '—' }}

Accéder à mon espace : {{ url('/billing') }}

Cordialement,
L'équipe IBIG FactPro — factpro.ibigsoft.com

IBIG FactPro — Votre licence a expiré
======================================

Bonjour,

Votre licence {{ $license->plan?->name ?? 'IBIG FactPro' }} a expiré le {{ $license->ends_at?->format('d/m/Y') ?? '—' }}.

Bonne nouvelle : vos données sont conservées.
Renouvelez votre abonnement pour retrouver l'accès complet.

Renouveler mon abonnement : {{ url('/billing/plans') }}

Pour toute question : support@ibigsoft.com

Cordialement,
L'équipe IBIG FactPro — factpro.ibigsoft.com

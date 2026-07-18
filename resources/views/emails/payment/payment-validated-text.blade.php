IBIG FactPro — Paiement confirmé — Licence activée !
======================================================

Félicitations !
Votre paiement a été confirmé et votre licence est désormais active.

Forfait : {{ $license->plan?->name ?? '—' }}
Clé de licence : {{ $license->license_key }}
Début : {{ $license->starts_at?->format('d/m/Y') ?? '—' }}
Expiration : {{ $license->ends_at?->format('d/m/Y') ?? '—' }}
Référence commande : {{ $order->order_number }}

Accédez à votre espace client : {{ url('/billing') }}

@if($receiptPath && file_exists($receiptPath))
Votre reçu de paiement est joint à cet email en PDF.
@endif

Merci de votre confiance.
L'équipe IBIG FactPro — factpro.ibigsoft.com

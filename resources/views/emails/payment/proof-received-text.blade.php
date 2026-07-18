IBIG FactPro — Preuve de paiement reçue
=========================================

Bonjour,

Nous avons bien reçu votre preuve de paiement pour l'abonnement {{ $order->plan?->name ?? $order->order_number }}.
Notre équipe la vérifiera dans les 24 à 48 heures ouvrables.

IMPORTANT : NE PAS RENVOYER votre preuve — elle est déjà enregistrée.

Référence commande : {{ $order->order_number }}
Référence transaction : {{ $transaction->internal_reference }}
Forfait : {{ $order->plan?->name ?? '—' }}
Montant déclaré : {{ $transaction->amount_declared ? number_format((float) $transaction->amount_declared, 0, ',', ' ') . ' ' . $transaction->currency : '—' }}

Vous recevrez un email de confirmation dès que la validation sera effectuée.

Cordialement,
L'équipe IBIG FactPro — factpro.ibigsoft.com

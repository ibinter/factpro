IBIG FactPro — Preuve de paiement refusée
==========================================

Bonjour,

Après vérification, votre preuve de paiement pour la commande {{ $order->order_number }} n'a pas pu être validée.

Motif du refus : {{ $reason }}

Que faire ?
- Vérifiez les informations transmises (référence de transaction, montant, justificatif).
- Soumettez une nouvelle preuve de paiement : {{ url('/billing/checkout/' . $order->id) }}
- Ou contactez notre support pour toute question.

Cordialement,
L'équipe IBIG FactPro — factpro.ibigsoft.com

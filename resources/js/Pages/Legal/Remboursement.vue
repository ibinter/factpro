<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Politique de remboursement" last-updated="23 juillet 2026" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Principe général</h2>
    <p>
      IBIG Soft SARL propose des abonnements à <strong>IBIG FactPro</strong> sur la base de forfaits
      mensuels ou annuels. La présente politique définit les conditions dans lesquelles un remboursement
      peut être accordé. Elle s'applique à tous les abonnés depuis le 23 juillet 2026.
    </p>

    <h2>2. Période d'essai gratuite</h2>
    <p>
      Tout nouvel abonné bénéficie d'une <strong>période d'essai gratuite de 14 jours</strong>
      sans engagement et sans saisie d'informations de paiement. Aucune facturation n'est effectuée
      durant cette période. À l'issue de l'essai, l'abonnement n'est activé que sur action explicite
      de l'utilisateur.
    </p>

    <h2>3. Remboursement dans les 7 jours suivant le premier paiement</h2>
    <p>
      Pour tout premier abonnement payant, IBIG Soft SARL accorde une <strong>garantie satisfait
      ou remboursé de 7 jours</strong> à compter de la date du premier paiement. Cette garantie
      ne s'applique qu'une seule fois par client et ne couvre pas les renouvellements.
    </p>
    <ul>
      <li>La demande doit être adressée à <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a> avant l'expiration du délai.</li>
      <li>Le remboursement est effectué par le même moyen de paiement que l'achat, dans un délai de 10 jours ouvrés.</li>
      <li>L'accès à la plateforme est désactivé dès validation du remboursement.</li>
    </ul>

    <h2>4. Abonnements mensuels</h2>
    <p>
      Les abonnements mensuels sont facturés par mois entier. En dehors de la garantie initiale de 7 jours,
      aucun remboursement proratisé n'est accordé pour les jours non consommés d'un mois en cours.
      L'annulation prend effet à la fin de la période mensuelle en cours.
    </p>

    <h2>5. Abonnements annuels</h2>
    <p>
      Les abonnements annuels bénéficient d'une réduction tarifaire en contrepartie d'un engagement
      de 12 mois. En cas de résiliation anticipée :
    </p>
    <ul>
      <li>Durant les 30 premiers jours suivant le premier paiement annuel : remboursement possible au prorata des mois restants, sous déduction d'une pénalité administrative de 10 %.</li>
      <li>Au-delà de 30 jours : aucun remboursement pour les mois restants de l'abonnement annuel.</li>
      <li>L'accès reste actif jusqu'à la fin de la période payée.</li>
    </ul>

    <h2>6. Cas de remboursement exceptionnels</h2>
    <p>
      Un remboursement peut être accordé exceptionnellement dans les cas suivants, sur présentation
      de justificatifs :
    </p>
    <ul>
      <li>Indisponibilité de la plateforme dépassant les seuils SLA sans crédit de service suffisant.</li>
      <li>Double facturation ou erreur de facturation de la part d'IBIG Soft SARL.</li>
      <li>Résiliation de l'activité du client dûment justifiée (fermeture d'entreprise avec preuve RCCM).</li>
    </ul>

    <h2>7. Demande de remboursement</h2>
    <p>Pour soumettre une demande de remboursement :</p>
    <ul>
      <li>Envoyez un email à <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a></li>
      <li>Objet : <em>« Demande de remboursement — [votre identifiant client] »</em></li>
      <li>Joignez votre numéro de facture et la raison de la demande.</li>
    </ul>
    <p>
      Notre équipe traitera votre demande dans un délai de <strong>5 jours ouvrés</strong>.
    </p>

    <h2>8. Droit applicable</h2>
    <p>
      La présente politique est régie par le droit ivoirien et les dispositions de l'OHADA.
      Tout litige sera soumis aux juridictions compétentes d'Abidjan, Côte d'Ivoire.
    </p>

  </LegalLayout>
</template>

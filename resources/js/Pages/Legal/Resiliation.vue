<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>
<template>
  <LegalLayout title="Politique de résiliation" :can-login="canLogin" :can-register="canRegister">
    <h2>1. Résiliation par l'utilisateur</h2>
    <p>Vous pouvez résilier votre abonnement IBIG FactPro à tout moment, sans frais ni pénalité, depuis votre espace abonnement dans les paramètres de votre compte.</p>
    <p>Après résiliation :</p>
    <ul>
      <li>Votre accès reste actif jusqu'à la fin de la période déjà payée.</li>
      <li>Aucun remboursement n'est effectué pour la période en cours.</li>
      <li>Vos données sont conservées pendant 12 mois, période durant laquelle vous pouvez les exporter.</li>
      <li>Après 12 mois, vos données peuvent être supprimées définitivement.</li>
    </ul>

    <h2>2. Résiliation par IBIG Soft</h2>
    <p>IBIG Soft se réserve le droit de résilier ou suspendre votre accès dans les cas suivants :</p>
    <ul>
      <li>Non-paiement après expiration du délai de grâce.</li>
      <li>Violation des Conditions Générales d'Utilisation.</li>
      <li>Activité frauduleuse ou illégale détectée.</li>
      <li>Décision judiciaire ou administrative.</li>
    </ul>
    <p>En cas de résiliation par IBIG Soft pour faute, aucun remboursement ne sera effectué.</p>

    <h2>3. Période de grâce</h2>
    <p>Après expiration de votre abonnement, une période de grâce de 7 jours vous permet de renouveler sans interruption de service. Passé ce délai, l'accès est suspendu et les données passent en lecture seule pendant 12 mois.</p>

    <h2>4. Export de données</h2>
    <p>Avant ou après résiliation, vous pouvez exporter l'ensemble de vos données (clients, documents, produits, historiques) aux formats PDF, Excel et CSV depuis votre espace.</p>

    <h2>5. Remboursements</h2>
    <p>Les abonnements ne font pas l'objet de remboursement au prorata, sauf disposition contractuelle spécifique convenue par écrit avec IBIG Soft. En cas de dysfonctionnement grave imputable à IBIG Soft, un avoir ou remboursement peut être accordé au cas par cas.</p>

    <h2>6. Contact</h2>
    <p>Pour toute question sur la résiliation : <a href="mailto:factpro@ibigsoft.com">factpro@ibigsoft.com</a></p>
  </LegalLayout>
</template>

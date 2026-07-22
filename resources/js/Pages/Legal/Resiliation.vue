<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Politique de résiliation" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Résiliation par l'utilisateur</h2>
    <p>
      Vous pouvez résilier votre abonnement IBIG FactPro à tout moment,
      <strong>sans frais ni pénalité</strong>, directement depuis votre espace
      <em>Paramètres → Abonnement</em> dans l'application.
    </p>
    <ul>
      <li>Votre accès reste <strong>actif jusqu'à la fin</strong> de la période déjà payée.</li>
      <li>Aucun remboursement n'est effectué pour la période en cours.</li>
      <li>Vos données sont conservées en lecture seule pendant <strong>12 mois</strong> après résiliation.</li>
      <li>Vous pouvez exporter toutes vos données à tout moment pendant cette période.</li>
      <li>Après 12 mois, vos données peuvent être définitivement supprimées.</li>
    </ul>

    <h2>2. Résiliation par IBIG Soft</h2>
    <p>IBIG SARL se réserve le droit de résilier ou suspendre votre accès dans les cas suivants :</p>
    <ul>
      <li>Non-paiement après expiration du délai de grâce de 7 jours.</li>
      <li>Violation des Conditions Générales d'Utilisation.</li>
      <li>Activité frauduleuse ou illégale avérée.</li>
      <li>Décision judiciaire ou administrative le requérant.</li>
    </ul>
    <p>
      En cas de résiliation par IBIG Soft pour faute avérée,
      <strong>aucun remboursement</strong> ne sera effectué.
    </p>

    <h2>3. Période de grâce après expiration</h2>
    <p>
      Après expiration de votre abonnement, une <strong>période de grâce de 7 jours</strong>
      vous permet de renouveler sans interruption de service.
    </p>
    <ul>
      <li>Pendant cette période, le service reste pleinement accessible.</li>
      <li>Passé ce délai, l'accès est suspendu automatiquement.</li>
      <li>Les données passent en lecture seule pendant 12 mois supplémentaires.</li>
    </ul>

    <h2>4. Export de vos données</h2>
    <p>
      Avant ou après résiliation, vous pouvez exporter <strong>l'intégralité</strong> de vos données
      depuis votre espace : clients, documents, produits, paiements et historiques.
    </p>
    <ul>
      <li>Export PDF de tous vos documents commerciaux.</li>
      <li>Export Excel / CSV de vos données clients, produits et transactions.</li>
      <li>Export comptable au format FEC (Fichier des Écritures Comptables).</li>
    </ul>

    <h2>5. Remboursements</h2>
    <p>
      Les abonnements ne font pas l'objet de remboursement au prorata,
      sauf disposition contractuelle spécifique convenue par écrit avec IBIG SARL.
    </p>
    <ul>
      <li>En cas de dysfonctionnement grave <strong>imputable à IBIG SARL</strong>, un avoir ou remboursement peut être accordé au cas par cas.</li>
      <li>Les demandes de remboursement doivent être adressées par email sous 14 jours.</li>
    </ul>

    <h2>6. Réactivation</h2>
    <p>
      Après résiliation, vous pouvez réactiver votre compte à tout moment pendant la période
      de conservation des données (12 mois). Vos données seront entièrement restaurées.
    </p>

    <h2>7. Contact</h2>
    <p>
      Pour toute question sur la résiliation ou pour toute demande particulière :<br>
      <a href="mailto:factpro@ibigsoft.com">factpro@ibigsoft.com</a>
    </p>

  </LegalLayout>
</template>

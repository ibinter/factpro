<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Data Processing Agreement (DPA)" last-updated="23 juillet 2026" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Objet et parties</h2>
    <p>
      Le présent Accord de Traitement des Données (<strong>DPA — Data Processing Agreement</strong>)
      est conclu entre <strong>IBIG Soft SARL</strong> (ci-après « le Sous-traitant »)
      et tout abonné d'IBIG FactPro agissant en qualité de Responsable du Traitement
      au sens du RGPD (UE 2016/679) (ci-après « le Responsable »).
      Il s'applique lorsque le Responsable traite, via IBIG FactPro, des données personnelles
      de résidents de l'Espace Économique Européen ou de toute juridiction imposant un accord
      similaire.
    </p>

    <h2>2. Rôles et responsabilités</h2>
    <ul>
      <li><strong>Responsable du traitement :</strong> l'abonné détermine les finalités et les moyens du traitement des données de ses propres clients saisis dans IBIG FactPro.</li>
      <li><strong>Sous-traitant :</strong> IBIG Soft SARL traite ces données uniquement sur instruction documentée du Responsable et aux fins exclusives de la fourniture du service.</li>
    </ul>

    <h2>3. Instructions documentées</h2>
    <p>
      En utilisant IBIG FactPro et en saisissant des données personnelles dans la plateforme,
      le Responsable donne à IBIG Soft SARL l'instruction documentée de traiter ces données
      aux seules fins de la fourniture, de la maintenance et de l'amélioration du service.
      Toute instruction complémentaire doit être formulée par écrit à
      <a href="mailto:dpo@ibigsoft.com">dpo@ibigsoft.com</a>.
    </p>

    <h2>4. Engagements d'IBIG Soft SARL (Sous-traitant)</h2>
    <ul>
      <li>Traiter les données personnelles uniquement sur instruction du Responsable, sauf obligation légale contraire.</li>
      <li>Garantir la confidentialité des données traitées et imposer des obligations équivalentes aux personnels autorisés.</li>
      <li>Mettre en œuvre les mesures de sécurité techniques et organisationnelles décrites dans notre <a href="/legal/securite">Politique de sécurité</a>.</li>
      <li>Notifier le Responsable sans délai indu (et au plus tard dans les 72 heures) de toute violation de données personnelles.</li>
      <li>Assister le Responsable dans la réponse aux demandes d'exercice de droits des personnes concernées.</li>
      <li>Supprimer ou restituer toutes les données personnelles à la fin du service, selon le choix du Responsable.</li>
      <li>Mettre à disposition toutes les informations nécessaires pour démontrer le respect des obligations du présent DPA.</li>
    </ul>

    <h2>5. Sous-traitants ultérieurs</h2>
    <p>
      IBIG Soft SARL peut faire appel à des sous-traitants ultérieurs (hébergeur, outil d'envoi d'emails, etc.).
      La liste des sous-traitants ultérieurs actuels est disponible sur demande à
      <a href="mailto:dpo@ibigsoft.com">dpo@ibigsoft.com</a>.
      IBIG Soft SARL s'assure que ces sous-traitants offrent des garanties suffisantes
      et les soumet à des obligations contractuelles équivalentes au présent DPA.
      Le Responsable sera informé par email de tout changement de sous-traitant ultérieur
      au moins 30 jours avant son entrée en vigueur, avec possibilité de s'y opposer.
    </p>

    <h2>6. Transferts internationaux</h2>
    <p>
      Tout transfert de données personnelles vers un pays tiers à l'EEE est encadré
      par des Clauses Contractuelles Types (CCT) adoptées par la Commission Européenne
      (Décision d'exécution UE 2021/914), ou par d'autres mécanismes de transfert appropriés.
    </p>

    <h2>7. Durée et fin du traitement</h2>
    <p>
      Le présent DPA entre en vigueur à la date d'acceptation des CGU et prend fin
      à la résiliation ou l'expiration de l'abonnement. À l'issue du contrat,
      IBIG Soft SARL supprime ou anonymise les données personnelles du Responsable
      dans un délai de <strong>90 jours</strong>, sauf obligation légale de conservation.
      Une exportation des données peut être demandée avant la résiliation.
    </p>

    <h2>8. Audits</h2>
    <p>
      Le Responsable peut demander, une fois par an, une attestation de conformité
      ou un rapport d'audit de sécurité. Pour toute demande d'audit spécifique,
      les modalités pratiques sont à convenir avec IBIG Soft SARL, les coûts
      éventuels étant à la charge du Responsable.
    </p>

    <h2>9. Contact DPO</h2>
    <p>
      Toute question relative au présent DPA doit être adressée à :
      <a href="mailto:dpo@ibigsoft.com">dpo@ibigsoft.com</a>.
    </p>

  </LegalLayout>
</template>

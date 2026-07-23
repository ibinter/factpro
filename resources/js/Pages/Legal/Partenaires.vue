<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Conditions partenaires et revendeurs" last-updated="23 juillet 2026" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Programme IBIG Partners</h2>
    <p>
      IBIG Soft SARL propose un programme de partenariat (<strong>IBIG Partners</strong>)
      destiné aux revendeurs, intégrateurs, consultants et agents commerciaux souhaitant
      distribuer ou recommander <strong>IBIG FactPro</strong> auprès de leurs clients
      dans la zone OHADA et au-delà. Les présentes conditions s'appliquent à tout partenaire
      admis dans le programme à compter du 23 juillet 2026.
    </p>

    <h2>2. Catégories de partenaires</h2>
    <ul>
      <li><strong>Partenaire Affilié :</strong> recommande IBIG FactPro via un lien de parrainage et perçoit une commission sur les abonnements souscrits.</li>
      <li><strong>Revendeur Agréé :</strong> revend IBIG FactPro en son nom propre, avec une remise négociée, et assure le premier niveau de support à ses clients.</li>
      <li><strong>Intégrateur Certifié :</strong> déploie et configure IBIG FactPro pour le compte de ses clients, avec accès à une formation et une certification technique.</li>
      <li><strong>Partenaire Stratégique :</strong> accord cadre personnalisé pour les grandes structures (banques, groupes d'entreprises, associations professionnelles).</li>
    </ul>

    <h2>3. Conditions d'admission</h2>
    <p>Pour rejoindre le programme IBIG Partners, le candidat doit :</p>
    <ul>
      <li>Être une personne morale régulièrement immatriculée au RCCM ou registre équivalent dans son pays.</li>
      <li>Exercer une activité compatible (informatique, conseil, comptabilité, formation, distribution de logiciels).</li>
      <li>Signer le contrat de partenariat et les présentes conditions.</li>
      <li>Ne pas distribuer de produits directement concurrents à IBIG FactPro sans accord préalable.</li>
    </ul>

    <h2>4. Rémunération et commissions</h2>
    <ul>
      <li><strong>Partenaire Affilié :</strong> commission de 20 % sur le premier paiement de chaque client référencé, versée le mois suivant l'encaissement.</li>
      <li><strong>Revendeur Agréé :</strong> remise de 25 % à 35 % sur le tarif public, selon le volume annuel d'abonnements vendus.</li>
      <li><strong>Intégrateur Certifié :</strong> remise de 30 % + prime de fidélisation annuelle si objectifs atteints.</li>
      <li>Les commissions sont versées par virement bancaire ou Mobile Money (selon disponibilité) dans les 30 jours suivant la période de référence.</li>
    </ul>

    <h2>5. Obligations du partenaire</h2>
    <ul>
      <li>Promouvoir IBIG FactPro de manière éthique, en ne faisant que des affirmations vérifiables et conformes à la documentation officielle.</li>
      <li>Ne pas modifier les logiciels, interfaces ou documents contractuels d'IBIG Soft SARL sans autorisation écrite.</li>
      <li>Respecter la charte graphique et les directives de marque fournies par IBIG Soft SARL.</li>
      <li>Signaler tout incident ou réclamation client dans les 48 heures.</li>
      <li>Ne pas démarcher activement les clients directs d'IBIG Soft SARL sans accord préalable.</li>
    </ul>

    <h2>6. Propriété intellectuelle et confidentialité</h2>
    <p>
      Le partenaire reconnaît qu'IBIG FactPro et tous les éléments associés (marques, codes, documentation)
      sont la propriété exclusive d'IBIG Soft SARL. Le partenaire s'engage à la confidentialité
      concernant les informations commerciales, techniques et financières partagées dans le cadre
      du programme, pendant la durée du partenariat et 3 ans après sa fin.
    </p>

    <h2>7. Durée et résiliation</h2>
    <p>
      Le contrat de partenariat est conclu pour une durée d'un an, renouvelable par tacite reconduction.
      Chaque partie peut résilier avec un préavis de <strong>30 jours</strong> par email recommandé.
      En cas de manquement grave aux obligations, IBIG Soft SARL peut résilier immédiatement
      et retenir les commissions dues si un préjudice est établi.
    </p>

    <h2>8. Candidature et contact</h2>
    <p>
      Pour rejoindre le programme IBIG Partners ou obtenir plus d'informations :
      <a href="mailto:contact@ibigsoft.com">contact@ibigsoft.com</a> — objet <em>« Candidature IBIG Partners »</em>.
    </p>

  </LegalLayout>
</template>

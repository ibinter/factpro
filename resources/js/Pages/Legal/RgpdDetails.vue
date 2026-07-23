<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Informations RGPD détaillées" last-updated="23 juillet 2026" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Introduction</h2>
    <p>
      La présente page complète notre <a href="/legal/confidentialite">Politique de confidentialité</a>
      en fournissant des informations détaillées sur le traitement des données personnelles
      conformément au <strong>Règlement Général sur la Protection des Données (RGPD — UE 2016/679)</strong>
      et à la législation ivoirienne sur la protection des données personnelles.
      Ces informations s'appliquent notamment aux clients d'IBIG FactPro dont les données
      ou celles de leurs propres clients sont soumises au RGPD (résidant ou traitant des
      données de résidents de l'Espace Économique Européen).
    </p>

    <h2>2. Responsable du traitement</h2>
    <p>
      <strong>IBIG Soft SARL</strong> (Intermark Business International Group)<br>
      Siège social : Abidjan, République de Côte d'Ivoire<br>
      Email : <a href="mailto:contact@ibigsoft.com">contact@ibigsoft.com</a><br>
      DPO (Délégué à la Protection des Données) : <a href="mailto:dpo@ibigsoft.com">dpo@ibigsoft.com</a>
    </p>

    <h2>3. Bases légales des traitements</h2>
    <ul>
      <li><strong>Exécution du contrat (Art. 6.1.b RGPD) :</strong> traitement des données nécessaires à la fourniture du service IBIG FactPro (compte, facturation, support).</li>
      <li><strong>Obligation légale (Art. 6.1.c) :</strong> conservation des données comptables et fiscales conformément aux exigences légales ivoiriennes et OHADA.</li>
      <li><strong>Intérêt légitime (Art. 6.1.f) :</strong> amélioration du service, détection des fraudes, sécurité de la plateforme.</li>
      <li><strong>Consentement (Art. 6.1.a) :</strong> envoi de communications marketing, utilisation de cookies non essentiels.</li>
    </ul>

    <h2>4. Catégories de données traitées</h2>
    <ul>
      <li><strong>Données d'identification :</strong> nom, prénom, email, téléphone, fonction, entreprise.</li>
      <li><strong>Données de connexion :</strong> adresse IP, identifiants de session, journaux d'accès.</li>
      <li><strong>Données commerciales :</strong> factures, devis, coordonnées des clients de l'abonné, montants.</li>
      <li><strong>Données de paiement :</strong> référence de transaction (les numéros de carte ne sont jamais stockés).</li>
      <li><strong>Données de navigation :</strong> cookies techniques et analytiques (voir <a href="/legal/cookies">Politique cookies</a>).</li>
    </ul>

    <h2>5. Durées de conservation</h2>
    <ul>
      <li>Données de compte actif : pendant toute la durée de l'abonnement.</li>
      <li>Données de compte résilié : 3 ans après la résiliation (prescriptions légales).</li>
      <li>Données comptables et fiscales : 10 ans conformément aux obligations OHADA.</li>
      <li>Journaux de sécurité : 12 mois glissants.</li>
      <li>Données de prospects non convertis : 3 ans après le dernier contact.</li>
    </ul>

    <h2>6. Transferts internationaux de données</h2>
    <p>
      Les données sont principalement stockées sur des serveurs hébergés en Europe (Union Européenne).
      Certains sous-traitants techniques peuvent être localisés hors UE ; dans ce cas,
      IBIG Soft SARL s'assure que les transferts sont encadrés par des <strong>Clauses Contractuelles Types (CCT)</strong>
      approuvées par la Commission Européenne, ou par d'autres garanties appropriées.
    </p>

    <h2>7. Droits des personnes concernées</h2>
    <p>Conformément au RGPD, vous disposez des droits suivants :</p>
    <ul>
      <li><strong>Droit d'accès (Art. 15) :</strong> obtenir une copie de vos données personnelles.</li>
      <li><strong>Droit de rectification (Art. 16) :</strong> corriger des données inexactes ou incomplètes.</li>
      <li><strong>Droit à l'effacement (Art. 17) :</strong> demander la suppression de vos données dans les cas prévus par le RGPD.</li>
      <li><strong>Droit à la limitation (Art. 18) :</strong> restreindre certains traitements.</li>
      <li><strong>Droit à la portabilité (Art. 20) :</strong> recevoir vos données dans un format structuré et lisible par machine.</li>
      <li><strong>Droit d'opposition (Art. 21) :</strong> vous opposer à certains traitements fondés sur l'intérêt légitime ou à des fins de prospection.</li>
      <li><strong>Droit de retrait du consentement :</strong> à tout moment pour les traitements basés sur le consentement, sans effet rétroactif.</li>
    </ul>
    <p>
      Pour exercer vos droits : <a href="mailto:dpo@ibigsoft.com">dpo@ibigsoft.com</a>.
      Nous répondrons dans un délai d'un mois (prorogeable à trois mois pour les demandes complexes).
    </p>

    <h2>8. Droit de réclamation</h2>
    <p>
      Vous avez le droit d'introduire une réclamation auprès de l'autorité de contrôle compétente
      dans votre pays de résidence. Pour les résidents de l'UE, il s'agit de l'autorité nationale
      de protection des données de votre État membre.
    </p>

    <h2>9. Contact DPO</h2>
    <p>
      Notre Délégué à la Protection des Données est joignable à :
      <a href="mailto:dpo@ibigsoft.com">dpo@ibigsoft.com</a>.
    </p>

  </LegalLayout>
</template>

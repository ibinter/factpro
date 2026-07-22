<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Conditions Générales d'Utilisation" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Objet</h2>
    <p>
      Les présentes Conditions Générales d'Utilisation (CGU) régissent l'accès et l'utilisation
      du logiciel SaaS <strong>IBIG FactPro</strong>, édité par IBIG SARL – Intermark Business
      International Group. En créant un compte, l'utilisateur accepte sans réserve les présentes CGU.
    </p>

    <h2>2. Description du service</h2>
    <p>
      IBIG FactPro est un logiciel de facturation professionnelle en ligne (SaaS) permettant :
    </p>
    <ul>
      <li>La création et gestion de devis, factures, bons de commande, reçus et bulletins de paie.</li>
      <li>La gestion des clients, produits, stocks et trésorerie.</li>
      <li>L'encaissement via Mobile Money (Wave, Orange Money, MTN, Moov, CinetPay, Flutterwave).</li>
      <li>L'export des documents en PDF, Excel et CSV.</li>
      <li>La caisse tactile POS avec impression thermique 58/80 mm.</li>
    </ul>

    <h2>3. Accès au service</h2>
    <p>
      L'accès est conditionné à la création d'un compte avec une adresse email valide.
      Un <strong>essai gratuit de 7 jours</strong> est offert sans carte bancaire.
      À l'issue de l'essai, un abonnement payant est requis pour continuer.
    </p>

    <h2>4. Compte utilisateur</h2>
    <ul>
      <li>L'utilisateur est responsable de la confidentialité de ses identifiants.</li>
      <li>Toute utilisation du compte est réputée effectuée par son titulaire.</li>
      <li>Tout accès non autorisé doit être signalé immédiatement à IBIG SARL.</li>
      <li>Un compte ne peut être partagé entre plusieurs personnes sans abonnement adapté.</li>
    </ul>

    <h2>5. Abonnements et paiements</h2>
    <p>
      Les abonnements sont mensuels ou annuels, renouvelés automatiquement.
      Les tarifs sont consultables sur la <a href="/pricing">page des tarifs</a>.
    </p>
    <ul>
      <li>Les prix sont exprimés en FCFA, EUR et USD selon le pays.</li>
      <li>Toute période commencée est due dans son intégralité.</li>
      <li>Les tarifs peuvent être révisés avec un préavis de 30 jours par email.</li>
      <li>Aucun remboursement au prorata sauf dysfonctionnement grave imputable à IBIG SARL.</li>
    </ul>

    <h2>6. Propriété des données</h2>
    <p>
      L'utilisateur conserve la <strong>pleine propriété</strong> de toutes les données qu'il saisit
      (clients, factures, produits, etc.). IBIG SARL traite ces données uniquement pour fournir
      le service, conformément à la <a href="/legal/confidentialite">Politique de confidentialité</a>.
    </p>

    <h2>7. Usages acceptables</h2>
    <p>L'utilisateur s'engage à ne pas utiliser IBIG FactPro pour :</p>
    <ul>
      <li>Émettre des documents frauduleux, falsifiés ou trompeurs.</li>
      <li>Toute activité illégale ou contraire aux lois ivoiriennes et OHADA.</li>
      <li>Tenter de contourner les mesures de sécurité du logiciel.</li>
      <li>Revendre ou sous-licencier l'accès sans autorisation écrite de IBIG SARL.</li>
    </ul>

    <h2>8. Disponibilité du service</h2>
    <p>
      IBIG SARL s'engage à maintenir une disponibilité de <strong>99 %</strong> mensuelle.
      Des interruptions planifiées pour maintenance peuvent survenir avec préavis
      par email ou notification dans l'application.
    </p>

    <h2>9. Résiliation</h2>
    <p>
      L'utilisateur peut résilier son abonnement à tout moment depuis son espace compte.
      Les modalités complètes sont décrites dans la
      <a href="/legal/resiliation">Politique de résiliation</a>.
    </p>

    <h2>10. Limitation de responsabilité</h2>
    <p>
      La responsabilité de IBIG SARL est limitée au montant des abonnements payés
      au cours des 3 derniers mois précédant le sinistre. IBIG SARL n'est pas
      responsable des dommages indirects, perte de revenus ou perte de données.
    </p>

    <h2>11. Modifications des CGU</h2>
    <p>
      IBIG SARL peut modifier les présentes CGU à tout moment.
      L'utilisateur sera notifié par email <strong>30 jours avant</strong> toute modification
      substantielle. La poursuite de l'utilisation vaut acceptation des nouvelles CGU.
    </p>

    <h2>12. Droit applicable</h2>
    <p>
      Les présentes CGU sont soumises au droit ivoirien et aux textes OHADA applicables.
      Tout litige sera porté devant les tribunaux compétents d'Abidjan, Côte d'Ivoire.
    </p>

  </LegalLayout>
</template>

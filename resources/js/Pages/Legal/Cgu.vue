<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>
<template>
  <LegalLayout title="Conditions générales d'utilisation" :can-login="canLogin" :can-register="canRegister">
    <h2>1. Objet</h2>
    <p>Les présentes Conditions Générales d'Utilisation (CGU) régissent l'accès et l'utilisation du logiciel SaaS IBIG FactPro, édité par IBIG SARL. En vous inscrivant ou en utilisant le service, vous acceptez sans réserve les présentes CGU.</p>

    <h2>2. Description du service</h2>
    <p>IBIG FactPro est une solution SaaS de facturation professionnelle accessible via navigateur web et installable en tant qu'application progressive (PWA). Le service comprend notamment : gestion des devis et factures, caisse POS, gestion des stocks, comptabilité, CRM, module RH, API REST, et portail client.</p>

    <h2>3. Accès au service</h2>
    <p>L'accès au service est conditionné à la création d'un compte et à l'acceptation des présentes CGU. Un essai gratuit de 7 jours est proposé sans carte bancaire. Au-delà, l'accès est soumis à la souscription d'un abonnement payant.</p>

    <h2>4. Compte utilisateur</h2>
    <p>Vous êtes responsable de la confidentialité de vos identifiants et de toutes les actions effectuées depuis votre compte. Vous devez informer IBIG Soft immédiatement en cas d'utilisation non autorisée. IBIG Soft se réserve le droit de suspendre tout compte en cas d'utilisation abusive.</p>

    <h2>5. Abonnements et paiements</h2>
    <p>Les abonnements sont sans engagement et facturés à la période choisie (mensuelle ou annuelle). Les tarifs sont indiqués en FCFA (XOF) hors taxes. IBIG Soft se réserve le droit de modifier ses tarifs avec un préavis de 30 jours.</p>

    <h2>6. Données et confidentialité</h2>
    <p>Vos données sont traitées conformément à notre <a href="/legal/confidentialite">Politique de confidentialité</a>. Vous restez propriétaire de vos données. IBIG Soft s'engage à ne pas les vendre à des tiers.</p>

    <h2>7. Utilisation acceptable</h2>
    <p>Il est interdit d'utiliser IBIG FactPro pour : des activités illégales, la génération de faux documents, des activités frauduleuses, l'envoi de spams, ou toute utilisation portant atteinte aux droits de tiers.</p>

    <h2>8. Disponibilité du service</h2>
    <p>IBIG Soft s'efforce de maintenir le service disponible 24h/24 et 7j/7. Des interruptions temporaires peuvent survenir pour maintenance. IBIG Soft ne garantit pas une disponibilité de 100 % sauf dans le cadre d'un forfait Enterprise avec SLA spécifique.</p>

    <h2>9. Résiliation</h2>
    <p>Vous pouvez résilier votre abonnement à tout moment depuis votre espace. L'accès reste actif jusqu'à la fin de la période payée. Voir notre <a href="/legal/resiliation">Politique de résiliation</a> pour plus de détails.</p>

    <h2>10. Limitation de responsabilité</h2>
    <p>IBIG SARL ne saurait être tenu responsable des pertes de données imputables à l'utilisateur, des dommages indirects ou immatériels, ni de l'utilisation faite par l'utilisateur des documents générés. La responsabilité de IBIG SARL est limitée au montant des sommes versées au cours des 3 derniers mois.</p>

    <h2>11. Modification des CGU</h2>
    <p>IBIG Soft peut modifier les présentes CGU. Les utilisateurs seront informés par email avec un préavis de 15 jours. La poursuite de l'utilisation du service après ce délai vaut acceptation des nouvelles CGU.</p>

    <h2>12. Droit applicable et litiges</h2>
    <p>Les présentes CGU sont soumises au droit ivoirien. Tout litige sera soumis à une tentative de résolution amiable, puis aux juridictions compétentes d'Abidjan, Côte d'Ivoire.</p>

    <p class="mt-8 text-gray-400 text-xs">Pour toute question : <a href="mailto:factpro@ibigsoft.com">factpro@ibigsoft.com</a></p>
  </LegalLayout>
</template>

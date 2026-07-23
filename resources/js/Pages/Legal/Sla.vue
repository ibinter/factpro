<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Accord de niveau de service (SLA)" last-updated="23 juillet 2026" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Objet et champ d'application</h2>
    <p>
      Le présent Accord de Niveau de Service (<strong>SLA</strong>) définit les engagements de disponibilité,
      de performance et de support qu'IBIG Soft SARL s'engage à respecter envers les abonnés
      du logiciel <strong>IBIG FactPro</strong>. Il complète les Conditions Générales d'Utilisation
      et s'applique à tous les forfaits actifs souscrits à compter du 23 juillet 2026.
    </p>

    <h2>2. Disponibilité garantie</h2>
    <p>
      IBIG Soft SARL garantit une disponibilité mensuelle de la plateforme selon les niveaux suivants :
    </p>
    <ul>
      <li><strong>Forfait Starter :</strong> disponibilité ≥ 99,0 % par mois calendaire.</li>
      <li><strong>Forfait Pro :</strong> disponibilité ≥ 99,5 % par mois calendaire.</li>
      <li><strong>Forfait Enterprise :</strong> disponibilité ≥ 99,9 % par mois calendaire.</li>
    </ul>
    <p>
      La disponibilité est calculée selon la formule :
      <em>(minutes du mois − minutes d'indisponibilité non planifiée) / minutes du mois × 100</em>.
      Les fenêtres de maintenance planifiée, notifiées 48 heures à l'avance, sont exclues du calcul.
    </p>

    <h2>3. Fenêtres de maintenance</h2>
    <p>
      Les opérations de maintenance programmée sont réalisées de préférence entre <strong>00 h 00 et 04 h 00 UTC</strong>
      les dimanches. IBIG Soft SARL s'efforce de limiter leur durée à 2 heures par intervention.
      Une notification est envoyée par email au moins 48 heures avant toute intervention planifiée.
    </p>

    <h2>4. Délais de rétablissement (RTO)</h2>
    <ul>
      <li><strong>Incident critique</strong> (service totalement indisponible) : rétablissement cible en moins de 4 heures.</li>
      <li><strong>Incident majeur</strong> (dégradation significative) : rétablissement cible en moins de 8 heures.</li>
      <li><strong>Incident mineur</strong> (gêne fonctionnelle partielle) : rétablissement cible en moins de 24 heures.</li>
    </ul>

    <h2>5. Objectif de point de reprise (RPO)</h2>
    <p>
      Les sauvegardes des données clients sont effectuées toutes les <strong>24 heures</strong> au minimum,
      avec une rétention de 30 jours glissants. Le RPO (Recovery Point Objective) cible est de 24 heures.
      Les abonnés Enterprise bénéficient d'un RPO réduit à 4 heures sur demande.
    </p>

    <h2>6. Crédits de service</h2>
    <p>
      En cas de non-respect des engagements de disponibilité, le client peut demander un crédit de service
      dans les <strong>15 jours</strong> suivant la fin du mois concerné :
    </p>
    <ul>
      <li>Disponibilité entre 99,0 % et 99,5 % (hors Starter) : crédit de 5 % du montant mensuel.</li>
      <li>Disponibilité entre 98,0 % et 99,0 % : crédit de 10 % du montant mensuel.</li>
      <li>Disponibilité inférieure à 98,0 % : crédit de 25 % du montant mensuel.</li>
    </ul>
    <p>
      Les crédits sont imputés sur la prochaine facture. Ils ne sont pas remboursables en espèces
      et ne constituent pas une reconnaissance de responsabilité.
    </p>

    <h2>7. Exclusions</h2>
    <p>Les engagements de disponibilité ne s'appliquent pas en cas :</p>
    <ul>
      <li>De force majeure (coupures d'électricité nationales, catastrophes naturelles, actes de guerre, pandémies).</li>
      <li>D'attaques DDoS d'ampleur exceptionnelle dépassant les capacités de mitigation.</li>
      <li>De défaillance de l'opérateur Internet ou télécom du client.</li>
      <li>D'usage non conforme aux présentes CGU ou à la Politique d'utilisation acceptable.</li>
      <li>De demandes de maintenance initiées par le client.</li>
    </ul>

    <h2>8. Surveillance et rapports</h2>
    <p>
      Un tableau de bord de statut public est accessible à l'adresse <strong>status.ibigsoft.com</strong>.
      Les abonnés Pro et Enterprise reçoivent un rapport mensuel de disponibilité par email.
    </p>

    <h2>9. Contact SLA</h2>
    <p>
      Pour toute réclamation relative au présent SLA : <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a>
      avec l'objet <em>« Réclamation SLA — [votre identifiant client] »</em>.
    </p>

  </LegalLayout>
</template>

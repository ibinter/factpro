<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Plan de continuité d'activité" last-updated="23 juillet 2026" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Objet et portée</h2>
    <p>
      Le présent Plan de Continuité d'Activité (<strong>PCA</strong>) décrit les dispositions
      qu'IBIG Soft SARL a mises en place pour assurer la continuité des services
      d'<strong>IBIG FactPro</strong> en cas d'incident majeur, de sinistre ou de crise
      susceptibles d'interrompre ou de dégrader significativement les opérations normales.
      Ce document est communiqué aux clients à titre informatif pour renforcer leur confiance
      dans la résilience de la plateforme.
    </p>

    <h2>2. Objectifs de continuité</h2>
    <ul>
      <li><strong>RTO (Recovery Time Objective) :</strong> reprise des services critiques en moins de 4 heures après un sinistre majeur.</li>
      <li><strong>RPO (Recovery Point Objective) :</strong> perte de données maximale acceptable de 24 heures (4 heures pour les forfaits Enterprise).</li>
      <li><strong>Disponibilité cible :</strong> maintien d'une disponibilité ≥ 99,5 % sur douze mois glissants, hors événements de force majeure.</li>
    </ul>

    <h2>3. Architecture de résilience</h2>
    <ul>
      <li><strong>Redondance géographique :</strong> l'infrastructure est déployée sur deux zones de disponibilité distinctes avec basculement automatique.</li>
      <li><strong>Équilibrage de charge :</strong> un load balancer distribue le trafic entre plusieurs instances d'application, éliminant les points uniques de défaillance.</li>
      <li><strong>Base de données répliquée :</strong> réplication synchrone sur une instance secondaire ; le basculement en cas de défaillance primaire est automatique.</li>
      <li><strong>CDN :</strong> les ressources statiques sont distribuées via un réseau de diffusion de contenu pour réduire la latence en Afrique de l'Ouest et assurer une disponibilité même en cas de perturbation d'un nœud.</li>
    </ul>

    <h2>4. Sauvegardes</h2>
    <ul>
      <li>Sauvegarde complète de la base de données : quotidienne, conservée 30 jours.</li>
      <li>Sauvegarde incrémentale : toutes les 6 heures.</li>
      <li>Sauvegardes stockées chiffrées (AES-256) dans une zone géographique distincte du site principal.</li>
      <li>Test de restauration effectué mensuellement pour valider l'intégrité des sauvegardes.</li>
    </ul>

    <h2>5. Scénarios couverts</h2>
    <ul>
      <li><strong>Panne serveur :</strong> basculement automatique vers l'instance secondaire en moins de 5 minutes.</li>
      <li><strong>Panne datacenter :</strong> redéploiement sur la zone secondaire, RTO cible de 2 heures.</li>
      <li><strong>Attaque DDoS :</strong> mitigation automatique par le WAF et le fournisseur d'hébergement ; escalade vers des ressources de protection renforcée si nécessaire.</li>
      <li><strong>Corruption de données :</strong> restauration depuis la dernière sauvegarde valide, notification immédiate des clients affectés.</li>
      <li><strong>Incident de sécurité majeur :</strong> isolation du périmètre compromis, investigation, assainissement et reprise progressive selon protocole interne.</li>
      <li><strong>Pandémie / force majeure :</strong> activation du travail à distance pour l'équipe technique ; tous les outils critiques sont accessibles en remote.</li>
    </ul>

    <h2>6. Organisation de crise</h2>
    <p>
      Une cellule de crise est activée dès la détection d'un incident de niveau 1 (critique).
      Elle comprend le responsable technique, le responsable sécurité et le dirigeant.
      Les clients sont informés via le tableau de bord de statut (<strong>status.ibigsoft.com</strong>)
      et par email dans les 30 minutes suivant la confirmation de l'incident.
    </p>

    <h2>7. Tests et révision du PCA</h2>
    <p>
      Le PCA est testé via des exercices de simulation (<em>Game Day</em>) au minimum une fois par an.
      Il est révisé après chaque incident significatif et a minima tous les 12 mois.
      Les résultats des tests ne sont pas communiqués publiquement mais peuvent être partagés
      avec les clients Enterprise sur accord de confidentialité.
    </p>

    <h2>8. Contact</h2>
    <p>
      Pour toute question relative à la continuité d'activité :
      <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a>.
    </p>

  </LegalLayout>
</template>

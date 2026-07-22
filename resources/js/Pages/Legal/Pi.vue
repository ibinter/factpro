<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Propriété intellectuelle" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Titulaire des droits</h2>
    <p>
      L'ensemble des droits de propriété intellectuelle relatifs au logiciel IBIG FactPro
      appartient exclusivement à <strong>IBIG SARL</strong> – Intermark Business International Group,
      protégés par les lois ivoiriennes, le traité OHADA et les conventions internationales applicables.
    </p>

    <h2>2. Éléments protégés</h2>
    <ul>
      <li>Le nom commercial « IBIG FactPro » et les marques déposées associées.</li>
      <li>Les logos, visuels, icônes, couleurs et identité graphique complète.</li>
      <li>Le code source, les algorithmes et l'architecture logicielle.</li>
      <li>Les interfaces utilisateur, maquettes et composants graphiques.</li>
      <li>La base de données et sa structure relationnelle.</li>
      <li>Les modèles de documents, templates PDF et rapports.</li>
      <li>La documentation, guides utilisateur, FAQ et contenus éditoriaux.</li>
      <li>L'assistant IA SARA et ses configurations spécifiques.</li>
      <li>Les marques IBIG Soft, IBIG SARL et IBIG PARTNERS.</li>
    </ul>

    <h2>3. Interdictions</h2>
    <p>Sont expressément interdits, sans autorisation écrite préalable de IBIG SARL :</p>
    <ul>
      <li>La reproduction, copie, imitation ou adaptation du logiciel ou de ses interfaces.</li>
      <li>L'extraction ou la réutilisation de tout ou partie de la base de données.</li>
      <li>L'utilisation des marques ou logos à des fins commerciales propres.</li>
      <li>La création de logiciels concurrents basés sur les éléments protégés.</li>
      <li>Le décompilage, désassemblage ou toute forme d'ingénierie inverse du code.</li>
    </ul>

    <h2>4. Licence d'utilisation accordée</h2>
    <p>
      L'abonnement à IBIG FactPro vous confère une <strong>licence personnelle, non exclusive,
      non transférable et révocable</strong> d'utilisation du logiciel, strictement limitée
      à votre usage professionnel interne, pour la durée de votre abonnement actif.
    </p>

    <h2>5. Propriété du contenu utilisateur</h2>
    <p>
      Vous conservez la <strong>pleine propriété</strong> de toutes les données que vous saisissez
      dans IBIG FactPro (clients, factures, produits, etc.).
      Vous accordez à IBIG SARL une licence limitée pour traiter ces données dans le seul but
      de vous fournir le service souscrit.
    </p>

    <h2>6. Signalement de violation</h2>
    <p>
      Si vous constatez une copie, imitation, utilisation abusive ou toute autre violation
      des droits de propriété intellectuelle de IBIG Soft, merci de nous le signaler :
    </p>
    <p>
      <strong>Email :</strong> <a href="mailto:factpro@ibigsoft.com">factpro@ibigsoft.com</a><br>
      <strong>Objet :</strong> « Signalement violation PI »
    </p>

    <p>
      <em>Note : les informations contenues dans cette page ont une valeur informative.
      Pour des conseils juridiques définitifs, consultez un professionnel compétent.</em>
    </p>

  </LegalLayout>
</template>

<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>
<template>
  <LegalLayout title="Propriété intellectuelle" :can-login="canLogin" :can-register="canRegister">
    <h2>1. Droits de propriété intellectuelle</h2>
    <p>L'ensemble des éléments suivants est la propriété exclusive de IBIG SARL – Intermark Business International Group et est protégé par les lois applicables en matière de propriété intellectuelle :</p>
    <ul>
      <li>Le nom commercial « IBIG FactPro » et les marques associées.</li>
      <li>Les logos, visuels, icônes, couleurs et identité graphique.</li>
      <li>Le code source, les algorithmes et l'architecture logicielle.</li>
      <li>Les interfaces utilisateur, maquettes et composants graphiques.</li>
      <li>La base de données et sa structure.</li>
      <li>Les modèles de documents, templates et rapports.</li>
      <li>La documentation, guides, FAQ et contenus éditoriaux.</li>
      <li>L'assistante IA SARA et ses configurations.</li>
      <li>Les marques IBIG Soft, IBIG SARL et IBIG PARTNERS.</li>
    </ul>

    <h2>2. Interdictions</h2>
    <p>Sont expressément interdits, sans autorisation écrite préalable de IBIG SARL :</p>
    <ul>
      <li>La reproduction, copie, imitation ou adaptation du logiciel ou de ses interfaces.</li>
      <li>L'extraction ou la réutilisation de la base de données.</li>
      <li>L'utilisation des marques ou logos à des fins commerciales.</li>
      <li>La création de logiciels concurrents basés sur les éléments protégés.</li>
      <li>Le décompilage, désassemblage ou ingénierie inverse du code source.</li>
    </ul>

    <h2>3. Signalement de violation</h2>
    <p>Si vous constatez une copie, imitation, utilisation abusive ou violation des droits de propriété intellectuelle de IBIG Soft, merci de nous le signaler immédiatement :</p>
    <p>📧 <a href="mailto:factpro@ibigsoft.com">factpro@ibigsoft.com</a><br>
    Objet : « Signalement violation PI »</p>

    <h2>4. Licence d'utilisation</h2>
    <p>L'abonnement à IBIG FactPro vous confère une licence d'utilisation personnelle, non exclusive, non transférable et révocable du logiciel, strictement limitée à votre usage professionnel interne, pour la durée de votre abonnement.</p>

    <h2>5. Contenu utilisateur</h2>
    <p>Vous conservez la propriété de toutes les données que vous saisissez dans IBIG FactPro (clients, factures, produits, etc.). Vous accordez à IBIG SARL une licence limitée pour traiter ces données dans le seul but de vous fournir le service.</p>

    <p class="mt-8 text-gray-400 text-xs"><em>Note : les informations contenues dans cette page ont une valeur indicative. Pour des conseils juridiques définitifs, consultez un professionnel compétent.</em></p>
  </LegalLayout>
</template>

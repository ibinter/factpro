<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const activeModule = ref('module-1');
const mobileMenuOpen = ref(false);

const modules = [
  { id: 'module-1',  icon: '🚀', title: 'Démarrage' },
  { id: 'module-2',  icon: '📄', title: 'Facturation' },
  { id: 'module-3',  icon: '👥', title: 'Clients' },
  { id: 'module-4',  icon: '📦', title: 'Produits & Stock' },
  { id: 'module-5',  icon: '💰', title: 'Paiements' },
  { id: 'module-6',  icon: '🖥️', title: 'Caisse POS' },
  { id: 'module-7',  icon: '📊', title: 'Rapports' },
  { id: 'module-8',  icon: '👤', title: 'Utilisateurs & Rôles' },
  { id: 'module-9',  icon: '⚙️', title: 'Paramètres' },
  { id: 'module-10', icon: '💳', title: 'Abonnement & Licence' },
  { id: 'module-11', icon: '📋', title: 'Catalogue de modèles' },
  { id: 'module-12', icon: '🎨', title: 'Styles visuels PDF' },
  { id: 'module-13', icon: '📑', title: 'Types de documents' },
];

function scrollTo(id) {
  const el = document.getElementById(id);
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
  activeModule.value = id;
  mobileMenuOpen.value = false;
}

// Intersection observer to highlight active section
let observer = null;
onMounted(() => {
  observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          activeModule.value = entry.target.id;
        }
      });
    },
    { rootMargin: '-20% 0px -70% 0px' }
  );
  modules.forEach(m => {
    const el = document.getElementById(m.id);
    if (el) observer.observe(el);
  });
});
onUnmounted(() => { if (observer) observer.disconnect(); });
</script>

<template>
  <Head title="Guide utilisateur — IBIG FactPro" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between gap-3 w-full">
        <div class="flex items-center gap-3">
          <span class="text-2xl">📖</span>
          <div>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 leading-tight">
              Guide utilisateur IBIG FactPro
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              13 modules · Procédures étape par étape
            </p>
          </div>
        </div>
        <a
          href="/help/guide/pdf"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:shadow-md hover:-translate-y-0.5"
          style="background: linear-gradient(135deg, #0062CC, #1a56db);"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Télécharger le guide PDF
        </a>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Mobile: module dropdown -->
        <div class="lg:hidden mb-4">
          <button
            @click="mobileMenuOpen = !mobileMenuOpen"
            class="w-full flex items-center justify-between px-4 py-3 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm text-sm font-semibold text-gray-700 dark:text-gray-200"
          >
            <span>
              {{ modules.find(m => m.id === activeModule)?.icon }}
              {{ modules.find(m => m.id === activeModule)?.title }}
            </span>
            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': mobileMenuOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div v-if="mobileMenuOpen" class="mt-1 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg overflow-hidden">
            <button
              v-for="m in modules" :key="m.id"
              @click="scrollTo(m.id)"
              class="w-full flex items-center gap-2 px-4 py-3 text-left text-sm transition-colors hover:bg-blue-50 dark:hover:bg-blue-950"
              :class="activeModule === m.id ? 'text-blue-600 font-semibold bg-blue-50 dark:bg-blue-950' : 'text-gray-700 dark:text-gray-300'"
            >
              <span>{{ m.icon }}</span>
              <span>{{ m.title }}</span>
            </button>
          </div>
        </div>

        <div class="flex gap-8">
          <!-- Sidebar (desktop) -->
          <aside class="hidden lg:block w-56 flex-shrink-0">
            <div class="sticky top-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
              <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Modules</p>
              </div>
              <nav class="py-2">
                <button
                  v-for="m in modules" :key="m.id"
                  @click="scrollTo(m.id)"
                  class="w-full flex items-center gap-2.5 px-4 py-2.5 text-left text-sm transition-colors"
                  :class="activeModule === m.id
                    ? 'text-blue-600 dark:text-blue-400 font-semibold bg-blue-50 dark:bg-blue-950 border-r-2 border-blue-600'
                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-750'"
                >
                  <span class="text-base">{{ m.icon }}</span>
                  <span class="leading-tight">{{ m.title }}</span>
                </button>
              </nav>
              <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700">
                <Link href="/help" class="text-xs text-gray-400 dark:text-gray-500 hover:text-blue-600 transition-colors">
                  ← Retour à l'aide
                </Link>
              </div>
            </div>
          </aside>

          <!-- Main content -->
          <main class="flex-1 min-w-0 space-y-12">

            <!-- ═══════════════════════════════════════════════════════
                 MODULE 1 — DÉMARRAGE
            ════════════════════════════════════════════════════════════ -->
            <section id="module-1" class="scroll-mt-6">
              <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700" style="border-left: 4px solid #0062CC">
                  <div class="flex items-center gap-3">
                    <span class="text-3xl">🚀</span>
                    <div>
                      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Module 1 — Démarrage</h2>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Première configuration de votre compte</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-6 space-y-8">

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-bold flex items-center justify-center">1</span>
                      Créer son compte et configurer la société
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300 list-none">
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">1.</span> Rendez-vous sur <strong>factpro.ibigsoft.com</strong> et cliquez sur <em>Essai gratuit</em>.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">2.</span> Renseignez votre adresse email et choisissez un mot de passe sécurisé (8 car. min, 1 majuscule, 1 chiffre).</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">3.</span> Cliquez sur <em>Créer mon compte</em> — aucune carte bancaire requise.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">4.</span> Allez dans <strong>Paramètres &gt; Société</strong>.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">5.</span> Renseignez : nom commercial, adresse complète, numéro RCCM, numéro d'impôt (NIF/NCC).</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">6.</span> Uploadez votre logo (PNG ou SVG recommandé, fond transparent).</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">7.</span> Enregistrez — le logo et les informations apparaissent désormais sur tous vos documents.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-bold flex items-center justify-center">2</span>
                      Inviter les premiers collaborateurs et définir leurs rôles
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Équipe</strong> et cliquez <em>Inviter un membre</em>.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">2.</span> Saisissez l'adresse email du collaborateur.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">3.</span> Sélectionnez son rôle : <em>Admin, Comptable, Commercial, Caissier</em> ou <em>Lecture seule</em>.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">4.</span> Cliquez <em>Envoyer l'invitation</em> — un email est envoyé avec un lien d'activation.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">5.</span> Le collaborateur accepte l'invitation et crée son mot de passe.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-bold flex items-center justify-center">3</span>
                      Paramétrer les modes de paiement acceptés
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Paiements</strong>.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">2.</span> Activez chaque mode : Orange Money, Wave, MTN MoMo, Moov Money, Espèces, Virement, Carte.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">3.</span> Pour chaque Mobile Money, renseignez le numéro du compte et le nom du titulaire.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">4.</span> Ces modes apparaissent sur les liens de paiement envoyés aux clients.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-bold flex items-center justify-center">4</span>
                      Personnaliser les modèles de documents
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Templates</strong>.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">2.</span> Choisissez parmi les modèles disponibles.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">3.</span> Définissez la couleur primaire (en-tête) et la couleur secondaire (accents).</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">4.</span> Activez ou désactivez la signature, le QR code et les mentions légales.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">5.</span> Prévisualisez le rendu PDF avant de sauvegarder.</li>
                    </ol>
                  </div>

                </div>
                <!-- Module nav -->
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                  <button @click="scrollTo('module-2')" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Module suivant : Facturation <span>→</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 MODULE 2 — FACTURATION
            ════════════════════════════════════════════════════════════ -->
            <section id="module-2" class="scroll-mt-6">
              <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700" style="border-left: 4px solid #7c3aed">
                  <div class="flex items-center gap-3">
                    <span class="text-3xl">📄</span>
                    <div>
                      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Module 2 — Facturation</h2>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Devis, factures, avoirs et récurrences</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-6 space-y-8">

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">1</span>
                      Créer un devis
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">1.</span> Allez dans <strong>Documents &gt; Nouveau devis</strong>.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">2.</span> Dans le champ <em>Client</em>, tapez le nom pour le sélectionner ou créez-le à la volée.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">3.</span> Cliquez <em>Ajouter une ligne</em>, cherchez le produit/service, ajustez la quantité et le prix unitaire.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">4.</span> Appliquez une remise globale (%) ou par ligne si nécessaire.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">5.</span> Vérifiez le total HT, la TVA et le total TTC.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">6.</span> Cliquez <em>Enregistrer</em> (brouillon) ou <em>Finaliser</em> pour le verrouiller.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">2</span>
                      Convertir un devis en facture en 1 clic
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">1.</span> Ouvrez le devis accepté par le client.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">2.</span> Cliquez le bouton <em>Convertir en facture</em>.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">3.</span> Vérifiez les informations reprises automatiquement (lignes, remises, conditions).</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">4.</span> Cliquez <em>Finaliser la facture</em> — un numéro est attribué automatiquement.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">3</span>
                      Créer une facture directe (sans devis)
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">1.</span> Allez dans <strong>Documents &gt; Nouvelle facture</strong>.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">2.</span> Sélectionnez le client, ajoutez les lignes et définissez la date d'échéance.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">3.</span> Cliquez <em>Finaliser</em>.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">4</span>
                      Envoyer par email avec PDF en pièce jointe
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">1.</span> Sur la page de la facture, cliquez <em>Envoyer par email</em>.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">2.</span> L'adresse du client et l'objet sont pré-remplis. Le PDF est joint automatiquement.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">3.</span> Personnalisez le message si besoin, puis cliquez <em>Envoyer</em>.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">4.</span> Le suivi de lecture est activé — vous serez notifié quand le client ouvre l'email.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">5</span>
                      Créer un avoir (annulation partielle ou totale)
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">1.</span> Ouvrez la facture à annuler.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">2.</span> Cliquez <em>Créer un avoir</em> dans le menu Actions.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">3.</span> Pour un avoir total, conservez toutes les lignes. Pour un avoir partiel, ajustez les quantités ou supprimez des lignes.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">4.</span> Finalisez l'avoir — il est lié à la facture d'origine et réduit le solde dû.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">6</span>
                      Configurer la numérotation automatique
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Numérotation</strong>.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">2.</span> Définissez le préfixe (ex : <code>FACT</code>), l'inclusion de l'année (<code>2026</code>) et la longueur de la séquence (ex : <code>0001</code>).</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">3.</span> Enregistrez — la prochaine facture adoptera ce format automatiquement.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">7</span>
                      Factures récurrentes
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">1.</span> Allez dans <strong>Documents &gt; Récurrences &gt; Nouvelle récurrence</strong>.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">2.</span> Créez le modèle de facture (client, lignes, montants).</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">3.</span> Définissez la fréquence : hebdomadaire, mensuelle, trimestrielle ou annuelle.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">4.</span> Indiquez la date de début et le nombre d'occurrences (ou <em>Sans limite</em>).</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">5.</span> Activez la récurrence — les factures seront générées et envoyées automatiquement.</li>
                    </ol>
                  </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                  <button @click="scrollTo('module-1')" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <span>←</span> Module précédent
                  </button>
                  <button @click="scrollTo('module-3')" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Module suivant : Clients <span>→</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 MODULE 3 — CLIENTS
            ════════════════════════════════════════════════════════════ -->
            <section id="module-3" class="scroll-mt-6">
              <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700" style="border-left: 4px solid #dc2626">
                  <div class="flex items-center gap-3">
                    <span class="text-3xl">👥</span>
                    <div>
                      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Module 3 — Clients</h2>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Gestion des clients, imports et relances</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-6 space-y-8">

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-xs font-bold flex items-center justify-center">1</span>
                      Ajouter un client
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-red-500 font-bold">1.</span> Allez dans <strong>Clients &gt; Nouveau client</strong>.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">2.</span> Renseignez : nom, email, téléphone, adresse, pays et type (Particulier / Entreprise).</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">3.</span> Pour les entreprises, renseignez les champs OHADA : <strong>RCCM</strong> et <strong>NIF</strong> (obligatoires sur les factures légales).</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">4.</span> Enregistrez — le client est immédiatement disponible pour vos documents.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-xs font-bold flex items-center justify-center">2</span>
                      Importer depuis un fichier CSV/Excel
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-red-500 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Import &gt; Clients</strong>.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">2.</span> Téléchargez le modèle CSV fourni par FactPro.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">3.</span> Remplissez les colonnes obligatoires : nom, email, pays. Les autres colonnes sont facultatives.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">4.</span> Importez le fichier — les doublons sont détectés par email.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">5.</span> Un rapport d'import indique les lignes créées, mises à jour et les erreurs.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-xs font-bold flex items-center justify-center">3</span>
                      Consulter l'historique d'un client
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-red-500 font-bold">1.</span> Cliquez sur le nom du client dans la liste.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">2.</span> Onglet <em>Documents</em> : tous les devis, factures et avoirs liés.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">3.</span> Onglet <em>Paiements</em> : historique de tous les encaissements.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">4.</span> Onglet <em>Statistiques</em> : CA total, panier moyen, taux de paiement.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">5.</span> Le <strong>solde dû</strong> est affiché en rouge en haut de la fiche si des factures sont impayées.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-xs font-bold flex items-center justify-center">4</span>
                      Configurer les relances automatiques
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-red-500 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Relances</strong>.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">2.</span> Créez une séquence : ex. J+30, J+60, J+90 après l'échéance.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">3.</span> Rédigez ou personnalisez le message de relance pour chaque étape.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">4.</span> Activez le canal : email, WhatsApp, ou les deux.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">5.</span> FactPro envoie les relances automatiquement aux clients avec des factures échues.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 text-xs font-bold flex items-center justify-center">5</span>
                      Pipeline commercial : créer et suivre une opportunité
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-red-500 font-bold">1.</span> Allez dans <strong>Commercial &gt; Pipeline</strong>.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">2.</span> Cliquez <em>Nouvelle opportunité</em> et liez-la à un client existant ou nouveau.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">3.</span> Définissez : montant estimé, date de closing, probabilité de succès.</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">4.</span> Glissez la carte entre les colonnes (Prospection → Qualification → Proposition → Gagné/Perdu).</li>
                      <li class="flex gap-2"><span class="text-red-500 font-bold">5.</span> Depuis une opportunité Gagnée, convertissez directement en devis ou facture.</li>
                    </ol>
                  </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                  <button @click="scrollTo('module-2')" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <span>←</span> Module précédent
                  </button>
                  <button @click="scrollTo('module-4')" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Module suivant : Produits &amp; Stock <span>→</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 MODULE 4 — PRODUITS & STOCK
            ════════════════════════════════════════════════════════════ -->
            <section id="module-4" class="scroll-mt-6">
              <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700" style="border-left: 4px solid #0891b2">
                  <div class="flex items-center gap-3">
                    <span class="text-3xl">📦</span>
                    <div>
                      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Module 4 — Produits &amp; Stock</h2>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Catalogue, alertes, codes-barres et inventaire</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-6 space-y-8">

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-cyan-100 dark:bg-cyan-900 text-cyan-700 dark:text-cyan-300 text-xs font-bold flex items-center justify-center">1</span>
                      Créer un produit ou service
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">1.</span> Allez dans <strong>Produits &gt; Nouveau produit</strong>.</li>
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">2.</span> Renseignez : code (SKU), désignation, catégorie, unité de mesure.</li>
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">3.</span> Entrez le <strong>prix HT</strong> et sélectionnez le taux de TVA applicable.</li>
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">4.</span> Pour un produit physique, activez <em>Suivi de stock</em> et entrez la quantité initiale.</li>
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">5.</span> Enregistrez.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-cyan-100 dark:bg-cyan-900 text-cyan-700 dark:text-cyan-300 text-xs font-bold flex items-center justify-center">2</span>
                      Configurer les alertes de stock faible
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">1.</span> Dans la fiche produit, renseignez le champ <strong>Seuil d'alerte</strong> (ex : 5 unités).</li>
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">2.</span> Quand le stock descend sous ce seuil, une alerte apparaît sur le tableau de bord.</li>
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">3.</span> Vous recevez également un email de notification automatique.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-cyan-100 dark:bg-cyan-900 text-cyan-700 dark:text-cyan-300 text-xs font-bold flex items-center justify-center">3</span>
                      Importer un catalogue produits depuis CSV
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">1.</span> Allez dans <strong>Produits &gt; Importer</strong>.</li>
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">2.</span> Téléchargez le modèle CSV (colonnes : SKU, nom, catégorie, prix HT, TVA, stock).</li>
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">3.</span> Remplissez et uploadez. Les produits existants (même SKU) sont mis à jour.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-cyan-100 dark:bg-cyan-900 text-cyan-700 dark:text-cyan-300 text-xs font-bold flex items-center justify-center">4</span>
                      Générer et imprimer des étiquettes codes-barres
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">1.</span> Dans la liste produits, cochez les articles concernés.</li>
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">2.</span> Cliquez <em>Actions &gt; Générer étiquettes</em>.</li>
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">3.</span> Choisissez le format d'étiquette (A4, 3 colonnes, etc.) et cliquez <em>Imprimer</em>.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-cyan-100 dark:bg-cyan-900 text-cyan-700 dark:text-cyan-300 text-xs font-bold flex items-center justify-center">5</span>
                      Réaliser un inventaire
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">1.</span> Allez dans <strong>Stocks &gt; Inventaire &gt; Nouvel inventaire</strong>.</li>
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">2.</span> Scannez ou saisissez les quantités réelles pour chaque article.</li>
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">3.</span> FactPro calcule les écarts (théorique vs réel).</li>
                      <li class="flex gap-2"><span class="text-cyan-600 font-bold">4.</span> Validez l'inventaire pour appliquer les ajustements de stock.</li>
                    </ol>
                  </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                  <button @click="scrollTo('module-3')" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <span>←</span> Module précédent
                  </button>
                  <button @click="scrollTo('module-5')" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Module suivant : Paiements <span>→</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 MODULE 5 — PAIEMENTS
            ════════════════════════════════════════════════════════════ -->
            <section id="module-5" class="scroll-mt-6">
              <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700" style="border-left: 4px solid #d97706">
                  <div class="flex items-center gap-3">
                    <span class="text-3xl">💰</span>
                    <div>
                      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Module 5 — Paiements</h2>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Encaissements, Mobile Money et rapprochement</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-6 space-y-8">

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 text-xs font-bold flex items-center justify-center">1</span>
                      Enregistrer un paiement
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">1.</span> Ouvrez la facture concernée.</li>
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">2.</span> Cliquez <em>Enregistrer un paiement</em>.</li>
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">3.</span> Indiquez le montant, le mode (espèces, virement, Mobile Money), la date et une référence optionnelle.</li>
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">4.</span> Validez — la facture passe au statut <em>Payée</em> (ou <em>Partiellement payée</em> si montant partiel).</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 text-xs font-bold flex items-center justify-center">2</span>
                      Paiement partiel et suivi des soldes
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">1.</span> Enregistrez un premier paiement partiel (ex : 50% de la facture).</li>
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">2.</span> Le solde restant est visible sur la facture et dans la fiche client.</li>
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">3.</span> Enregistrez autant de paiements partiels que nécessaire jusqu'à solde nul.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 text-xs font-bold flex items-center justify-center">3</span>
                      Configurer Orange Money / Wave / MTN MoMo / Moov Money
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Paiements</strong>.</li>
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">2.</span> Activez chaque opérateur souhaité.</li>
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">3.</span> Renseignez le numéro de compte et le nom du titulaire pour chaque opérateur.</li>
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">4.</span> Ces informations sont affichées sur les liens de paiement envoyés aux clients.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 text-xs font-bold flex items-center justify-center">4</span>
                      Générer un reçu de paiement
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">1.</span> Après validation d'un paiement, cliquez <em>Voir le reçu</em>.</li>
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">2.</span> Le reçu PDF est généré avec les détails du paiement.</li>
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">3.</span> Cliquez <em>Envoyer au client</em> pour l'envoyer par email ou WhatsApp.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300 text-xs font-bold flex items-center justify-center">5</span>
                      Rapprochement bancaire mensuel
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">1.</span> Allez dans <strong>Rapports &gt; Rapprochement bancaire</strong>.</li>
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">2.</span> Importez votre relevé bancaire (CSV depuis votre banque).</li>
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">3.</span> FactPro rapproche automatiquement les transactions avec les paiements enregistrés.</li>
                      <li class="flex gap-2"><span class="text-amber-600 font-bold">4.</span> Traitez les écarts manuellement et validez le rapprochement.</li>
                    </ol>
                  </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                  <button @click="scrollTo('module-4')" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <span>←</span> Module précédent
                  </button>
                  <button @click="scrollTo('module-6')" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Module suivant : Caisse POS <span>→</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 MODULE 6 — CAISSE POS
            ════════════════════════════════════════════════════════════ -->
            <section id="module-6" class="scroll-mt-6">
              <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700" style="border-left: 4px solid #059669">
                  <div class="flex items-center gap-3">
                    <span class="text-3xl">🖥️</span>
                    <div>
                      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Module 6 — Caisse POS</h2>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Point de vente, sessions et rapports caisse</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-6 space-y-8">

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs font-bold flex items-center justify-center">1</span>
                      Ouvrir une session de caisse
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-green-600 font-bold">1.</span> Allez dans <strong>Caisse POS &gt; Ouvrir la caisse</strong>.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">2.</span> Saisissez le <strong>fond de caisse initial</strong> (montant en espèces disponible au départ).</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">3.</span> Cliquez <em>Ouvrir la session</em> — l'interface de vente s'affiche.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs font-bold flex items-center justify-center">2</span>
                      Vendre en mode tactile
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-green-600 font-bold">1.</span> Scannez le code-barres du produit avec un lecteur USB ou l'appareil photo, ou tapez son nom dans la recherche.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">2.</span> Le produit est ajouté au panier. Ajustez la quantité si besoin.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">3.</span> Répétez pour chaque article de la vente.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs font-bold flex items-center justify-center">3</span>
                      Encaisser et imprimer le ticket
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-green-600 font-bold">1.</span> Cliquez <em>Payer</em>.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">2.</span> Sélectionnez le mode d'encaissement : espèces, carte ou mobile money.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">3.</span> Pour les espèces, entrez le montant remis par le client — la monnaie à rendre est calculée automatiquement.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">4.</span> Validez et imprimez le ticket (imprimante thermique ou email).</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs font-bold flex items-center justify-center">4</span>
                      Gérer les remises et promotions en caisse
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-green-600 font-bold">1.</span> Sur une ligne du panier, cliquez l'icône <em>Remise</em>.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">2.</span> Entrez le pourcentage ou le montant fixe de remise.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">3.</span> Pour une remise globale sur le panier, cliquez <em>Remise globale</em> en bas de l'écran.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs font-bold flex items-center justify-center">5</span>
                      Fermer la caisse (rapport X/Z)
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-green-600 font-bold">1.</span> Cliquez <em>Fermer la caisse</em> en fin de journée.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">2.</span> Saisissez le comptage physique des espèces dans le tiroir.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">3.</span> FactPro calcule l'écart entre le théorique et le réel.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">4.</span> Le <strong>rapport Z</strong> est généré automatiquement (total par mode, écarts, CA de la journée).</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">5.</span> Imprimez ou exportez le rapport pour votre comptabilité.</li>
                    </ol>
                  </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                  <button @click="scrollTo('module-5')" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <span>←</span> Module précédent
                  </button>
                  <button @click="scrollTo('module-7')" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Module suivant : Rapports <span>→</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 MODULE 7 — RAPPORTS
            ════════════════════════════════════════════════════════════ -->
            <section id="module-7" class="scroll-mt-6">
              <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700" style="border-left: 4px solid #7c3aed">
                  <div class="flex items-center gap-3">
                    <span class="text-3xl">📊</span>
                    <div>
                      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Module 7 — Rapports</h2>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Analytics, exports comptables et TVA</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-6 space-y-8">

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">1</span>
                      Rapport de CA mensuel/trimestriel/annuel
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">1.</span> Allez dans <strong>Rapports &gt; Chiffre d'affaires</strong>.</li>
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">2.</span> Sélectionnez la période : mois, trimestre, année ou plage personnalisée.</li>
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">3.</span> Consultez le CA par client, par produit ou par commercial.</li>
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">4.</span> Comparez avec la période précédente grâce au mode <em>Comparaison</em>.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">2</span>
                      Export FEC pour le comptable
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">1.</span> Allez dans <strong>Rapports &gt; Comptabilité &gt; Export FEC</strong>.</li>
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">2.</span> Sélectionnez l'exercice fiscal.</li>
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">3.</span> Cliquez <em>Générer</em> — le fichier FEC est compatible Sage et autres logiciels comptables.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">3</span>
                      Rapport de TVA collectée/déductible
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">1.</span> Allez dans <strong>Rapports &gt; Fiscal &gt; TVA</strong>.</li>
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">2.</span> Choisissez le régime (mensuel ou trimestriel) et la période.</li>
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">3.</span> Le rapport affiche TVA collectée (ventes), TVA déductible (achats) et le solde à reverser à l'État.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">4</span>
                      Rapport de performance par vendeur
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">1.</span> Allez dans <strong>Rapports &gt; Performance commerciale</strong>.</li>
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">2.</span> Filtrez par commercial pour voir son CA, nombre de factures et taux de conversion.</li>
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">3.</span> Utilisez ce rapport pour calculer les commissions.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">5</span>
                      Exporter en PDF ou Excel
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">1.</span> Sur n'importe quel rapport, cliquez le bouton <em>Exporter</em>.</li>
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">2.</span> Choisissez le format : PDF (mise en page) ou Excel (.xlsx pour manipulation).</li>
                      <li class="flex gap-2"><span class="text-purple-600 font-bold">3.</span> Le fichier est téléchargé immédiatement.</li>
                    </ol>
                  </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                  <button @click="scrollTo('module-6')" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <span>←</span> Module précédent
                  </button>
                  <button @click="scrollTo('module-8')" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Module suivant : Utilisateurs &amp; Rôles <span>→</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 MODULE 8 — UTILISATEURS & RÔLES
            ════════════════════════════════════════════════════════════ -->
            <section id="module-8" class="scroll-mt-6">
              <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700" style="border-left: 4px solid #374151">
                  <div class="flex items-center gap-3">
                    <span class="text-3xl">👤</span>
                    <div>
                      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Module 8 — Utilisateurs &amp; Rôles</h2>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Invitations, permissions et journal d'activité</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-6 space-y-8">

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold flex items-center justify-center">1</span>
                      Inviter un collaborateur par email
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-gray-500 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Équipe &gt; Inviter un membre</strong>.</li>
                      <li class="flex gap-2"><span class="text-gray-500 font-bold">2.</span> Saisissez l'adresse email et choisissez un rôle.</li>
                      <li class="flex gap-2"><span class="text-gray-500 font-bold">3.</span> Cliquez <em>Envoyer l'invitation</em> — l'email est envoyé avec un lien valable 48h.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold flex items-center justify-center">2</span>
                      Assigner un rôle
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">Les 5 rôles disponibles et leurs droits principaux :</p>
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                      <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                          <tr>
                            <th class="px-4 py-2 font-semibold">Rôle</th>
                            <th class="px-4 py-2 font-semibold">Factures</th>
                            <th class="px-4 py-2 font-semibold">Paiements</th>
                            <th class="px-4 py-2 font-semibold">Rapports</th>
                            <th class="px-4 py-2 font-semibold">Paramètres</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-600 dark:text-gray-300">
                          <tr><td class="px-4 py-2 font-semibold">Admin</td><td class="px-4 py-2">Complet</td><td class="px-4 py-2">Complet</td><td class="px-4 py-2">Complet</td><td class="px-4 py-2">Complet</td></tr>
                          <tr class="bg-gray-50 dark:bg-gray-750"><td class="px-4 py-2 font-semibold">Comptable</td><td class="px-4 py-2">Lecture + export</td><td class="px-4 py-2">Création</td><td class="px-4 py-2">Complet</td><td class="px-4 py-2">Non</td></tr>
                          <tr><td class="px-4 py-2 font-semibold">Commercial</td><td class="px-4 py-2">Création + envoi</td><td class="px-4 py-2">Lecture</td><td class="px-4 py-2">Limité</td><td class="px-4 py-2">Non</td></tr>
                          <tr class="bg-gray-50 dark:bg-gray-750"><td class="px-4 py-2 font-semibold">Caissier</td><td class="px-4 py-2">Caisse POS</td><td class="px-4 py-2">Caisse POS</td><td class="px-4 py-2">Caisse</td><td class="px-4 py-2">Non</td></tr>
                          <tr><td class="px-4 py-2 font-semibold">Lecture seule</td><td class="px-4 py-2">Lecture</td><td class="px-4 py-2">Lecture</td><td class="px-4 py-2">Lecture</td><td class="px-4 py-2">Non</td></tr>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold flex items-center justify-center">3</span>
                      Modifier ou révoquer l'accès d'un utilisateur
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-gray-500 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Équipe</strong>.</li>
                      <li class="flex gap-2"><span class="text-gray-500 font-bold">2.</span> Cliquez sur le membre concerné.</li>
                      <li class="flex gap-2"><span class="text-gray-500 font-bold">3.</span> Pour changer le rôle : sélectionnez le nouveau rôle et sauvegardez.</li>
                      <li class="flex gap-2"><span class="text-gray-500 font-bold">4.</span> Pour révoquer l'accès : cliquez <em>Supprimer de l'équipe</em> — la session est immédiatement terminée.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold flex items-center justify-center">4</span>
                      Journal d'activité
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-gray-500 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Journal d'activité</strong>.</li>
                      <li class="flex gap-2"><span class="text-gray-500 font-bold">2.</span> Chaque action (création, modification, suppression, connexion) est enregistrée avec : utilisateur, date, heure, IP et détail.</li>
                      <li class="flex gap-2"><span class="text-gray-500 font-bold">3.</span> Filtrez par utilisateur ou par type d'action pour auditer rapidement.</li>
                    </ol>
                  </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                  <button @click="scrollTo('module-7')" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <span>←</span> Module précédent
                  </button>
                  <button @click="scrollTo('module-9')" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Module suivant : Paramètres <span>→</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 MODULE 9 — PARAMÈTRES
            ════════════════════════════════════════════════════════════ -->
            <section id="module-9" class="scroll-mt-6">
              <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700" style="border-left: 4px solid #0f766e">
                  <div class="flex items-center gap-3">
                    <span class="text-3xl">⚙️</span>
                    <div>
                      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Module 9 — Paramètres</h2>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Société, TVA, devises, sauvegardes et sécurité</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-6 space-y-8">

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300 text-xs font-bold flex items-center justify-center">1</span>
                      Configurer les informations légales de la société
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Société</strong>.</li>
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">2.</span> Renseignez ou mettez à jour : raison sociale, forme juridique, capital, RCCM, NIF, adresse du siège.</li>
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">3.</span> Ces informations sont imprimées sur chaque document émis et sont obligatoires pour la conformité OHADA.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300 text-xs font-bold flex items-center justify-center">2</span>
                      Gérer la TVA
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Fiscalité &gt; TVA</strong>.</li>
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">2.</span> Définissez le taux standard (ex : 18%), le taux réduit et les produits exonérés.</li>
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">3.</span> Associez chaque taux aux catégories de produits concernées.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300 text-xs font-bold flex items-center justify-center">3</span>
                      Paramétrer les devises et le taux de change
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Devises</strong>.</li>
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">2.</span> Sélectionnez la devise principale (XOF, XAF, EUR, USD, etc.).</li>
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">3.</span> Activez les devises secondaires et définissez leur taux de change manuellement ou activez la mise à jour automatique.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300 text-xs font-bold flex items-center justify-center">4</span>
                      Sauvegardes automatiques et export manuel
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">1.</span> Les sauvegardes automatiques s'effectuent chaque 24h (conservées 30 jours).</li>
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">2.</span> Pour un export manuel : <strong>Paramètres &gt; RGPD &gt; Exporter mes données</strong>.</li>
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">3.</span> Une archive ZIP (CSV + JSON) vous est envoyée par email sous 24h.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-700 dark:text-teal-300 text-xs font-bold flex items-center justify-center">5</span>
                      Activer le 2FA (double authentification)
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">1.</span> Allez dans <strong>Profil &gt; Sécurité &gt; Authentification à deux facteurs</strong>.</li>
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">2.</span> Scannez le QR code avec Google Authenticator ou Authy.</li>
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">3.</span> Entrez le code à 6 chiffres pour confirmer l'activation.</li>
                      <li class="flex gap-2"><span class="text-teal-600 font-bold">4.</span> À chaque connexion, le code de l'application sera demandé en plus du mot de passe.</li>
                    </ol>
                  </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                  <button @click="scrollTo('module-8')" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <span>←</span> Module précédent
                  </button>
                  <button @click="scrollTo('module-10')" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Module suivant : Abonnement <span>→</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 MODULE 10 — ABONNEMENT & LICENCE
            ════════════════════════════════════════════════════════════ -->
            <section id="module-10" class="scroll-mt-6">
              <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700" style="border-left: 4px solid #be185d">
                  <div class="flex items-center gap-3">
                    <span class="text-3xl">💳</span>
                    <div>
                      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Module 10 — Abonnement &amp; Licence</h2>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Forfaits, paiements et clés d'activation</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-6 space-y-8">

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-pink-100 dark:bg-pink-900 text-pink-700 dark:text-pink-300 text-xs font-bold flex items-center justify-center">1</span>
                      Consulter son forfait et sa date d'expiration
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Abonnement</strong>.</li>
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">2.</span> Le forfait actif, la date d'expiration et le nombre d'utilisateurs inclus sont affichés.</li>
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">3.</span> Une barre de progression indique le temps restant avant le renouvellement.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-pink-100 dark:bg-pink-900 text-pink-700 dark:text-pink-300 text-xs font-bold flex items-center justify-center">2</span>
                      Passer à une formule supérieure (upgrade)
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Abonnement &gt; Changer de plan</strong>.</li>
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">2.</span> Comparez les plans disponibles (Starter, Business, Enterprise).</li>
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">3.</span> Sélectionnez le plan cible et cliquez <em>Passer à ce plan</em>.</li>
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">4.</span> Le changement est immédiat. La différence est calculée au prorata.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-pink-100 dark:bg-pink-900 text-pink-700 dark:text-pink-300 text-xs font-bold flex items-center justify-center">3</span>
                      Payer avec Mobile Money ou virement
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">1.</span> Sur la page de renouvellement, sélectionnez le mode de paiement.</li>
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">2.</span> Pour Mobile Money : suivez les instructions affichées, effectuez le transfert et uploadez la capture de confirmation.</li>
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">3.</span> Pour virement bancaire : les coordonnées bancaires sont affichées. Mentionnez votre numéro de compte dans le libellé.</li>
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">4.</span> Votre abonnement est activé dès validation du paiement (max 24h ouvrées).</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-pink-100 dark:bg-pink-900 text-pink-700 dark:text-pink-300 text-xs font-bold flex items-center justify-center">4</span>
                      Utiliser une clé d'activation reçue d'un revendeur
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Abonnement &gt; Clé d'activation</strong>.</li>
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">2.</span> Collez la clé reçue de votre revendeur (format : XXXX-XXXX-XXXX-XXXX).</li>
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">3.</span> Cliquez <em>Activer</em> — le plan et la durée correspondants sont appliqués immédiatement.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-pink-100 dark:bg-pink-900 text-pink-700 dark:text-pink-300 text-xs font-bold flex items-center justify-center">5</span>
                      Télécharger sa facture d'abonnement
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">1.</span> Allez dans <strong>Paramètres &gt; Abonnement &gt; Historique des paiements</strong>.</li>
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">2.</span> Chaque paiement affiche un bouton <em>Télécharger la facture</em>.</li>
                      <li class="flex gap-2"><span class="text-pink-600 font-bold">3.</span> Le PDF est téléchargé immédiatement, prêt pour votre comptabilité.</li>
                    </ol>
                  </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                  <button @click="scrollTo('module-9')" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <span>←</span> Module précédent
                  </button>
                  <button @click="scrollTo('module-11')" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Module suivant : Catalogue de modèles <span>→</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 MODULE 11 — CATALOGUE DE MODÈLES DE DOCUMENTS
            ════════════════════════════════════════════════════════════ -->
            <section id="module-11" class="scroll-mt-6">
              <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700" style="border-left: 4px solid #0062CC">
                  <div class="flex items-center gap-3">
                    <span class="text-3xl">📋</span>
                    <div>
                      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Module 11 — Catalogue de modèles de documents</h2>
                      <p class="text-sm text-gray-500 dark:text-gray-400">498 modèles organisés en 24 catégories sectorielles</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-6 space-y-8">

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-bold flex items-center justify-center">1</span>
                      Présentation du catalogue
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                      FactPro propose <strong>498 modèles de documents</strong> prêts à l'emploi, organisés en <strong>24 catégories sectorielles</strong> (BTP, Transport, Commerce, Juridique, Santé, etc.). Chaque modèle est conçu pour un type d'activité précis et intègre les mentions obligatoires et la structure adaptée.
                    </p>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-bold flex items-center justify-center">2</span>
                      Accéder au catalogue
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">1.</span> Allez dans <strong>Documents</strong> et cliquez sur le bouton <em>Nouveau document</em>.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">2.</span> Dans la fenêtre de création, cliquez sur l'onglet <strong>Catalogue</strong>.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">3.</span> Parcourez les catégories ou utilisez la recherche pour trouver le modèle adapté à votre activité.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-bold flex items-center justify-center">3</span>
                      Utiliser l'aperçu interactif
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">1.</span> Cliquez sur un modèle pour afficher son <strong>aperçu interactif</strong> avec des données fictives représentatives de votre secteur.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">2.</span> L'aperçu indique le <strong>style visuel PDF</strong> (template) qui sera pré-sélectionné lors de la création du document.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">3.</span> Les données fictives affichées dans l'aperçu sont indicatives — le document réel sera personnalisé avec vos propres informations et celles de votre client.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-xs font-bold flex items-center justify-center">4</span>
                      Créer un document depuis le catalogue
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">1.</span> Après avoir consulté l'aperçu du modèle, cliquez le bouton <em>Créer ce document</em>.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">2.</span> FactPro lance automatiquement la création avec le <strong>bon type de document</strong> ET le <strong>bon style visuel</strong> pré-sélectionné — plus besoin de les choisir manuellement.</li>
                      <li class="flex gap-2"><span class="text-blue-500 font-bold">3.</span> Complétez ensuite les champs du document (client, lignes, montants) comme d'habitude.</li>
                    </ol>
                  </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                  <button @click="scrollTo('module-10')" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <span>←</span> Module précédent
                  </button>
                  <button @click="scrollTo('module-12')" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Module suivant : Styles visuels PDF <span>→</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 MODULE 12 — STYLES VISUELS PDF (TEMPLATES)
            ════════════════════════════════════════════════════════════ -->
            <section id="module-12" class="scroll-mt-6">
              <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700" style="border-left: 4px solid #7c3aed">
                  <div class="flex items-center gap-3">
                    <span class="text-3xl">🎨</span>
                    <div>
                      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Module 12 — Styles visuels PDF (templates)</h2>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Recommandation automatique et galerie de styles</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-6 space-y-8">

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">1</span>
                      Style recommandé automatiquement
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                      Lors de la création d'un document, FactPro recommande automatiquement un style visuel PDF adapté au type de document choisi. Exemples de recommandations :
                    </p>
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                      <table class="w-full text-xs text-left">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                          <tr>
                            <th class="px-4 py-2 font-semibold">Type de document</th>
                            <th class="px-4 py-2 font-semibold">Style recommandé</th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-gray-600 dark:text-gray-300">
                          <tr><td class="px-4 py-2">Bon de livraison</td><td class="px-4 py-2 font-semibold">Template Transport</td></tr>
                          <tr class="bg-gray-50 dark:bg-gray-750"><td class="px-4 py-2">Contrat</td><td class="px-4 py-2 font-semibold">Template Juridique</td></tr>
                          <tr><td class="px-4 py-2">Facture BTP</td><td class="px-4 py-2 font-semibold">Template BTP</td></tr>
                          <tr class="bg-gray-50 dark:bg-gray-750"><td class="px-4 py-2">Facture standard</td><td class="px-4 py-2 font-semibold">Template Commerce</td></tr>
                          <tr><td class="px-4 py-2">Devis</td><td class="px-4 py-2 font-semibold">Template selon votre secteur</td></tr>
                        </tbody>
                      </table>
                    </div>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">2</span>
                      Accéder à la galerie complète de styles
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">1.</span> Lors de la création ou de l'édition d'un document, la galerie de styles est <strong>réduite par défaut</strong> afin de ne pas surcharger l'interface.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">2.</span> Cliquez sur le bouton <em>Modifier</em> situé à côté du style recommandé pour ouvrir la <strong>galerie complète</strong> des styles disponibles selon votre forfait.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">3.</span> Cliquez sur un style pour voir un aperçu en temps réel.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">4.</span> Sélectionnez le style souhaité et confirmez.</li>
                    </ol>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 text-xs font-bold flex items-center justify-center">3</span>
                      Changer le style à tout moment
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">1.</span> Le style visuel peut être modifié <strong>avant ou après la création</strong> du document, tant que celui-ci n'est pas finalisé.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">2.</span> Sur un document en brouillon, cliquez <em>Modifier le style</em> dans le panneau latéral pour accéder à nouveau à la galerie.</li>
                      <li class="flex gap-2"><span class="text-purple-500 font-bold">3.</span> Le changement de style n'affecte pas le contenu du document (lignes, montants, client) — uniquement la mise en page visuelle du PDF généré.</li>
                    </ol>
                  </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                  <button @click="scrollTo('module-11')" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <span>←</span> Module précédent
                  </button>
                  <button @click="scrollTo('module-13')" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Module suivant : Types de documents <span>→</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- ═══════════════════════════════════════════════════════
                 MODULE 13 — COMPRENDRE LES TYPES DE DOCUMENTS
            ════════════════════════════════════════════════════════════ -->
            <section id="module-13" class="scroll-mt-6">
              <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700" style="border-left: 4px solid #059669">
                  <div class="flex items-center gap-3">
                    <span class="text-3xl">📑</span>
                    <div>
                      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Module 13 — Comprendre les types de documents</h2>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Workflow recommandé et rôle de chaque document</p>
                    </div>
                  </div>
                </div>
                <div class="px-6 py-6 space-y-8">

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs font-bold flex items-center justify-center">1</span>
                      Workflow recommandé
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                      Pour une gestion commerciale complète et tracée, FactPro recommande de suivre ce cycle documentaire :
                    </p>
                    <div class="flex flex-wrap items-center gap-2 text-sm font-semibold mb-4">
                      <span class="px-3 py-1.5 rounded-lg bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300">Devis</span>
                      <span class="text-gray-400">→</span>
                      <span class="px-3 py-1.5 rounded-lg bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300">Bon de commande</span>
                      <span class="text-gray-400">→</span>
                      <span class="px-3 py-1.5 rounded-lg bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300">Bon de livraison</span>
                      <span class="text-gray-400">→</span>
                      <span class="px-3 py-1.5 rounded-lg bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300">Facture</span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      Chaque étape peut être générée en un clic depuis le document précédent. Ce workflow est recommandé mais non obligatoire — vous pouvez créer une facture directe sans passer par les étapes précédentes.
                    </p>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs font-bold flex items-center justify-center">2</span>
                      Bon de livraison — pourquoi sans prix ?
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                      Le Bon de livraison (BL) <strong>n'affiche pas de prix</strong> — c'est un comportement normal et voulu. Le BL est un <strong>document de transport et de réception</strong>, pas un document commercial. Il sert à :
                    </p>
                    <ul class="space-y-1.5 text-sm text-gray-600 dark:text-gray-300 list-none pl-2">
                      <li class="flex gap-2"><span class="text-green-600">•</span> Confirmer les quantités et références livrées</li>
                      <li class="flex gap-2"><span class="text-green-600">•</span> Obtenir la signature de réception du client ou du livreur</li>
                      <li class="flex gap-2"><span class="text-green-600">•</span> Servir de preuve de livraison en cas de litige</li>
                    </ul>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">
                      Les prix figurent sur la facture associée, pas sur le bon de livraison.
                    </p>
                  </div>

                  <div>
                    <h3 class="text-base font-bold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                      <span class="w-6 h-6 rounded-full bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 text-xs font-bold flex items-center justify-center">3</span>
                      Facture d'acompte et facture de solde
                    </h3>
                    <ol class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                      <li class="flex gap-2"><span class="text-green-600 font-bold">1.</span> La <strong>Facture d'acompte</strong> permet de facturer un pourcentage du montant total <strong>avant la livraison</strong> (ex : 30% à la commande, 30% à mi-parcours). Elle est liée au devis ou bon de commande d'origine.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">2.</span> Vous pouvez émettre <strong>plusieurs factures d'acompte</strong> successives pour le même projet.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">3.</span> La <strong>Facture de solde</strong> clôture le cycle commercial : elle déduit automatiquement tous les acomptes déjà perçus et affiche uniquement le montant restant dû par le client.</li>
                      <li class="flex gap-2"><span class="text-green-600 font-bold">4.</span> Pour créer une facture de solde, ouvrez le devis ou le bon de commande et cliquez <em>Générer la facture de solde</em> — les acomptes sont repris automatiquement.</li>
                    </ol>
                  </div>

                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between">
                  <button @click="scrollTo('module-12')" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-gray-700">
                    <span>←</span> Module précédent
                  </button>
                  <button @click="scrollTo('module-1')" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                    Revenir au début <span>↑</span>
                  </button>
                </div>
              </div>
            </section>

            <!-- ── CTA final ── -->
            <div class="rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-600 p-8 text-center bg-gray-50 dark:bg-gray-800">
              <div class="text-4xl mb-3">🤖</div>
              <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-2">Une question ? Parlez à SARA</h3>
              <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
                Notre assistante IA répond 24h/24 à toutes vos questions sur FactPro.
              </p>
              <div class="flex flex-wrap justify-center gap-3">
                <button
                  @click="() => window.dispatchEvent(new CustomEvent('sara:open'))"
                  class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-sm text-gray-900 transition-all hover:shadow-md hover:-translate-y-0.5"
                  style="background-color: #F0C040;"
                >
                  <span>🤖</span> Parler à SARA maintenant
                </button>
                <Link
                  href="/contact"
                  class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-sm text-white transition-all hover:shadow-md hover:-translate-y-0.5"
                  style="background-color: #0062CC;"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                  Contacter le support
                </Link>
              </div>
            </div>

          </main>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

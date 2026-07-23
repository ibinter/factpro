<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const filter = ref('all'); // 'all' | 'feature' | 'improvement' | 'fix' | 'security' | 'performance'

const entries = [
  {
    version: '2.4.0',
    date: '23 juillet 2026',
    isNew: true,
    changes: [
      { type: 'feature', text: 'Centre d\'aide avec 100 questions-réponses organisées en 10 catégories' },
      { type: 'feature', text: 'Recherche globale Ctrl+K — clients, factures et produits en temps réel' },
      { type: 'feature', text: 'SARA : mode interne dans l\'application avec suggestions contextuelles' },
      { type: 'feature', text: '18 pages légales complètes (SLA, DPA, Charte éthique, Accessibilité…)' },
      { type: 'feature', text: 'Pages d\'erreur brandées 401/403/404/500 avec animation étoiles' },
      { type: 'feature', text: 'Visite guidée onboarding pour les nouveaux utilisateurs' },
      { type: 'feature', text: 'Cookie banner RGPD avec 4 catégories (nécessaires, préférences, statistiques, marketing)' },
      { type: 'improvement', text: 'Landing page enrichie : info bar, trust badges, avant/après, témoignages, autres logiciels IBIG' },
      { type: 'improvement', text: 'Emails lifecycle : bienvenue, fin d\'essai, première facture, compte bloqué, paiement échoué' },
    ]
  },
  {
    version: '2.3.0',
    date: '15 juillet 2026',
    isNew: false,
    changes: [
      { type: 'security', text: 'Chiffrement AES-256 des données sensibles (numéros fiscaux, IBAN)' },
      { type: 'feature', text: 'Coffre-fort numérique avec archivage immuable SHA-256 sur 10 ans' },
      { type: 'feature', text: 'Signature qualifiée eIDAS niveau avancé avec OTP SMS' },
      { type: 'feature', text: 'Rapport de conformité RGPD automatisé' },
      { type: 'security', text: 'Politique de mots de passe renforcée et gestion des sessions' },
      { type: 'feature', text: 'Contrats commerciaux : gestion, alertes d\'expiration, versions' },
      { type: 'feature', text: 'GED (Gestion électronique de documents) avec tags et recherche fulltext' },
      { type: 'feature', text: 'Analytics & BI avancés : 7 widgets, graphiques CA, taux de recouvrement' },
    ]
  },
  {
    version: '2.2.0',
    date: '5 juillet 2026',
    isNew: false,
    changes: [
      { type: 'feature', text: 'Module RH & Paie : bulletins de paie OHADA, CNSS, IRPP' },
      { type: 'feature', text: 'Workflow d\'approbation multi-niveaux pour les documents' },
      { type: 'feature', text: 'Tracking email : ouverture et clics sur les documents envoyés' },
      { type: 'feature', text: 'Programme fidélité client : points, niveaux, récompenses' },
      { type: 'feature', text: 'Forecasting CA & objectifs commerciaux' },
      { type: 'improvement', text: 'Mode hors-ligne PWA avec synchronisation différée (IndexedDB)' },
      { type: 'improvement', text: 'Export Excel natif .xlsx sur toutes les listes' },
      { type: 'performance', text: 'Cache Redis — pages 3× plus rapides' },
    ]
  },
  {
    version: '2.1.0',
    date: '20 juin 2026',
    isNew: false,
    changes: [
      { type: 'feature', text: 'Assistant IA SARA pour la saisie assistée (Claude API)' },
      { type: 'feature', text: 'Import CSV/Excel clients et produits' },
      { type: 'feature', text: 'Notifications temps réel (Laravel Echo + WebSockets)' },
      { type: 'feature', text: 'Multi-langue : Français, Anglais, Arabe (RTL)' },
      { type: 'feature', text: 'CRM léger : pipeline de prospects avec Kanban' },
    ]
  },
  {
    version: '2.0.0',
    date: '1er juin 2026',
    isNew: false,
    changes: [
      { type: 'feature', text: '100 templates PDF professionnels (toutes familles de documents)' },
      { type: 'feature', text: 'OCR scan des factures fournisseurs (extraction automatique)' },
      { type: 'feature', text: 'Connecteurs Zapier & Make (webhooks entrants/sortants)' },
      { type: 'feature', text: 'Marketplace de templates communautaires' },
      { type: 'feature', text: 'SDK PHP officiel FactPro (package composer)' },
      { type: 'feature', text: 'White-label revendeur : logo et couleurs personnalisés' },
      { type: 'feature', text: 'Factur-X / e-facture France 2026 (norme CII/EN-16931)' },
      { type: 'improvement', text: 'Dashboard BI avancé avec 7 KPIs et graphiques SVG' },
    ]
  },
  {
    version: '1.9.0',
    date: '10 mai 2026',
    isNew: false,
    changes: [
      { type: 'feature', text: 'Passerelles paiement Afrique : CinetPay, FedaPay, Flutterwave' },
      { type: 'feature', text: 'POS avancé : gestion tables restaurant, multi-caissier, rapport X' },
      { type: 'feature', text: 'Fiscalité multi-pays OHADA, Maroc, Sénégal, Algérie' },
      { type: 'feature', text: 'Programme ambassadeur avec commissions sur 3 niveaux (jusqu\'à 50%)' },
      { type: 'security', text: 'Double authentification TOTP (Google Authenticator, Authy)' },
    ]
  },
  {
    version: '1.5.0',
    date: '1er avril 2026',
    isNew: false,
    changes: [
      { type: 'feature', text: 'Module Time Tracking & Projets avec facturation au temps passé' },
      { type: 'feature', text: 'Notes de frais avec justificatifs photo et remboursement' },
      { type: 'feature', text: 'Comptabilité & export FEC (France)' },
      { type: 'feature', text: 'Factures récurrentes avec auto-envoi' },
      { type: 'feature', text: 'Multi-devises 160+ avec taux de change automatiques' },
      { type: 'feature', text: 'Achats fournisseurs et gestion des bons de commande' },
    ]
  },
  {
    version: '1.0.0',
    date: '1er janvier 2026',
    isNew: false,
    changes: [
      { type: 'feature', text: 'Lancement de IBIG FactPro — facturation et gestion d\'entreprise en ligne' },
      { type: 'feature', text: 'Devis, factures, avoirs, bons de livraison, bons de commande' },
      { type: 'feature', text: 'Gestion clients et produits/services' },
      { type: 'feature', text: 'Module caisse POS tactile avec impression thermique 58/80mm' },
      { type: 'feature', text: 'Paiement Mobile Money (Orange Money, Wave, MTN MoMo)' },
      { type: 'feature', text: 'QR anti-falsification sur tous les documents' },
      { type: 'feature', text: 'Portail client self-service pour suivi des documents' },
      { type: 'feature', text: 'API REST publique v1 complète' },
      { type: 'feature', text: 'PWA installable sur mobile (Android & iPhone)' },
    ]
  },
];

const typeConfig = {
  feature:     { label: 'Nouveauté',    bg: '#eff6ff', color: '#1d4ed8', dot: '#3b82f6', border: '#bfdbfe' },
  improvement: { label: 'Amélioration', bg: '#f0fdf4', color: '#166534', dot: '#22c55e', border: '#bbf7d0' },
  fix:         { label: 'Correction',   bg: '#fff7ed', color: '#9a3412', dot: '#f97316', border: '#fed7aa' },
  security:    { label: 'Sécurité',     bg: '#fdf4ff', color: '#7e22ce', dot: '#a855f7', border: '#e9d5ff' },
  performance: { label: 'Performance',  bg: '#f0fdf4', color: '#065f46', dot: '#10b981', border: '#a7f3d0' },
};

const filterOptions = [
  { key: 'all',         label: 'Tout',          icon: '🗂' },
  { key: 'feature',     label: 'Nouveautés',    icon: '✨' },
  { key: 'improvement', label: 'Améliorations', icon: '⚡' },
  { key: 'fix',         label: 'Corrections',   icon: '🔧' },
  { key: 'security',    label: 'Sécurité',      icon: '🔐' },
  { key: 'performance', label: 'Performance',   icon: '🚀' },
];

const filtered = computed(() => {
  if (filter.value === 'all') return entries;
  return entries
    .map(e => ({ ...e, changes: e.changes.filter(c => c.type === filter.value) }))
    .filter(e => e.changes.length > 0);
});

const totalChanges = computed(() =>
  entries.reduce((acc, e) => acc + e.changes.length, 0)
);
</script>

<template>
  <Head title="Nouveautés & Mises à jour" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Nouveautés & Mises à jour</h2>
    </template>

    <!-- Hero header -->
    <div style="background: linear-gradient(135deg, #002D5B 0%, #0062CC 100%)" class="py-14 px-4 text-center text-white">
      <div class="mx-auto max-w-3xl">
        <div class="mb-3 text-5xl">🚀</div>
        <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Nouveautés &amp; Mises à jour</h1>
        <p class="mt-3 text-lg text-blue-200">
          Suivez l'évolution de IBIG FactPro — chaque version apporte son lot d'améliorations.
        </p>
        <div class="mt-6 inline-flex items-center gap-3 rounded-full bg-white/10 px-5 py-2 text-sm backdrop-blur">
          <span class="font-bold text-yellow-300">{{ entries.length }} versions</span>
          <span class="text-blue-300">·</span>
          <span class="font-bold text-yellow-300">{{ totalChanges }} améliorations</span>
          <span class="text-blue-300">·</span>
          <span class="text-blue-100">depuis janvier 2026</span>
        </div>
      </div>
    </div>

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">

      <!-- Filter pills -->
      <div class="mb-10 flex flex-wrap items-center justify-center gap-2">
        <button
          v-for="opt in filterOptions"
          :key="opt.key"
          @click="filter = opt.key"
          class="inline-flex items-center gap-1.5 rounded-full border px-4 py-1.5 text-sm font-medium transition-all duration-150 focus:outline-none"
          :style="filter === opt.key
            ? 'background:#0062CC;color:#fff;border-color:#0062CC;box-shadow:0 2px 8px rgba(0,98,204,.35)'
            : 'background:#fff;color:#374151;border-color:#e5e7eb'"
        >
          <span>{{ opt.icon }}</span>
          {{ opt.label }}
        </button>
      </div>

      <!-- Timeline -->
      <div class="relative">
        <!-- Vertical line -->
        <div class="absolute left-[88px] top-0 bottom-0 hidden w-px bg-gray-200 sm:block"></div>

        <div class="space-y-12">
          <article
            v-for="entry in filtered"
            :key="entry.version"
            class="relative"
          >
            <!-- Version / date column (desktop) -->
            <div class="flex flex-col sm:flex-row sm:gap-0">
              <!-- Left: version badge -->
              <div class="mb-4 shrink-0 sm:mb-0 sm:w-[120px] sm:pr-6 sm:text-right">
                <div class="inline-block">
                  <!-- Golden ring for latest -->
                  <span
                    class="inline-flex flex-col items-center"
                  >
                    <span
                      class="inline-block rounded-lg px-2.5 py-1 text-sm font-black leading-none"
                      :style="entry.isNew
                        ? 'background:#F0C040;color:#001d3d;box-shadow:0 0 0 3px #F0C040, 0 0 0 5px #fef9c3'
                        : 'background:#002D5B;color:#fff'"
                    >
                      v{{ entry.version }}
                    </span>
                    <span v-if="entry.isNew" class="mt-1 inline-block animate-pulse rounded-full bg-yellow-400 px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-widest text-yellow-900">
                      NOUVEAU
                    </span>
                    <span class="mt-1 block text-xs text-gray-400 whitespace-nowrap">{{ entry.date }}</span>
                  </span>
                </div>
              </div>

              <!-- Timeline dot (desktop) -->
              <div class="absolute left-[81px] hidden sm:flex items-center justify-center">
                <div
                  class="h-4 w-4 rounded-full border-2 border-white"
                  :style="entry.isNew ? 'background:#F0C040;box-shadow:0 0 0 3px #F0C04066' : 'background:#0062CC'"
                ></div>
              </div>

              <!-- Right: changes -->
              <div
                class="flex-1 rounded-xl border bg-white p-5 shadow-sm transition-shadow hover:shadow-md sm:ml-8"
                :style="entry.isNew ? 'border-color:#F0C040;box-shadow:0 0 0 2px #F0C04033' : 'border-color:#e5e7eb'"
              >
                <!-- Version title row -->
                <div class="mb-4 flex flex-wrap items-center gap-2 border-b border-gray-100 pb-3">
                  <h2 class="text-lg font-extrabold text-gray-900">Version {{ entry.version }}</h2>
                  <span class="text-sm text-gray-400">— {{ entry.date }}</span>
                  <span
                    v-if="entry.isNew"
                    class="ml-auto inline-flex animate-pulse items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-bold"
                    style="background:#fef9c3;color:#854d0e"
                  >
                    ⭐ Dernière version
                  </span>
                </div>

                <!-- Change items -->
                <ul class="space-y-2.5">
                  <li
                    v-for="(change, i) in entry.changes"
                    :key="i"
                    class="flex items-start gap-3"
                  >
                    <!-- Colored dot -->
                    <span
                      class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                      :style="`background:${typeConfig[change.type]?.dot ?? '#6b7280'}`"
                    ></span>
                    <!-- Type badge -->
                    <span
                      class="mt-0.5 shrink-0 rounded-full border px-2 py-0.5 text-[11px] font-semibold"
                      :style="`background:${typeConfig[change.type]?.bg};color:${typeConfig[change.type]?.color};border-color:${typeConfig[change.type]?.border}`"
                    >
                      {{ typeConfig[change.type]?.label ?? change.type }}
                    </span>
                    <!-- Text -->
                    <span class="text-sm leading-relaxed text-gray-700">{{ change.text }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </article>
        </div>
      </div>

      <!-- Footer CTA -->
      <div class="mt-16 rounded-2xl p-8 text-center" style="background:linear-gradient(135deg,#002D5B,#0062CC)">
        <p class="text-lg font-bold text-white">Une suggestion ou un bug ?</p>
        <p class="mt-1 text-sm text-blue-200">Notre équipe est à votre écoute pour améliorer votre expérience.</p>
        <a
          href="mailto:support@ibigsoft.com"
          class="mt-4 inline-block rounded-lg px-6 py-2.5 text-sm font-bold transition hover:opacity-90"
          style="background:#F0C040;color:#001d3d"
        >
          Contacter le support
        </a>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

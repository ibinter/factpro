<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';

const props = defineProps({
    versions: { type: Array, default: () => [] },
});

const filter = ref('all');

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

// Fallback static data if no server data provided
const staticEntries = [
    {
        version: '2.6.0',
        date: '28 juillet 2026',
        isNew: true,
        type: 'major',
        changes: [
            { type: 'improvement', text: 'Navigation publique redessinée : menus déroulants Produit / Ressources / Entreprise pour une meilleure lisibilité sur tous les écrans' },
            { type: 'improvement', text: 'Paramètres société entièrement repensés en 6 onglets : Identité, Coordonnées, Facturation, Apparence, Signature, Paiements' },
            { type: 'improvement', text: 'Dashboard BI : devise dynamique selon la société, alertes individuelles dismissibles, accès rapides à 12 modules en un clic' },
            { type: 'improvement', text: 'Dashboard : graphique CA sur 12 mois avec affichage du nombre de factures au survol' },
            { type: 'feature', text: 'Footer interne FactPro © 2026 — Un produit IBIG Soft, liens Aide / Nouveautés / CGU / Support' },
            { type: 'improvement', text: 'Responsive mobile : overflow-x-auto sur tous les tableaux (Clients, Produits, Rapports)' },
            { type: 'improvement', text: 'En-têtes de liste avec flex-wrap pour éviter le débordement sur petits écrans' },
            { type: 'feature', text: '3 nouveaux modes de paiement configurables : Moov Money, Espèces, Chèque' },
            { type: 'improvement', text: 'Sélecteur pays (18 pays) et devise (9 devises) avec menus déroulants dans les paramètres société' },
            { type: 'fix', text: 'Correction doublon blocs signature dans les paramètres société' },
            { type: 'fix', text: 'Correction incohérence "14 jours" → "7 jours" dans les métadonnées Schema.org' },
            { type: 'improvement', text: 'Mode sombre complet (dark mode) sur le dashboard, les paramètres société et les listes' },
        ],
    },
    {
        version: '2.5.0',
        date: '27 juillet 2026',
        isNew: false,
        type: 'major',
        changes: [
            { type: 'feature', text: 'Catalogue de 498 modèles de documents organisés par secteur (Vente, BTP, Santé, Agriculture, IT, Restauration, Finance, etc.)' },
            { type: 'feature', text: 'Sélection automatique du modèle PDF selon le type de document (Bon de livraison → Transport, Contrat → Juridique, etc.)' },
            { type: 'feature', text: 'Aperçu interactif de chaque modèle avec données fictives représentatives par secteur' },
            { type: 'feature', text: 'Bandeau informatif "Aperçu indicatif" dans la prévisualisation des modèles' },
            { type: 'feature', text: 'Galerie de styles PDF réduite par défaut pour un parcours de création simplifié' },
            { type: 'feature', text: 'Modèle visuel recommandé visible dans la prévisualisation catalogue avant création' },
            { type: 'fix', text: 'Correction de 6 incohérences critiques entre le catalogue et les types de documents (factproType)' },
            { type: 'fix', text: 'La Facture Proforma, Facture d\'Acompte, Reçu de paiement et Bon de Commande Client sont maintenant correctement routés' },
            { type: 'fix', text: 'Correction du montant à 0 sur les Avis de loyer et Reçus de caution' },
            { type: 'fix', text: 'Correction de la description manquante (mois/année) sur les quittances de loyer' },
            { type: 'fix', text: '4 nouveaux templates PDF enregistrés (Futuriste Dark, Tech Modern, etc.)' },
            { type: 'fix', text: 'Ordre des routes corrigé pour éviter le conflit sur l\'export Excel' },
        ],
    },
    {
        version: '2.4.0',
        date: '23 juillet 2026',
        isNew: false,
        type: 'major',
        changes: [
            { type: 'feature', text: 'Centre d\'aide avec 100 questions-réponses organisées en 10 catégories' },
            { type: 'feature', text: 'Recherche globale Ctrl+K — clients, factures et produits en temps réel' },
            { type: 'feature', text: 'SARA : assistante IA avec suggestions contextuelles' },
            { type: 'feature', text: '18 pages légales complètes (SLA, DPA, Charte éthique, Accessibilité…)' },
            { type: 'improvement', text: 'Landing page enrichie : trust badges, témoignages, autres logiciels IBIG' },
            { type: 'improvement', text: 'Emails lifecycle : bienvenue, fin d\'essai, première facture' },
        ],
    },
    {
        version: '2.3.0',
        date: '15 juillet 2026',
        isNew: false,
        type: 'minor',
        changes: [
            { type: 'security', text: 'Chiffrement AES-256 des données sensibles (numéros fiscaux, IBAN)' },
            { type: 'feature', text: 'Coffre-fort numérique avec archivage immuable SHA-256 sur 10 ans' },
            { type: 'feature', text: 'Signature qualifiée eIDAS niveau avancé avec OTP SMS' },
            { type: 'feature', text: 'GED (Gestion électronique de documents) avec tags et recherche fulltext' },
        ],
    },
    {
        version: '2.0.0',
        date: '1er juin 2026',
        isNew: false,
        type: 'major',
        changes: [
            { type: 'feature', text: '100 templates PDF professionnels (toutes familles de documents)' },
            { type: 'feature', text: 'White-label revendeur : logo et couleurs personnalisés' },
            { type: 'feature', text: 'Factur-X / e-facture France 2026 (norme CII/EN-16931)' },
            { type: 'improvement', text: 'Dashboard BI avancé avec 7 KPIs et graphiques SVG' },
        ],
    },
    {
        version: '1.0.0',
        date: '1er janvier 2026',
        isNew: false,
        type: 'major',
        changes: [
            { type: 'feature', text: 'Lancement de IBIG FactPro — facturation et gestion d\'entreprise en ligne' },
            { type: 'feature', text: 'Devis, factures, avoirs, bons de livraison, bons de commande' },
            { type: 'feature', text: 'Paiement Mobile Money (Orange Money, Wave, MTN MoMo)' },
            { type: 'feature', text: 'QR anti-falsification sur tous les documents' },
            { type: 'feature', text: 'PWA installable sur mobile (Android & iPhone)' },
        ],
    },
];

const entries = computed(() =>
    props.versions && props.versions.length > 0 ? props.versions : staticEntries
);

const filtered = computed(() => {
    if (filter.value === 'all') return entries.value;
    return entries.value
        .map(e => ({ ...e, changes: e.changes.filter(c => c.type === filter.value) }))
        .filter(e => e.changes.length > 0);
});

const totalChanges = computed(() =>
    entries.value.reduce((acc, e) => acc + e.changes.length, 0)
);
</script>

<template>
    <Head title="Nouveautés & Mises à jour — IBIG FactPro" />

    <PublicNav />

    <!-- Hero -->
    <section style="background:linear-gradient(135deg,#001120 0%,#002D5B 60%,#0062CC 100%)" class="py-16 px-4 text-center text-white">
        <div class="mx-auto max-w-3xl">
            <div class="mb-3 text-5xl">🚀</div>
            <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">
                Nouveautés &amp; Mises à jour<br class="hidden sm:block" />
                <span style="color:#F0C040">IBIG FactPro</span>
            </h1>
            <p class="mt-4 text-lg text-blue-200">
                Suivez l'évolution du logiciel version après version.
            </p>
            <div class="mt-6 inline-flex items-center gap-3 rounded-full bg-white/10 px-5 py-2.5 text-sm backdrop-blur">
                <span class="font-bold" style="color:#F0C040">{{ entries.length }} versions</span>
                <span class="text-blue-300">·</span>
                <span class="font-bold" style="color:#F0C040">{{ totalChanges }} améliorations</span>
                <span class="text-blue-300">·</span>
                <span class="text-blue-100">depuis janvier 2026</span>
            </div>
        </div>
    </section>

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

        <!-- Empty state -->
        <div v-if="filtered.length === 0" class="py-20 text-center">
            <div class="text-5xl mb-4">📭</div>
            <p class="text-lg font-semibold text-gray-600">Aucune entrée pour ce filtre.</p>
            <button @click="filter = 'all'" class="mt-4 text-sm text-blue-600 hover:underline">Voir tout</button>
        </div>

        <!-- Timeline -->
        <div v-else class="relative">
            <!-- Vertical line -->
            <div class="absolute left-[88px] top-0 bottom-0 hidden w-px bg-gray-200 sm:block"></div>

            <div class="space-y-12">
                <article v-for="entry in filtered" :key="entry.version" class="relative">
                    <div class="flex flex-col sm:flex-row sm:gap-0">
                        <!-- Left: version badge -->
                        <div class="mb-4 shrink-0 sm:mb-0 sm:w-[120px] sm:pr-6 sm:text-right">
                            <div class="inline-block">
                                <span class="inline-flex flex-col items-center">
                                    <span
                                        class="inline-block rounded-lg px-2.5 py-1 text-sm font-black leading-none"
                                        :style="entry.isNew
                                            ? 'background:#F0C040;color:#001d3d;box-shadow:0 0 0 3px #F0C040,0 0 0 5px #fef9c3'
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

                        <!-- Timeline dot -->
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

                            <ul class="space-y-2.5">
                                <li v-for="(change, i) in entry.changes" :key="i" class="flex items-start gap-3">
                                    <span
                                        class="mt-1.5 h-2 w-2 shrink-0 rounded-full"
                                        :style="`background:${typeConfig[change.type]?.dot ?? '#6b7280'}`"
                                    ></span>
                                    <span
                                        class="mt-0.5 shrink-0 rounded-full border px-2 py-0.5 text-[11px] font-semibold"
                                        :style="`background:${typeConfig[change.type]?.bg};color:${typeConfig[change.type]?.color};border-color:${typeConfig[change.type]?.border}`"
                                    >
                                        {{ typeConfig[change.type]?.label ?? change.type }}
                                    </span>
                                    <span class="text-sm leading-relaxed text-gray-700">{{ change.text }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </article>
            </div>
        </div>

        <!-- CTA -->
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

    <PublicFooter />
</template>

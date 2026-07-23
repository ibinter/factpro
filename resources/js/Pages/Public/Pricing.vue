<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';
import Analytics from '@/Components/Analytics.vue';

const props = defineProps({
    plans: { type: Array, default: () => [] },
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const billing = ref('monthly');
const fmt = (n) => new Intl.NumberFormat('fr-FR').format(Math.round(Number(n) || 0));
const val = (v) => (v === 'unlimited' || v == null ? '∞' : fmt(v));
const has = (plan, needle) =>
    (plan.features || []).some((f) => f.toLowerCase().includes(needle.toLowerCase()));

const price = (plan) => billing.value === 'monthly'
    ? plan.price_monthly
    : Math.round(plan.price_monthly * 12 * 0.8 / 12);

const planColors = {
    starter:    { bg: '#f8faff', ring: '#dbeafe', btn: '#eff6ff', btnText: '#1d4ed8', accent: '#3b82f6' },
    pro:        { bg: '#001d3d', ring: '#F0C040', btn: '#F0C040', btnText: '#001d3d', accent: '#F0C040' },
    business:   { bg: '#f0fdf4', ring: '#bbf7d0', btn: '#dcfce7', btnText: '#166534', accent: '#22c55e' },
    enterprise: { bg: '#faf5ff', ring: '#e9d5ff', btn: '#ede9fe', btnText: '#6d28d9', accent: '#8b5cf6' },
};
const color = (plan) => planColors[plan.code] || planColors.starter;

/* Tableau comparatif */
const limitRows = [
    { label: 'Documents / mois',     key: 'documents_per_month' },
    { label: 'Utilisateurs',         key: 'users' },
    { label: 'Sociétés',             key: 'companies' },
    { label: 'Clients',              key: 'customers' },
    { label: 'Produits / services',  key: 'products' },
    { label: 'Modèles de documents', key: 'templates' },
    { label: 'Stockage (Mo)',        key: 'storage_mb' },
];

const featureRows = [
    { label: 'QR Anti-falsification',       needle: 'qr' },
    { label: 'Portail client',              needle: 'portail' },
    { label: 'Multi-devises 160+',          needle: 'devises' },
    { label: 'Factures récurrentes',        needle: 'récurrentes' },
    { label: 'Signature électronique',      needle: 'signature' },
    { label: 'Caisse POS / ticket',         needle: 'caisse' },
    { label: 'Impression thermique 58/80mm',needle: 'thermique' },
    { label: 'Gestion des stocks',          needle: 'stocks' },
    { label: 'Mobile Money',               needle: 'mobile money' },
    { label: 'Comptabilité + FEC',          needle: 'fec' },
    { label: 'Module RH & Paie',           needle: 'rh' },
    { label: 'API REST',                   needle: 'api' },
    { label: 'White-Label',                needle: 'white-label' },
    { label: 'Assistant IA SARA avancé',   needle: 'sara' },
];

const faqs = [
    { q: 'Les prix sont-ils en FCFA ?',
      a: 'Oui, tous nos forfaits sont facturés en FCFA (XOF). Les équivalents en euros et dollars sont donnés à titre indicatif au taux en vigueur.' },
    { q: 'Comment fonctionne la remise annuelle ?',
      a: "En choisissant la facturation annuelle, vous bénéficiez de 20 % de réduction, soit environ deux mois offerts par an. Le montant total est facturé en une fois." },
    { q: "L'essai gratuit inclut-il toutes les fonctionnalités ?",
      a: "Oui, pendant 7 jours vous accédez à toutes les fonctionnalités du forfait choisi. Aucune carte bancaire requise pour démarrer." },
    { q: 'Puis-je changer de forfait à tout moment ?',
      a: 'Oui, vous pouvez passer à un forfait supérieur ou inférieur depuis votre espace abonnement. La différence de prix est calculée au prorata.' },
    { q: 'Quels moyens de paiement acceptez-vous ?',
      a: 'Mobile Money (Orange Money, Wave, MTN, Moov), virement bancaire national et international, CinetPay, FedaPay, et paiement en espèces en agence.' },
    { q: 'Puis-je résilier à tout moment ?',
      a: "Oui, sans engagement. Vous pouvez résilier depuis votre espace abonnement. Votre accès reste actif jusqu'à la fin de la période payée." },
];
const openFaq = ref(null);
const toggleFaq = (i) => (openFaq.value = openFaq.value === i ? null : i);

/* ── ROI Calculator ── */
const roiFactures   = ref(20);
const roiMinutes    = ref(30);
const roiHoraire    = ref(5000);
const STARTER_PRICE = 4900;

const roiHeures   = computed(() => Math.round((roiFactures.value * roiMinutes.value) / 60 * 10) / 10);
const roiCout     = computed(() => Math.round(roiHeures.value * roiHoraire.value));
const roiEconomie = computed(() => Math.round(roiCout.value * 0.75));
const roiPct      = computed(() => Math.round((roiEconomie.value / STARTER_PRICE) * 100));

/* ── FAQ enrichie ── */
const enrichedFaqs = [
    { q: 'Puis-je changer de plan à tout moment ?',
      a: 'Oui, la mise à niveau est instantanée. Le crédit de votre plan actuel est proratisé et déduit du nouveau forfait.' },
    { q: 'Y a-t-il des frais cachés ?',
      a: 'Non. Le prix affiché inclut tout — hébergement, sauvegardes automatiques, mises à jour et support standard.' },
    { q: "Que se passe-t-il à la fin de l'essai gratuit ?",
      a: 'Votre compte passe automatiquement en mode lecture seule. Vos données sont conservées pendant 90 jours, le temps de choisir un forfait.' },
    { q: 'Quels moyens de paiement acceptez-vous ?',
      a: 'Wave, Orange Money, MTN MoMo, Moov, CinetPay, Stripe (carte internationale), virement bancaire, et plus encore.' },
    { q: 'Proposez-vous des remises annuelles ?',
      a: 'Oui ! Payez annuellement et économisez 2 mois (≈ 17 % de réduction). Contactez-nous pour les tarifs entreprise sur mesure.' },
];
const openEnrichedFaq = ref(null);
const toggleEnrichedFaq = (i) => (openEnrichedFaq.value = openEnrichedFaq.value === i ? null : i);

/* ── Données statiques comparateur enrichi ── */
const staticPlans = ['Gratuit', 'Starter', 'Pro', 'Business'];
const staticRows = [
    { label: 'Factures / mois',    values: ['5', 'Illimité', 'Illimité', 'Illimité'] },
    { label: 'Clients',            values: ['10', '200', 'Illimité', 'Illimité'] },
    { label: 'Utilisateurs',       values: ['1', '2', '5', '20'] },
    { label: 'Templates PDF',      values: ['3', '10', '30', '100+'] },
    { label: 'Devis / Proforma',   values: ['✅', '✅', '✅', '✅'] },
    { label: 'Module stocks',      values: ['❌', '✅', '✅', '✅'] },
    { label: 'Multi-sociétés',     values: ['❌', '❌', '✅', '✅'] },
    { label: 'API REST',           values: ['❌', '❌', '✅', '✅'] },
    { label: 'White label',        values: ['❌', '❌', '❌', '✅'] },
    { label: 'Support prioritaire',values: ['❌', 'Email', 'Chat', 'Dédié'] },
    { label: 'SARA IA',            values: ['Limité', '✅', '✅', '✅'] },
];
</script>

<template>
    <Head title="Tarifs — IBIG FactPro">
        <meta name="description" content="Plans et tarifs FactPro : Gratuit, Starter 4 900 FCFA/mois, Pro, Business. Essai 14 jours sans carte bancaire.">
        <meta property="og:title" content="Tarifs FactPro — Plans adaptés à chaque entreprise">
        <meta property="og:description" content="Plans et tarifs FactPro : Gratuit, Starter 4 900 FCFA/mois, Pro, Business. Essai 14 jours sans carte bancaire.">
        <meta property="og:type" content="website">
        <script type="application/ld+json">{"@context":"https://schema.org","@type":"WebPage","name":"Tarifs FactPro","description":"Plans et tarifs FactPro pour PME africaines"}</script>
    </Head>

    <div class="min-h-screen bg-white text-gray-800">
        <PublicNav :can-login="canLogin" :can-register="canRegister" />

        <!-- ══ HERO TARIFS ══ -->
        <section style="background:linear-gradient(135deg,#001d3d 0%,#0062CC 70%,#0099ff 100%)" class="px-6 py-20 text-center text-white">
            <span class="inline-block rounded-full px-4 py-1.5 text-xs font-bold mb-4" style="background:rgba(240,192,64,.2);color:#F0C040;border:1px solid rgba(240,192,64,.3)">
                ✨ 7 jours d'essai gratuit · Sans carte bancaire
            </span>
            <h1 class="text-4xl font-extrabold sm:text-5xl">Des tarifs clairs, sans surprise</h1>
            <p class="mx-auto mt-4 max-w-2xl text-white/80 text-lg">
                Tous les forfaits incluent le QR anti-falsification et l'essai gratuit de 7 jours.<br>
                Résiliez à tout moment, sans engagement.
            </p>

            <!-- Toggle mensuel / annuel -->
            <div class="mt-8 inline-flex items-center gap-1 rounded-full p-1 shadow" style="background:rgba(255,255,255,.15)">
                <button
                    class="rounded-full px-6 py-2 text-sm font-semibold transition"
                    :class="billing === 'monthly' ? 'text-brand-900 bg-white shadow' : 'text-white/80 hover:text-white'"
                    @click="billing = 'monthly'"
                >Mensuel</button>
                <button
                    class="rounded-full px-6 py-2 text-sm font-semibold transition flex items-center gap-2"
                    :class="billing === 'yearly' ? 'text-brand-900 bg-white shadow' : 'text-white/80 hover:text-white'"
                    @click="billing = 'yearly'"
                >
                    Annuel
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold" style="background:#F0C040;color:#001d3d">-20%</span>
                </button>
            </div>

            <!-- Badges de confiance -->
            <div class="mt-8 flex flex-wrap justify-center gap-4 text-xs text-white/60">
                <span>✓ Sans engagement</span>
                <span>✓ Données hébergées en sécurité</span>
                <span>✓ Support inclus</span>
                <span>✓ Conforme OHADA</span>
                <span>✓ Mobile Money intégré</span>
            </div>
        </section>

        <!-- ══ CARTES FORFAITS ══ -->
        <section class="mx-auto max-w-7xl px-6 -mt-6 pb-16">
            <div v-if="!plans.length" class="py-20 text-center text-gray-400">
                <div class="text-5xl mb-4">📦</div>
                <p class="text-lg font-semibold">Forfaits en cours de configuration</p>
                <p class="text-sm mt-2">Contactez-nous pour obtenir une offre personnalisée.</p>
            </div>

            <div v-else class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4 items-start">
                <div
                    v-for="plan in plans"
                    :key="plan.code"
                    class="relative flex flex-col rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1"
                    :class="plan.highlight ? 'shadow-2xl scale-105' : 'shadow-lg'"
                    :style="plan.highlight
                        ? 'background:#001d3d;ring:2px solid #F0C040'
                        : `background:${color(plan).bg};outline:1px solid ${color(plan).ring}`"
                >
                    <!-- Badge Populaire -->
                    <div v-if="plan.highlight"
                         class="absolute top-0 inset-x-0 py-1.5 text-center text-xs font-extrabold uppercase tracking-widest"
                         style="background:#F0C040;color:#001d3d">
                        ⭐ Le plus populaire
                    </div>

                    <div class="p-6" :class="plan.highlight ? 'pt-10' : ''">
                        <!-- Nom du forfait -->
                        <div class="text-xs font-extrabold uppercase tracking-widest mb-1"
                             :style="`color:${color(plan).accent}`">
                            {{ plan.name }}
                        </div>
                        <p class="text-sm leading-snug" :class="plan.highlight ? 'text-white/70' : 'text-gray-500'">
                            {{ plan.short_description }}
                        </p>

                        <!-- Prix -->
                        <div class="mt-5">
                            <div class="flex items-end gap-1">
                                <span class="text-4xl font-extrabold" :class="plan.highlight ? 'text-white' : 'text-gray-900'">
                                    {{ new Intl.NumberFormat('fr-FR').format(price(plan)) }}
                                </span>
                                <span class="mb-1 text-sm" :class="plan.highlight ? 'text-white/60' : 'text-gray-400'"> FCFA/mois</span>
                            </div>
                            <div class="text-xs mt-1" :class="plan.highlight ? 'text-white/40' : 'text-gray-400'">
                                ≈ {{ new Intl.NumberFormat('fr-FR').format(Math.round(price(plan) / 655.957)) }} € · {{ new Intl.NumberFormat('fr-FR').format(Math.round(price(plan) / 590)) }} $
                                <template v-if="billing === 'yearly'">
                                    · {{ new Intl.NumberFormat('fr-FR').format(Math.round(plan.price_monthly * 12 * 0.8)) }} FCFA/an
                                </template>
                            </div>
                        </div>

                        <!-- CTA -->
                        <a v-if="canRegister" href="/register"
                           class="mt-5 block rounded-xl py-3 text-center text-sm font-bold transition hover:scale-105 active:scale-95"
                           :style="`background:${color(plan).btn};color:${color(plan).btnText}`">
                            Démarrer l'essai gratuit →
                        </a>

                        <!-- Séparateur -->
                        <div class="my-5 border-t" :style="`border-color:${plan.highlight ? 'rgba(255,255,255,.1)' : '#e5e7eb'}`"></div>

                        <!-- Liste des fonctionnalités -->
                        <ul class="space-y-2">
                            <li v-for="feat in (plan.features || [])" :key="feat"
                                class="flex items-start gap-2 text-sm"
                                :class="plan.highlight ? 'text-white/80' : 'text-gray-600'">
                                <span class="mt-0.5 flex-shrink-0 text-base" :style="`color:${color(plan).accent}`">✓</span>
                                {{ feat }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Note annuel -->
            <p v-if="billing === 'yearly'" class="mt-6 text-center text-xs text-gray-400">
                * Tarif mensuel affiché pour la facturation annuelle — soit 2 mois offerts par rapport au mensuel.
            </p>
        </section>

        <!-- ══ TABLEAU COMPARATIF ══ -->
        <section v-if="plans.length" class="bg-gray-50 px-6 py-20">
            <div class="mx-auto max-w-7xl">
                <div class="text-center mb-12">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest mb-3" style="background:#eff6ff;color:#0062CC">Comparatif</span>
                    <h2 class="text-3xl font-extrabold text-gray-900">Comparez tous les forfaits en détail</h2>
                </div>
                <div class="overflow-x-auto rounded-2xl shadow">
                    <table class="w-full min-w-[640px] border-collapse bg-white text-sm">
                        <thead>
                            <tr style="background:#001d3d">
                                <th class="p-4 text-left font-semibold text-white/60 w-48">Fonctionnalité</th>
                                <th v-for="plan in plans" :key="plan.code"
                                    class="p-4 text-center font-bold text-white"
                                    :class="plan.highlight ? 'text-yellow-300' : ''">
                                    {{ plan.name }}
                                    <div v-if="plan.highlight" class="text-[10px] font-normal text-yellow-300/70 mt-0.5">Le plus populaire</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- LIMITES -->
                            <tr class="bg-blue-50">
                                <td class="px-4 py-2 text-xs font-bold uppercase tracking-wide text-blue-600" :colspan="plans.length + 1">Limites</td>
                            </tr>
                            <tr v-for="(row, i) in limitRows" :key="row.key"
                                class="border-b border-gray-100 transition hover:bg-gray-50"
                                :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50'">
                                <td class="p-4 text-left text-gray-600 font-medium">{{ row.label }}</td>
                                <td v-for="plan in plans" :key="plan.code"
                                    class="p-4 text-center font-bold"
                                    :class="plan.highlight ? 'text-blue-700 bg-blue-50/30' : 'text-gray-800'">
                                    {{ val(plan.limits[row.key]) }}
                                </td>
                            </tr>

                            <!-- FONCTIONNALITÉS -->
                            <tr class="bg-blue-50">
                                <td class="px-4 py-2 text-xs font-bold uppercase tracking-wide text-blue-600" :colspan="plans.length + 1">Fonctionnalités incluses</td>
                            </tr>
                            <tr v-for="(row, i) in featureRows" :key="row.label"
                                class="border-b border-gray-100 transition hover:bg-gray-50"
                                :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50'">
                                <td class="p-4 text-left text-gray-600">{{ row.label }}</td>
                                <td v-for="plan in plans" :key="plan.code"
                                    class="p-4 text-center"
                                    :class="plan.highlight ? 'bg-blue-50/30' : ''">
                                    <span v-if="has(plan, row.needle)" class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold text-white" style="background:#0062CC">✓</span>
                                    <span v-else class="text-gray-200 text-lg">—</span>
                                </td>
                            </tr>

                            <!-- LIGNE PRIX -->
                            <tr style="background:#f8faff">
                                <td class="p-4 font-bold text-gray-700">Prix mensuel</td>
                                <td v-for="plan in plans" :key="plan.code"
                                    class="p-4 text-center font-extrabold text-blue-700">
                                    {{ new Intl.NumberFormat('fr-FR').format(plan.price_monthly) }} FCFA
                                </td>
                            </tr>
                            <!-- LIGNE CTA -->
                            <tr>
                                <td class="p-4"></td>
                                <td v-for="plan in plans" :key="plan.code" class="p-4 text-center">
                                    <a v-if="canRegister" href="/register"
                                       class="inline-block rounded-lg px-4 py-2 text-sm font-bold transition hover:scale-105"
                                       :style="plan.highlight ? 'background:#0062CC;color:#fff' : 'background:#eff6ff;color:#0062CC'">
                                        Essai gratuit →
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ══ GARANTIES ══ -->
        <section class="px-6 py-16 bg-white">
            <div class="mx-auto max-w-5xl">
                <h2 class="text-center text-2xl font-extrabold text-gray-900 mb-10">Toujours inclus, quel que soit votre forfait</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="g in [
                        { icon: '🔐', title: 'SSL & sécurité', text: 'Connexion sécurisée, données chiffrées, journalisation des accès.' },
                        { icon: '📱', title: 'PWA installable', text: 'Installez FactPro sur votre téléphone ou ordinateur en un clic.' },
                        { icon: '🌍', title: 'Multi-langues', text: 'Interface disponible en français et en anglais.' },
                        { icon: '🔄', title: 'Mises à jour incluses', text: 'Toutes les nouvelles fonctionnalités vous parviennent automatiquement.' },
                    ]" :key="g.title"
                        class="rounded-2xl border border-gray-100 bg-gray-50 p-5 text-center">
                        <div class="text-4xl mb-3">{{ g.icon }}</div>
                        <div class="font-bold text-gray-800 mb-1">{{ g.title }}</div>
                        <p class="text-xs text-gray-500 leading-relaxed">{{ g.text }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══ FAQ TARIFAIRE ══ -->
        <section class="px-6 py-20" style="background:#f8faff">
            <div class="mx-auto max-w-3xl">
                <div class="text-center mb-12">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest mb-3" style="background:#eff6ff;color:#0062CC">FAQ</span>
                    <h2 class="text-3xl font-extrabold text-gray-900">Questions sur les tarifs</h2>
                </div>
                <div class="space-y-3">
                    <div v-for="(faq, i) in faqs" :key="i"
                         class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 transition hover:ring-blue-200">
                        <button class="flex w-full items-center justify-between px-6 py-5 text-left font-semibold text-gray-900"
                                @click="toggleFaq(i)">
                            {{ faq.q }}
                            <span class="ml-4 flex-shrink-0 text-xl transition duration-200" :class="openFaq === i ? 'rotate-45' : ''" style="color:#0062CC">+</span>
                        </button>
                        <Transition name="faq-slide">
                            <div v-if="openFaq === i" class="px-6 pb-5 text-sm text-gray-600 leading-relaxed">{{ faq.a }}</div>
                        </Transition>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══ CALCULATEUR ROI ══ -->
        <section class="px-6 py-20" style="background:#002D5B">
            <div class="mx-auto max-w-5xl">
                <div class="text-center mb-12">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest mb-3" style="background:rgba(240,192,64,.2);color:#F0C040;border:1px solid rgba(240,192,64,.3)">
                        ROI Calculator
                    </span>
                    <h2 class="text-3xl font-extrabold text-white">Calculez votre retour sur investissement</h2>
                    <p class="mt-3 text-white/60 max-w-xl mx-auto text-sm">Ajustez les curseurs selon votre activité pour voir ce que FactPro vous fait réellement gagner.</p>
                </div>

                <div class="grid gap-10 lg:grid-cols-2 items-start">
                    <!-- Sliders -->
                    <div class="space-y-8 bg-white/5 rounded-2xl p-8 border border-white/10">
                        <!-- Factures -->
                        <div>
                            <div class="flex justify-between mb-2">
                                <label class="text-sm font-semibold text-white/80">Factures émises par mois</label>
                                <span class="text-sm font-bold" style="color:#F0C040">{{ roiFactures }}</span>
                            </div>
                            <input type="range" min="1" max="500" v-model.number="roiFactures"
                                   class="w-full h-2 rounded-full appearance-none cursor-pointer"
                                   style="accent-color:#F0C040;background:rgba(255,255,255,.15)" />
                            <div class="flex justify-between text-xs text-white/30 mt-1"><span>1</span><span>500</span></div>
                        </div>
                        <!-- Minutes -->
                        <div>
                            <div class="flex justify-between mb-2">
                                <label class="text-sm font-semibold text-white/80">Temps moyen par facture (sans logiciel)</label>
                                <span class="text-sm font-bold" style="color:#F0C040">{{ roiMinutes }} min</span>
                            </div>
                            <input type="range" min="5" max="60" v-model.number="roiMinutes"
                                   class="w-full h-2 rounded-full appearance-none cursor-pointer"
                                   style="accent-color:#F0C040;background:rgba(255,255,255,.15)" />
                            <div class="flex justify-between text-xs text-white/30 mt-1"><span>5 min</span><span>60 min</span></div>
                        </div>
                        <!-- Valeur horaire -->
                        <div>
                            <div class="flex justify-between mb-2">
                                <label class="text-sm font-semibold text-white/80">Valeur horaire de votre temps (FCFA)</label>
                                <span class="text-sm font-bold" style="color:#F0C040">{{ new Intl.NumberFormat('fr-FR').format(roiHoraire) }} FCFA</span>
                            </div>
                            <input type="range" min="1000" max="50000" step="500" v-model.number="roiHoraire"
                                   class="w-full h-2 rounded-full appearance-none cursor-pointer"
                                   style="accent-color:#F0C040;background:rgba(255,255,255,.15)" />
                            <div class="flex justify-between text-xs text-white/30 mt-1"><span>1 000</span><span>50 000</span></div>
                        </div>
                    </div>

                    <!-- Résultats -->
                    <div class="space-y-5">
                        <div class="rounded-2xl bg-white/5 border border-white/10 p-6 flex flex-col gap-1">
                            <p class="text-xs text-white/50 uppercase tracking-widest font-semibold">Temps perdu / mois</p>
                            <p class="text-4xl font-extrabold" style="color:#F0C040">{{ roiHeures }} h</p>
                            <p class="text-xs text-white/40">à traiter manuellement vos factures</p>
                        </div>
                        <div class="rounded-2xl bg-white/5 border border-white/10 p-6 flex flex-col gap-1">
                            <p class="text-xs text-white/50 uppercase tracking-widest font-semibold">Heures économisées / mois</p>
                            <p class="text-4xl font-extrabold" style="color:#F0C040">{{ Math.round(roiHeures * 0.75 * 10) / 10 }} h</p>
                            <p class="text-xs text-white/40">grâce aux 75 % de gain de temps FactPro</p>
                        </div>
                        <div class="rounded-2xl bg-white/5 border border-white/10 p-6 flex flex-col gap-1">
                            <p class="text-xs text-white/50 uppercase tracking-widest font-semibold">Économie / mois</p>
                            <p class="text-4xl font-extrabold" style="color:#F0C040">{{ new Intl.NumberFormat('fr-FR').format(roiEconomie) }} FCFA</p>
                            <p class="text-xs text-white/40">valeur du temps récupéré</p>
                        </div>
                        <div class="rounded-2xl p-6 flex flex-col gap-1" style="background:rgba(240,192,64,.15);border:1px solid rgba(240,192,64,.4)">
                            <p class="text-xs font-semibold uppercase tracking-widest" style="color:#F0C040">ROI sur votre abonnement Starter</p>
                            <p class="text-5xl font-extrabold" style="color:#F0C040">{{ new Intl.NumberFormat('fr-FR').format(roiPct) }} %</p>
                            <p class="text-xs text-white/50">pour {{ new Intl.NumberFormat('fr-FR').format(STARTER_PRICE) }} FCFA / mois</p>
                        </div>
                        <a v-if="canRegister" href="/register"
                           class="mt-2 block rounded-xl py-3.5 text-center text-sm font-extrabold tracking-wide transition hover:scale-105 active:scale-95 shadow-lg"
                           style="background:#F0C040;color:#001d3d">
                            Commencer maintenant →
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══ COMPARATEUR DÉTAILLÉ DES PLANS ══ -->
        <section class="px-6 py-20 bg-gray-50">
            <div class="mx-auto max-w-7xl">
                <div class="text-center mb-12">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest mb-3" style="background:#eff6ff;color:#0062CC">Comparateur</span>
                    <h2 class="text-3xl font-extrabold text-gray-900">Quel plan vous correspond ?</h2>
                    <p class="mt-3 text-gray-500 text-sm">Toutes les fonctionnalités, plan par plan, sans ambiguïté.</p>
                </div>
                <div class="overflow-x-auto rounded-2xl shadow-xl">
                    <table class="w-full min-w-[580px] border-collapse bg-white text-sm">
                        <thead>
                            <tr style="background:#001d3d">
                                <th class="p-4 text-left font-semibold text-white/50 w-48">Fonctionnalité</th>
                                <th v-for="(col, ci) in staticPlans" :key="col"
                                    class="p-4 text-center font-bold text-white"
                                    :class="ci === 2 ? 'border-l-2 border-r-2 border-yellow-400' : ''">
                                    {{ col }}
                                    <div v-if="ci === 2" class="text-[10px] font-normal mt-0.5" style="color:#F0C040">⭐ Populaire</div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, ri) in staticRows" :key="row.label"
                                class="border-b border-gray-100 transition hover:bg-blue-50/30"
                                :class="ri % 2 === 0 ? 'bg-white' : 'bg-gray-50/60'">
                                <td class="p-4 font-medium text-gray-700">{{ row.label }}</td>
                                <td v-for="(val2, ci) in row.values" :key="ci"
                                    class="p-4 text-center font-semibold"
                                    :class="[
                                        ci === 2 ? 'border-l-2 border-r-2 border-yellow-300/40 text-blue-700' : 'text-gray-700',
                                        val2 === '✅' ? 'text-green-600' : '',
                                        val2 === '❌' ? 'text-gray-200' : '',
                                    ]">
                                    {{ val2 }}
                                </td>
                            </tr>
                            <!-- Ligne CTA -->
                            <tr style="background:#f8faff">
                                <td class="p-4 text-gray-400 text-xs">Commencer →</td>
                                <td v-for="(col, ci) in staticPlans" :key="col" class="p-4 text-center"
                                    :class="ci === 2 ? 'border-l-2 border-r-2 border-yellow-300/40' : ''">
                                    <a v-if="canRegister" href="/register"
                                       class="inline-block rounded-lg px-4 py-2 text-xs font-bold transition hover:scale-105"
                                       :style="ci === 2
                                           ? 'background:#0062CC;color:#fff'
                                           : 'background:#eff6ff;color:#0062CC'">
                                        Essai gratuit →
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ══ FAQ PRICING ENRICHIE ══ -->
        <section class="px-6 py-20 bg-white">
            <div class="mx-auto max-w-3xl">
                <div class="text-center mb-12">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest mb-3" style="background:#eff6ff;color:#0062CC">FAQ Pricing</span>
                    <h2 class="text-3xl font-extrabold text-gray-900">Vos questions sur les abonnements</h2>
                </div>
                <div class="divide-y divide-gray-100 rounded-2xl border border-gray-100 overflow-hidden shadow-sm">
                    <div v-for="(faq, i) in enrichedFaqs" :key="i" class="bg-white">
                        <button class="flex w-full items-center justify-between px-6 py-5 text-left font-semibold text-gray-900 hover:bg-gray-50 transition"
                                @click="toggleEnrichedFaq(i)">
                            <span>{{ faq.q }}</span>
                            <span class="ml-4 flex-shrink-0 h-6 w-6 rounded-full flex items-center justify-center text-sm font-extrabold transition-transform duration-200"
                                  :class="openEnrichedFaq === i ? 'rotate-45' : ''"
                                  style="background:#F0C040;color:#001d3d">+</span>
                        </button>
                        <div v-show="openEnrichedFaq === i"
                             class="px-6 pb-5 text-sm text-gray-600 leading-relaxed border-t border-gray-100">
                            {{ faq.a }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══ CTA FINAL ══ -->
        <section class="relative overflow-hidden px-6 py-20 text-center" style="background:linear-gradient(135deg,#001d3d,#0062CC)">
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -right-20 top-0 h-64 w-64 rounded-full opacity-20" style="background:radial-gradient(circle,#F0C040,transparent)"></div>
                <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full opacity-10" style="background:radial-gradient(circle,#ffffff,transparent)"></div>
            </div>
            <div class="relative">
                <h2 class="text-3xl font-extrabold text-white sm:text-4xl">Commencez dès aujourd'hui</h2>
                <p class="mx-auto mt-4 max-w-xl text-lg text-white/80">
                    7 jours d'essai gratuit, sans carte bancaire, sans engagement.
                </p>
                <div class="mt-10 flex flex-wrap justify-center gap-4">
                    <a v-if="canRegister" href="/register"
                       class="rounded-xl px-10 py-4 text-base font-bold shadow-xl transition hover:scale-105 active:scale-95"
                       style="background:#F0C040;color:#001d3d">
                        Démarrer gratuitement →
                    </a>
                    <a href="/"
                       class="rounded-xl border px-10 py-4 text-base font-semibold text-white transition hover:bg-white/10"
                       style="border-color:rgba(255,255,255,.3)">
                        Retour à l'accueil
                    </a>
                </div>
                <div class="mt-8 flex flex-wrap justify-center gap-6 text-sm text-white/60">
                    <span>✓ Sans carte bancaire</span>
                    <span>✓ Résiliable à tout moment</span>
                    <span>✓ 7 jours complets</span>
                    <span>✓ Support inclus</span>
                </div>
            </div>
        </section>

        <PublicFooter />
    </div>
    <Analytics />
</template>

<style scoped>
.faq-slide-enter-active,.faq-slide-leave-active{transition:all .25s ease}
.faq-slide-enter-from,.faq-slide-leave-to{opacity:0;transform:translateY(-8px)}
</style>

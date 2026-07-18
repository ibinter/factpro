<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';

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

/* Lignes du tableau comparatif (cahier §22.1) */
const limitRows = [
    { label: 'Documents / mois', key: 'documents_per_month' },
    { label: 'Utilisateurs', key: 'users' },
    { label: 'Sociétés', key: 'companies' },
    { label: 'Clients', key: 'customers' },
    { label: 'Produits', key: 'products' },
    { label: 'Modèles de documents', key: 'templates' },
    { label: 'Stockage (Mo)', key: 'storage_mb' },
];

const featureRows = [
    { label: 'QR anti-falsification', needle: 'qr' },
    { label: 'Portail client', needle: 'portail' },
    { label: 'Multi-devises', needle: 'devises' },
    { label: 'Factures récurrentes', needle: 'récurrentes' },
    { label: 'POS / Ticket de caisse', needle: 'caisse' },
    { label: 'Impression thermique 58/80mm', needle: 'thermique' },
    { label: 'Gestion des stocks', needle: 'stocks' },
    { label: 'Comptabilité + FEC', needle: 'fec' },
    { label: 'API REST', needle: 'api' },
    { label: 'White-Label', needle: 'white-label' },
];

const faqs = [
    { q: 'Les prix sont-ils en FCFA ?', a: 'Oui, tous nos forfaits sont facturés en FCFA (XOF). Les équivalents en euros et dollars sont donnés à titre indicatif au taux en vigueur.' },
    { q: 'Comment fonctionne la remise annuelle ?', a: 'En choisissant la facturation annuelle, vous bénéficiez de 20 % de réduction, soit environ deux mois offerts par an.' },
    { q: 'Puis-je changer de forfait ?', a: 'Oui, vous pouvez passer à un forfait supérieur ou inférieur à tout moment depuis votre espace abonnement.' },
    { q: 'Quels moyens de paiement acceptez-vous ?', a: 'Mobile Money (Orange Money, Wave, MTN, Moov), virement bancaire national et international.' },
];
const openFaq = ref(null);
const toggleFaq = (i) => (openFaq.value = openFaq.value === i ? null : i);
</script>

<template>
    <Head title="Tarifs — IBIG FactPro" />

    <div class="min-h-screen bg-white text-gray-800">
        <PublicNav :can-login="canLogin" :can-register="canRegister" />

        <!-- En-tête -->
        <section class="bg-gradient-to-b from-brand-950 to-brand-700 px-6 py-16 text-center text-white">
            <h1 class="text-4xl font-extrabold">Des tarifs clairs, sans surprise</h1>
            <p class="mx-auto mt-4 max-w-2xl text-white/80">
                Tous les forfaits incluent l'essai gratuit de 7 jours et le QR anti-falsification. Résiliez à tout moment.
            </p>
            <div class="mt-8 inline-flex items-center gap-1 rounded-full bg-white/10 p-1">
                <button
                    class="rounded-full px-5 py-2 text-sm font-semibold transition"
                    :class="billing === 'monthly' ? 'bg-white text-brand-900' : 'text-white/80'"
                    @click="billing = 'monthly'"
                >Mensuel</button>
                <button
                    class="rounded-full px-5 py-2 text-sm font-semibold transition"
                    :class="billing === 'yearly' ? 'bg-white text-brand-900' : 'text-white/80'"
                    @click="billing = 'yearly'"
                >Annuel <span class="text-gold-500">-20%</span></button>
            </div>
        </section>

        <!-- Cartes de prix -->
        <section class="mx-auto -mt-10 max-w-7xl px-6">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="plan in plans"
                    :key="plan.code"
                    class="relative flex flex-col rounded-2xl bg-white p-6 shadow-lg"
                    :class="plan.highlight ? 'ring-2 ring-gold-400' : 'ring-1 ring-gray-100'"
                >
                    <span
                        v-if="plan.highlight"
                        class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-gold-400 px-3 py-1 text-xs font-bold text-brand-900"
                    >Populaire</span>
                    <div class="text-sm font-bold uppercase tracking-wide text-brand-600">{{ plan.name }}</div>
                    <p class="mt-1 h-10 text-xs text-gray-500">{{ plan.short_description }}</p>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-brand-900">
                            {{ billing === 'monthly' ? fmt(plan.price_monthly) : fmt(plan.price_yearly / 12) }}
                        </span>
                        <span class="text-sm text-gray-400"> FCFA / mois</span>
                    </div>
                    <div class="mt-1 text-xs text-gray-400">
                        ≈ {{ fmt(plan.eur) }} € · {{ fmt(plan.usd) }} $
                        <template v-if="billing === 'yearly'"> · {{ fmt(plan.price_yearly) }} FCFA / an</template>
                    </div>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="mt-6 rounded-lg px-4 py-2.5 text-center text-sm font-bold transition"
                        :class="plan.highlight ? 'bg-brand-600 text-white hover:bg-brand-700' : 'bg-brand-50 text-brand-700 hover:bg-brand-100'"
                    >Démarrer l'essai gratuit</Link>
                </div>
            </div>
        </section>

        <!-- Tableau comparatif -->
        <section class="mx-auto max-w-7xl px-6 py-20">
            <h2 class="text-center text-3xl font-extrabold text-brand-900">Comparatif détaillé</h2>
            <div class="mt-10 overflow-x-auto">
                <table class="w-full min-w-[720px] border-collapse text-sm">
                    <thead>
                        <tr>
                            <th class="p-4 text-left font-semibold text-gray-500">Fonctionnalité</th>
                            <th
                                v-for="plan in plans"
                                :key="plan.code"
                                class="p-4 text-center font-bold"
                                :class="plan.highlight ? 'text-brand-700' : 'text-brand-900'"
                            >{{ plan.name }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-gray-50">
                            <td class="p-3 text-left text-xs font-bold uppercase tracking-wide text-gray-400" :colspan="plans.length + 1">Limites</td>
                        </tr>
                        <tr v-for="row in limitRows" :key="row.key" class="border-b border-gray-100">
                            <td class="p-4 text-left text-gray-600">{{ row.label }}</td>
                            <td v-for="plan in plans" :key="plan.code" class="p-4 text-center font-semibold text-brand-900">
                                {{ val(plan.limits[row.key]) }}
                            </td>
                        </tr>
                        <tr class="bg-gray-50">
                            <td class="p-3 text-left text-xs font-bold uppercase tracking-wide text-gray-400" :colspan="plans.length + 1">Fonctionnalités</td>
                        </tr>
                        <tr v-for="row in featureRows" :key="row.label" class="border-b border-gray-100">
                            <td class="p-4 text-left text-gray-600">{{ row.label }}</td>
                            <td v-for="plan in plans" :key="plan.code" class="p-4 text-center">
                                <span v-if="has(plan, row.needle)" class="font-bold text-brand-600">✓</span>
                                <span v-else class="text-gray-300">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- FAQ tarifaire -->
        <section class="bg-gray-50 px-6 py-20">
            <div class="mx-auto max-w-3xl">
                <h2 class="text-center text-3xl font-extrabold text-brand-900">Questions sur les tarifs</h2>
                <div class="mt-10 space-y-3">
                    <div v-for="(faq, i) in faqs" :key="i" class="overflow-hidden rounded-xl bg-white ring-1 ring-gray-100">
                        <button class="flex w-full items-center justify-between px-5 py-4 text-left font-semibold text-brand-900" @click="toggleFaq(i)">
                            {{ faq.q }}
                            <span class="text-brand-600 transition" :class="openFaq === i ? 'rotate-45' : ''">+</span>
                        </button>
                        <div v-show="openFaq === i" class="px-5 pb-5 text-sm text-gray-600">{{ faq.a }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="bg-gradient-to-r from-brand-900 to-brand-600 px-6 py-16 text-center text-white">
            <h2 class="text-3xl font-extrabold">Commencez dès aujourd'hui</h2>
            <p class="mx-auto mt-3 max-w-xl text-white/80">7 jours d'essai gratuit, sans carte bancaire.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="rounded-lg bg-gold-400 px-8 py-3 font-bold text-brand-900 shadow-lg transition hover:bg-gold-300"
                >Démarrer gratuitement</Link>
                <Link
                    :href="route('home')"
                    class="rounded-lg border border-white/30 px-8 py-3 font-semibold text-white transition hover:bg-white/10"
                >Retour à l'accueil</Link>
            </div>
        </section>

        <PublicFooter />
    </div>
</template>

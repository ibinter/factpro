<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';

defineProps({
    canLogin: { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

/* --- Forfaits chargés dynamiquement depuis /pricing-data --- */
const plans = ref([]);
const loadingPlans = ref(true);
const billing = ref('monthly'); // 'monthly' | 'yearly'

const fmt = (n) => new Intl.NumberFormat('fr-FR').format(Math.round(Number(n) || 0));

onMounted(async () => {
    try {
        const { data } = await window.axios.get('/pricing-data');
        plans.value = data.plans ?? [];
    } catch (e) {
        plans.value = [];
    } finally {
        loadingPlans.value = false;
    }
});

/* --- Contenu statique --- */
const features = [
    { icon: '🔒', title: 'QR anti-falsification', text: 'Chaque document est scellé (SHA-256) et vérifiable en ligne par QR code. Infalsifiable.' },
    { icon: '🖨️', title: 'Impression thermique 58/80 mm', text: 'Tickets de caisse et reçus imprimés directement sur imprimante thermique.' },
    { icon: '🛒', title: 'POS / Caisse', text: 'Encaissez en boutique, gérez la caisse et éditez le ticket en un clic.' },
    { icon: '🌍', title: 'Multi-devises 160+', text: 'Facturez dans le monde entier avec conversion automatique et parités à jour.' },
    { icon: '👥', title: 'Portail client', text: 'Vos clients consultent, téléchargent et paient leurs factures en ligne.' },
    { icon: '🔔', title: 'Relances automatiques', text: 'Relances email, SMS et WhatsApp programmées pour être payé plus vite.' },
    { icon: '📊', title: 'Comptabilité & FEC', text: 'Comptabilité simplifiée conforme OHADA et export du Fichier des Écritures Comptables.' },
    { icon: '🔌', title: 'API REST', text: 'Connectez FactPro à vos outils métier grâce à une API REST complète.' },
];

const trustBadges = ['OHADA', 'Factur-X', 'Mobile Money', 'QR anti-falsification'];

const why = [
    { icon: '🌍', title: 'Afrique-first', text: 'Prix en FCFA, TVA OHADA, Mobile Money (Orange, MTN, Wave, Moov) : conçu pour votre réalité.' },
    { icon: '🛡️', title: 'Infalsifiable', text: 'Hash cryptographique, QR de vérification publique et archivage immuable 10 ans.' },
    { icon: '🧰', title: 'Tout-en-un', text: 'Devis, factures, POS, stocks, projets, comptabilité : un seul outil pour toute la gestion.' },
    { icon: '🚀', title: 'Prêt en 2 minutes', text: '7 jours d\'essai gratuit, sans carte bancaire. Le filigrane disparaît dès l\'abonnement.' },
];

const faqs = [
    { q: 'L\'essai est-il vraiment gratuit ?', a: 'Oui. 7 jours d\'essai complets, sans carte bancaire. Aucun prélèvement automatique à la fin.' },
    { q: 'Qu\'est-ce que le filigrane d\'essai ?', a: 'Pendant l\'essai, vos documents portent la mention « VERSION ESSAI ». Elle disparaît instantanément dès votre premier abonnement.' },
    { q: 'Quels moyens de paiement acceptez-vous ?', a: 'Mobile Money (Orange Money, Wave, MTN, Moov), virement bancaire national et international. Activation rapide après vérification.' },
    { q: 'Puis-je résilier à tout moment ?', a: 'Oui. Aucun engagement : vos abonnements sont mensuels ou annuels et se résilient librement depuis votre espace.' },
];

const openFaq = ref(null);
const toggleFaq = (i) => (openFaq.value = openFaq.value === i ? null : i);

/* Limites clés affichées sur chaque carte de prix */
const limitLabel = (v) => (v === 'unlimited' || v == null ? 'Illimité' : fmt(v));
</script>

<template>
    <Head title="IBIG FactPro — Facturation professionnelle pour l'Afrique et le monde" />

    <div class="min-h-screen bg-white text-gray-800">
        <PublicNav :can-login="canLogin" :can-register="canRegister" />

        <!-- HERO -->
        <section class="relative overflow-hidden bg-gradient-to-b from-brand-950 to-brand-700 text-white">
            <div class="mx-auto grid max-w-7xl items-center gap-12 px-6 py-20 lg:grid-cols-2 lg:py-28">
                <div>
                    <span class="inline-block rounded-full bg-white/10 px-4 py-1 text-xs font-semibold text-gold-400">
                        7 jours d'essai gratuit · sans carte bancaire
                    </span>
                    <h1 class="mt-5 text-4xl font-extrabold leading-tight sm:text-5xl">
                        Les outils de facturation des multinationales,
                        <span class="text-gold-400">pour chaque entrepreneur</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg text-white/80">
                        Devis, factures et documents commerciaux professionnels avec QR anti-falsification,
                        impression thermique, Mobile Money et multi-devises. Du vendeur de rue au directeur de PME.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-lg bg-gold-400 px-8 py-3 text-base font-bold text-brand-900 shadow-lg transition hover:bg-gold-300"
                        >
                            Démarrer l'essai gratuit 7 jours
                        </Link>
                        <Link
                            :href="route('public.pricing')"
                            class="rounded-lg border border-white/30 px-8 py-3 text-base font-semibold text-white transition hover:bg-white/10"
                        >
                            Voir les tarifs
                        </Link>
                    </div>
                    <p class="mt-4 text-xs text-white/50">Sans carte bancaire · Résiliable à tout moment</p>
                </div>

                <!-- Mockup stylisé CSS -->
                <div class="relative hidden lg:block">
                    <div class="rotate-2 rounded-2xl bg-white p-5 shadow-2xl">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-lg bg-brand-600"></div>
                                <div>
                                    <div class="h-2 w-24 rounded bg-gray-800"></div>
                                    <div class="mt-1 h-2 w-16 rounded bg-gray-300"></div>
                                </div>
                            </div>
                            <div class="rounded bg-brand-50 px-2 py-1 text-[10px] font-bold text-brand-700">FACTURE</div>
                        </div>
                        <div class="space-y-2 py-4">
                            <div class="flex justify-between"><div class="h-2 w-32 rounded bg-gray-200"></div><div class="h-2 w-12 rounded bg-gray-200"></div></div>
                            <div class="flex justify-between"><div class="h-2 w-40 rounded bg-gray-200"></div><div class="h-2 w-12 rounded bg-gray-200"></div></div>
                            <div class="flex justify-between"><div class="h-2 w-28 rounded bg-gray-200"></div><div class="h-2 w-12 rounded bg-gray-200"></div></div>
                        </div>
                        <div class="flex items-end justify-between border-t border-gray-100 pt-3">
                            <div class="grid h-14 w-14 place-items-center rounded bg-brand-950 text-[8px] text-white">QR</div>
                            <div class="text-right">
                                <div class="text-[10px] text-gray-400">Total TTC</div>
                                <div class="text-lg font-extrabold text-brand-900">1 250 000 FCFA</div>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -bottom-5 -left-5 -rotate-3 rounded-xl bg-gold-400 px-4 py-3 text-sm font-bold text-brand-900 shadow-xl">
                        ✓ Authentifié · QR vérifié
                    </div>
                </div>
            </div>

            <!-- Bandeau confiance -->
            <div class="border-t border-white/10 bg-brand-950/50">
                <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-8 gap-y-2 px-6 py-4 text-sm font-semibold text-white/70">
                    <span v-for="badge in trustBadges" :key="badge" class="flex items-center gap-2">
                        <span class="text-gold-400">◆</span> {{ badge }}
                    </span>
                </div>
            </div>
        </section>

        <!-- FONCTIONNALITÉS -->
        <section id="fonctionnalites" class="mx-auto max-w-7xl px-6 py-20">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-brand-900">Tout ce qu'il faut pour facturer comme un pro</h2>
                <p class="mx-auto mt-3 max-w-2xl text-gray-500">Une suite complète pensée pour les entrepreneurs et PME d'Afrique et du monde.</p>
            </div>
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="feature in features"
                    :key="feature.title"
                    class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md"
                >
                    <div class="grid h-12 w-12 place-items-center rounded-xl bg-brand-50 text-2xl">{{ feature.icon }}</div>
                    <h3 class="mt-4 font-bold text-brand-900">{{ feature.title }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ feature.text }}</p>
                </div>
            </div>
        </section>

        <!-- TARIFS -->
        <section id="tarifs" class="bg-gray-50 px-6 py-20">
            <div class="mx-auto max-w-7xl">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold text-brand-900">Des tarifs simples, en FCFA</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-gray-500">Choisissez le forfait adapté à votre activité. Changez ou résiliez quand vous voulez.</p>

                    <!-- Toggle mensuel / annuel -->
                    <div class="mt-8 inline-flex items-center gap-1 rounded-full bg-white p-1 shadow-sm ring-1 ring-gray-100">
                        <button
                            class="rounded-full px-5 py-2 text-sm font-semibold transition"
                            :class="billing === 'monthly' ? 'bg-brand-600 text-white' : 'text-gray-500'"
                            @click="billing = 'monthly'"
                        >Mensuel</button>
                        <button
                            class="rounded-full px-5 py-2 text-sm font-semibold transition"
                            :class="billing === 'yearly' ? 'bg-brand-600 text-white' : 'text-gray-500'"
                            @click="billing = 'yearly'"
                        >Annuel <span class="text-gold-500">-20%</span></button>
                    </div>
                </div>

                <div v-if="loadingPlans" class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="i in 4" :key="i" class="h-96 animate-pulse rounded-2xl bg-white shadow-sm"></div>
                </div>

                <div v-else class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="plan in plans"
                        :key="plan.code"
                        class="relative flex flex-col rounded-2xl bg-white p-6 shadow-sm"
                        :class="plan.highlight ? 'ring-2 ring-gold-400 lg:-translate-y-2' : 'ring-1 ring-gray-100'"
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

                        <ul class="mt-5 flex-1 space-y-2 border-t border-gray-100 pt-5 text-sm text-gray-600">
                            <li class="flex items-center gap-2"><span class="text-brand-600">✓</span> {{ limitLabel(plan.limits.documents_per_month) }} documents / mois</li>
                            <li class="flex items-center gap-2"><span class="text-brand-600">✓</span> {{ limitLabel(plan.limits.users) }} utilisateur(s)</li>
                            <li class="flex items-center gap-2"><span class="text-brand-600">✓</span> {{ limitLabel(plan.limits.companies) }} société(s)</li>
                            <li v-for="feat in (plan.features || []).slice(0, 3)" :key="feat" class="flex items-center gap-2">
                                <span class="text-brand-600">✓</span> {{ feat }}
                            </li>
                        </ul>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="mt-6 rounded-lg px-4 py-2.5 text-center text-sm font-bold transition"
                            :class="plan.highlight ? 'bg-brand-600 text-white hover:bg-brand-700' : 'bg-brand-50 text-brand-700 hover:bg-brand-100'"
                        >Commencer</Link>
                    </div>
                </div>

                <div class="mt-10 text-center">
                    <Link :href="route('public.pricing')" class="text-sm font-semibold text-brand-600 hover:text-brand-700">
                        Comparer tous les forfaits en détail →
                    </Link>
                </div>
            </div>
        </section>

        <!-- POURQUOI -->
        <section class="mx-auto max-w-7xl px-6 py-20">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-brand-900">Pourquoi IBIG FactPro</h2>
            </div>
            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="item in why" :key="item.title" class="rounded-2xl bg-gradient-to-b from-brand-50 to-white p-6 ring-1 ring-brand-50">
                    <div class="text-3xl">{{ item.icon }}</div>
                    <h3 class="mt-3 font-bold text-brand-900">{{ item.title }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ item.text }}</p>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section id="faq" class="bg-gray-50 px-6 py-20">
            <div class="mx-auto max-w-3xl">
                <h2 class="text-center text-3xl font-extrabold text-brand-900">Questions fréquentes</h2>
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

        <!-- CTA final -->
        <section class="bg-gradient-to-r from-brand-900 to-brand-600 px-6 py-16 text-center text-white">
            <h2 class="text-3xl font-extrabold">Prêt à facturer comme les grands ?</h2>
            <p class="mx-auto mt-3 max-w-xl text-white/80">Lancez votre essai gratuit de 7 jours. Sans carte bancaire, sans engagement.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="rounded-lg bg-gold-400 px-8 py-3 font-bold text-brand-900 shadow-lg transition hover:bg-gold-300"
                >Démarrer gratuitement</Link>
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="rounded-lg border border-white/30 px-8 py-3 font-semibold text-white transition hover:bg-white/10"
                >J'ai déjà un compte</Link>
            </div>
        </section>

        <PublicFooter />
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';
import Analytics from '@/Components/Analytics.vue';

defineProps({
    canLogin:    { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const products = [
    { icon: '🧾', name: 'FactPro',    desc: 'Facturation & Gestion commerciale', available: true,  link: '/register' },
    { icon: '📦', name: 'StockPro',   desc: 'Gestion de stocks avancée',         available: false, link: null },
    { icon: '👥', name: 'RHPro',      desc: 'RH & Paie OHADA',                   available: false, link: null },
    { icon: '🏪', name: 'CaissePro',  desc: 'Logiciel de caisse POS',            available: false, link: null },
    { icon: '📊', name: 'ComptaPro',  desc: 'Comptabilité & Bilan',              available: false, link: null },
    { icon: '🤝', name: 'CRMPro',     desc: 'CRM & Gestion clients',             available: false, link: null },
];

const team = [
    { initials: 'KI', color: '#001d3d', name: 'Koné Ibrahim',      role: 'Fondateur & CEO',         bio: 'Expert en systèmes d\'information, 10 ans d\'expérience' },
    { initials: 'TA', color: '#7c3aed', name: 'Traoré Aminata',    role: 'Directrice Produit',      bio: 'Ex-fintech, spécialiste UX pour marchés émergents' },
    { initials: 'DM', color: '#0891b2', name: 'Diallo Moussa',     role: 'Lead Developer',          bio: 'Full-stack Laravel/Vue, architecte solutions cloud' },
];
</script>

<template>
    <Head title="À propos d'IBIG Soft — FactPro">
        <meta name="description" content="IBIG Soft SARL — Éditeur ivoirien de logiciels de gestion pour PME africaines. Fondé à Abidjan, présent dans 18 pays.">
        <meta property="og:title" content="À propos d'IBIG Soft — L'innovation au service des entrepreneurs africains">
    </Head>

    <PublicNav :can-login="canLogin" :can-register="canRegister" />

    <!-- Hero -->
    <section style="background:linear-gradient(135deg,#001d3d 0%,#002D5B 100%)" class="py-24 px-6 text-center text-white">
        <div class="mx-auto max-w-4xl">
            <span class="inline-block mb-6 rounded-full px-4 py-1.5 text-xs font-semibold" style="background:rgba(240,192,64,.15);color:#F0C040;border:1px solid rgba(240,192,64,.3)">
                🇨🇮 Fondé à Abidjan, Côte d'Ivoire · 2023
            </span>
            <h1 class="text-4xl md:text-5xl font-extrabold mb-6 leading-tight">
                IBIG Soft — L'innovation au service<br class="hidden md:block" /> des entrepreneurs africains
            </h1>
            <p class="text-lg md:text-xl text-white/75 max-w-2xl mx-auto">
                Nous construisons des logiciels de gestion simples, puissants et adaptés aux réalités de l'Afrique
            </p>
        </div>
    </section>

    <!-- Mission / Vision / Valeurs -->
    <section class="bg-white py-20 px-6">
        <div class="mx-auto max-w-6xl">
            <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-14">Qui sommes-nous ?</h2>
            <div class="grid gap-8 md:grid-cols-3">
                <div class="rounded-2xl border border-blue-100 bg-blue-50 p-8">
                    <div class="text-4xl mb-4">🎯</div>
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Notre Mission</h3>
                    <p class="text-gray-600 leading-relaxed">Démocratiser l'accès aux outils de gestion professionnels pour les PME africaines</p>
                </div>
                <div class="rounded-2xl border border-purple-100 bg-purple-50 p-8">
                    <div class="text-4xl mb-4">👁</div>
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Notre Vision</h3>
                    <p class="text-gray-600 leading-relaxed">Devenir le logiciel de gestion de référence en Afrique francophone d'ici 2028</p>
                </div>
                <div class="rounded-2xl border border-yellow-100 bg-yellow-50 p-8">
                    <div class="text-4xl mb-4">💎</div>
                    <h3 class="text-lg font-bold text-gray-900 mb-3">Nos Valeurs</h3>
                    <p class="text-gray-600 leading-relaxed">Simplicité · Fiabilité · Innovation · Proximité client</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Chiffres clés -->
    <section style="background:#F0C040" class="py-16 px-6">
        <div class="mx-auto max-w-6xl">
            <h2 class="text-2xl font-extrabold text-center mb-12" style="color:#001d3d">Nos chiffres clés</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-5xl font-extrabold" style="color:#001d3d">500+</div>
                    <div class="mt-2 text-sm font-semibold" style="color:#002D5B">Entreprises clientes</div>
                </div>
                <div>
                    <div class="text-5xl font-extrabold" style="color:#001d3d">18</div>
                    <div class="mt-2 text-sm font-semibold" style="color:#002D5B">Pays couverts</div>
                </div>
                <div>
                    <div class="text-5xl font-extrabold" style="color:#001d3d">100+</div>
                    <div class="mt-2 text-sm font-semibold" style="color:#002D5B">Templates PDF</div>
                </div>
                <div>
                    <div class="text-5xl font-extrabold" style="color:#001d3d">99.97%</div>
                    <div class="mt-2 text-sm font-semibold" style="color:#002D5B">Disponibilité</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Nos produits -->
    <section class="bg-gray-50 py-20 px-6">
        <div class="mx-auto max-w-6xl">
            <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-4">Nos produits IBIG Soft</h2>
            <p class="text-center text-gray-500 mb-14">Une suite complète de logiciels pour les entrepreneurs africains</p>
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="product in products"
                    :key="product.name"
                    class="rounded-2xl bg-white border border-gray-100 p-6 shadow-sm transition hover:shadow-md"
                >
                    <div class="flex items-start justify-between mb-4">
                        <span class="text-3xl">{{ product.icon }}</span>
                        <span
                            v-if="product.available"
                            class="rounded-full px-2.5 py-1 text-xs font-semibold"
                            style="background:#dcfce7;color:#166534"
                        >Disponible</span>
                        <span
                            v-else
                            class="rounded-full px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-500"
                        >Bientôt</span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">{{ product.name }}</h3>
                    <p class="text-sm text-gray-500 mb-4">{{ product.desc }}</p>
                    <a
                        v-if="product.available && product.link"
                        :href="product.link"
                        class="inline-block rounded-lg px-4 py-2 text-sm font-semibold transition hover:opacity-90"
                        style="background:#001d3d;color:white"
                    >Commencer gratuitement →</a>
                    <span v-else class="inline-block text-xs text-gray-400">Disponible bientôt</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Équipe -->
    <section class="bg-white py-20 px-6">
        <div class="mx-auto max-w-5xl">
            <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-4">Une équipe passionnée</h2>
            <p class="text-center text-gray-500 mb-14">Des experts dédiés à la réussite des entrepreneurs africains</p>
            <div class="grid gap-8 md:grid-cols-3">
                <div
                    v-for="member in team"
                    :key="member.name"
                    class="text-center"
                >
                    <div
                        class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full text-2xl font-extrabold text-white"
                        :style="{ background: member.color }"
                    >{{ member.initials }}</div>
                    <h3 class="text-lg font-bold text-gray-900">{{ member.name }}</h3>
                    <div class="text-sm font-semibold mb-2" style="color:#F0C040">{{ member.role }}</div>
                    <p class="text-sm text-gray-500">{{ member.bio }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA final -->
    <section style="background:#001d3d" class="py-20 px-6 text-center text-white">
        <div class="mx-auto max-w-2xl">
            <h2 class="text-3xl font-extrabold mb-4">
                Rejoignez des centaines d'entreprises qui font confiance à IBIG FactPro
            </h2>
            <p class="text-white/65 mb-8">Commencez gratuitement pendant 7 jours. Aucune carte bancaire requise.</p>
            <a
                href="/register"
                class="inline-block rounded-xl px-8 py-3.5 text-base font-bold transition hover:scale-105"
                style="background:#F0C040;color:#001d3d"
            >Créer mon compte gratuitement →</a>
        </div>
    </section>

    <PublicFooter />
    <Analytics />
</template>

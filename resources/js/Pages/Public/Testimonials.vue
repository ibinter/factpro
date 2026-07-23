<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';
import Analytics from '@/Components/Analytics.vue';

defineProps({
    canLogin:    { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const featuredTestimonials = [
    {
        initials: 'AK',
        color: '#0062CC',
        name: 'Amara Koné',
        role: 'Directeur Général',
        company: 'Koné & Associés SARL',
        location: 'Abidjan, Côte d\'Ivoire',
        sector: 'Cabinet comptable',
        rating: 5,
        quote: 'Avant FactPro, nous passions 2 heures par jour à gérer la facturation de nos clients. Maintenant c\'est 15 minutes. Le module multi-sociétés est parfait pour notre cabinet qui gère 23 dossiers clients. La conformité OHADA automatique nous a évité plusieurs erreurs fiscales.',
    },
    {
        initials: 'FD',
        color: '#7c3aed',
        name: 'Fatou Diallo',
        role: 'Fondatrice',
        company: 'DigiShop Boutique',
        location: 'Dakar, Sénégal',
        sector: 'Commerce de détail',
        rating: 5,
        quote: 'La caisse POS de FactPro a transformé mon magasin. Mes caissières ont été formées en 30 minutes. Les rapports journaliers m\'arrivent directement sur WhatsApp. J\'ai économisé 180 000 FCFA par mois en ne payant plus le logiciel de caisse que j\'utilisais avant.',
    },
    {
        initials: 'JN',
        color: '#0891b2',
        name: 'Jean-Paul Nguema',
        role: 'DAF',
        company: 'Groupe Nguema BTP',
        location: 'Douala, Cameroun',
        sector: 'BTP / Construction',
        rating: 5,
        quote: 'La gestion de nos devis et situations de travaux est devenue un jeu d\'enfant. Le module de suivi des paiements nous a permis de réduire nos impayés de 40%. Le support client d\'IBIG répond en moins d\'1 heure, même le week-end.',
    },
];

const shortTestimonials = [
    { initials: 'IT', color: '#059669', name: 'Ibrahim Traoré', role: 'Gérant', company: 'Pharmacie Centrale', location: 'Ouagadougou', sector: 'Pharmacie', rating: 5, quote: 'Gestion des stocks médicaments parfaite. Alertes de rupture automatiques.' },
    { initials: 'MA', color: '#db2777', name: 'Marie-Claire Akossi', role: 'CEO', company: 'MC Events', location: 'Abidjan', sector: 'Événementiel', rating: 5, quote: 'Devis signés en ligne par mes clients. Plus besoin d\'imprimer !' },
    { initials: 'MB', color: '#d97706', name: 'Moussa Ba', role: 'Propriétaire', company: 'Moussa Auto', location: 'Saint-Louis', sector: 'Automobile', rating: 5, quote: 'Le POS gère parfaitement les pièces détachées. Inventaire en temps réel.' },
    { initials: 'AC', color: '#7c3aed', name: 'Aïcha Coulibaly', role: 'DG', company: 'Groupe Scolaire Excellence', location: 'Bamako', sector: 'Éducation', rating: 5, quote: 'Facturation des frais de scolarité automatisée. Les parents paient en ligne.' },
    { initials: 'RA', color: '#0062CC', name: 'Roger Attiogbé', role: 'Expert-comptable', company: 'Cabinet Attiogbé', location: 'Lomé', sector: 'Comptabilité', rating: 5, quote: 'Migration en 2h depuis notre ancien logiciel. L\'import CSV est impeccable.' },
    { initials: 'NB', color: '#0891b2', name: 'Nadia Benali', role: 'Directrice', company: 'Clinique Al Shifa', location: 'Casablanca', sector: 'Santé', rating: 5, quote: 'La version arabe est parfaite. Facturation en MAD avec TVA marocaine correcte.' },
];
</script>

<template>
    <Head title="Témoignages & Cas Clients — IBIG FactPro">
        <meta name="description" content="500+ entreprises à travers 18 pays font confiance à IBIG FactPro. Découvrez leurs témoignages et cas clients.">
        <meta property="og:title" content="Témoignages clients — IBIG FactPro, la facturation pour l'Afrique">
    </Head>

    <PublicNav :can-login="canLogin" :can-register="canRegister" />

    <!-- Hero -->
    <section style="background:linear-gradient(135deg,#001d3d 0%,#002D5B 100%)" class="py-24 px-6 text-center text-white">
        <div class="mx-auto max-w-4xl">
            <span class="inline-block mb-6 rounded-full px-4 py-1.5 text-xs font-semibold" style="background:rgba(240,192,64,.15);color:#F0C040;border:1px solid rgba(240,192,64,.3)">
                ⭐ 4.8/5 sur 312 avis vérifiés
            </span>
            <h1 class="text-4xl md:text-5xl font-extrabold mb-6 leading-tight">
                Ils nous font confiance
            </h1>
            <p class="text-lg md:text-xl text-white/75 max-w-2xl mx-auto mb-10">
                500+ entreprises à travers 18 pays utilisent IBIG FactPro pour gérer leur facturation au quotidien.
            </p>
            <!-- Stats band -->
            <div class="inline-flex flex-wrap justify-center gap-x-6 gap-y-2 rounded-2xl px-8 py-4 text-sm font-bold" style="background:#F0C040;color:#001d3d">
                <span>4.8/5 ★ sur 312 avis</span>
                <span class="hidden sm:inline opacity-40">·</span>
                <span>500+ clients actifs</span>
                <span class="hidden sm:inline opacity-40">·</span>
                <span>18 pays</span>
                <span class="hidden sm:inline opacity-40">·</span>
                <span>99.97% uptime</span>
            </div>
        </div>
    </section>

    <!-- Section 1 — Témoignages vedettes -->
    <section class="bg-gray-50 py-20 px-6">
        <div class="mx-auto max-w-6xl">
            <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-4">Témoignages clients</h2>
            <p class="text-center text-gray-500 mb-14">Ce que disent nos clients les plus fidèles</p>
            <div class="grid gap-8 md:grid-cols-3">
                <div
                    v-for="t in featuredTestimonials"
                    :key="t.name"
                    class="rounded-2xl border border-gray-100 bg-white p-8 shadow-lg flex flex-col"
                >
                    <!-- Stars -->
                    <div class="mb-4 flex gap-0.5 text-xl" style="color:#F0C040">
                        <span v-for="i in t.rating" :key="i">★</span>
                    </div>
                    <!-- Quote -->
                    <p class="text-gray-700 leading-relaxed text-sm flex-1 mb-6">"{{ t.quote }}"</p>
                    <!-- Author -->
                    <div class="flex items-center gap-3">
                        <div
                            class="h-12 w-12 rounded-full flex items-center justify-center text-sm font-extrabold text-white flex-shrink-0"
                            :style="{ background: t.color }"
                        >{{ t.initials }}</div>
                        <div>
                            <div class="font-bold text-gray-900 text-sm">{{ t.name }}</div>
                            <div class="text-xs text-gray-500">{{ t.role }} · {{ t.company }}</div>
                            <div class="text-xs text-gray-400">{{ t.location }}</div>
                        </div>
                    </div>
                    <!-- Sector badge -->
                    <div class="mt-4">
                        <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold" style="background:#EFF6FF;color:#0062CC">
                            {{ t.sector }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 2 — Grille 6 témoignages courts -->
    <section class="bg-white py-20 px-6">
        <div class="mx-auto max-w-6xl">
            <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-4">Ils en parlent mieux que nous</h2>
            <p class="text-center text-gray-500 mb-14">Des entrepreneurs de toute l'Afrique partagent leur expérience</p>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="t in shortTestimonials"
                    :key="t.name"
                    class="rounded-2xl border border-gray-100 bg-gray-50 p-6 shadow-sm flex flex-col gap-4"
                >
                    <div class="flex gap-0.5 text-base" style="color:#F0C040">
                        <span v-for="i in t.rating" :key="i">★</span>
                    </div>
                    <p class="text-gray-700 text-sm leading-relaxed flex-1">"{{ t.quote }}"</p>
                    <div class="flex items-center gap-3">
                        <div
                            class="h-10 w-10 rounded-full flex items-center justify-center text-xs font-extrabold text-white flex-shrink-0"
                            :style="{ background: t.color }"
                        >{{ t.initials }}</div>
                        <div>
                            <div class="font-semibold text-gray-900 text-sm">{{ t.name }}</div>
                            <div class="text-xs text-gray-500">{{ t.company }} · {{ t.location }}</div>
                            <span class="inline-block mt-1 rounded-full px-2 py-0.5 text-xs font-medium" style="background:#EFF6FF;color:#0062CC">
                                {{ t.sector }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 3 — Cas clients / Success stories -->
    <section class="bg-gray-50 py-20 px-6">
        <div class="mx-auto max-w-6xl">
            <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-4">Cas clients — Success stories</h2>
            <p class="text-center text-gray-500 mb-14">Des résultats concrets mesurés après déploiement de FactPro</p>
            <div class="grid gap-8 md:grid-cols-2">

                <!-- Cas 1 -->
                <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-lg">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="h-14 w-14 rounded-xl flex items-center justify-center text-base font-extrabold text-white flex-shrink-0" style="background:#0062CC">K&A</div>
                        <div>
                            <div class="font-extrabold text-gray-900 text-lg">Koné & Associés SARL</div>
                            <div class="text-sm text-gray-500">Cabinet comptable · 23 employés · Abidjan</div>
                        </div>
                    </div>
                    <div class="space-y-4 mb-6">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-widest text-gray-400">Défi</span>
                            <p class="text-sm text-gray-700 mt-1">Gestion manuelle de la facturation sur Excel pour 100+ clients, erreurs fréquentes, conformité OHADA incertaine.</p>
                        </div>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-widest text-gray-400">Solution</span>
                            <p class="text-sm text-gray-700 mt-1">FactPro plan Pro avec module multi-sociétés et relances automatiques.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-xl p-4 text-center" style="background:#FFF8E1">
                            <div class="text-2xl font-extrabold" style="color:#F0C040">-87%</div>
                            <div class="text-xs text-gray-600 mt-1">de temps sur la facturation</div>
                        </div>
                        <div class="rounded-xl p-4 text-center" style="background:#FFF8E1">
                            <div class="text-2xl font-extrabold" style="color:#F0C040">+2,3M</div>
                            <div class="text-xs text-gray-600 mt-1">FCFA impayés récupérés</div>
                        </div>
                        <div class="rounded-xl p-4 text-center" style="background:#FFF8E1">
                            <div class="text-2xl font-extrabold" style="color:#F0C040">450</div>
                            <div class="text-xs text-gray-600 mt-1">factures / mois en moyenne</div>
                        </div>
                        <div class="rounded-xl p-4 text-center" style="background:#FFF8E1">
                            <div class="text-2xl font-extrabold" style="color:#F0C040">0</div>
                            <div class="text-xs text-gray-600 mt-1">erreur OHADA depuis le déploiement</div>
                        </div>
                    </div>
                </div>

                <!-- Cas 2 -->
                <div class="rounded-2xl border border-gray-100 bg-white p-8 shadow-lg">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="h-14 w-14 rounded-xl flex items-center justify-center text-base font-extrabold text-white flex-shrink-0" style="background:#7c3aed">DS</div>
                        <div>
                            <div class="font-extrabold text-gray-900 text-lg">DigiShop Boutique</div>
                            <div class="text-sm text-gray-500">Commerce multi-boutiques · 3 points de vente · Dakar</div>
                        </div>
                    </div>
                    <div class="space-y-4 mb-6">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-widest text-gray-400">Défi</span>
                            <p class="text-sm text-gray-700 mt-1">Incohérences entre les stocks de 3 boutiques, pas de vision centralisée, pertes difficiles à identifier.</p>
                        </div>
                        <div>
                            <span class="text-xs font-bold uppercase tracking-widest text-gray-400">Solution</span>
                            <p class="text-sm text-gray-700 mt-1">FactPro plan Business avec POS multi-caissier et intégration Mobile Money.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="rounded-xl p-4 text-center" style="background:#FFF8E1">
                            <div class="text-2xl font-extrabold" style="color:#F0C040">-23%</div>
                            <div class="text-xs text-gray-600 mt-1">de pertes sur les stocks</div>
                        </div>
                        <div class="rounded-xl p-4 text-center" style="background:#FFF8E1">
                            <div class="text-2xl font-extrabold" style="color:#F0C040">3</div>
                            <div class="text-xs text-gray-600 mt-1">boutiques synchronisées en temps réel</div>
                        </div>
                        <div class="rounded-xl p-4 text-center" style="background:#FFF8E1">
                            <div class="text-2xl font-extrabold" style="color:#F0C040">+31%</div>
                            <div class="text-xs text-gray-600 mt-1">de ventes via Wave CI + Orange Money</div>
                        </div>
                        <div class="rounded-xl p-4 text-center" style="background:#FFF8E1">
                            <div class="text-2xl font-extrabold" style="color:#F0C040">Auto</div>
                            <div class="text-xs text-gray-600 mt-1">rapport de caisse chaque soir</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Section 4 — CTA final -->
    <section style="background:linear-gradient(135deg,#001d3d 0%,#002D5B 100%)" class="py-20 px-6 text-center text-white">
        <div class="mx-auto max-w-2xl">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4">
                Rejoignez 500+ entreprises qui font confiance à FactPro
            </h2>
            <p class="text-lg text-white/70 mb-10">
                Essai gratuit 14 jours · Sans carte bancaire · Configuration en 5 minutes
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a
                    href="/register"
                    class="rounded-xl px-8 py-4 text-base font-bold shadow-lg transition hover:opacity-90"
                    style="background:#F0C040;color:#001d3d"
                >
                    Commencer gratuitement
                </a>
                <a
                    href="/pricing"
                    class="rounded-xl border-2 border-white/40 px-8 py-4 text-base font-bold text-white transition hover:border-white hover:bg-white/10"
                >
                    Voir les tarifs
                </a>
            </div>
        </div>
    </section>

    <PublicFooter />
    <Analytics />
</template>

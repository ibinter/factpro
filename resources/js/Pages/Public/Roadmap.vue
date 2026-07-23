<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';
import Analytics from '@/Components/Analytics.vue';

const props = defineProps({
    features:    { type: Array,  default: () => [] },
    stats:       { type: Object, default: () => ({}) },
    canLogin:    { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
    auth:        { type: Object, default: () => ({}) },
});

const isLoggedIn = computed(() => !!props.auth?.user);

// Local reactive copy for optimistic UI
const localFeatures = ref(props.features.map(f => ({ ...f })));

const statusFilter   = ref('all');
const categoryFilter = ref('all');

const statusOptions = [
    { value: 'all',         label: 'Tout' },
    { value: 'planned',     label: '🔵 Planifié' },
    { value: 'in_progress', label: '🟡 En cours' },
    { value: 'delivered',   label: '✅ Livré' },
];

const categoryOptions = [
    { value: 'all',         label: 'Toutes catégories' },
    { value: 'general',     label: 'Général' },
    { value: 'pos',         label: 'Caisse POS' },
    { value: 'facturation', label: 'Facturation' },
    { value: 'stocks',      label: 'Stocks' },
    { value: 'api',         label: 'API / Intégrations' },
    { value: 'mobile',      label: 'Mobile' },
];

const filtered = computed(() => {
    return localFeatures.value
        .filter(f => statusFilter.value   === 'all' || f.status   === statusFilter.value)
        .filter(f => categoryFilter.value === 'all' || f.category === categoryFilter.value)
        .sort((a, b) => b.votes_count - a.votes_count);
});

const statusBadge = (status) => {
    if (status === 'planned')     return { label: 'Planifié',  cls: 'bg-blue-100 text-blue-700' };
    if (status === 'in_progress') return { label: 'En cours',  cls: 'bg-amber-100 text-amber-700' };
    if (status === 'delivered')   return { label: 'Livré',     cls: 'bg-green-100 text-green-700' };
    return { label: 'Annulé', cls: 'bg-gray-100 text-gray-500' };
};

const categoryLabel = (cat) => {
    const map = { general: 'Général', pos: 'POS', facturation: 'Facturation', stocks: 'Stocks', api: 'API', mobile: 'Mobile' };
    return map[cat] ?? cat;
};

const voting = ref({});

async function toggleVote(feature) {
    if (!isLoggedIn.value) {
        window.location.href = '/login';
        return;
    }
    if (voting.value[feature.id]) return;
    voting.value[feature.id] = true;

    // Optimistic update
    const local = localFeatures.value.find(f => f.id === feature.id);
    const wasVoted = local.has_voted;
    local.has_voted   = !wasVoted;
    local.votes_count += wasVoted ? -1 : 1;

    try {
        const { data } = await axios.post(`/roadmap/${feature.id}/vote`);
        local.has_voted   = data.voted;
        local.votes_count = data.votes_count;
    } catch {
        // Revert
        local.has_voted   = wasVoted;
        local.votes_count += wasVoted ? 1 : -1;
    } finally {
        voting.value[feature.id] = false;
    }
}
</script>

<template>
    <Head title="Roadmap — FactPro">
        <meta name="description" content="Découvrez les fonctionnalités à venir de FactPro et votez pour celles que vous souhaitez voir en priorité." />
    </Head>

    <Analytics />
    <PublicNav :can-login="canLogin" :can-register="canRegister" />

    <!-- Hero -->
    <section style="background:linear-gradient(135deg,#001d3d 0%,#003068 60%,#001d3d 100%)" class="py-20 text-center">
        <div class="mx-auto max-w-3xl px-6">
            <div class="mb-4 inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:rgba(201,168,76,.18);color:#C9A84C">
                Roadmap Publique
            </div>
            <h1 class="text-4xl font-extrabold text-white sm:text-5xl">Notre Roadmap</h1>
            <p class="mt-4 text-lg" style="color:rgba(255,255,255,.72)">
                Votez pour les fonctionnalités que vous souhaitez voir en priorité dans FactPro.
            </p>

            <!-- Stats -->
            <div class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-2xl p-4" style="background:rgba(255,255,255,.07)">
                    <div class="text-3xl font-extrabold" style="color:#C9A84C">{{ stats.planned }}</div>
                    <div class="mt-1 text-xs font-semibold text-white/60">Planifiées</div>
                </div>
                <div class="rounded-2xl p-4" style="background:rgba(255,255,255,.07)">
                    <div class="text-3xl font-extrabold" style="color:#C9A84C">{{ stats.in_progress }}</div>
                    <div class="mt-1 text-xs font-semibold text-white/60">En cours</div>
                </div>
                <div class="rounded-2xl p-4" style="background:rgba(255,255,255,.07)">
                    <div class="text-3xl font-extrabold" style="color:#C9A84C">{{ stats.delivered }}</div>
                    <div class="mt-1 text-xs font-semibold text-white/60">Livrées</div>
                </div>
                <div class="rounded-2xl p-4" style="background:rgba(255,255,255,.07)">
                    <div class="text-3xl font-extrabold" style="color:#C9A84C">{{ stats.total_votes }}</div>
                    <div class="mt-1 text-xs font-semibold text-white/60">Votes total</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Filtres -->
    <section class="border-b border-gray-100 bg-white py-5 sticky top-0 z-30 shadow-sm">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-3 px-6">
            <!-- Status tabs -->
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="opt in statusOptions"
                    :key="opt.value"
                    @click="statusFilter = opt.value"
                    class="rounded-full px-4 py-1.5 text-sm font-semibold transition"
                    :class="statusFilter === opt.value
                        ? 'bg-brand-600 text-white shadow'
                        : 'bg-gray-100 text-gray-600 hover:bg-brand-50 hover:text-brand-700'"
                >
                    {{ opt.label }}
                </button>
            </div>

            <div class="ml-auto">
                <select
                    v-model="categoryFilter"
                    class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-400"
                >
                    <option v-for="c in categoryOptions" :key="c.value" :value="c.value">{{ c.label }}</option>
                </select>
            </div>
        </div>
    </section>

    <!-- Grille -->
    <section class="bg-gray-50 py-12">
        <div class="mx-auto max-w-6xl px-6">
            <div v-if="filtered.length === 0" class="py-20 text-center text-gray-400">
                Aucune fonctionnalité ne correspond à ces filtres.
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div
                    v-for="feature in filtered"
                    :key="feature.id"
                    class="flex gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-100 transition hover:shadow-md"
                >
                    <!-- Bouton vote -->
                    <div class="flex flex-col items-center gap-1 flex-shrink-0">
                        <button
                            @click="toggleVote(feature)"
                            :disabled="voting[feature.id]"
                            class="flex h-12 w-12 flex-col items-center justify-center rounded-xl text-lg font-bold transition"
                            :class="feature.has_voted
                                ? 'bg-brand-600 text-white shadow-md'
                                : 'bg-gray-100 text-gray-500 hover:bg-brand-50 hover:text-brand-600'"
                            :title="isLoggedIn ? (feature.has_voted ? 'Retirer mon vote' : 'Voter') : 'Connectez-vous pour voter'"
                        >
                            🗳️
                        </button>
                        <span class="text-sm font-bold" :class="feature.has_voted ? 'text-brand-600' : 'text-gray-600'">
                            {{ feature.votes_count }}
                        </span>
                    </div>

                    <!-- Contenu -->
                    <div class="flex-1 min-w-0">
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusBadge(feature.status).cls">
                                {{ statusBadge(feature.status).label }}
                            </span>
                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">
                                {{ categoryLabel(feature.category) }}
                            </span>
                            <span v-if="feature.delivered_at" class="text-xs text-gray-400">· {{ feature.delivered_at }}</span>
                        </div>
                        <h3 class="text-base font-bold text-gray-900">{{ feature.title }}</h3>
                        <p class="mt-1 text-sm leading-relaxed text-gray-500">{{ feature.description }}</p>
                    </div>
                </div>
            </div>

            <!-- Note bas de page -->
            <div class="mt-10 rounded-2xl p-5 text-center text-sm" style="background:#f0f7ff;border:1px solid #bfdbfe;color:#1e40af">
                <template v-if="isLoggedIn">
                    Les fonctionnalités les plus votées sont priorisées dans notre planning de développement.
                </template>
                <template v-else>
                    <Link href="/login" class="font-bold underline">Connectez-vous</Link> pour voter ·
                    Les fonctionnalités les plus votées sont priorisées dans notre planning
                </template>
            </div>
        </div>
    </section>

    <PublicFooter />
</template>

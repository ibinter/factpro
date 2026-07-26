<script setup>
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AdminTabs from '@/Components/AdminTabs.vue';

const props = defineProps({
    stats:         { type: Object, default: () => ({}) },
    users:         { type: Object, default: () => ({}) },
    recent_errors: { type: Array,  default: () => [] },
    app_version:   { type: String, default: '1.0.0' },
    checked_at:    { type: String, default: '' },
});

const page = usePage();
const flash = ref(page.props.flash ?? {});

// Format checked_at to locale string
const checkedAtFormatted = (() => {
    try {
        return new Date(props.checked_at).toLocaleString('fr-FR');
    } catch {
        return props.checked_at;
    }
})();

// Status helpers
const statusIcon = (s) => ({ ok: '✅', warning: '⚠️', error: '❌', unknown: '❓' }[s] ?? '❓');
const statusColor = (s) => ({
    ok:      'border-green-200 bg-green-50',
    warning: 'border-orange-200 bg-orange-50',
    error:   'border-red-200 bg-red-50',
    unknown: 'border-gray-200 bg-gray-50',
}[s] ?? 'border-gray-200 bg-gray-50');
const statusTextColor = (s) => ({
    ok:      'text-green-700',
    warning: 'text-orange-600',
    error:   'text-red-700',
    unknown: 'text-gray-500',
}[s] ?? 'text-gray-500');
const statusLabel = (s) => ({ ok: 'Opérationnel', warning: 'Avertissement', error: 'Erreur', unknown: 'Inconnu' }[s] ?? s);

const diskBarColor = (pct) => {
    if (pct > 85) return 'bg-red-500';
    if (pct > 70) return 'bg-orange-400';
    return 'bg-green-500';
};

// Cache actions
const clearing = ref('');
function clearCache(type) {
    clearing.value = type;
    const url = {
        all:    route('admin.observability.cache.clear'),
        config: route('admin.observability.cache.config'),
        route:  route('admin.observability.cache.route'),
        view:   route('admin.observability.cache.view'),
    }[type];
    router.post(url, {}, {
        onFinish: () => { clearing.value = ''; },
        onSuccess: () => { flash.value = page.props.flash ?? {}; },
    });
}
</script>

<template>
    <Head title="Observabilité — IBIG FactPro" />

    <div class="min-h-screen bg-gray-50">
        <!-- Admin Tabs -->
        <div class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm">
            <AdminTabs />
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Flash message -->
            <div
                v-if="flash && flash.success"
                class="mb-6 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-5 py-3 text-green-800 text-sm font-medium shadow-sm"
            >
                ✅ {{ flash.success }}
            </div>

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    État des services IBIG FactPro
                    <span class="ml-3 text-lg font-semibold text-gray-400">v{{ app_version }}</span>
                </h1>
                <p class="text-gray-500 mt-1 text-sm">
                    Vérifié à {{ checkedAtFormatted }}
                </p>
            </div>

            <!-- Section 1 : Cartes état services -->
            <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-3">Services</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

                <!-- Database -->
                <div :class="['rounded-2xl border p-5 shadow-sm', statusColor(stats.db)]">
                    <div class="text-2xl mb-1">{{ statusIcon(stats.db) }}</div>
                    <div class="font-bold text-gray-800">Base de données</div>
                    <div :class="['text-sm font-semibold mt-1', statusTextColor(stats.db)]">
                        {{ statusLabel(stats.db) }}
                    </div>
                </div>

                <!-- Queue -->
                <div :class="['rounded-2xl border p-5 shadow-sm', statusColor(stats.queue)]">
                    <div class="text-2xl mb-1">{{ statusIcon(stats.queue) }}</div>
                    <div class="font-bold text-gray-800">File de jobs</div>
                    <div :class="['text-sm font-semibold mt-1', statusTextColor(stats.queue)]">
                        {{ statusLabel(stats.queue) }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ stats.pending_jobs }} en attente · {{ stats.failed_jobs }} échoués
                    </div>
                </div>

                <!-- Disk -->
                <div :class="['rounded-2xl border p-5 shadow-sm', statusColor(stats.disk)]">
                    <div class="text-2xl mb-1">{{ statusIcon(stats.disk) }}</div>
                    <div class="font-bold text-gray-800">Disque</div>
                    <div :class="['text-sm font-semibold mt-1', statusTextColor(stats.disk)]">
                        {{ stats.disk_used_percent }}% utilisé
                    </div>
                    <div class="text-xs text-gray-500 mt-1">{{ stats.disk_free_gb }} Go libres</div>
                </div>

                <!-- SMTP -->
                <div class="rounded-2xl border border-green-200 bg-green-50 p-5 shadow-sm">
                    <div class="text-2xl mb-1">✅</div>
                    <div class="font-bold text-gray-800">SMTP / Email</div>
                    <div class="text-sm font-semibold mt-1 text-green-700">Opérationnel</div>
                </div>
            </div>

            <!-- Section 2 : Stats utilisateurs -->
            <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-3">Utilisateurs</h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
                    <div class="text-sm text-gray-500 font-medium mb-1">Total</div>
                    <div class="text-4xl font-extrabold text-blue-600">{{ users.total }}</div>
                    <div class="text-xs text-gray-400 mt-1">utilisateurs</div>
                </div>
                <div class="rounded-2xl border border-green-100 bg-white p-5 shadow-sm">
                    <div class="text-sm text-gray-500 font-medium mb-1">Actifs</div>
                    <div class="text-4xl font-extrabold text-green-600">{{ users.active }}</div>
                    <div class="text-xs text-gray-400 mt-1">comptes actifs</div>
                </div>
                <div class="rounded-2xl border border-orange-100 bg-white p-5 shadow-sm">
                    <div class="text-sm text-gray-500 font-medium mb-1">En essai</div>
                    <div class="text-4xl font-extrabold text-orange-500">{{ users.trial }}</div>
                    <div class="text-xs text-gray-400 mt-1">comptes trial</div>
                </div>
                <div class="rounded-2xl border border-indigo-100 bg-white p-5 shadow-sm">
                    <div class="text-sm text-gray-500 font-medium mb-1">Nouveaux aujourd'hui</div>
                    <div class="text-4xl font-extrabold text-indigo-600">{{ users.new_today }}</div>
                    <div class="text-xs text-gray-400 mt-1">inscrits ce jour</div>
                </div>
            </div>

            <!-- Section 3 : Queue details -->
            <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-3">File de jobs</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Jobs en attente</div>
                        <div class="text-3xl font-bold text-gray-800 mt-1">{{ stats.pending_jobs }}</div>
                    </div>
                    <div class="text-4xl opacity-20">⏳</div>
                </div>
                <div :class="[
                    'rounded-2xl border p-5 shadow-sm flex items-center justify-between',
                    stats.failed_jobs > 0 ? 'border-red-200 bg-red-50' : 'border-gray-100 bg-white'
                ]">
                    <div>
                        <div class="text-sm text-gray-500">Jobs échoués</div>
                        <div :class="['text-3xl font-bold mt-1', stats.failed_jobs > 0 ? 'text-red-600' : 'text-gray-800']">
                            {{ stats.failed_jobs }}
                        </div>
                    </div>
                    <div class="text-4xl opacity-20">💥</div>
                </div>
            </div>

            <!-- Section 4 : Utilisation disque -->
            <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-3">Disque</h2>
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm mb-8">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Espace utilisé</span>
                    <span class="font-semibold">{{ stats.disk_used_percent }}% · {{ stats.disk_free_gb }} Go libres</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-4 overflow-hidden">
                    <div
                        :class="['h-4 rounded-full transition-all duration-500', diskBarColor(stats.disk_used_percent)]"
                        :style="{ width: stats.disk_used_percent + '%' }"
                    />
                </div>
                <div class="mt-2 flex gap-4 text-xs text-gray-400">
                    <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-green-500"></span> &lt; 70% — OK</span>
                    <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-orange-400"></span> 70-85% — Avertissement</span>
                    <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full bg-red-500"></span> &gt; 85% — Critique</span>
                </div>
            </div>

            <!-- Section 5 : Erreurs récentes -->
            <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-3">Erreurs récentes (log)</h2>
            <div class="rounded-2xl border border-red-100 bg-white shadow-sm mb-8 overflow-hidden">
                <div v-if="recent_errors.length === 0" class="px-6 py-8 text-center text-gray-400 text-sm">
                    Aucune erreur récente dans les logs. 🎉
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-xs divide-y divide-red-50">
                        <thead class="bg-red-50 text-gray-500 uppercase text-xs tracking-wide">
                            <tr>
                                <th class="px-4 py-3 text-left w-8">#</th>
                                <th class="px-4 py-3 text-left">Message d'erreur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-red-50">
                            <tr
                                v-for="(err, i) in recent_errors"
                                :key="i"
                                class="bg-red-50/30 hover:bg-red-50 transition"
                            >
                                <td class="px-4 py-2 text-gray-400 align-top">{{ i + 1 }}</td>
                                <td class="px-4 py-2 text-red-700 font-mono break-all">{{ err }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 6 : Actions rapides -->
            <h2 class="text-sm font-semibold uppercase tracking-widest text-gray-400 mb-3">Actions rapides</h2>
            <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-500 mb-4">Vider les caches applicatifs Laravel (config, routes, vues).</p>
                <div class="flex flex-wrap gap-3">
                    <button
                        @click="clearCache('all')"
                        :disabled="clearing !== ''"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gray-800 text-white text-sm font-semibold hover:bg-gray-700 disabled:opacity-50 transition"
                    >
                        <span v-if="clearing === 'all'" class="animate-spin">⏳</span>
                        <span v-else>🗑️</span>
                        Tout vider
                    </button>
                    <button
                        @click="clearCache('config')"
                        :disabled="clearing !== ''"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 disabled:opacity-50 transition"
                    >
                        <span v-if="clearing === 'config'" class="animate-spin">⏳</span>
                        <span v-else>⚙️</span>
                        Cache config
                    </button>
                    <button
                        @click="clearCache('route')"
                        :disabled="clearing !== ''"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 disabled:opacity-50 transition"
                    >
                        <span v-if="clearing === 'route'" class="animate-spin">⏳</span>
                        <span v-else>🔀</span>
                        Cache routes
                    </button>
                    <button
                        @click="clearCache('view')"
                        :disabled="clearing !== ''"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-semibold hover:bg-teal-700 disabled:opacity-50 transition"
                    >
                        <span v-if="clearing === 'view'" class="animate-spin">⏳</span>
                        <span v-else>🖼️</span>
                        Cache vues
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

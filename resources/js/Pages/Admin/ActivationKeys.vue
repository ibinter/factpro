<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    keys:    Object,
    plans:   Array,
    batches: Array,
    filters: Object,
});

// ── Génération de lot ──────────────────────────────────────────────────────
const generateForm = useForm({
    plan_id:       '',
    quantity:      10,
    duration_days: 365,
    expires_at:    '',
});

const submitGenerate = () => {
    generateForm.post(route('admin.activation-keys.store'), {
        onSuccess: () => generateForm.reset(),
    });
};

// ── Révocation ─────────────────────────────────────────────────────────────
const revokeModal = ref(null); // { key }
const revokeForm  = useForm({ reason: '' });

const openRevoke = (key) => {
    revokeModal.value = key;
    revokeForm.reset();
};

const submitRevoke = () => {
    revokeForm.post(route('admin.activation-keys.revoke', revokeModal.value.id), {
        onSuccess: () => { revokeModal.value = null; revokeForm.reset(); },
    });
};

// ── Filtres ────────────────────────────────────────────────────────────────
const filterForm = useForm({
    batch:     props.filters?.batch     ?? '',
    status:    props.filters?.status    ?? '',
    plan_id:   props.filters?.plan_id   ?? '',
    date_from: props.filters?.date_from ?? '',
    date_to:   props.filters?.date_to   ?? '',
});

const applyFilters = () => {
    router.get(route('admin.activation-keys.index'), filterForm.data(), { preserveState: true });
};

const resetFilters = () => {
    filterForm.reset();
    router.get(route('admin.activation-keys.index'));
};

// ── Affichage des codes (masqué par défaut) ────────────────────────────────
const visibleCodes = ref(new Set());
const toggleCode = (id) => {
    if (visibleCodes.value.has(id)) {
        visibleCodes.value.delete(id);
    } else {
        visibleCodes.value.add(id);
    }
};
const maskCode = (code) => code.replace(/[A-Z0-9]/g, (c, i) => i < 5 ? c : '•');

// ── Helpers ────────────────────────────────────────────────────────────────
const statusColors = {
    available: 'bg-green-100 text-green-700',
    used:      'bg-gray-100 text-gray-600',
    expired:   'bg-amber-100 text-amber-700',
    revoked:   'bg-red-100 text-red-700',
};
const statusLabels = {
    available: 'Disponible',
    used:      'Utilisée',
    expired:   'Expirée',
    revoked:   'Révoquée',
};
</script>

<template>
    <Head title="Clés d'activation" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Clés d'activation formule</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <AdminTabs />

                <!-- Formulaire génération de lot -->
                <div class="rounded-xl bg-white p-6 shadow">
                    <h3 class="mb-4 font-semibold text-gray-800">Générer un nouveau lot</h3>
                    <form @submit.prevent="submitGenerate" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Forfait *</label>
                            <select v-model="generateForm.plan_id" required
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                                <option value="">— Choisir —</option>
                                <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                            <p v-if="generateForm.errors.plan_id" class="mt-1 text-xs text-red-600">{{ generateForm.errors.plan_id }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Quantité (1-500) *</label>
                            <input type="number" v-model="generateForm.quantity" min="1" max="500" required
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" />
                            <p v-if="generateForm.errors.quantity" class="mt-1 text-xs text-red-600">{{ generateForm.errors.quantity }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Durée (jours) *</label>
                            <input type="number" v-model="generateForm.duration_days" min="1" max="3650" required
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" />
                            <p v-if="generateForm.errors.duration_days" class="mt-1 text-xs text-red-600">{{ generateForm.errors.duration_days }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Expiration clé (optionnel)</label>
                            <input type="date" v-model="generateForm.expires_at"
                                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" />
                            <p v-if="generateForm.errors.expires_at" class="mt-1 text-xs text-red-600">{{ generateForm.errors.expires_at }}</p>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" :disabled="generateForm.processing"
                                class="w-full rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50">
                                {{ generateForm.processing ? 'Génération...' : 'Générer le lot' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Filtres -->
                <div class="rounded-xl bg-white p-4 shadow">
                    <div class="flex flex-wrap items-end gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Lot</label>
                            <select v-model="filterForm.batch"
                                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                                <option value="">Tous les lots</option>
                                <option v-for="b in batches" :key="b.batch" :value="b.batch">{{ b.batch }} ({{ b.total }})</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Statut</label>
                            <select v-model="filterForm.status"
                                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                                <option value="">Tous</option>
                                <option value="available">Disponible</option>
                                <option value="used">Utilisée</option>
                                <option value="expired">Expirée</option>
                                <option value="revoked">Révoquée</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Forfait</label>
                            <select v-model="filterForm.plan_id"
                                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                                <option value="">Tous</option>
                                <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Du</label>
                            <input type="date" v-model="filterForm.date_from"
                                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600">Au</label>
                            <input type="date" v-model="filterForm.date_to"
                                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none" />
                        </div>
                        <button @click="applyFilters"
                            class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                            Filtrer
                        </button>
                        <button @click="resetFilters"
                            class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                            Réinitialiser
                        </button>

                        <!-- Export CSV par lot -->
                        <a v-if="filterForm.batch"
                            :href="route('admin.activation-keys.export', filterForm.batch)"
                            class="ml-auto rounded-md border border-green-600 px-4 py-2 text-sm font-semibold text-green-700 hover:bg-green-50">
                            ↓ Exporter CSV ({{ filterForm.batch }})
                        </a>
                    </div>
                </div>

                <!-- Tableau des clés -->
                <div class="overflow-hidden rounded-xl bg-white shadow">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Code</th>
                                    <th class="px-4 py-3">Lot</th>
                                    <th class="px-4 py-3">Forfait</th>
                                    <th class="px-4 py-3">Durée</th>
                                    <th class="px-4 py-3">Expire le</th>
                                    <th class="px-4 py-3">Statut</th>
                                    <th class="px-4 py-3">Société</th>
                                    <th class="px-4 py-3">Utilisé le</th>
                                    <th class="px-4 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="k in keys.data" :key="k.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <button @click="toggleCode(k.id)"
                                            class="font-mono text-xs text-brand-700 hover:underline focus:outline-none"
                                            :title="visibleCodes.has(k.id) ? 'Masquer' : 'Afficher'">
                                            {{ visibleCodes.has(k.id) ? k.code : maskCode(k.code) }}
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ k.batch }}</td>
                                    <td class="px-4 py-3">{{ k.plan?.name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ k.duration_days }} j</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ k.expires_at ? new Date(k.expires_at).toLocaleDateString('fr-FR') : '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                            :class="statusColors[k.status] ?? 'bg-gray-100 text-gray-600'">
                                            {{ statusLabels[k.status] ?? k.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-600">{{ k.used_by_company?.name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ k.used_at ? new Date(k.used_at).toLocaleDateString('fr-FR') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button
                                            v-if="k.status === 'available'"
                                            @click="openRevoke(k)"
                                            class="text-xs font-semibold text-red-600 hover:underline">
                                            Révoquer
                                        </button>
                                        <span v-else class="text-xs text-gray-400">—</span>
                                    </td>
                                </tr>
                                <tr v-if="!keys.data.length">
                                    <td colspan="9" class="px-6 py-10 text-center text-gray-400">Aucune clé pour les filtres sélectionnés.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="keys.links?.length > 3" class="flex flex-wrap gap-1 px-4 py-3">
                        <template v-for="link in keys.links" :key="link.label">
                            <a v-if="link.url" :href="link.url" v-html="link.label"
                                class="rounded px-3 py-1.5 text-sm"
                                :class="link.active ? 'bg-brand-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'" />
                            <span v-else v-html="link.label" class="px-3 py-1.5 text-sm text-gray-400" />
                        </template>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal révocation -->
        <div v-if="revokeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-1 font-semibold text-gray-800">Révoquer la clé</h3>
                <p class="mb-4 font-mono text-sm text-brand-700">{{ revokeModal.code }}</p>
                <form @submit.prevent="submitRevoke">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Motif de révocation *</label>
                    <textarea v-model="revokeForm.reason" rows="3" required
                        placeholder="Ex. : Clé distribuée par erreur…"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none"></textarea>
                    <p v-if="revokeForm.errors.reason" class="mt-1 text-xs text-red-600">{{ revokeForm.errors.reason }}</p>
                    <div class="mt-4 flex justify-end gap-3">
                        <button type="button" @click="revokeModal = null"
                            class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                            Annuler
                        </button>
                        <button type="submit" :disabled="revokeForm.processing"
                            class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50">
                            {{ revokeForm.processing ? 'Révocation...' : 'Confirmer la révocation' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

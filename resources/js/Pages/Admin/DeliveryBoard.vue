<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    deliveries: Object,
    agents:     Array,
    filters:    Object,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const STATUS_LABELS = {
    pending:          '⏳ En attente',
    assigned:         '👤 Assigné',
    out_for_delivery: '🚚 En route',
    delivered:        '📦 Livré',
    payment_received: '💰 Paiement reçu',
    failed:           '❌ Échec',
    returned:         '↩ Retourné',
};

const STATUS_COLORS = {
    pending:          'bg-yellow-100 text-yellow-800',
    assigned:         'bg-blue-100 text-blue-800',
    out_for_delivery: 'bg-indigo-100 text-indigo-800',
    delivered:        'bg-green-100 text-green-800',
    payment_received: 'bg-emerald-100 text-emerald-800',
    failed:           'bg-red-100 text-red-800',
    returned:         'bg-gray-100 text-gray-700',
};

// ── Filtres ──────────────────────────────────────────────────────────────
const filterForm = useForm({
    status:   props.filters?.status ?? '',
    city:     props.filters?.city ?? '',
    agent_id: props.filters?.agent_id ?? '',
});

const applyFilters = () => {
    router.get(route('admin.deliveries.index'), filterForm.data(), { preserveState: true });
};

// ── Assigner un agent ─────────────────────────────────────────────────────
const assigningId = ref(null);
const assignForm = useForm({ delivery_agent_id: '' });

const openAssign = (delivery) => {
    assigningId.value = delivery.id;
    assignForm.delivery_agent_id = delivery.delivery_agent_id ?? '';
};

const submitAssign = (deliveryId) => {
    assignForm.post(route('admin.deliveries.assign', deliveryId), {
        onSuccess: () => { assigningId.value = null; },
    });
};

// ── Confirmer paiement ────────────────────────────────────────────────────
const confirmingId = ref(null);
const confirmForm = useForm({ amount_received: '', agent_notes: '' });

const openConfirm = (delivery) => {
    confirmingId.value = delivery.id;
    confirmForm.amount_received = delivery.cod_amount;
    confirmForm.agent_notes = '';
};

const submitConfirm = (deliveryId) => {
    confirmForm.post(route('admin.deliveries.confirm', deliveryId), {
        onSuccess: () => { confirmingId.value = null; },
    });
};
</script>

<template>
    <Head title="Tableau de bord livraisons" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Livraisons COD</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 space-y-5">

                <!-- Filtres -->
                <div class="rounded-xl bg-white p-4 shadow flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Statut</label>
                        <select v-model="filterForm.status" class="rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Tous</option>
                            <option v-for="(label, key) in STATUS_LABELS" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Ville</label>
                        <input v-model="filterForm.city" type="text" placeholder="Ex: Abidjan"
                            class="rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Agent</label>
                        <select v-model="filterForm.agent_id" class="rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Tous les agents</option>
                            <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                        </select>
                    </div>
                    <button @click="applyFilters" class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                        Filtrer
                    </button>
                </div>

                <!-- Tableau des livraisons -->
                <div class="rounded-xl bg-white shadow overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Commande</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Client</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Ville</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Montant COD</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Statut</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Agent</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!deliveries.data?.length">
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucune livraison trouvée.</td>
                            </tr>
                            <tr v-for="d in deliveries.data" :key="d.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs">{{ d.order?.order_number }}</td>
                                <td class="px-4 py-3">
                                    <div>{{ d.contact_name }}</div>
                                    <div class="text-xs text-gray-400">{{ d.contact_phone }}</div>
                                </td>
                                <td class="px-4 py-3">{{ d.delivery_city }}</td>
                                <td class="px-4 py-3 font-semibold">{{ fmt(d.cod_amount) }} {{ d.cod_currency }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" :class="STATUS_COLORS[d.status]">
                                        {{ STATUS_LABELS[d.status] ?? d.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs">{{ d.agent?.name ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <!-- Assigner -->
                                        <button
                                            v-if="['pending','assigned'].includes(d.status)"
                                            @click="openAssign(d)"
                                            class="rounded bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-200"
                                        >
                                            Assigner
                                        </button>
                                        <!-- Confirmer paiement -->
                                        <button
                                            v-if="['out_for_delivery','delivered'].includes(d.status)"
                                            @click="openConfirm(d)"
                                            class="rounded bg-green-100 px-2 py-1 text-xs font-semibold text-green-700 hover:bg-green-200"
                                        >
                                            Confirmer paiement
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="deliveries.last_page > 1" class="flex justify-center gap-2">
                    <a
                        v-for="page in deliveries.last_page"
                        :key="page"
                        :href="deliveries.path + '?page=' + page"
                        class="rounded px-3 py-1 text-sm"
                        :class="page === deliveries.current_page ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                    >
                        {{ page }}
                    </a>
                </div>

            </div>
        </div>

        <!-- Modal assigner un agent -->
        <div v-if="assigningId !== null" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 font-semibold text-gray-800">Assigner un agent</h3>
                <select v-model="assignForm.delivery_agent_id" class="block w-full rounded-md border-gray-300 text-sm mb-4">
                    <option value="">— Choisir un agent —</option>
                    <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }} ({{ a.city }})</option>
                </select>
                <div class="flex justify-end gap-3">
                    <button @click="assigningId = null" class="text-sm text-gray-500 hover:text-gray-700">Annuler</button>
                    <button
                        @click="submitAssign(assigningId)"
                        :disabled="!assignForm.delivery_agent_id || assignForm.processing"
                        class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                    >
                        Assigner
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal confirmer paiement -->
        <div v-if="confirmingId !== null" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 font-semibold text-gray-800">Confirmer la réception du paiement</h3>
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Montant reçu *</label>
                    <input v-model="confirmForm.amount_received" type="number" step="1" min="0"
                        class="block w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500" />
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes agent</label>
                    <textarea v-model="confirmForm.agent_notes" rows="2"
                        class="block w-full rounded-md border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500"
                        placeholder="Remarques, écarts..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button @click="confirmingId = null" class="text-sm text-gray-500 hover:text-gray-700">Annuler</button>
                    <button
                        @click="submitConfirm(confirmingId)"
                        :disabled="!confirmForm.amount_received || confirmForm.processing"
                        class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50"
                    >
                        {{ confirmForm.processing ? '...' : 'Confirmer' }}
                    </button>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    documents: Object,
    filters: Object,
    types: Array,
});

const search = ref(props.filters.search ?? '');
const type = ref(props.filters.type ?? '');
const status = ref(props.filters.status ?? '');

let timeout = null;
const applyFilters = () => {
    router.get(
        route('documents.index'),
        { search: search.value || undefined, type: type.value || undefined, status: status.value || undefined },
        { preserveState: true, replace: true },
    );
};
watch(search, () => {
    clearTimeout(timeout);
    timeout = setTimeout(applyFilters, 350);
});
watch([type, status], applyFilters);

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const statusLabels = {
    draft: 'Brouillon', sent: 'Envoyé', viewed: 'Vu', accepted: 'Accepté',
    rejected: 'Refusé', partial: 'Partiel', paid: 'Payé', overdue: 'En retard',
    cancelled: 'Annulé', converted: 'Converti',
};
const statusColors = {
    draft: 'bg-gray-100 text-gray-700', sent: 'bg-blue-100 text-blue-700',
    viewed: 'bg-indigo-100 text-indigo-700', accepted: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700', partial: 'bg-amber-100 text-amber-700',
    paid: 'bg-green-100 text-green-700', overdue: 'bg-red-100 text-red-700',
    cancelled: 'bg-gray-100 text-gray-500', converted: 'bg-purple-100 text-purple-700',
};

const typeLabel = (value) => props.types.find((t) => t.value === value)?.label ?? value;
</script>

<template>
    <Head title="Documents" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Documents commerciaux</h2>
                <div class="flex gap-2">
                    <Link
                        :href="route('documents.create', { type: 'quote' })"
                        class="rounded-md border border-brand-600 px-4 py-2 text-sm font-semibold text-brand-600 hover:bg-brand-50"
                    >
                        + Devis
                    </Link>
                    <Link
                        :href="route('documents.create', { type: 'invoice' })"
                        class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700"
                    >
                        + Facture
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap gap-3">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="N° de document ou client…"
                        class="w-64 rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                    />
                    <select v-model="type" class="rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Tous les types</option>
                        <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
                    </select>
                    <select v-model="status" class="rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">Tous les statuts</option>
                        <option v-for="(label, value) in statusLabels" :key="value" :value="value">{{ label }}</option>
                    </select>
                </div>

                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Numéro</th>
                                <th class="px-6 py-3">Client</th>
                                <th class="px-6 py-3">Émis le</th>
                                <th class="px-6 py-3 text-right">Total TTC</th>
                                <th class="px-6 py-3">Statut</th>
                                <th class="px-6 py-3">Scellé</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr
                                v-for="doc in documents.data"
                                :key="doc.id"
                                class="cursor-pointer hover:bg-gray-50"
                                @click="router.visit(route('documents.show', doc.id))"
                            >
                                <td class="px-6 py-3 text-gray-500">{{ typeLabel(doc.type) }}</td>
                                <td class="px-6 py-3 font-semibold text-brand-600">{{ doc.number }}</td>
                                <td class="px-6 py-3">{{ doc.customer?.name ?? '—' }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ doc.issue_date?.slice(0, 10) }}</td>
                                <td class="px-6 py-3 text-right font-semibold">{{ fmt(doc.total) }} {{ doc.currency }}</td>
                                <td class="px-6 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="statusColors[doc.status]">
                                        {{ statusLabels[doc.status] ?? doc.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <span v-if="doc.finalized_at" title="Document scellé — infalsifiable">🔒</span>
                                    <span v-else class="text-gray-300">—</span>
                                </td>
                            </tr>
                            <tr v-if="!documents.data.length">
                                <td colspan="7" class="px-6 py-10 text-center text-gray-400">Aucun document trouvé.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="documents.links.length > 3" class="flex flex-wrap gap-1">
                    <template v-for="link in documents.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="rounded px-3 py-1.5 text-sm"
                            :class="link.active ? 'bg-brand-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                        />
                        <span v-else v-html="link.label" class="px-3 py-1.5 text-sm text-gray-400" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

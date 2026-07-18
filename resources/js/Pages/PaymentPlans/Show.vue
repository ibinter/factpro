<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    plan: Object,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const statusLabels = { active: 'Actif', completed: 'Terminé', cancelled: 'Annulé' };
const statusColors = {
    active: 'bg-green-100 text-green-700',
    completed: 'bg-brand-100 text-brand-700',
    cancelled: 'bg-gray-100 text-gray-500',
};
const installmentStatusLabels = { pending: 'À venir', invoiced: 'Facturé', paid: 'Payé' };
const installmentStatusColors = {
    pending: 'bg-gray-100 text-gray-600',
    invoiced: 'bg-blue-100 text-blue-700',
    paid: 'bg-green-100 text-green-700',
};

const progress = computed(() =>
    props.plan.total_amount ? Math.min(100, Math.round((props.plan.total_invoiced / props.plan.total_amount) * 100)) : 0,
);

const invoiceInstallment = (inst) => {
    router.post(route('payment-plans.installment.invoice', inst.id), {}, { preserveScroll: true });
};

const cancelPlan = () => {
    router.post(route('payment-plans.cancel', props.plan.id), {}, { preserveScroll: true });
};
</script>

<template>
    <Head :title="'Plan ' + plan.name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    📅 {{ plan.name }}
                    <span class="ml-2 rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusColors[plan.status]">
                        {{ statusLabels[plan.status] ?? plan.status }}
                    </span>
                </h2>
                <Link :href="route('payment-plans.index')" class="text-sm text-brand-600 hover:underline">← Tous les plans</Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Résumé -->
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-400">Résumé</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">Total</dt><dd class="font-semibold">{{ fmt(plan.total_amount) }} {{ plan.currency }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Facturé</dt><dd class="font-semibold text-green-600">{{ fmt(plan.total_invoiced) }} {{ plan.currency }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Reste</dt><dd class="font-semibold text-red-600">{{ fmt(plan.remaining) }} {{ plan.currency }}</dd></div>
                        </dl>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-400">Client & source</h3>
                        <div class="text-sm">
                            <div class="font-semibold text-gray-800">{{ plan.customer?.name ?? '—' }}</div>
                            <div v-if="plan.source_document" class="mt-2">
                                <Link :href="route('documents.show', plan.source_document.id)" class="text-brand-600 hover:underline">
                                    Document source : {{ plan.source_document.number }}
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-400">Progression</h3>
                        <div class="mb-1 text-right text-xs text-gray-400">{{ progress }}%</div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full bg-brand-600" :style="{ width: progress + '%' }"></div>
                        </div>
                        <button v-if="plan.status === 'active' && plan.total_invoiced === 0"
                            @click="cancelPlan"
                            class="mt-4 text-sm text-red-600 hover:underline">
                            Annuler le plan
                        </button>
                    </div>
                </div>

                <!-- Échéancier -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="border-b px-6 py-4"><h3 class="font-semibold text-gray-800">Échéancier</h3></div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Échéance</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3 text-right">Montant</th>
                                <th class="px-6 py-3 text-center">Statut</th>
                                <th class="px-6 py-3 text-right">Facture</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="inst in plan.installments" :key="inst.id">
                                <td class="px-6 py-3 font-medium text-gray-700">{{ inst.label }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ inst.due_date }}</td>
                                <td class="px-6 py-3 text-right font-semibold">{{ fmt(inst.amount) }} {{ plan.currency }}</td>
                                <td class="px-6 py-3 text-center">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="installmentStatusColors[inst.status]">
                                        {{ installmentStatusLabels[inst.status] ?? inst.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <Link v-if="inst.document" :href="route('documents.show', inst.document.id)"
                                        class="font-semibold text-brand-600 hover:underline">
                                        {{ inst.document.number }}
                                    </Link>
                                    <button v-else-if="plan.status !== 'cancelled'"
                                        @click="invoiceInstallment(inst)"
                                        class="rounded-md border border-brand-600 px-3 py-1 text-xs font-semibold text-brand-600 hover:bg-brand-50">
                                        Générer la facture
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';

const props = defineProps({
    hasAccess: Boolean,
    agents: { type: Array, default: () => [] },
    preview: { type: Object, default: null },
    customers: { type: Array, default: () => [] },
    payouts: { type: Array, default: () => [] },
    stats: { type: Object, default: null },
    period: { type: Object, default: () => ({}) },
    currency: { type: String, default: 'XOF' },
});

/* ---------------- Formatage (Intl fr-FR) ---------------- */
const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 }).format(Number(n ?? 0));
const fmtMoney = (n) => `${fmt(n)} ${props.currency}`;
const fmtRate = (n) => `${new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 }).format(Number(n ?? 0))} %`;
const fmtDate = (d) =>
    d ? new Date(d + 'T00:00:00').toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';

/* ---------------- Période ---------------- */
const periodForm = reactive({
    from: props.period?.from ?? '',
    to: props.period?.to ?? '',
});

const applyPeriod = () => {
    router.get(route('commissions.index'), { from: periodForm.from, to: periodForm.to }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

/* ---------------- Vendeurs (CRUD) ---------------- */
const showAgentModal = ref(false);
const editingAgentId = ref(null);

const agentForm = useForm({
    name: '',
    email: '',
    phone: '',
    commission_rate: 0,
    is_active: true,
    notes: '',
});

const openCreateAgent = () => {
    editingAgentId.value = null;
    agentForm.reset();
    agentForm.clearErrors();
    showAgentModal.value = true;
};

const openEditAgent = (agent) => {
    editingAgentId.value = agent.id;
    agentForm.clearErrors();
    agentForm.name = agent.name;
    agentForm.email = agent.email ?? '';
    agentForm.phone = agent.phone ?? '';
    agentForm.commission_rate = agent.commission_rate;
    agentForm.is_active = agent.is_active;
    agentForm.notes = agent.notes ?? '';
    showAgentModal.value = true;
};

const submitAgent = () => {
    const opts = {
        preserveScroll: true,
        onSuccess: () => {
            showAgentModal.value = false;
            agentForm.reset();
        },
    };
    if (editingAgentId.value) {
        agentForm.put(route('commissions.agents.update', editingAgentId.value), opts);
    } else {
        agentForm.post(route('commissions.agents.store'), opts);
    }
};

const deleteAgent = (agent) => {
    if (!confirm(`Supprimer le vendeur « ${agent.name} » ? Ses clients seront détachés.`)) return;
    router.delete(route('commissions.agents.destroy', agent.id), { preserveScroll: true });
};

/* ---------------- Affectation clients ---------------- */
// Sélection locale : id client -> id vendeur (ou '' pour non affecté).
const assignment = reactive({});
props.customers.forEach((c) => {
    assignment[c.id] = c.sales_agent_id ?? '';
});

const savingAssign = ref(false);

const saveAssignments = () => {
    // Regroupe les clients par vendeur choisi (on ignore « non affecté » ici).
    const groups = {};
    props.customers.forEach((c) => {
        const chosen = assignment[c.id];
        const current = c.sales_agent_id ?? '';
        if (chosen && String(chosen) !== String(current)) {
            (groups[chosen] ??= []).push(c.id);
        }
    });

    const agentIds = Object.keys(groups);
    if (!agentIds.length) return;

    savingAssign.value = true;
    let remaining = agentIds.length;
    agentIds.forEach((agentId) => {
        router.post(route('commissions.assign', agentId), { customer_ids: groups[agentId] }, {
            preserveScroll: true,
            preserveState: false,
            onFinish: () => {
                remaining -= 1;
                if (remaining <= 0) savingAssign.value = false;
            },
        });
    });
};

/* ---------------- Décomptes ---------------- */
const payoutForm = useForm({
    sales_agent_id: '',
    from: props.period?.from ?? '',
    to: props.period?.to ?? '',
    rate: '',
});

const generatePayout = (agent) => {
    payoutForm.clearErrors();
    payoutForm.sales_agent_id = agent ? agent.id : payoutForm.sales_agent_id;
    payoutForm.from = periodForm.from;
    payoutForm.to = periodForm.to;
    if (!payoutForm.sales_agent_id) return;
    payoutForm.post(route('commissions.payouts.generate'), {
        preserveScroll: true,
        onSuccess: () => {
            payoutForm.rate = '';
        },
    });
};

const markPaid = (payout) => {
    router.post(route('commissions.payouts.pay', payout.id), {}, { preserveScroll: true });
};

const activeAgents = computed(() => props.agents.filter((a) => a.is_active));

const STATUS_META = {
    pending: { label: 'À payer', class: 'bg-amber-100 text-amber-700' },
    paid: { label: 'Payée', class: 'bg-green-100 text-green-700' },
};
</script>

<template>
    <Head title="Commissions vendeurs" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Commissions vendeurs</h2>
                <div v-if="hasAccess" class="flex gap-2">
                    <PrimaryButton @click="openCreateAgent">➕ Vendeur</PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
                <!-- Upsell si forfait insuffisant -->
                <div v-if="!hasAccess" class="mx-auto max-w-2xl rounded-lg bg-white p-8 text-center shadow">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gold-400/20 text-3xl">🤝</div>
                    <h3 class="text-lg font-semibold text-brand-900">Commissions vendeurs disponibles à partir du forfait BUSINESS</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Gérez vos commerciaux, affectez-leur des clients et calculez automatiquement leurs
                        commissions sur les factures payées, avec suivi des décomptes et marquage « commission
                        payée » — avec les forfaits BUSINESS et ENTERPRISE.
                    </p>
                    <Link :href="route('billing.plans')" class="mt-6 inline-block">
                        <PrimaryButton>Voir les forfaits</PrimaryButton>
                    </Link>
                </div>

                <template v-else>
                    <!-- Cartes stats -->
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Vendeurs actifs</div>
                            <div class="mt-1 text-2xl font-bold text-brand-900">{{ stats.active_agents }}</div>
                            <div class="text-xs text-gray-400">Commerciaux en activité</div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">CA commissionnable (période)</div>
                            <div class="mt-1 text-2xl font-bold text-brand-900">{{ fmtMoney(stats.commissionable_month) }}</div>
                            <div class="text-xs text-gray-400">Factures payées des clients affectés</div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Commissions dues</div>
                            <div class="mt-1 text-2xl font-bold text-brand-900">{{ fmtMoney(stats.commissions_due) }}</div>
                            <div class="text-xs text-gray-400">Décomptes non encore payés</div>
                        </div>
                    </div>

                    <!-- Sélecteur de période -->
                    <div class="flex flex-wrap items-end gap-3 rounded-lg bg-white p-4 shadow">
                        <div>
                            <InputLabel for="from" value="Du" />
                            <TextInput id="from" v-model="periodForm.from" type="date" class="mt-1 block" />
                        </div>
                        <div>
                            <InputLabel for="to" value="Au" />
                            <TextInput id="to" v-model="periodForm.to" type="date" class="mt-1 block" />
                        </div>
                        <SecondaryButton @click="applyPeriod">Appliquer</SecondaryButton>
                    </div>

                    <!-- Vendeurs -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="border-b px-4 py-3 text-sm font-semibold text-brand-900">Vendeurs</div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-400">
                                    <tr>
                                        <th class="px-4 py-2">Nom</th>
                                        <th class="px-4 py-2 text-right">Taux</th>
                                        <th class="px-4 py-2 text-right">Clients</th>
                                        <th class="px-4 py-2 text-right">CA commissionnable</th>
                                        <th class="px-4 py-2 text-right">Commission estimée</th>
                                        <th class="px-4 py-2 text-center">Statut</th>
                                        <th class="px-4 py-2 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="agent in agents" :key="agent.id">
                                        <td class="px-4 py-2">
                                            <div class="font-medium text-gray-800">{{ agent.name }}</div>
                                            <div v-if="agent.email" class="text-xs text-gray-400">{{ agent.email }}</div>
                                        </td>
                                        <td class="px-4 py-2 text-right">{{ fmtRate(agent.commission_rate) }}</td>
                                        <td class="px-4 py-2 text-right">{{ agent.customers_count }}</td>
                                        <td class="px-4 py-2 text-right">{{ fmtMoney(agent.base) }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-brand-900">{{ fmtMoney(agent.commission) }}</td>
                                        <td class="px-4 py-2 text-center">
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                                :class="agent.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                            >{{ agent.is_active ? 'Actif' : 'Inactif' }}</span>
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="flex justify-end gap-2">
                                                <button class="text-xs text-brand-600 hover:underline" @click="generatePayout(agent)">Générer le décompte</button>
                                                <button class="text-xs text-gray-500 hover:underline" @click="openEditAgent(agent)">Éditer</button>
                                                <button class="text-xs text-red-600 hover:underline" @click="deleteAgent(agent)">Supprimer</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!agents.length">
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-400">Aucun vendeur enregistré.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Affectation clients -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="flex items-center justify-between border-b px-4 py-3">
                            <span class="text-sm font-semibold text-brand-900">Affectation des clients</span>
                            <PrimaryButton :disabled="savingAssign" @click="saveAssignments">Enregistrer</PrimaryButton>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-400">
                                    <tr>
                                        <th class="px-4 py-2">Client</th>
                                        <th class="px-4 py-2">Vendeur affecté</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="c in customers" :key="c.id">
                                        <td class="px-4 py-2 text-gray-800">{{ c.name }}</td>
                                        <td class="px-4 py-2">
                                            <select v-model="assignment[c.id]" class="rounded-md border-gray-300 text-sm">
                                                <option value="">— Non affecté —</option>
                                                <option v-for="a in agents" :key="a.id" :value="a.id">{{ a.name }}</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr v-if="!customers.length">
                                        <td colspan="2" class="px-4 py-6 text-center text-gray-400">Aucun client.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Décomptes de commission -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="border-b px-4 py-3 text-sm font-semibold text-brand-900">Décomptes de commission</div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-400">
                                    <tr>
                                        <th class="px-4 py-2">Vendeur</th>
                                        <th class="px-4 py-2">Période</th>
                                        <th class="px-4 py-2 text-right">Base</th>
                                        <th class="px-4 py-2 text-right">Taux</th>
                                        <th class="px-4 py-2 text-right">Commission</th>
                                        <th class="px-4 py-2 text-center">Statut</th>
                                        <th class="px-4 py-2 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="p in payouts" :key="p.id">
                                        <td class="px-4 py-2 text-gray-800">{{ p.agent?.name ?? '—' }}</td>
                                        <td class="px-4 py-2 text-gray-600">{{ fmtDate(p.period_start) }} → {{ fmtDate(p.period_end) }}</td>
                                        <td class="px-4 py-2 text-right">{{ fmtMoney(p.base_amount) }}</td>
                                        <td class="px-4 py-2 text-right">{{ fmtRate(p.rate) }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-brand-900">{{ fmtMoney(p.commission_amount) }}</td>
                                        <td class="px-4 py-2 text-center">
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                                :class="STATUS_META[p.status]?.class"
                                            >{{ STATUS_META[p.status]?.label ?? p.status }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <button
                                                v-if="p.status !== 'paid'"
                                                class="text-xs text-green-600 hover:underline"
                                                @click="markPaid(p)"
                                            >Marquer payé</button>
                                            <span v-else class="text-xs text-gray-400">Réglée le {{ fmtDate(p.paid_at) }}</span>
                                        </td>
                                    </tr>
                                    <tr v-if="!payouts.length">
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-400">Aucun décompte généré.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Modale vendeur -->
        <Modal :show="showAgentModal" @close="showAgentModal = false">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-brand-900">
                    {{ editingAgentId ? 'Modifier le vendeur' : 'Nouveau vendeur' }}
                </h2>

                <div class="mt-4 space-y-4">
                    <div>
                        <InputLabel for="agent-name" value="Nom" />
                        <TextInput id="agent-name" v-model="agentForm.name" type="text" class="mt-1 block w-full" />
                        <InputError :message="agentForm.errors.name" class="mt-1" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="agent-email" value="Email" />
                            <TextInput id="agent-email" v-model="agentForm.email" type="email" class="mt-1 block w-full" />
                            <InputError :message="agentForm.errors.email" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="agent-phone" value="Téléphone" />
                            <TextInput id="agent-phone" v-model="agentForm.phone" type="text" class="mt-1 block w-full" />
                            <InputError :message="agentForm.errors.phone" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel for="agent-rate" value="Taux de commission (%)" />
                            <TextInput id="agent-rate" v-model="agentForm.commission_rate" type="number" step="0.01" min="0" max="100" class="mt-1 block w-full" />
                            <InputError :message="agentForm.errors.commission_rate" class="mt-1" />
                        </div>
                        <label class="mt-6 inline-flex items-center gap-2">
                            <input v-model="agentForm.is_active" type="checkbox" class="rounded border-gray-300 text-brand-600" />
                            <span class="text-sm text-gray-700">Actif</span>
                        </label>
                    </div>
                    <div>
                        <InputLabel for="agent-notes" value="Notes" />
                        <textarea id="agent-notes" v-model="agentForm.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm"></textarea>
                        <InputError :message="agentForm.errors.notes" class="mt-1" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <SecondaryButton @click="showAgentModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="agentForm.processing" @click="submitAgent">Enregistrer</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

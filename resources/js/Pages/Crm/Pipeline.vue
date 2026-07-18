<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    hasAccess: Boolean,
    stages: Object,
    stats: Object,
    customers: Array,
});

const STAGE_LABELS = {
    prospect:    { label: 'Prospect',       color: 'bg-gray-100 border-gray-300 text-gray-700' },
    contacted:   { label: 'Contacté',       color: 'bg-blue-100 border-blue-300 text-blue-700' },
    qualified:   { label: 'Qualifié',       color: 'bg-yellow-100 border-yellow-300 text-yellow-700' },
    quote_sent:  { label: 'Devis envoyé',   color: 'bg-orange-100 border-orange-300 text-orange-700' },
    won:         { label: 'Gagné',          color: 'bg-green-100 border-green-300 text-green-700' },
    lost:        { label: 'Perdu',          color: 'bg-red-100 border-red-300 text-red-700' },
};

const STAGE_ORDER = ['prospect', 'contacted', 'qualified', 'quote_sent', 'won', 'lost'];

const SOURCE_BADGES = {
    website:   'bg-purple-100 text-purple-700',
    referral:  'bg-green-100 text-green-700',
    cold_call: 'bg-blue-100 text-blue-700',
    social:    'bg-pink-100 text-pink-700',
};

const showNewDeal = ref(false);
const newDealStage = ref('prospect');

const form = useForm({
    prospect_name: '',
    prospect_email: '',
    prospect_phone: '',
    customer_id: '',
    stage: 'prospect',
    value: '',
    probability: '',
    source: '',
    notes: '',
    expected_close_date: '',
});

const openNewDeal = (stage = 'prospect') => {
    newDealStage.value = stage;
    form.reset();
    form.stage = stage;
    showNewDeal.value = true;
};

const submitDeal = () => {
    form.post(route('crm.store'), {
        onSuccess: () => { showNewDeal.value = false; form.reset(); },
    });
};

const moveStage = (deal, direction) => {
    const idx = STAGE_ORDER.indexOf(deal.stage);
    const next = STAGE_ORDER[idx + direction];
    if (!next || next === 'won' || next === 'lost') return;
    router.post(route('crm.stage', deal.id), { stage: next }, { preserveScroll: true });
};

const fmt = (val) => val ? new Intl.NumberFormat('fr-FR').format(val) + ' XOF' : '—';
</script>

<template>
    <Head title="CRM Pipeline" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">CRM — Pipeline commercial</h2>
                <PrimaryButton v-if="hasAccess" @click="openNewDeal()">+ Nouveau deal</PrimaryButton>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">

                <!-- Accès refusé -->
                <div v-if="!hasAccess" class="rounded-lg bg-yellow-50 border border-yellow-200 p-8 text-center">
                    <p class="text-lg font-semibold text-yellow-800">Fonctionnalité BUSINESS+</p>
                    <p class="mt-2 text-sm text-yellow-700">Le CRM est disponible à partir du forfait Business. Mettez à niveau votre abonnement.</p>
                    <Link :href="route('billing.plans')" class="mt-4 inline-block rounded-md bg-yellow-600 px-4 py-2 text-sm text-white hover:bg-yellow-700">Voir les forfaits</Link>
                </div>

                <template v-else>
                    <!-- Stats -->
                    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div class="rounded-lg bg-white p-4 shadow text-center">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Pipeline total</p>
                            <p class="mt-1 text-xl font-bold text-brand-700">{{ fmt(stats.total_pipeline) }}</p>
                        </div>
                        <div class="rounded-lg bg-white p-4 shadow text-center">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Deals actifs</p>
                            <p class="mt-1 text-xl font-bold text-gray-800">{{ stats.active_count }}</p>
                        </div>
                        <div class="rounded-lg bg-white p-4 shadow text-center">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Taux de closing</p>
                            <p class="mt-1 text-xl font-bold text-green-700">{{ stats.closing_rate }}%</p>
                        </div>
                        <div class="rounded-lg bg-white p-4 shadow text-center">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Gagné ce mois</p>
                            <p class="mt-1 text-xl font-bold text-green-700">{{ fmt(stats.won_value_month) }}</p>
                        </div>
                    </div>

                    <!-- Kanban -->
                    <div class="flex gap-4 overflow-x-auto pb-4">
                        <div
                            v-for="stageKey in STAGE_ORDER"
                            :key="stageKey"
                            class="flex-shrink-0 w-72 rounded-lg bg-gray-50 border border-gray-200"
                        >
                            <!-- Header colonne -->
                            <div :class="['flex items-center justify-between px-3 py-2 border-b rounded-t-lg', STAGE_LABELS[stageKey].color]">
                                <span class="font-semibold text-sm">{{ STAGE_LABELS[stageKey].label }}</span>
                                <div class="flex items-center gap-2 text-xs">
                                    <span class="rounded-full bg-white bg-opacity-60 px-2 py-0.5 font-bold">
                                        {{ stages[stageKey]?.count ?? 0 }}
                                    </span>
                                    <span class="opacity-70">{{ fmt(stages[stageKey]?.total) }}</span>
                                </div>
                            </div>

                            <!-- Cartes -->
                            <div class="p-2 space-y-2 min-h-[80px]">
                                <div
                                    v-for="deal in stages[stageKey]?.deals"
                                    :key="deal.id"
                                    class="rounded-md bg-white shadow-sm border border-gray-100 p-3 text-sm"
                                >
                                    <div class="flex items-start justify-between gap-1">
                                        <p class="font-semibold text-gray-800 truncate">
                                            {{ deal.customer?.name ?? deal.prospect_name ?? 'Sans nom' }}
                                        </p>
                                        <Link :href="route('crm.show', deal.id)" class="text-brand-600 hover:text-brand-800 flex-shrink-0 text-base leading-none">→</Link>
                                    </div>
                                    <p v-if="deal.value" class="mt-1 text-green-700 font-medium">{{ fmt(deal.value) }}</p>
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        <span v-if="deal.source" :class="['rounded-full px-2 py-0.5 text-xs', SOURCE_BADGES[deal.source] ?? 'bg-gray-100 text-gray-600']">{{ deal.source }}</span>
                                        <span v-if="deal.probability !== null" class="rounded-full bg-gray-100 text-gray-600 px-2 py-0.5 text-xs">{{ deal.probability }}%</span>
                                    </div>
                                    <div v-if="deal.expected_close_date" class="mt-1 text-xs text-gray-400">
                                        Closing : {{ deal.expected_close_date }}
                                    </div>
                                    <div v-if="deal.assigned_to" class="mt-1 text-xs text-gray-400">
                                        {{ deal.assigned_to.name }}
                                    </div>
                                    <!-- Boutons déplacement -->
                                    <div v-if="!['won','lost'].includes(stageKey)" class="mt-2 flex gap-1">
                                        <button
                                            v-if="STAGE_ORDER.indexOf(stageKey) > 0"
                                            class="rounded bg-gray-100 px-2 py-0.5 text-xs hover:bg-gray-200"
                                            @click="moveStage(deal, -1)"
                                        >← Reculer</button>
                                        <button
                                            v-if="STAGE_ORDER.indexOf(stageKey) < 3"
                                            class="rounded bg-brand-50 text-brand-700 px-2 py-0.5 text-xs hover:bg-brand-100"
                                            @click="moveStage(deal, 1)"
                                        >Avancer →</button>
                                    </div>
                                </div>

                                <!-- Bouton ajouter dans colonne -->
                                <button
                                    v-if="!['won','lost'].includes(stageKey)"
                                    class="w-full text-left text-xs text-gray-400 hover:text-brand-600 px-1 py-1"
                                    @click="openNewDeal(stageKey)"
                                >+ Nouveau deal</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Modal création deal -->
        <Modal :show="showNewDeal" @close="showNewDeal = false" max-width="lg">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Nouveau deal</h3>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Étape</label>
                        <select v-model="form.stage" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option v-for="s in STAGE_ORDER" :key="s" :value="s">{{ STAGE_LABELS[s].label }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Client existant</label>
                        <select v-model="form.customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">— Prospect (nouveau) —</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>

                    <template v-if="!form.customer_id">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom prospect *</label>
                            <input v-model="form.prospect_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" />
                            <p v-if="form.errors.prospect_name" class="mt-1 text-xs text-red-600">{{ form.errors.prospect_name }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input v-model="form.prospect_email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Téléphone</label>
                                <input v-model="form.prospect_phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" />
                            </div>
                        </div>
                    </template>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Valeur estimée</label>
                            <input v-model="form.value" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Probabilité (%)</label>
                            <input v-model="form.probability" type="number" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Source</label>
                            <select v-model="form.source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="">—</option>
                                <option value="website">Site web</option>
                                <option value="referral">Référence</option>
                                <option value="cold_call">Appel froid</option>
                                <option value="social">Réseaux sociaux</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date closing</label>
                            <input v-model="form.expected_close_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-3">
                    <SecondaryButton @click="showNewDeal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing" @click="submitDeal">Créer le deal</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

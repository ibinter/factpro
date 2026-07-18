<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    deal: Object,
    activities: Array,
});

const tab = ref('details');

const STAGE_LABELS = {
    prospect:   { label: 'Prospect',     cls: 'bg-gray-100 text-gray-700' },
    contacted:  { label: 'Contacté',     cls: 'bg-blue-100 text-blue-700' },
    qualified:  { label: 'Qualifié',     cls: 'bg-yellow-100 text-yellow-700' },
    quote_sent: { label: 'Devis envoyé', cls: 'bg-orange-100 text-orange-700' },
    won:        { label: 'Gagné',        cls: 'bg-green-100 text-green-700' },
    lost:       { label: 'Perdu',        cls: 'bg-red-100 text-red-700' },
};

const ACTIVITY_ICONS = {
    note:             '📝',
    call:             '📞',
    email:            '📧',
    meeting:          '🤝',
    stage_change:     '🔀',
    document_created: '📄',
};

// Formulaires
const showLost = ref(false);
const showStage = ref(false);
const showEdit  = ref(false);

const activityForm = useForm({ type: 'note', content: '' });

const lostForm = useForm({ lost_reason: '' });

const stageForm = useForm({ stage: props.deal.stage });

const editForm = useForm({
    prospect_name:       props.deal.prospect_name ?? '',
    prospect_email:      props.deal.prospect_email ?? '',
    prospect_phone:      props.deal.prospect_phone ?? '',
    value:               props.deal.value ?? '',
    probability:         props.deal.probability ?? '',
    source:              props.deal.source ?? '',
    notes:               props.deal.notes ?? '',
    expected_close_date: props.deal.expected_close_date ?? '',
});

const markWon = () => {
    router.post(route('crm.won', props.deal.id), {}, { preserveScroll: true });
};

const markLost = () => {
    lostForm.post(route('crm.lost', props.deal.id), {
        preserveScroll: true,
        onSuccess: () => { showLost.value = false; },
    });
};

const changeStage = () => {
    stageForm.post(route('crm.stage', props.deal.id), {
        preserveScroll: true,
        onSuccess: () => { showStage.value = false; },
    });
};

const addActivity = () => {
    activityForm.post(route('crm.activities.store', props.deal.id), {
        preserveScroll: true,
        onSuccess: () => activityForm.reset(),
    });
};

const saveEdit = () => {
    editForm.put(route('crm.update', props.deal.id), {
        preserveScroll: true,
        onSuccess: () => { showEdit.value = false; },
    });
};

const convertToCustomer = () => {
    router.post(route('crm.won', props.deal.id), {}, { preserveScroll: true });
};

const fmt = (val) => val ? new Intl.NumberFormat('fr-FR').format(val) + ' XOF' : '—';

const displayName = props.deal.customer?.name ?? props.deal.prospect_name ?? 'Sans nom';
</script>

<template>
    <Head :title="'Deal — ' + displayName" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('crm.pipeline')" class="text-sm text-gray-500 hover:text-brand-600">← Pipeline</Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ displayName }}</h2>
                <span :class="['rounded-full px-3 py-0.5 text-xs font-semibold', STAGE_LABELS[deal.stage]?.cls]">
                    {{ STAGE_LABELS[deal.stage]?.label }}
                </span>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Actions -->
                <div class="flex flex-wrap gap-2">
                    <PrimaryButton @click="showEdit = true">Modifier</PrimaryButton>
                    <SecondaryButton @click="showStage = true">Changer étape</SecondaryButton>
                    <button
                        v-if="deal.stage !== 'won' && deal.stage !== 'lost'"
                        class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                        @click="markWon"
                    >Marquer Gagné</button>
                    <DangerButton
                        v-if="deal.stage !== 'won' && deal.stage !== 'lost'"
                        @click="showLost = true"
                    >Marquer Perdu</DangerButton>
                    <button
                        v-if="!deal.customer_id && deal.stage !== 'lost'"
                        class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700"
                        @click="convertToCustomer"
                    >Convertir en client</button>
                </div>

                <!-- Onglets -->
                <div class="border-b border-gray-200 flex gap-6">
                    <button v-for="t in ['details', 'activities', 'documents']" :key="t"
                        :class="['pb-2 text-sm font-medium border-b-2 transition', tab === t ? 'border-brand-600 text-brand-600' : 'border-transparent text-gray-500 hover:text-gray-700']"
                        @click="tab = t"
                    >
                        <span v-if="t === 'details'">Détails</span>
                        <span v-else-if="t === 'activities'">Activités</span>
                        <span v-else>Documents</span>
                    </button>
                </div>

                <!-- Détails -->
                <div v-if="tab === 'details'" class="rounded-lg bg-white shadow p-6 grid grid-cols-2 gap-4 text-sm">
                    <div><span class="text-gray-500">Valeur</span><p class="font-semibold text-green-700">{{ fmt(deal.value) }}</p></div>
                    <div><span class="text-gray-500">Probabilité</span><p class="font-semibold">{{ deal.probability !== null ? deal.probability + '%' : '—' }}</p></div>
                    <div><span class="text-gray-500">Source</span><p class="font-semibold">{{ deal.source ?? '—' }}</p></div>
                    <div><span class="text-gray-500">Date closing</span><p class="font-semibold">{{ deal.expected_close_date ?? '—' }}</p></div>
                    <div><span class="text-gray-500">Commercial</span><p class="font-semibold">{{ deal.assigned_to?.name ?? '—' }}</p></div>
                    <div v-if="deal.customer"><span class="text-gray-500">Client</span><p class="font-semibold">{{ deal.customer.name }}</p></div>
                    <div v-else>
                        <span class="text-gray-500">Prospect</span>
                        <p class="font-semibold">{{ deal.prospect_name }}</p>
                        <p class="text-xs text-gray-400">{{ deal.prospect_email }} · {{ deal.prospect_phone }}</p>
                    </div>
                    <div v-if="deal.notes" class="col-span-2"><span class="text-gray-500">Notes</span><p class="mt-1 whitespace-pre-wrap">{{ deal.notes }}</p></div>
                    <div v-if="deal.lost_reason" class="col-span-2"><span class="text-gray-500">Raison perte</span><p class="mt-1 text-red-600">{{ deal.lost_reason }}</p></div>
                </div>

                <!-- Activités -->
                <div v-if="tab === 'activities'" class="space-y-4">
                    <!-- Formulaire ajout -->
                    <div class="rounded-lg bg-white shadow p-4">
                        <h3 class="mb-3 text-sm font-semibold text-gray-700">Ajouter une activité</h3>
                        <div class="flex gap-3 mb-2">
                            <select v-model="activityForm.type" class="rounded-md border-gray-300 shadow-sm text-sm">
                                <option value="note">Note</option>
                                <option value="call">Appel</option>
                                <option value="email">Email</option>
                                <option value="meeting">Réunion</option>
                            </select>
                        </div>
                        <textarea v-model="activityForm.content" rows="2" placeholder="Contenu de l'activité…"
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm"
                        ></textarea>
                        <p v-if="activityForm.errors.content" class="mt-1 text-xs text-red-600">{{ activityForm.errors.content }}</p>
                        <div class="mt-2 flex justify-end">
                            <PrimaryButton :disabled="activityForm.processing" @click="addActivity">Enregistrer</PrimaryButton>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="space-y-3">
                        <div v-for="act in activities" :key="act.id"
                            class="flex gap-3 rounded-lg bg-white shadow-sm border border-gray-100 p-3"
                        >
                            <span class="text-xl flex-shrink-0">{{ ACTIVITY_ICONS[act.type] ?? '📌' }}</span>
                            <div class="flex-1 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-gray-700">{{ act.user?.name }}</span>
                                    <span class="text-xs text-gray-400">{{ act.created_at }}</span>
                                </div>
                                <p class="mt-0.5 text-gray-600">{{ act.content }}</p>
                                <p v-if="act.metadata?.from" class="mt-0.5 text-xs text-gray-400">
                                    {{ act.metadata.from }} → {{ act.metadata.to }}
                                </p>
                            </div>
                        </div>
                        <p v-if="!activities.length" class="text-center text-sm text-gray-400 py-6">Aucune activité enregistrée.</p>
                    </div>
                </div>

                <!-- Documents -->
                <div v-if="tab === 'documents'" class="rounded-lg bg-white shadow p-6 text-sm">
                    <div v-if="deal.document">
                        <p class="font-semibold">Document associé :</p>
                        <Link :href="route('documents.show', deal.document.id)" class="text-brand-600 hover:underline">
                            {{ deal.document.number }} — {{ deal.document.type_label }}
                        </Link>
                    </div>
                    <p v-else class="text-gray-400 text-center py-6">Aucun document associé à ce deal.</p>
                </div>

            </div>
        </div>

        <!-- Modal Marquer perdu -->
        <Modal :show="showLost" @close="showLost = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Marquer comme Perdu</h3>
                <label class="block text-sm text-gray-700 mb-1">Raison (optionnelle)</label>
                <textarea v-model="lostForm.lost_reason" rows="3" class="w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                <div class="mt-4 flex justify-end gap-3">
                    <SecondaryButton @click="showLost = false">Annuler</SecondaryButton>
                    <DangerButton :disabled="lostForm.processing" @click="markLost">Confirmer</DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Modal Changer étape -->
        <Modal :show="showStage" @close="showStage = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Changer l'étape</h3>
                <select v-model="stageForm.stage" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                    <option v-for="(meta, key) in STAGE_LABELS" :key="key" :value="key">{{ meta.label }}</option>
                </select>
                <div class="mt-4 flex justify-end gap-3">
                    <SecondaryButton @click="showStage = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="stageForm.processing" @click="changeStage">Appliquer</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modal Modifier -->
        <Modal :show="showEdit" @close="showEdit = false" max-width="lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Modifier le deal</h3>
                <div class="space-y-3 text-sm">
                    <div v-if="!deal.customer_id">
                        <label class="block text-gray-700 font-medium">Nom prospect</label>
                        <input v-model="editForm.prospect_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <input v-model="editForm.prospect_email" type="email" placeholder="Email" class="rounded-md border-gray-300 shadow-sm" />
                            <input v-model="editForm.prospect_phone" type="text" placeholder="Téléphone" class="rounded-md border-gray-300 shadow-sm" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-gray-700 font-medium">Valeur</label>
                            <input v-model="editForm.value" type="number" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium">Probabilité (%)</label>
                            <input v-model="editForm.probability" type="number" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-gray-700 font-medium">Source</label>
                            <select v-model="editForm.source" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">—</option>
                                <option value="website">Site web</option>
                                <option value="referral">Référence</option>
                                <option value="cold_call">Appel froid</option>
                                <option value="social">Réseaux sociaux</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-medium">Date closing</label>
                            <input v-model="editForm.expected_close_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium">Notes</label>
                        <textarea v-model="editForm.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    <SecondaryButton @click="showEdit = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="editForm.processing" @click="saveEdit">Enregistrer</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

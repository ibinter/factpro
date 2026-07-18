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
import { ref } from 'vue';

const props = defineProps({
    hasAccess: Boolean,
    projects: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ active_projects: 0, month_minutes: 0, unbilled_amount: 0 }) },
    filters: { type: Object, default: () => ({}) },
    currency: { type: String, default: 'XOF' },
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);
const fmtHours = (minutes) => {
    const h = Math.floor((minutes ?? 0) / 60);
    const m = (minutes ?? 0) % 60;
    return `${h}h${String(m).padStart(2, '0')}`;
};

const STATUS_LABELS = {
    active: 'Actif',
    paused: 'En pause',
    completed: 'Terminé',
    archived: 'Archivé',
};
const STATUS_CLASSES = {
    active: 'bg-green-100 text-green-700',
    paused: 'bg-amber-100 text-amber-700',
    completed: 'bg-blue-100 text-blue-700',
    archived: 'bg-gray-100 text-gray-500',
};

/* ---- Filtre statut ---- */
const statusFilter = ref(props.filters.status ?? '');
const applyFilter = () => {
    router.get(route('projects.index'), statusFilter.value ? { status: statusFilter.value } : {}, {
        preserveState: true,
        replace: true,
    });
};

/* ---- Avancement heures vs budget ---- */
const hoursPct = (project) => {
    if (!project.budget_hours || Number(project.budget_hours) <= 0) return null;
    return Math.round((project.total_minutes / 60 / Number(project.budget_hours)) * 100);
};

/* ---- Modale création / édition ---- */
const showModal = ref(false);
const editing = ref(null);
const confirmingDelete = ref(null);

const form = useForm({
    name: '',
    description: '',
    customer_id: null,
    status: 'active',
    hourly_rate: null,
    budget_hours: null,
    budget_amount: null,
    currency: props.currency,
    starts_at: null,
    ends_at: null,
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.currency = props.currency;
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (project) => {
    editing.value = project;
    form.name = project.name;
    form.description = project.description ?? '';
    form.customer_id = project.customer_id;
    form.status = project.status;
    form.hourly_rate = project.hourly_rate;
    form.budget_hours = project.budget_hours;
    form.budget_amount = project.budget_amount;
    form.currency = project.currency ?? props.currency;
    form.starts_at = project.starts_at;
    form.ends_at = project.ends_at;
    form.clearErrors();
    showModal.value = true;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => { showModal.value = false; form.reset(); },
    };
    if (editing.value) {
        form.put(route('projects.update', editing.value.id), options);
    } else {
        form.post(route('projects.store'), options);
    }
};

const destroy = () => {
    router.delete(route('projects.destroy', confirmingDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => (confirmingDelete.value = null),
    });
};
</script>

<template>
    <Head title="Projets & temps" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Projets & suivi du temps</h2>
                <PrimaryButton v-if="hasAccess" @click="openCreate">+ Nouveau projet</PrimaryButton>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Upsell si forfait insuffisant -->
                <div v-if="!hasAccess" class="mx-auto max-w-2xl rounded-lg bg-white p-8 text-center shadow">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gold-400/20 text-3xl">⏱️</div>
                    <h3 class="text-lg font-semibold text-brand-900">Projets & suivi du temps disponibles à partir du forfait BUSINESS</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Créez des projets clients avec budget, saisissez vos heures (manuellement ou au chronomètre),
                        suivez l'avancement et convertissez le temps passé en facture en un clic,
                        avec les forfaits BUSINESS et ENTERPRISE.
                    </p>
                    <Link :href="route('billing.plans')" class="mt-6 inline-block">
                        <PrimaryButton>Voir les forfaits</PrimaryButton>
                    </Link>
                </div>

                <template v-else>
                    <!-- Cartes statistiques -->
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Projets actifs</div>
                            <div class="mt-1 text-3xl font-bold text-brand-900">{{ stats.active_projects }}</div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Heures ce mois</div>
                            <div class="mt-1 text-3xl font-bold text-brand-900">{{ fmtHours(stats.month_minutes) }}</div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">À facturer</div>
                            <div class="mt-1 text-3xl font-bold text-gold-400">{{ fmt(stats.unbilled_amount) }} <span class="text-base text-gray-400">{{ currency }}</span></div>
                        </div>
                    </div>

                    <!-- Filtre statut -->
                    <div class="flex items-center gap-3">
                        <select
                            v-model="statusFilter"
                            @change="applyFilter"
                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        >
                            <option value="">Tous les statuts</option>
                            <option v-for="(label, key) in STATUS_LABELS" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <span class="text-sm text-gray-500">{{ projects.length }} projet(s)</span>
                    </div>

                    <!-- Tableau des projets -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Projet</th>
                                    <th class="px-6 py-3">Client</th>
                                    <th class="px-6 py-3">Statut</th>
                                    <th class="px-6 py-3">Heures / budget</th>
                                    <th class="px-6 py-3 text-right">À facturer</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="project in projects" :key="project.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <Link :href="route('projects.show', project.id)" class="font-semibold text-brand-600 hover:underline">
                                            {{ project.name }}
                                        </Link>
                                        <div v-if="project.hourly_rate" class="text-xs text-gray-400">
                                            {{ fmt(project.hourly_rate) }} {{ project.currency }}/h
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-gray-600">{{ project.customer?.name ?? '—' }}</td>
                                    <td class="px-6 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="STATUS_CLASSES[project.status]">
                                            {{ STATUS_LABELS[project.status] ?? project.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="font-medium text-gray-800">
                                            {{ fmtHours(project.total_minutes) }}
                                            <span v-if="project.budget_hours" class="text-gray-400">/ {{ Number(project.budget_hours) }}h</span>
                                        </div>
                                        <div v-if="hoursPct(project) !== null" class="mt-1 h-1.5 w-32 overflow-hidden rounded-full bg-gray-200">
                                            <div
                                                class="h-full rounded-full"
                                                :class="hoursPct(project) > 100 ? 'bg-red-500' : 'bg-brand-600'"
                                                :style="{ width: Math.min(100, hoursPct(project)) + '%' }"
                                            ></div>
                                        </div>
                                        <div v-if="hoursPct(project) !== null && hoursPct(project) > 100" class="mt-0.5 text-xs font-semibold text-red-600">
                                            Budget dépassé ({{ hoursPct(project) }}%)
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-right font-semibold" :class="project.unbilled_amount > 0 ? 'text-gray-800' : 'text-gray-400'">
                                        {{ fmt(project.unbilled_amount) }} {{ project.currency }}
                                    </td>
                                    <td class="px-6 py-3 text-right whitespace-nowrap">
                                        <Link :href="route('projects.show', project.id)" class="text-sm font-semibold text-brand-600 hover:underline">Ouvrir</Link>
                                        <button class="ml-3 text-sm font-semibold text-gray-600 hover:underline" @click="openEdit(project)">Modifier</button>
                                        <button class="ml-3 text-sm font-semibold text-red-500 hover:underline" @click="confirmingDelete = project">Supprimer</button>
                                    </td>
                                </tr>
                                <tr v-if="!projects.length">
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                        Aucun projet. Créez votre premier projet pour commencer à suivre votre temps.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>
        </div>

        <!-- Modale création / édition -->
        <Modal :show="showModal" @close="showModal = false" max-width="2xl">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">
                    {{ editing ? 'Modifier le projet' : 'Nouveau projet' }}
                </h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <InputLabel value="Nom du projet *" />
                        <TextInput v-model="form.name" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Client" />
                        <select v-model="form.customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option :value="null">— Aucun client —</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <InputError :message="form.errors.customer_id" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Statut" />
                        <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option v-for="(label, key) in STATUS_LABELS" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel value="Description" />
                        <textarea v-model="form.description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                    </div>
                    <div>
                        <InputLabel :value="`Taux horaire (${form.currency}/h)`" />
                        <TextInput v-model="form.hourly_rate" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                        <InputError :message="form.errors.hourly_rate" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Devise" />
                        <TextInput v-model="form.currency" maxlength="3" class="mt-1 block w-full uppercase" />
                        <InputError :message="form.errors.currency" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Budget (heures)" />
                        <TextInput v-model="form.budget_hours" type="number" step="0.5" min="0" class="mt-1 block w-full" />
                        <InputError :message="form.errors.budget_hours" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Budget (montant)" />
                        <TextInput v-model="form.budget_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                        <InputError :message="form.errors.budget_amount" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Date de début" />
                        <TextInput v-model="form.starts_at" type="date" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="Date de fin" />
                        <TextInput v-model="form.ends_at" type="date" class="mt-1 block w-full" />
                        <InputError :message="form.errors.ends_at" class="mt-1" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing" @click="submit">
                        {{ editing ? 'Enregistrer' : 'Créer le projet' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Confirmation suppression -->
        <Modal :show="!!confirmingDelete" @close="confirmingDelete = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">Supprimer ce projet ?</h3>
                <p class="mt-2 text-sm text-gray-500">
                    « {{ confirmingDelete?.name }} » et ses entrées de temps ne seront plus visibles.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingDelete = null">Annuler</SecondaryButton>
                    <DangerButton @click="destroy">Supprimer</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

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
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    project: Object,
    entries: Object, // paginator
    totals: Object,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);
const fmtHours = (minutes) => {
    const h = Math.floor((minutes ?? 0) / 60);
    const m = (minutes ?? 0) % 60;
    return `${h}h${String(m).padStart(2, '0')}`;
};
const fmtDate = (d) => (d ? new Intl.DateTimeFormat('fr-FR').format(new Date(d)) : '—');

const STATUS_LABELS = { active: 'Actif', paused: 'En pause', completed: 'Terminé', archived: 'Archivé' };
const STATUS_CLASSES = {
    active: 'bg-green-100 text-green-700',
    paused: 'bg-amber-100 text-amber-700',
    completed: 'bg-blue-100 text-blue-700',
    archived: 'bg-gray-100 text-gray-500',
};

/* ============================================================
 * CHRONOMÈTRE — persiste dans localStorage (survit au refresh)
 * ============================================================ */
const timerKey = `factpro_timer_${props.project.id}`;
const timerStart = ref(null); // timestamp ms
const nowTick = ref(Date.now());
let interval = null;

const startTicking = () => {
    clearInterval(interval);
    interval = setInterval(() => (nowTick.value = Date.now()), 1000);
};

onMounted(() => {
    const saved = localStorage.getItem(timerKey);
    if (saved && !Number.isNaN(parseInt(saved))) {
        timerStart.value = parseInt(saved);
        startTicking();
    }
});
onBeforeUnmount(() => clearInterval(interval));

const timerRunning = computed(() => timerStart.value !== null);
const elapsedSeconds = computed(() =>
    timerRunning.value ? Math.max(0, Math.floor((nowTick.value - timerStart.value) / 1000)) : 0,
);
const timerDisplay = computed(() => {
    const s = elapsedSeconds.value;
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = s % 60;
    return h > 0
        ? `${h}:${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`
        : `${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
});

const startTimer = () => {
    timerStart.value = Date.now();
    nowTick.value = Date.now();
    localStorage.setItem(timerKey, String(timerStart.value));
    startTicking();
};

const stopTimer = () => {
    // Durée arrondie à la minute supérieure, minimum 1 minute.
    const minutes = Math.max(1, Math.ceil(elapsedSeconds.value / 60));
    clearInterval(interval);
    localStorage.removeItem(timerKey);
    timerStart.value = null;
    openEntryModal(null, minutes);
};

/* ============================================================
 * Saisie / édition d'une entrée de temps
 * ============================================================ */
const showEntryModal = ref(false);
const editingEntry = ref(null);
const durationMode = ref('hm'); // 'hm' = heures:minutes, 'min' = minutes

const entryForm = useForm({
    description: '',
    entry_date: new Date().toISOString().slice(0, 10),
    hours: 0,
    minutes: 0,
    duration_minutes: null,
    hourly_rate: null,
    is_billable: true,
});

const openEntryModal = (entry = null, prefillMinutes = null) => {
    editingEntry.value = entry;
    entryForm.reset();
    entryForm.clearErrors();
    entryForm.entry_date = new Date().toISOString().slice(0, 10);
    durationMode.value = 'hm';

    if (entry) {
        entryForm.description = entry.description;
        entryForm.entry_date = entry.entry_date;
        entryForm.hours = Math.floor(entry.duration_minutes / 60);
        entryForm.minutes = entry.duration_minutes % 60;
        entryForm.hourly_rate = entry.hourly_rate;
        entryForm.is_billable = entry.is_billable;
    } else if (prefillMinutes) {
        entryForm.hours = Math.floor(prefillMinutes / 60);
        entryForm.minutes = prefillMinutes % 60;
    }
    showEntryModal.value = true;
};

const computedMinutes = computed(() => {
    if (durationMode.value === 'min') {
        return Math.max(0, parseInt(entryForm.duration_minutes) || 0);
    }
    return Math.max(0, (parseInt(entryForm.hours) || 0) * 60 + (parseInt(entryForm.minutes) || 0));
});

const submitEntry = () => {
    const payload = entryForm.transform((data) => ({
        description: data.description,
        entry_date: data.entry_date,
        duration_minutes: computedMinutes.value,
        hourly_rate: data.hourly_rate === '' ? null : data.hourly_rate,
        is_billable: data.is_billable,
    }));

    const options = {
        preserveScroll: true,
        onSuccess: () => { showEntryModal.value = false; entryForm.reset(); },
    };

    if (editingEntry.value) {
        payload.put(route('projects.entries.update', editingEntry.value.id), options);
    } else {
        payload.post(route('projects.entries.store', props.project.id), options);
    }
};

/* ---- Suppression d'une entrée ---- */
const confirmingEntryDelete = ref(null);
const destroyEntry = () => {
    router.delete(route('projects.entries.destroy', confirmingEntryDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => (confirmingEntryDelete.value = null),
    });
};

/* ============================================================
 * Sélection & facturation
 * ============================================================ */
const selected = reactive({}); // entry id -> bool
const selectableEntries = computed(() => props.entries.data.filter((e) => e.is_billable && !e.is_billed));

const allChecked = computed({
    get: () => selectableEntries.value.length > 0 && selectableEntries.value.every((e) => selected[e.id]),
    set: (value) => selectableEntries.value.forEach((e) => (selected[e.id] = value)),
});

const selectedEntries = computed(() => selectableEntries.value.filter((e) => selected[e.id]));
const selectedAmount = computed(() => selectedEntries.value.reduce((sum, e) => sum + Number(e.amount || 0), 0));

const confirmingInvoice = ref(false);
const invoicing = ref(false);
const invoiceError = ref('');

const submitInvoice = () => {
    invoicing.value = true;
    invoiceError.value = '';
    router.post(
        route('projects.invoice', props.project.id),
        { entry_ids: selectedEntries.value.map((e) => e.id) },
        {
            onError: (errors) => {
                invoiceError.value = errors.entry_ids || 'Erreur lors de la facturation.';
            },
            onFinish: () => {
                invoicing.value = false;
                confirmingInvoice.value = false;
            },
        },
    );
};

/* ---- Avancement budget ---- */
const barClass = (pct) => (pct > 100 ? 'bg-red-500' : 'bg-brand-600');
</script>

<template>
    <Head :title="`Projet — ${project.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link :href="route('projects.index')" class="text-sm font-semibold text-brand-600 hover:underline">← Projets</Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ project.name }}</h2>
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="STATUS_CLASSES[project.status]">
                        {{ STATUS_LABELS[project.status] ?? project.status }}
                    </span>
                </div>
                <PrimaryButton @click="openEntryModal()">+ Saisie manuelle</PrimaryButton>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- En-tête projet + chronomètre -->
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="rounded-lg bg-white p-5 shadow lg:col-span-2">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Client</div>
                                <div class="mt-1 font-semibold text-gray-800">{{ project.customer?.name ?? '— Aucun client —' }}</div>
                                <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Taux horaire par défaut</div>
                                <div class="mt-1 font-semibold text-gray-800">
                                    {{ project.hourly_rate ? `${fmt(project.hourly_rate)} ${project.currency}/h` : '—' }}
                                </div>
                                <div class="mt-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Période</div>
                                <div class="mt-1 text-sm text-gray-600">{{ fmtDate(project.starts_at) }} → {{ fmtDate(project.ends_at) }}</div>
                            </div>
                            <div class="space-y-4">
                                <!-- Budget heures -->
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        <span>Heures : {{ fmtHours(totals.total_minutes) }}<template v-if="project.budget_hours"> / {{ Number(project.budget_hours) }}h</template></span>
                                        <span v-if="totals.hours_pct !== null" :class="totals.hours_over_budget ? 'text-red-600' : ''">{{ totals.hours_pct }}%</span>
                                    </div>
                                    <div v-if="totals.hours_pct !== null" class="mt-1 h-2 overflow-hidden rounded-full bg-gray-200">
                                        <div class="h-full rounded-full" :class="barClass(totals.hours_pct)" :style="{ width: Math.min(100, totals.hours_pct) + '%' }"></div>
                                    </div>
                                    <p v-if="totals.hours_over_budget" class="mt-1 text-xs font-semibold text-red-600">⚠ Budget heures dépassé</p>
                                </div>
                                <!-- Budget montant -->
                                <div>
                                    <div class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        <span>Montant : {{ fmt(totals.total_billable_amount) }}<template v-if="project.budget_amount"> / {{ fmt(project.budget_amount) }}</template> {{ project.currency }}</span>
                                        <span v-if="totals.amount_pct !== null" :class="totals.amount_over_budget ? 'text-red-600' : ''">{{ totals.amount_pct }}%</span>
                                    </div>
                                    <div v-if="totals.amount_pct !== null" class="mt-1 h-2 overflow-hidden rounded-full bg-gray-200">
                                        <div class="h-full rounded-full" :class="barClass(totals.amount_pct)" :style="{ width: Math.min(100, totals.amount_pct) + '%' }"></div>
                                    </div>
                                    <p v-if="totals.amount_over_budget" class="mt-1 text-xs font-semibold text-red-600">⚠ Budget montant dépassé</p>
                                </div>
                                <div class="rounded-md bg-gray-50 p-3 text-sm">
                                    <div class="flex justify-between"><span class="text-gray-500">À facturer</span><span class="font-bold text-gray-800">{{ fmt(totals.unbilled_amount) }} {{ project.currency }}</span></div>
                                    <div class="mt-1 flex justify-between text-xs"><span class="text-gray-400">soit</span><span class="text-gray-500">{{ fmtHours(totals.unbilled_minutes) }}</span></div>
                                </div>
                            </div>
                        </div>
                        <p v-if="project.description" class="mt-4 border-t pt-3 text-sm text-gray-600">{{ project.description }}</p>
                    </div>

                    <!-- Chronomètre -->
                    <div class="flex flex-col items-center justify-center rounded-lg bg-brand-900 p-6 text-white shadow">
                        <div class="text-xs font-semibold uppercase tracking-wide text-white/60">Chronomètre</div>
                        <div class="mt-2 font-mono text-5xl font-bold tabular-nums" :class="timerRunning ? 'text-gold-400' : 'text-white/40'">
                            {{ timerRunning ? timerDisplay : '00:00' }}
                        </div>
                        <button
                            v-if="!timerRunning"
                            type="button"
                            class="mt-5 w-full rounded-md bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-500"
                            @click="startTimer"
                        >
                            ▶ Démarrer
                        </button>
                        <button
                            v-else
                            type="button"
                            class="mt-5 w-full rounded-md bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500"
                            @click="stopTimer"
                        >
                            ⏹ Arrêter
                        </button>
                        <p class="mt-3 text-center text-xs text-white/50">Le chrono survit au rafraîchissement de la page.</p>
                    </div>
                </div>

                <!-- Barre de facturation -->
                <div
                    v-if="selectedEntries.length"
                    class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-brand-900 px-5 py-3 text-white shadow"
                >
                    <span class="text-sm">
                        <span class="font-bold text-gold-400">{{ selectedEntries.length }}</span> entrée(s) sélectionnée(s) —
                        <span class="font-bold">{{ fmt(selectedAmount) }} {{ project.currency }}</span>
                    </span>
                    <button
                        type="button"
                        class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold transition hover:bg-brand-500"
                        @click="confirmingInvoice = true"
                    >
                        🧾 Facturer la sélection
                    </button>
                </div>

                <!-- Tableau des entrées -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="w-10 px-4 py-3">
                                    <input v-if="selectableEntries.length" type="checkbox" v-model="allChecked" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                </th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Description</th>
                                <th class="px-4 py-3">Par</th>
                                <th class="px-4 py-3 text-right">Durée</th>
                                <th class="px-4 py-3 text-right">Taux</th>
                                <th class="px-4 py-3 text-right">Montant</th>
                                <th class="px-4 py-3">Statut</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="entry in entries.data" :key="entry.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <input
                                        v-if="entry.is_billable && !entry.is_billed"
                                        type="checkbox"
                                        v-model="selected[entry.id]"
                                        class="rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                                    />
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-600">{{ fmtDate(entry.entry_date) }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ entry.description }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ entry.user?.name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-medium tabular-nums">{{ fmtHours(entry.duration_minutes) }}</td>
                                <td class="px-4 py-3 text-right text-gray-500">{{ entry.effective_rate ? fmt(entry.effective_rate) : '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ fmt(entry.amount) }}</td>
                                <td class="px-4 py-3">
                                    <span v-if="!entry.is_billable" class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-500">Non facturable</span>
                                    <span v-else-if="entry.is_billed" class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">Facturé</span>
                                    <span v-else class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">À facturer</span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <template v-if="!entry.is_billed">
                                        <button class="text-sm font-semibold text-brand-600 hover:underline" @click="openEntryModal(entry)">Éditer</button>
                                        <button class="ml-3 text-sm font-semibold text-red-500 hover:underline" @click="confirmingEntryDelete = entry">Supprimer</button>
                                    </template>
                                    <span v-else class="text-xs text-gray-400">verrouillé</span>
                                </td>
                            </tr>
                            <tr v-if="!entries.data.length">
                                <td colspan="9" class="px-4 py-10 text-center text-gray-400">
                                    Aucune entrée de temps. Lancez le chronomètre ou faites une saisie manuelle.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="entries.links && entries.links.length > 3" class="flex flex-wrap gap-1">
                    <template v-for="link in entries.links" :key="link.label">
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

        <!-- Modale saisie / édition d'entrée -->
        <Modal :show="showEntryModal" @close="showEntryModal = false" max-width="lg">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">
                    {{ editingEntry ? "Modifier l'entrée de temps" : 'Nouvelle entrée de temps' }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <InputLabel value="Description *" />
                        <TextInput v-model="entryForm.description" class="mt-1 block w-full" placeholder="Ex. : Maquettes page d'accueil" required />
                        <InputError :message="entryForm.errors.description" class="mt-1" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Date *" />
                            <TextInput v-model="entryForm.entry_date" type="date" class="mt-1 block w-full" required />
                            <InputError :message="entryForm.errors.entry_date" class="mt-1" />
                        </div>
                        <div>
                            <div class="flex items-center justify-between">
                                <InputLabel value="Durée *" />
                                <button type="button" class="text-xs font-semibold text-brand-600 hover:underline" @click="durationMode = durationMode === 'hm' ? 'min' : 'hm'">
                                    {{ durationMode === 'hm' ? 'Saisir en minutes' : 'Saisir en h : min' }}
                                </button>
                            </div>
                            <div v-if="durationMode === 'hm'" class="mt-1 flex items-center gap-2">
                                <TextInput v-model="entryForm.hours" type="number" min="0" max="24" class="w-full" />
                                <span class="text-sm text-gray-500">h</span>
                                <TextInput v-model="entryForm.minutes" type="number" min="0" max="59" class="w-full" />
                                <span class="text-sm text-gray-500">min</span>
                            </div>
                            <TextInput v-else v-model="entryForm.duration_minutes" type="number" min="1" max="1440" class="mt-1 block w-full" placeholder="Minutes" />
                            <InputError :message="entryForm.errors.duration_minutes" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel :value="`Taux horaire (optionnel, ${project.currency}/h)`" />
                            <TextInput v-model="entryForm.hourly_rate" type="number" step="0.01" min="0" class="mt-1 block w-full" :placeholder="project.hourly_rate ? `Défaut projet : ${fmt(project.hourly_rate)}` : 'Taux du projet'" />
                            <InputError :message="entryForm.errors.hourly_rate" class="mt-1" />
                        </div>
                        <label class="flex items-center gap-2 self-end pb-2 text-sm text-gray-700">
                            <input type="checkbox" v-model="entryForm.is_billable" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                            Facturable
                        </label>
                    </div>
                    <p class="text-xs text-gray-500">Durée retenue : <span class="font-semibold">{{ fmtHours(computedMinutes) }}</span></p>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showEntryModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="entryForm.processing || computedMinutes < 1" @click="submitEntry">
                        {{ editingEntry ? 'Enregistrer' : 'Ajouter' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Confirmation suppression entrée -->
        <Modal :show="!!confirmingEntryDelete" @close="confirmingEntryDelete = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">Supprimer cette entrée ?</h3>
                <p class="mt-2 text-sm text-gray-500">
                    « {{ confirmingEntryDelete?.description }} » ({{ fmtHours(confirmingEntryDelete?.duration_minutes) }}) sera supprimée définitivement.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingEntryDelete = null">Annuler</SecondaryButton>
                    <DangerButton @click="destroyEntry">Supprimer</DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Confirmation facturation -->
        <Modal :show="confirmingInvoice" @close="confirmingInvoice = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">🧾 Facturer la sélection ?</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Une facture sera créée pour <span class="font-semibold">{{ project.customer?.name ?? '—' }}</span> avec
                    <span class="font-semibold">{{ selectedEntries.length }} entrée(s)</span> de temps
                    ({{ fmtHours(selectedEntries.reduce((s, e) => s + e.duration_minutes, 0)) }}), soit
                    <span class="font-bold">{{ fmt(selectedAmount) }} {{ project.currency }}</span> HT.
                </p>
                <p v-if="!project.customer" class="mt-2 text-sm font-semibold text-red-600">
                    Ce projet n'a pas de client : associez d'abord un client au projet.
                </p>
                <p v-if="invoiceError" class="mt-2 text-sm font-semibold text-red-600">{{ invoiceError }}</p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingInvoice = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="invoicing || !project.customer" @click="submitInvoice">
                        {{ invoicing ? 'Création…' : 'Créer la facture' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

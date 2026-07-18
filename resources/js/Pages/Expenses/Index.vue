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
import { ref, watch } from 'vue';

const props = defineProps({
    hasAccess: Boolean,
    canReview: Boolean,
    expenses: Object,
    stats: Object,
    categories: Object,
    filters: Object,
    currency: { type: String, default: 'XOF' },
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 }).format(n ?? 0);
const fmtMoney = (n, c) => `${fmt(n)} ${c ?? props.currency}`;
const fmtDate = (d) =>
    d ? new Date(d + 'T00:00:00').toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';

const STATUS_META = {
    draft: { label: 'Brouillon', class: 'bg-gray-100 text-gray-600' },
    submitted: { label: 'À valider', class: 'bg-amber-100 text-amber-700' },
    approved: { label: 'Approuvée', class: 'bg-green-100 text-green-700' },
    rejected: { label: 'Rejetée', class: 'bg-red-100 text-red-700' },
    reimbursed: { label: 'Remboursée', class: 'bg-blue-100 text-blue-700' },
};

const CATEGORY_CLASSES = {
    transport: 'bg-sky-100 text-sky-700',
    repas: 'bg-orange-100 text-orange-700',
    hebergement: 'bg-violet-100 text-violet-700',
    fournitures: 'bg-teal-100 text-teal-700',
    carburant: 'bg-rose-100 text-rose-700',
    communication: 'bg-indigo-100 text-indigo-700',
    autre: 'bg-gray-100 text-gray-600',
};

/* ---------------- Filtres ---------------- */
const filterForm = ref({
    status: props.filters?.status ?? '',
    category: props.filters?.category ?? '',
    month: props.filters?.month ?? '',
});

let filterTimeout = null;
watch(filterForm, (value) => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        const params = Object.fromEntries(Object.entries(value).filter(([, v]) => v !== '' && v !== null));
        router.get(route('expenses.index'), params, { preserveState: true, replace: true });
    }, 300);
}, { deep: true });

/* ---------------- Création / édition ---------------- */
const showModal = ref(false);
const editingId = ref(null);

const form = useForm({
    category: '',
    description: '',
    amount: '',
    expense_date: new Date().toISOString().slice(0, 10),
    receipt: null,
    _method: 'POST',
});

const openCreate = () => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form._method = 'POST';
    showModal.value = true;
};

const openEdit = (expense) => {
    editingId.value = expense.id;
    form.clearErrors();
    form.category = expense.category;
    form.description = expense.description;
    form.amount = expense.amount;
    form.expense_date = expense.expense_date;
    form.receipt = null;
    form._method = 'PUT';
    showModal.value = true;
};

const onFileChange = (event) => {
    form.receipt = event.target.files[0] ?? null;
};

const submit = () => {
    const url = editingId.value ? route('expenses.update', editingId.value) : route('expenses.store');
    // POST + _method=PUT : Inertia bascule en FormData dès qu'un fichier est présent.
    form.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};

/* ---------------- Revue (approbation / rejet) ---------------- */
const approve = (expense) => {
    router.post(route('expenses.review', expense.id), { decision: 'approve' }, { preserveScroll: true });
};

const showRejectModal = ref(false);
const rejectForm = useForm({ decision: 'reject', note: '' });
const rejectTarget = ref(null);

const openReject = (expense) => {
    rejectTarget.value = expense;
    rejectForm.reset();
    rejectForm.clearErrors();
    rejectForm.decision = 'reject';
    showRejectModal.value = true;
};

const submitReject = () => {
    if (!rejectTarget.value) return;
    rejectForm.post(route('expenses.review', rejectTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        },
    });
};

/* ---------------- Remboursement / suppression ---------------- */
const reimburse = (expense) => {
    if (!confirm(`Marquer la dépense « ${expense.description} » comme remboursée ?`)) return;
    router.post(route('expenses.reimburse', expense.id), {}, { preserveScroll: true });
};

const destroy = (expense) => {
    if (!confirm(`Supprimer la note de frais « ${expense.description} » ?`)) return;
    router.delete(route('expenses.destroy', expense.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Notes de frais" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Notes de frais</h2>
                <PrimaryButton v-if="hasAccess" @click="openCreate">➕ Nouvelle dépense</PrimaryButton>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
                <!-- Upsell si forfait insuffisant -->
                <div v-if="!hasAccess" class="mx-auto max-w-2xl rounded-lg bg-white p-8 text-center shadow">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gold-400/20 text-3xl">🧾</div>
                    <h3 class="text-lg font-semibold text-brand-900">Notes de frais disponibles à partir du forfait BUSINESS</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Remboursez vos collaborateurs en toute simplicité : dépenses avec justificatifs photo ou PDF,
                        catégories, workflow d'approbation et suivi des remboursements, avec les forfaits BUSINESS et ENTERPRISE.
                    </p>
                    <Link :href="route('billing.plans')" class="mt-6 inline-block">
                        <PrimaryButton>Voir les forfaits</PrimaryButton>
                    </Link>
                </div>

                <template v-else>
                    <!-- Cartes stats -->
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">En attente de validation</div>
                            <div class="mt-1 text-2xl font-bold" :class="stats.pending_count > 0 ? 'text-amber-600' : 'text-brand-900'">
                                {{ fmtMoney(stats.pending_amount) }}
                            </div>
                            <div class="text-xs text-gray-400">{{ stats.pending_count }} dépense(s) soumise(s)</div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Approuvées à rembourser</div>
                            <div class="mt-1 text-2xl font-bold text-green-600">{{ fmtMoney(stats.approved_amount) }}</div>
                            <div class="text-xs text-gray-400">Montant validé non remboursé</div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Remboursées ce mois</div>
                            <div class="mt-1 text-2xl font-bold text-brand-900">{{ fmtMoney(stats.reimbursed_month_amount) }}</div>
                            <div class="text-xs text-gray-400">Depuis le début du mois</div>
                        </div>
                    </div>

                    <!-- Filtres -->
                    <div class="grid gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-3">
                        <div>
                            <InputLabel value="Statut" />
                            <select v-model="filterForm.status" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Tous les statuts</option>
                                <option value="submitted">À valider</option>
                                <option value="approved">Approuvée</option>
                                <option value="rejected">Rejetée</option>
                                <option value="reimbursed">Remboursée</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Catégorie" />
                            <select v-model="filterForm.category" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Toutes les catégories</option>
                                <option v-for="(label, slug) in categories" :key="slug" :value="slug">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Mois" />
                            <TextInput v-model="filterForm.month" type="month" class="mt-1 block w-full text-sm" />
                        </div>
                    </div>

                    <!-- Tableau des dépenses -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3">Date</th>
                                        <th class="px-4 py-3">Catégorie</th>
                                        <th class="px-4 py-3">Description</th>
                                        <th class="px-4 py-3">Déclarant</th>
                                        <th class="px-4 py-3 text-right">Montant</th>
                                        <th class="px-4 py-3 text-center">Justif.</th>
                                        <th class="px-4 py-3">Statut</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="e in expenses.data" :key="e.id" class="hover:bg-gray-50">
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-500">{{ fmtDate(e.expense_date) }}</td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="CATEGORY_CLASSES[e.category] ?? CATEGORY_CLASSES.autre">
                                                {{ categories[e.category] ?? e.category }}
                                            </span>
                                        </td>
                                        <td class="max-w-[240px] px-4 py-3">
                                            <div class="truncate font-semibold text-gray-800" :title="e.description">{{ e.description }}</div>
                                            <div v-if="e.status === 'rejected' && e.review_note" class="mt-0.5 truncate text-xs text-red-600" :title="e.review_note">
                                                Motif : {{ e.review_note }}
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ e.user?.name ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-gray-800">{{ fmtMoney(e.amount, e.currency) }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <a
                                                v-if="e.has_receipt"
                                                :href="route('expenses.receipt', e.id)"
                                                target="_blank"
                                                class="font-semibold text-brand-600 hover:underline"
                                                :title="e.receipt_original_name ?? 'Voir le justificatif'"
                                            >📎</a>
                                            <span v-else class="text-gray-300">—</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                                :class="STATUS_META[e.status]?.class"
                                                :title="e.status === 'rejected' && e.review_note ? `Motif : ${e.review_note}` : (e.reviewer ? `Validé par ${e.reviewer.name}` : undefined)"
                                            >
                                                {{ STATUS_META[e.status]?.label ?? e.status }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <template v-if="e.can_review">
                                                    <button
                                                        class="rounded bg-green-100 px-2 py-1 text-xs font-semibold text-green-700 hover:bg-green-200"
                                                        title="Approuver"
                                                        @click="approve(e)"
                                                    >✓ Approuver</button>
                                                    <button
                                                        class="rounded bg-red-100 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-200"
                                                        title="Rejeter"
                                                        @click="openReject(e)"
                                                    >✗ Rejeter</button>
                                                </template>
                                                <button
                                                    v-if="e.can_reimburse"
                                                    class="rounded bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-200"
                                                    title="Marquer remboursée"
                                                    @click="reimburse(e)"
                                                >💸 Marquer remboursée</button>
                                                <button
                                                    v-if="e.can_edit"
                                                    class="rounded px-2 py-1 text-xs font-semibold text-gray-500 hover:bg-gray-100"
                                                    title="Modifier"
                                                    @click="openEdit(e)"
                                                >✏️</button>
                                                <button
                                                    v-if="e.can_edit"
                                                    class="rounded px-2 py-1 text-xs font-semibold text-red-500 hover:bg-red-50"
                                                    title="Supprimer"
                                                    @click="destroy(e)"
                                                >🗑️</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!expenses.data.length">
                                        <td colspan="8" class="px-4 py-10 text-center text-gray-400">Aucune note de frais.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="expenses.links.length > 3" class="flex flex-wrap gap-1">
                        <template v-for="link in expenses.links" :key="link.label">
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
                </template>
            </div>
        </div>

        <!-- Modale création / édition -->
        <Modal :show="showModal" @close="showModal = false" max-width="lg">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">
                    {{ editingId ? 'Modifier la note de frais' : 'Nouvelle note de frais' }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <InputLabel value="Catégorie *" />
                        <select v-model="form.category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="" disabled>Choisir une catégorie…</option>
                            <option v-for="(label, slug) in categories" :key="slug" :value="slug">{{ label }}</option>
                        </select>
                        <InputError :message="form.errors.category" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Description *" />
                        <TextInput v-model="form.description" class="mt-1 block w-full" placeholder="Ex : taxi aéroport, déjeuner client…" required />
                        <InputError :message="form.errors.description" class="mt-1" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel :value="`Montant (${currency}) *`" />
                            <TextInput v-model="form.amount" type="number" step="0.01" min="1" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.amount" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Date de la dépense *" />
                            <TextInput v-model="form.expense_date" type="date" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.expense_date" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Justificatif (photo ou PDF, 8 Mo max)" />
                        <input
                            type="file"
                            accept=".jpg,.jpeg,.png,.webp,.pdf"
                            class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-brand-600 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white hover:file:bg-brand-900"
                            @change="onFileChange"
                        />
                        <p v-if="form.receipt" class="mt-1 text-xs text-gray-500">📎 {{ form.receipt.name }}</p>
                        <p v-else-if="editingId" class="mt-1 text-xs text-gray-400">
                            Laissez vide pour conserver le justificatif actuel ; un nouveau fichier le remplace.
                        </p>
                        <InputError :message="form.errors.receipt" class="mt-1" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing || !form.category" @click="submit">
                        {{ editingId ? 'Mettre à jour' : 'Soumettre' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale motif de rejet -->
        <Modal :show="showRejectModal" @close="showRejectModal = false" max-width="md">
            <div class="p-6">
                <h3 class="mb-2 text-lg font-semibold text-gray-800">Rejeter la note de frais</h3>
                <p v-if="rejectTarget" class="mb-4 text-sm text-gray-500">
                    « {{ rejectTarget.description }} » — {{ fmtMoney(rejectTarget.amount, rejectTarget.currency) }}
                </p>

                <div>
                    <InputLabel value="Motif du rejet *" />
                    <textarea
                        v-model="rejectForm.note"
                        rows="3"
                        maxlength="255"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        placeholder="Ex : justificatif illisible, dépense hors politique…"
                    ></textarea>
                    <InputError :message="rejectForm.errors.note" class="mt-1" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showRejectModal = false">Annuler</SecondaryButton>
                    <DangerButton :disabled="rejectForm.processing || !rejectForm.note" @click="submitReject">
                        ✗ Rejeter
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    transactions: Object,
    stats: Object,
    plans: Array,
    filters: Object,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);
const fmtMoney = (n, currency = 'FCFA') => `${fmt(n)} ${currency}`;

// Filtres
const search = ref(props.filters.search ?? '');
const statusFilter = ref(props.filters.status ?? '');
const providerFilter = ref(props.filters.provider ?? '');
const countryFilter = ref(props.filters.country ?? '');
const currencyFilter = ref(props.filters.currency ?? '');
const planFilter = ref(props.filters.plan ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');

let filterTimer = null;
const applyFilters = () => {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        router.get(route('admin.payment-queue'), {
            search: search.value || undefined,
            status: statusFilter.value || undefined,
            provider: providerFilter.value || undefined,
            country: countryFilter.value || undefined,
            currency: currencyFilter.value || undefined,
            plan: planFilter.value || undefined,
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
        }, { preserveState: true, replace: true, preserveScroll: true });
    }, 350);
};
watch([search, statusFilter, providerFilter, countryFilter, currencyFilter, planFilter, dateFrom, dateTo], applyFilters);

// Fraud score
const fraudScore = (t) => {
    let score = 0;
    if (t.amount_declared !== null && Math.abs(t.amount_declared - t.amount_expected) > t.amount_expected * 0.05) score++;
    if (!t.provider_reference) score++;
    if (!t.proofs?.length) score++;
    return score;
};
const fraudBadge = (t) => {
    const s = fraudScore(t);
    if (s === 0) return { label: '🟢 Faible', cls: 'bg-green-100 text-green-700' };
    if (s === 1) return { label: '🟡 Moyen', cls: 'bg-amber-100 text-amber-700' };
    return { label: '🔴 Élevé', cls: 'bg-red-100 text-red-700' };
};

const riskColors = {
    LOW: 'bg-green-100 text-green-700',
    MEDIUM: 'bg-amber-100 text-amber-700',
    HIGH: 'bg-orange-100 text-orange-700',
    CRITICAL: 'bg-red-100 text-red-700',
};

const providerLabels = {
    orange_money: 'Orange Money', mtn_momo: 'MTN MoMo', wave: 'Wave', moov: 'Moov Money',
    bank_transfer_national: 'Virement national', bank_transfer_international: 'Virement international',
    moneroo: 'Moneroo', cash: 'Espèces',
};

const statusLabels = {
    pending: 'En attente', under_review: 'En vérification', proof_submitted: 'Preuve soumise',
    manually_validated: 'Validé', rejected: 'Rejeté', missing_info: 'Info manquante',
    suspected_fraud: 'Suspect',
};
const statusColors = {
    pending: 'bg-amber-100 text-amber-700', under_review: 'bg-blue-100 text-blue-700',
    proof_submitted: 'bg-indigo-100 text-indigo-700', manually_validated: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700', missing_info: 'bg-orange-100 text-orange-700',
    suspected_fraud: 'bg-red-200 text-red-800',
};

// Drawer
const drawerTx = ref(null);
const openDrawer = (tx) => { drawerTx.value = tx; };
const closeDrawer = () => { drawerTx.value = null; };

// Modales
const validating = ref(null);
const rejecting = ref(null);
const complementing = ref(null);
const provisioning = ref(null);
const suspecting = ref(null);

const validateForm = useForm({
    amount_received: 0,
    note: '',
    received_date: new Date().toISOString().split('T')[0],
    controlled_reference: '',
});
const rejectForm = useForm({ reason: '', reason_code: 'other' });
const complementForm = useForm({ complement_note: '' });
const provisionalForm = useForm({ motif: '', days: 7 });
const suspectForm = useForm({ reason: '' });

const openValidate = (tx) => {
    validating.value = tx;
    validateForm.amount_received = tx.amount_declared ?? tx.amount_expected;
    validateForm.controlled_reference = tx.provider_reference ?? '';
    validateForm.clearErrors();
};

const submitValidate = () => {
    validateForm.post(route('admin.payments.validate', validating.value.id), {
        preserveScroll: true,
        onSuccess: () => { validating.value = null; closeDrawer(); },
    });
};

const submitReject = () => {
    rejectForm.post(route('admin.payments.reject', rejecting.value.id), {
        preserveScroll: true,
        onSuccess: () => { rejecting.value = null; rejectForm.reset(); closeDrawer(); },
    });
};

const submitComplement = () => {
    complementForm.post(route('admin.payments.complement', complementing.value.id), {
        preserveScroll: true,
        onSuccess: () => { complementing.value = null; complementForm.reset(); closeDrawer(); },
    });
};

const submitProvisional = () => {
    provisionalForm.post(route('admin.orders.provisional', provisioning.value.order.id), {
        preserveScroll: true,
        onSuccess: () => { provisioning.value = null; provisionalForm.reset(); closeDrawer(); },
    });
};

const submitSuspect = () => {
    suspectForm.post(route('admin.payments.suspect', suspecting.value.id), {
        preserveScroll: true,
        onSuccess: () => { suspecting.value = null; suspectForm.reset(); closeDrawer(); },
    });
};

const rejectReasonCodes = [
    { value: 'amount_mismatch', label: 'Montant incorrect' },
    { value: 'fake_proof', label: 'Preuve falsifiée' },
    { value: 'duplicate', label: 'Doublon' },
    { value: 'no_proof', label: 'Aucune preuve' },
    { value: 'expired', label: 'Délai expiré' },
    { value: 'other', label: 'Autre' },
];
</script>

<template>
    <Head title="Admin — File de validation" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Console Superadmin — <span class="text-amber-600">File de validation des paiements</span>
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <AdminTabs />

                <!-- Compteurs -->
                <div class="grid grid-cols-3 gap-4">
                    <div class="rounded-lg bg-white p-5 shadow text-center">
                        <div class="text-xs uppercase tracking-wide text-gray-500">En attente</div>
                        <div class="mt-1 text-3xl font-bold text-amber-600">{{ stats.pending }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow text-center">
                        <div class="text-xs uppercase tracking-wide text-gray-500">À vérifier</div>
                        <div class="mt-1 text-3xl font-bold text-blue-600">{{ stats.under_review }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow text-center">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Provisoires actives</div>
                        <div class="mt-1 text-3xl font-bold text-indigo-600">{{ stats.provisional }}</div>
                    </div>
                </div>

                <!-- Filtres -->
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
                        <input v-model="search" type="text" placeholder="Client / Réf…"
                            class="col-span-2 rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                        <select v-model="statusFilter" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Tous statuts</option>
                            <option value="pending">En attente</option>
                            <option value="under_review">En vérif.</option>
                            <option value="proof_submitted">Preuve soumise</option>
                            <option value="missing_info">Info manquante</option>
                        </select>
                        <select v-model="providerFilter" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Méthode</option>
                            <option v-for="(label, key) in providerLabels" :key="key" :value="key">{{ label }}</option>
                        </select>
                        <input v-model="countryFilter" type="text" placeholder="Pays (CI, SN…)"
                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                        <select v-model="planFilter" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Forfait</option>
                            <option v-for="p in plans" :key="p.code" :value="p.code">{{ p.name }}</option>
                        </select>
                        <input v-model="dateFrom" type="date" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                        <input v-model="dateTo" type="date" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                    </div>
                </div>

                <!-- Tableau -->
                <div class="overflow-x-auto rounded-lg bg-white shadow">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Client</th>
                                <th class="px-4 py-3 text-left">Forfait</th>
                                <th class="px-4 py-3 text-right">Attendu</th>
                                <th class="px-4 py-3 text-right">Déclaré</th>
                                <th class="px-4 py-3 text-left">Méthode</th>
                                <th class="px-4 py-3 text-left">Référence</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Statut</th>
                                <th class="px-4 py-3 text-left">Score fraude</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ tx.user?.name }}</div>
                                    <div class="text-xs text-gray-400">{{ tx.user?.email }}</div>
                                    <div class="text-xs text-gray-400">{{ tx.user?.country }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ tx.order?.plan }}</div>
                                    <div class="text-xs text-gray-400">{{ tx.order?.duration_months }} mois</div>
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-semibold">
                                    {{ fmt(tx.amount_expected) }} {{ tx.currency }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-semibold"
                                    :class="tx.amount_declared !== null && Math.abs(tx.amount_declared - tx.amount_expected) > tx.amount_expected * 0.05 ? 'text-red-600' : 'text-green-600'">
                                    {{ tx.amount_declared !== null ? fmt(tx.amount_declared) : '—' }} {{ tx.amount_declared !== null ? tx.currency : '' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                        {{ providerLabels[tx.provider] ?? tx.provider }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ tx.provider_reference ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ tx.created_at }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-bold" :class="statusColors[tx.status] ?? 'bg-gray-100 text-gray-600'">
                                        {{ statusLabels[tx.status] ?? tx.status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-bold" :class="fraudBadge(tx).cls">
                                        {{ fraudBadge(tx).label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1 flex-wrap">
                                        <button @click="openDrawer(tx)" title="Voir détails"
                                            class="rounded bg-gray-100 p-1.5 text-gray-600 hover:bg-gray-200">👁</button>
                                        <button @click="openValidate(tx)" title="Valider"
                                            class="rounded bg-green-100 p-1.5 text-green-700 hover:bg-green-200">✅</button>
                                        <button @click="rejecting = tx" title="Rejeter"
                                            class="rounded bg-red-100 p-1.5 text-red-700 hover:bg-red-200">❌</button>
                                        <button @click="complementing = tx" title="Demander complément"
                                            class="rounded bg-amber-100 p-1.5 text-amber-700 hover:bg-amber-200">ℹ️</button>
                                        <button @click="provisioning = tx" title="Activation provisoire"
                                            class="rounded bg-indigo-100 p-1.5 text-indigo-700 hover:bg-indigo-200">⚡</button>
                                        <button @click="suspecting = tx" title="Marquer suspect"
                                            class="rounded bg-red-50 p-1.5 text-red-600 hover:bg-red-100">🔍</button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!transactions.data.length">
                                <td colspan="10" class="px-4 py-12 text-center text-gray-400">
                                    🎉 Aucun paiement en attente de vérification.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="transactions.last_page > 1" class="flex justify-center gap-2">
                    <Link
                        v-for="link in transactions.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        class="rounded px-3 py-1 text-sm"
                        :class="link.active ? 'bg-brand-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100'"
                    />
                </div>
            </div>
        </div>

        <!-- Drawer latéral -->
        <Teleport to="body">
            <div v-if="drawerTx" class="fixed inset-0 z-40 flex">
                <div class="flex-1 bg-black/40" @click="closeDrawer" />
                <div class="relative w-full max-w-xl overflow-y-auto bg-white shadow-xl">
                    <div class="sticky top-0 flex items-center justify-between border-b bg-white px-6 py-4">
                        <h3 class="text-lg font-bold text-gray-800">Détails — {{ drawerTx.internal_reference }}</h3>
                        <button @click="closeDrawer" class="text-gray-400 hover:text-gray-700">✕</button>
                    </div>
                    <div class="space-y-6 p-6">
                        <!-- Infos commande -->
                        <div class="rounded-lg bg-gray-50 p-4 space-y-2 text-sm">
                            <div><span class="font-semibold text-gray-600">Client :</span> {{ drawerTx.user?.name }} ({{ drawerTx.user?.email }})</div>
                            <div><span class="font-semibold text-gray-600">Pays :</span> {{ drawerTx.user?.country }}</div>
                            <div><span class="font-semibold text-gray-600">Commande :</span> {{ drawerTx.order?.order_number }}</div>
                            <div><span class="font-semibold text-gray-600">Forfait :</span> {{ drawerTx.order?.plan }} — {{ drawerTx.order?.duration_months }} mois</div>
                            <div><span class="font-semibold text-gray-600">Montant attendu :</span> <b>{{ fmt(drawerTx.amount_expected) }} {{ drawerTx.currency }}</b></div>
                            <div><span class="font-semibold text-gray-600">Montant déclaré :</span>
                                <b :class="drawerTx.amount_declared !== null && Math.abs(drawerTx.amount_declared - drawerTx.amount_expected) > drawerTx.amount_expected * 0.05 ? 'text-red-600' : 'text-green-600'">
                                    {{ drawerTx.amount_declared !== null ? fmt(drawerTx.amount_declared) : '—' }} {{ drawerTx.currency }}
                                </b>
                            </div>
                            <div><span class="font-semibold text-gray-600">Méthode :</span> {{ providerLabels[drawerTx.provider] ?? drawerTx.provider }}</div>
                            <div><span class="font-semibold text-gray-600">Référence :</span> {{ drawerTx.provider_reference ?? '—' }}</div>
                            <div><span class="font-semibold text-gray-600">Expéditeur :</span> {{ drawerTx.sender_name ?? '—' }} ({{ drawerTx.sender_number ?? '—' }})</div>
                            <div><span class="font-semibold text-gray-600">Soumis le :</span> {{ drawerTx.created_at }}</div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-600">Score fraude :</span>
                                <span class="rounded-full px-2 py-0.5 text-xs font-bold" :class="fraudBadge(drawerTx).cls">{{ fraudBadge(drawerTx).label }}</span>
                            </div>
                        </div>

                        <!-- Preuves -->
                        <div v-if="drawerTx.proofs?.length">
                            <h4 class="mb-2 font-semibold text-gray-700">Preuves soumises</h4>
                            <div class="space-y-2">
                                <div v-for="proof in drawerTx.proofs" :key="proof.id" class="flex items-center justify-between rounded-md bg-blue-50 px-3 py-2">
                                    <span class="text-sm text-blue-800">📎 {{ proof.original_filename }} ({{ Math.round(proof.file_size / 1024) }} Ko)</span>
                                    <a :href="proof.url" target="_blank" class="text-xs font-semibold text-blue-600 hover:underline">Voir</a>
                                </div>
                            </div>
                        </div>
                        <div v-else class="rounded-md bg-red-50 px-3 py-2 text-sm text-red-600">
                            ⚠️ Aucune preuve soumise
                        </div>

                        <!-- Actions rapides depuis le drawer -->
                        <div class="border-t pt-4">
                            <h4 class="mb-3 font-semibold text-gray-700">Actions</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <button @click="openValidate(drawerTx)"
                                    class="rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700">
                                    ✅ Valider
                                </button>
                                <button @click="rejecting = drawerTx"
                                    class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                    ❌ Rejeter
                                </button>
                                <button @click="complementing = drawerTx"
                                    class="rounded-md bg-amber-500 px-3 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                                    ℹ️ Complément
                                </button>
                                <button @click="provisioning = drawerTx"
                                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                    ⚡ Provisoire
                                </button>
                                <button @click="suspecting = drawerTx"
                                    class="col-span-2 rounded-md bg-gray-700 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                                    🔍 Marquer suspect
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Modale validation -->
        <Modal :show="!!validating" @close="validating = null">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Valider le paiement</h3>
                <p class="text-sm text-gray-500">
                    <b>{{ validating?.internal_reference }}</b> — licence activée immédiatement pour
                    <b>{{ validating?.user?.name }}</b>.
                </p>
                <div>
                    <InputLabel value="Date réelle de réception *" />
                    <TextInput v-model="validateForm.received_date" type="date" class="mt-1 block w-full" />
                    <InputError :message="validateForm.errors.received_date" class="mt-1" />
                </div>
                <div>
                    <InputLabel value="Montant réellement reçu *" />
                    <TextInput v-model="validateForm.amount_received" type="number" step="1" min="1" class="mt-1 block w-full" />
                    <InputError :message="validateForm.errors.amount_received" class="mt-1" />
                </div>
                <div>
                    <InputLabel value="Référence contrôlée *" />
                    <TextInput v-model="validateForm.controlled_reference" type="text" class="mt-1 block w-full" placeholder="Référence vérifiée en banque/opérateur" />
                    <InputError :message="validateForm.errors.controlled_reference" class="mt-1" />
                </div>
                <div>
                    <InputLabel value="Commentaire interne" />
                    <textarea v-model="validateForm.note" rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        placeholder="Notes internes…"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <SecondaryButton @click="validating = null">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="validateForm.processing" @click="submitValidate">
                        Valider &amp; activer la licence
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale rejet -->
        <Modal :show="!!rejecting" @close="rejecting = null">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Rejeter le paiement</h3>
                <p class="text-sm text-gray-500">Le client sera notifié. Motif <b>obligatoire</b>.</p>
                <div>
                    <InputLabel value="Code motif *" />
                    <select v-model="rejectForm.reason_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option v-for="rc in rejectReasonCodes" :key="rc.value" :value="rc.value">{{ rc.label }}</option>
                    </select>
                    <InputError :message="rejectForm.errors.reason_code" class="mt-1" />
                </div>
                <div>
                    <InputLabel value="Détail du motif *" />
                    <textarea v-model="rejectForm.reason" rows="3" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        placeholder="Ex : référence introuvable, montant insuffisant…"></textarea>
                    <InputError :message="rejectForm.errors.reason" class="mt-1" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <SecondaryButton @click="rejecting = null">Annuler</SecondaryButton>
                    <DangerButton :disabled="rejectForm.processing" @click="submitReject">Rejeter</DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Modale complément -->
        <Modal :show="!!complementing" @close="complementing = null">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Demander un complément</h3>
                <p class="text-sm text-gray-500">Un message sera envoyé au client lui demandant de compléter son dossier.</p>
                <div>
                    <InputLabel value="Note de demande *" />
                    <textarea v-model="complementForm.complement_note" rows="3" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        placeholder="Ex : veuillez fournir un reçu plus lisible…"></textarea>
                    <InputError :message="complementForm.errors.complement_note" class="mt-1" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <SecondaryButton @click="complementing = null">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="complementForm.processing" @click="submitComplement">Envoyer la demande</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale provisoire -->
        <Modal :show="!!provisioning" @close="provisioning = null">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Activation provisoire</h3>
                <p class="text-sm text-gray-500">La licence sera activée temporairement en attente de confirmation du paiement.</p>
                <div>
                    <InputLabel value="Durée *" />
                    <select v-model="provisionalForm.days" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option :value="7">7 jours</option>
                        <option :value="14">14 jours</option>
                        <option :value="30">30 jours</option>
                    </select>
                    <InputError :message="provisionalForm.errors.days" class="mt-1" />
                </div>
                <div>
                    <InputLabel value="Motif *" />
                    <textarea v-model="provisionalForm.motif" rows="2" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        placeholder="Ex : virement en cours de traitement…"></textarea>
                    <InputError :message="provisionalForm.errors.motif" class="mt-1" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <SecondaryButton @click="provisioning = null">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="provisionalForm.processing" @click="submitProvisional">Activer provisoirement</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale suspect -->
        <Modal :show="!!suspecting" @close="suspecting = null">
            <div class="p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">Marquer comme suspect</h3>
                <p class="text-sm text-gray-500">La transaction sera marquée pour investigation approfondie.</p>
                <div>
                    <InputLabel value="Raison *" />
                    <textarea v-model="suspectForm.reason" rows="2" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        placeholder="Ex : IP suspecte, références incohérentes…"></textarea>
                    <InputError :message="suspectForm.errors.reason" class="mt-1" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <SecondaryButton @click="suspecting = null">Annuler</SecondaryButton>
                    <DangerButton :disabled="suspectForm.processing" @click="submitSuspect">Marquer suspect</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

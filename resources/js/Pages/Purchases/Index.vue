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
import { ref, watch, computed } from 'vue';

const props = defineProps({
    hasAccess: Boolean,
    suppliers: { type: Array, default: () => [] },
    invoices: { type: Object, default: null },
    stats: { type: Object, default: null },
    categories: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
    currency: { type: String, default: 'XOF' },
});

/* ---------------- Formatage (Intl fr-FR) ---------------- */
const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 }).format(Number(n ?? 0));
const fmtMoney = (n, c) => `${fmt(n)} ${c ?? props.currency}`;
const fmtDate = (d) =>
    d ? new Date(d + 'T00:00:00').toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';

const STATUS_META = {
    unpaid: { label: 'Impayé', class: 'bg-red-100 text-red-700' },
    partial: { label: 'Partiel', class: 'bg-amber-100 text-amber-700' },
    paid: { label: 'Payé', class: 'bg-green-100 text-green-700' },
};

/* ---------------- Filtres factures ---------------- */
const filterForm = ref({
    supplier: props.filters?.supplier ?? '',
    status: props.filters?.status ?? '',
    month: props.filters?.month ?? '',
});

let filterTimeout = null;
watch(filterForm, (value) => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        const params = Object.fromEntries(Object.entries(value).filter(([, v]) => v !== '' && v !== null));
        router.get(route('purchases.index'), params, { preserveState: true, replace: true });
    }, 300);
}, { deep: true });

/* ---------------- Fournisseurs (CRUD) ---------------- */
const showSupplierModal = ref(false);
const editingSupplierId = ref(null);

const supplierForm = useForm({
    name: '',
    contact_name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    country: 'CI',
    tax_id: '',
    notes: '',
});

const openCreateSupplier = () => {
    editingSupplierId.value = null;
    supplierForm.reset();
    supplierForm.clearErrors();
    showSupplierModal.value = true;
};

const openEditSupplier = (supplier) => {
    editingSupplierId.value = supplier.id;
    supplierForm.clearErrors();
    supplierForm.name = supplier.name ?? '';
    supplierForm.contact_name = supplier.contact_name ?? '';
    supplierForm.email = supplier.email ?? '';
    supplierForm.phone = supplier.phone ?? '';
    supplierForm.address = supplier.address ?? '';
    supplierForm.city = supplier.city ?? '';
    supplierForm.country = supplier.country ?? 'CI';
    supplierForm.tax_id = supplier.tax_id ?? '';
    supplierForm.notes = supplier.notes ?? '';
    showSupplierModal.value = true;
};

const submitSupplier = () => {
    const opts = {
        preserveScroll: true,
        onSuccess: () => {
            showSupplierModal.value = false;
            supplierForm.reset();
        },
    };
    if (editingSupplierId.value) {
        supplierForm.put(route('purchases.suppliers.update', editingSupplierId.value), opts);
    } else {
        supplierForm.post(route('purchases.suppliers.store'), opts);
    }
};

const destroySupplier = (supplier) => {
    if (!confirm(`Supprimer le fournisseur « ${supplier.name} » ?`)) return;
    router.delete(route('purchases.suppliers.destroy', supplier.id), { preserveScroll: true });
};

/* ---------------- Factures d'achat (CRUD) ---------------- */
const showInvoiceModal = ref(false);
const editingInvoiceId = ref(null);

const invoiceForm = useForm({
    supplier_id: '',
    number: '',
    reference: '',
    invoice_date: new Date().toISOString().slice(0, 10),
    due_date: '',
    amount_ht: '',
    vat_amount: '',
    amount_ttc: '',
    category: 'marchandises',
    notes: '',
    receipt: null,
    _method: 'POST',
});

/* TTC = HT + TVA automatique (modifiable manuellement). */
const ttcTouched = ref(false);
watch(() => [invoiceForm.amount_ht, invoiceForm.vat_amount], () => {
    if (ttcTouched.value) return;
    const ht = parseFloat(invoiceForm.amount_ht);
    const vat = parseFloat(invoiceForm.vat_amount);
    if (!isNaN(ht) || !isNaN(vat)) {
        invoiceForm.amount_ttc = ((isNaN(ht) ? 0 : ht) + (isNaN(vat) ? 0 : vat)).toFixed(2);
    }
});

const openCreateInvoice = () => {
    editingInvoiceId.value = null;
    invoiceForm.reset();
    invoiceForm.clearErrors();
    invoiceForm.invoice_date = new Date().toISOString().slice(0, 10);
    invoiceForm.category = 'marchandises';
    invoiceForm._method = 'POST';
    ttcTouched.value = false;
    showInvoiceModal.value = true;
};

const openEditInvoice = (invoice) => {
    editingInvoiceId.value = invoice.id;
    invoiceForm.clearErrors();
    invoiceForm.supplier_id = invoice.supplier_id;
    invoiceForm.number = invoice.number;
    invoiceForm.reference = invoice.reference ?? '';
    invoiceForm.invoice_date = invoice.invoice_date;
    invoiceForm.due_date = invoice.due_date ?? '';
    invoiceForm.amount_ht = invoice.amount_ht;
    invoiceForm.vat_amount = invoice.vat_amount;
    invoiceForm.amount_ttc = invoice.amount_ttc;
    invoiceForm.category = invoice.category;
    invoiceForm.notes = invoice.notes ?? '';
    invoiceForm.receipt = null;
    invoiceForm._method = 'PUT';
    ttcTouched.value = true; // en édition, on ne réécrase pas le TTC saisi
    showInvoiceModal.value = true;
};

const onFileChange = (event) => {
    invoiceForm.receipt = event.target.files[0] ?? null;
};

const submitInvoice = () => {
    const url = editingInvoiceId.value
        ? route('purchases.invoices.update', editingInvoiceId.value)
        : route('purchases.invoices.store');
    // POST + _method=PUT : Inertia bascule en FormData dès qu'un fichier est présent.
    invoiceForm.post(url, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            showInvoiceModal.value = false;
            invoiceForm.reset();
        },
    });
};

const destroyInvoice = (invoice) => {
    if (!confirm(`Supprimer la facture « ${invoice.number} » ?`)) return;
    router.delete(route('purchases.invoices.destroy', invoice.id), { preserveScroll: true });
};

/* ---------------- Règlement ---------------- */
const showPaymentModal = ref(false);
const paymentTarget = ref(null);
const paymentForm = useForm({
    amount: '',
    paid_at: new Date().toISOString().slice(0, 10),
});

const openPayment = (invoice) => {
    paymentTarget.value = invoice;
    paymentForm.reset();
    paymentForm.clearErrors();
    paymentForm.amount = invoice.balance_due;
    paymentForm.paid_at = new Date().toISOString().slice(0, 10);
    showPaymentModal.value = true;
};

const submitPayment = () => {
    if (!paymentTarget.value) return;
    paymentForm.post(route('purchases.invoices.payment', paymentTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showPaymentModal.value = false;
            paymentForm.reset();
        },
    });
};

const hasInvoices = computed(() => (props.invoices?.data?.length ?? 0) > 0);
</script>

<template>
    <Head title="Achats fournisseurs" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Achats fournisseurs</h2>
                <div v-if="hasAccess" class="flex gap-2">
                    <SecondaryButton @click="openCreateSupplier">🏢 Nouveau fournisseur</SecondaryButton>
                    <SecondaryButton as="a" :href="route('purchases.ocr.index')">📷 Scanner une facture</SecondaryButton>
                    <PrimaryButton :disabled="!suppliers.length" @click="openCreateInvoice">➕ Nouvelle facture</PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
                <!-- Upsell si forfait insuffisant -->
                <div v-if="!hasAccess" class="mx-auto max-w-2xl rounded-lg bg-white p-8 text-center shadow">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gold-400/20 text-3xl">🛒</div>
                    <h3 class="text-lg font-semibold text-brand-900">Achats fournisseurs disponibles à partir du forfait BUSINESS</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Répertoriez vos fournisseurs, saisissez vos factures d'achat (HT / TVA / TTC, échéance,
                        justificatif privé), suivez vos paiements et alimentez automatiquement votre comptabilité
                        (journal des achats, TVA déductible, charges) — avec les forfaits BUSINESS et ENTERPRISE.
                    </p>
                    <Link :href="route('billing.plans')" class="mt-6 inline-block">
                        <PrimaryButton>Voir les forfaits</PrimaryButton>
                    </Link>
                </div>

                <template v-else>
                    <!-- Cartes stats -->
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Achats du mois (TTC)</div>
                            <div class="mt-1 text-2xl font-bold text-brand-900">{{ fmtMoney(stats.purchases_month_ttc) }}</div>
                            <div class="text-xs text-gray-400">Factures datées du mois en cours</div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Impayé total</div>
                            <div class="mt-1 text-2xl font-bold" :class="stats.unpaid_total > 0 ? 'text-red-600' : 'text-green-600'">
                                {{ fmtMoney(stats.unpaid_total) }}
                            </div>
                            <div class="text-xs text-gray-400">Reste à payer (impayé + partiel)</div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">TVA déductible du mois</div>
                            <div class="mt-1 text-2xl font-bold text-gray-700">{{ fmtMoney(stats.vat_deductible_month) }}</div>
                            <div class="text-xs text-gray-400">Récupérable sur la déclaration</div>
                        </div>
                    </div>

                    <!-- Répertoire fournisseurs -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="flex items-center justify-between border-b px-4 py-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                                Répertoire fournisseurs ({{ suppliers.length }})
                            </h3>
                            <SecondaryButton class="!py-1 !text-xs" @click="openCreateSupplier">➕ Ajouter</SecondaryButton>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="px-4 py-2">Nom</th>
                                        <th class="px-4 py-2">Contact</th>
                                        <th class="px-4 py-2">Ville / Pays</th>
                                        <th class="px-4 py-2 text-right">Factures</th>
                                        <th class="px-4 py-2 text-right">Total TTC</th>
                                        <th class="px-4 py-2 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="s in suppliers" :key="s.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-2 font-semibold text-gray-800">
                                            {{ s.name }}
                                            <span v-if="s.tax_id" class="block text-xs font-normal text-gray-400">N° fiscal : {{ s.tax_id }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-gray-600">
                                            <div v-if="s.contact_name">{{ s.contact_name }}</div>
                                            <div v-if="s.email" class="text-xs text-gray-400">{{ s.email }}</div>
                                            <div v-if="s.phone" class="text-xs text-gray-400">{{ s.phone }}</div>
                                            <span v-if="!s.contact_name && !s.email && !s.phone" class="text-gray-300">—</span>
                                        </td>
                                        <td class="px-4 py-2 text-gray-600">
                                            {{ s.city || '—' }}<span v-if="s.country"> ({{ s.country }})</span>
                                        </td>
                                        <td class="px-4 py-2 text-right text-gray-600">{{ s.invoices_count }}</td>
                                        <td class="px-4 py-2 text-right font-medium text-gray-800">{{ fmtMoney(s.invoices_ttc_sum) }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 text-right">
                                            <button class="rounded px-2 py-1 text-xs font-semibold text-gray-500 hover:bg-gray-100" title="Modifier" @click="openEditSupplier(s)">✏️</button>
                                            <button class="rounded px-2 py-1 text-xs font-semibold text-red-500 hover:bg-red-50" title="Supprimer" @click="destroySupplier(s)">🗑️</button>
                                        </td>
                                    </tr>
                                    <tr v-if="!suppliers.length">
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                            Aucun fournisseur. Ajoutez-en un pour commencer à saisir des factures d'achat.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Factures d'achat -->
                    <div class="space-y-3">
                        <!-- Filtres -->
                        <div class="grid gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-3">
                            <div>
                                <InputLabel value="Fournisseur" />
                                <select v-model="filterForm.supplier" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                    <option value="">Tous les fournisseurs</option>
                                    <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel value="Statut" />
                                <select v-model="filterForm.status" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                    <option value="">Tous les statuts</option>
                                    <option value="unpaid">Impayé</option>
                                    <option value="partial">Partiel</option>
                                    <option value="paid">Payé</option>
                                </select>
                            </div>
                            <div>
                                <InputLabel value="Mois" />
                                <TextInput v-model="filterForm.month" type="month" class="mt-1 block w-full text-sm" />
                            </div>
                        </div>

                        <!-- Tableau -->
                        <div class="overflow-hidden rounded-lg bg-white shadow">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                        <tr>
                                            <th class="px-4 py-3">Date</th>
                                            <th class="px-4 py-3">N°</th>
                                            <th class="px-4 py-3">Fournisseur</th>
                                            <th class="px-4 py-3">Catégorie</th>
                                            <th class="px-4 py-3 text-right">TTC</th>
                                            <th class="px-4 py-3 text-right">Payé</th>
                                            <th class="px-4 py-3 text-right">Reste</th>
                                            <th class="px-4 py-3">Statut</th>
                                            <th class="px-4 py-3 text-center">Justif.</th>
                                            <th class="px-4 py-3 text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        <tr v-for="i in invoices.data" :key="i.id" class="hover:bg-gray-50">
                                            <td class="whitespace-nowrap px-4 py-3 text-gray-500">{{ fmtDate(i.invoice_date) }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-gray-800">{{ i.number }}</td>
                                            <td class="px-4 py-3 text-gray-700">{{ i.supplier?.name ?? '—' }}</td>
                                            <td class="px-4 py-3">
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">
                                                    {{ categories[i.category] ?? i.category }}
                                                </span>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-gray-800">{{ fmtMoney(i.amount_ttc, i.currency) }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-gray-600">{{ fmtMoney(i.amount_paid, i.currency) }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right" :class="i.balance_due > 0 ? 'font-medium text-red-600' : 'text-gray-400'">{{ fmtMoney(i.balance_due, i.currency) }}</td>
                                            <td class="px-4 py-3">
                                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="STATUS_META[i.status]?.class">
                                                    {{ STATUS_META[i.status]?.label ?? i.status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <a v-if="i.has_receipt" :href="route('purchases.invoices.receipt', i.id)" target="_blank"
                                                    class="font-semibold text-brand-600 hover:underline" :title="i.receipt_original_name ?? 'Voir le justificatif'">📎</a>
                                                <span v-else class="text-gray-300">—</span>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                                <div class="flex items-center justify-end gap-1">
                                                    <button v-if="i.balance_due > 0" class="rounded bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-200" title="Enregistrer un règlement" @click="openPayment(i)">💸</button>
                                                    <button class="rounded px-2 py-1 text-xs font-semibold text-gray-500 hover:bg-gray-100" title="Modifier" @click="openEditInvoice(i)">✏️</button>
                                                    <button class="rounded px-2 py-1 text-xs font-semibold text-red-500 hover:bg-red-50" title="Supprimer" @click="destroyInvoice(i)">🗑️</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="!hasInvoices">
                                            <td colspan="10" class="px-4 py-10 text-center text-gray-400">Aucune facture d'achat.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <div v-if="invoices.links.length > 3" class="flex flex-wrap gap-1">
                            <template v-for="link in invoices.links" :key="link.label">
                                <Link v-if="link.url" :href="link.url" v-html="link.label"
                                    class="rounded px-3 py-1.5 text-sm"
                                    :class="link.active ? 'bg-brand-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'" />
                                <span v-else v-html="link.label" class="px-3 py-1.5 text-sm text-gray-400" />
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Modale fournisseur -->
        <Modal :show="showSupplierModal" @close="showSupplierModal = false" max-width="lg">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">
                    {{ editingSupplierId ? 'Modifier le fournisseur' : 'Nouveau fournisseur' }}
                </h3>
                <div class="space-y-4">
                    <div>
                        <InputLabel value="Nom / Raison sociale *" />
                        <TextInput v-model="supplierForm.name" class="mt-1 block w-full" required />
                        <InputError :message="supplierForm.errors.name" class="mt-1" />
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Contact" />
                            <TextInput v-model="supplierForm.contact_name" class="mt-1 block w-full" />
                            <InputError :message="supplierForm.errors.contact_name" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Email" />
                            <TextInput v-model="supplierForm.email" type="email" class="mt-1 block w-full" />
                            <InputError :message="supplierForm.errors.email" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Téléphone" />
                            <TextInput v-model="supplierForm.phone" class="mt-1 block w-full" />
                            <InputError :message="supplierForm.errors.phone" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="N° fiscal" />
                            <TextInput v-model="supplierForm.tax_id" class="mt-1 block w-full" />
                            <InputError :message="supplierForm.errors.tax_id" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Adresse" />
                            <TextInput v-model="supplierForm.address" class="mt-1 block w-full" />
                            <InputError :message="supplierForm.errors.address" class="mt-1" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <InputLabel value="Ville" />
                                <TextInput v-model="supplierForm.city" class="mt-1 block w-full" />
                                <InputError :message="supplierForm.errors.city" class="mt-1" />
                            </div>
                            <div>
                                <InputLabel value="Pays" />
                                <TextInput v-model="supplierForm.country" maxlength="2" class="mt-1 block w-full uppercase" />
                                <InputError :message="supplierForm.errors.country" class="mt-1" />
                            </div>
                        </div>
                    </div>
                    <div>
                        <InputLabel value="Notes" />
                        <textarea v-model="supplierForm.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                        <InputError :message="supplierForm.errors.notes" class="mt-1" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showSupplierModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="supplierForm.processing || !supplierForm.name" @click="submitSupplier">
                        {{ editingSupplierId ? 'Mettre à jour' : 'Ajouter' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale facture d'achat -->
        <Modal :show="showInvoiceModal" @close="showInvoiceModal = false" max-width="2xl">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">
                    {{ editingInvoiceId ? 'Modifier la facture d\'achat' : 'Nouvelle facture d\'achat' }}
                </h3>
                <div class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Fournisseur *" />
                            <select v-model="invoiceForm.supplier_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="" disabled>Choisir un fournisseur…</option>
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                            <InputError :message="invoiceForm.errors.supplier_id" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Catégorie *" />
                            <select v-model="invoiceForm.category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option v-for="(label, slug) in categories" :key="slug" :value="slug">{{ label }}</option>
                            </select>
                            <InputError :message="invoiceForm.errors.category" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="N° de facture *" />
                            <TextInput v-model="invoiceForm.number" class="mt-1 block w-full" required />
                            <InputError :message="invoiceForm.errors.number" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Référence interne" />
                            <TextInput v-model="invoiceForm.reference" class="mt-1 block w-full" />
                            <InputError :message="invoiceForm.errors.reference" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Date de facture *" />
                            <TextInput v-model="invoiceForm.invoice_date" type="date" class="mt-1 block w-full" required />
                            <InputError :message="invoiceForm.errors.invoice_date" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Échéance" />
                            <TextInput v-model="invoiceForm.due_date" type="date" class="mt-1 block w-full" />
                            <InputError :message="invoiceForm.errors.due_date" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <InputLabel :value="`Montant HT (${currency}) *`" />
                            <TextInput v-model="invoiceForm.amount_ht" type="number" step="0.01" min="0" class="mt-1 block w-full" required />
                            <InputError :message="invoiceForm.errors.amount_ht" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel :value="`TVA (${currency})`" />
                            <TextInput v-model="invoiceForm.vat_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                            <InputError :message="invoiceForm.errors.vat_amount" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel :value="`Montant TTC (${currency}) *`" />
                            <TextInput v-model="invoiceForm.amount_ttc" type="number" step="0.01" min="0" class="mt-1 block w-full" required @input="ttcTouched = true" />
                            <InputError :message="invoiceForm.errors.amount_ttc" class="mt-1" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-400">Le TTC est calculé automatiquement (HT + TVA) mais reste modifiable.</p>

                    <div>
                        <InputLabel value="Justificatif (photo ou PDF, 8 Mo max)" />
                        <input type="file" accept=".jpg,.jpeg,.png,.webp,.pdf"
                            class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-md file:border-0 file:bg-brand-600 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white hover:file:bg-brand-900"
                            @change="onFileChange" />
                        <p v-if="invoiceForm.receipt" class="mt-1 text-xs text-gray-500">📎 {{ invoiceForm.receipt.name }}</p>
                        <p v-else-if="editingInvoiceId" class="mt-1 text-xs text-gray-400">Laissez vide pour conserver le justificatif actuel ; un nouveau fichier le remplace.</p>
                        <InputError :message="invoiceForm.errors.receipt" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Notes" />
                        <textarea v-model="invoiceForm.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                        <InputError :message="invoiceForm.errors.notes" class="mt-1" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showInvoiceModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="invoiceForm.processing || !invoiceForm.supplier_id || !invoiceForm.number" @click="submitInvoice">
                        {{ editingInvoiceId ? 'Mettre à jour' : 'Enregistrer' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Modale règlement -->
        <Modal :show="showPaymentModal" @close="showPaymentModal = false" max-width="md">
            <div class="p-6">
                <h3 class="mb-2 text-lg font-semibold text-gray-800">Enregistrer un règlement</h3>
                <p v-if="paymentTarget" class="mb-4 text-sm text-gray-500">
                    Facture « {{ paymentTarget.number }} » — reste à payer {{ fmtMoney(paymentTarget.balance_due, paymentTarget.currency) }}
                </p>
                <div class="space-y-4">
                    <div>
                        <InputLabel :value="`Montant réglé (${currency}) *`" />
                        <TextInput v-model="paymentForm.amount" type="number" step="0.01" min="0.01" :max="paymentTarget?.balance_due" class="mt-1 block w-full" required />
                        <InputError :message="paymentForm.errors.amount" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Date de règlement" />
                        <TextInput v-model="paymentForm.paid_at" type="date" class="mt-1 block w-full" />
                        <InputError :message="paymentForm.errors.paid_at" class="mt-1" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showPaymentModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="paymentForm.processing || !paymentForm.amount" @click="submitPayment">💸 Valider le règlement</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

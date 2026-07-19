<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import { useAiAssist } from '@/Composables/useAiAssist';
import axios from 'axios';

const props = defineProps({
    document: { type: Object, default: null },
    documentType: String,
    customers: Array,
    products: Array,
    defaults: Object,
    types: Array,
    templates: { type: Array, default: () => [] },
    defaultTemplate: { type: String, default: null },
});

const isEdit = computed(() => !!props.document);
const typeLabel = computed(() => props.types.find((t) => t.value === (props.document?.type ?? props.documentType))?.label ?? 'Document');

// ── Customers list (local copy so new quick-adds appear immediately) ──────────
const customersList = ref([...(props.customers ?? [])]);

// ── Lines ─────────────────────────────────────────────────────────────────────
const emptyLine = () => ({
    product_id: null,
    description: '',
    quantity: 1,
    unit: 'unité',
    unit_price: 0,
    line_discount_type: 'percent',
    discount_percent: 0,
    tax_rate: props.defaults.tax_rate,
});

const form = useForm({
    type: props.document?.type ?? props.documentType,
    customer_id: props.document?.customer_id ?? null,
    reference: props.document?.reference ?? '',
    issue_date: props.document?.issue_date?.slice(0, 10) ?? new Date().toISOString().slice(0, 10),
    due_date: props.document?.due_date?.slice(0, 10) ?? null,
    currency: props.document?.currency ?? props.defaults.currency,
    template_key: props.document?.template_key ?? props.defaultTemplate ?? null,
    discount_type: props.document?.discount_type ?? null,
    discount_value: Number(props.document?.discount_value ?? 0),
    notes: props.document?.notes ?? '',
    terms: props.document?.terms ?? '',
    lines: props.document?.lines?.map((l) => ({
        product_id: l.product_id,
        description: l.description,
        quantity: Number(l.quantity),
        unit: l.unit,
        unit_price: Number(l.unit_price),
        line_discount_type: l.line_discount_type ?? 'percent',
        discount_percent: Number(l.discount_percent),
        tax_rate: Number(l.tax_rate),
    })) ?? [emptyLine()],
});

const addLine = () => form.lines.push(emptyLine());
const removeLine = (index) => form.lines.splice(index, 1);

const onProductSelect = (line) => {
    const product = props.products.find((p) => p.id === line.product_id);
    if (product) {
        line.description = product.name + (product.description ? ' — ' + product.description : '');
        line.unit_price = Number(product.price);
        line.tax_rate = Number(product.tax_rate);
        line.unit = product.unit;
    }
};

// ── Calcul des totaux ─────────────────────────────────────────────────────────
const lineTotal = (line) => {
    const base = line.quantity * line.unit_price;
    if (line.line_discount_type === 'fixed') {
        return Math.max(0, Math.round((base - (line.discount_percent || 0)) * 100) / 100);
    }
    return Math.round(base * (1 - (line.discount_percent || 0) / 100) * 100) / 100;
};

const subtotal = computed(() => form.lines.reduce((sum, l) => sum + lineTotal(l), 0));

const discountAmount = computed(() => {
    if (form.discount_type === 'percent') return (subtotal.value * (form.discount_value || 0)) / 100;
    if (form.discount_type === 'fixed') return Math.min(form.discount_value || 0, subtotal.value);
    return 0;
});

const taxAmount = computed(() => {
    const base = subtotal.value - discountAmount.value;
    if (subtotal.value <= 0) return 0;
    return form.lines.reduce((sum, l) => {
        const share = lineTotal(l) / subtotal.value;
        return sum + base * share * ((l.tax_rate || 0) / 100);
    }, 0);
});

const total = computed(() => subtotal.value - discountAmount.value + taxAmount.value);

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

// ── IA ────────────────────────────────────────────────────────────────────────
const { loading: aiLoading, suggestDescription, suggestPrice } = useAiAssist();
const aiAvailable = ref(false);

onMounted(async () => {
    try {
        const { data } = await axios.get('/ai/status');
        aiAvailable.value = data.available && data.plan_ok;
    } catch { /* IA non disponible */ }
});

const fillDescription = async (line) => {
    if (!aiAvailable.value) return;
    const name = props.products.find(p => p.id === line.product_id)?.name || line.description;
    if (!name) return;
    const desc = await suggestDescription(name);
    if (desc && !line.description) line.description = desc;
};

const fillPrice = async (line) => {
    if (!aiAvailable.value) return;
    const name = props.products.find(p => p.id === line.product_id)?.name || line.description;
    if (!name) return;
    const price = await suggestPrice(name, form.currency);
    if (price !== null && price > 0) line.unit_price = price;
};

// ── Modal création rapide client ─────────────────────────────────────────────
const showQuickModal = ref(false);
const quickSaving = ref(false);
const quickError = ref('');
const quickForm = ref({ type: 'company', name: '', email: '', phone: '', address: '' });

const openQuickModal = () => {
    quickForm.value = { type: 'company', name: '', email: '', phone: '', address: '' };
    quickError.value = '';
    showQuickModal.value = true;
};
const closeQuickModal = () => { showQuickModal.value = false; };

const saveQuickCustomer = async () => {
    quickError.value = '';
    if (!quickForm.value.name.trim()) { quickError.value = 'Le nom est requis.'; return; }
    quickSaving.value = true;
    try {
        const { data } = await axios.post(route('customers.quick'), quickForm.value);
        customersList.value.push(data);
        form.customer_id = data.id;
        closeQuickModal();
    } catch (e) {
        quickError.value = e.response?.data?.message || e.response?.data?.error || 'Erreur lors de la création.';
    } finally {
        quickSaving.value = false;
    }
};

// ── Sélecteur de templates par famille ───────────────────────────────────────
const templateFamilies = computed(() => {
    const groups = {};
    for (const t of props.templates) {
        (groups[t.family] ??= []).push(t);
    }
    return groups;
});
const activeFamily = ref(null);

const filteredTemplates = computed(() => {
    if (!activeFamily.value) return props.templates;
    return props.templates.filter(t => t.family === activeFamily.value);
});

const families = computed(() => [...new Set(props.templates.map(t => t.family))]);

// ── Submit ────────────────────────────────────────────────────────────────────
const submit = () => {
    if (isEdit.value) {
        form.put(route('documents.update', props.document.id));
    } else {
        form.post(route('documents.store'));
    }
};
</script>

<template>
    <Head :title="(isEdit ? 'Modifier ' : 'Nouveau ') + typeLabel" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ isEdit ? 'Modifier' : 'Nouveau' }} — {{ typeLabel }}
                    <span v-if="isEdit" class="ml-2 text-sm font-normal text-gray-400">{{ document.number }}</span>
                </h2>
                <Link :href="route('documents.index')" class="text-sm font-semibold text-gray-500 hover:underline">← Retour</Link>
            </div>
        </template>

        <!-- Modal création rapide client -->
        <Teleport to="body">
            <div v-if="showQuickModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
                @click.self="closeQuickModal">
                <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
                    <div class="flex items-center justify-between border-b px-6 py-4">
                        <h3 class="text-base font-bold text-gray-900">Nouveau client rapide</h3>
                        <button type="button" @click="closeQuickModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-4 px-6 py-5">
                        <p v-if="quickError" class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ quickError }}</p>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Type *</label>
                            <select v-model="quickForm.type" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="company">Société / Entreprise</option>
                                <option value="individual">Particulier</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nom *</label>
                            <input v-model="quickForm.name" type="text" placeholder="Nom de l'entreprise ou du client"
                                class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                @keyup.enter="saveQuickCustomer" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                                <input v-model="quickForm.email" type="email" placeholder="contact@..." class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Téléphone</label>
                                <input v-model="quickForm.phone" type="tel" placeholder="+225..." class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Adresse</label>
                            <input v-model="quickForm.address" type="text" class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 border-t px-6 py-4">
                        <button type="button" @click="closeQuickModal"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="button" @click="saveQuickCustomer" :disabled="quickSaving"
                            class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-60 flex items-center gap-2">
                            <span v-if="quickSaving" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            {{ quickSaving ? 'Enregistrement…' : 'Créer et sélectionner' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <div class="py-8">
            <form @submit.prevent="submit" class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- En-tête du document -->
                <div class="grid gap-4 rounded-xl bg-white p-6 shadow-sm border border-gray-100 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-if="!isEdit">
                        <InputLabel value="Type de document" />
                        <select v-model="form.type" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                    </div>

                    <!-- Sélecteur client avec bouton création rapide -->
                    <div>
                        <InputLabel value="Client" />
                        <div class="mt-1 flex items-center gap-1.5">
                            <select v-model="form.customer_id" class="block min-w-0 flex-1 rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option :value="null">— Aucun —</option>
                                <option v-for="c in customersList" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                            <button type="button" @click="openQuickModal"
                                class="flex-shrink-0 rounded-lg border border-brand-300 bg-brand-50 p-2 text-brand-700 hover:bg-brand-100 transition-colors"
                                title="Ajouter un nouveau client">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </button>
                        </div>
                        <InputError :message="form.errors.customer_id" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Date d'émission *" />
                        <TextInput v-model="form.issue_date" type="date" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.issue_date" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Échéance" />
                        <TextInput v-model="form.due_date" type="date" class="mt-1 block w-full" />
                        <InputError :message="form.errors.due_date" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Référence client" />
                        <TextInput v-model="form.reference" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="Devise" />
                        <TextInput v-model="form.currency" maxlength="3" class="mt-1 block w-full uppercase" />
                    </div>

                    <!-- Sélecteur de modèle PDF avec filtre par famille -->
                    <div v-if="templates.length" class="sm:col-span-2 lg:col-span-4">
                        <div class="flex items-center justify-between mb-2">
                            <InputLabel value="Modèle visuel du PDF" class="!mb-0" />
                            <span class="text-xs text-gray-400">{{ templates.length }} modèles disponibles</span>
                        </div>

                        <!-- Filtres familles -->
                        <div class="mb-2 flex flex-wrap gap-1.5">
                            <button type="button" @click="activeFamily = null"
                                class="rounded-full px-3 py-1 text-xs font-medium transition-colors"
                                :class="!activeFamily ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                                Tous ({{ templates.length }})
                            </button>
                            <button v-for="fam in families" :key="fam" type="button" @click="activeFamily = fam"
                                class="rounded-full px-3 py-1 text-xs font-medium transition-colors capitalize"
                                :class="activeFamily === fam ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                                {{ fam }}
                            </button>
                        </div>

                        <div class="max-h-52 overflow-y-auto rounded-lg border border-gray-100 p-2">
                            <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3 lg:grid-cols-5">
                                <button
                                    v-for="t in filteredTemplates"
                                    :key="t.key"
                                    type="button"
                                    @click="form.template_key = t.key"
                                    :title="t.description"
                                    class="flex items-center gap-2 rounded-lg border px-2.5 py-2 text-left transition-all"
                                    :class="form.template_key === t.key
                                        ? 'border-brand-600 bg-brand-50 ring-1 ring-brand-600'
                                        : 'border-gray-200 bg-white hover:border-gray-300 hover:shadow-sm'"
                                >
                                    <span class="flex shrink-0 items-center -space-x-1">
                                        <span class="h-4 w-4 rounded-full border-2 border-white shadow-sm" :style="{ backgroundColor: t.primary }"></span>
                                        <span class="h-4 w-4 rounded-full border-2 border-white shadow-sm" :style="{ backgroundColor: t.secondary }"></span>
                                        <span class="h-3.5 w-3.5 rounded-full border-2 border-white shadow-sm" :style="{ backgroundColor: t.accent }"></span>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-[11px] font-semibold leading-tight" :class="form.template_key === t.key ? 'text-brand-700' : 'text-gray-700'">
                                            {{ t.name }}
                                        </span>
                                        <span class="block truncate text-[9px] text-gray-400 capitalize">{{ t.family }}</span>
                                    </span>
                                    <span v-if="form.template_key === t.key" class="ml-auto flex-shrink-0 text-brand-600">
                                        <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                </button>
                            </div>
                        </div>
                        <InputError :message="form.errors.template_key" class="mt-1" />
                    </div>
                </div>

                <!-- Lignes du document -->
                <div class="overflow-hidden rounded-xl bg-white shadow-sm border border-gray-100">
                    <!-- Hint mode libre -->
                    <div class="flex items-start gap-2 border-b border-blue-100 bg-blue-50 px-4 py-2.5 text-xs text-blue-700">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span><strong>Saisie libre :</strong> Tapez directement votre description (formation, prestation, service…) sans sélectionner de produit catalogue. La colonne "Produit" est optionnelle.</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-brand-900 text-left text-xs uppercase tracking-wide text-white">
                                <tr>
                                    <th class="px-3 py-3" style="width: 17%">Produit <span class="font-normal opacity-60">(optionnel)</span></th>
                                    <th class="px-3 py-3" style="width: 27%">Description *</th>
                                    <th class="px-3 py-3 text-right" style="width: 7%">Qté</th>
                                    <th class="px-3 py-3 text-right" style="width: 11%">P.U. HT</th>
                                    <th class="px-3 py-3" style="width: 14%">
                                        <div class="flex items-center justify-end gap-1">
                                            Remise
                                            <span class="font-normal opacity-60 text-[10px]">(%/fixe)</span>
                                        </div>
                                    </th>
                                    <th class="px-3 py-3 text-right" style="width: 7%">TVA %</th>
                                    <th class="px-3 py-3 text-right" style="width: 13%">Total HT</th>
                                    <th class="px-2 py-3" style="width: 4%"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="(line, index) in form.lines" :key="index" class="align-top hover:bg-gray-50/50 transition-colors">
                                    <td class="px-3 py-2">
                                        <select
                                            v-model="line.product_id"
                                            @change="onProductSelect(line)"
                                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                        >
                                            <option :value="null">✏ Saisie libre</option>
                                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <textarea
                                            v-model="line.description"
                                            rows="1"
                                            required
                                            class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                        ></textarea>
                                        <button
                                            v-if="aiAvailable && !line.description"
                                            type="button"
                                            @click="fillDescription(line)"
                                            :disabled="aiLoading"
                                            class="mt-1 flex items-center gap-1 text-xs text-purple-600 hover:text-purple-800 disabled:opacity-50"
                                        >
                                            <span v-if="aiLoading" class="inline-block h-3 w-3 animate-spin rounded-full border border-purple-600 border-t-transparent"></span>
                                            <span v-else>✨</span>
                                            Suggestion IA
                                        </button>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input v-model.number="line.quantity" type="number" step="0.01" min="0.01"
                                            class="block w-full rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input v-model.number="line.unit_price" type="number" step="0.01" min="0"
                                            class="block w-full rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                        <button
                                            v-if="aiAvailable && !line.unit_price"
                                            type="button"
                                            @click="fillPrice(line)"
                                            :disabled="aiLoading"
                                            class="mt-1 flex items-center gap-1 text-xs text-green-600 hover:text-green-800 disabled:opacity-50"
                                        >
                                            <span v-if="aiLoading" class="inline-block h-3 w-3 animate-spin rounded-full border border-green-600 border-t-transparent"></span>
                                            <span v-else>💰</span> Prix IA
                                        </button>
                                    </td>
                                    <!-- Remise : type + valeur -->
                                    <td class="px-3 py-2">
                                        <div class="flex items-center gap-1">
                                            <!-- Toggle %/fixe -->
                                            <button type="button"
                                                @click="line.line_discount_type = line.line_discount_type === 'percent' ? 'fixed' : 'percent'; line.discount_percent = 0"
                                                class="flex-shrink-0 rounded-md border px-1.5 py-1 text-[10px] font-bold transition-colors"
                                                :class="line.line_discount_type === 'fixed'
                                                    ? 'border-amber-300 bg-amber-50 text-amber-700'
                                                    : 'border-gray-200 bg-gray-50 text-gray-600 hover:bg-gray-100'"
                                                :title="line.line_discount_type === 'fixed' ? 'Remise en montant fixe — cliquer pour passer en %' : 'Remise en % — cliquer pour passer en montant fixe'">
                                                {{ line.line_discount_type === 'fixed' ? '€' : '%' }}
                                            </button>
                                            <input v-model.number="line.discount_percent" type="number" step="0.01" min="0"
                                                :max="line.line_discount_type === 'percent' ? 100 : undefined"
                                                class="block w-full rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                                :placeholder="line.line_discount_type === 'fixed' ? '0' : '0'" />
                                        </div>
                                        <div v-if="line.discount_percent > 0" class="mt-0.5 text-right text-[10px] text-red-500">
                                            − {{ fmt(line.line_discount_type === 'fixed' ? line.discount_percent : line.quantity * line.unit_price * line.discount_percent / 100) }}
                                        </div>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input v-model.number="line.tax_rate" type="number" step="0.1" min="0" max="100"
                                            class="block w-full rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    </td>
                                    <td class="px-3 py-2 text-right font-semibold text-gray-800">{{ fmt(lineTotal(line)) }}</td>
                                    <td class="px-2 py-2 text-center">
                                        <button
                                            type="button"
                                            @click="removeLine(index)"
                                            :disabled="form.lines.length === 1"
                                            class="rounded-full p-1 text-red-400 hover:bg-red-50 hover:text-red-600 disabled:opacity-30 transition-colors"
                                            title="Supprimer">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex items-center justify-between border-t px-4 py-3">
                        <button type="button" @click="addLine"
                            class="flex items-center gap-1.5 text-sm font-semibold text-brand-600 hover:text-brand-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Ajouter une ligne
                        </button>
                        <span class="text-xs text-gray-400">{{ form.lines.length }} ligne{{ form.lines.length > 1 ? 's' : '' }}</span>
                        <InputError :message="form.errors.lines" class="mt-1" />
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Notes & conditions -->
                    <div class="space-y-4 rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                        <div>
                            <InputLabel value="Notes (visibles sur le document)" />
                            <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm"></textarea>
                        </div>
                        <div>
                            <InputLabel value="Conditions de paiement" />
                            <textarea v-model="form.terms" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm"></textarea>
                        </div>
                    </div>

                    <!-- Totaux -->
                    <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-100">
                        <h3 class="mb-4 text-sm font-semibold text-gray-700">Récapitulatif</h3>

                        <!-- Remise globale -->
                        <div class="mb-5 rounded-lg bg-gray-50 p-3">
                            <p class="mb-2 text-xs font-medium text-gray-600">Remise globale (sur sous-total)</p>
                            <div class="flex items-center gap-2">
                                <select v-model="form.discount_type" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                    <option :value="null">Aucune remise</option>
                                    <option value="percent">En pourcentage (%)</option>
                                    <option value="fixed">Montant fixe ({{ form.currency }})</option>
                                </select>
                                <input
                                    v-if="form.discount_type"
                                    v-model.number="form.discount_value"
                                    type="number" step="0.01" min="0"
                                    :placeholder="form.discount_type === 'percent' ? 'ex: 10' : 'ex: 5000'"
                                    class="w-28 rounded-lg border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                />
                            </div>
                        </div>

                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Sous-total HT</dt>
                                <dd class="font-semibold">{{ fmt(subtotal) }} {{ form.currency }}</dd>
                            </div>
                            <div v-if="discountAmount > 0" class="flex justify-between text-red-600">
                                <dt>
                                    Remise
                                    <span class="text-xs text-red-400">
                                        ({{ form.discount_type === 'percent' ? form.discount_value + '%' : 'montant fixe' }})
                                    </span>
                                </dt>
                                <dd>−{{ fmt(discountAmount) }} {{ form.currency }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">TVA</dt>
                                <dd class="font-semibold">{{ fmt(taxAmount) }} {{ form.currency }}</dd>
                            </div>
                            <div class="flex justify-between rounded-lg bg-brand-900 px-3 py-2.5 text-base text-white">
                                <dt class="font-bold">TOTAL TTC</dt>
                                <dd class="font-bold">{{ fmt(total) }} {{ form.currency }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="isEdit ? route('documents.show', document.id) : route('documents.index')">
                        <SecondaryButton type="button">Annuler</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing" class="flex items-center gap-2">
                        <span v-if="form.processing" class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                        {{ isEdit ? 'Enregistrer les modifications' : 'Créer le document' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

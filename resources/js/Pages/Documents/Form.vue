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

const emptyLine = () => ({
    product_id: null,
    description: '',
    quantity: 1,
    unit: 'unité',
    unit_price: 0,
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

const lineTotal = (line) =>
    Math.round(line.quantity * line.unit_price * (1 - (line.discount_percent || 0) / 100) * 100) / 100;

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

const { loading: aiLoading, suggestDescription, suggestPrice } = useAiAssist();
const aiAvailable = ref(false);

onMounted(async () => {
    try {
        const { data } = await (await import('axios')).default.get('/ai/status');
        aiAvailable.value = data.available && data.plan_ok;
    } catch { /* IA non disponible */ }
});

const onDescriptionBlur = async (line, index) => {
    // Suggestion automatique uniquement si pas de produit sélectionné et description vide
    if (!aiAvailable.value || line.product_id || line.description) return;
};

const fillDescription = async (line) => {
    if (!aiAvailable.value) return;
    const productName = props.products.find(p => p.id === line.product_id)?.name || line.description;
    if (!productName) return;
    const desc = await suggestDescription(productName);
    if (desc && !line.description) line.description = desc;
};

const fillPrice = async (line) => {
    if (!aiAvailable.value) return;
    const name = props.products.find(p => p.id === line.product_id)?.name || line.description;
    if (!name) return;
    const price = await suggestPrice(name, form.currency);
    if (price !== null && price > 0) line.unit_price = price;
};

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

        <div class="py-8">
            <form @submit.prevent="submit" class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- En-tête du document -->
                <div class="grid gap-4 rounded-lg bg-white p-6 shadow sm:grid-cols-2 lg:grid-cols-4">
                    <div v-if="!isEdit">
                        <InputLabel value="Type de document" />
                        <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option v-for="t in types" :key="t.value" :value="t.value">{{ t.label }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Client" />
                        <select v-model="form.customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option :value="null">— Aucun —</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
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

                    <!-- Sélecteur de modèle visuel PDF (cahier §16) -->
                    <div v-if="templates.length" class="sm:col-span-2 lg:col-span-4">
                        <InputLabel value="Modèle visuel du PDF" />
                        <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                            <button
                                v-for="t in templates"
                                :key="t.key"
                                type="button"
                                @click="form.template_key = t.key"
                                :title="t.description"
                                class="flex items-center gap-2 rounded-md border px-3 py-2 text-left transition"
                                :class="form.template_key === t.key
                                    ? 'border-brand-600 bg-brand-50 ring-1 ring-brand-600'
                                    : 'border-gray-200 bg-white hover:border-gray-300'"
                            >
                                <span class="flex shrink-0 items-center -space-x-1">
                                    <span class="h-4 w-4 rounded-full border border-white" :style="{ backgroundColor: t.primary }"></span>
                                    <span class="h-4 w-4 rounded-full border border-white" :style="{ backgroundColor: t.secondary }"></span>
                                    <span class="h-4 w-4 rounded-full border border-white" :style="{ backgroundColor: t.accent }"></span>
                                </span>
                                <span class="min-w-0">
                                    <span class="block truncate text-xs font-semibold" :class="form.template_key === t.key ? 'text-brand-700' : 'text-gray-700'">
                                        {{ t.name }}
                                    </span>
                                    <span class="block truncate text-[10px] text-gray-400">{{ t.family }}</span>
                                </span>
                            </button>
                        </div>
                        <InputError :message="form.errors.template_key" class="mt-1" />
                    </div>
                </div>

                <!-- Lignes -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="w-full text-sm">
                        <thead class="bg-brand-900 text-left text-xs uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-4 py-3" style="width: 18%">Produit</th>
                                <th class="px-4 py-3" style="width: 28%">Description *</th>
                                <th class="px-4 py-3 text-right" style="width: 9%">Qté</th>
                                <th class="px-4 py-3 text-right" style="width: 13%">P.U. HT</th>
                                <th class="px-4 py-3 text-right" style="width: 8%">Rem. %</th>
                                <th class="px-4 py-3 text-right" style="width: 8%">TVA %</th>
                                <th class="px-4 py-3 text-right" style="width: 12%">Total HT</th>
                                <th class="px-2 py-3" style="width: 4%"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="(line, index) in form.lines" :key="index" class="align-top">
                                <td class="px-4 py-2">
                                    <select
                                        v-model="line.product_id"
                                        @change="onProductSelect(line)"
                                        class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                    >
                                        <option :value="null">— Libre —</option>
                                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                </td>
                                <td class="px-4 py-2">
                                    <textarea
                                        v-model="line.description"
                                        rows="1"
                                        required
                                        @blur="onDescriptionBlur(line, index)"
                                        class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                    ></textarea>
                                    <button
                                        v-if="aiAvailable && !line.description"
                                        type="button"
                                        @click="fillDescription(line)"
                                        :disabled="aiLoading"
                                        class="mt-1 flex items-center gap-1 text-xs text-purple-600 hover:text-purple-800 disabled:opacity-50"
                                        title="Suggestion IA"
                                    >
                                        <span v-if="aiLoading" class="inline-block h-3 w-3 animate-spin rounded-full border border-purple-600 border-t-transparent"></span>
                                        <span v-else>✨</span>
                                        Suggestion IA
                                    </button>
                                </td>
                                <td class="px-4 py-2">
                                    <input v-model.number="line.quantity" type="number" step="0.01" min="0.01" class="block w-full rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                </td>
                                <td class="px-4 py-2">
                                    <input v-model.number="line.unit_price" type="number" step="0.01" min="0" class="block w-full rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    <button
                                        v-if="aiAvailable && !line.unit_price"
                                        type="button"
                                        @click="fillPrice(line)"
                                        :disabled="aiLoading"
                                        class="mt-1 flex items-center gap-1 text-xs text-green-600 hover:text-green-800 disabled:opacity-50"
                                        title="Prix suggéré"
                                    >
                                        <span v-if="aiLoading" class="inline-block h-3 w-3 animate-spin rounded-full border border-green-600 border-t-transparent"></span>
                                        <span v-else>💰</span>
                                        Prix suggéré
                                    </button>
                                </td>
                                <td class="px-4 py-2">
                                    <input v-model.number="line.discount_percent" type="number" step="0.1" min="0" max="100" class="block w-full rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                </td>
                                <td class="px-4 py-2">
                                    <input v-model.number="line.tax_rate" type="number" step="0.1" min="0" max="100" class="block w-full rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                </td>
                                <td class="px-4 py-2 text-right font-semibold">{{ fmt(lineTotal(line)) }}</td>
                                <td class="px-2 py-2 text-center">
                                    <button
                                        type="button"
                                        @click="removeLine(index)"
                                        :disabled="form.lines.length === 1"
                                        class="text-red-400 hover:text-red-600 disabled:opacity-30"
                                        title="Supprimer la ligne"
                                    >✕</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="border-t px-4 py-3">
                        <button type="button" @click="addLine" class="text-sm font-semibold text-brand-600 hover:underline">
                            + Ajouter une ligne
                        </button>
                        <InputError :message="form.errors.lines" class="mt-1" />
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Notes & conditions -->
                    <div class="space-y-4 rounded-lg bg-white p-6 shadow">
                        <div>
                            <InputLabel value="Notes (visibles sur le document)" />
                            <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                        </div>
                        <div>
                            <InputLabel value="Conditions de paiement" />
                            <textarea v-model="form.terms" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                        </div>
                    </div>

                    <!-- Totaux -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="mb-4 flex items-center gap-3">
                            <InputLabel value="Remise globale" class="shrink-0" />
                            <select v-model="form.discount_type" class="rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option :value="null">Aucune</option>
                                <option value="percent">%</option>
                                <option value="fixed">Montant fixe</option>
                            </select>
                            <input
                                v-if="form.discount_type"
                                v-model.number="form.discount_value"
                                type="number" step="0.01" min="0"
                                class="w-28 rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            />
                        </div>

                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-500">Sous-total HT</dt>
                                <dd class="font-semibold">{{ fmt(subtotal) }} {{ form.currency }}</dd>
                            </div>
                            <div v-if="discountAmount > 0" class="flex justify-between text-red-600">
                                <dt>Remise</dt>
                                <dd>−{{ fmt(discountAmount) }} {{ form.currency }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500">TVA</dt>
                                <dd class="font-semibold">{{ fmt(taxAmount) }} {{ form.currency }}</dd>
                            </div>
                            <div class="flex justify-between border-t pt-2 text-base">
                                <dt class="font-bold text-brand-900">TOTAL TTC</dt>
                                <dd class="font-bold text-brand-900">{{ fmt(total) }} {{ form.currency }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="isEdit ? route('documents.show', document.id) : route('documents.index')">
                        <SecondaryButton type="button">Annuler</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing">
                        {{ isEdit ? 'Enregistrer les modifications' : 'Créer le document' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

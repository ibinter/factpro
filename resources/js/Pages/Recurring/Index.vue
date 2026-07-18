<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    hasAccess: Boolean,
    templates: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ active: 0, mrr: 0 }) },
    customers: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] },
    defaults: { type: Object, default: () => ({ currency: 'XOF', tax_rate: 18 }) },
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);
const fmtDate = (d) => (d ? new Date(d + 'T00:00:00').toLocaleDateString('fr-FR') : '—');
const today = new Date().toISOString().slice(0, 10);

const FREQUENCIES = [
    { value: 'weekly', label: 'Hebdomadaire' },
    { value: 'monthly', label: 'Mensuel' },
    { value: 'quarterly', label: 'Trimestriel' },
    { value: 'semiannual', label: 'Semestriel' },
    { value: 'yearly', label: 'Annuel' },
];

const frequencyLabel = (t) => {
    const base = FREQUENCIES.find((f) => f.value === t.frequency)?.label ?? t.frequency;
    return t.interval > 1 ? `${base} ×${t.interval}` : base;
};

const isDue = (t) => t.is_active && t.next_run_date && t.next_run_date <= today;

/* ---- Éditeur (création / édition) ---- */
const showEditor = ref(false);
const editing = ref(null); // gabarit en cours d'édition (null = création)

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
    name: '',
    customer_id: null,
    frequency: 'monthly',
    interval: 1,
    day_of_month: null,
    next_run_date: today,
    due_days: 30,
    end_mode: 'never', // never | date | count (champ front uniquement)
    end_date: null,
    occurrences_limit: null,
    auto_finalize: true,
    auto_send: false,
    currency: props.defaults.currency,
    notes: '',
    terms: '',
    lines: [emptyLine()],
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.next_run_date = today;
    form.currency = props.defaults.currency;
    form.lines = [emptyLine()];
    showEditor.value = true;
};

const openEdit = (t) => {
    editing.value = t;
    form.clearErrors();
    form.name = t.name;
    form.customer_id = t.customer_id;
    form.frequency = t.frequency;
    form.interval = t.interval;
    form.day_of_month = t.day_of_month;
    form.next_run_date = t.next_run_date;
    form.due_days = t.due_days;
    form.end_mode = t.end_date ? 'date' : t.occurrences_limit ? 'count' : 'never';
    form.end_date = t.end_date;
    form.occurrences_limit = t.occurrences_limit;
    form.auto_finalize = !!t.auto_finalize;
    form.auto_send = !!t.auto_send;
    form.currency = t.currency;
    form.notes = t.notes ?? '';
    form.terms = t.terms ?? '';
    form.lines = (t.lines ?? []).map((l) => ({
        product_id: l.product_id ?? null,
        description: l.description ?? '',
        quantity: Number(l.quantity ?? 1),
        unit: l.unit ?? 'unité',
        unit_price: Number(l.unit_price ?? 0),
        discount_percent: Number(l.discount_percent ?? 0),
        tax_rate: Number(l.tax_rate ?? 0),
    }));
    if (!form.lines.length) form.lines = [emptyLine()];
    showEditor.value = true;
};

watch(
    () => form.end_mode,
    (mode) => {
        if (mode !== 'date') form.end_date = null;
        if (mode !== 'count') form.occurrences_limit = null;
    },
);

/* ---- Lignes du gabarit (même esprit que Documents/Form.vue) ---- */
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
const taxAmount = computed(() => form.lines.reduce((sum, l) => sum + lineTotal(l) * ((l.tax_rate || 0) / 100), 0));
const total = computed(() => subtotal.value + taxAmount.value);

const selectedCustomer = computed(() => props.customers.find((c) => c.id === form.customer_id));

const submit = () => {
    const transformed = form.transform((data) => {
        const { end_mode, ...payload } = data;
        return payload;
    });
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            showEditor.value = false;
            form.reset();
        },
    };
    if (editing.value) {
        transformed.put(route('recurring.update', editing.value.id), options);
    } else {
        transformed.post(route('recurring.store'), options);
    }
};

/* ---- Actions du tableau ---- */
const runNow = (t) => {
    if (confirm(`Générer immédiatement une facture depuis « ${t.name} » ?\nLa prochaine échéance sera recalculée à partir d'aujourd'hui.`)) {
        router.post(route('recurring.run', t.id));
    }
};

const toggle = (t) => router.post(route('recurring.toggle', t.id), {}, { preserveScroll: true });

const destroy = (t) => {
    if (confirm(`Supprimer le gabarit « ${t.name} » ?\nLes factures déjà générées seront conservées.`)) {
        router.delete(route('recurring.destroy', t.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Factures récurrentes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">🔁 Factures récurrentes</h2>
                <PrimaryButton v-if="hasAccess" @click="openCreate">+ Nouveau gabarit</PrimaryButton>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Upsell si forfait STARTER -->
                <div v-if="!hasAccess" class="mx-auto max-w-2xl rounded-lg bg-white p-8 text-center shadow">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gold-400/20 text-3xl">🔁</div>
                    <h3 class="text-lg font-semibold text-brand-900">Abonnements automatiques disponibles dès le forfait PRO</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Créez des gabarits de facturation périodique (hebdomadaire, mensuel, trimestriel, semestriel
                        ou annuel) : vos factures d'abonnement sont générées automatiquement à chaque échéance,
                        scellées et même envoyées par email à vos clients si vous le souhaitez.
                    </p>
                    <Link :href="route('billing.plans')" class="mt-6 inline-block">
                        <PrimaryButton>Voir les forfaits</PrimaryButton>
                    </Link>
                </div>

                <template v-else>
                    <!-- Stats -->
                    <div class="mb-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-sm font-medium text-gray-500">Gabarits actifs</div>
                            <div class="mt-1 text-3xl font-bold text-brand-900">{{ stats.active }}</div>
                        </div>
                        <div class="rounded-lg bg-brand-900 p-5 text-white shadow">
                            <div class="text-sm font-medium text-white/70">MRR estimé (revenu mensuel récurrent)</div>
                            <div class="mt-1 text-3xl font-bold text-gold-400">{{ fmt(stats.mrr) }} {{ defaults.currency }}</div>
                        </div>
                    </div>

                    <!-- Tableau des gabarits -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="px-4 py-3">Gabarit</th>
                                        <th class="px-4 py-3">Client</th>
                                        <th class="px-4 py-3">Fréquence</th>
                                        <th class="px-4 py-3">Prochaine émission</th>
                                        <th class="px-4 py-3 text-center">Occurrences</th>
                                        <th class="px-4 py-3 text-right">Total TTC</th>
                                        <th class="px-4 py-3 text-center">Auto</th>
                                        <th class="px-4 py-3 text-center">Statut</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="t in templates" :key="t.id" class="hover:bg-gray-50" :class="{ 'opacity-60': !t.is_active }">
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-800">{{ t.name }}</div>
                                            <div v-if="t.last_run_date" class="text-xs text-gray-400">Dernière : {{ fmtDate(t.last_run_date) }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">{{ t.customer?.name ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-block rounded-full bg-brand-600/10 px-2.5 py-0.5 text-xs font-semibold text-brand-600">
                                                {{ frequencyLabel(t) }}
                                            </span>
                                            <div v-if="t.day_of_month" class="mt-0.5 text-xs text-gray-400">le {{ t.day_of_month }} du mois</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                                :class="isDue(t) ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'"
                                            >
                                                {{ fmtDate(t.next_run_date) }}
                                            </span>
                                            <div v-if="t.end_date" class="mt-0.5 text-xs text-gray-400">fin : {{ fmtDate(t.end_date) }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold text-gray-700">
                                            {{ t.occurrences_done }}<span v-if="t.occurrences_limit" class="font-normal text-gray-400"> / {{ t.occurrences_limit }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-800">{{ fmt(t.total) }} {{ t.currency }}</td>
                                        <td class="px-4 py-3 text-center text-base">
                                            <span v-if="t.auto_finalize" title="Finalisation automatique (document scellé)">🔒</span>
                                            <span v-if="t.auto_send" title="Envoi email automatique au client">✉</span>
                                            <span v-if="!t.auto_finalize && !t.auto_send" class="text-xs text-gray-400">—</span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span
                                                class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                                :class="t.is_active ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                                            >
                                                {{ t.is_active ? 'Actif' : 'En pause' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                                <button
                                                    type="button" @click="runNow(t)"
                                                    class="rounded px-1.5 py-0.5 text-brand-600 hover:bg-brand-600/10"
                                                    title="Générer une facture maintenant"
                                                >▶</button>
                                                <button
                                                    type="button" @click="toggle(t)"
                                                    class="rounded px-1.5 py-0.5 text-gray-500 hover:bg-gray-100"
                                                    :title="t.is_active ? 'Mettre en pause' : 'Reprendre'"
                                                >{{ t.is_active ? '⏸' : '▶️' }}</button>
                                                <button
                                                    type="button" @click="openEdit(t)"
                                                    class="text-xs font-semibold text-brand-600 hover:underline"
                                                >Modifier</button>
                                                <button
                                                    type="button" @click="destroy(t)"
                                                    class="text-xs font-semibold text-red-500 hover:underline"
                                                >Supprimer</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!templates.length">
                                        <td colspan="9" class="px-4 py-10 text-center text-gray-500">
                                            Aucun gabarit de facture récurrente.
                                            <button type="button" class="ml-1 font-semibold text-brand-600 hover:underline" @click="openCreate">
                                                Créez votre premier abonnement
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Panneau création / édition -->
        <div v-if="showEditor" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-gray-900/60" @click="showEditor = false"></div>
            <div class="relative mx-auto my-8 w-full max-w-5xl px-4">
                <form @submit.prevent="submit" class="relative rounded-lg bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b px-6 py-4">
                        <h3 class="text-lg font-semibold text-brand-900">
                            {{ editing ? 'Modifier le gabarit' : 'Nouveau gabarit de facture récurrente' }}
                        </h3>
                        <button type="button" class="text-2xl leading-none text-gray-400 hover:text-gray-600" @click="showEditor = false">&times;</button>
                    </div>

                    <div class="max-h-[75vh] space-y-6 overflow-y-auto p-6">
                        <!-- Identité -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Libellé interne *" />
                                <TextInput v-model="form.name" class="mt-1 block w-full" placeholder="Abonnement maintenance mensuel" required />
                                <InputError :message="form.errors.name" class="mt-1" />
                            </div>
                            <div>
                                <InputLabel value="Client *" />
                                <select v-model="form.customer_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                    <option :value="null" disabled>— Choisir un client —</option>
                                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                                <InputError :message="form.errors.customer_id" class="mt-1" />
                            </div>
                        </div>

                        <!-- Planification -->
                        <div class="rounded-lg border border-gray-200 p-4">
                            <h4 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Planification</h4>
                            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <InputLabel value="Fréquence *" />
                                    <select v-model="form.frequency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                        <option v-for="f in FREQUENCIES" :key="f.value" :value="f.value">{{ f.label }}</option>
                                    </select>
                                    <InputError :message="form.errors.frequency" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Toutes les N périodes" />
                                    <TextInput v-model.number="form.interval" type="number" min="1" max="12" class="mt-1 block w-full" />
                                    <InputError :message="form.errors.interval" class="mt-1" />
                                </div>
                                <div v-if="form.frequency !== 'weekly'">
                                    <InputLabel value="Jour du mois (1–28)" />
                                    <TextInput v-model.number="form.day_of_month" type="number" min="1" max="28" class="mt-1 block w-full" placeholder="ex. 1" />
                                    <InputError :message="form.errors.day_of_month" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Première émission *" />
                                    <TextInput v-model="form.next_run_date" type="date" class="mt-1 block w-full" required />
                                    <InputError :message="form.errors.next_run_date" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel value="Échéance : émission + N jours" />
                                    <TextInput v-model.number="form.due_days" type="number" min="0" max="120" class="mt-1 block w-full" />
                                    <InputError :message="form.errors.due_days" class="mt-1" />
                                </div>
                                <div class="sm:col-span-2 lg:col-span-3">
                                    <InputLabel value="Fin de l'abonnement" />
                                    <div class="mt-1 flex flex-wrap items-center gap-4 text-sm text-gray-700">
                                        <label class="flex items-center gap-1.5">
                                            <input type="radio" v-model="form.end_mode" value="never" class="border-gray-300 text-brand-600 focus:ring-brand-500" /> Sans fin
                                        </label>
                                        <label class="flex items-center gap-1.5">
                                            <input type="radio" v-model="form.end_mode" value="date" class="border-gray-300 text-brand-600 focus:ring-brand-500" /> Jusqu'au
                                            <TextInput v-if="form.end_mode === 'date'" v-model="form.end_date" type="date" class="text-sm" />
                                        </label>
                                        <label class="flex items-center gap-1.5">
                                            <input type="radio" v-model="form.end_mode" value="count" class="border-gray-300 text-brand-600 focus:ring-brand-500" /> Après
                                            <TextInput v-if="form.end_mode === 'count'" v-model.number="form.occurrences_limit" type="number" min="1" max="999" class="w-20 text-sm" />
                                            <span v-if="form.end_mode === 'count'">facture(s)</span>
                                        </label>
                                    </div>
                                    <InputError :message="form.errors.end_date || form.errors.occurrences_limit" class="mt-1" />
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-6 border-t pt-4 text-sm text-gray-700">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" v-model="form.auto_finalize" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                    🔒 Finaliser automatiquement (facture scellée, infalsifiable)
                                </label>
                                <label class="flex items-start gap-2">
                                    <input type="checkbox" v-model="form.auto_send" class="mt-0.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                    <span>
                                        ✉ Envoyer par email au client
                                        <span class="block text-xs text-gray-400">nécessite un email client</span>
                                        <span v-if="form.auto_send && selectedCustomer && !selectedCustomer.email" class="block text-xs font-semibold text-amber-600">
                                            ⚠ Ce client n'a pas d'adresse email : la facture sera générée mais pas envoyée.
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- Lignes du gabarit -->
                        <div class="overflow-hidden rounded-lg border border-gray-200">
                            <table class="w-full text-sm">
                                <thead class="bg-brand-900 text-left text-xs uppercase tracking-wide text-white">
                                    <tr>
                                        <th class="px-3 py-2.5" style="width: 20%">Produit</th>
                                        <th class="px-3 py-2.5" style="width: 30%">Description *</th>
                                        <th class="px-3 py-2.5 text-right" style="width: 10%">Qté</th>
                                        <th class="px-3 py-2.5 text-right" style="width: 14%">P.U. HT</th>
                                        <th class="px-3 py-2.5 text-right" style="width: 9%">TVA %</th>
                                        <th class="px-3 py-2.5 text-right" style="width: 13%">Total HT</th>
                                        <th class="px-2 py-2.5" style="width: 4%"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="(line, index) in form.lines" :key="index" class="align-top">
                                        <td class="px-3 py-2">
                                            <select
                                                v-model="line.product_id"
                                                @change="onProductSelect(line)"
                                                class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                            >
                                                <option :value="null">— Libre —</option>
                                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                            </select>
                                        </td>
                                        <td class="px-3 py-2">
                                            <textarea
                                                v-model="line.description"
                                                rows="1"
                                                required
                                                class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                            ></textarea>
                                            <InputError :message="form.errors[`lines.${index}.description`]" class="mt-1" />
                                        </td>
                                        <td class="px-3 py-2">
                                            <input v-model.number="line.quantity" type="number" step="0.01" min="0.01" class="block w-full rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                        </td>
                                        <td class="px-3 py-2">
                                            <input v-model.number="line.unit_price" type="number" step="0.01" min="0" class="block w-full rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                        </td>
                                        <td class="px-3 py-2">
                                            <input v-model.number="line.tax_rate" type="number" step="0.1" min="0" max="100" class="block w-full rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold">{{ fmt(lineTotal(line)) }}</td>
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
                            <div class="flex items-center justify-between border-t bg-gray-50 px-3 py-2.5">
                                <button type="button" @click="addLine" class="text-sm font-semibold text-brand-600 hover:underline">
                                    + Ajouter une ligne
                                </button>
                                <div class="text-sm">
                                    <span class="mr-4 text-gray-500">HT : {{ fmt(subtotal) }} · TVA : {{ fmt(taxAmount) }}</span>
                                    <span class="font-bold text-brand-900">TOTAL TTC : {{ fmt(total) }} {{ form.currency }}</span>
                                </div>
                            </div>
                            <InputError :message="form.errors.lines" class="px-3 pb-2" />
                        </div>

                        <!-- Notes & conditions -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Notes (visibles sur la facture)" />
                                <textarea v-model="form.notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                            </div>
                            <div>
                                <InputLabel value="Conditions de paiement" />
                                <textarea v-model="form.terms" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t bg-gray-50 px-6 py-4">
                        <SecondaryButton type="button" @click="showEditor = false">Annuler</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">
                            {{ editing ? 'Enregistrer les modifications' : 'Créer le gabarit' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

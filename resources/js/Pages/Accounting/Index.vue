<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    hasAccess: Boolean,
    tab: { type: String, default: 'journal' },
    from: { type: String, default: '' },
    to: { type: String, default: '' },
    data: { type: Object, default: null },
    currency: { type: String, default: 'XOF' },
    taxRate: { type: Number, default: 18 },
    fecYear: { type: Number, default: new Date().getFullYear() },
});

/* ---- Formatage montants (Intl fr-FR + devise société) ---- */
let currencyFormatter;
try {
    currencyFormatter = new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: props.currency,
        maximumFractionDigits: 2,
    });
} catch {
    currencyFormatter = null;
}
const money = (value) => {
    const n = Number(value ?? 0);
    if (currencyFormatter) return currencyFormatter.format(n);
    return `${new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 }).format(n)} ${props.currency}`;
};
const amountClass = (value) => (Number(value) < 0 ? 'text-red-600' : 'text-gray-800');

const monthLabel = (ym) => {
    const [year, month] = String(ym).split('-').map(Number);
    const label = new Intl.DateTimeFormat('fr-FR', { month: 'long', year: 'numeric' }).format(new Date(year, month - 1, 1));
    return label.charAt(0).toUpperCase() + label.slice(1);
};

/* ---- Onglets ---- */
const tabs = [
    { key: 'journal', label: 'Journal des ventes' },
    { key: 'purchases', label: 'Achats' },
    { key: 'aged', label: 'Balance âgée' },
    { key: 'vat', label: 'TVA' },
    { key: 'pnl', label: 'Résultat' },
];

/* ---- Badges statut de paiement (journal des achats) ---- */
const PURCHASE_STATUS = {
    unpaid: { label: 'Impayé', class: 'bg-red-100 text-red-700' },
    partial: { label: 'Partiel', class: 'bg-amber-100 text-amber-700' },
    paid: { label: 'Payé', class: 'bg-green-100 text-green-700' },
};

/* ---- Période ---- */
const from = ref(props.from);
const to = ref(props.to);

const reload = (tab = props.tab) => {
    router.get(
        route('accounting.index'),
        { tab, from: from.value, to: to.value },
        { preserveState: true, preserveScroll: true },
    );
};

const pad = (n) => String(n).padStart(2, '0');
const iso = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

const setThisMonth = () => {
    const now = new Date();
    from.value = iso(new Date(now.getFullYear(), now.getMonth(), 1));
    to.value = iso(new Date(now.getFullYear(), now.getMonth() + 1, 0));
    reload();
};
const setThisQuarter = () => {
    const now = new Date();
    const q = Math.floor(now.getMonth() / 3);
    from.value = iso(new Date(now.getFullYear(), q * 3, 1));
    to.value = iso(new Date(now.getFullYear(), q * 3 + 3, 0));
    reload();
};
const setThisYear = () => {
    const now = new Date();
    from.value = `${now.getFullYear()}-01-01`;
    to.value = `${now.getFullYear()}-12-31`;
    reload();
};

/* ---- Exports ---- */
const csvUrl = computed(() => route('accounting.journal.csv', { from: props.from, to: props.to }));
const fecUrl = computed(() => route('accounting.fec', { year: props.fecYear }));

/* ---- Balance âgée : intensité du rouge selon l'ancienneté ---- */
const bucketClass = (value, level) => {
    if (Number(value) === 0) return 'text-gray-300';
    return ['text-gray-800', 'text-amber-600', 'text-orange-600', 'text-red-600 font-semibold', 'text-red-700 font-bold'][level];
};
</script>

<template>
    <Head title="Comptabilité" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Comptabilité simplifiée</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Upsell si forfait insuffisant -->
                <div v-if="!hasAccess" class="mx-auto max-w-2xl rounded-lg bg-white p-8 text-center shadow">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gold-400/20 text-3xl">📒</div>
                    <h3 class="text-lg font-semibold text-brand-900">Comptabilité disponible à partir du forfait BUSINESS</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Journal des ventes automatique, balance âgée clients, récapitulatif TVA,
                        compte de résultat simplifié et export FEC (norme française) —
                        avec les forfaits BUSINESS et ENTERPRISE.
                    </p>
                    <Link :href="route('billing.plans')" class="mt-6 inline-block">
                        <PrimaryButton>Voir les forfaits</PrimaryButton>
                    </Link>
                </div>

                <div v-else class="space-y-4">
                    <!-- Sélecteur de période -->
                    <div class="flex flex-wrap items-end gap-3 rounded-lg bg-white p-4 shadow">
                        <div>
                            <InputLabel value="Du" class="text-xs" />
                            <TextInput v-model="from" type="date" class="mt-1 text-sm" @change="reload()" />
                        </div>
                        <div>
                            <InputLabel value="Au" class="text-xs" />
                            <TextInput v-model="to" type="date" class="mt-1 text-sm" @change="reload()" />
                        </div>
                        <div class="flex gap-2 pb-0.5">
                            <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 transition hover:border-brand-600 hover:text-brand-600" @click="setThisMonth">Ce mois</button>
                            <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 transition hover:border-brand-600 hover:text-brand-600" @click="setThisQuarter">Ce trimestre</button>
                            <button type="button" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-medium text-gray-700 transition hover:border-brand-600 hover:text-brand-600" @click="setThisYear">Cette année</button>
                        </div>
                        <p v-if="tab === 'aged'" class="pb-1 text-xs text-gray-400">La balance âgée est calculée à date du jour (période ignorée).</p>
                    </div>

                    <!-- Onglets -->
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex flex-wrap gap-6">
                            <button
                                v-for="t in tabs" :key="t.key" type="button"
                                class="border-b-2 px-1 pb-3 text-sm font-medium transition"
                                :class="tab === t.key
                                    ? 'border-brand-600 text-brand-600'
                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                                @click="reload(t.key)"
                            >
                                {{ t.label }}
                            </button>
                            <Link
                                :href="route('accounting.export.index')"
                                class="border-b-2 border-transparent px-1 pb-3 text-sm font-medium text-gray-500 transition hover:border-gray-300 hover:text-gray-700"
                            >
                                Exports tiers
                            </Link>
                        </nav>
                    </div>

                    <!-- ============ JOURNAL DES VENTES ============ -->
                    <div v-if="tab === 'journal' && data" class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-4 py-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                                Journal des ventes — {{ data.lines.length }} écriture(s)
                            </h3>
                            <div class="flex gap-2">
                                <a :href="csvUrl" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:border-brand-600 hover:text-brand-600">⬇ CSV</a>
                                <a :href="fecUrl" class="rounded-md bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-brand-500">⬇ FEC {{ fecYear }}</a>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="px-4 py-2">Date</th>
                                        <th class="px-2 py-2">Pièce</th>
                                        <th class="px-2 py-2">Client</th>
                                        <th class="px-2 py-2">Type</th>
                                        <th class="px-2 py-2 text-right">HT</th>
                                        <th class="px-2 py-2 text-right">TVA</th>
                                        <th class="px-4 py-2 text-right">TTC</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="line in data.lines" :key="line.piece" class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-gray-600">{{ line.date }}</td>
                                        <td class="px-2 py-2 font-mono text-xs text-gray-800">{{ line.piece }}</td>
                                        <td class="px-2 py-2 text-gray-800">{{ line.client }}</td>
                                        <td class="px-2 py-2 text-xs text-gray-500">{{ line.type_label }}</td>
                                        <td class="px-2 py-2 text-right" :class="amountClass(line.ht)">{{ money(line.ht) }}</td>
                                        <td class="px-2 py-2 text-right" :class="amountClass(line.tva)">{{ money(line.tva) }}</td>
                                        <td class="px-4 py-2 text-right font-medium" :class="amountClass(line.ttc)">{{ money(line.ttc) }}</td>
                                    </tr>
                                    <tr v-if="!data.lines.length">
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucun document finalisé sur la période.</td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="data.lines.length" class="border-t-2 border-gray-300 bg-gray-50 font-semibold">
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 text-gray-700">Totaux</td>
                                        <td class="px-2 py-2 text-right" :class="amountClass(data.totals.ht)">{{ money(data.totals.ht) }}</td>
                                        <td class="px-2 py-2 text-right" :class="amountClass(data.totals.tva)">{{ money(data.totals.tva) }}</td>
                                        <td class="px-4 py-2 text-right" :class="amountClass(data.totals.ttc)">{{ money(data.totals.ttc) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- ============ JOURNAL DES ACHATS ============ -->
                    <div v-if="tab === 'purchases' && data" class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b px-4 py-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                                Journal des achats — {{ data.lines.length }} facture(s)
                            </h3>
                            <Link :href="route('purchases.index')" class="rounded-md border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:border-brand-600 hover:text-brand-600">
                                Gérer les achats →
                            </Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="px-4 py-2">Date</th>
                                        <th class="px-2 py-2">Pièce</th>
                                        <th class="px-2 py-2">Fournisseur</th>
                                        <th class="px-2 py-2">Catégorie</th>
                                        <th class="px-2 py-2 text-right">HT</th>
                                        <th class="px-2 py-2 text-right">TVA déduct.</th>
                                        <th class="px-2 py-2 text-right">TTC</th>
                                        <th class="px-4 py-2 text-center">Statut</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="(line, idx) in data.lines" :key="idx" class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-gray-600">{{ line.date }}</td>
                                        <td class="px-2 py-2 font-mono text-xs text-gray-800">{{ line.piece }}</td>
                                        <td class="px-2 py-2 text-gray-800">{{ line.fournisseur }}</td>
                                        <td class="px-2 py-2 text-xs text-gray-500">{{ line.categorie }}</td>
                                        <td class="px-2 py-2 text-right" :class="amountClass(line.ht)">{{ money(line.ht) }}</td>
                                        <td class="px-2 py-2 text-right" :class="amountClass(line.tva)">{{ money(line.tva) }}</td>
                                        <td class="px-2 py-2 text-right font-medium" :class="amountClass(line.ttc)">{{ money(line.ttc) }}</td>
                                        <td class="px-4 py-2 text-center">
                                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="PURCHASE_STATUS[line.statut]?.class">
                                                {{ PURCHASE_STATUS[line.statut]?.label ?? line.statut }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!data.lines.length">
                                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">Aucune facture d'achat sur la période.</td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="data.lines.length" class="border-t-2 border-gray-300 bg-gray-50 font-semibold">
                                    <tr>
                                        <td colspan="4" class="px-4 py-2 text-gray-700">Totaux</td>
                                        <td class="px-2 py-2 text-right" :class="amountClass(data.totals.ht)">{{ money(data.totals.ht) }}</td>
                                        <td class="px-2 py-2 text-right" :class="amountClass(data.totals.tva)">{{ money(data.totals.tva) }}</td>
                                        <td class="px-2 py-2 text-right" :class="amountClass(data.totals.ttc)">{{ money(data.totals.ttc) }}</td>
                                        <td class="px-4 py-2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- ============ BALANCE ÂGÉE ============ -->
                    <div v-if="tab === 'aged' && data" class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="border-b px-4 py-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Balance âgée clients — encours par ancienneté</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="px-4 py-2">Client</th>
                                        <th class="px-2 py-2 text-right">Non échu</th>
                                        <th class="px-2 py-2 text-right">0–30 j</th>
                                        <th class="px-2 py-2 text-right">31–60 j</th>
                                        <th class="px-2 py-2 text-right">61–90 j</th>
                                        <th class="px-2 py-2 text-right text-red-600">+90 j</th>
                                        <th class="px-4 py-2 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="row in data.rows" :key="row.client" class="hover:bg-gray-50">
                                        <td class="px-4 py-2 font-medium text-gray-800">{{ row.client }}</td>
                                        <td class="px-2 py-2 text-right" :class="bucketClass(row.current, 0)">{{ money(row.current) }}</td>
                                        <td class="px-2 py-2 text-right" :class="bucketClass(row.b0_30, 1)">{{ money(row.b0_30) }}</td>
                                        <td class="px-2 py-2 text-right" :class="bucketClass(row.b31_60, 2)">{{ money(row.b31_60) }}</td>
                                        <td class="px-2 py-2 text-right" :class="bucketClass(row.b61_90, 3)">{{ money(row.b61_90) }}</td>
                                        <td class="px-2 py-2 text-right" :class="bucketClass(row.b90_plus, 4)">{{ money(row.b90_plus) }}</td>
                                        <td class="px-4 py-2 text-right font-semibold" :class="amountClass(row.total)">{{ money(row.total) }}</td>
                                    </tr>
                                    <tr v-if="!data.rows.length">
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">Aucun encours client. 🎉</td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="data.rows.length" class="border-t-2 border-gray-300 bg-gray-50 font-semibold">
                                    <tr>
                                        <td class="px-4 py-2 text-gray-700">Totaux</td>
                                        <td class="px-2 py-2 text-right">{{ money(data.totals.current) }}</td>
                                        <td class="px-2 py-2 text-right">{{ money(data.totals.b0_30) }}</td>
                                        <td class="px-2 py-2 text-right">{{ money(data.totals.b31_60) }}</td>
                                        <td class="px-2 py-2 text-right">{{ money(data.totals.b61_90) }}</td>
                                        <td class="px-2 py-2 text-right text-red-700">{{ money(data.totals.b90_plus) }}</td>
                                        <td class="px-4 py-2 text-right">{{ money(data.totals.total) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- ============ RÉCAPITULATIF TVA ============ -->
                    <div v-if="tab === 'vat' && data" class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="border-b px-4 py-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Récapitulatif TVA par mois</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                                    <tr>
                                        <th class="px-4 py-2">Mois</th>
                                        <th class="px-2 py-2 text-right">Base HT</th>
                                        <th class="px-2 py-2 text-right">TVA collectée</th>
                                        <th class="px-2 py-2 text-right">TVA déductible</th>
                                        <th class="px-4 py-2 text-right">TVA nette</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="row in data.rows" :key="row.month" class="hover:bg-gray-50">
                                        <td class="px-4 py-2 font-medium text-gray-800">{{ monthLabel(row.month) }}</td>
                                        <td class="px-2 py-2 text-right" :class="amountClass(row.ht)">{{ money(row.ht) }}</td>
                                        <td class="px-2 py-2 text-right" :class="amountClass(row.tva)">{{ money(row.tva) }}</td>
                                        <td class="px-2 py-2 text-right text-gray-600">{{ money(row.vat_deductible) }}</td>
                                        <td class="px-4 py-2 text-right font-medium" :class="amountClass(row.vat_net)">{{ money(row.vat_net) }}</td>
                                    </tr>
                                    <tr v-if="!data.rows.length">
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Aucune écriture de TVA sur la période.</td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="data.rows.length" class="border-t-2 border-gray-300 bg-gray-50 font-semibold">
                                    <tr>
                                        <td class="px-4 py-2 text-gray-700">Totaux</td>
                                        <td class="px-2 py-2 text-right">{{ money(data.totals.ht) }}</td>
                                        <td class="px-2 py-2 text-right">{{ money(data.totals.tva) }}</td>
                                        <td class="px-2 py-2 text-right">{{ money(data.totals.vat_deductible) }}</td>
                                        <td class="px-4 py-2 text-right">{{ money(data.totals.vat_net) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <p class="border-t px-4 py-3 text-xs text-gray-500">
                            Récapitulatif destiné à préparer votre déclaration — TVA {{ taxRate }}%.
                            TVA nette = TVA collectée (ventes) − TVA déductible (achats fournisseurs).
                        </p>
                    </div>

                    <!-- ============ COMPTE DE RÉSULTAT ============ -->
                    <div v-if="tab === 'pnl' && data" class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-lg bg-white p-6 shadow">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Chiffre d'affaires HT</p>
                                <p class="mt-2 text-2xl font-bold text-brand-900">{{ money(data.revenue) }}</p>
                            </div>
                            <div class="rounded-lg bg-white p-6 shadow">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Charges</p>
                                <p class="mt-2 text-2xl font-bold text-gray-700">{{ money(data.expenses) }}</p>
                                <p class="mt-1 text-xs text-gray-400">
                                    Notes de frais + achats fournisseurs (HT).
                                </p>
                            </div>
                            <div class="rounded-lg p-6 shadow" :class="data.result >= 0 ? 'bg-emerald-600' : 'bg-red-600'">
                                <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Résultat</p>
                                <p class="mt-2 text-2xl font-bold text-white">{{ money(data.result) }}</p>
                                <p v-if="data.margin !== null" class="mt-1 text-sm font-medium text-white/90">
                                    Marge : {{ new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 1 }).format(data.margin) }} %
                                </p>
                            </div>
                        </div>
                        <p class="text-xs text-gray-400">
                            Compte de résultat simplifié : CA HT (ventes finalisées, avoirs déduits) − charges
                            (notes de frais approuvées/remboursées + achats fournisseurs HT de la période,
                            en comptabilité d'engagement).
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

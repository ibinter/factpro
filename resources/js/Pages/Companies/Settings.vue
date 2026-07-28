<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    company: Object,
    templates: Array,
});

// ─── Tabs ───────────────────────────────────────────────────────────────────
const tabs = [
    { id: 'identity',  label: 'Identité',      icon: '🏢' },
    { id: 'contact',   label: 'Coordonnées',   icon: '📍' },
    { id: 'billing',   label: 'Facturation',   icon: '🧾' },
    { id: 'style',     label: 'Apparence',     icon: '🎨' },
    { id: 'signature', label: 'Signature',     icon: '✍️' },
    { id: 'payments',  label: 'Paiements',     icon: '💳' },
];
const activeTab = ref('identity');

// ─── Formulaire principal ────────────────────────────────────────────────────
const form = useForm({
    name:             props.company.name             ?? '',
    legal_name:       props.company.legal_name       ?? '',
    email:            props.company.email            ?? '',
    phone:            props.company.phone            ?? '',
    address:          props.company.address          ?? '',
    city:             props.company.city             ?? '',
    country:          props.company.country          ?? 'CI',
    currency:         props.company.currency         ?? 'XOF',
    tax_id:           props.company.tax_id           ?? '',
    trade_register:   props.company.trade_register   ?? '',
    invoice_footer:   props.company.invoice_footer   ?? '',
    default_tax_rate: props.company.default_tax_rate ?? 0,
    default_template: props.company.default_template ?? '',
});

const submit = () => form.patch(route('companies.settings.update'), { preserveScroll: true });

// Templates PDF groupés par famille
const templateFamilies = computed(() => {
    const groups = {};
    for (const t of props.templates) (groups[t.family] ??= []).push(t);
    return groups;
});

// Pays OHADA + principaux
const countries = [
    { code: 'CI', label: "Côte d'Ivoire" },
    { code: 'SN', label: 'Sénégal' },
    { code: 'CM', label: 'Cameroun' },
    { code: 'ML', label: 'Mali' },
    { code: 'BF', label: 'Burkina Faso' },
    { code: 'TG', label: 'Togo' },
    { code: 'BJ', label: 'Bénin' },
    { code: 'GN', label: 'Guinée' },
    { code: 'NE', label: 'Niger' },
    { code: 'GA', label: 'Gabon' },
    { code: 'CG', label: 'Congo' },
    { code: 'CD', label: 'Congo (RDC)' },
    { code: 'MA', label: 'Maroc' },
    { code: 'TN', label: 'Tunisie' },
    { code: 'DZ', label: 'Algérie' },
    { code: 'FR', label: 'France' },
    { code: 'BE', label: 'Belgique' },
    { code: 'CH', label: 'Suisse' },
];

const currencies = [
    { code: 'XOF', label: 'Franc CFA BCEAO (XOF)' },
    { code: 'XAF', label: 'Franc CFA BEAC (XAF)' },
    { code: 'GNF', label: 'Franc guinéen (GNF)' },
    { code: 'MAD', label: 'Dirham marocain (MAD)' },
    { code: 'TND', label: 'Dinar tunisien (TND)' },
    { code: 'DZD', label: 'Dinar algérien (DZD)' },
    { code: 'EUR', label: 'Euro (EUR)' },
    { code: 'USD', label: 'Dollar US (USD)' },
    { code: 'GBP', label: 'Livre sterling (GBP)' },
];

// ─── Logo ───────────────────────────────────────────────────────────────────
const logoForm    = useForm({ logo: null });
const logoInput   = ref(null);
const logoPreview = ref(null);

const onLogoChange = (e) => {
    const file = e.target.files[0];
    logoForm.logo = file ?? null;
    logoPreview.value = file ? URL.createObjectURL(file) : null;
};
const submitLogo = () => {
    logoForm.post(route('companies.logo'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { logoForm.reset(); logoPreview.value = null; if (logoInput.value) logoInput.value.value = ''; },
    });
};

// ─── Signature ──────────────────────────────────────────────────────────────
const sigForm    = useForm({ signature: null });
const sigInput   = ref(null);
const sigPreview = ref(props.company.signature_path ? `/storage/${props.company.signature_path}` : null);

const onSigChange = (e) => {
    const file = e.target.files[0];
    sigForm.signature = file ?? null;
    sigPreview.value = file ? URL.createObjectURL(file) : sigPreview.value;
};
const submitSig = () => {
    sigForm.post(route('companies.signature'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { sigForm.reset(); if (sigInput.value) sigInput.value.value = ''; },
    });
};

// ─── Cachet ─────────────────────────────────────────────────────────────────
const stampForm    = useForm({ stamp: null });
const stampInput   = ref(null);
const stampPreview = ref(props.company.stamp_path ? `/storage/${props.company.stamp_path}` : null);

const onStampChange = (e) => {
    const file = e.target.files[0];
    stampForm.stamp = file ?? null;
    stampPreview.value = file ? URL.createObjectURL(file) : stampPreview.value;
};
const submitStamp = () => {
    stampForm.post(route('companies.stamp'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => { stampForm.reset(); if (stampInput.value) stampInput.value.value = ''; },
    });
};

// ─── Signature settings ──────────────────────────────────────────────────────
const sigSettings = useForm({
    show_signature:     props.company.show_signature     ?? false,
    show_stamp:         props.company.show_stamp         ?? false,
    signature_label:    props.company.signature_label    ?? '',
    sig_show_emitter:   props.company.sig_show_emitter   ?? true,
    sig_show_client:    props.company.sig_show_client    ?? true,
    sig_mode:           props.company.sig_mode           ?? 'manual',
    sig_custom_mention: props.company.sig_custom_mention ?? '',
    sig_emitter_label:  props.company.sig_emitter_label  ?? '',
    sig_client_label:   props.company.sig_client_label   ?? '',
});
const saveSigSettings = () => sigSettings.patch(route('companies.signature-settings'), { preserveScroll: true });

// ─── Style des documents ────────────────────────────────────────────────────
const docStyle = useForm({
    accent_color: props.company.document_style?.accent_color ?? '#1e3a8a',
    header_style: props.company.document_style?.header_style ?? 'modern',
    font_family:  props.company.document_style?.font_family  ?? 'dejavu_sans',
});
const saveDocStyle = () => docStyle.put(route('companies.document-style'), { preserveScroll: true });

// ─── Moyens de paiement ──────────────────────────────────────────────────────
const METHOD_TYPES = [
    { type: 'wave',          label: 'Wave',              icon: '🌊', fields: ['number'] },
    { type: 'orange_money',  label: 'Orange Money',      icon: '🟠', fields: ['number'] },
    { type: 'mtn_momo',      label: 'MTN MoMo',          icon: '🟡', fields: ['number'] },
    { type: 'moov_money',    label: 'Moov Money',        icon: '🔵', fields: ['number'] },
    { type: 'bank_transfer', label: 'Virement bancaire', icon: '🏦', fields: ['account_name', 'bank_name', 'iban', 'bic'] },
    { type: 'paypal',        label: 'PayPal',            icon: '💙', fields: ['email'] },
    { type: 'cash',          label: 'Espèces',           icon: '💵', fields: [] },
    { type: 'cheque',        label: 'Chèque',            icon: '📄', fields: ['bank_name'] },
];

const buildDefaultMethod = (type) => ({
    type, enabled: true, label: '', number: '',
    account_name: '', bank_name: '', iban: '', bic: '', email: '', note: '',
});

const pmForm = useForm({
    payment_methods: (props.company.payment_methods ?? []).map(m => ({ ...buildDefaultMethod(m.type), ...m })),
});

const addPaymentMethod    = (type) => { if (!pmForm.payment_methods.some(m => m.type === type)) pmForm.payment_methods.push(buildDefaultMethod(type)); };
const removePaymentMethod = (idx)  => pmForm.payment_methods.splice(idx, 1);
const savePaymentMethods  = ()     => pmForm.put(route('companies.payment-methods'), { preserveScroll: true });

const labelFor = (type) => METHOD_TYPES.find(m => m.type === type)?.label ?? type;
const iconFor  = (type) => METHOD_TYPES.find(m => m.type === type)?.icon  ?? '💳';
const fieldsFor = (type) => METHOD_TYPES.find(m => m.type === type)?.fields ?? [];
const availableToAdd = computed(() => METHOD_TYPES.filter(m => !pmForm.payment_methods.some(p => p.type === m.type)));
</script>

<template>
    <Head title="Paramètres de la société" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Paramètres de la société</h2>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ company.name }} — identité, coordonnées, facturation et logo.</p>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

                <!-- Tabs navigation -->
                <div class="mb-6 flex gap-1 overflow-x-auto rounded-xl bg-gray-100 dark:bg-gray-800 p-1">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        type="button"
                        @click="activeTab = tab.id"
                        class="flex shrink-0 items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium transition-all"
                        :class="activeTab === tab.id
                            ? 'bg-white dark:bg-gray-700 text-brand-700 dark:text-brand-400 shadow-sm'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                    >
                        <span>{{ tab.icon }}</span>
                        <span>{{ tab.label }}</span>
                    </button>
                </div>

                <!-- ══════════════════════════════════════════════════════════
                     ONGLET : Identité
                ════════════════════════════════════════════════════════════ -->
                <div v-show="activeTab === 'identity'" class="space-y-6">

                    <!-- Logo -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Logo de la société</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Affiché sur vos documents PDF et dans l'application. JPG, PNG, WEBP ou SVG — 2 Mo max.</p>

                        <div class="mt-5 flex flex-wrap items-center gap-6">
                            <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                                <img v-if="logoPreview" :src="logoPreview" alt="Aperçu" class="h-full w-full object-contain" />
                                <img v-else-if="company.logo_path" :src="`/storage/${company.logo_path}`" alt="Logo" class="h-full w-full object-contain" />
                                <span v-else class="text-3xl opacity-30">🏢</span>
                            </div>
                            <div class="min-w-0 flex-1 space-y-3">
                                <input
                                    ref="logoInput"
                                    type="file"
                                    accept=".jpg,.jpeg,.png,.webp,.svg"
                                    class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 dark:file:bg-brand-900/30 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-700 dark:file:text-brand-400 hover:file:bg-brand-100 dark:hover:file:bg-brand-900/50"
                                    @change="onLogoChange"
                                />
                                <InputError :message="logoForm.errors.logo" />
                                <button
                                    type="button"
                                    :disabled="!logoForm.logo || logoForm.processing"
                                    @click="submitLogo"
                                    class="inline-flex items-center gap-2 rounded-lg bg-brand-600 hover:bg-brand-700 disabled:opacity-50 px-4 py-2 text-sm font-semibold text-white transition-colors"
                                >
                                    <svg v-if="logoForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    Enregistrer le logo
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Identité -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Identité juridique</h3>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom commercial <span class="text-red-500">*</span></label>
                                <input v-model="form.name" type="text" placeholder="Mon Entreprise"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                                    :class="{ 'border-red-500': form.errors.name }" />
                                <InputError :message="form.errors.name" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Raison sociale</label>
                                <input v-model="form.legal_name" type="text" placeholder="SARL Mon Entreprise"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                <InputError :message="form.errors.legal_name" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">N° fiscal / contribuable</label>
                                <input v-model="form.tax_id" type="text" placeholder="Ex : CI0123456789"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                <InputError :message="form.errors.tax_id" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Registre du commerce (RCCM)</label>
                                <input v-model="form.trade_register" type="text" placeholder="Ex : CI-ABJ-2024-B-1234"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                <InputError :message="form.errors.trade_register" class="mt-1" />
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <span v-if="form.recentlySuccessful" class="text-sm font-medium text-green-600 dark:text-green-400">✓ Enregistré</span>
                            <button type="button" :disabled="form.processing" @click="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-brand-600 hover:bg-brand-700 disabled:opacity-50 px-5 py-2.5 text-sm font-semibold text-white transition-colors">
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════
                     ONGLET : Coordonnées
                ════════════════════════════════════════════════════════════ -->
                <div v-show="activeTab === 'contact'" class="space-y-6">
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Coordonnées</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ces informations apparaissent sur vos factures et documents PDF.</p>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                <input v-model="form.email" type="email" placeholder="contact@monentreprise.com"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                <InputError :message="form.errors.email" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Téléphone</label>
                                <input v-model="form.phone" type="tel" placeholder="+225 07 00 00 00"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                <InputError :message="form.errors.phone" class="mt-1" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adresse</label>
                                <input v-model="form.address" type="text" placeholder="Rue, quartier, BP..."
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                <InputError :message="form.errors.address" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ville</label>
                                <input v-model="form.city" type="text" placeholder="Abidjan"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                <InputError :message="form.errors.city" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pays <span class="text-red-500">*</span></label>
                                <select v-model="form.country"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    <option v-for="c in countries" :key="c.code" :value="c.code">{{ c.label }}</option>
                                    <option value="">Autre (saisir le code)</option>
                                </select>
                                <InputError :message="form.errors.country" class="mt-1" />
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <span v-if="form.recentlySuccessful" class="text-sm font-medium text-green-600 dark:text-green-400">✓ Enregistré</span>
                            <button type="button" :disabled="form.processing" @click="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-brand-600 hover:bg-brand-700 disabled:opacity-50 px-5 py-2.5 text-sm font-semibold text-white transition-colors">
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════
                     ONGLET : Facturation
                ════════════════════════════════════════════════════════════ -->
                <div v-show="activeTab === 'billing'" class="space-y-6">
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Paramètres de facturation</h3>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Devise <span class="text-red-500">*</span></label>
                                <select v-model="form.currency"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    <option v-for="c in currencies" :key="c.code" :value="c.code">{{ c.label }}</option>
                                </select>
                                <InputError :message="form.errors.currency" class="mt-1" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">TVA par défaut (%)</label>
                                <input v-model="form.default_tax_rate" type="number" min="0" max="100" step="0.01" placeholder="18"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                <InputError :message="form.errors.default_tax_rate" class="mt-1" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Modèle PDF par défaut</label>
                                <select v-model="form.default_template"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    <option value="">— Aucun (choix à chaque document) —</option>
                                    <optgroup v-for="(list, family) in templateFamilies" :key="family" :label="family">
                                        <option v-for="t in list" :key="t.key" :value="t.key">{{ t.name }}</option>
                                    </optgroup>
                                </select>
                                <InputError :message="form.errors.default_template" class="mt-1" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pied de page des factures</label>
                                <textarea v-model="form.invoice_footer" rows="3" maxlength="500"
                                    placeholder="Mentions légales, coordonnées bancaires, message de remerciement…"
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                                <InputError :message="form.errors.invoice_footer" class="mt-1" />
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <span v-if="form.recentlySuccessful" class="text-sm font-medium text-green-600 dark:text-green-400">✓ Enregistré</span>
                            <button type="button" :disabled="form.processing" @click="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-brand-600 hover:bg-brand-700 disabled:opacity-50 px-5 py-2.5 text-sm font-semibold text-white transition-colors">
                                <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════
                     ONGLET : Apparence
                ════════════════════════════════════════════════════════════ -->
                <div v-show="activeTab === 'style'" class="space-y-6">
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Style des documents PDF</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Personnalisez l'apparence de vos factures, devis et documents générés.</p>

                        <div class="mt-6 space-y-6">
                            <!-- Couleur principale -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Couleur principale</label>
                                <div class="flex items-center gap-4">
                                    <input type="color" v-model="docStyle.accent_color"
                                        class="h-10 w-16 cursor-pointer rounded-lg border border-gray-300 dark:border-gray-600 p-0.5" />
                                    <span class="font-mono text-sm text-gray-500 dark:text-gray-400">{{ docStyle.accent_color }}</span>
                                    <button type="button" @click="docStyle.accent_color = '#1e3a8a'"
                                        class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 underline">Réinitialiser</button>
                                </div>
                                <p class="mt-1.5 text-xs text-gray-400 dark:text-gray-500">Utilisée pour le titre du document, l'en-tête du tableau et le total TTC.</p>
                            </div>

                            <!-- Mise en page -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Mise en page de l'en-tête</label>
                                <div class="grid sm:grid-cols-3 gap-3">
                                    <label v-for="opt in [
                                        { v: 'modern',  l: 'Modern',  d: 'Bandeau coloré plein' },
                                        { v: 'classic', l: 'Classic', d: 'Bordure colorée uniquement' },
                                        { v: 'minimal', l: 'Minimal', d: 'Sans bandeau, sobre' },
                                    ]" :key="opt.v"
                                        class="flex items-start gap-3 cursor-pointer rounded-xl border p-3 transition"
                                        :class="docStyle.header_style === opt.v
                                            ? 'border-brand-500 bg-brand-50 dark:bg-brand-900/30'
                                            : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
                                    >
                                        <input type="radio" :value="opt.v" v-model="docStyle.header_style"
                                            class="mt-0.5 text-brand-600 focus:ring-brand-500" />
                                        <div>
                                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ opt.l }}</span>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ opt.d }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Police -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Police de caractères</label>
                                <select v-model="docStyle.font_family"
                                    class="w-full max-w-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    <option value="dejavu_sans">DejaVu Sans (défaut)</option>
                                    <option value="times_new_roman">Times New Roman (serif)</option>
                                    <option value="courier">Courier (monospace)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <span v-if="docStyle.recentlySuccessful" class="text-sm font-medium text-green-600 dark:text-green-400">✓ Style enregistré</span>
                            <button type="button" :disabled="docStyle.processing" @click="saveDocStyle"
                                class="inline-flex items-center gap-2 rounded-lg bg-brand-600 hover:bg-brand-700 disabled:opacity-50 px-5 py-2.5 text-sm font-semibold text-white transition-colors">
                                <svg v-if="docStyle.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Enregistrer le style
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════
                     ONGLET : Signature & Cachet
                ════════════════════════════════════════════════════════════ -->
                <div v-show="activeTab === 'signature'" class="space-y-6">

                    <!-- Paramètres de signature -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Zones de signature sur les PDF</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configurez comment les signatures apparaissent sur vos documents.</p>

                        <div class="mt-5 space-y-5">
                            <!-- Zones émetteur / client -->
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div class="rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 p-4">
                                    <label class="flex items-center gap-2 cursor-pointer mb-3">
                                        <input type="checkbox" v-model="sigSettings.sig_show_emitter"
                                            class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Zone signature émetteur</span>
                                    </label>
                                    <div v-if="sigSettings.sig_show_emitter">
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Libellé personnalisé</label>
                                        <input v-model="sigSettings.sig_emitter_label" type="text"
                                            placeholder="Signature et cachet du Directeur"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                    </div>
                                </div>
                                <div class="rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 p-4">
                                    <label class="flex items-center gap-2 cursor-pointer mb-3">
                                        <input type="checkbox" v-model="sigSettings.sig_show_client"
                                            class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Zone signature client</span>
                                    </label>
                                    <div v-if="sigSettings.sig_show_client">
                                        <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Libellé personnalisé</label>
                                        <input v-model="sigSettings.sig_client_label" type="text"
                                            placeholder="Bon pour accord — Signature client"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                    </div>
                                </div>
                            </div>

                            <!-- Mode de signature -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type de signature accepté</label>
                                <div class="flex flex-wrap gap-3">
                                    <label v-for="opt in [
                                        { v: 'manual',  l: 'Manuelle (zone vide)' },
                                        { v: 'digital', l: 'Numérique (image uploadée)' },
                                        { v: 'both',    l: 'Manuelle ou numérique' },
                                    ]" :key="opt.v" class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" :value="opt.v" v-model="sigSettings.sig_mode"
                                            class="text-brand-600 focus:ring-brand-500" />
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ opt.l }}</span>
                                    </label>
                                </div>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">En mode numérique, votre signature uploadée s'affiche automatiquement dans la zone émetteur.</p>
                            </div>

                            <!-- Imprimer sur PDF -->
                            <div class="flex flex-wrap items-center gap-4 pt-2 border-t border-gray-100 dark:border-gray-700">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="sigSettings.show_signature"
                                        class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Imprimer ma signature numérique sur les PDF</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="sigSettings.show_stamp"
                                        class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                    <span class="text-sm text-gray-700 dark:text-gray-300">Imprimer mon cachet sur les PDF</span>
                                </label>
                            </div>

                            <div v-if="sigSettings.show_signature">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Libellé signataire</label>
                                <input v-model="sigSettings.signature_label" type="text" placeholder="Ex : Le Directeur Général"
                                    class="w-full max-w-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                            </div>

                            <!-- Mention légale personnalisée -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Mention légale personnalisée
                                    <span class="text-xs font-normal text-gray-400">(laisser vide = mention par défaut)</span>
                                </label>
                                <textarea v-model="sigSettings.sig_custom_mention" rows="2"
                                    placeholder="Ex : Tout retard de paiement entraîne des pénalités au taux légal..."
                                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"></textarea>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <span v-if="sigSettings.recentlySuccessful" class="text-sm font-medium text-green-600 dark:text-green-400">✓ Enregistré</span>
                            <button type="button" :disabled="sigSettings.processing" @click="saveSigSettings"
                                class="inline-flex items-center gap-2 rounded-lg bg-brand-600 hover:bg-brand-700 disabled:opacity-50 px-5 py-2.5 text-sm font-semibold text-white transition-colors">
                                <svg v-if="sigSettings.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Enregistrer les réglages
                            </button>
                        </div>
                    </div>

                    <!-- Upload signature & cachet -->
                    <div class="grid sm:grid-cols-2 gap-6">
                        <!-- Signature -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">✍️ Signature numérique</h4>
                            <div class="mb-3 flex h-24 items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 border border-dashed border-gray-200 dark:border-gray-600 overflow-hidden">
                                <img v-if="sigPreview" :src="sigPreview" alt="Signature" class="max-h-full max-w-full object-contain" />
                                <span v-else class="text-xs text-gray-400 dark:text-gray-500">Aucune signature</span>
                            </div>
                            <input ref="sigInput" type="file" accept=".jpg,.jpeg,.png,.webp"
                                class="block w-full text-xs text-gray-500 dark:text-gray-400 file:mr-2 file:rounded-lg file:border-0 file:bg-brand-50 dark:file:bg-brand-900/30 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-brand-700 dark:file:text-brand-400"
                                @change="onSigChange" />
                            <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">PNG avec fond transparent recommandé. Max 2 Mo.</p>
                            <InputError :message="sigForm.errors.signature" class="mt-1" />
                            <button type="button" :disabled="!sigForm.signature || sigForm.processing" @click="submitSig"
                                class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 disabled:opacity-50 px-3 py-1.5 text-xs font-semibold text-white transition-colors">
                                <svg v-if="sigForm.processing" class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Uploader la signature
                            </button>
                        </div>

                        <!-- Cachet -->
                        <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm p-5">
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">🔏 Cachet / Tampon</h4>
                            <div class="mb-3 flex h-24 items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-700 border border-dashed border-gray-200 dark:border-gray-600 overflow-hidden">
                                <img v-if="stampPreview" :src="stampPreview" alt="Cachet" class="max-h-full max-w-full object-contain" />
                                <span v-else class="text-xs text-gray-400 dark:text-gray-500">Aucun cachet</span>
                            </div>
                            <input ref="stampInput" type="file" accept=".jpg,.jpeg,.png,.webp"
                                class="block w-full text-xs text-gray-500 dark:text-gray-400 file:mr-2 file:rounded-lg file:border-0 file:bg-brand-50 dark:file:bg-brand-900/30 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-brand-700 dark:file:text-brand-400"
                                @change="onStampChange" />
                            <p class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">PNG avec fond transparent recommandé. Max 2 Mo.</p>
                            <InputError :message="stampForm.errors.stamp" class="mt-1" />
                            <button type="button" :disabled="!stampForm.stamp || stampForm.processing" @click="submitStamp"
                                class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 disabled:opacity-50 px-3 py-1.5 text-xs font-semibold text-white transition-colors">
                                <svg v-if="stampForm.processing" class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Uploader le cachet
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════════
                     ONGLET : Paiements
                ════════════════════════════════════════════════════════════ -->
                <div v-show="activeTab === 'payments'" class="space-y-6">
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Moyens de paiement</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Configurez vos coordonnées de paiement. Elles s'afficheront sur vos PDFs et votre page publique.
                        </p>

                        <!-- Méthodes configurées -->
                        <div v-if="pmForm.payment_methods.length" class="mt-5 space-y-4">
                            <div v-for="(method, idx) in pmForm.payment_methods" :key="method.type"
                                class="rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <label class="flex items-center gap-2.5 cursor-pointer">
                                        <input type="checkbox" v-model="method.enabled"
                                            class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                        <span class="text-base">{{ iconFor(method.type) }}</span>
                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ labelFor(method.type) }}</span>
                                        <span v-if="!method.enabled" class="text-xs text-gray-400 italic">(désactivé)</span>
                                    </label>
                                    <button type="button" @click="removePaymentMethod(idx)"
                                        class="text-xs text-red-500 hover:text-red-700 dark:hover:text-red-400 font-medium transition-colors">
                                        Supprimer
                                    </button>
                                </div>

                                <div v-if="method.enabled" class="grid gap-3 sm:grid-cols-2">
                                    <template v-if="fieldsFor(method.type).includes('number')">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Numéro</label>
                                            <input v-model="method.number" type="text" placeholder="07 XX XX XX"
                                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Libellé personnalisé</label>
                                            <input v-model="method.label" type="text" :placeholder="`${labelFor(method.type)} - Nom du compte`"
                                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                        </div>
                                    </template>

                                    <template v-if="fieldsFor(method.type).includes('bank_name')">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nom de la banque</label>
                                            <input v-model="method.bank_name" type="text" placeholder="Ecobank CI"
                                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nom du titulaire</label>
                                            <input v-model="method.account_name" type="text" placeholder="SARL Mon Entreprise"
                                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">IBAN / N° de compte</label>
                                            <input v-model="method.iban" type="text" placeholder="CI XX XXXX XXXX XXXX"
                                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">BIC / Swift</label>
                                            <input v-model="method.bic" type="text" placeholder="ECOCCIAB"
                                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                        </div>
                                    </template>

                                    <template v-if="fieldsFor(method.type).includes('email')">
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Adresse PayPal</label>
                                            <input v-model="method.email" type="email" placeholder="paiement@monentreprise.com"
                                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                        </div>
                                    </template>

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Note (facultatif)</label>
                                        <input v-model="method.note" type="text" placeholder="Ex : Disponible 8h-20h"
                                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="!pmForm.payment_methods.length"
                            class="mt-5 rounded-xl bg-gray-50 dark:bg-gray-700/50 border border-dashed border-gray-200 dark:border-gray-600 p-6 text-center text-sm text-gray-400 dark:text-gray-500">
                            Aucun moyen de paiement configuré. Ajoutez-en un ci-dessous.
                        </div>

                        <!-- Ajouter -->
                        <div v-if="availableToAdd.length" class="mt-4">
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Ajouter un moyen de paiement :</p>
                            <div class="flex flex-wrap gap-2">
                                <button v-for="m in availableToAdd" :key="m.type" type="button" @click="addPaymentMethod(m.type)"
                                    class="flex items-center gap-1.5 rounded-full border border-brand-300 dark:border-brand-700 bg-brand-50 dark:bg-brand-900/30 px-3 py-1.5 text-xs font-medium text-brand-700 dark:text-brand-400 hover:bg-brand-100 dark:hover:bg-brand-900/50 transition-colors">
                                    <span>{{ m.icon }}</span> + {{ m.label }}
                                </button>
                            </div>
                        </div>

                        <div class="mt-5 flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <span v-if="pmForm.recentlySuccessful" class="text-sm font-medium text-green-600 dark:text-green-400">✓ Enregistré</span>
                            <button type="button" :disabled="pmForm.processing" @click="savePaymentMethods"
                                class="inline-flex items-center gap-2 rounded-lg bg-brand-600 hover:bg-brand-700 disabled:opacity-50 px-5 py-2.5 text-sm font-semibold text-white transition-colors">
                                <svg v-if="pmForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Enregistrer les paiements
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

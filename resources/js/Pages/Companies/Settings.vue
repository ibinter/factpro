<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    company: Object,
    templates: Array,
});

const form = useForm({
    name: props.company.name ?? '',
    legal_name: props.company.legal_name ?? '',
    email: props.company.email ?? '',
    phone: props.company.phone ?? '',
    address: props.company.address ?? '',
    city: props.company.city ?? '',
    country: props.company.country ?? 'CI',
    currency: props.company.currency ?? 'XOF',
    tax_id: props.company.tax_id ?? '',
    trade_register: props.company.trade_register ?? '',
    invoice_footer: props.company.invoice_footer ?? '',
    default_tax_rate: props.company.default_tax_rate ?? 0,
    default_template: props.company.default_template ?? '',
});

const submit = () => {
    form.patch(route('companies.settings.update'), { preserveScroll: true });
};

// Templates PDF groupés par famille pour le <select>
const templateFamilies = computed(() => {
    const groups = {};
    for (const t of props.templates) {
        (groups[t.family] ??= []).push(t);
    }
    return groups;
});

// Upload du logo
const logoForm = useForm({ logo: null });
const logoInput = ref(null);
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
        onSuccess: () => {
            logoForm.reset();
            logoPreview.value = null;
            if (logoInput.value) logoInput.value.value = '';
        },
    });
};

// Upload signature
const sigForm = useForm({ signature: null });
const sigInput = ref(null);
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

// Upload cachet
const stampForm = useForm({ stamp: null });
const stampInput = ref(null);
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

// ─── Moyens de paiement configurables ───────────────────────────────────────
const METHOD_TYPES = [
    { type: 'wave',          label: 'Wave',           fields: ['number'] },
    { type: 'orange_money',  label: 'Orange Money',   fields: ['number'] },
    { type: 'mtn_momo',      label: 'MTN MoMo',       fields: ['number'] },
    { type: 'bank_transfer', label: 'Virement bancaire', fields: ['account_name', 'bank_name', 'iban', 'bic'] },
    { type: 'paypal',        label: 'PayPal',         fields: ['email'] },
];

const buildDefaultMethod = (type) => ({
    type,
    enabled: true,
    label: '',
    number: '',
    account_name: '',
    bank_name: '',
    iban: '',
    bic: '',
    email: '',
    note: '',
});

const pmForm = useForm({
    payment_methods: (props.company.payment_methods ?? []).map(m => ({ ...buildDefaultMethod(m.type), ...m })),
});

const addPaymentMethod = (type) => {
    if (pmForm.payment_methods.some(m => m.type === type)) return;
    pmForm.payment_methods.push(buildDefaultMethod(type));
};

const removePaymentMethod = (index) => {
    pmForm.payment_methods.splice(index, 1);
};

const savePaymentMethods = () => {
    pmForm.put(route('companies.payment-methods'), { preserveScroll: true });
};

const labelFor = (type) => METHOD_TYPES.find(m => m.type === type)?.label ?? type;
const fieldsFor = (type) => METHOD_TYPES.find(m => m.type === type)?.fields ?? [];
const availableToAdd = computed(() =>
    METHOD_TYPES.filter(m => !pmForm.payment_methods.some(p => p.type === m.type))
);

// Style des documents
const docStyle = useForm({
    accent_color:  props.company.document_style?.accent_color  ?? '#1e3a8a',
    header_style:  props.company.document_style?.header_style  ?? 'modern',
    font_family:   props.company.document_style?.font_family   ?? 'dejavu_sans',
});
const saveDocStyle = () => {
    docStyle.put(route('companies.document-style'), { preserveScroll: true });
};

// Paramètres signature/cachet
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
const saveSigSettings = () => {
    sigSettings.patch(route('companies.signature-settings'), { preserveScroll: true });
};
</script>

<template>
    <Head title="Paramètres de la société" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Paramètres de la société</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ company.name }} — identité, coordonnées, facturation et logo.</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Logo -->
                <section class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-800">Logo</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Affiché sur vos documents PDF et dans l'application. JPG, PNG, WEBP ou SVG — 2 Mo max.
                    </p>

                    <div class="mt-4 flex flex-wrap items-center gap-6">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                            <img
                                v-if="logoPreview"
                                :src="logoPreview"
                                alt="Aperçu du nouveau logo"
                                class="h-full w-full object-contain"
                            />
                            <img
                                v-else-if="company.logo_path"
                                :src="`/storage/${company.logo_path}`"
                                alt="Logo actuel"
                                class="h-full w-full object-contain"
                            />
                            <span v-else class="text-2xl text-gray-300">🏢</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <input
                                ref="logoInput"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,.svg"
                                class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-brand-700 hover:file:bg-brand-100"
                                @change="onLogoChange"
                            />
                            <InputError :message="logoForm.errors.logo" class="mt-1" />
                            <PrimaryButton class="mt-3" :disabled="!logoForm.logo || logoForm.processing" @click="submitLogo">
                                Uploader le logo
                            </PrimaryButton>
                        </div>
                    </div>
                </section>

                <!-- Identité -->
                <section class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-800">Identité</h3>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Nom commercial *" />
                            <TextInput v-model="form.name" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Raison sociale" />
                            <TextInput v-model="form.legal_name" class="mt-1 block w-full" />
                            <InputError :message="form.errors.legal_name" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="N° fiscal / contribuable" />
                            <TextInput v-model="form.tax_id" class="mt-1 block w-full" />
                            <InputError :message="form.errors.tax_id" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Registre du commerce (RCCM)" />
                            <TextInput v-model="form.trade_register" class="mt-1 block w-full" />
                            <InputError :message="form.errors.trade_register" class="mt-1" />
                        </div>
                    </div>
                </section>

                <!-- Coordonnées -->
                <section class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-800">Coordonnées</h3>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Email" />
                            <TextInput v-model="form.email" type="email" class="mt-1 block w-full" />
                            <InputError :message="form.errors.email" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Téléphone" />
                            <TextInput v-model="form.phone" class="mt-1 block w-full" />
                            <InputError :message="form.errors.phone" class="mt-1" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel value="Adresse" />
                            <TextInput v-model="form.address" class="mt-1 block w-full" />
                            <InputError :message="form.errors.address" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Ville" />
                            <TextInput v-model="form.city" class="mt-1 block w-full" />
                            <InputError :message="form.errors.city" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Pays (code 2 lettres) *" />
                            <TextInput v-model="form.country" maxlength="2" class="mt-1 block w-full uppercase" required />
                            <InputError :message="form.errors.country" class="mt-1" />
                        </div>
                    </div>
                </section>

                <!-- Facturation -->
                <section class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-800">Facturation</h3>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Devise (code 3 lettres) *" />
                            <TextInput v-model="form.currency" maxlength="3" class="mt-1 block w-full uppercase" required />
                            <InputError :message="form.errors.currency" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="TVA par défaut (%)" />
                            <TextInput v-model="form.default_tax_rate" type="number" min="0" max="100" step="0.01" class="mt-1 block w-full" />
                            <InputError :message="form.errors.default_tax_rate" class="mt-1" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel value="Modèle PDF par défaut" />
                            <select
                                v-model="form.default_template"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            >
                                <option value="">— Aucun (choix à chaque document) —</option>
                                <optgroup v-for="(list, family) in templateFamilies" :key="family" :label="family">
                                    <option v-for="t in list" :key="t.key" :value="t.key">{{ t.name }}</option>
                                </optgroup>
                            </select>
                            <InputError :message="form.errors.default_template" class="mt-1" />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel value="Pied de page des factures" />
                            <textarea
                                v-model="form.invoice_footer"
                                rows="3"
                                maxlength="500"
                                placeholder="Mentions légales, coordonnées bancaires, message de remerciement…"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                            ></textarea>
                            <InputError :message="form.errors.invoice_footer" class="mt-1" />
                        </div>
                    </div>
                </section>

                <div class="flex items-center justify-end gap-3">
                    <span v-if="form.recentlySuccessful" class="text-sm text-green-600">Enregistré.</span>
                    <PrimaryButton :disabled="form.processing" @click="submit">Enregistrer les paramètres</PrimaryButton>
                </div>

                <!-- Style des documents -->
                <section class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-800">Style des documents PDF</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Personnalisez l'apparence visuelle de vos factures, devis et autres documents générés.
                    </p>

                    <div class="mt-5 space-y-5">
                        <!-- Couleur accentuation -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Couleur principale</label>
                            <div class="flex items-center gap-4">
                                <input
                                    type="color"
                                    v-model="docStyle.accent_color"
                                    class="h-10 w-16 cursor-pointer rounded border border-gray-300 p-0.5"
                                />
                                <span class="text-sm font-mono text-gray-500">{{ docStyle.accent_color }}</span>
                                <button type="button" class="text-xs text-gray-400 hover:text-gray-600 underline" @click="docStyle.accent_color = '#1e3a8a'">Réinitialiser</button>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Utilisée pour le titre du document, l'en-tête du tableau et le total TTC.</p>
                            <InputError :message="docStyle.errors.accent_color" class="mt-1" />
                        </div>

                        <!-- Style de bandeau -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mise en page de l'en-tête</label>
                            <div class="grid sm:grid-cols-3 gap-3">
                                <label v-for="opt in [
                                    { v: 'modern',  l: 'Modern',  d: 'Bandeau coloré plein (défaut)' },
                                    { v: 'classic', l: 'Classic', d: 'Bordure colorée uniquement' },
                                    { v: 'minimal', l: 'Minimal', d: 'Sans bandeau, sobre' },
                                ]" :key="opt.v"
                                    class="flex items-start gap-3 cursor-pointer rounded-lg border p-3 transition"
                                    :class="docStyle.header_style === opt.v ? 'border-brand-500 bg-brand-50' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <input type="radio" :value="opt.v" v-model="docStyle.header_style" class="mt-0.5 text-brand-600 focus:ring-brand-500" />
                                    <div>
                                        <span class="text-sm font-semibold text-gray-800">{{ opt.l }}</span>
                                        <p class="text-xs text-gray-500">{{ opt.d }}</p>
                                    </div>
                                </label>
                            </div>
                            <InputError :message="docStyle.errors.header_style" class="mt-1" />
                        </div>

                        <!-- Police -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Police de caractères</label>
                            <select v-model="docStyle.font_family"
                                class="mt-1 block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm">
                                <option value="dejavu_sans">DejaVu Sans (défaut)</option>
                                <option value="times_new_roman">Times New Roman (serif)</option>
                                <option value="courier">Courier (monospace)</option>
                            </select>
                            <InputError :message="docStyle.errors.font_family" class="mt-1" />
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-end gap-3">
                        <span v-if="docStyle.recentlySuccessful" class="text-sm text-green-600">Style enregistré.</span>
                        <PrimaryButton :disabled="docStyle.processing" @click="saveDocStyle">Enregistrer le style</PrimaryButton>
                    </div>
                </section>

                <!-- Signature & Cachet -->
                <section class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-800">Signature & Cachet</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Ajoutez votre signature numérique et votre tampon d'entreprise. Ils apparaîtront automatiquement sur les PDF générés si l'option est activée.
                    </p>

                    <!-- Zones de signature sur les PDF -->
                    <div class="mt-5 rounded-lg bg-gray-50 p-4 space-y-4">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Zones de signature sur les PDF</p>

                        <!-- Emetteur / Client -->
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="rounded-md border border-gray-200 bg-white p-3">
                                <label class="flex items-center gap-2 cursor-pointer mb-2">
                                    <input type="checkbox" v-model="sigSettings.sig_show_emitter" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                    <span class="text-sm text-gray-700 font-medium">Zone signature émetteur</span>
                                </label>
                                <div v-if="sigSettings.sig_show_emitter">
                                    <label class="block text-xs text-gray-500 mb-1">Libellé (facultatif)</label>
                                    <input v-model="sigSettings.sig_emitter_label" type="text"
                                        placeholder="Ex : Signature et cachet du Directeur"
                                        class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                </div>
                            </div>
                            <div class="rounded-md border border-gray-200 bg-white p-3">
                                <label class="flex items-center gap-2 cursor-pointer mb-2">
                                    <input type="checkbox" v-model="sigSettings.sig_show_client" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                    <span class="text-sm text-gray-700 font-medium">Zone signature client</span>
                                </label>
                                <div v-if="sigSettings.sig_show_client">
                                    <label class="block text-xs text-gray-500 mb-1">Libellé (facultatif)</label>
                                    <input v-model="sigSettings.sig_client_label" type="text"
                                        placeholder="Ex : Bon pour accord — Signature client"
                                        class="block w-full rounded border-gray-300 text-xs shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                </div>
                            </div>
                        </div>

                        <!-- Mode de signature -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-2">Type de signature accepté</label>
                            <div class="flex flex-wrap gap-3">
                                <label class="flex items-center gap-1.5 cursor-pointer text-sm text-gray-700">
                                    <input type="radio" v-model="sigSettings.sig_mode" value="manual" class="text-brand-600 focus:ring-brand-500" />
                                    Manuelle uniquement
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer text-sm text-gray-700">
                                    <input type="radio" v-model="sigSettings.sig_mode" value="digital" class="text-brand-600 focus:ring-brand-500" />
                                    Numérique uniquement
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer text-sm text-gray-700">
                                    <input type="radio" v-model="sigSettings.sig_mode" value="both" class="text-brand-600 focus:ring-brand-500" />
                                    Manuelle ou numérique
                                </label>
                            </div>
                        </div>

                        <!-- Signature numérique sur PDF (ancienne option) -->
                        <div class="flex items-center gap-3 pt-1 border-t border-gray-200">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="sigSettings.show_signature" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                <span class="text-sm text-gray-700">Imprimer ma signature numérique sur les PDF</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer ml-4">
                                <input type="checkbox" v-model="sigSettings.show_stamp" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                <span class="text-sm text-gray-700">Imprimer mon cachet sur les PDF</span>
                            </label>
                        </div>
                        <div v-if="sigSettings.show_signature">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Libellé signataire (signature numérique)</label>
                            <input v-model="sigSettings.signature_label" type="text"
                                placeholder="Ex : Le Directeur Général"
                                class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 max-w-xs" />
                        </div>
                    </div>

                    <!-- Zone signatures sur PDF -->
                    <div class="mt-5 rounded-lg border border-brand-100 bg-brand-50/60 p-4 space-y-4">
                        <h4 class="text-sm font-bold text-brand-800">⚙️ Paramètres zone signature (PDF)</h4>

                        <!-- Zones à afficher -->
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-2">Zones à afficher sur le document</p>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="sigSettings.sig_show_emitter" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                    <span class="text-sm text-gray-700">Zone signature émetteur</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="sigSettings.sig_show_client" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                    <span class="text-sm text-gray-700">Zone signature client</span>
                                </label>
                            </div>
                        </div>

                        <!-- Mode signature -->
                        <div>
                            <p class="text-xs font-semibold text-gray-600 mb-2">Mode de signature</p>
                            <div class="flex flex-wrap gap-4">
                                <label v-for="opt in [{v:'manual',l:'Manuelle (zone vide)'},{v:'digital',l:'Numérique (image uploadée)'},{v:'both',l:'Les deux'}]"
                                    :key="opt.v" class="flex items-center gap-1.5 cursor-pointer">
                                    <input type="radio" :value="opt.v" v-model="sigSettings.sig_mode" class="text-brand-600 focus:ring-brand-500" />
                                    <span class="text-sm text-gray-700">{{ opt.l }}</span>
                                </label>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1">En mode numérique, votre signature uploadée s'affiche automatiquement dans la zone émetteur.</p>
                        </div>

                        <!-- Libellés personnalisés -->
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Libellé de la zone émetteur</label>
                                <input v-model="sigSettings.sig_emitter_label" type="text"
                                    placeholder="Signature et cachet de l'émetteur"
                                    class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Libellé de la zone client</label>
                                <input v-model="sigSettings.sig_client_label" type="text"
                                    placeholder="Bon pour accord — Signature du client"
                                    class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                            </div>
                        </div>

                        <!-- Mention légale personnalisée -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Mention légale personnalisée <span class="text-gray-400 font-normal">(laisser vide = mention par défaut)</span></label>
                            <textarea v-model="sigSettings.sig_custom_mention" rows="2"
                                placeholder="Ex : Tout retard de paiement entraîne des pénalités au taux légal en vigueur..."
                                class="block w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <PrimaryButton @click="saveSigSettings" :disabled="sigSettings.processing" class="text-xs py-1.5 px-3">
                                <span v-if="sigSettings.recentlySuccessful" class="text-green-300 mr-1">✓</span>
                                Sauvegarder les réglages de signature
                            </PrimaryButton>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-6 sm:grid-cols-2">
                        <!-- Signature -->
                        <div class="rounded-lg border border-dashed border-gray-300 p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Signature numérique</h4>
                            <div class="mb-3 flex h-20 items-center justify-center rounded-md bg-gray-50 border border-gray-200 overflow-hidden">
                                <img v-if="sigPreview" :src="sigPreview" alt="Signature" class="max-h-full max-w-full object-contain" />
                                <span v-else class="text-xs text-gray-400">Aucune signature</span>
                            </div>
                            <input ref="sigInput" type="file" accept=".jpg,.jpeg,.png,.webp"
                                class="block w-full text-xs text-gray-500 file:mr-2 file:rounded file:border-0 file:bg-brand-50 file:px-2 file:py-1 file:text-xs file:font-medium file:text-brand-700"
                                @change="onSigChange" />
                            <p class="mt-1 text-[10px] text-gray-400">PNG avec fond transparent recommandé. Max 2 Mo.</p>
                            <InputError :message="sigForm.errors.signature" class="mt-1" />
                            <PrimaryButton class="mt-2 text-xs" :disabled="!sigForm.signature || sigForm.processing" @click="submitSig">
                                Uploader la signature
                            </PrimaryButton>
                        </div>

                        <!-- Cachet -->
                        <div class="rounded-lg border border-dashed border-gray-300 p-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Cachet / Tampon</h4>
                            <div class="mb-3 flex h-20 items-center justify-center rounded-md bg-gray-50 border border-gray-200 overflow-hidden">
                                <img v-if="stampPreview" :src="stampPreview" alt="Cachet" class="max-h-full max-w-full object-contain" />
                                <span v-else class="text-xs text-gray-400">Aucun cachet</span>
                            </div>
                            <input ref="stampInput" type="file" accept=".jpg,.jpeg,.png,.webp"
                                class="block w-full text-xs text-gray-500 file:mr-2 file:rounded file:border-0 file:bg-brand-50 file:px-2 file:py-1 file:text-xs file:font-medium file:text-brand-700"
                                @change="onStampChange" />
                            <p class="mt-1 text-[10px] text-gray-400">PNG avec fond transparent recommandé. Max 2 Mo.</p>
                            <InputError :message="stampForm.errors.stamp" class="mt-1" />
                            <PrimaryButton class="mt-2 text-xs" :disabled="!stampForm.stamp || stampForm.processing" @click="submitStamp">
                                Uploader le cachet
                            </PrimaryButton>
                        </div>
                    </div>
                </section>

                <!-- Moyens de paiement -->
                <section class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-800">Moyens de paiement</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Configurez vos coordonnées de paiement. Elles s'afficheront sur vos PDFs et votre page publique.
                    </p>

                    <!-- Méthodes configurées -->
                    <div v-if="pmForm.payment_methods.length" class="mt-4 space-y-4">
                        <div
                            v-for="(method, idx) in pmForm.payment_methods"
                            :key="method.type"
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" v-model="method.enabled"
                                            class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                        <span class="text-sm font-semibold text-gray-800">{{ labelFor(method.type) }}</span>
                                    </label>
                                    <span v-if="!method.enabled" class="text-xs text-gray-400 italic">(désactivé — non affiché sur les PDFs)</span>
                                </div>
                                <button type="button" @click="removePaymentMethod(idx)"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium">
                                    Supprimer
                                </button>
                            </div>

                            <div v-if="method.enabled" class="grid gap-3 sm:grid-cols-2">
                                <!-- Wave / Orange Money / MTN MoMo -->
                                <template v-if="fieldsFor(method.type).includes('number')">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Numéro</label>
                                        <input v-model="method.number" type="text"
                                            placeholder="Ex : 07 XX XX XX"
                                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Libellé personnalisé</label>
                                        <input v-model="method.label" type="text"
                                            :placeholder="`Ex : ${labelFor(method.type)} - Nom du compte`"
                                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    </div>
                                </template>

                                <!-- Virement bancaire -->
                                <template v-if="fieldsFor(method.type).includes('bank_name')">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Nom de la banque</label>
                                        <input v-model="method.bank_name" type="text"
                                            placeholder="Ex : Ecobank CI"
                                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Nom du titulaire</label>
                                        <input v-model="method.account_name" type="text"
                                            placeholder="Ex : SARL Mon Entreprise"
                                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">IBAN / N° de compte</label>
                                        <input v-model="method.iban" type="text"
                                            placeholder="CI XX XXXX XXXX XXXX XXXX"
                                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">BIC / Swift</label>
                                        <input v-model="method.bic" type="text"
                                            placeholder="Ex : ECOCCIAB"
                                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    </div>
                                </template>

                                <!-- PayPal -->
                                <template v-if="fieldsFor(method.type).includes('email')">
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Adresse PayPal</label>
                                        <input v-model="method.email" type="email"
                                            placeholder="Ex : paiement@monentreprise.com"
                                            class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                    </div>
                                </template>

                                <!-- Note commune -->
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Note (facultatif)</label>
                                    <input v-model="method.note" type="text"
                                        placeholder="Ex : Disponible 8h-20h"
                                        class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ajouter un moyen de paiement -->
                    <div v-if="availableToAdd.length" class="mt-4">
                        <p class="text-xs font-medium text-gray-600 mb-2">Ajouter un moyen de paiement :</p>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="m in availableToAdd"
                                :key="m.type"
                                type="button"
                                @click="addPaymentMethod(m.type)"
                                class="rounded-full border border-brand-300 bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 hover:bg-brand-100 transition"
                            >
                                + {{ m.label }}
                            </button>
                        </div>
                    </div>

                    <div v-if="!pmForm.payment_methods.length" class="mt-4 rounded-md bg-gray-50 p-4 text-sm text-gray-400 text-center">
                        Aucun moyen de paiement configuré. Ajoutez-en un ci-dessus.
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-3">
                        <span v-if="pmForm.recentlySuccessful" class="text-sm text-green-600">Enregistré.</span>
                        <PrimaryButton :disabled="pmForm.processing" @click="savePaymentMethods">
                            Enregistrer les moyens de paiement
                        </PrimaryButton>
                    </div>
                </section>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

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

// Paramètres signature/cachet
const sigSettings = useForm({
    show_signature:  props.company.show_signature  ?? false,
    show_stamp:      props.company.show_stamp      ?? false,
    signature_label: props.company.signature_label ?? '',
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

                <!-- Signature & Cachet -->
                <section class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-800">Signature & Cachet</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Ajoutez votre signature numérique et votre tampon d'entreprise. Ils apparaîtront automatiquement sur les PDF générés si l'option est activée.
                    </p>

                    <!-- Activation + libellé -->
                    <div class="mt-5 rounded-lg bg-gray-50 p-4 space-y-3">
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="sigSettings.show_signature" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                <span class="text-sm text-gray-700 font-medium">Afficher la signature sur les PDF</span>
                            </label>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="sigSettings.show_stamp" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                <span class="text-sm text-gray-700 font-medium">Afficher le cachet sur les PDF</span>
                            </label>
                        </div>
                        <div v-if="sigSettings.show_signature">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Libellé signataire</label>
                            <input v-model="sigSettings.signature_label" type="text"
                                placeholder="Ex : Le Directeur Général, Signé par..."
                                class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500 max-w-xs" />
                        </div>
                        <div class="flex justify-end">
                            <PrimaryButton @click="saveSigSettings" :disabled="sigSettings.processing" class="text-xs py-1.5 px-3">
                                <span v-if="sigSettings.recentlySuccessful" class="text-green-300 mr-1">✓</span>
                                Sauvegarder les réglages
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

            </div>
        </div>
    </AuthenticatedLayout>
</template>

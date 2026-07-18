<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    hasAccess: Boolean,
    scans: { type: Array, default: () => [] },
});

/* -------------------- Upload -------------------- */
const isDragging = ref(false);
const uploading = ref(false);
const uploadError = ref('');
const currentScan = ref(null);

const onDrop = (e) => {
    isDragging.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file) handleFile(file);
};

const onFileInput = (e) => {
    const file = e.target.files?.[0];
    if (file) handleFile(file);
};

const ALLOWED_TYPES = ['application/pdf', 'image/jpeg', 'image/png'];
const MAX_SIZE = 10 * 1024 * 1024; // 10 MB

const handleFile = async (file) => {
    uploadError.value = '';
    if (!ALLOWED_TYPES.includes(file.type)) {
        uploadError.value = 'Format non accepté. Utilisez PDF, JPG ou PNG.';
        return;
    }
    if (file.size > MAX_SIZE) {
        uploadError.value = 'Fichier trop volumineux (max 10 Mo).';
        return;
    }

    const formData = new FormData();
    formData.append('file', file);
    uploading.value = true;

    try {
        const { data } = await axios.post(route('purchases.ocr.upload'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        currentScan.value = { id: data.id, status: data.status, original_filename: file.name, extracted_data: null };
        // Lancer le traitement synchrone
        await processScan(data.id);
    } catch (e) {
        uploadError.value = e.response?.data?.message ?? 'Erreur lors de l\'upload.';
    } finally {
        uploading.value = false;
    }
};

const processScan = async (scanId) => {
    try {
        const { data } = await axios.post(route('purchases.ocr.process', scanId));
        currentScan.value = data;
    } catch (e) {
        uploadError.value = e.response?.data?.message ?? 'Erreur lors du traitement OCR.';
    }
};

/* -------------------- Conversion en achat -------------------- */
const convertForm = useForm({
    supplier_id: '',
    number: '',
    invoice_date: '',
    amount_ht: '',
    vat_amount: '',
    amount_ttc: '',
    category: 'marchandises',
});

const showConvert = computed(() => currentScan.value?.status === 'done');

const fillFromExtracted = () => {
    const d = currentScan.value?.extracted_data;
    if (!d) return;
    convertForm.number = d.invoice_number ?? '';
    convertForm.invoice_date = d.invoice_date ?? '';
    convertForm.amount_ttc = d.total_amount ?? '';
    convertForm.vat_amount = d.tax_amount ?? '';
    if (d.total_amount && d.tax_amount) {
        convertForm.amount_ht = (parseFloat(d.total_amount) - parseFloat(d.tax_amount)).toFixed(2);
    }
};

const submitConvert = () => {
    if (!currentScan.value) return;
    convertForm.post(route('purchases.ocr.convert', currentScan.value.id), {
        preserveScroll: true,
    });
};

const fmtDate = (d) =>
    d ? new Date(d + 'T00:00:00').toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';

const STATUS_META = {
    pending:    { label: 'En attente', class: 'bg-gray-100 text-gray-600' },
    processing: { label: 'Traitement…', class: 'bg-blue-100 text-blue-700' },
    done:       { label: 'Terminé', class: 'bg-green-100 text-green-700' },
    failed:     { label: 'Échec', class: 'bg-red-100 text-red-700' },
};
</script>

<template>
    <Head title="Scanner une facture OCR" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">📷 Scanner une facture fournisseur</h2>
                <Link :href="route('purchases.index')">
                    <SecondaryButton>← Retour aux achats</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Upsell -->
                <div v-if="!hasAccess" class="mx-auto max-w-2xl rounded-lg bg-white p-8 text-center shadow">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-3xl">📷</div>
                    <h3 class="text-lg font-semibold text-gray-800">OCR disponible à partir du forfait BUSINESS</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Scannez vos factures fournisseurs (PDF ou photo) et laissez l'IA extraire automatiquement
                        les données : fournisseur, numéro, date, montant TTC, TVA.
                    </p>
                    <Link :href="route('billing.plans')" class="mt-6 inline-block">
                        <PrimaryButton>Voir les forfaits</PrimaryButton>
                    </Link>
                </div>

                <template v-else>
                    <!-- Zone drag & drop -->
                    <div
                        class="relative flex min-h-48 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed p-8 text-center transition-colors"
                        :class="isDragging ? 'border-brand-500 bg-brand-50' : 'border-gray-300 bg-white hover:border-brand-400'"
                        @dragover.prevent="isDragging = true"
                        @dragleave="isDragging = false"
                        @drop.prevent="onDrop"
                        @click="$refs.fileInput.click()"
                    >
                        <input ref="fileInput" type="file" accept=".pdf,.jpg,.jpeg,.png" class="hidden" @change="onFileInput" />
                        <div class="text-4xl">{{ uploading ? '⏳' : '📄' }}</div>
                        <p class="mt-3 text-sm font-semibold text-gray-700">
                            {{ uploading ? 'Traitement en cours…' : 'Glissez votre facture ici ou cliquez pour choisir' }}
                        </p>
                        <p class="mt-1 text-xs text-gray-400">PDF, JPG, PNG — 10 Mo max</p>
                    </div>

                    <p v-if="uploadError" class="text-sm text-red-600">{{ uploadError }}</p>

                    <!-- Résultats OCR -->
                    <div v-if="currentScan" class="rounded-lg bg-white p-6 shadow">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800">{{ currentScan.original_filename }}</h3>
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="STATUS_META[currentScan.status]?.class">
                                {{ STATUS_META[currentScan.status]?.label ?? currentScan.status }}
                            </span>
                        </div>

                        <div v-if="currentScan.status === 'done' && currentScan.extracted_data" class="space-y-2 text-sm">
                            <div class="grid gap-2 sm:grid-cols-2">
                                <div><span class="font-medium text-gray-500">Fournisseur :</span> {{ currentScan.extracted_data.supplier_name ?? '—' }}</div>
                                <div><span class="font-medium text-gray-500">N° Facture :</span> {{ currentScan.extracted_data.invoice_number ?? '—' }}</div>
                                <div><span class="font-medium text-gray-500">Date :</span> {{ fmtDate(currentScan.extracted_data.invoice_date) }}</div>
                                <div><span class="font-medium text-gray-500">Total TTC :</span> {{ currentScan.extracted_data.total_amount ?? '—' }}</div>
                                <div><span class="font-medium text-gray-500">TVA :</span> {{ currentScan.extracted_data.tax_amount ?? '—' }}</div>
                            </div>

                            <!-- Formulaire de correction & conversion -->
                            <div v-if="showConvert" class="mt-6 border-t pt-6">
                                <div class="mb-4 flex items-center justify-between">
                                    <h4 class="font-semibold text-gray-700">Créer la facture d'achat</h4>
                                    <SecondaryButton class="!py-1 !text-xs" @click="fillFromExtracted">↩ Pré-remplir depuis l'OCR</SecondaryButton>
                                </div>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <InputLabel value="N° de facture *" />
                                        <TextInput v-model="convertForm.number" class="mt-1 block w-full" />
                                        <InputError :message="convertForm.errors.number" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel value="Date *" />
                                        <TextInput v-model="convertForm.invoice_date" type="date" class="mt-1 block w-full" />
                                        <InputError :message="convertForm.errors.invoice_date" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel value="Montant HT *" />
                                        <TextInput v-model="convertForm.amount_ht" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                                        <InputError :message="convertForm.errors.amount_ht" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel value="TVA" />
                                        <TextInput v-model="convertForm.vat_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                                        <InputError :message="convertForm.errors.vat_amount" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel value="Total TTC *" />
                                        <TextInput v-model="convertForm.amount_ttc" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                                        <InputError :message="convertForm.errors.amount_ttc" class="mt-1" />
                                    </div>
                                    <div>
                                        <InputLabel value="Catégorie *" />
                                        <select v-model="convertForm.category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                            <option value="marchandises">Marchandises</option>
                                            <option value="services">Services</option>
                                            <option value="fournitures">Fournitures</option>
                                            <option value="loyer">Loyer</option>
                                            <option value="energie">Énergie</option>
                                            <option value="transport">Transport</option>
                                            <option value="autre">Autre</option>
                                        </select>
                                        <InputError :message="convertForm.errors.category" class="mt-1" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <InputLabel value="ID Fournisseur *" />
                                        <TextInput v-model="convertForm.supplier_id" type="number" class="mt-1 block w-full" placeholder="ID du fournisseur existant" />
                                        <InputError :message="convertForm.errors.supplier_id" class="mt-1" />
                                        <p class="mt-1 text-xs text-gray-400">Saisissez l'identifiant d'un fournisseur existant dans votre répertoire.</p>
                                    </div>
                                </div>
                                <div class="mt-4 flex justify-end">
                                    <PrimaryButton :disabled="convertForm.processing" @click="submitConvert">
                                        ✅ Créer l'achat
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="currentScan.status === 'failed'" class="text-sm text-red-600">
                            Échec du traitement OCR.
                        </div>
                    </div>

                    <!-- Scans récents -->
                    <div v-if="scans.length" class="rounded-lg bg-white shadow">
                        <div class="border-b px-4 py-3">
                            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Scans récents (mois en cours)</h3>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-2">Fichier</th>
                                    <th class="px-4 py-2">Date</th>
                                    <th class="px-4 py-2">Statut</th>
                                    <th class="px-4 py-2">Achat lié</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="s in scans" :key="s.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-gray-700">{{ s.original_filename }}</td>
                                    <td class="px-4 py-2 text-gray-500">{{ fmtDate(s.created_at?.slice(0, 10)) }}</td>
                                    <td class="px-4 py-2">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="STATUS_META[s.status]?.class">
                                            {{ STATUS_META[s.status]?.label ?? s.status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-gray-500">
                                        <span v-if="s.purchase_id" class="text-green-600">✔ #{{ s.purchase_id }}</span>
                                        <span v-else class="text-gray-300">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    asset: Object,
    schedule: Array,
});

const showDispose = ref(false);
const disposeForm = useForm({
    status: 'disposed',
    disposal_date: '',
    disposal_price: '',
});

const showWriteOff = ref(false);
const writeOffForm = useForm({ status: 'written_off', disposal_date: new Date().toISOString().slice(0, 10) });

const submitDispose = () => {
    disposeForm.patch(route('assets.update', props.asset.id), {
        onSuccess: () => { showDispose.value = false; },
    });
};

const submitWriteOff = () => {
    writeOffForm.patch(route('assets.update', props.asset.id), {
        onSuccess: () => { showWriteOff.value = false; },
    });
};

const fmt = (n) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(n);
const fmtCur = (n) => fmt(n) + ' ' + (props.asset.currency ?? 'XOF');
const pct = (n) => (n * 100).toFixed(1) + ' %';

const catLabel = (c) => ({ materiel: 'Matériel', vehicule: 'Véhicule', immeuble: 'Immeuble', logiciel: 'Logiciel', autre: 'Autre' }[c] ?? c);

// SVG chart data
const chartW = 600;
const chartH = 200;
const padding = { top: 20, right: 20, bottom: 30, left: 60 };
const innerW = chartW - padding.left - padding.right;
const innerH = chartH - padding.top - padding.bottom;

const maxVal = computed(() => props.asset.purchase_price);
const years  = computed(() => props.schedule.map(r => r.year));

const nbvPoints = computed(() => {
    const pts = [{ x: 0, y: 0 }]; // start: full value = 0% depreciated
    props.schedule.forEach((r, i) => {
        const x = ((i + 1) / props.schedule.length) * innerW;
        const y = innerH - (r.net_book_value / maxVal.value) * innerH;
        pts.push({ x, y });
    });
    return pts;
});

const polyline = computed(() =>
    nbvPoints.value.map(p => `${p.x + padding.left},${p.y + padding.top}`).join(' ')
);

const area = computed(() => {
    const pts = nbvPoints.value;
    const last = pts[pts.length - 1];
    return [
        ...pts.map(p => `${p.x + padding.left},${p.y + padding.top}`),
        `${last.x + padding.left},${innerH + padding.top}`,
        `${padding.left},${innerH + padding.top}`,
    ].join(' ');
});
</script>

<template>
    <Head :title="'Immo — ' + asset.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">{{ asset.name }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ catLabel(asset.category) }} · {{ asset.reference }}</p>
                </div>
                <div class="flex gap-2" v-if="asset.status === 'active'">
                    <SecondaryButton @click="showWriteOff = true">Mettre au rebut</SecondaryButton>
                    <PrimaryButton @click="showDispose = true">Céder</PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Info card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Prix d'achat</p>
                        <p class="font-bold text-gray-900 dark:text-white mt-1">{{ fmtCur(asset.purchase_price) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">VNC actuelle</p>
                        <p class="font-bold text-blue-600 mt-1">{{ fmtCur(asset.current_net_book_value ?? asset.purchase_price) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Durée</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-200 mt-1">{{ asset.duration_years }} ans · {{ asset.depreciation_method === 'linear' ? 'Linéaire' : 'Dégressif' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Date d'achat</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-200 mt-1">{{ asset.purchase_date }}</p>
                    </div>
                    <div v-if="asset.supplier">
                        <p class="text-gray-500">Fournisseur</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-200 mt-1">{{ asset.supplier }}</p>
                    </div>
                    <div v-if="asset.location">
                        <p class="text-gray-500">Localisation</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-200 mt-1">{{ asset.location }}</p>
                    </div>
                    <div v-if="asset.serial_number">
                        <p class="text-gray-500">N° série</p>
                        <p class="font-semibold text-gray-800 dark:text-gray-200 mt-1">{{ asset.serial_number }}</p>
                    </div>
                </div>

                <!-- Depreciation chart -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Courbe de dépréciation</h3>
                    <svg :viewBox="`0 0 ${chartW} ${chartH}`" class="w-full" style="max-height:200px">
                        <!-- area fill -->
                        <polygon :points="area" fill="rgba(59,130,246,0.15)" />
                        <!-- line -->
                        <polyline :points="polyline" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linejoin="round" />
                        <!-- y-axis labels -->
                        <text :x="padding.left - 5" :y="padding.top" text-anchor="end" font-size="10" fill="#9ca3af">{{ fmtCur(maxVal) }}</text>
                        <text :x="padding.left - 5" :y="padding.top + innerH" text-anchor="end" font-size="10" fill="#9ca3af">0</text>
                        <!-- x-axis year labels -->
                        <text v-for="(y, i) in years" :key="y"
                              :x="((i + 1) / schedule.length) * innerW + padding.left"
                              :y="chartH - 5"
                              text-anchor="middle" font-size="9" fill="#9ca3af">{{ y }}</text>
                    </svg>
                </div>

                <!-- Depreciation table -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        Tableau d'amortissement
                    </h3>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Année</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-300">Taux</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-300">Dotation</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-300">Cumul amort.</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-300">VNC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-for="row in schedule" :key="row.year"
                                :class="row.year === new Date().getFullYear() ? 'bg-blue-50 dark:bg-blue-900/20 font-semibold' : ''">
                                <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ row.year }}</td>
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">{{ pct(row.rate) }}</td>
                                <td class="px-4 py-3 text-right text-gray-800 dark:text-gray-200">{{ fmtCur(row.depreciation_amount) }}</td>
                                <td class="px-4 py-3 text-right text-gray-800 dark:text-gray-200">{{ fmtCur(row.accumulated_depreciation) }}</td>
                                <td class="px-4 py-3 text-right text-blue-600">{{ fmtCur(row.net_book_value) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal cession -->
        <Modal :show="showDispose" @close="showDispose = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Cession de l'immobilisation</h3>
                <form @submit.prevent="submitDispose" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de cession *</label>
                        <input v-model="disposeForm.disposal_date" type="date" required
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Prix de cession</label>
                        <input v-model="disposeForm.disposal_price" type="number" min="0" step="1"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm text-sm" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <SecondaryButton type="button" @click="showDispose = false">Annuler</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="disposeForm.processing">Enregistrer la cession</PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal mise au rebut -->
        <Modal :show="showWriteOff" @close="showWriteOff = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Mettre au rebut</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">L'immobilisation sera marquée comme mise au rebut à la date d'aujourd'hui.</p>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="showWriteOff = false">Annuler</SecondaryButton>
                    <PrimaryButton @click="submitWriteOff" :disabled="writeOffForm.processing">Confirmer</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

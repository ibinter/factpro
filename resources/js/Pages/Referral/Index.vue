<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    referrals: {
        type: Array,
        default: () => [],
    },
});

const copied = ref(false);

function copyLink() {
    navigator.clipboard.writeText(props.stats.link).then(() => {
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    });
}

const statusLabels = {
    pending:   'En attente',
    converted: 'Converti',
    rewarded:  'Récompensé',
};

const statusClasses = {
    pending:   'bg-yellow-100 text-yellow-800',
    converted: 'bg-blue-100 text-blue-800',
    rewarded:  'bg-green-100 text-green-800',
};
</script>

<template>
    <Head title="Programme ambassadeur" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                🎁 Programme ambassadeur
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-8">

                <!-- Lien de parrainage -->
                <div class="rounded-xl border border-brand-200 bg-brand-50 p-6">
                    <h3 class="mb-1 text-lg font-semibold text-brand-900">Votre lien de parrainage</h3>
                    <p class="mb-4 text-sm text-brand-700">
                        Partagez ce lien. Chaque filleul qui souscrit un abonnement vous offre
                        <strong>1 mois gratuit</strong> sur votre licence.
                    </p>
                    <div class="flex items-center gap-2">
                        <input
                            :value="stats.link"
                            readonly
                            class="flex-1 rounded-lg border border-brand-300 bg-white px-3 py-2 text-sm text-gray-700 focus:outline-none"
                        />
                        <button
                            type="button"
                            @click="copyLink"
                            class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-brand-700 focus:outline-none"
                        >
                            {{ copied ? '✓ Copié !' : 'Copier' }}
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-brand-600">
                        Code : <strong>{{ stats.code }}</strong>
                    </p>
                </div>

                <!-- Statistiques -->
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 bg-white p-5 text-center shadow-sm">
                        <div class="text-3xl font-bold text-gray-900">{{ stats.total }}</div>
                        <div class="mt-1 text-xs font-medium uppercase tracking-wide text-gray-500">Parrainages</div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-5 text-center shadow-sm">
                        <div class="text-3xl font-bold text-blue-600">{{ stats.converted }}</div>
                        <div class="mt-1 text-xs font-medium uppercase tracking-wide text-gray-500">Convertis</div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-5 text-center shadow-sm">
                        <div class="text-3xl font-bold text-green-600">{{ stats.rewarded }}</div>
                        <div class="mt-1 text-xs font-medium uppercase tracking-wide text-gray-500">Récompensés</div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-white p-5 text-center shadow-sm">
                        <div class="text-3xl font-bold text-gold-600">{{ stats.months_earned }}</div>
                        <div class="mt-1 text-xs font-medium uppercase tracking-wide text-gray-500">Mois gagnés</div>
                    </div>
                </div>

                <!-- Tableau des filleuls -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="font-semibold text-gray-800">Mes filleuls</h3>
                    </div>

                    <div v-if="referrals.length === 0" class="px-6 py-10 text-center text-sm text-gray-500">
                        Aucun filleul pour l'instant — partagez votre lien !
                    </div>

                    <table v-else class="w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3 text-left">Filleul</th>
                                <th class="px-6 py-3 text-left">Statut</th>
                                <th class="px-6 py-3 text-left">Date</th>
                                <th class="px-6 py-3 text-left">Récompense</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="r in referrals" :key="r.id">
                                <td class="px-6 py-3 font-medium text-gray-700">
                                    {{ r.referred_name ?? '—' }}
                                </td>
                                <td class="px-6 py-3">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                        :class="statusClasses[r.status]"
                                    >
                                        {{ statusLabels[r.status] ?? r.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-500">{{ r.created_at }}</td>
                                <td class="px-6 py-3 text-gray-500">
                                    <span v-if="r.status === 'rewarded'" class="text-green-600 font-medium">
                                        +{{ r.reward_months }} mois offert{{ r.reward_months > 1 ? 's' : '' }}
                                    </span>
                                    <span v-else class="text-gray-400">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Offres spéciales ONG / École -->
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="font-semibold text-gray-800">Offres spéciales</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Vous êtes une ONG ou un établissement scolaire ? Bénéficiez d'un tarif préférentiel.
                        </p>
                    </div>
                    <div class="grid gap-4 p-6 sm:grid-cols-2">
                        <!-- ONG -->
                        <div class="rounded-lg border-2 border-dashed border-green-300 bg-green-50 p-5">
                            <div class="mb-2 flex items-center gap-2">
                                <span class="text-2xl">🌿</span>
                                <h4 class="font-semibold text-green-800">ONG / Association</h4>
                            </div>
                            <p class="text-sm text-green-700">
                                <strong>50% de réduction</strong> sur tous les forfaits pour les organisations
                                à but non lucratif reconnues.
                            </p>
                            <p class="mt-3 text-xs text-green-600">
                                Code promo : <strong>ONG50</strong>
                            </p>
                            <a
                                href="mailto:contact@ibigfactpro.com?subject=Offre ONG"
                                class="mt-3 inline-block rounded-md bg-green-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-green-700"
                            >
                                Faire une demande
                            </a>
                        </div>

                        <!-- École -->
                        <div class="rounded-lg border-2 border-dashed border-blue-300 bg-blue-50 p-5">
                            <div class="mb-2 flex items-center gap-2">
                                <span class="text-2xl">🎓</span>
                                <h4 class="font-semibold text-blue-800">École / Université</h4>
                            </div>
                            <p class="text-sm text-blue-700">
                                <strong>40% de réduction</strong> sur tous les forfaits pour les établissements
                                d'enseignement.
                            </p>
                            <p class="mt-3 text-xs text-blue-600">
                                Code promo : <strong>SCHOOL40</strong>
                            </p>
                            <a
                                href="mailto:contact@ibigfactpro.com?subject=Offre Ecole"
                                class="mt-3 inline-block rounded-md bg-blue-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-blue-700"
                            >
                                Faire une demande
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

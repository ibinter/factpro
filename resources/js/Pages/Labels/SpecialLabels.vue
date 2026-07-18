<script setup>
import { ref, computed } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'

const props = defineProps({
    document:            { type: Object, required: true },
    customer:            { type: Object, default: null },
    items:               { type: Array, default: () => [] },
    deliveryStickerUrl:  { type: String, required: true },
    warrantyBaseUrl:     { type: String, required: true },
    thermal110Url:       { type: String, required: true },
})

// Onglet actif
const activeTab = ref('sticker')

// Étiquette garantie
const selectedItemIndex = ref(0)
const warrantyYears = ref(2)

const warrantyUrl = computed(() => {
    // Remplacer le dernier segment (0) par l'index sélectionné
    const base = props.warrantyBaseUrl.replace(/\/\d+(\?|$)/, `/${selectedItemIndex.value}$1`)
    return base + `?years=${warrantyYears.value}`
})

const tabs = [
    { key: 'sticker',  label: '🏷️ Sticker Livraison' },
    { key: 'warranty', label: '🔖 Étiquette Garantie' },
    { key: 'thermal',  label: '🧾 Ticket 110mm' },
]
</script>

<template>
    <Head :title="`Étiquettes spéciales — ${document.number}`" />

    <div class="max-w-5xl mx-auto px-4 py-8">
        <!-- Titre -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                Étiquettes spéciales
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ document.type_label }} · {{ document.number }}
            </p>
        </div>

        <!-- Onglets -->
        <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-6">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                @click="activeTab = tab.key"
                class="px-4 py-2.5 text-sm font-medium rounded-t-lg border border-b-0 transition-colors"
                :class="activeTab === tab.key
                    ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-blue-600 dark:text-blue-400'
                    : 'bg-gray-50 dark:bg-gray-900 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
            >
                {{ tab.label }}
            </button>
        </div>

        <!-- Sticker livraison -->
        <div v-if="activeTab === 'sticker'" class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
                    Sticker de livraison A6
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Format 148×105 mm — imprimable sur étiquette adhésive A6. Inclut l'adresse destinataire,
                    la référence expédition et un QR code de vérification.
                </p>

                <div v-if="customer" class="text-sm bg-gray-50 dark:bg-gray-700 rounded p-3 mb-4">
                    <div class="font-medium">{{ customer.name }}</div>
                    <div v-if="customer.address" class="text-gray-500">{{ customer.address }}</div>
                    <div v-if="customer.city"    class="text-gray-500">{{ customer.city }}</div>
                    <div v-if="customer.phone"   class="text-gray-500">{{ customer.phone }}</div>
                </div>
                <div v-else class="text-sm text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 rounded p-3 mb-4">
                    Aucun client associé à ce document — le sticker sera généré sans destinataire.
                </div>

                <a
                    :href="deliveryStickerUrl"
                    target="_blank"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Télécharger PDF
                </a>
            </div>
        </div>

        <!-- Étiquette garantie -->
        <div v-if="activeTab === 'warranty'" class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
                    Étiquette de garantie
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Format 85×55 mm (carte de visite). Sélectionnez l'article et la durée de garantie.
                </p>

                <!-- Sélecteur d'article -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Article
                    </label>
                    <select
                        v-model="selectedItemIndex"
                        class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
                        <option v-for="(item, idx) in items" :key="item.index" :value="idx">
                            {{ item.description }}
                        </option>
                    </select>
                </div>

                <!-- Durée de garantie -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Durée de garantie
                    </label>
                    <div class="flex gap-2">
                        <button
                            v-for="y in [1, 2, 3, 5]"
                            :key="y"
                            @click="warrantyYears = y"
                            class="px-3 py-1.5 rounded text-sm font-medium border transition-colors"
                            :class="warrantyYears === y
                                ? 'bg-blue-600 border-blue-600 text-white'
                                : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'"
                        >
                            {{ y }} an{{ y > 1 ? 's' : '' }}
                        </button>
                    </div>
                </div>

                <a
                    :href="warrantyUrl"
                    target="_blank"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Télécharger PDF
                </a>
            </div>
        </div>

        <!-- Ticket 110mm -->
        <div v-if="activeTab === 'thermal'" class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
                    Ticket thermique 110mm
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Format 104mm — supermarché, hôtel, station-service, pharmacie.
                    Mise en page 2 colonnes avec détail TVA et informations client.
                </p>

                <div class="flex gap-3">
                    <a
                        :href="thermal110Url"
                        target="_blank"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-medium px-5 py-2.5 rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Imprimer / Prévisualiser
                    </a>
                </div>
            </div>
        </div>
    </div>
</template>

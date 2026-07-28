<script setup>
import { computed } from 'vue'
// route() est disponible globalement via Ziggy (injecté dans app.js)

const props = defineProps({
    chain: {
        type: Array,
        default: () => [],
    },
})

const typeLabels = {
    quote:         'Devis',
    order:         'Bon commande',
    delivery_note: 'Bon livraison',
    invoice:       'Facture',
    credit_note:   'Avoir',
    purchase_order:'Bon achat',
}

const statusConfig = {
    draft:     { label: 'Brouillon', bg: 'bg-gray-100 dark:bg-gray-700', text: 'text-gray-600 dark:text-gray-300', ring: 'ring-gray-300 dark:ring-gray-600' },
    sent:      { label: 'Envoyé',    bg: 'bg-blue-100 dark:bg-blue-900',  text: 'text-blue-700 dark:text-blue-300', ring: 'ring-blue-300 dark:ring-blue-600' },
    paid:      { label: 'Payé',      bg: 'bg-green-100 dark:bg-green-900', text: 'text-green-700 dark:text-green-300', ring: 'ring-green-300 dark:ring-green-600' },
    overdue:   { label: 'En retard', bg: 'bg-red-100 dark:bg-red-900',    text: 'text-red-600 dark:text-red-300',  ring: 'ring-red-300 dark:ring-red-600' },
    cancelled: { label: 'Annulé',    bg: 'bg-red-200 dark:bg-red-950',    text: 'text-red-800 dark:text-red-200',  ring: 'ring-red-400 dark:ring-red-700' },
}

function statusCfg(status) {
    return statusConfig[status] ?? statusConfig.draft
}

function formatAmount(total, currency) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: currency ?? 'XOF', minimumFractionDigits: 0 }).format(total ?? 0)
}
</script>

<template>
    <div v-if="chain.length" class="w-full overflow-x-auto">
        <div class="flex items-center gap-0 min-w-max py-4 px-2">
            <template v-for="(item, index) in chain" :key="item.id">
                <!-- Node -->
                <a
                    :href="route('documents.show', item.id)"
                    class="flex flex-col items-center group"
                >
                    <!-- Card -->
                    <div
                        class="flex flex-col items-center w-36 rounded-xl border p-3 shadow-sm transition-shadow hover:shadow-md"
                        :class="[statusCfg(item.status).bg, statusCfg(item.status).ring, 'ring-1']"
                    >
                        <!-- Type label -->
                        <span class="text-xs font-semibold uppercase tracking-wide mb-1"
                              :class="statusCfg(item.status).text">
                            {{ typeLabels[item.type] ?? item.type }}
                        </span>

                        <!-- Number -->
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-100 truncate w-full text-center">
                            {{ item.number }}
                        </span>

                        <!-- Amount -->
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ formatAmount(item.total, item.currency) }}
                        </span>

                        <!-- Status badge -->
                        <span
                            class="mt-2 inline-block rounded-full px-2 py-0.5 text-xs font-medium"
                            :class="[statusCfg(item.status).bg, statusCfg(item.status).text]"
                        >
                            {{ statusCfg(item.status).label }}
                        </span>
                    </div>
                </a>

                <!-- Arrow connector -->
                <div
                    v-if="index < chain.length - 1"
                    class="flex items-center px-1 text-gray-400 dark:text-gray-500"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </template>
        </div>
    </div>

    <div v-else class="text-sm text-gray-400 dark:text-gray-500 italic py-2">
        Aucune chaîne de conversion disponible.
    </div>
</template>

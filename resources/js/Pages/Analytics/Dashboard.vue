<template>
  <AppLayout title="Analytics & BI">
    <div class="p-6 space-y-6">

      <!-- Header -->
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Analytics & BI</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Tableau de bord intelligent — vue en temps réel</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <!-- Sélecteur période -->
          <select
            v-model="period"
            @change="reloadAllWidgets"
            class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm px-3 py-2"
          >
            <option value="7d">7 derniers jours</option>
            <option value="30d">30 derniers jours</option>
            <option value="90d">90 derniers jours</option>
            <option value="365d">12 derniers mois</option>
          </select>
          <!-- Ajouter widget -->
          <button @click="showAddWidget = true" class="btn-primary text-sm">
            + Ajouter un widget
          </button>
          <!-- AI Insights -->
          <button @click="fetchAiInsights" :disabled="aiLoading" class="btn-secondary text-sm flex items-center gap-2">
            <svg v-if="aiLoading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <span>✨ Insights IA</span>
          </button>
          <!-- Export PDF -->
          <a :href="`/analytics/export/report?period=${period}`" target="_blank" class="btn-secondary text-sm">
            📄 PDF
          </a>
        </div>
      </div>

      <!-- AI Insights panel -->
      <div v-if="aiInsights.length" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-4 space-y-2">
        <div class="flex items-center justify-between">
          <h3 class="font-semibold text-blue-800 dark:text-blue-300 text-sm">Insights IA</h3>
          <button @click="aiInsights = []" class="text-blue-400 hover:text-blue-600 text-xs">✕ Fermer</button>
        </div>
        <p v-for="(insight, i) in aiInsights" :key="i" class="text-sm text-blue-700 dark:text-blue-300">{{ insight }}</p>
      </div>

      <!-- Widget grid -->
      <div class="grid grid-cols-4 gap-4">

        <!-- KPI Summary -->
        <div class="col-span-4">
          <KpiWidget :period="period" />
        </div>

        <!-- Charts row -->
        <div class="col-span-4 lg:col-span-3">
          <RevenueChartWidget :period="period" :compare="compare" />
        </div>
        <div class="col-span-4 lg:col-span-1">
          <RecoveryRateWidget :period="period" />
        </div>

        <div class="col-span-4 lg:col-span-2">
          <InvoiceStatusWidget :period="period" />
        </div>
        <div class="col-span-4 lg:col-span-2">
          <CashflowWidget />
        </div>

        <div class="col-span-4 lg:col-span-2">
          <TopClientsWidget :period="period" />
        </div>
        <div class="col-span-4 lg:col-span-2">
          <TopProductsWidget :period="period" />
        </div>

      </div>

      <!-- Modal ajouter widget -->
      <div v-if="showAddWidget" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 w-full max-w-md">
          <h3 class="font-bold text-lg mb-4">Ajouter un widget</h3>
          <div class="grid grid-cols-2 gap-3">
            <button
              v-for="(meta, key) in availableWidgets"
              :key="key"
              @click="addWidget(key)"
              class="border border-gray-200 dark:border-gray-600 rounded-lg p-3 text-left hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
            >
              <p class="font-medium text-sm">{{ meta.label }}</p>
            </button>
          </div>
          <button @click="showAddWidget = false" class="mt-4 w-full btn-secondary text-sm">Annuler</button>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { ref, provide } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
import KpiWidget from './Widgets/KpiWidget.vue'
import RevenueChartWidget from './Widgets/RevenueChartWidget.vue'
import RecoveryRateWidget from './Widgets/RecoveryRateWidget.vue'
import InvoiceStatusWidget from './Widgets/InvoiceStatusWidget.vue'
import CashflowWidget from './Widgets/CashflowWidget.vue'
import TopClientsWidget from './Widgets/TopClientsWidget.vue'
import TopProductsWidget from './Widgets/TopProductsWidget.vue'

const props = defineProps({
  widgets: Array,
  availableWidgets: Object,
})

const period       = ref('30d')
const compare      = ref(false)
const showAddWidget= ref(false)
const aiLoading    = ref(false)
const aiInsights   = ref([])

// Provide period globally to child widgets
provide('analyticsPeriod', period)

function reloadAllWidgets () {
  // Child widgets listen to the injected period via vue provide/inject
}

async function fetchAiInsights () {
  aiLoading.value = true
  try {
    const res = await fetch('/analytics/ai-insights', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
    })
    const json = await res.json()
    aiInsights.value = json.insights ?? []
  } catch (e) {
    aiInsights.value = ['Impossible de charger les insights IA.']
  } finally {
    aiLoading.value = false
  }
}

async function addWidget (type) {
  const meta = props.availableWidgets[type]
  await fetch('/analytics/widgets', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
    body: JSON.stringify({ widget_type: type, width: meta.defaultWidth, height: meta.defaultHeight }),
  })
  showAddWidget.value = false
  router.reload()
}
</script>

<style scoped>
.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition-colors;
}
.btn-secondary {
  @apply bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium px-4 py-2 rounded-lg transition-colors;
}
</style>

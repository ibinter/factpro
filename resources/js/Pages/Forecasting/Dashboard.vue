<template>
  <AppLayout title="Forecasting & Objectifs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

      <!-- En-tête -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Forecasting & Objectifs</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Prévisions de chiffre d'affaires et suivi des objectifs</p>
        </div>
        <div class="flex gap-3">
          <button
            @click="showTargetModal = true"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Définir un objectif
          </button>
          <a
            :href="route('forecasting.export')"
            class="inline-flex items-center px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-medium hover:bg-gray-800 transition-colors"
            target="_blank"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Exporter rapport direction
          </a>
        </div>
      </div>

      <!-- Alertes sous-performance -->
      <div v-if="underperformance.length > 0" class="space-y-2">
        <div
          v-for="agent in underperformance"
          :key="agent.user_id"
          class="flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-4 py-3"
        >
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
            ⚠ Sous-performance
          </span>
          <span class="text-sm font-medium text-red-800 dark:text-red-200">
            {{ agent.name }} — {{ agent.pct_achieved }}% de l'objectif
            ({{ fmt(agent.actual) }} / {{ fmt(agent.target) }} {{ agent.currency }})
          </span>
        </div>
      </div>

      <!-- Cartes KPI : Objectif + Prévision -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Carte Objectif du mois -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Objectif du mois</h2>

          <div class="flex items-end justify-between mb-3">
            <div>
              <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ fmt(comparison.actual) }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-400">réalisé sur {{ fmt(comparison.target) }} {{ comparison.currency }}</div>
            </div>
            <div :class="['text-2xl font-bold', pctColor(comparison.pct_achieved)]">
              {{ comparison.pct_achieved }}%
            </div>
          </div>

          <!-- Barre de progression -->
          <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
            <div
              :class="['h-3 rounded-full transition-all duration-500', progressBarColor(comparison.pct_achieved)]"
              :style="{ width: Math.min(comparison.pct_achieved, 100) + '%' }"
            ></div>
          </div>

          <div class="mt-3 flex items-center gap-2">
            <span v-if="comparison.on_track" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
              ✓ Sur la bonne voie
            </span>
            <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
              ✗ En retard
            </span>
            <span v-if="comparison.gap > 0" class="text-xs text-gray-500 dark:text-gray-400">
              Écart : {{ fmt(comparison.gap) }} {{ comparison.currency }} restants
            </span>
          </div>

          <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 grid grid-cols-2 gap-4 text-sm">
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Jours écoulés</div>
              <div class="font-semibold">{{ forecast.days_elapsed }}j / {{ forecast.days_elapsed + forecast.days_remaining }}j</div>
            </div>
            <div>
              <div class="text-xs text-gray-500 dark:text-gray-400">Cadence journalière</div>
              <div class="font-semibold">{{ fmt(forecast.daily_rate) }} {{ comparison.currency }}/j</div>
            </div>
          </div>
        </div>

        <!-- Carte Prévision -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
          <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Prévision de fin de mois</h2>

          <div class="space-y-4">
            <div v-for="(item, key) in forecastMethods" :key="key" class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ item.label }}</div>
                <div class="text-xs text-gray-400">{{ item.description }}</div>
              </div>
              <div class="text-right">
                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ fmt(item.value) }}</div>
                <div class="text-xs text-gray-500">{{ comparison.currency }}</div>
              </div>
            </div>
          </div>

          <!-- Recommandation -->
          <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Recommandation (moyenne des 3 méthodes)</div>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ fmt(recommendedForecast) }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ comparison.currency }}</div>
          </div>
        </div>
      </div>

      <!-- Graphique SVG Réalisé vs Objectif 12 mois -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-6">CA Réalisé vs Objectif — 12 mois</h2>
        <div class="overflow-x-auto">
          <svg :viewBox="`0 0 ${chartWidth} ${chartHeight}`" class="w-full min-w-[600px]" style="height: 280px;">
            <!-- Grille horizontale -->
            <g v-for="(tick, i) in yTicks" :key="'tick-'+i">
              <line :x1="chartPadding" :y1="yPos(tick)" :x2="chartWidth - 20" :y2="yPos(tick)" stroke="#e2e8f0" stroke-width="1"/>
              <text :x="chartPadding - 8" :y="yPos(tick) + 4" text-anchor="end" font-size="9" fill="#94a3b8">{{ fmtShort(tick) }}</text>
            </g>

            <!-- Barres réalisé (bleu) + objectif (or pointillé) -->
            <g v-for="(row, i) in historyWithTarget" :key="'bar-'+i">
              <!-- Barre réalisé -->
              <rect
                :x="barX(i)"
                :y="yPos(row.revenue)"
                :width="barWidth"
                :height="Math.max(0, chartHeight - chartPaddingBottom - yPos(row.revenue))"
                fill="#3b82f6"
                rx="2"
                opacity="0.85"
              />
              <!-- Ligne objectif (pointillée or) -->
              <line
                v-if="row.target"
                :x1="barX(i) - 4"
                :y1="yPos(row.target)"
                :x2="barX(i) + barWidth + 4"
                :y2="yPos(row.target)"
                stroke="#f59e0b"
                stroke-width="2"
                stroke-dasharray="4,2"
              />
              <!-- Label mois -->
              <text
                :x="barX(i) + barWidth / 2"
                :y="chartHeight - 4"
                text-anchor="middle"
                font-size="8"
                fill="#94a3b8"
              >{{ shortLabel(row.label) }}</text>
            </g>

            <!-- Légende -->
            <rect x="20" y="10" width="10" height="10" fill="#3b82f6" rx="2"/>
            <text x="34" y="19" font-size="9" fill="#64748b">Réalisé</text>
            <line x1="80" y1="15" x2="95" y2="15" stroke="#f59e0b" stroke-width="2" stroke-dasharray="4,2"/>
            <text x="99" y="19" font-size="9" fill="#64748b">Objectif</text>
          </svg>
        </div>
      </div>

      <!-- Tableau historique précision prévisions -->
      <div v-if="accuracy.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Précision des prévisions passées</h2>
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700">
              <th class="text-left py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Période</th>
              <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">CA réel</th>
              <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Prévision</th>
              <th class="text-right py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Écart</th>
              <th class="text-center py-2 px-3 text-xs font-semibold text-gray-500 uppercase">Précision</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in accuracy" :key="row.period" class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
              <td class="py-2 px-3 font-medium">{{ row.period }}</td>
              <td class="py-2 px-3 text-right">{{ fmt(row.actual_revenue) }}</td>
              <td class="py-2 px-3 text-right">{{ fmt(row.forecasted_revenue) }}</td>
              <td class="py-2 px-3 text-right" :class="row.accuracy_pct > 20 ? 'text-red-600' : 'text-green-600'">
                {{ row.accuracy_pct }}%
              </td>
              <td class="py-2 px-3 text-center">
                <span :class="['inline-block px-2 py-0.5 rounded-full text-xs font-bold', row.accuracy_pct <= 10 ? 'bg-green-100 text-green-800' : row.accuracy_pct <= 20 ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800']">
                  {{ row.accuracy_pct <= 10 ? 'Excellente' : row.accuracy_pct <= 20 ? 'Bonne' : 'À améliorer' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>

    <!-- Modal Définir un objectif -->
    <Teleport to="body">
      <div v-if="showTargetModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
          <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Définir un objectif</h3>
          <form @submit.prevent="submitTarget" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type de période</label>
                <select v-model="targetForm.period_type" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                  <option value="month">Mensuel</option>
                  <option value="quarter">Trimestriel</option>
                  <option value="year">Annuel</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Année</label>
                <input v-model.number="targetForm.period_year" type="number" min="2020" max="2100" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"/>
              </div>
            </div>
            <div v-if="targetForm.period_type === 'month'">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mois (1-12)</label>
              <input v-model.number="targetForm.period_month" type="number" min="1" max="12" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant objectif CA (HT)</label>
              <input v-model.number="targetForm.target_amount" type="number" min="0" step="1000" required class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"/>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nb factures cible (opt.)</label>
                <input v-model.number="targetForm.target_invoices" type="number" min="0" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"/>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Devise</label>
                <input v-model="targetForm.currency" type="text" maxlength="3" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"/>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vendeur (laisser vide = objectif global)</label>
              <input v-model.number="targetForm.assigned_to_id" type="number" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white" placeholder="ID utilisateur (optionnel)"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
              <textarea v-model="targetForm.notes" rows="2" class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
            </div>
            <div class="flex gap-3 pt-2">
              <button type="submit" :disabled="submitting" class="flex-1 bg-blue-600 text-white rounded-lg py-2 text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
                {{ submitting ? 'Enregistrement...' : 'Enregistrer' }}
              </button>
              <button type="button" @click="showTargetModal = false" class="flex-1 border border-gray-300 dark:border-gray-600 rounded-lg py-2 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700">
                Annuler
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  forecast: Object,
  comparison: Object,
  history: Array,
  underperformance: Array,
  accuracy: Array,
})

// ---- Formatage ----
function fmt(val) {
  if (!val && val !== 0) return '—'
  return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(val)
}
function fmtShort(val) {
  if (val >= 1_000_000) return (val / 1_000_000).toFixed(1) + 'M'
  if (val >= 1_000) return (val / 1_000).toFixed(0) + 'k'
  return fmt(val)
}
function shortLabel(label) {
  return label ? label.substring(0, 6) : ''
}

// ---- Couleurs ----
function pctColor(pct) {
  if (pct >= 80) return 'text-green-600 dark:text-green-400'
  if (pct >= 50) return 'text-orange-500 dark:text-orange-400'
  return 'text-red-600 dark:text-red-400'
}
function progressBarColor(pct) {
  if (pct >= 80) return 'bg-green-500'
  if (pct >= 50) return 'bg-orange-400'
  return 'bg-red-500'
}

// ---- Méthodes prévision ----
const forecastMethods = computed(() => [
  { key: 'linear_projection', label: 'Projection linéaire', description: 'Basée sur la cadence journalière actuelle', value: props.forecast?.forecasts?.linear_projection ?? 0 },
  { key: 'moving_average', label: 'Moyenne mobile 3 mois', description: 'Basée sur les 3 derniers mois', value: props.forecast?.forecasts?.moving_average ?? 0 },
  { key: 'last_year', label: 'Même mois N-1', description: "CA du même mois l'année dernière", value: props.forecast?.forecasts?.last_year ?? 0 },
])

const recommendedForecast = computed(() => {
  const vals = forecastMethods.value.map(m => m.value).filter(v => v > 0)
  if (!vals.length) return 0
  return Math.round(vals.reduce((a, b) => a + b, 0) / vals.length)
})

// ---- SVG Chart ----
const chartWidth = 760
const chartHeight = 260
const chartPadding = 60
const chartPaddingBottom = 30
const barGroupWidth = computed(() => (chartWidth - chartPadding - 20) / (props.history?.length || 12))
const barWidth = computed(() => barGroupWidth.value * 0.65)

function barX(i) {
  return chartPadding + i * barGroupWidth.value + barGroupWidth.value * 0.175
}

const maxRevenue = computed(() => {
  const values = (props.history || []).map(r => Math.max(r.revenue, r.target || 0))
  return Math.max(...values, 1)
})

const yTicks = computed(() => {
  const max = maxRevenue.value
  const step = Math.ceil(max / 4 / 100000) * 100000 || 100000
  return [0, step, step * 2, step * 3, step * 4].filter(v => v <= max * 1.15)
})

function yPos(val) {
  const usableHeight = chartHeight - chartPaddingBottom - 20
  return 20 + usableHeight - (val / (maxRevenue.value * 1.1)) * usableHeight
}

const historyWithTarget = computed(() => props.history || [])

// ---- Modal objectif ----
const showTargetModal = ref(false)
const submitting = ref(false)
const now = new Date()
const targetForm = ref({
  period_type: 'month',
  period_month: now.getMonth() + 1,
  period_year: now.getFullYear(),
  target_amount: null,
  target_invoices: null,
  target_customers: null,
  currency: 'XOF',
  notes: '',
  assigned_to_id: null,
})

async function submitTarget() {
  submitting.value = true
  try {
    await fetch(route('forecasting.targets.store'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        'Accept': 'application/json',
      },
      body: JSON.stringify(targetForm.value),
    })
    showTargetModal.value = false
    router.reload({ only: ['comparison', 'underperformance'] })
  } finally {
    submitting.value = false
  }
}
</script>

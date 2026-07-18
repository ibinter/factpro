<template>
  <AuthenticatedLayout :title="'Analyse ABC — Pareto'">
    <div class="space-y-6">
      <!-- En-tête & sélecteur période -->
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Analyse ABC / Pareto</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Classification produits selon la règle des 80/20</p>
        </div>
        <div class="flex items-center gap-3">
          <label class="text-sm text-gray-700 dark:text-gray-300 font-medium">Période :</label>
          <select
            v-model="selectedMonths"
            class="rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
            @change="loadData"
          >
            <option :value="3">3 mois</option>
            <option :value="6">6 mois</option>
            <option :value="12">12 mois</option>
            <option :value="24">24 mois</option>
          </select>
          <button
            class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors"
            @click="exportCsv"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Exporter CSV
          </button>
        </div>
      </div>

      <!-- Cartes résumé -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border-l-4 border-green-500">
          <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wide">Classe A</p>
          <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ analysis.summary.A.count }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">produits · ~80% du CA</p>
          <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">{{ formatPrice(analysis.summary.A.revenue) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border-l-4 border-orange-400">
          <p class="text-xs font-semibold text-orange-500 dark:text-orange-400 uppercase tracking-wide">Classe B</p>
          <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ analysis.summary.B.count }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">produits · ~15% du CA</p>
          <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">{{ formatPrice(analysis.summary.B.revenue) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border-l-4 border-red-400">
          <p class="text-xs font-semibold text-red-500 dark:text-red-400 uppercase tracking-wide">Classe C</p>
          <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ analysis.summary.C.count }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">produits · ~5% du CA</p>
          <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">{{ formatPrice(analysis.summary.C.revenue) }}</p>
        </div>
      </div>

      <!-- Graphique Pareto -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Graphique de Pareto</h2>
        <div class="overflow-x-auto">
          <svg :width="chartWidth" height="280" class="min-w-full">
            <!-- Grille -->
            <g v-for="i in 5" :key="'grid-'+i">
              <line
                :x1="marginLeft"
                :y1="marginTop + (chartHeight / 5) * (i - 1)"
                :x2="marginLeft + innerWidth"
                :y2="marginTop + (chartHeight / 5) * (i - 1)"
                stroke="#e5e7eb" stroke-width="1"
              />
              <text
                :x="marginLeft - 8"
                :y="marginTop + (chartHeight / 5) * (i - 1) + 4"
                text-anchor="end" font-size="10" fill="#9ca3af"
              >{{ 100 - (i - 1) * 20 }}%</text>
            </g>

            <!-- Barres CA -->
            <rect
              v-for="(item, idx) in chartItems"
              :key="'bar-'+idx"
              :x="marginLeft + idx * barWidth"
              :y="marginTop + chartHeight - barHeight(item.revenue)"
              :width="barWidth - 2"
              :height="barHeight(item.revenue)"
              :fill="classColor(item.class)"
              rx="2"
            />

            <!-- Courbe % cumulé -->
            <polyline
              :points="cumulativePoints"
              fill="none"
              stroke="#6366f1"
              stroke-width="2"
            />

            <!-- Points courbe -->
            <circle
              v-for="(item, idx) in chartItems"
              :key="'dot-'+idx"
              :cx="marginLeft + idx * barWidth + barWidth / 2"
              :cy="marginTop + chartHeight - (item.cumulative_pct / 100) * chartHeight"
              r="3"
              fill="#6366f1"
            />

            <!-- Ligne 80% -->
            <line
              :x1="marginLeft"
              :y1="marginTop + chartHeight * 0.2"
              :x2="marginLeft + innerWidth"
              :y2="marginTop + chartHeight * 0.2"
              stroke="#f59e0b" stroke-width="1" stroke-dasharray="4"
            />
            <text :x="marginLeft + innerWidth + 4" :y="marginTop + chartHeight * 0.2 + 4" font-size="10" fill="#f59e0b">80%</text>
          </svg>
        </div>
        <div class="flex gap-6 mt-4 text-xs text-gray-500 dark:text-gray-400">
          <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-500 inline-block"></span> Classe A</span>
          <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-orange-400 inline-block"></span> Classe B</span>
          <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-400 inline-block"></span> Classe C</span>
          <span class="flex items-center gap-1"><span class="w-3 h-1 bg-indigo-500 inline-block"></span> % CA cumulé</span>
        </div>
      </div>

      <!-- Filtres classe -->
      <div class="flex gap-2">
        <button
          v-for="f in ['Tous', 'A', 'B', 'C']" :key="f"
          :class="['px-4 py-2 rounded-lg text-sm font-medium transition-colors',
            activeFilter === f
              ? 'bg-indigo-600 text-white'
              : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700']"
          @click="activeFilter = f"
        >
          {{ f === 'Tous' ? 'Tous les produits' : 'Classe ' + f }}
        </button>
      </div>

      <!-- Tableau de classification -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Classe</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Produit</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">CA Période</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">% CA</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">% Cumulé</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Stock</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Recommandation</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-for="item in filteredProducts" :key="item.product_id" class="hover:bg-gray-50 dark:hover:bg-gray-750">
                <td class="px-4 py-3">
                  <span :class="classBadge(item.class)" class="px-2 py-1 rounded-full text-xs font-bold">
                    {{ item.class }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <p class="text-sm font-medium text-gray-900 dark:text-white">{{ item.product_name }}</p>
                  <p v-if="item.product_sku" class="text-xs text-gray-400">{{ item.product_sku }}</p>
                </td>
                <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-white tabular-nums">{{ formatPrice(item.revenue) }}</td>
                <td class="px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400 tabular-nums">{{ item.revenue_pct.toFixed(1) }}%</td>
                <td class="px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400 tabular-nums">{{ item.cumulative_pct.toFixed(1) }}%</td>
                <td class="px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400 tabular-nums">{{ item.stock_quantity.toFixed(0) }}</td>
                <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 max-w-xs">
                  {{ item.recommendations[0] }}
                </td>
              </tr>
              <tr v-if="filteredProducts.length === 0">
                <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400 dark:text-gray-500">
                  Aucun produit pour ce filtre.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  analysis: Object,
  months: Number,
})

const selectedMonths = ref(props.months)
const activeFilter = ref('Tous')

// Chart dimensions
const marginLeft = 40
const marginTop = 10
const chartHeight = 200
const maxBars = 30
const chartItems = computed(() => props.analysis.products.slice(0, maxBars))
const barWidth = computed(() => Math.max(8, Math.floor(600 / Math.max(chartItems.value.length, 1))))
const innerWidth = computed(() => chartItems.value.length * barWidth.value)
const chartWidth = computed(() => marginLeft + innerWidth.value + 50)

const maxRevenue = computed(() => {
  const max = Math.max(...chartItems.value.map(p => p.revenue), 1)
  return max
})

function barHeight(revenue) {
  return (revenue / maxRevenue.value) * chartHeight
}

const cumulativePoints = computed(() => {
  return chartItems.value
    .map((item, idx) => {
      const x = marginLeft + idx * barWidth.value + barWidth.value / 2
      const y = marginTop + chartHeight - (item.cumulative_pct / 100) * chartHeight
      return `${x},${y}`
    })
    .join(' ')
})

function classColor(cls) {
  return { A: '#22c55e', B: '#fb923c', C: '#f87171' }[cls] || '#94a3b8'
}

function classBadge(cls) {
  return {
    A: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
    B: 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
    C: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
  }[cls] || ''
}

const filteredProducts = computed(() => {
  if (activeFilter.value === 'Tous') return props.analysis.products
  return props.analysis.products.filter(p => p.class === activeFilter.value)
})

function formatPrice(value) {
  return new Intl.NumberFormat('fr-CI', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(value)
}

function loadData() {
  router.get(route('stock.abc'), { months: selectedMonths.value }, { preserveState: true, replace: true })
}

function exportCsv() {
  window.location.href = route('stock.abc.export', { months: selectedMonths.value })
}
</script>

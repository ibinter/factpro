<template>
  <AppLayout title="Analytics — Documents">
    <div class="p-6 space-y-6">

      <!-- Header -->
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Analytics Documents</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Performance documentaire — CA, conversion et recouvrement</p>
        </div>
        <a href="/analytics" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
          ← Retour au tableau de bord
        </a>
      </div>

      <!-- KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- CA mois courant -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">CA mois courant</p>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatMoney(caCurrentMonth) }}</p>
          <p class="text-xs text-gray-400 mt-1">Factures payées ce mois</p>
        </div>

        <!-- Taux de conversion -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Taux conversion devis</p>
          <p class="text-2xl font-bold" :class="conversionRate >= 50 ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400'">
            {{ conversionRate }}%
          </p>
          <p class="text-xs text-gray-400 mt-1">{{ convertedThisMonth }} / {{ quotesThisMonth }} devis ce mois</p>
        </div>

        <!-- Délai moyen paiement -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
          <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Délai moyen paiement</p>
          <p class="text-2xl font-bold" :class="avgPaymentDelay <= 30 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
            {{ avgPaymentDelay > 0 ? avgPaymentDelay + ' j' : 'N/A' }}
          </p>
          <p class="text-xs text-gray-400 mt-1">Sur les 3 derniers mois</p>
        </div>
      </div>

      <!-- Charts row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Bar chart CA 12 mois -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
          <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">CA mensuel — 12 derniers mois</h2>
          <div class="overflow-x-auto">
            <svg :viewBox="`0 0 ${barChartWidth} 200`" xmlns="http://www.w3.org/2000/svg" class="w-full" style="min-width:480px">
              <!-- Grid lines -->
              <line v-for="n in 4" :key="n"
                :x1="BAR_PADDING_LEFT" :y1="barY(n / 4)"
                :x2="barChartWidth - 10" :y2="barY(n / 4)"
                stroke="#e5e7eb" stroke-width="1" />
              <!-- Y labels -->
              <text v-for="n in 4" :key="'yl'+n"
                :x="BAR_PADDING_LEFT - 4" :y="barY(n / 4) + 4"
                font-size="9" fill="#9ca3af" text-anchor="end">
                {{ formatShort(maxRevenue * (1 - n / 4)) }}
              </text>
              <!-- Bars -->
              <g v-for="(m, i) in monthlyRevenue" :key="m.month">
                <rect
                  :x="barX(i) + 4"
                  :y="barY(m.total / (maxRevenue || 1))"
                  :width="barWidth - 8"
                  :height="BAR_CHART_HEIGHT - barY(m.total / (maxRevenue || 1))"
                  rx="3"
                  :fill="m.total > 0 ? '#6366f1' : '#e5e7eb'"
                  class="transition-all duration-300"
                />
                <!-- Value label on top -->
                <text v-if="m.total > 0"
                  :x="barX(i) + barWidth / 2"
                  :y="barY(m.total / (maxRevenue || 1)) - 3"
                  font-size="8" fill="#6366f1" text-anchor="middle">
                  {{ formatShort(m.total) }}
                </text>
                <!-- Month label -->
                <text
                  :x="barX(i) + barWidth / 2"
                  :y="BAR_CHART_HEIGHT + 14"
                  font-size="8" fill="#9ca3af" text-anchor="middle">
                  {{ m.label.slice(0, 3) }}
                </text>
              </g>
            </svg>
          </div>
        </div>

        <!-- Donut statuts -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
          <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Factures par statut</h2>
          <div v-if="totalDocuments > 0" class="flex flex-col items-center gap-4">
            <svg viewBox="0 0 160 160" xmlns="http://www.w3.org/2000/svg" class="w-40 h-40">
              <g transform="translate(80,80)">
                <path v-for="(seg, i) in donutSegments" :key="i"
                  :d="seg.d"
                  :fill="seg.color"
                  class="transition-all duration-300"
                />
              </g>
              <!-- Center text -->
              <text x="80" y="76" text-anchor="middle" font-size="20" font-weight="bold" fill="#374151">{{ totalDocuments }}</text>
              <text x="80" y="92" text-anchor="middle" font-size="10" fill="#9ca3af">factures</text>
            </svg>
            <!-- Legend -->
            <div class="w-full space-y-1">
              <div v-for="seg in donutSegments" :key="seg.status" class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5">
                  <span class="inline-block w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{background: seg.color}"></span>
                  <span class="text-gray-600 dark:text-gray-300 capitalize">{{ seg.status }}</span>
                </div>
                <span class="text-gray-500 dark:text-gray-400 font-medium">{{ seg.count }}</span>
              </div>
            </div>
          </div>
          <div v-else class="flex items-center justify-center h-40 text-gray-400 text-sm">Aucune donnée</div>
        </div>

      </div>

      <!-- Top 5 clients -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Top 5 clients par chiffre d'affaires</h2>
        <div v-if="topClients.length > 0" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700">
                <th class="text-left py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">#</th>
                <th class="text-left py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Client</th>
                <th class="text-right py-2 pr-4 text-gray-500 dark:text-gray-400 font-medium">Factures</th>
                <th class="text-right py-2 text-gray-500 dark:text-gray-400 font-medium">CA total</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(client, i) in topClients" :key="client.id"
                class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
              >
                <td class="py-3 pr-4">
                  <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold"
                    :class="i === 0 ? 'bg-amber-100 text-amber-700' : i === 1 ? 'bg-gray-100 text-gray-600' : 'bg-orange-50 text-orange-600'">
                    {{ i + 1 }}
                  </span>
                </td>
                <td class="py-3 pr-4">
                  <span class="font-medium text-gray-900 dark:text-white">{{ client.name || 'Client inconnu' }}</span>
                </td>
                <td class="py-3 pr-4 text-right text-gray-500 dark:text-gray-400">{{ client.invoice_count }}</td>
                <td class="py-3 text-right">
                  <span class="font-semibold text-gray-900 dark:text-white">{{ formatMoney(client.total) }}</span>
                  <!-- Progress bar relative to #1 -->
                  <div class="mt-1 h-1 rounded-full bg-gray-100 dark:bg-gray-700 w-full">
                    <div class="h-1 rounded-full bg-indigo-500 transition-all duration-500"
                      :style="{width: (client.total / topClients[0].total * 100).toFixed(1) + '%'}"></div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="flex items-center justify-center h-24 text-gray-400 text-sm">Aucun client trouvé</div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  monthlyRevenue:     { type: Array,  default: () => [] },
  conversionRate:     { type: Number, default: 0 },
  quotesThisMonth:    { type: Number, default: 0 },
  convertedThisMonth: { type: Number, default: 0 },
  byStatus:           { type: Array,  default: () => [] },
  topClients:         { type: Array,  default: () => [] },
  caCurrentMonth:     { type: Number, default: 0 },
  avgPaymentDelay:    { type: Number, default: 0 },
})

// ── Bar chart constants ─────────────────────────────────────────────────────
const BAR_PADDING_LEFT = 36
const BAR_CHART_HEIGHT = 140

const barChartWidth = computed(() => BAR_PADDING_LEFT + props.monthlyRevenue.length * 52 + 10)
const barWidth      = computed(() => 52)
const maxRevenue    = computed(() => Math.max(...props.monthlyRevenue.map(m => m.total), 1))

function barX(i) { return BAR_PADDING_LEFT + i * barWidth.value }
function barY(ratio) { return BAR_CHART_HEIGHT * (1 - Math.min(ratio, 1)) }

// ── Donut chart ─────────────────────────────────────────────────────────────
const totalDocuments = computed(() => props.byStatus.reduce((s, r) => s + r.count, 0))

const donutSegments = computed(() => {
  const R = 70
  const r = 44
  let angle = -Math.PI / 2
  const total = totalDocuments.value || 1

  return props.byStatus.map(row => {
    const sweep = (row.count / total) * 2 * Math.PI
    const x1 = Math.cos(angle) * R
    const y1 = Math.sin(angle) * R
    const x2 = Math.cos(angle + sweep) * R
    const y2 = Math.sin(angle + sweep) * R
    const xi1 = Math.cos(angle) * r
    const yi1 = Math.sin(angle) * r
    const xi2 = Math.cos(angle + sweep) * r
    const yi2 = Math.sin(angle + sweep) * r
    const large = sweep > Math.PI ? 1 : 0

    const d = `M ${x1} ${y1} A ${R} ${R} 0 ${large} 1 ${x2} ${y2} L ${xi2} ${yi2} A ${r} ${r} 0 ${large} 0 ${xi1} ${yi1} Z`
    angle += sweep
    return { ...row, d }
  })
})

// ── Formatters ───────────────────────────────────────────────────────────────
function formatMoney(val) {
  if (!val) return '0 FCFA'
  if (val >= 1_000_000) return (val / 1_000_000).toFixed(1) + ' M FCFA'
  if (val >= 1_000)     return Math.round(val / 1_000) + ' k FCFA'
  return Math.round(val) + ' FCFA'
}

function formatShort(val) {
  if (!val || val <= 0) return ''
  if (val >= 1_000_000) return (val / 1_000_000).toFixed(1) + 'M'
  if (val >= 1_000)     return Math.round(val / 1_000) + 'k'
  return Math.round(val)
}
</script>

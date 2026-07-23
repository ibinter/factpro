<template>
  <div class="widget-card">
    <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Trésorerie (12 mois)</h3>
    <div v-if="loading" class="h-48 bg-gray-50 dark:bg-gray-700 rounded animate-pulse" />
    <svg v-else-if="data && data.labels.length" viewBox="0 0 580 170" class="w-full h-48">
      <line v-for="i in 4" :key="i" x1="0" :y1="i * 34" x2="580" :y2="i * 34" stroke="#f3f4f6" stroke-width="1" />
      <!-- Bars -->
      <g v-for="(label, i) in data.labels" :key="i">
        <rect
          :x="barX(i)" :y="barY(data.inflows[i])"
          :width="barW / 2 - 1" :height="barH(data.inflows[i])"
          fill="#1a56db" rx="2" opacity="0.85" />
        <rect
          :x="barX(i) + barW / 2" :y="barY(data.outflows[i])"
          :width="barW / 2 - 1" :height="barH(data.outflows[i])"
          fill="#ef4444" rx="2" opacity="0.7" />
        <text v-if="i % 2 === 0" :x="barX(i) + barW / 2" y="168" text-anchor="middle" font-size="8" fill="#9ca3af">{{ shortLabel(label) }}</text>
      </g>
    </svg>
    <p v-else class="text-center text-gray-400 py-12 text-sm">Aucune donnée.</p>
    <div class="flex gap-4 mt-2 text-xs text-gray-500">
      <span class="flex items-center gap-1"><span class="w-3 h-3 bg-blue-600 rounded inline-block"></span> Encaissements</span>
      <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-500 rounded inline-block"></span> Décaissements</span>
    </div>
  </div>
</template>

<script setup>
import { inject, computed } from 'vue'
import { useWidgetData } from './useWidgetData'

const period = inject('analyticsPeriod')
const { data, loading } = useWidgetData('cashflow', period)

const W = 580, H = 155

const barW = computed(() => {
  const n = data.value?.labels?.length || 12
  return W / n
})

function maxVal () {
  if (!data.value) return 1
  return Math.max(...data.value.inflows, ...data.value.outflows, 1)
}

function barX (i) { return i * barW.value + 2 }
function barY (v) { return H - (v / maxVal()) * H }
function barH (v) { return (v / maxVal()) * H }

function shortLabel (label) {
  return label.slice(0, 3)
}
</script>

<style scoped>
.widget-card { @apply bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-100 dark:border-gray-700; }
</style>

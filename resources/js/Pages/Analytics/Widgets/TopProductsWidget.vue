<template>
  <div class="widget-card">
    <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Top 10 Produits / Services</h3>
    <div v-if="loading" class="animate-pulse space-y-2">
      <div v-for="i in 5" :key="i" class="h-6 bg-gray-100 dark:bg-gray-700 rounded" />
    </div>
    <div v-else-if="data && data.labels.length" class="space-y-2">
      <div v-for="(label, i) in data.labels" :key="i" class="flex items-center gap-3">
        <span class="text-xs font-bold text-gray-400 w-4 text-right">{{ i + 1 }}</span>
        <div class="flex-1">
          <div class="flex justify-between text-xs mb-0.5">
            <span class="text-gray-700 dark:text-gray-300 truncate max-w-[160px]">{{ label }}</span>
            <span class="font-semibold">{{ fmt(data.values[i]) }}</span>
          </div>
          <div class="h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full">
            <div class="h-1.5 rounded-full" :style="{ width: pct(i) + '%', background: colors[i % colors.length] }" />
          </div>
        </div>
      </div>
    </div>
    <p v-else class="text-center text-gray-400 py-12 text-sm">Aucun produit sur la période.</p>
  </div>
</template>

<script setup>
import { inject } from 'vue'
import { useWidgetData } from './useWidgetData'

const period = inject('analyticsPeriod')
const { data, loading } = useWidgetData('top_products', period)

const colors = ['#1a56db','#7c3aed','#059669','#d97706','#dc2626','#0891b2','#9333ea','#16a34a','#ea580c','#6b7280']

function fmt (v) {
  return new Intl.NumberFormat('fr-FR', { notation: 'compact', maximumFractionDigits: 1 }).format(v ?? 0)
}
function pct (i) {
  if (!data.value?.values?.length) return 0
  const max = data.value.values[0] || 1
  return Math.round((data.value.values[i] / max) * 100)
}
</script>

<style scoped>
.widget-card { @apply bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-100 dark:border-gray-700; }
</style>

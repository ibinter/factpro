<template>
  <div class="widget-card">
    <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Statut des factures</h3>
    <div v-if="loading" class="animate-pulse h-48 bg-gray-50 dark:bg-gray-700 rounded" />
    <div v-else-if="data && data.labels.length" class="space-y-3">
      <div v-for="(label, i) in data.labels" :key="i" class="flex items-center gap-3">
        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{ background: data.colors[i] }" />
        <div class="flex-1">
          <div class="flex justify-between text-sm mb-0.5">
            <span class="text-gray-700 dark:text-gray-300 capitalize">{{ label }}</span>
            <span class="font-semibold">{{ data.values[i] }}</span>
          </div>
          <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
            <div class="h-2 rounded-full transition-all duration-500"
              :style="{ width: pct(data.values[i]) + '%', background: data.colors[i] }" />
          </div>
        </div>
      </div>
    </div>
    <p v-else class="text-center text-gray-400 py-12 text-sm">Aucune facture sur la période.</p>
  </div>
</template>

<script setup>
import { inject, computed } from 'vue'
import { useWidgetData } from './useWidgetData'

const period = inject('analyticsPeriod')
const { data, loading } = useWidgetData('invoice_status', period)

function pct (v) {
  if (!data.value) return 0
  const total = data.value.values.reduce((a, b) => a + b, 0)
  return total > 0 ? Math.round(v / total * 100) : 0
}
</script>

<style scoped>
.widget-card { @apply bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-100 dark:border-gray-700; }
</style>

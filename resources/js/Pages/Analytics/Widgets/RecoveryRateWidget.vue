<template>
  <div class="widget-card h-full">
    <h3 class="font-semibold text-gray-800 dark:text-white mb-4">Taux de recouvrement</h3>
    <div v-if="loading" class="animate-pulse space-y-3">
      <div class="h-32 bg-gray-100 dark:bg-gray-700 rounded-full w-32 mx-auto" />
    </div>
    <div v-else-if="data" class="flex flex-col items-center gap-4">
      <!-- Donut SVG -->
      <svg viewBox="0 0 120 120" class="w-32 h-32">
        <circle cx="60" cy="60" r="48" fill="none" stroke="#e5e7eb" stroke-width="12" />
        <circle cx="60" cy="60" r="48" fill="none" stroke="#1a56db" stroke-width="12"
          :stroke-dasharray="`${data.rate * 3.016} 301.6`"
          stroke-dashoffset="75.4" stroke-linecap="round" />
        <text x="60" y="64" text-anchor="middle" font-size="18" font-weight="700" fill="#1a56db">{{ data.rate }}%</text>
        <text x="60" y="76" text-anchor="middle" font-size="8" fill="#9ca3af">recouvré</text>
      </svg>
      <div class="w-full space-y-2 text-sm">
        <div class="flex justify-between">
          <span class="text-gray-500">Payées</span>
          <span class="font-semibold text-green-600">{{ data.paid }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">Total</span>
          <span class="font-semibold">{{ data.total }}</span>
        </div>
        <div v-if="data.overdue_amount > 0" class="flex justify-between">
          <span class="text-gray-500">En souffrance</span>
          <span class="font-semibold text-red-500">{{ fmt(data.overdue_amount) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { inject } from 'vue'
import { useWidgetData } from './useWidgetData'

const period = inject('analyticsPeriod')
const { data, loading } = useWidgetData('recovery_rate', period)

function fmt (v) {
  return new Intl.NumberFormat('fr-FR', { notation: 'compact', maximumFractionDigits: 1 }).format(v ?? 0)
}
</script>

<style scoped>
.widget-card { @apply bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-100 dark:border-gray-700; }
</style>

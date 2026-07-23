<template>
  <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
    <div v-if="loading" v-for="i in 5" :key="i" class="widget-card animate-pulse h-20 bg-gray-100 dark:bg-gray-700 rounded-xl" />
    <template v-else-if="data">
      <div class="widget-card">
        <p class="kpi-label">CA Mois courant</p>
        <p class="kpi-value">{{ fmt(data.current_month_revenue) }}</p>
        <p :class="['kpi-badge', data.growth_pct >= 0 ? 'badge-pos' : 'badge-neg']">
          {{ data.growth_pct >= 0 ? '+' : '' }}{{ data.growth_pct }}%
        </p>
      </div>
      <div class="widget-card">
        <p class="kpi-label">CA Mois précédent</p>
        <p class="kpi-value">{{ fmt(data.prev_month_revenue) }}</p>
      </div>
      <div class="widget-card">
        <p class="kpi-label">Factures (mois)</p>
        <p class="kpi-value">{{ data.invoice_count }}</p>
      </div>
      <div class="widget-card">
        <p class="kpi-label">Panier moyen</p>
        <p class="kpi-value">{{ fmt(data.avg_invoice_value) }}</p>
      </div>
      <div class="widget-card">
        <p class="kpi-label">Croissance</p>
        <p :class="['kpi-value', data.growth_pct >= 0 ? 'text-green-600' : 'text-red-500']">
          {{ data.growth_pct >= 0 ? '+' : '' }}{{ data.growth_pct }}%
        </p>
      </div>
    </template>
  </div>
</template>

<script setup>
import { inject, computed, toRef } from 'vue'
import { useWidgetData } from './useWidgetData'

const props  = defineProps({ period: String })
const period = inject('analyticsPeriod')
const { data, loading } = useWidgetData('kpi_summary', period)

function fmt (v) {
  return new Intl.NumberFormat('fr-FR', { notation: 'compact', maximumFractionDigits: 1 }).format(v ?? 0)
}
</script>

<style scoped>
.widget-card { @apply bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-100 dark:border-gray-700; }
.kpi-label   { @apply text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1; }
.kpi-value   { @apply text-2xl font-bold text-gray-900 dark:text-white; }
.kpi-badge   { @apply inline-block text-xs font-semibold px-2 py-0.5 rounded-full mt-1; }
.badge-pos   { @apply bg-green-100 text-green-700; }
.badge-neg   { @apply bg-red-100 text-red-600; }
</style>

<template>
  <div class="widget-card">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-semibold text-gray-800 dark:text-white">Chiffre d'affaires</h3>
      <label class="flex items-center gap-2 text-xs text-gray-500">
        <input type="checkbox" v-model="compare" @change="reload" class="rounded"> Comparer période préc.
      </label>
    </div>
    <div v-if="loading" class="h-48 bg-gray-50 dark:bg-gray-700 rounded animate-pulse" />
    <svg v-else-if="data && data.labels.length" viewBox="0 0 600 180" class="w-full h-48">
      <!-- Grid lines -->
      <line v-for="i in 4" :key="i" :x1="0" :y1="i * 36" :x2="600" :y2="i * 36" stroke="#e5e7eb" stroke-width="1" />
      <!-- Previous period area -->
      <polyline v-if="compare && data.previous.length"
        :points="svgPoints(data.previous)" fill="none" stroke="#93c5fd" stroke-width="1.5" stroke-dasharray="4,3" />
      <!-- Current period area -->
      <polygon :points="svgArea(data.current)" fill="#dbeafe" fill-opacity="0.5" />
      <polyline :points="svgPoints(data.current)" fill="none" stroke="#1a56db" stroke-width="2" />
      <!-- Dots -->
      <circle v-for="(v, i) in data.current" :key="i"
        :cx="xPos(i)" :cy="yPos(v)" r="3" fill="#1a56db" />
      <!-- X labels (every 5th) -->
      <text v-for="(label, i) in data.labels" :key="'l'+i"
        v-if="i % Math.ceil(data.labels.length / 8) === 0"
        :x="xPos(i)" y="175" text-anchor="middle" font-size="9" fill="#9ca3af">{{ label }}</text>
    </svg>
    <p v-else class="text-center text-gray-400 py-12 text-sm">Aucune donnée sur la période.</p>
    <div class="flex gap-4 mt-2 text-xs text-gray-500">
      <span class="flex items-center gap-1"><span class="w-6 h-0.5 bg-blue-600 inline-block"></span> Période actuelle</span>
      <span v-if="compare" class="flex items-center gap-1"><span class="w-6 h-0.5 bg-blue-300 inline-block border-dashed border-t border-blue-300"></span> Période précédente</span>
    </div>
  </div>
</template>

<script setup>
import { ref, inject } from 'vue'
import { useWidgetData } from './useWidgetData'

const period  = inject('analyticsPeriod')
const compare = ref(false)

const { data, loading, reload } = useWidgetData('revenue_chart', period)

const W = 600, H = 160

function maxVal (arr) {
  const m = Math.max(...arr, 1)
  return m
}

function xPos (i) {
  const n = data.value?.current?.length || 1
  return 10 + (i / (n - 1 || 1)) * (W - 20)
}

function yPos (v) {
  const m = maxVal([...(data.value?.current || []), ...(data.value?.previous || [])])
  return H - (v / m) * (H - 10) + 5
}

function svgPoints (arr) {
  return arr.map((v, i) => `${xPos(i)},${yPos(v)}`).join(' ')
}

function svgArea (arr) {
  if (!arr.length) return ''
  const n = arr.length
  const top = arr.map((v, i) => `${xPos(i)},${yPos(v)}`).join(' ')
  return `${xPos(0)},${H+5} ${top} ${xPos(n-1)},${H+5}`
}
</script>

<style scoped>
.widget-card { @apply bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-100 dark:border-gray-700; }
</style>

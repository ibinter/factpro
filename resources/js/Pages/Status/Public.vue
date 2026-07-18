<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm">
      <div class="max-w-4xl mx-auto px-4 py-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">FactPro Status</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Statut en temps réel des services</p>
        </div>
        <div :class="overallStatusClass" class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold">
          <span>{{ overallStatusIcon }}</span>
          <span>{{ overallStatusText }}</span>
        </div>
      </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8 space-y-8">

      <!-- Components Grid -->
      <section>
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Composants</h2>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow divide-y divide-gray-100 dark:divide-gray-700">
          <div
            v-for="comp in components"
            :key="comp.key"
            class="flex items-center justify-between px-5 py-4"
          >
            <span class="text-gray-800 dark:text-gray-100 font-medium">{{ comp.name }}</span>
            <span :class="componentStatusClass(comp.status)" class="flex items-center gap-1.5 text-sm font-medium px-3 py-1 rounded-full">
              <span class="w-2 h-2 rounded-full" :class="componentDotClass(comp.status)"></span>
              {{ componentStatusLabel(comp.status) }}
            </span>
          </div>
        </div>
      </section>

      <!-- Active Incidents -->
      <section v-if="incidents.length > 0">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Incidents actifs</h2>
        <div class="space-y-4">
          <div
            v-for="incident in incidents"
            :key="incident.id"
            class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 border-l-4"
            :class="incidentBorderClass(incident.severity)"
          >
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <span class="font-semibold text-gray-900 dark:text-white">{{ incident.title }}</span>
                  <span :class="severityBadgeClass(incident.severity)" class="text-xs px-2 py-0.5 rounded-full font-medium uppercase">
                    {{ incident.severity }}
                  </span>
                  <span class="text-xs text-gray-500 px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded-full">
                    {{ incident.status }}
                  </span>
                </div>
                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2">{{ incident.description }}</p>
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="comp in (incident.affected_components || [])"
                    :key="comp"
                    class="text-xs bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 px-2 py-0.5 rounded"
                  >{{ comp }}</span>
                </div>
              </div>
              <span class="text-xs text-gray-400 whitespace-nowrap">{{ timeAgo(incident.started_at) }}</span>
            </div>
          </div>
        </div>
      </section>

      <!-- Resolved Incidents History -->
      <section v-if="resolved.length > 0">
        <button
          @click="historyOpen = !historyOpen"
          class="w-full flex items-center justify-between text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4"
        >
          <span>Historique des incidents résolus</span>
          <svg :class="historyOpen ? 'rotate-180' : ''" class="w-5 h-5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>
        <div v-if="historyOpen" class="space-y-3">
          <div
            v-for="inc in resolved"
            :key="inc.id"
            class="bg-white dark:bg-gray-800 rounded-xl shadow px-5 py-4 flex items-center justify-between"
          >
            <div>
              <div class="flex items-center gap-2">
                <span class="text-green-500">✅</span>
                <span class="font-medium text-gray-900 dark:text-white text-sm">{{ inc.title }}</span>
              </div>
              <span class="text-xs text-gray-400 mt-0.5 block">
                {{ formatDate(inc.started_at) }}
                <template v-if="inc.resolved_at">
                  → {{ formatDate(inc.resolved_at) }}
                  ({{ duration(inc.started_at, inc.resolved_at) }})
                </template>
              </span>
            </div>
            <span class="text-xs text-green-600 dark:text-green-400 font-semibold">Résolu</span>
          </div>
        </div>
      </section>

    </main>

    <!-- Footer -->
    <footer class="max-w-4xl mx-auto px-4 py-8 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-gray-400">
      <span>Mis à jour toutes les 60 secondes</span>
      <div class="flex items-center gap-4">
        <a href="mailto:support@factpro.ibigsoft.com?subject=Abonnement+statut" class="hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
          S'abonner aux mises à jour
        </a>
        <span>Propulsé par <span class="font-semibold text-gray-600 dark:text-gray-300">FactPro</span></span>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  incidents: { type: Array, default: () => [] },
  resolved:  { type: Array, default: () => [] },
  components: { type: Array, default: () => [] },
})

const historyOpen = ref(false)
let refreshInterval = null

const overallOperational = computed(() =>
  props.incidents.length === 0
)

const overallStatusIcon = computed(() => overallOperational.value ? '🟢' : '🔴')
const overallStatusText = computed(() =>
  overallOperational.value ? 'Tous les systèmes opérationnels' : 'Incident en cours'
)
const overallStatusClass = computed(() =>
  overallOperational.value
    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
    : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
)

function componentStatusLabel(status) {
  return {
    operational:    'Opérationnel',
    degraded:       'Dégradé',
    partial_outage: 'Interruption partielle',
    major_outage:   'Interruption majeure',
  }[status] ?? status
}

function componentStatusClass(status) {
  return {
    operational:    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    degraded:       'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    partial_outage: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    major_outage:   'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
  }[status] ?? 'bg-gray-100 text-gray-600'
}

function componentDotClass(status) {
  return {
    operational:    'bg-green-500',
    degraded:       'bg-yellow-500',
    partial_outage: 'bg-orange-500',
    major_outage:   'bg-red-500',
  }[status] ?? 'bg-gray-400'
}

function incidentBorderClass(severity) {
  return {
    critical: 'border-red-500',
    major:    'border-orange-400',
    minor:    'border-yellow-400',
  }[severity] ?? 'border-gray-300'
}

function severityBadgeClass(severity) {
  return {
    critical: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    major:    'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    minor:    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
  }[severity] ?? 'bg-gray-100 text-gray-600'
}

function timeAgo(dateStr) {
  const diff = Math.floor((Date.now() - new Date(dateStr)) / 60000)
  if (diff < 1)  return 'à l\'instant'
  if (diff < 60) return `Il y a ${diff} min`
  const h = Math.floor(diff / 60)
  if (h < 24) return `Il y a ${h}h`
  return `Il y a ${Math.floor(h / 24)}j`
}

function formatDate(dateStr) {
  return new Date(dateStr).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' })
}

function duration(startStr, endStr) {
  const mins = Math.floor((new Date(endStr) - new Date(startStr)) / 60000)
  if (mins < 60) return `${mins} min`
  return `${Math.floor(mins / 60)}h${mins % 60 > 0 ? mins % 60 + 'min' : ''}`
}

async function refresh() {
  try {
    const res = await fetch('/status.json')
    if (!res.ok) return
    // Page will reload on next Inertia visit; for now data auto-refreshes via full reload
    window.location.reload()
  } catch { /* ignore */ }
}

onMounted(() => {
  refreshInterval = setInterval(refresh, 60000)
})

onUnmounted(() => {
  clearInterval(refreshInterval)
})
</script>

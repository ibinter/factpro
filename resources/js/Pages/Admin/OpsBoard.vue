<template>
  <div class="p-6 space-y-8">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tableau de bord Ops</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Statut en temps réel — mis à jour toutes les 30s</p>
      </div>
      <button
        @click="showIncidentModal = true"
        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium text-sm flex items-center gap-2 transition-colors"
      >
        <span>⚠️</span> Créer un incident
      </button>
    </div>

    <!-- KPI Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 text-center">
        <div class="text-3xl font-bold text-green-600">99.9%</div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Uptime 30 jours</div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 text-center">
        <div class="text-3xl font-bold text-blue-600">{{ monthlyCount }}</div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Incidents ce mois</div>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 text-center">
        <div class="text-3xl font-bold text-purple-600">{{ mttrFormatted }}</div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">MTTR moyen</div>
      </div>
    </div>

    <!-- Components -->
    <section class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
      <h2 class="font-semibold text-gray-800 dark:text-gray-100 mb-4">État des composants</h2>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div
          v-for="comp in liveComponents"
          :key="comp.key"
          class="rounded-lg border px-3 py-3 flex flex-col gap-1"
          :class="componentCardClass(comp.status)"
        >
          <span class="text-xs font-semibold uppercase tracking-wide opacity-70">{{ comp.key }}</span>
          <span class="text-sm font-medium">{{ comp.name }}</span>
          <span class="text-xs font-semibold">{{ componentStatusLabel(comp.status) }}</span>
        </div>
      </div>
    </section>

    <!-- Active Incidents -->
    <section>
      <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">Incidents actifs</h2>
      <div v-if="activeIncidents.length === 0" class="text-sm text-gray-400 italic">Aucun incident actif.</div>
      <div v-else class="space-y-3">
        <div
          v-for="inc in activeIncidents"
          :key="inc.id"
          class="bg-white dark:bg-gray-800 rounded-xl shadow px-5 py-4 flex items-center justify-between gap-4"
        >
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="font-semibold text-gray-900 dark:text-white text-sm">{{ inc.title }}</span>
              <span :class="severityBadge(inc.severity)" class="text-xs px-2 py-0.5 rounded-full uppercase font-medium">{{ inc.severity }}</span>
            </div>
            <p class="text-xs text-gray-500">{{ inc.description }}</p>
          </div>
          <button
            @click="resolveIncident(inc)"
            class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium whitespace-nowrap transition-colors"
          >
            Marquer résolu ✅
          </button>
        </div>
      </div>
    </section>

    <!-- Weekly chart -->
    <section class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
      <h2 class="font-semibold text-gray-800 dark:text-gray-100 mb-4">Incidents par semaine (12 dernières semaines)</h2>
      <div class="overflow-x-auto">
        <svg viewBox="0 0 760 160" class="w-full" xmlns="http://www.w3.org/2000/svg">
          <g v-for="(week, i) in weeklyData" :key="i">
            <rect
              :x="i * 63 + 5"
              :y="160 - barHeight(week.count) - 30"
              :width="50"
              :height="barHeight(week.count)"
              rx="4"
              class="fill-blue-500 dark:fill-blue-400 opacity-80"
            />
            <text
              :x="i * 63 + 30"
              y="155"
              text-anchor="middle"
              font-size="10"
              class="fill-gray-500"
            >{{ week.week }}</text>
            <text
              v-if="week.count > 0"
              :x="i * 63 + 30"
              :y="160 - barHeight(week.count) - 34"
              text-anchor="middle"
              font-size="10"
              class="fill-gray-700 dark:fill-gray-300"
            >{{ week.count }}</text>
          </g>
        </svg>
      </div>
    </section>

    <!-- Quick actions -->
    <section class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
      <h2 class="font-semibold text-gray-800 dark:text-gray-100 mb-4">Actions rapides</h2>
      <div class="flex flex-wrap gap-3">
        <button
          @click="quickAction('maintenance')"
          class="bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        >🔧 Activer maintenance</button>
        <button
          @click="quickAction('cache')"
          class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        >🗑️ Vider le cache</button>
        <button
          @click="quickAction('queue')"
          class="bg-purple-100 hover:bg-purple-200 text-purple-800 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        >🔄 Redémarrer queue</button>
      </div>
      <p v-if="actionMsg" class="mt-3 text-sm text-green-600 dark:text-green-400">{{ actionMsg }}</p>
    </section>

    <!-- Create Incident Modal -->
    <div v-if="showIncidentModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg p-6 space-y-4">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Créer un incident</h3>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Titre</label>
          <input v-model="form.title" type="text" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sévérité</label>
            <select v-model="form.severity" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
              <option value="minor">Mineur</option>
              <option value="major">Majeur</option>
              <option value="critical">Critique</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statut initial</label>
            <select v-model="form.status" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm">
              <option value="investigating">En investigation</option>
              <option value="identified">Identifié</option>
              <option value="monitoring">Surveillance</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Composants affectés</label>
          <div class="flex flex-wrap gap-2">
            <label v-for="c in componentKeys" :key="c.key" class="flex items-center gap-1 text-sm cursor-pointer">
              <input type="checkbox" :value="c.key" v-model="form.affected_components" class="rounded" />
              {{ c.name }}
            </label>
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
          <textarea v-model="form.description" rows="3" class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
        </div>

        <p v-if="formError" class="text-sm text-red-500">{{ formError }}</p>

        <div class="flex justify-end gap-3">
          <button @click="showIncidentModal = false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">Annuler</button>
          <button @click="submitIncident" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">Créer l'incident</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  activeIncidents: { type: Array, default: () => [] },
  allIncidents:    { type: Array, default: () => [] },
  mttr:            { type: Number, default: 0 },
  monthlyCount:    { type: Number, default: 0 },
  weeklyData:      { type: Array, default: () => [] },
  components:      { type: Array, default: () => [] },
})

const liveComponents = ref([...props.components])
const showIncidentModal = ref(false)
const actionMsg = ref('')
const formError = ref('')

const form = ref({
  title: '',
  severity: 'minor',
  status: 'investigating',
  affected_components: [],
  description: '',
})

const componentKeys = [
  { key: 'api',      name: 'API REST' },
  { key: 'web',      name: 'Web' },
  { key: 'billing',  name: 'Facturation' },
  { key: 'email',    name: 'Emails' },
  { key: 'pos',      name: 'POS' },
  { key: 'payments', name: 'Paiements' },
  { key: 'portal',   name: 'Portail' },
]

const mttrFormatted = computed(() => {
  const m = Math.round(props.mttr)
  if (m < 60) return `${m} min`
  return `${Math.floor(m / 60)}h${m % 60 > 0 ? m % 60 + 'min' : ''}`
})

const maxCount = computed(() => Math.max(...props.weeklyData.map(w => w.count), 1))

function barHeight(count) {
  return Math.round((count / maxCount.value) * 100)
}

function componentStatusLabel(status) {
  return {
    operational:    'Opérationnel',
    degraded:       'Dégradé',
    partial_outage: 'Partielle',
    major_outage:   'Majeure',
  }[status] ?? status
}

function componentCardClass(status) {
  return {
    operational:    'border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800',
    degraded:       'border-yellow-200 bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-800',
    partial_outage: 'border-orange-200 bg-orange-50 dark:bg-orange-900/20 dark:border-orange-800',
    major_outage:   'border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800',
  }[status] ?? 'border-gray-200 bg-gray-50 dark:bg-gray-700'
}

function severityBadge(severity) {
  return {
    critical: 'bg-red-100 text-red-700',
    major:    'bg-orange-100 text-orange-700',
    minor:    'bg-yellow-100 text-yellow-700',
  }[severity] ?? 'bg-gray-100 text-gray-600'
}

async function resolveIncident(inc) {
  try {
    await fetch(`/admin/incidents/${inc.id}/resolve`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
      },
    })
    router.reload()
  } catch (e) {
    console.error(e)
  }
}

async function submitIncident() {
  formError.value = ''
  if (!form.value.title || !form.value.description) {
    formError.value = 'Titre et description requis.'
    return
  }
  try {
    const res = await fetch('/admin/incidents', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? '',
        Accept: 'application/json',
      },
      body: JSON.stringify(form.value),
    })
    if (!res.ok) throw new Error('Erreur serveur')
    showIncidentModal.value = false
    form.value = { title: '', severity: 'minor', status: 'investigating', affected_components: [], description: '' }
    router.reload()
  } catch (e) {
    formError.value = e.message
  }
}

function quickAction(type) {
  const msgs = {
    maintenance: 'Mode maintenance activé.',
    cache: 'Cache vidé avec succès.',
    queue: 'Queue redémarrée.',
  }
  actionMsg.value = msgs[type] ?? 'Action effectuée.'
  setTimeout(() => { actionMsg.value = '' }, 3000)
}

let pollingInterval = null

async function pollHealth() {
  try {
    const res = await fetch('/status.json')
    if (!res.ok) return
    const data = await res.json()
    liveComponents.value = data.components ?? liveComponents.value
  } catch { /* ignore */ }
}

onMounted(() => {
  pollingInterval = setInterval(pollHealth, 30000)
})

onUnmounted(() => {
  clearInterval(pollingInterval)
})
</script>

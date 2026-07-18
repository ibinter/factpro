<template>
  <AppLayout :title="`Jalons — ${project.name}`">
    <div class="max-w-5xl mx-auto px-4 py-8 space-y-8">

      <!-- En-tête projet -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ project.name }}</h1>
          <p v-if="project.customer" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Client : {{ project.customer.name }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <span :class="statusClass(project.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
            {{ statusLabel(project.status) }}
          </span>
          <button @click="showAddModal = true"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
            + Ajouter un jalon
          </button>
        </div>
      </div>

      <!-- Alerte budget dépassé -->
      <div v-if="budgetStatus.over_budget"
        class="flex items-start gap-3 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
        <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
          <p class="font-semibold text-red-700 dark:text-red-400">Budget dépassé</p>
          <p class="text-sm text-red-600 dark:text-red-300 mt-0.5">Ce projet a dépassé son budget alloué.</p>
        </div>
      </div>

      <!-- Jauges budget -->
      <div v-if="project.budget_hours || project.budget_amount" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Jauge heures -->
        <div v-if="project.budget_hours" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <p class="text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Heures consommées</p>
          <div class="relative h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500"
              :class="gaugeColor(budgetStatus.hours_pct)"
              :style="{ width: `${Math.min(100, budgetStatus.hours_pct || 0)}%` }">
            </div>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            {{ budgetStatus.hours_logged }}h / {{ project.budget_hours }}h
            <span v-if="budgetStatus.hours_pct !== null" class="font-semibold ml-1">({{ budgetStatus.hours_pct }}%)</span>
          </p>
        </div>

        <!-- Jauge montant -->
        <div v-if="project.budget_amount" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
          <p class="text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">Montant facturé</p>
          <div class="relative h-3 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500"
              :class="gaugeColor(budgetStatus.amount_pct)"
              :style="{ width: `${Math.min(100, budgetStatus.amount_pct || 0)}%` }">
            </div>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
            {{ fmt(budgetStatus.amount_billed) }} / {{ fmt(project.budget_amount) }} {{ project.currency }}
            <span v-if="budgetStatus.amount_pct !== null" class="font-semibold ml-1">({{ budgetStatus.amount_pct }}%)</span>
          </p>
        </div>
      </div>

      <!-- Avancement global -->
      <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between mb-2">
          <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Avancement global</p>
          <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ completionPct }}%</span>
        </div>
        <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
          <div class="h-full bg-blue-500 rounded-full transition-all duration-500"
            :style="{ width: `${completionPct}%` }">
          </div>
        </div>
      </div>

      <!-- Timeline jalons -->
      <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Jalons</h2>

        <div v-if="milestones.length === 0" class="text-center py-12 text-gray-400 dark:text-gray-500">
          Aucun jalon défini. Ajoutez le premier jalon de ce projet.
        </div>

        <div v-else class="relative">
          <!-- Ligne verticale -->
          <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

          <div class="space-y-6">
            <div v-for="m in milestones" :key="m.id" class="relative pl-12">
              <!-- Cercle statut -->
              <div class="absolute left-0 w-9 h-9 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-900"
                :class="milestoneCircleClass(m.status)">
                <svg v-if="m.status === 'invoiced'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <svg v-else-if="m.status === 'completed'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <svg v-else-if="m.status === 'in_progress'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div v-else class="w-2 h-2 rounded-full bg-white"></div>
              </div>

              <!-- Contenu jalon -->
              <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                <div class="flex items-start justify-between gap-4 flex-wrap">
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                      <h3 class="font-semibold text-gray-900 dark:text-white">{{ m.name }}</h3>
                      <span :class="milestoneBadgeClass(m.status)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium">
                        {{ milestoneStatusLabel(m.status) }}
                      </span>
                      <!-- Badge Facturé -->
                      <a v-if="m.status === 'invoiced' && m.document_id"
                        :href="route('documents.show', m.document_id)"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 hover:underline">
                        ✓ Facturé {{ m.document?.number }}
                      </a>
                    </div>
                    <p v-if="m.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ m.description }}</p>
                    <p v-if="m.due_date" class="text-xs text-gray-400 dark:text-gray-500 mt-1">Échéance : {{ m.due_date }}</p>
                  </div>
                  <div class="text-right shrink-0">
                    <p v-if="m.billing_amount" class="text-base font-bold text-gray-900 dark:text-white">
                      {{ fmt(m.billing_amount) }} {{ project.currency }}
                    </p>
                    <p v-else class="text-sm text-gray-400">— montant non défini</p>
                  </div>
                </div>

                <!-- Barre de complétion -->
                <div class="mt-3">
                  <div class="flex items-center gap-3">
                    <input type="range" min="0" max="100" :value="m.completion_pct"
                      class="flex-1 h-1.5 appearance-none bg-gray-200 dark:bg-gray-700 rounded-full cursor-pointer accent-blue-600"
                      :disabled="m.status === 'invoiced'"
                      @change="updateCompletion(m, $event.target.value)" />
                    <span class="text-xs font-mono text-gray-600 dark:text-gray-300 w-8 text-right">{{ m.completion_pct }}%</span>
                  </div>
                </div>

                <!-- Actions -->
                <div class="mt-3 flex items-center gap-2 flex-wrap">
                  <!-- Bouton Facturer -->
                  <button v-if="m.status === 'completed' && m.billing_amount"
                    @click="billMilestone(m)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-medium transition-colors">
                    Facturer ce jalon
                  </button>
                  <!-- Bouton Modifier -->
                  <button @click="openEditModal(m)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-medium transition-colors">
                    Modifier
                  </button>
                  <!-- Bouton Supprimer -->
                  <button @click="deleteMilestone(m)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 rounded-lg text-xs font-medium transition-colors">
                    Supprimer
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Ajouter / Modifier jalon -->
    <teleport to="body">
      <div v-if="showAddModal || editingMilestone"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-5">
            {{ editingMilestone ? 'Modifier le jalon' : 'Nouveau jalon' }}
          </h3>
          <form @submit.prevent="submitMilestone" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
              <input v-model="form.name" type="text" required maxlength="150"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
              <textarea v-model="form.description" rows="2"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Échéance</label>
                <input v-model="form.due_date" type="date"
                  class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Montant ({{ project.currency }})</label>
                <input v-model="form.billing_amount" type="number" min="0" step="0.01"
                  class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Statut</label>
              <select v-model="form.status"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="pending">En attente</option>
                <option value="in_progress">En cours</option>
                <option value="completed">Terminé</option>
              </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
              <button type="button" @click="closeModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition-colors">
                Annuler
              </button>
              <button type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                {{ editingMilestone ? 'Enregistrer' : 'Créer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </teleport>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  project: Object,
  milestones: Array,
  budgetStatus: Object,
  completionPct: Number,
})

// Modal state
const showAddModal = ref(false)
const editingMilestone = ref(null)

const form = reactive({
  name: '',
  description: '',
  due_date: '',
  billing_amount: '',
  status: 'pending',
})

function openEditModal(m) {
  editingMilestone.value = m
  form.name = m.name
  form.description = m.description || ''
  form.due_date = m.due_date || ''
  form.billing_amount = m.billing_amount || ''
  form.status = m.status
}

function closeModal() {
  showAddModal.value = false
  editingMilestone.value = null
  Object.assign(form, { name: '', description: '', due_date: '', billing_amount: '', status: 'pending' })
}

function submitMilestone() {
  if (editingMilestone.value) {
    router.put(route('milestones.update', editingMilestone.value.id), { ...form }, {
      onSuccess: closeModal,
    })
  } else {
    router.post(route('projects.milestones.store', props.project.id), { ...form }, {
      onSuccess: closeModal,
    })
  }
}

function updateCompletion(m, value) {
  router.put(route('milestones.update', m.id), {
    completion_pct: parseInt(value),
  }, { preserveScroll: true })
}

function billMilestone(m) {
  if (confirm(`Facturer le jalon "${m.name}" ?`)) {
    router.post(route('milestones.bill', m.id))
  }
}

function deleteMilestone(m) {
  if (confirm(`Supprimer le jalon "${m.name}" ?`)) {
    router.delete(route('milestones.destroy', m.id), { preserveScroll: true })
  }
}

// Helpers d'affichage
function fmt(v) {
  if (v == null) return '—'
  return new Intl.NumberFormat('fr-FR').format(v)
}

function route(name, params) {
  return window.route ? window.route(name, params) : `#${name}`
}

function gaugeColor(pct) {
  if (pct == null) return 'bg-gray-300 dark:bg-gray-600'
  if (pct < 70) return 'bg-green-500'
  if (pct < 90) return 'bg-orange-400'
  return 'bg-red-500'
}

function statusClass(s) {
  const map = {
    active: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    paused: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    completed: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    archived: 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400',
  }
  return map[s] || 'bg-gray-100 text-gray-500'
}

function statusLabel(s) {
  return { active: 'Actif', paused: 'En pause', completed: 'Terminé', archived: 'Archivé' }[s] || s
}

function milestoneCircleClass(s) {
  return {
    pending: 'bg-gray-400 dark:bg-gray-600',
    in_progress: 'bg-blue-500',
    completed: 'bg-green-500',
    invoiced: 'bg-purple-600',
  }[s] || 'bg-gray-400'
}

function milestoneBadgeClass(s) {
  return {
    pending: 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
    in_progress: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    completed: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    invoiced: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
  }[s] || 'bg-gray-100 text-gray-600'
}

function milestoneStatusLabel(s) {
  return { pending: 'En attente', in_progress: 'En cours', completed: 'Terminé', invoiced: 'Facturé' }[s] || s
}
</script>

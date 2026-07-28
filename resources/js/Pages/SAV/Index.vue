<script setup>
import { ref, computed } from 'vue'
import { router, Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  repairs: Object,
  filters: Object,
})

const search = ref(props.filters?.search ?? '')
const statusFilter = ref(props.filters?.status ?? '')
const priorityFilter = ref(props.filters?.priority ?? '')

function applyFilters() {
  router.get(route('sav.repairs.index'), {
    search: search.value,
    status: statusFilter.value,
    priority: priorityFilter.value,
  }, { preserveState: true, replace: true })
}

const statusBadge = {
  received:      'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
  diagnosing:    'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
  waiting_parts: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
  repairing:     'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
  ready:         'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
  delivered:     'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
  cancelled:     'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
}

const statusLabels = {
  received: 'Reçu', diagnosing: 'Diagnostic', waiting_parts: 'Attente pièces',
  repairing: 'En réparation', ready: 'Prêt', delivered: 'Livré', cancelled: 'Annulé',
}

const priorityBadge = {
  low:    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
  normal: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
  high:   'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
  urgent: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
}

const priorityLabels = {
  low: 'Basse', normal: 'Normale', high: 'Haute', urgent: 'Urgente',
}

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('fr-FR')
}
</script>

<template>
  <Head title="SAV - Réparations" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">SAV — Réparations</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row gap-3 mb-6">
          <input
            v-model="search"
            @input="applyFilters"
            type="text"
            placeholder="Rechercher ticket, marque, client…"
            class="flex-1 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
          />

          <select
            v-model="statusFilter"
            @change="applyFilters"
            class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm"
          >
            <option value="">Tous les statuts</option>
            <option v-for="(label, key) in statusLabels" :key="key" :value="key">{{ label }}</option>
          </select>

          <select
            v-model="priorityFilter"
            @change="applyFilters"
            class="rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm"
          >
            <option value="">Toutes priorités</option>
            <option v-for="(label, key) in priorityLabels" :key="key" :value="key">{{ label }}</option>
          </select>

          <Link
            :href="route('sav.repairs.create')"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow-sm transition"
          >
            + Nouvelle réparation
          </Link>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900">
              <tr>
                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">N° Ticket</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Client</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Appareil</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Statut</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Priorité</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Reçu le</th>
                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Promis le</th>
                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Coût final</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-if="repairs.data.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">Aucune réparation trouvée.</td>
              </tr>
              <tr
                v-for="repair in repairs.data"
                :key="repair.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-750 transition"
              >
                <td class="px-4 py-3 font-mono">
                  <Link :href="route('sav.repairs.show', repair.id)" class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">
                    {{ repair.ticket_number }}
                  </Link>
                </td>
                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                  {{ repair.customer?.name ?? '—' }}
                </td>
                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                  <span v-if="repair.brand || repair.model_name">{{ [repair.brand, repair.model_name].filter(Boolean).join(' ') }}</span>
                  <span v-else class="text-gray-400">{{ repair.device_type }}</span>
                </td>
                <td class="px-4 py-3">
                  <span :class="['inline-flex px-2 py-0.5 rounded-full text-xs font-semibold', statusBadge[repair.status]]">
                    {{ statusLabels[repair.status] ?? repair.status }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span :class="['inline-flex px-2 py-0.5 rounded-full text-xs font-semibold', priorityBadge[repair.priority]]">
                    {{ priorityLabels[repair.priority] ?? repair.priority }}
                  </span>
                </td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ formatDate(repair.received_at) }}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ formatDate(repair.promised_at) }}</td>
                <td class="px-4 py-3 text-right text-gray-800 dark:text-gray-200 font-medium">
                  {{ repair.final_cost ? Number(repair.final_cost).toLocaleString('fr-FR', { minimumFractionDigits: 2 }) : '—' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="repairs.last_page > 1" class="mt-4 flex gap-2 justify-center">
          <Link
            v-for="link in repairs.links"
            :key="link.label"
            :href="link.url ?? '#'"
            :class="[
              'px-3 py-1 rounded text-sm border',
              link.active
                ? 'bg-indigo-600 text-white border-indigo-600'
                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700',
              !link.url ? 'opacity-40 pointer-events-none' : '',
            ]"
            v-html="link.label"
          />
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

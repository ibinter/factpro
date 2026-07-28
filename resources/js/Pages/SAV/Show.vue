<script setup>
import { ref, computed } from 'vue'
import { useForm, router, Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  repair: Object,
})

const statusBadge = {
  received:      'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
  diagnosing:    'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
  waiting_parts: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
  repairing:     'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
  ready:         'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
  delivered:     'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
  cancelled:     'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
}

const priorityBadge = {
  low:    'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
  normal: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
  high:   'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
  urgent: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
}

// Workflow transitions
const workflowNext = {
  received:      { label: 'Démarrer diagnostic', next: 'diagnosing' },
  diagnosing:    { label: 'Attente pièces', next: 'waiting_parts' },
  waiting_parts: { label: 'Démarrer réparation', next: 'repairing' },
  repairing:     { label: 'Marquer Prêt', next: 'ready' },
  ready:         { label: 'Marquer Livré', next: 'delivered' },
}

const diagnosisForm = useForm({
  diagnosis: props.repair.diagnosis ?? '',
  estimated_cost: props.repair.estimated_cost ?? '',
  final_cost: props.repair.final_cost ?? '',
  internal_notes: props.repair.internal_notes ?? '',
})

const editingDiagnosis = ref(false)

function saveDiagnosis() {
  diagnosisForm.patch(route('sav.repairs.update', props.repair.id), {
    onSuccess: () => { editingDiagnosis.value = false },
  })
}

function changeStatus(newStatus) {
  router.patch(route('sav.repairs.update', props.repair.id), { status: newStatus })
}

function cancelRepair() {
  if (confirm('Confirmer l\'annulation de cette réparation ?')) {
    router.patch(route('sav.repairs.update', props.repair.id), { status: 'cancelled' })
  }
}

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

function formatCurrency(val) {
  if (val === null || val === undefined || val === '') return '—'
  return Number(val).toLocaleString('fr-FR', { minimumFractionDigits: 2 })
}

const deviceTypeLabel = {
  smartphone: 'Smartphone', tablet: 'Tablette', computer: 'Ordinateur',
  printer: 'Imprimante', tv: 'Téléviseur', other: 'Autre',
}
</script>

<template>
  <Head :title="`Réparation ${repair.ticket_number}`" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center gap-3">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          Réparation — {{ repair.ticket_number }}
        </h2>
        <span :class="['inline-flex px-2.5 py-1 rounded-full text-xs font-bold', statusBadge[repair.status]]">
          {{ repair.status_label }}
        </span>
        <span :class="['inline-flex px-2.5 py-1 rounded-full text-xs font-bold', priorityBadge[repair.priority]]">
          {{ repair.priority_label }}
        </span>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <!-- Workflow buttons -->
        <div v-if="repair.status !== 'delivered' && repair.status !== 'cancelled'" class="flex flex-wrap gap-3">
          <button
            v-if="workflowNext[repair.status]"
            @click="changeStatus(workflowNext[repair.status].next)"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow-sm transition"
          >
            {{ workflowNext[repair.status].label }}
          </button>
          <button
            @click="cancelRepair"
            class="inline-flex items-center px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 dark:bg-red-900 dark:hover:bg-red-800 dark:text-red-200 text-sm font-medium rounded-md shadow-sm transition"
          >
            Annuler
          </button>
          <Link
            :href="route('sav.repairs.edit', repair.id)"
            class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md shadow-sm transition"
          >
            Modifier
          </Link>
        </div>

        <!-- Info grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- Appareil -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-4">Appareil</h3>
            <dl class="space-y-2 text-sm">
              <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Type</dt>
                <dd class="text-gray-800 dark:text-gray-200 font-medium">{{ deviceTypeLabel[repair.device_type] ?? repair.device_type }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Marque / Modèle</dt>
                <dd class="text-gray-800 dark:text-gray-200 font-medium">
                  {{ [repair.brand, repair.model_name].filter(Boolean).join(' ') || '—' }}
                </dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">N° de série</dt>
                <dd class="text-gray-800 dark:text-gray-200 font-mono">{{ repair.serial_number || '—' }}</dd>
              </div>
              <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Technicien</dt>
                <dd class="text-gray-800 dark:text-gray-200">{{ repair.technician_name || '—' }}</dd>
              </div>
              <div class="border-t border-gray-100 dark:border-gray-700 pt-2 mt-2">
                <dt class="text-gray-500 dark:text-gray-400 mb-1">Panne signalée</dt>
                <dd class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ repair.issue_description }}</dd>
              </div>
            </dl>
          </div>

          <!-- Client & dates -->
          <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-4">Client & Dates</h3>
            <dl class="space-y-2 text-sm">
              <div class="flex justify-between">
                <dt class="text-gray-500 dark:text-gray-400">Client</dt>
                <dd class="text-gray-800 dark:text-gray-200 font-medium">{{ repair.customer?.name ?? '—' }}</dd>
              </div>
              <div class="flex justify-between" v-if="repair.customer?.email">
                <dt class="text-gray-500 dark:text-gray-400">Email</dt>
                <dd class="text-gray-800 dark:text-gray-200">{{ repair.customer.email }}</dd>
              </div>
              <div class="flex justify-between" v-if="repair.customer?.phone">
                <dt class="text-gray-500 dark:text-gray-400">Téléphone</dt>
                <dd class="text-gray-800 dark:text-gray-200">{{ repair.customer.phone }}</dd>
              </div>
              <div class="border-t border-gray-100 dark:border-gray-700 pt-2 mt-2 space-y-2">
                <div class="flex justify-between">
                  <dt class="text-gray-500 dark:text-gray-400">Reçu le</dt>
                  <dd class="text-gray-800 dark:text-gray-200">{{ formatDate(repair.received_at) }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-gray-500 dark:text-gray-400">Promis le</dt>
                  <dd class="text-gray-800 dark:text-gray-200">{{ formatDate(repair.promised_at) }}</dd>
                </div>
                <div class="flex justify-between" v-if="repair.delivered_at">
                  <dt class="text-gray-500 dark:text-gray-400">Livré le</dt>
                  <dd class="text-green-600 dark:text-green-400 font-medium">{{ formatDate(repair.delivered_at) }}</dd>
                </div>
              </div>
              <div class="border-t border-gray-100 dark:border-gray-700 pt-2 mt-2 space-y-2">
                <div class="flex justify-between">
                  <dt class="text-gray-500 dark:text-gray-400">Coût estimé</dt>
                  <dd class="text-gray-800 dark:text-gray-200">{{ formatCurrency(repair.estimated_cost) }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-gray-500 dark:text-gray-400">Coût final</dt>
                  <dd class="text-gray-800 dark:text-gray-200 font-semibold">{{ formatCurrency(repair.final_cost) }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-gray-500 dark:text-gray-400">Acompte</dt>
                  <dd class="text-gray-800 dark:text-gray-200">{{ formatCurrency(repair.deposit_amount) }}</dd>
                </div>
              </div>
            </dl>
          </div>
        </div>

        <!-- Diagnostic -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Diagnostic & Notes internes</h3>
            <button
              v-if="!editingDiagnosis && repair.status !== 'delivered' && repair.status !== 'cancelled'"
              @click="editingDiagnosis = true"
              class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
            >
              Modifier
            </button>
          </div>

          <div v-if="!editingDiagnosis" class="space-y-3 text-sm">
            <div>
              <p class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide mb-1">Diagnostic</p>
              <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ repair.diagnosis || '—' }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wide mb-1">Notes internes</p>
              <p class="text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ repair.internal_notes || '—' }}</p>
            </div>
          </div>

          <form v-else @submit.prevent="saveDiagnosis" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Diagnostic</label>
              <textarea v-model="diagnosisForm.diagnosis" rows="3"
                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Coût estimé</label>
                <input v-model="diagnosisForm.estimated_cost" type="number" min="0" step="0.01"
                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Coût final</label>
                <input v-model="diagnosisForm.final_cost" type="number" min="0" step="0.01"
                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes internes</label>
              <textarea v-model="diagnosisForm.internal_notes" rows="2"
                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>
            <div class="flex gap-3">
              <button type="submit" :disabled="diagnosisForm.processing"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-medium rounded-md transition">
                Enregistrer
              </button>
              <button type="button" @click="editingDiagnosis = false"
                class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md transition">
                Annuler
              </button>
            </div>
          </form>
        </div>

        <!-- Pièces utilisées -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
          <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-4">Pièces utilisées</h3>
          <div v-if="repair.parts && repair.parts.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
              <thead>
                <tr>
                  <th class="py-2 pr-4 text-left text-gray-500 dark:text-gray-400 font-medium">Description</th>
                  <th class="py-2 pr-4 text-right text-gray-500 dark:text-gray-400 font-medium">Qté</th>
                  <th class="py-2 pr-4 text-right text-gray-500 dark:text-gray-400 font-medium">P.U.</th>
                  <th class="py-2 text-right text-gray-500 dark:text-gray-400 font-medium">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="part in repair.parts" :key="part.id">
                  <td class="py-2 pr-4 text-gray-800 dark:text-gray-200">
                    {{ part.description }}
                    <span v-if="part.product" class="text-gray-400 text-xs ml-1">({{ part.product.name }})</span>
                  </td>
                  <td class="py-2 pr-4 text-right text-gray-700 dark:text-gray-300">{{ part.quantity }}</td>
                  <td class="py-2 pr-4 text-right text-gray-700 dark:text-gray-300">{{ formatCurrency(part.unit_cost) }}</td>
                  <td class="py-2 text-right text-gray-800 dark:text-gray-200 font-medium">{{ formatCurrency(part.line_total) }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="py-2 pr-4 text-right text-gray-500 dark:text-gray-400 font-medium text-sm">Total pièces</td>
                  <td class="py-2 text-right font-bold text-gray-800 dark:text-gray-100">
                    {{ formatCurrency(repair.parts.reduce((s, p) => s + Number(p.line_total), 0)) }}
                  </td>
                </tr>
              </tfoot>
            </table>
          </div>
          <p v-else class="text-sm text-gray-400 dark:text-gray-500">Aucune pièce enregistrée pour cette réparation.</p>
        </div>

        <!-- Notes client -->
        <div v-if="repair.customer_notes" class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
          <h4 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-1">Notes client</h4>
          <p class="text-sm text-yellow-700 dark:text-yellow-300 whitespace-pre-wrap">{{ repair.customer_notes }}</p>
        </div>

        <!-- Back -->
        <div>
          <Link :href="route('sav.repairs.index')" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
            &larr; Retour à la liste
          </Link>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<template>
  <AuthenticatedLayout title="Webhooks entrants (Zapier / Make)">
    <div class="max-w-5xl mx-auto py-8 px-4">

      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-brand-900">Webhooks entrants</h1>
          <p class="text-gray-500 text-sm mt-1">Permettez à Zapier, Make et autres outils de déclencher des actions dans FactPro.</p>
        </div>
        <button @click="openCreateModal()" class="bg-brand-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-brand-700 transition">
          + Nouveau webhook
        </button>
      </div>

      <!-- Flash token modal -->
      <div v-if="newToken" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8">
          <div class="text-center mb-4">
            <div class="text-4xl mb-2">🔑</div>
            <h2 class="text-xl font-bold text-gray-900">Copiez votre token maintenant</h2>
            <p class="text-sm text-gray-500 mt-1">Ce token ne sera plus affiché après la fermeture de cette fenêtre.</p>
          </div>
          <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 font-mono text-sm break-all text-gray-800 mb-4">
            {{ newToken }}
          </div>
          <button @click="copyToken(newToken)" class="w-full mb-3 bg-brand-600 text-white py-2 rounded-lg font-semibold hover:bg-brand-700 transition">
            Copier le token
          </button>
          <button @click="newToken = null" class="w-full border border-gray-300 text-gray-600 py-2 rounded-lg hover:bg-gray-50 transition">
            J'ai copié, fermer
          </button>
          <p v-if="copied" class="text-green-600 text-xs text-center mt-2">Token copié dans le presse-papiers !</p>
        </div>
      </div>

      <!-- Webhooks list -->
      <div class="space-y-4 mb-10">
        <div v-if="webhooks.length === 0" class="text-gray-400 text-center py-12 bg-white rounded-xl border border-gray-200">
          Aucun webhook entrant configuré. Créez-en un pour commencer.
        </div>

        <div v-for="wh in webhooks" :key="wh.id" class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3 mb-2">
                <span :class="wh.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                      class="text-xs font-semibold px-2 py-0.5 rounded-full">
                  {{ wh.is_active ? 'Actif' : 'Inactif' }}
                </span>
                <span :class="sourceBadgeClass(wh.source)"
                      class="text-xs font-semibold px-2 py-0.5 rounded-full">
                  {{ sourceName(wh.source) }}
                </span>
                <span class="font-semibold text-gray-900">{{ wh.name }}</span>
              </div>
              <div class="flex flex-wrap gap-1 mb-2">
                <span v-for="action in (wh.allowed_actions || [])" :key="action"
                      class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded">
                  {{ action }}
                </span>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-xs text-gray-400">Token :</span>
                <span class="font-mono text-xs text-gray-500">{{ wh.token_preview }}</span>
              </div>
              <div class="flex gap-4 mt-2 text-xs text-gray-400">
                <span>{{ wh.calls_count }} appels</span>
                <span v-if="wh.last_called_at">Dernier : {{ formatDate(wh.last_called_at) }}</span>
              </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
              <button @click="confirmRegenerate(wh)"
                      class="text-sm border border-yellow-400 text-yellow-600 rounded px-3 py-1.5 hover:bg-yellow-50">
                Régénérer token
              </button>
              <button @click="confirmDelete(wh)"
                      class="text-sm border border-red-300 text-red-500 rounded px-3 py-1.5 hover:bg-red-50">
                Supprimer
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Guide d'intégration -->
      <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Guide d'intégration</h2>

        <div class="mb-4">
          <h3 class="text-sm font-semibold text-gray-700 mb-2">Exemple — Créer un client via curl (Zapier/Make)</h3>
          <pre class="bg-gray-900 text-green-300 text-xs rounded-lg p-4 overflow-x-auto">curl -X POST https://votre-domaine.com/api/zapier/customers \
  -H "Authorization: Bearer VOTRE_SECRET_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "company",
    "name": "Acme Corp",
    "email": "contact@acme.com",
    "country": "CI",
    "currency": "XOF"
  }'</pre>
        </div>

        <div class="mb-4">
          <h3 class="text-sm font-semibold text-gray-700 mb-2">Endpoints disponibles</h3>
          <div class="overflow-x-auto">
            <table class="text-xs w-full border border-gray-200 rounded-lg">
              <thead class="bg-gray-100">
                <tr>
                  <th class="text-left px-3 py-2">Méthode</th>
                  <th class="text-left px-3 py-2">Endpoint</th>
                  <th class="text-left px-3 py-2">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr class="border-t border-gray-200" v-for="ep in endpoints" :key="ep.path">
                  <td class="px-3 py-2 font-mono font-semibold" :class="ep.method === 'POST' ? 'text-blue-600' : 'text-green-600'">{{ ep.method }}</td>
                  <td class="px-3 py-2 font-mono text-gray-600">{{ ep.path }}</td>
                  <td class="px-3 py-2 text-gray-500">{{ ep.description }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Modal Création -->
      <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 z-40 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8">
          <h2 class="text-xl font-bold text-gray-900 mb-6">Nouveau webhook entrant</h2>

          <form @submit.prevent="submitCreate">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
              <input v-model="form.name" type="text" required maxlength="100"
                     placeholder="Mon Zap #1"
                     class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500 focus:border-transparent" />
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
              <select v-model="form.source"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-brand-500">
                <option value="zapier">Zapier</option>
                <option value="make">Make (ex-Integromat)</option>
                <option value="custom">Autre / Custom</option>
              </select>
            </div>

            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Actions autorisées</label>
              <div class="space-y-2">
                <label v-for="action in availableActions" :key="action.value" class="flex items-center gap-2 text-sm">
                  <input type="checkbox" :value="action.value" v-model="form.allowed_actions"
                         class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                  <span>{{ action.label }}</span>
                </label>
              </div>
            </div>

            <div class="flex gap-3">
              <button type="submit" :disabled="submitting"
                      class="flex-1 bg-brand-600 text-white py-2 rounded-lg font-semibold hover:bg-brand-700 transition disabled:opacity-50">
                {{ submitting ? 'Création...' : 'Créer le webhook' }}
              </button>
              <button type="button" @click="showCreateModal = false"
                      class="flex-1 border border-gray-300 text-gray-600 py-2 rounded-lg hover:bg-gray-50 transition">
                Annuler
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Confirm regenerate -->
      <div v-if="regenerateTarget" class="fixed inset-0 bg-black/50 z-40 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 text-center">
          <div class="text-4xl mb-3">⚠️</div>
          <h2 class="text-xl font-bold text-gray-900 mb-2">Régénérer le token ?</h2>
          <p class="text-sm text-gray-500 mb-6">
            L'ancien token sera immédiatement invalidé. Toutes les intégrations utilisant ce token cesseront de fonctionner.
          </p>
          <div class="flex gap-3">
            <button @click="doRegenerate()" class="flex-1 bg-yellow-500 text-white py-2 rounded-lg font-semibold hover:bg-yellow-600 transition">
              Régénérer
            </button>
            <button @click="regenerateTarget = null" class="flex-1 border border-gray-300 text-gray-600 py-2 rounded-lg hover:bg-gray-50">
              Annuler
            </button>
          </div>
        </div>
      </div>

      <!-- Confirm delete -->
      <div v-if="deleteTarget" class="fixed inset-0 bg-black/50 z-40 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 text-center">
          <div class="text-4xl mb-3">🗑️</div>
          <h2 class="text-xl font-bold text-gray-900 mb-2">Supprimer ce webhook ?</h2>
          <p class="text-sm text-gray-500 mb-6">
            Cette action est irréversible. Les intégrations utilisant ce token ne fonctionneront plus.
          </p>
          <div class="flex gap-3">
            <button @click="doDelete()" class="flex-1 bg-red-600 text-white py-2 rounded-lg font-semibold hover:bg-red-700 transition">
              Supprimer
            </button>
            <button @click="deleteTarget = null" class="flex-1 border border-gray-300 text-gray-600 py-2 rounded-lg hover:bg-gray-50">
              Annuler
            </button>
          </div>
        </div>
      </div>

    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  webhooks: Array,
})

const page = usePage()
const newToken = ref(null)
const copied = ref(false)
const showCreateModal = ref(false)
const submitting = ref(false)
const regenerateTarget = ref(null)
const deleteTarget = ref(null)

const availableActions = [
  { value: 'create_customer', label: 'Créer un client' },
  { value: 'create_document', label: 'Créer un document (devis/facture)' },
  { value: 'register_payment', label: 'Enregistrer un paiement' },
]

const endpoints = [
  { method: 'POST', path: '/api/zapier/customers', description: 'Créer un client' },
  { method: 'POST', path: '/api/zapier/documents', description: 'Créer un document' },
  { method: 'POST', path: '/api/zapier/payments', description: 'Enregistrer un paiement' },
  { method: 'GET', path: '/api/zapier/triggers/new-invoice', description: 'Polling nouvelles factures (Zapier)' },
  { method: 'GET', path: '/api/zapier/triggers/new-customer', description: 'Polling nouveaux clients (Zapier)' },
  { method: 'POST', path: '/api/make/customers', description: 'Créer un client (Make)' },
  { method: 'POST', path: '/api/make/documents', description: 'Créer un document (Make)' },
  { method: 'GET', path: '/api/make/triggers/invoices', description: 'Polling factures (Make)' },
]

const form = ref({
  name: '',
  source: 'zapier',
  allowed_actions: ['create_customer', 'create_document', 'register_payment'],
})

function openCreateModal() {
  form.value = { name: '', source: 'zapier', allowed_actions: ['create_customer', 'create_document', 'register_payment'] }
  showCreateModal.value = true
}

function submitCreate() {
  submitting.value = true
  router.post(route('incoming-webhooks.store'), form.value, {
    onSuccess: () => {
      showCreateModal.value = false
      submitting.value = false
      const flash = page.props.flash
      if (flash?.new_token) {
        newToken.value = flash.new_token
      }
    },
    onError: () => { submitting.value = false },
  })
}

function confirmRegenerate(wh) {
  regenerateTarget.value = wh
}

function doRegenerate() {
  router.post(route('incoming-webhooks.regenerate', regenerateTarget.value.id), {}, {
    onSuccess: () => {
      regenerateTarget.value = null
      const flash = page.props.flash
      if (flash?.new_token) {
        newToken.value = flash.new_token
      }
    },
  })
}

function confirmDelete(wh) {
  deleteTarget.value = wh
}

function doDelete() {
  router.delete(route('incoming-webhooks.destroy', deleteTarget.value.id), {
    onSuccess: () => { deleteTarget.value = null },
  })
}

function copyToken(token) {
  navigator.clipboard.writeText(token).then(() => {
    copied.value = true
    setTimeout(() => { copied.value = false }, 3000)
  })
}

function sourceName(source) {
  return { zapier: 'Zapier', make: 'Make', custom: 'Custom' }[source] ?? source
}

function sourceBadgeClass(source) {
  return {
    zapier: 'bg-orange-100 text-orange-700',
    make: 'bg-purple-100 text-purple-700',
    custom: 'bg-gray-100 text-gray-600',
  }[source] ?? 'bg-gray-100 text-gray-600'
}

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' })
}

// Detect flash token on page load (e.g. after redirect)
onMounted(() => {
  const flash = page.props.flash
  if (flash?.new_token) {
    newToken.value = flash.new_token
  }
})
</script>

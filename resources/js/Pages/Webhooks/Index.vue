<template>
  <AuthenticatedLayout title="Webhooks sortants">
    <div class="max-w-5xl mx-auto py-8 px-4">

      <!-- Upsell -->
      <div v-if="!hasAccess" class="rounded-xl border-2 border-brand-600 bg-white p-8 text-center shadow">
        <div class="text-5xl mb-4">🔗</div>
        <h2 class="text-2xl font-bold text-brand-900 mb-2">Webhooks sortants</h2>
        <p class="text-gray-600 mb-6">
          Recevez des notifications en temps réel dans vos outils (Zapier, Make, votre serveur…)
          lors des événements FactPro. Disponible dès le plan <strong>Business</strong>.
        </p>
        <a href="/billing" class="inline-block bg-brand-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-brand-700 transition">
          Passer au plan Business
        </a>
      </div>

      <template v-else>
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <h1 class="text-2xl font-bold text-brand-900">Webhooks sortants</h1>
            <p class="text-gray-500 text-sm mt-1">Notifications automatiques vers vos systèmes externes.</p>
          </div>
          <button @click="openModal()" class="bg-brand-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-brand-700 transition">
            + Ajouter un endpoint
          </button>
        </div>

        <!-- Flash -->
        <div v-if="$page.props.flash?.success" class="mb-4 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3">
          {{ $page.props.flash.success }}
        </div>

        <!-- Endpoints list -->
        <div class="space-y-4 mb-10">
          <div v-if="endpoints.length === 0" class="text-gray-400 text-center py-12">
            Aucun endpoint configuré. Ajoutez-en un pour commencer.
          </div>

          <div v-for="ep in endpoints" :key="ep.id" class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-1">
                  <span :class="ep.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                        class="text-xs font-semibold px-2 py-0.5 rounded-full">
                    {{ ep.is_active ? 'Actif' : 'Inactif' }}
                  </span>
                  <span class="font-mono text-sm text-brand-900 truncate">{{ ep.url }}</span>
                </div>
                <div class="flex flex-wrap gap-1 mb-2">
                  <span v-for="ev in ep.events" :key="ev"
                        class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded">
                    {{ ev }}
                  </span>
                </div>
                <!-- Secret masqué -->
                <div class="flex items-center gap-2 mt-2">
                  <span class="text-xs text-gray-400">Secret :</span>
                  <span class="font-mono text-xs text-gray-500">{{ showSecrets[ep.id] ? ep.secret : '••••••••••••••••••••' }}</span>
                  <button @click="toggleSecret(ep.id)" class="text-xs text-brand-600 hover:underline">
                    {{ showSecrets[ep.id] ? 'Masquer' : 'Voir' }}
                  </button>
                  <button @click="copySecret(ep.secret)" class="text-xs text-gray-500 hover:text-brand-600 border border-gray-300 rounded px-2 py-0.5">
                    Copier
                  </button>
                </div>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <button @click="sendTest(ep)" class="text-sm border border-gray-300 rounded px-3 py-1.5 hover:bg-gray-50">
                  Test
                </button>
                <button @click="openModal(ep)" class="text-sm border border-brand-600 text-brand-600 rounded px-3 py-1.5 hover:bg-brand-50">
                  Éditer
                </button>
                <button @click="deleteEndpoint(ep)" class="text-sm border border-red-300 text-red-500 rounded px-3 py-1.5 hover:bg-red-50">
                  Suppr.
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Livraisons récentes -->
        <div>
          <h2 class="text-lg font-semibold text-brand-900 mb-3">Livraisons récentes</h2>
          <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <div v-if="deliveries.length === 0" class="text-gray-400 text-center py-8">
              Aucune livraison enregistrée.
            </div>
            <table v-else class="w-full text-sm">
              <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                  <th class="px-4 py-2 text-left text-gray-600 font-medium">Événement</th>
                  <th class="px-4 py-2 text-left text-gray-600 font-medium">Status</th>
                  <th class="px-4 py-2 text-left text-gray-600 font-medium">Tentative</th>
                  <th class="px-4 py-2 text-left text-gray-600 font-medium">Date</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="d in deliveries" :key="d.id">
                  <td class="px-4 py-2 font-mono text-xs text-blue-700">{{ d.event }}</td>
                  <td class="px-4 py-2">
                    <span v-if="d.delivered_at" class="text-xs bg-green-100 text-green-700 rounded-full px-2 py-0.5">
                      {{ d.response_status }} OK
                    </span>
                    <span v-else class="text-xs bg-red-100 text-red-600 rounded-full px-2 py-0.5">
                      {{ d.response_status || 'Erreur' }}
                    </span>
                  </td>
                  <td class="px-4 py-2 text-gray-500">{{ d.attempt }}</td>
                  <td class="px-4 py-2 text-gray-400 text-xs">{{ formatDate(d.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>

    <!-- Modal ajout/édition -->
    <div v-if="modal.open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 p-6">
        <h3 class="text-lg font-bold text-brand-900 mb-4">
          {{ modal.endpoint ? 'Modifier l\'endpoint' : 'Ajouter un endpoint' }}
        </h3>

        <form @submit.prevent="submitModal">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">URL de destination *</label>
            <input v-model="form.url" type="url" placeholder="https://votre-serveur.com/webhook"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-600"
                   required />
            <p v-if="errors.url" class="text-red-500 text-xs mt-1">{{ errors.url }}</p>
          </div>

          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Événements *</label>
            <div class="space-y-2">
              <label v-for="ev in events_list" :key="ev" class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" :value="ev" v-model="form.events"
                       class="rounded border-gray-300 text-brand-600" />
                <span class="text-sm font-mono text-gray-700">{{ ev }}</span>
              </label>
            </div>
            <p v-if="errors.events" class="text-red-500 text-xs mt-1">{{ errors.events }}</p>
          </div>

          <div v-if="modal.endpoint" class="mb-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-brand-600" />
              <span class="text-sm text-gray-700">Endpoint actif</span>
            </label>
          </div>

          <div class="flex gap-3 justify-end">
            <button type="button" @click="modal.open = false"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
              Annuler
            </button>
            <button type="submit" :disabled="form.processing"
                    class="px-4 py-2 bg-brand-600 text-white rounded-lg text-sm font-semibold hover:bg-brand-700 disabled:opacity-50">
              {{ modal.endpoint ? 'Enregistrer' : 'Créer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  hasAccess: Boolean,
  endpoints: { type: Array, default: () => [] },
  events_list: { type: Array, default: () => [] },
  deliveries: { type: Array, default: () => [] },
})

const showSecrets = ref({})
const errors = ref({})

const modal = reactive({ open: false, endpoint: null })
const form = useForm({ url: '', events: [], is_active: true })

function toggleSecret(id) {
  showSecrets.value[id] = !showSecrets.value[id]
}

function copySecret(secret) {
  navigator.clipboard.writeText(secret).then(() => alert('Secret copié !'))
}

function openModal(ep = null) {
  modal.endpoint = ep
  errors.value = {}
  if (ep) {
    form.url = ep.url
    form.events = [...(ep.events || [])]
    form.is_active = ep.is_active
  } else {
    form.url = ''
    form.events = []
    form.is_active = true
  }
  modal.open = true
}

function submitModal() {
  errors.value = {}
  if (modal.endpoint) {
    form.put(route('outgoing-webhooks.update', modal.endpoint.id), {
      onSuccess: () => { modal.open = false },
      onError: (e) => { errors.value = e },
    })
  } else {
    form.post(route('outgoing-webhooks.store'), {
      onSuccess: () => { modal.open = false },
      onError: (e) => { errors.value = e },
    })
  }
}

function deleteEndpoint(ep) {
  if (!confirm('Supprimer cet endpoint ?')) return
  router.delete(route('outgoing-webhooks.destroy', ep.id))
}

function sendTest(ep) {
  router.post(route('outgoing-webhooks.test', ep.id))
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' })
}
</script>

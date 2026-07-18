<template>
  <AppLayout title="Réapprovisionnement automatique">
    <div class="max-w-7xl mx-auto px-4 py-6">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          Réapprovisionnement automatique
        </h1>
        <button
          v-if="activeTab === 'rules'"
          @click="openCreateModal"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <span>+ Créer une règle</span>
        </button>
      </div>

      <!-- Tabs -->
      <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-6">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          class="px-4 py-2 text-sm font-medium transition border-b-2 -mb-px"
          :class="activeTab === tab.key
            ? 'border-blue-600 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
        >
          {{ tab.label }}
          <span
            v-if="tab.badge"
            class="ml-1 px-1.5 py-0.5 text-xs rounded-full bg-red-100 text-red-700"
          >{{ tab.badge }}</span>
        </button>
      </div>

      <!-- ─── Tab: Alertes stock ──────────────────────────────────────── -->
      <div v-if="activeTab === 'alerts'">
        <div v-if="lowStock.length === 0" class="text-center py-16 text-gray-400">
          <p class="text-4xl mb-3">✅</p>
          <p>Tous les stocks sont au-dessus des seuils définis.</p>
        </div>
        <div v-else class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
              <tr>
                <th class="px-4 py-3 text-left">Produit</th>
                <th class="px-4 py-3 text-right">Stock actuel</th>
                <th class="px-4 py-3 text-right">Seuil</th>
                <th class="px-4 py-3 text-right">Qté à commander</th>
                <th class="px-4 py-3 text-left">Fournisseur</th>
                <th class="px-4 py-3 text-center">Statut</th>
                <th class="px-4 py-3 text-center">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr
                v-for="item in lowStock"
                :key="item.rule_id"
                class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800"
              >
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                  {{ item.product_name }}
                  <span v-if="item.sku" class="ml-1 text-xs text-gray-400">{{ item.sku }}</span>
                </td>
                <td class="px-4 py-3 text-right font-mono text-red-600 font-semibold">
                  {{ item.stock_quantity }}
                </td>
                <td class="px-4 py-3 text-right font-mono text-gray-500">{{ item.trigger_threshold }}</td>
                <td class="px-4 py-3 text-right font-mono">{{ item.order_quantity }}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ item.supplier_name ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                  <span
                    v-if="item.in_cooldown"
                    class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700"
                  >En attente</span>
                  <span v-else class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700">
                    Rupture imminente
                  </span>
                </td>
                <td class="px-4 py-3 text-center">
                  <button
                    :disabled="item.in_cooldown || loadingTrigger === item.rule_id"
                    @click="triggerRule(item.rule_id)"
                    class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition"
                  >
                    {{ loadingTrigger === item.rule_id ? '…' : 'Commander maintenant' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ─── Tab: Règles auto ───────────────────────────────────────── -->
      <div v-if="activeTab === 'rules'">
        <div v-if="rules.length === 0" class="text-center py-16 text-gray-400">
          <p class="text-4xl mb-3">⚙️</p>
          <p>Aucune règle configurée. Créez-en une pour commencer.</p>
        </div>
        <div v-else class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
              <tr>
                <th class="px-4 py-3 text-left">Produit</th>
                <th class="px-4 py-3 text-right">Seuil</th>
                <th class="px-4 py-3 text-right">Qté cmd</th>
                <th class="px-4 py-3 text-left">Fournisseur</th>
                <th class="px-4 py-3 text-center">Cooldown</th>
                <th class="px-4 py-3 text-center">Auto-approve</th>
                <th class="px-4 py-3 text-center">Actif</th>
                <th class="px-4 py-3 text-center">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr
                v-for="rule in rules"
                :key="rule.id"
                class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800"
              >
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                  {{ rule.product?.name }}
                </td>
                <td class="px-4 py-3 text-right font-mono">{{ rule.trigger_threshold }}</td>
                <td class="px-4 py-3 text-right font-mono">{{ rule.order_quantity }}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                  {{ rule.supplier?.name ?? '—' }}
                </td>
                <td class="px-4 py-3 text-center text-gray-500">{{ rule.cooldown_hours }}h</td>
                <td class="px-4 py-3 text-center">
                  <span
                    class="px-2 py-0.5 text-xs rounded-full"
                    :class="rule.auto_approve ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
                  >{{ rule.auto_approve ? 'Oui' : 'Non' }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <button
                    @click="toggleActive(rule)"
                    class="relative inline-flex h-5 w-9 items-center rounded-full transition"
                    :class="rule.is_active ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'"
                  >
                    <span
                      class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                      :class="rule.is_active ? 'translate-x-4' : 'translate-x-0.5'"
                    />
                  </button>
                </td>
                <td class="px-4 py-3 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <button
                      @click="openEditModal(rule)"
                      class="text-blue-600 hover:underline text-xs"
                    >Modifier</button>
                    <button
                      @click="deleteRule(rule)"
                      class="text-red-500 hover:underline text-xs"
                    >Suppr.</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ─── Tab: Historique BOC ───────────────────────────────────── -->
      <div v-if="activeTab === 'history'">
        <div v-if="historyLoading" class="text-center py-10 text-gray-400">Chargement…</div>
        <div v-else-if="historyItems.length === 0" class="text-center py-16 text-gray-400">
          <p class="text-4xl mb-3">📋</p>
          <p>Aucun BOC généré automatiquement pour l'instant.</p>
        </div>
        <div v-else class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
              <tr>
                <th class="px-4 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-left">Produit</th>
                <th class="px-4 py-3 text-right">Qté</th>
                <th class="px-4 py-3 text-left">Fournisseur</th>
                <th class="px-4 py-3 text-left">N° BOC</th>
                <th class="px-4 py-3 text-center">Statut</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr
                v-for="item in historyItems"
                :key="item.rule_id"
                class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800"
              >
                <td class="px-4 py-3 text-gray-500">
                  {{ item.last_triggered_at ? new Date(item.last_triggered_at).toLocaleDateString('fr') : '—' }}
                </td>
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ item.product_name }}</td>
                <td class="px-4 py-3 text-right font-mono">{{ item.order_quantity }}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ item.supplier_name ?? '—' }}</td>
                <td class="px-4 py-3">
                  <a
                    v-if="item.document_id"
                    :href="`/documents/${item.document_id}`"
                    class="text-blue-600 hover:underline font-mono"
                  >{{ item.document_number }}</a>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span
                    class="px-2 py-0.5 text-xs rounded-full"
                    :class="{
                      'bg-gray-100 text-gray-600': item.document_status === 'draft',
                      'bg-green-100 text-green-700': item.document_status === 'paid',
                      'bg-blue-100 text-blue-700': item.document_status === 'sent',
                    }"
                  >{{ item.document_status ?? '—' }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ─── Modal Création / Édition ──────────────────────────────── -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      @click.self="closeModal"
    >
      <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
            {{ editingRule ? 'Modifier la règle' : 'Créer une règle' }}
          </h2>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <form @submit.prevent="submitForm" class="px-6 py-5 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Produit *</label>
            <select v-model="form.product_id" required
              class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
              <option value="">— Sélectionner —</option>
              <option v-for="p in products" :key="p.id" :value="p.id">
                {{ p.name }} <template v-if="p.sku">({{ p.sku }})</template>
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fournisseur</label>
            <select v-model="form.supplier_id"
              class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white">
              <option :value="null">— Aucun —</option>
              <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seuil déclencheur *</label>
              <input type="number" v-model.number="form.trigger_threshold" min="0" required
                class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantité à commander *</label>
              <input type="number" v-model.number="form.order_quantity" min="1" required
                class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cooldown (heures)</label>
            <input type="number" v-model.number="form.cooldown_hours" min="1"
              class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" />
          </div>
          <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
              <input type="checkbox" v-model="form.is_active" class="rounded" />
              Règle active
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
              <input type="checkbox" v-model="form.auto_approve" class="rounded" />
              Finaliser automatiquement
            </label>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
            <textarea v-model="form.notes" rows="2"
              class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white resize-none" />
          </div>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" @click="closeModal"
              class="px-4 py-2 text-sm border rounded-lg dark:border-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
              Annuler
            </button>
            <button type="submit"
              class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
              {{ editingRule ? 'Mettre à jour' : 'Créer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  rules:     { type: Array, default: () => [] },
  lowStock:  { type: Array, default: () => [] },
  products:  { type: Array, default: () => [] },
  suppliers: { type: Array, default: () => [] },
})

// ─── Tabs ────────────────────────────────────────────────────────────────
const activeTab = ref('alerts')

const tabs = computed(() => [
  { key: 'alerts',  label: '⚠️ Alertes stock',  badge: props.lowStock.length || null },
  { key: 'rules',   label: '⚙️ Règles auto',     badge: null },
  { key: 'history', label: '📋 Historique BOC',  badge: null },
])

// ─── History ─────────────────────────────────────────────────────────────
const historyItems   = ref([])
const historyLoading = ref(false)
const historyLoaded  = ref(false)

watch(activeTab, (tab) => {
  if (tab === 'history' && !historyLoaded.value) {
    loadHistory()
  }
})

async function loadHistory() {
  historyLoading.value = true
  try {
    const resp = await fetch(route('stock.auto-reorder.history'), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    historyItems.value = await resp.json()
    historyLoaded.value = true
  } finally {
    historyLoading.value = false
  }
}

// ─── Trigger manual ──────────────────────────────────────────────────────
const loadingTrigger = ref(null)

async function triggerRule(ruleId) {
  if (!confirm('Créer un bon de commande maintenant ?')) return
  loadingTrigger.value = ruleId
  router.post(route('stock.auto-reorder.trigger', ruleId), {}, {
    onFinish: () => { loadingTrigger.value = null },
  })
}

// ─── Toggle active ───────────────────────────────────────────────────────
function toggleActive(rule) {
  router.put(route('stock.auto-reorder.update', rule.id), {
    is_active: !rule.is_active,
  }, { preserveScroll: true })
}

// ─── Delete ──────────────────────────────────────────────────────────────
function deleteRule(rule) {
  if (!confirm(`Supprimer la règle pour « ${rule.product?.name} » ?`)) return
  router.delete(route('stock.auto-reorder.destroy', rule.id), { preserveScroll: true })
}

// ─── Modal ───────────────────────────────────────────────────────────────
const showModal   = ref(false)
const editingRule = ref(null)

const emptyForm = () => ({
  product_id:        '',
  supplier_id:       null,
  trigger_threshold: 0,
  order_quantity:    1,
  cooldown_hours:    24,
  is_active:         true,
  auto_approve:      false,
  notes:             '',
})

const form = ref(emptyForm())

function openCreateModal() {
  editingRule.value = null
  form.value = emptyForm()
  showModal.value = true
}

function openEditModal(rule) {
  editingRule.value = rule
  form.value = {
    product_id:        rule.product_id,
    supplier_id:       rule.supplier_id,
    trigger_threshold: rule.trigger_threshold,
    order_quantity:    rule.order_quantity,
    cooldown_hours:    rule.cooldown_hours,
    is_active:         rule.is_active,
    auto_approve:      rule.auto_approve,
    notes:             rule.notes ?? '',
  }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingRule.value = null
}

function submitForm() {
  if (editingRule.value) {
    router.put(route('stock.auto-reorder.update', editingRule.value.id), form.value, {
      onSuccess: closeModal,
      preserveScroll: true,
    })
  } else {
    router.post(route('stock.auto-reorder.store'), form.value, {
      onSuccess: closeModal,
      preserveScroll: true,
    })
  }
}
</script>

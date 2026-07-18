<template>
  <AppLayout title="Programme Fidélité">
    <div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Programme Fidélité</h1>

      <!-- Tabs -->
      <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
        <nav class="-mb-px flex space-x-6">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-3 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === tab.id
                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
            ]"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <!-- Tab: Programme -->
      <div v-if="activeTab === 'programme'">
        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-lg p-5 shadow">
            <div class="text-sm text-gray-500 dark:text-gray-400">Clients membres</div>
            <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ stats.members_count }}</div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg p-5 shadow">
            <div class="text-sm text-gray-500 dark:text-gray-400">Points distribués ce mois</div>
            <div class="text-3xl font-bold text-green-600 dark:text-green-400 mt-1">{{ stats.points_this_month.toLocaleString() }}</div>
          </div>
          <div class="bg-white dark:bg-gray-800 rounded-lg p-5 shadow">
            <div class="text-sm text-gray-500 dark:text-gray-400">Récompenses échangées</div>
            <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ stats.redemptions_count }}</div>
          </div>
        </div>

        <!-- Monthly chart -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow">
          <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Points distribués par mois</h2>
          <div class="flex items-end space-x-2 h-40">
            <div
              v-for="m in monthlyPoints"
              :key="m.month"
              class="flex-1 flex flex-col items-center"
            >
              <div
                class="w-full bg-indigo-500 rounded-t"
                :style="{ height: barHeight(m.total) + 'px' }"
                :title="m.total + ' pts'"
              ></div>
              <div class="text-xs text-gray-500 mt-1">{{ m.month?.slice(5) }}</div>
            </div>
            <div v-if="monthlyPoints.length === 0" class="text-gray-400 text-sm">Aucune donnée</div>
          </div>
        </div>
      </div>

      <!-- Tab: Top Clients -->
      <div v-if="activeTab === 'clients'">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Client</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Niveau</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Points</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="(row, i) in topCustomers" :key="row.customer?.id" class="hover:bg-gray-50 dark:hover:bg-gray-750">
                <td class="px-6 py-4 text-sm text-gray-500">{{ i + 1 }}</td>
                <td class="px-6 py-4">
                  <div class="font-medium text-gray-900 dark:text-white">{{ row.customer?.name }}</div>
                  <div class="text-xs text-gray-400">{{ row.customer?.email }}</div>
                </td>
                <td class="px-6 py-4">
                  <span
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold"
                    :style="{ backgroundColor: row.level?.color + '22', color: row.level?.color }"
                  >
                    {{ row.level?.icon }} {{ row.level?.name }}
                  </span>
                </td>
                <td class="px-6 py-4 text-right font-bold text-indigo-600 dark:text-indigo-400">
                  {{ row.total_points.toLocaleString() }}
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                  <a
                    :href="`/loyalty/customers/${row.customer?.id}/card`"
                    target="_blank"
                    class="text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 px-2 py-1 rounded hover:bg-indigo-200"
                  >
                    Carte PDF
                  </a>
                </td>
              </tr>
              <tr v-if="topCustomers.length === 0">
                <td colspan="5" class="px-6 py-8 text-center text-gray-400">Aucun client fidèle encore</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Tab: Récompenses -->
      <div v-if="activeTab === 'recompenses'">
        <div class="flex justify-end mb-4">
          <button
            @click="showRewardForm = true"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition"
          >
            + Créer une récompense
          </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="reward in rewards"
            :key="reward.id"
            class="bg-white dark:bg-gray-800 rounded-lg p-5 shadow border border-gray-100 dark:border-gray-700"
          >
            <div class="flex justify-between items-start">
              <h3 class="font-semibold text-gray-900 dark:text-white">{{ reward.name }}</h3>
              <span :class="reward.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'" class="text-xs px-2 py-0.5 rounded-full">
                {{ reward.is_active ? 'Actif' : 'Inactif' }}
              </span>
            </div>
            <p v-if="reward.description" class="text-sm text-gray-500 mt-1">{{ reward.description }}</p>
            <div class="mt-3 flex items-center justify-between">
              <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ reward.points_cost }} pts</span>
              <span class="text-sm text-gray-600 dark:text-gray-300">
                {{ rewardTypeLabel(reward.reward_type) }} {{ reward.reward_value }}{{ reward.reward_type === 'discount_percent' ? '%' : ' XOF' }}
              </span>
            </div>
            <div v-if="reward.stock !== null" class="text-xs text-gray-400 mt-2">Stock : {{ reward.stock }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ reward.redemptions_count }} échanges</div>
          </div>
          <div v-if="rewards.length === 0" class="col-span-3 text-center py-12 text-gray-400">
            Aucune récompense créée. Cliquez sur "Créer une récompense" pour commencer.
          </div>
        </div>

        <!-- New reward modal -->
        <div v-if="showRewardForm" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md shadow-xl">
            <h2 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Nouvelle récompense</h2>
            <form @submit.prevent="submitReward">
              <div class="space-y-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom</label>
                  <input v-model="rewardForm.name" required class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Coût (points)</label>
                  <input v-model.number="rewardForm.points_cost" type="number" min="1" required class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                  <select v-model="rewardForm.reward_type" class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white">
                    <option value="discount_percent">Remise %</option>
                    <option value="discount_fixed">Remise fixe</option>
                    <option value="free_product">Produit gratuit</option>
                    <option value="gift">Cadeau</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Valeur</label>
                  <input v-model.number="rewardForm.reward_value" type="number" min="0" step="0.01" required class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white" />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock (vide = illimité)</label>
                  <input v-model.number="rewardForm.stock" type="number" min="0" class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white" />
                </div>
              </div>
              <div class="flex justify-end space-x-3 mt-5">
                <button type="button" @click="showRewardForm = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400">Annuler</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Créer</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Tab: Configuration -->
      <div v-if="activeTab === 'config'">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow max-w-xl">
          <h2 class="text-lg font-semibold mb-5 text-gray-900 dark:text-white">Configuration du programme</h2>
          <form @submit.prevent="submitSetup" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom du programme</label>
              <input v-model="setupForm.name" class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Points par 1000 XOF dépensé</label>
              <input v-model.number="setupForm.points_per_1000" type="number" min="1" required class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Seuil Argent (pts)</label>
                <input v-model.number="setupForm.silver_threshold" type="number" min="0" required class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Seuil Or (pts)</label>
                <input v-model.number="setupForm.gold_threshold" type="number" min="0" required class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expiration des points (mois, vide = jamais)</label>
              <input v-model.number="setupForm.expiry_months" type="number" min="1" class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:text-white" />
            </div>
            <div class="flex items-center">
              <input v-model="setupForm.is_active" type="checkbox" id="is_active" class="mr-2" />
              <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Programme actif</label>
            </div>
            <div class="pt-2">
              <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 transition">
                Enregistrer
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  program: Object,
  stats: Object,
  rewards: Array,
  topCustomers: Array,
  monthlyPoints: Array,
})

const activeTab = ref('programme')
const showRewardForm = ref(false)

const tabs = [
  { id: 'programme', label: '🏠 Programme' },
  { id: 'clients', label: '👑 Top Clients' },
  { id: 'recompenses', label: '🎁 Récompenses' },
  { id: 'config', label: '⚙️ Configuration' },
]

const setupForm = ref({
  name: props.program?.name ?? 'Programme Fidélité',
  points_per_1000: props.program?.points_per_1000 ?? 1,
  silver_threshold: props.program?.silver_threshold ?? 500,
  gold_threshold: props.program?.gold_threshold ?? 2000,
  expiry_months: props.program?.expiry_months ?? null,
  is_active: props.program?.is_active ?? true,
})

const rewardForm = ref({
  name: '',
  points_cost: 100,
  reward_type: 'discount_percent',
  reward_value: 10,
  stock: null,
})

const maxMonthlyPoints = computed(() => {
  const vals = (props.monthlyPoints ?? []).map(m => Number(m.total))
  return Math.max(...vals, 1)
})

function barHeight(val) {
  return Math.max(4, (Number(val) / maxMonthlyPoints.value) * 120)
}

function rewardTypeLabel(type) {
  return { discount_percent: 'Remise', discount_fixed: 'Remise', free_product: 'Gratuit', gift: 'Cadeau' }[type] ?? type
}

function submitSetup() {
  router.post(route('loyalty.setup'), setupForm.value, {
    preserveScroll: true,
  })
}

function submitReward() {
  router.post(route('loyalty.rewards.store'), rewardForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showRewardForm.value = false
      rewardForm.value = { name: '', points_cost: 100, reward_type: 'discount_percent', reward_value: 10, stock: null }
    },
  })
}
</script>

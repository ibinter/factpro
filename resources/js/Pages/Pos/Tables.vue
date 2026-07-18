<template>
  <AppLayout title="Plan de salle">
    <div class="p-6 max-w-6xl mx-auto">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Plan de salle</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Gérez vos tables et commandes en attente</p>
        </div>
        <div class="flex gap-3">
          <Link :href="route('pos.index')"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm font-medium">
            ← Retour à la caisse
          </Link>
          <button @click="openAddModal"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white text-sm font-medium transition"
            style="background-color:#0062CC">
            + Nouvelle table
          </button>
        </div>
      </div>

      <!-- Légende -->
      <div class="flex gap-4 mb-6 text-sm">
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span> Libre</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span> Occupée</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span> Réservée</span>
      </div>

      <!-- Grille de tables -->
      <div v-if="tables.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        <div v-for="table in tables" :key="table.id"
          class="relative rounded-xl border-2 p-4 cursor-pointer transition hover:shadow-md"
          :class="statusClasses(table.status)"
          @click="selectTable(table)">
          <div class="text-center">
            <div class="text-xl mb-1">🪑</div>
            <div class="font-semibold text-gray-900 dark:text-white text-sm">{{ table.name }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">{{ table.seats }} couverts</div>
            <div class="mt-2">
              <span class="text-xs font-medium px-2 py-0.5 rounded-full" :class="badgeClasses(table.status)">
                {{ statusLabel(table.status) }}
              </span>
            </div>
            <div v-if="table.status === 'occupied' && table.order_data" class="mt-1 text-xs text-gray-500">
              {{ table.order_data.length }} ligne(s)
            </div>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-16 text-gray-500 dark:text-gray-400">
        <div class="text-5xl mb-4">🍽️</div>
        <p class="text-lg font-medium">Aucune table configurée</p>
        <p class="text-sm mt-1">Ajoutez des tables pour gérer votre plan de salle.</p>
        <button @click="openAddModal" class="mt-4 px-4 py-2 rounded-lg text-white text-sm font-medium" style="background-color:#0062CC">
          + Créer la première table
        </button>
      </div>
    </div>

    <!-- Modal ajout/édition -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeModal">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
          {{ editingTable ? 'Modifier la table' : 'Nouvelle table' }}
        </h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom *</label>
            <input v-model="form.name" type="text" placeholder="ex: Table 1, Terrasse A"
              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Couverts</label>
            <input v-model.number="form.seats" type="number" min="1" max="99"
              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <button @click="closeModal" class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
            Annuler
          </button>
          <button @click="saveTable" :disabled="!form.name"
            class="px-4 py-2 rounded-lg text-white text-sm font-medium transition disabled:opacity-50"
            style="background-color:#0062CC">
            {{ editingTable ? 'Enregistrer' : 'Créer' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal détail table -->
    <div v-if="selectedTable" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="selectedTable = null">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-lg mx-4">
        <div class="flex justify-between items-start mb-4">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ selectedTable.name }}</h2>
          <button @click="selectedTable = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">✕</button>
        </div>

        <div class="mb-4 flex gap-2">
          <span class="text-sm px-2 py-0.5 rounded-full font-medium" :class="badgeClasses(selectedTable.status)">
            {{ statusLabel(selectedTable.status) }}
          </span>
          <span class="text-sm text-gray-500 dark:text-gray-400">{{ selectedTable.seats }} couverts</span>
        </div>

        <!-- Commande en attente -->
        <div v-if="selectedTable.order_data && selectedTable.order_data.length > 0" class="mb-4">
          <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Commande en attente</h3>
          <div class="space-y-1">
            <div v-for="(line, i) in selectedTable.order_data" :key="i"
              class="flex justify-between text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 rounded px-3 py-1.5">
              <span>{{ line.description || line.name }} × {{ line.quantity }}</span>
              <span class="font-medium">{{ formatMoney(line.unit_price * line.quantity) }}</span>
            </div>
          </div>
        </div>
        <p v-else class="text-sm text-gray-400 mb-4">Aucune commande en attente.</p>

        <div class="flex justify-between items-center">
          <div class="flex gap-2">
            <button @click="editTable(selectedTable)" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
              Modifier
            </button>
            <button v-if="selectedTable.status === 'free'" @click="deleteTable(selectedTable)"
              class="px-3 py-1.5 text-sm rounded-lg border border-red-300 text-red-600 hover:bg-red-50 transition">
              Supprimer
            </button>
          </div>
          <button v-if="selectedTable.status !== 'free'" @click="freeTable(selectedTable)"
            class="px-4 py-2 rounded-lg text-white text-sm font-medium bg-green-600 hover:bg-green-700 transition">
            Libérer la table
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
  tables: Array,
})

const showModal = ref(false)
const editingTable = ref(null)
const selectedTable = ref(null)
const form = ref({ name: '', seats: 4 })

function statusClasses(status) {
  return {
    'border-green-400 bg-green-50 dark:bg-green-950/30': status === 'free',
    'border-red-400 bg-red-50 dark:bg-red-950/30': status === 'occupied',
    'border-yellow-400 bg-yellow-50 dark:bg-yellow-950/30': status === 'reserved',
  }
}

function badgeClasses(status) {
  return {
    'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300': status === 'free',
    'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300': status === 'occupied',
    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300': status === 'reserved',
  }
}

function statusLabel(status) {
  return { free: 'Libre', occupied: 'Occupée', reserved: 'Réservée' }[status] ?? status
}

function formatMoney(amount) {
  return Number(amount || 0).toLocaleString('fr-FR', { minimumFractionDigits: 2 })
}

function openAddModal() {
  editingTable.value = null
  form.value = { name: '', seats: 4 }
  showModal.value = true
}

function editTable(table) {
  selectedTable.value = null
  editingTable.value = table
  form.value = { name: table.name, seats: table.seats }
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editingTable.value = null
}

function saveTable() {
  if (editingTable.value) {
    router.put(route('pos.tables.update', editingTable.value.id), form.value, {
      onSuccess: closeModal,
    })
  } else {
    router.post(route('pos.tables.store'), form.value, {
      onSuccess: closeModal,
    })
  }
}

function deleteTable(table) {
  if (!confirm(`Supprimer "${table.name}" ?`)) return
  router.delete(route('pos.tables.destroy', table.id), {
    onSuccess: () => { selectedTable.value = null },
  })
}

function freeTable(table) {
  router.post(route('pos.tables.free', table.id), {}, {
    onSuccess: () => { selectedTable.value = null },
  })
}

function selectTable(table) {
  selectedTable.value = table
}
</script>

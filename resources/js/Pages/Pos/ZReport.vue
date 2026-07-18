<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  reports: { type: Array, default: () => [] },
  currency: { type: String, default: 'XOF' },
})

const page = usePage()

// ─── Saisie caisse comptée ───────────────────────────────────────────────────
const showCloseModal = ref(false)
const sessionIdToClose = ref(null)
const actualCash = ref('')
const notes = ref('')
const processing = ref(false)

// Caisse théorique (from x-report, loaded lazily)
const theoreticalCash = ref(null)

// Clavier virtuel
const keys = ['7','8','9','4','5','6','1','2','3','0','00','⌫']

function pressKey(k) {
  if (k === '⌫') {
    actualCash.value = actualCash.value.slice(0, -1)
  } else {
    actualCash.value += k
  }
}

const parsedCash = computed(() => parseFloat(actualCash.value) || 0)
const diff = computed(() => {
  if (theoreticalCash.value === null) return null
  return parsedCash.value - theoreticalCash.value
})
const diffColor = computed(() => {
  if (diff.value === null) return 'text-gray-500'
  if (diff.value > 0) return 'text-green-600'
  if (diff.value < 0) return 'text-red-600'
  return 'text-gray-700'
})

// ─── Confirmation modal ───────────────────────────────────────────────────────
const showConfirm = ref(false)

async function openCloseModal(sessionId) {
  sessionIdToClose.value = sessionId
  actualCash.value = ''
  notes.value = ''
  theoreticalCash.value = null
  showCloseModal.value = true
  // Fetch X-report to get theoretical cash
  try {
    const res = await fetch(route('pos.x-report', sessionId), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    if (res.ok) {
      const data = await res.json()
      theoreticalCash.value = data.theoretical_cash ?? null
    }
  } catch (_) { /* ignore */ }
}

function confirmGenerate() {
  if (!actualCash.value) return
  showConfirm.value = true
}

function cancelConfirm() {
  showConfirm.value = false
}

function generateZ() {
  if (!sessionIdToClose.value) return
  processing.value = true
  router.post(
    route('pos.z-report.generate', sessionIdToClose.value),
    { actual_cash: parsedCash.value, notes: notes.value },
    {
      onFinish: () => { processing.value = false },
      onSuccess: () => {
        showCloseModal.value = false
        showConfirm.value = false
      },
    }
  )
}

function fmt(n) {
  return new Intl.NumberFormat('fr-FR').format(n ?? 0)
}
</script>

<template>
  <div class="p-6 max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
      Rapports Z — Historique des clôtures
    </h1>

    <!-- Tableau historique -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300 uppercase text-xs">
          <tr>
            <th class="px-4 py-3 text-left">N° Z</th>
            <th class="px-4 py-3 text-left">Date clôture</th>
            <th class="px-4 py-3 text-left">Caissier</th>
            <th class="px-4 py-3 text-right">Tickets</th>
            <th class="px-4 py-3 text-right">Total ventes</th>
            <th class="px-4 py-3 text-right">Écart</th>
            <th class="px-4 py-3 text-center">PDF</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
          <tr v-if="reports.length === 0">
            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
              Aucun rapport Z généré pour le moment.
            </td>
          </tr>
          <tr
            v-for="r in reports"
            :key="r.id"
            class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition"
          >
            <td class="px-4 py-3 font-mono font-semibold text-indigo-600 dark:text-indigo-400">
              {{ r.z_number }}
            </td>
            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
              {{ r.z_report_generated_at ? new Date(r.z_report_generated_at).toLocaleString('fr-FR') : '—' }}
            </td>
            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ r.cashier ?? '—' }}</td>
            <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">{{ r.tickets_count }}</td>
            <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-100">
              {{ fmt(r.total_sales) }} {{ currency }}
            </td>
            <td
              class="px-4 py-3 text-right font-semibold"
              :class="{
                'text-green-600': r.difference > 0,
                'text-red-600': r.difference < 0,
                'text-gray-500': r.difference === 0,
              }"
            >
              {{ r.difference >= 0 ? '+' : '' }}{{ fmt(r.difference) }} {{ currency }}
            </td>
            <td class="px-4 py-3 text-center">
              <a
                :href="r.pdf_url"
                target="_blank"
                class="inline-flex items-center gap-1 text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg transition"
              >
                PDF
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ─── Modal clôture ─────────────────────────────────────────────── -->
    <Teleport to="body">
      <div
        v-if="showCloseModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
      >
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm p-6">
          <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-4">
            Clôture de caisse — Rapport Z
          </h2>

          <!-- Caisse théorique -->
          <div v-if="theoreticalCash !== null" class="mb-4 p-3 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500 dark:text-gray-300">Caisse théorique</span>
              <span class="font-semibold text-gray-800 dark:text-white">
                {{ fmt(theoreticalCash) }} {{ currency }}
              </span>
            </div>
          </div>

          <!-- Affichage montant saisi -->
          <div class="mb-3 p-4 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl text-center">
            <div class="text-xs text-indigo-500 mb-1">Caisse comptée</div>
            <div class="text-3xl font-bold text-indigo-700 dark:text-indigo-300 font-mono">
              {{ actualCash ? fmt(parsedCash) : '—' }} {{ currency }}
            </div>
            <div v-if="diff !== null && actualCash" class="mt-1 text-sm font-semibold" :class="diffColor">
              Écart : {{ diff >= 0 ? '+' : '' }}{{ fmt(diff) }} {{ currency }}
            </div>
          </div>

          <!-- Clavier virtuel -->
          <div class="grid grid-cols-3 gap-2 mb-4">
            <button
              v-for="k in keys"
              :key="k"
              @click="pressKey(k)"
              class="h-12 rounded-xl font-bold text-lg transition"
              :class="k === '⌫'
                ? 'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 hover:bg-red-200'
                : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-indigo-100 dark:hover:bg-indigo-800'"
            >
              {{ k }}
            </button>
          </div>

          <!-- Notes -->
          <textarea
            v-model="notes"
            placeholder="Notes (optionnel)"
            rows="2"
            class="w-full border border-gray-200 dark:border-gray-600 rounded-lg p-2 text-sm mb-4 bg-white dark:bg-gray-700 text-gray-800 dark:text-white resize-none"
          />

          <div class="flex gap-3">
            <button
              @click="showCloseModal = false"
              class="flex-1 py-2 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
              Annuler
            </button>
            <button
              @click="confirmGenerate"
              :disabled="!actualCash"
              class="flex-1 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 text-white font-semibold transition"
            >
              Générer le Rapport Z
            </button>
          </div>
        </div>
      </div>

      <!-- ─── Confirmation irréversible ────────────────────────────── -->
      <div
        v-if="showConfirm"
        class="fixed inset-0 z-60 flex items-center justify-center bg-black/70"
      >
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-xs p-6 text-center">
          <div class="text-4xl mb-3">⚠️</div>
          <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2">
            Action irréversible
          </h3>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
            Le rapport Z clôture définitivement la session. Cette opération ne peut pas être annulée.
          </p>
          <div class="flex gap-3">
            <button
              @click="cancelConfirm"
              class="flex-1 py-2 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 transition"
            >
              Retour
            </button>
            <button
              @click="generateZ"
              :disabled="processing"
              class="flex-1 py-2 rounded-xl bg-red-600 hover:bg-red-700 disabled:opacity-40 text-white font-semibold transition"
            >
              {{ processing ? 'Génération…' : 'Confirmer' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

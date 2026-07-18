<template>
  <AppLayout title="Déclaration OSS TVA UE">
    <div class="max-w-5xl mx-auto py-8 px-4 space-y-8">
      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
          Déclaration OSS — TVA Intracommunautaire UE
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          One Stop Shop — Régime Union
        </p>
      </div>

      <!-- Quarter/Year selector -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">
          Sélectionner la période
        </h2>
        <div class="flex flex-wrap gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Trimestre
            </label>
            <select
              v-model="form.quarter"
              class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2"
            >
              <option value="1">T1 — Janv. / Févr. / Mars</option>
              <option value="2">T2 — Avr. / Mai / Juin</option>
              <option value="3">T3 — Juil. / Août / Sept.</option>
              <option value="4">T4 — Oct. / Nov. / Déc.</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Année
            </label>
            <select
              v-model="form.year"
              class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2"
            >
              <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
            </select>
          </div>

          <button
            @click="calculate"
            :disabled="loading"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-medium px-5 py-2 rounded-lg transition"
          >
            <span v-if="loading">Calcul en cours…</span>
            <span v-else>Calculer</span>
          </button>
        </div>
      </div>

      <!-- Result table -->
      <div v-if="result" class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 space-y-4">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
          Résultat — T{{ result.period.quarter }} {{ result.period.year }}
        </h2>

        <div v-if="result.below_threshold" class="rounded-lg bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-700 p-4 text-amber-800 dark:text-amber-300 text-sm">
          Le chiffre d'affaires UE est inférieur au seuil de <strong>10 000 €</strong>.
          Vous pouvez appliquer la TVA de votre pays d'établissement.
        </div>

        <div v-if="result.by_country.length === 0" class="text-gray-500 dark:text-gray-400 text-sm">
          Aucune facture vers des clients UE sur cette période.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-700 text-left text-gray-500 dark:text-gray-400">
                <th class="pb-3 pr-4 font-medium">Pays</th>
                <th class="pb-3 pr-4 font-medium text-right">Base HT (€)</th>
                <th class="pb-3 pr-4 font-medium text-right">Taux TVA</th>
                <th class="pb-3 font-medium text-right">TVA due (€)</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in result.by_country"
                :key="row.country"
                class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/40"
              >
                <td class="py-3 pr-4 font-medium text-gray-900 dark:text-white">
                  {{ countryFlag(row.country) }} {{ row.country }}
                </td>
                <td class="py-3 pr-4 text-right text-gray-700 dark:text-gray-300">
                  {{ fmt(row.base_ht) }}
                </td>
                <td class="py-3 pr-4 text-right text-gray-700 dark:text-gray-300">
                  {{ row.vat_rate }}%
                </td>
                <td class="py-3 text-right font-semibold text-gray-900 dark:text-white">
                  {{ fmt(row.vat_amount) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Total -->
        <div class="flex justify-end">
          <div class="rounded-xl bg-blue-600 text-white px-8 py-4 text-center min-w-[200px]">
            <div class="text-sm opacity-80">Total TVA à déclarer</div>
            <div class="text-3xl font-bold mt-1">{{ fmt(result.total_vat) }} €</div>
          </div>
        </div>

        <!-- Note -->
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
          Déclaration à soumettre sur le portail OSS de votre État membre d'identification
          avant le dernier jour du mois suivant la fin du trimestre.
        </p>
      </div>

      <!-- VAT number validator -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">
          Validation numéro de TVA intracommunautaire
        </h2>
        <div class="flex flex-wrap gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Pays
            </label>
            <select
              v-model="vatForm.country_code"
              class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2"
            >
              <option v-for="c in euCountries" :key="c" :value="c">
                {{ countryFlag(c) }} {{ c }}
              </option>
            </select>
          </div>

          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Numéro de TVA
            </label>
            <input
              v-model="vatForm.vat_number"
              type="text"
              placeholder="FR12345678901"
              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2"
              @keyup.enter="checkVat"
            />
          </div>

          <button
            @click="checkVat"
            :disabled="vatLoading"
            class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 disabled:opacity-50 text-white font-medium px-5 py-2 rounded-lg transition"
          >
            Vérifier
          </button>
        </div>

        <div v-if="vatResult !== null" class="mt-4 flex items-center gap-3">
          <span class="text-2xl">{{ vatResult ? '✅' : '❌' }}</span>
          <span :class="vatResult ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="font-semibold">
            {{ vatResult ? 'Numéro de TVA valide' : 'Format invalide' }}
          </span>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'
import axios from 'axios'

const props = defineProps({
  euCountries: Array,
  vatRates: Object,
})

// Quarter/year form
const currentYear = new Date().getFullYear()
const years = Array.from({ length: 6 }, (_, i) => currentYear - i)

const form = ref({
  quarter: String(Math.ceil((new Date().getMonth() + 1) / 3)),
  year: currentYear,
})

const loading = ref(false)
const result = ref(null)

async function calculate() {
  loading.value = true
  result.value = null
  try {
    const { data } = await axios.post(route('tax.oss.declaration'), {
      quarter: parseInt(form.value.quarter),
      year: parseInt(form.value.year),
    })
    result.value = data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

// VAT validator
const vatForm = ref({ country_code: 'FR', vat_number: '' })
const vatLoading = ref(false)
const vatResult = ref(null)

async function checkVat() {
  if (!vatForm.value.vat_number) return
  vatLoading.value = true
  vatResult.value = null
  try {
    const { data } = await axios.post(route('tax.oss.validate-vat'), vatForm.value)
    vatResult.value = data.valid
  } catch (e) {
    vatResult.value = false
  } finally {
    vatLoading.value = false
  }
}

// Helpers
function fmt(val) {
  return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val)
}

const FLAG_OFFSETS = {
  AT: '🇦🇹', BE: '🇧🇪', BG: '🇧🇬', CH: '🇨🇭', CY: '🇨🇾', CZ: '🇨🇿',
  DE: '🇩🇪', DK: '🇩🇰', EE: '🇪🇪', ES: '🇪🇸', FI: '🇫🇮', FR: '🇫🇷',
  GB: '🇬🇧', GR: '🇬🇷', HR: '🇭🇷', HU: '🇭🇺', IE: '🇮🇪', IT: '🇮🇹',
  LT: '🇱🇹', LU: '🇱🇺', LV: '🇱🇻', MT: '🇲🇹', NL: '🇳🇱', PL: '🇵🇱',
  PT: '🇵🇹', RO: '🇷🇴', SE: '🇸🇪', SI: '🇸🇮', SK: '🇸🇰',
}

function countryFlag(code) {
  return FLAG_OFFSETS[code] ?? '🏳️'
}
</script>

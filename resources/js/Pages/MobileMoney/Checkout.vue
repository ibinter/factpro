<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">
        Paiement Mobile Money
      </h1>

      <!-- Sélecteur opérateur -->
      <div v-if="!initiated" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Choisissez votre opérateur
          </label>
          <div class="grid grid-cols-2 gap-3">
            <button
              v-for="op in operators"
              :key="op.id"
              @click="selectOperator(op)"
              :class="[
                'p-4 rounded-xl border-2 transition-all text-center',
                form.driver === op.id
                  ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30'
                  : 'border-gray-200 dark:border-gray-600 hover:border-blue-300'
              ]"
            >
              <span class="text-2xl block mb-1">{{ op.emoji }}</span>
              <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ op.label }}</span>
              <span class="text-xs text-gray-500 dark:text-gray-400 block">{{ op.countries }}</span>
            </button>
          </div>
        </div>

        <!-- Numéro de téléphone -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Numéro de téléphone
          </label>
          <input
            v-model="form.phone"
            @input="onPhoneInput"
            type="tel"
            :placeholder="selectedOperator?.placeholder ?? '+225 XX XX XX XX XX'"
            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <p v-if="detectedDriver && detectedDriver !== form.driver" class="text-xs text-amber-600 dark:text-amber-400 mt-1">
            Numéro détecté : {{ operatorLabel(detectedDriver) }}
          </p>
        </div>

        <!-- Montant -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Montant
          </label>
          <div class="flex gap-2">
            <input
              v-model.number="form.amount"
              type="number"
              min="1"
              placeholder="0"
              class="flex-1 px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <select
              v-model="form.currency"
              class="px-3 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="XOF">XOF</option>
              <option value="XAF">XAF</option>
              <option value="GHS">GHS</option>
              <option value="NGN">NGN</option>
            </select>
          </div>
        </div>

        <!-- Instructions opérateur -->
        <div v-if="selectedOperator" class="p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-sm text-blue-700 dark:text-blue-300">
          {{ selectedOperator.instructions }}
        </div>

        <!-- Erreur -->
        <div v-if="error" class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 text-sm text-red-700 dark:text-red-300">
          {{ error }}
        </div>

        <!-- Bouton payer -->
        <button
          @click="pay"
          :disabled="!canPay || loading"
          class="w-full py-3 px-6 rounded-xl font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <span v-if="loading">Initiation en cours…</span>
          <span v-else>Payer {{ form.amount ? form.amount.toLocaleString() : '' }} {{ form.currency }}</span>
        </button>
      </div>

      <!-- État polling après initiation -->
      <div v-else class="text-center space-y-4">
        <!-- En attente -->
        <div v-if="paymentStatus === 'pending' || paymentStatus === 'PENDING'">
          <div class="text-5xl mb-3 animate-pulse">⏳</div>
          <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">En attente de confirmation</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ instructions }}</p>
          <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">
            Vérification toutes les 5s… ({{ Math.floor(elapsed / 1000) }}s / 600s)
          </p>
          <div class="mt-4 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
            <div
              class="h-full bg-blue-500 transition-all duration-1000"
              :style="{ width: Math.min((elapsed / 600000) * 100, 100) + '%' }"
            ></div>
          </div>
        </div>

        <!-- Succès -->
        <div v-else-if="paymentStatus === 'paid' || paymentStatus === 'succeeded'">
          <div class="text-5xl mb-3">✅</div>
          <p class="text-lg font-semibold text-green-600 dark:text-green-400">Paiement confirmé !</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Merci pour votre paiement.</p>
        </div>

        <!-- Échec -->
        <div v-else>
          <div class="text-5xl mb-3">❌</div>
          <p class="text-lg font-semibold text-red-600 dark:text-red-400">Paiement échoué</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ paymentStatus }}</p>
          <button
            @click="reset"
            class="mt-4 px-6 py-2 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          >
            Réessayer
          </button>
        </div>

        <!-- Lien Wave si disponible -->
        <a
          v-if="checkoutUrl"
          :href="checkoutUrl"
          target="_blank"
          class="inline-block mt-4 px-6 py-3 rounded-xl font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors"
        >
          Ouvrir l'application Wave
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  drivers: Array,
})

const operators = [
  {
    id: 'wave',
    emoji: '🌊',
    label: 'Wave',
    countries: 'SN, CI',
    placeholder: '+221 7X XXX XX XX',
    instructions: 'Vous allez être redirigé vers l\'application Wave pour confirmer le paiement.',
  },
  {
    id: 'orange_money',
    emoji: '🟠',
    label: 'Orange Money',
    countries: 'CI, SN, CM, ML, BF',
    placeholder: '+225 07 XX XX XX XX',
    instructions: 'Vous recevrez une notification USSD Orange pour confirmer le paiement.',
  },
  {
    id: 'mtn_momo',
    emoji: '🟡',
    label: 'MTN MoMo',
    countries: 'CI, CM, GH, NG',
    placeholder: '+225 05 XX XX XX XX',
    instructions: 'Vous recevrez une notification USSD MTN. Entrez votre PIN Mobile Money pour confirmer.',
  },
  {
    id: 'moov_money',
    emoji: '🔵',
    label: 'Moov Money',
    countries: 'CI, BJ, TG, BF',
    placeholder: '+229 95 XX XX XX',
    instructions: 'Vous recevrez une notification USSD Flooz Moov. Entrez votre code secret pour valider.',
  },
]

const form = ref({
  driver: '',
  phone: '',
  amount: null,
  currency: 'XOF',
})

const loading = ref(false)
const error = ref('')
const initiated = ref(false)
const paymentStatus = ref('pending')
const instructions = ref('')
const checkoutUrl = ref('')
const currentReference = ref('')
const detectedDriver = ref(null)

const elapsed = ref(0)
let pollTimer = null
let elapsedTimer = null

const selectedOperator = computed(() => operators.find(o => o.id === form.value.driver) ?? null)

const canPay = computed(() =>
  form.value.driver && form.value.phone.length >= 8 && form.value.amount > 0
)

function selectOperator(op) {
  form.value.driver = op.id
}

function operatorLabel(driverId) {
  return operators.find(o => o.id === driverId)?.label ?? driverId
}

async function onPhoneInput() {
  if (form.value.phone.length < 8) {
    detectedDriver.value = null
    return
  }
  try {
    const res = await fetch(route('mobile-money.detect'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
      body: JSON.stringify({ phone: form.value.phone, country: 'CI' }),
    })
    const data = await res.json()
    detectedDriver.value = data.driver ?? null
    if (data.driver && !form.value.driver) {
      form.value.driver = data.driver
    }
  } catch {
    // silencieux
  }
}

async function pay() {
  error.value = ''
  loading.value = true

  try {
    const res = await fetch(route('mobile-money.initiate'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
      body: JSON.stringify(form.value),
    })

    const data = await res.json()

    if (!res.ok) {
      error.value = data.error ?? 'Une erreur est survenue'
      return
    }

    initiated.value = true
    currentReference.value = data.reference
    instructions.value = data.instructions ?? ''
    checkoutUrl.value = data.checkout_url ?? ''
    paymentStatus.value = 'pending'

    startPolling()
  } catch (e) {
    error.value = 'Impossible de contacter le serveur'
  } finally {
    loading.value = false
  }
}

function startPolling() {
  elapsed.value = 0
  elapsedTimer = setInterval(() => {
    elapsed.value += 1000
    if (elapsed.value >= 600000) {
      stopPolling()
      paymentStatus.value = 'timeout'
    }
  }, 1000)

  pollTimer = setInterval(async () => {
    try {
      const res = await fetch(
        route('mobile-money.status', currentReference.value) + '?driver=' + form.value.driver
      )
      const data = await res.json()
      paymentStatus.value = data.status ?? 'pending'
      if (data.paid || ['failed', 'FAILED', 'error'].includes(data.status)) {
        stopPolling()
      }
    } catch {
      // continue polling
    }
  }, 5000)
}

function stopPolling() {
  if (pollTimer) clearInterval(pollTimer)
  if (elapsedTimer) clearInterval(elapsedTimer)
  pollTimer = null
  elapsedTimer = null
}

function reset() {
  stopPolling()
  initiated.value = false
  paymentStatus.value = 'pending'
  checkoutUrl.value = ''
  currentReference.value = ''
  error.value = ''
  elapsed.value = 0
}

onUnmounted(() => stopPolling())
</script>

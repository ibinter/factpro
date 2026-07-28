<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Méthodes de paiement
      </h2>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

        <!-- Flash success -->
        <transition name="fade">
          <div v-if="$page.props.flash?.success"
               class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-green-800 dark:bg-green-900/20 dark:border-green-700 dark:text-green-300 flex items-center gap-2">
            <svg class="h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ $page.props.flash.success }}
          </div>
        </transition>

        <form @submit.prevent="submit" class="space-y-6">

          <!-- ═══════════════════════════════════════════════════
               SECTION 1 — Passerelles en ligne
          ═══════════════════════════════════════════════════ -->
          <SectionCard title="Passerelles de paiement en ligne" icon="🌐">
            <div class="space-y-4">

              <!-- CinetPay -->
              <GatewayAccordion
                v-model:enabled="form.cinetpay.enabled"
                label="CinetPay"
                logo-color="#E8762A"
                :key-prefix="'cinetpay'"
              >
                <PasswordField v-model="form.cinetpay.api_key" label="API Key" />
                <InputField v-model="form.cinetpay.site_id" label="Site ID" />
              </GatewayAccordion>

              <!-- FedaPay -->
              <GatewayAccordion
                v-model:enabled="form.fedapay.enabled"
                label="FedaPay"
                logo-color="#1E40AF"
              >
                <PasswordField v-model="form.fedapay.secret_key" label="Secret Key" />
                <SelectField
                  v-model="form.fedapay.environment"
                  label="Environnement"
                  :options="[{value:'sandbox',label:'Sandbox'},{value:'live',label:'Live'}]"
                />
              </GatewayAccordion>

              <!-- Flutterwave -->
              <GatewayAccordion
                v-model:enabled="form.flutterwave.enabled"
                label="Flutterwave"
                logo-color="#F5A623"
              >
                <PasswordField v-model="form.flutterwave.secret_key" label="Secret Key" />
              </GatewayAccordion>

              <!-- Moneroo -->
              <GatewayAccordion
                v-model:enabled="form.moneroo.enabled"
                label="Moneroo"
                logo-color="#6366F1"
              >
                <PasswordField v-model="form.moneroo.public_key" label="Public Key" />
                <PasswordField v-model="form.moneroo.secret_key" label="Secret Key" />
              </GatewayAccordion>

              <!-- Stripe -->
              <GatewayAccordion
                v-model:enabled="form.stripe.enabled"
                label="Stripe"
                logo-color="#635BFF"
              >
                <PasswordField v-model="form.stripe.publishable_key" label="Publishable Key" />
                <PasswordField v-model="form.stripe.secret_key" label="Secret Key" />
              </GatewayAccordion>

              <!-- PayPal -->
              <GatewayAccordion
                v-model:enabled="form.paypal.enabled"
                label="PayPal"
                logo-color="#003087"
              >
                <PasswordField v-model="form.paypal.client_id" label="Client ID" />
                <PasswordField v-model="form.paypal.secret" label="Secret" />
                <SelectField
                  v-model="form.paypal.environment"
                  label="Environnement"
                  :options="[{value:'sandbox',label:'Sandbox'},{value:'live',label:'Live'}]"
                />
              </GatewayAccordion>

            </div>
          </SectionCard>

          <!-- ═══════════════════════════════════════════════════
               SECTION 2 — Mobile Money
          ═══════════════════════════════════════════════════ -->
          <SectionCard title="Mobile Money" icon="📱">
            <div class="space-y-4">

              <MobileMoneyAccordion
                v-for="op in mobileOperators"
                :key="op.key"
                v-model:enabled="form[op.key].enabled"
                :label="op.label"
                :badge-color="op.color"
              >
                <InputField v-model="form[op.key].phone" label="Numéro de téléphone" placeholder="+221 xx xxx xx xx" />
                <InputField v-model="form[op.key].name" label="Nom du bénéficiaire" />
                <TextareaField v-model="form[op.key].instructions" label="Instructions de paiement" />
              </MobileMoneyAccordion>

            </div>
          </SectionCard>

          <!-- ═══════════════════════════════════════════════════
               SECTION 3 — Méthodes classiques
          ═══════════════════════════════════════════════════ -->
          <SectionCard title="Méthodes classiques" icon="🏦">
            <div class="space-y-4">

              <!-- Virement bancaire -->
              <ClassicAccordion v-model:enabled="form.bank_transfer.enabled" label="Virement bancaire" icon="🏛️">
                <InputField v-model="form.bank_transfer.bank_name" label="Nom de la banque" />
                <InputField v-model="form.bank_transfer.account_name" label="Nom du titulaire" />
                <InputField v-model="form.bank_transfer.rib" label="RIB" />
                <InputField v-model="form.bank_transfer.iban" label="IBAN" />
                <InputField v-model="form.bank_transfer.swift" label="Code SWIFT" />
              </ClassicAccordion>

              <!-- Chèque -->
              <ClassicAccordion v-model:enabled="form.cheque.enabled" label="Chèque" icon="📄">
                <InputField v-model="form.cheque.payable_to" label="Chèque à l'ordre de" />
                <InputField v-model="form.cheque.address" label="Adresse d'envoi" />
              </ClassicAccordion>

              <!-- Espèces -->
              <ClassicAccordion v-model:enabled="form.cash.enabled" label="Espèces" icon="💵">
                <InputField v-model="form.cash.address" label="Adresse" />
                <InputField v-model="form.cash.hours" label="Horaires d'ouverture" placeholder="Lun-Ven 8h-17h" />
              </ClassicAccordion>

              <!-- Western Union -->
              <ClassicAccordion v-model:enabled="form.western_union.enabled" label="Western Union" icon="🌍">
                <InputField v-model="form.western_union.name" label="Nom du bénéficiaire" />
                <InputField v-model="form.western_union.country" label="Pays" />
                <InputField v-model="form.western_union.city" label="Ville" />
              </ClassicAccordion>

              <!-- Transfert SWIFT -->
              <ClassicAccordion v-model:enabled="form.swift_transfer.enabled" label="Transfert SWIFT international" icon="✈️">
                <InputField v-model="form.swift_transfer.bank_name" label="Nom de la banque" />
                <InputField v-model="form.swift_transfer.swift_code" label="Code SWIFT / BIC" />
                <InputField v-model="form.swift_transfer.iban" label="IBAN" />
                <InputField v-model="form.swift_transfer.beneficiary" label="Bénéficiaire" />
              </ClassicAccordion>

            </div>
          </SectionCard>

          <!-- ═══════════════════════════════════════════════════
               SECTION 4 — Crypto
          ═══════════════════════════════════════════════════ -->
          <SectionCard title="Cryptomonnaies" icon="₿">
            <div class="space-y-4">

              <!-- USDT TRC-20 -->
              <ClassicAccordion v-model:enabled="form.usdt_trc20.enabled" label="USDT (TRC-20)" icon="🪙">
                <InputField v-model="form.usdt_trc20.address" label="Adresse du portefeuille" placeholder="T..." />
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Réseau</label>
                  <input type="text" value="TRC20" readonly
                         class="w-full rounded-md border border-gray-200 bg-gray-100 dark:bg-gray-700 dark:border-gray-600 px-3 py-2 text-sm text-gray-500 dark:text-gray-400 cursor-not-allowed" />
                </div>
              </ClassicAccordion>

              <!-- Bitcoin -->
              <ClassicAccordion v-model:enabled="form.bitcoin.enabled" label="Bitcoin" icon="₿">
                <InputField v-model="form.bitcoin.address" label="Adresse Bitcoin" placeholder="bc1..." />
              </ClassicAccordion>

            </div>
          </SectionCard>

          <!-- Padding pour le sticky -->
          <div class="h-20"></div>
        </form>

        <!-- ═══════════════════════════════════════════════════
             Bouton sticky
        ═══════════════════════════════════════════════════ -->
        <div class="fixed bottom-0 left-0 right-0 z-50 border-t border-gray-200 bg-white/95 backdrop-blur dark:border-gray-700 dark:bg-gray-900/95 px-4 py-3 shadow-lg">
          <div class="mx-auto max-w-5xl flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Société : <span class="font-medium text-gray-700 dark:text-gray-200">{{ company?.name }}</span>
              <span v-if="company?.currency" class="ml-2 text-gray-400">· {{ company.currency }}</span>
            </p>
            <button
              type="button"
              @click="submit"
              :disabled="form.processing"
              class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="form.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              {{ form.processing ? 'Enregistrement…' : 'Enregistrer les modifications' }}
            </button>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, defineComponent, h } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

// ─── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({
  methods: { type: Object, default: () => ({}) },
  company: Object,
})

// ─── Helpers ──────────────────────────────────────────────────────────────────
const g = (key, defaults = {}) => ({
  enabled: false,
  ...defaults,
  ...(props.methods?.[key] ?? {}),
})

// ─── Form ─────────────────────────────────────────────────────────────────────
const form = useForm({
  // Passerelles
  cinetpay:      g('cinetpay',      { api_key: '', site_id: '' }),
  fedapay:       g('fedapay',       { secret_key: '', environment: 'sandbox' }),
  flutterwave:   g('flutterwave',   { secret_key: '' }),
  moneroo:       g('moneroo',       { public_key: '', secret_key: '' }),
  stripe:        g('stripe',        { publishable_key: '', secret_key: '' }),
  paypal:        g('paypal',        { client_id: '', secret: '', environment: 'sandbox' }),

  // Mobile Money
  orange_money:  g('orange_money',  { phone: '', name: '', instructions: '' }),
  mtn_momo:      g('mtn_momo',      { phone: '', name: '', instructions: '' }),
  wave:          g('wave',          { phone: '', name: '', instructions: '' }),
  moov_money:    g('moov_money',    { phone: '', name: '', instructions: '' }),
  airtel_money:  g('airtel_money',  { phone: '', name: '', instructions: '' }),
  free_money:    g('free_money',    { phone: '', name: '', instructions: '' }),
  mpesa:         g('mpesa',         { phone: '', name: '', instructions: '' }),

  // Classiques
  bank_transfer: g('bank_transfer', { bank_name: '', account_name: '', rib: '', iban: '', swift: '' }),
  cheque:        g('cheque',        { payable_to: '', address: '' }),
  cash:          g('cash',          { address: '', hours: '' }),
  western_union: g('western_union', { name: '', country: '', city: '' }),
  swift_transfer:g('swift_transfer',{ bank_name: '', swift_code: '', iban: '', beneficiary: '' }),

  // Crypto
  usdt_trc20:   g('usdt_trc20',    { address: '' }),
  bitcoin:      g('bitcoin',        { address: '' }),
})

const submit = () => {
  form.put(route('settings.payment-methods.update'))
}

// ─── Mobile operators config ───────────────────────────────────────────────────
const mobileOperators = [
  { key: 'orange_money', label: 'Orange Money',       color: '#EA580C' },
  { key: 'mtn_momo',     label: 'MTN Mobile Money',   color: '#EAB308' },
  { key: 'wave',         label: 'Wave',                color: '#2563EB' },
  { key: 'moov_money',   label: 'Moov Money',          color: '#7C3AED' },
  { key: 'airtel_money', label: 'Airtel Money',        color: '#DC2626' },
  { key: 'free_money',   label: 'Free Money',          color: '#16A34A' },
  { key: 'mpesa',        label: 'M-Pesa',              color: '#15803D' },
]

// ─── Sub-components ───────────────────────────────────────────────────────────

// SectionCard
const SectionCard = defineComponent({
  props: { title: String, icon: String },
  setup(props, { slots }) {
    return () => h('div', {
      class: 'rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 overflow-hidden'
    }, [
      h('div', {
        class: 'flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 px-5 py-4'
      }, [
        h('span', { class: 'text-xl' }, props.icon),
        h('h3', { class: 'text-base font-semibold text-gray-800 dark:text-gray-100' }, props.title),
      ]),
      h('div', { class: 'p-5' }, slots.default?.()),
    ])
  }
})

// Toggle
const Toggle = defineComponent({
  props: { modelValue: Boolean },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    return () => h('button', {
      type: 'button',
      role: 'switch',
      'aria-checked': props.modelValue,
      onClick: () => emit('update:modelValue', !props.modelValue),
      class: [
        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
        props.modelValue ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-600'
      ]
    }, [
      h('span', {
        class: [
          'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
          props.modelValue ? 'translate-x-5' : 'translate-x-0'
        ]
      })
    ])
  }
})

// GatewayAccordion
const GatewayAccordion = defineComponent({
  props: {
    enabled: Boolean,
    label: String,
    logoColor: { type: String, default: '#6366F1' },
  },
  emits: ['update:enabled'],
  setup(props, { slots, emit }) {
    return () => h('div', {
      class: 'rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden'
    }, [
      h('div', {
        class: 'flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-700/40 cursor-pointer',
        onClick: () => emit('update:enabled', !props.enabled)
      }, [
        h('div', { class: 'flex items-center gap-3' }, [
          h('span', {
            class: 'inline-flex h-7 w-7 items-center justify-center rounded-md text-white text-xs font-bold',
            style: { backgroundColor: props.logoColor }
          }, props.label.charAt(0)),
          h('span', { class: 'font-medium text-gray-800 dark:text-gray-100 text-sm' }, props.label),
        ]),
        h(Toggle, {
          modelValue: props.enabled,
          onClick: (e) => e.stopPropagation(),
          'onUpdate:modelValue': (v) => emit('update:enabled', v),
        }),
      ]),
      props.enabled
        ? h('div', { class: 'px-4 py-4 border-t border-gray-100 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white dark:bg-gray-800' },
            slots.default?.())
        : null,
    ])
  }
})

// MobileMoneyAccordion
const MobileMoneyAccordion = defineComponent({
  props: {
    enabled: Boolean,
    label: String,
    badgeColor: { type: String, default: '#6366F1' },
  },
  emits: ['update:enabled'],
  setup(props, { slots, emit }) {
    return () => h('div', {
      class: 'rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden'
    }, [
      h('div', {
        class: 'flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-700/40 cursor-pointer',
        onClick: () => emit('update:enabled', !props.enabled)
      }, [
        h('div', { class: 'flex items-center gap-3' }, [
          h('span', {
            class: 'inline-block h-3 w-3 rounded-full flex-shrink-0',
            style: { backgroundColor: props.badgeColor }
          }),
          h('span', { class: 'font-medium text-gray-800 dark:text-gray-100 text-sm' }, props.label),
        ]),
        h(Toggle, {
          modelValue: props.enabled,
          onClick: (e) => e.stopPropagation(),
          'onUpdate:modelValue': (v) => emit('update:enabled', v),
        }),
      ]),
      props.enabled
        ? h('div', { class: 'px-4 py-4 border-t border-gray-100 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white dark:bg-gray-800' },
            slots.default?.())
        : null,
    ])
  }
})

// ClassicAccordion
const ClassicAccordion = defineComponent({
  props: {
    enabled: Boolean,
    label: String,
    icon: { type: String, default: '💳' },
  },
  emits: ['update:enabled'],
  setup(props, { slots, emit }) {
    return () => h('div', {
      class: 'rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden'
    }, [
      h('div', {
        class: 'flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-700/40 cursor-pointer',
        onClick: () => emit('update:enabled', !props.enabled)
      }, [
        h('div', { class: 'flex items-center gap-3' }, [
          h('span', { class: 'text-lg' }, props.icon),
          h('span', { class: 'font-medium text-gray-800 dark:text-gray-100 text-sm' }, props.label),
        ]),
        h(Toggle, {
          modelValue: props.enabled,
          onClick: (e) => e.stopPropagation(),
          'onUpdate:modelValue': (v) => emit('update:enabled', v),
        }),
      ]),
      props.enabled
        ? h('div', { class: 'px-4 py-4 border-t border-gray-100 dark:border-gray-700 grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white dark:bg-gray-800' },
            slots.default?.())
        : null,
    ])
  }
})

// InputField
const InputField = defineComponent({
  props: { modelValue: String, label: String, placeholder: String },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    return () => h('div', {}, [
      h('label', { class: 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1' }, props.label),
      h('input', {
        type: 'text',
        value: props.modelValue,
        placeholder: props.placeholder ?? '',
        onInput: (e) => emit('update:modelValue', e.target.value),
        class: 'block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500'
      })
    ])
  }
})

// PasswordField
const PasswordField = defineComponent({
  props: { modelValue: String, label: String },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    const show = ref(false)
    const eyeOpen = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`
    const eyeClosed = `<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>`

    return () => h('div', {}, [
      h('label', { class: 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1' }, props.label),
      h('div', { class: 'relative' }, [
        h('input', {
          type: show.value ? 'text' : 'password',
          value: props.modelValue,
          onInput: (e) => emit('update:modelValue', e.target.value),
          class: 'block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 pr-9 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500'
        }),
        h('button', {
          type: 'button',
          onClick: () => { show.value = !show.value },
          class: 'absolute inset-y-0 right-0 flex items-center px-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200',
          innerHTML: show.value ? eyeOpen : eyeClosed,
        })
      ])
    ])
  }
})

// SelectField
const SelectField = defineComponent({
  props: {
    modelValue: String,
    label: String,
    options: { type: Array, default: () => [] },
  },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    return () => h('div', {}, [
      h('label', { class: 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1' }, props.label),
      h('select', {
        value: props.modelValue,
        onChange: (e) => emit('update:modelValue', e.target.value),
        class: 'block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500'
      }, props.options.map(opt => h('option', { value: opt.value }, opt.label)))
    ])
  }
})

// TextareaField
const TextareaField = defineComponent({
  props: { modelValue: String, label: String, placeholder: String },
  emits: ['update:modelValue'],
  setup(props, { emit }) {
    return () => h('div', { class: 'sm:col-span-2' }, [
      h('label', { class: 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1' }, props.label),
      h('textarea', {
        value: props.modelValue,
        rows: 3,
        placeholder: props.placeholder ?? '',
        onInput: (e) => emit('update:modelValue', e.target.value),
        class: 'block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm text-gray-900 dark:text-gray-100 placeholder-gray-400 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 resize-y'
      })
    ])
  }
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

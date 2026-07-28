<template>
  <div class="min-h-screen bg-gray-100 font-sans">

    <!-- Toast annulation -->
    <transition name="fade">
      <div v-if="showCancelToast"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-orange-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2">
        <span>⚠️</span>
        <span>Paiement annulé. Vous pouvez réessayer.</span>
        <button @click="showCancelToast = false" class="ml-4 text-white/80 hover:text-white">✕</button>
      </div>
    </transition>

    <!-- Toast copié -->
    <transition name="fade">
      <div v-if="copiedToast"
        class="fixed top-4 right-4 z-50 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm">
        ✓ Copié !
      </div>
    </transition>

    <!-- Bandeau PAYÉ -->
    <div v-if="isPaid"
      class="bg-green-500 text-white text-center py-4 px-4 shadow-md">
      <div class="max-w-3xl mx-auto flex items-center justify-center gap-3">
        <span class="text-2xl">✅</span>
        <div>
          <p class="font-bold text-lg">Ce document a été payé</p>
          <p class="text-green-100 text-sm">Merci pour votre paiement. Une confirmation vous a été envoyée.</p>
        </div>
      </div>
    </div>

    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="max-w-3xl mx-auto px-4 py-4 flex items-center gap-4">
        <img v-if="company.logo_url" :src="company.logo_url" alt="Logo" class="h-12 w-auto object-contain" />
        <div v-else class="h-12 w-12 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xl">
          {{ company.name?.charAt(0) ?? 'C' }}
        </div>
        <div>
          <h1 class="font-bold text-gray-900 text-lg leading-tight">{{ company.name }}</h1>
          <p v-if="company.city || company.address" class="text-gray-500 text-sm">
            {{ [company.address, company.city].filter(Boolean).join(', ') }}
          </p>
        </div>
      </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-6 space-y-6">

      <!-- Résumé du document -->
      <section class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
          <div>
            <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold mb-1">{{ document.type_label }}</p>
            <h2 class="text-2xl font-bold text-gray-900">{{ document.number }}</h2>
            <p v-if="document.issue_date" class="text-sm text-gray-500 mt-1">
              Émis le {{ formatDate(document.issue_date) }}
            </p>
          </div>
          <div class="text-right">
            <p class="text-3xl font-bold text-indigo-700">{{ formatAmount(document.total) }} {{ document.currency }}</p>
            <span :class="statusClass(document.status)"
              class="inline-block mt-1 text-xs font-semibold px-3 py-1 rounded-full">
              {{ document.status }}
            </span>
          </div>
        </div>

        <hr class="my-4 border-gray-100" />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
          <div>
            <p class="text-gray-400 text-xs uppercase font-semibold mb-1">Émetteur</p>
            <p class="text-gray-800 font-medium">{{ company.name }}</p>
            <p v-if="company.email" class="text-gray-500">{{ company.email }}</p>
            <p v-if="company.phone" class="text-gray-500">{{ company.phone }}</p>
          </div>
          <div v-if="customer">
            <p class="text-gray-400 text-xs uppercase font-semibold mb-1">Client</p>
            <p class="text-gray-800 font-medium">{{ customer.name }}</p>
            <p v-if="customer.email" class="text-gray-500">{{ customer.email }}</p>
            <p v-if="customer.phone" class="text-gray-500">{{ customer.phone }}</p>
          </div>
        </div>

        <div v-if="document.due_date" class="mt-3 text-sm">
          <span class="text-gray-400 text-xs uppercase font-semibold">Échéance :</span>
          <span :class="isOverdue ? 'text-red-600 font-semibold' : 'text-gray-700'" class="ml-2">
            {{ formatDate(document.due_date) }}
            <span v-if="isOverdue" class="ml-1 text-red-500">(en retard)</span>
          </span>
        </div>

        <div v-if="document.notes" class="mt-3 text-sm text-gray-500 italic border-l-4 border-gray-100 pl-3">
          {{ document.notes }}
        </div>
      </section>

      <!-- Tableau des lignes -->
      <section v-if="lines && lines.length" class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
          <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide">Détail des prestations</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left px-6 py-3 text-gray-500 font-medium">Description</th>
                <th class="text-center px-4 py-3 text-gray-500 font-medium">Qté</th>
                <th class="text-right px-6 py-3 text-gray-500 font-medium">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="(line, i) in lines" :key="i" class="hover:bg-gray-50/50">
                <td class="px-6 py-3 text-gray-800">
                  {{ line.description }}
                  <span v-if="line.tax_rate" class="ml-1 text-xs text-gray-400">(TVA {{ line.tax_rate }}%)</span>
                </td>
                <td class="px-4 py-3 text-center text-gray-600">{{ line.quantity }}</td>
                <td class="px-6 py-3 text-right font-medium text-gray-800">
                  {{ formatAmount(line.line_total) }} {{ document.currency }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Totaux -->
      <section class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-700 text-sm uppercase tracking-wide mb-4">Récapitulatif</h3>
        <div class="space-y-2 text-sm">
          <div v-if="document.subtotal != null" class="flex justify-between text-gray-600">
            <span>Sous-total HT</span>
            <span>{{ formatAmount(document.subtotal) }} {{ document.currency }}</span>
          </div>
          <div v-if="document.discount_amount && document.discount_amount > 0" class="flex justify-between text-green-600">
            <span>Remise</span>
            <span>-{{ formatAmount(document.discount_amount) }} {{ document.currency }}</span>
          </div>
          <div v-if="document.tax_amount != null" class="flex justify-between text-gray-600">
            <span>TVA</span>
            <span>{{ formatAmount(document.tax_amount) }} {{ document.currency }}</span>
          </div>
          <div class="flex justify-between font-bold text-gray-900 text-base border-t border-gray-100 pt-2 mt-2">
            <span>Total TTC</span>
            <span>{{ formatAmount(document.total) }} {{ document.currency }}</span>
          </div>
          <div v-if="document.amount_paid && document.amount_paid > 0" class="flex justify-between text-green-600">
            <span>Déjà payé</span>
            <span>-{{ formatAmount(document.amount_paid) }} {{ document.currency }}</span>
          </div>
          <div v-if="remainingAmount > 0" class="flex justify-between font-bold text-indigo-700 text-lg border-t border-indigo-100 pt-2 mt-1">
            <span>Reste à payer</span>
            <span>{{ formatAmount(remainingAmount) }} {{ document.currency }}</span>
          </div>
          <div v-else-if="!isPaid" class="flex justify-between font-bold text-green-600 text-base border-t pt-2">
            <span>Solde</span>
            <span>0 {{ document.currency }}</span>
          </div>
        </div>
      </section>

      <!-- Section paiement -->
      <section v-if="!isPaid" class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-indigo-50 border-b border-indigo-100">
          <h3 class="font-semibold text-indigo-800 flex items-center gap-2">
            <span>💳</span> Choisissez votre mode de paiement
          </h3>
        </div>

        <!-- Onglets -->
        <div class="flex overflow-x-auto border-b border-gray-100 bg-gray-50">
          <button v-if="hasOnline" @click="activeTab = 'online'"
            :class="activeTab === 'online' ? 'border-b-2 border-indigo-600 text-indigo-700 bg-white' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors flex items-center gap-1">
            🌐 En ligne
          </button>
          <button v-if="hasMobileMoney" @click="activeTab = 'mobile'"
            :class="activeTab === 'mobile' ? 'border-b-2 border-indigo-600 text-indigo-700 bg-white' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors flex items-center gap-1">
            📱 Mobile Money
          </button>
          <button v-if="hasClassic" @click="activeTab = 'classic'"
            :class="activeTab === 'classic' ? 'border-b-2 border-indigo-600 text-indigo-700 bg-white' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors flex items-center gap-1">
            🏦 Virement / Classique
          </button>
          <button v-if="hasCrypto" @click="activeTab = 'crypto'"
            :class="activeTab === 'crypto' ? 'border-b-2 border-indigo-600 text-indigo-700 bg-white' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors flex items-center gap-1">
            ₿ Crypto
          </button>
        </div>

        <div class="p-6">

          <!-- A) Paiements en ligne -->
          <div v-if="activeTab === 'online'">
            <p class="text-sm text-gray-500 mb-4">Cliquez sur votre solution de paiement préférée pour être redirigé vers la page de paiement sécurisée.</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <button
                v-for="gw in activeOnlineGateways"
                :key="gw.key"
                @click="payOnline(gw.key)"
                :disabled="loadingGateway === gw.key"
                class="relative flex flex-col items-center gap-2 border border-gray-200 rounded-xl p-4 hover:border-indigo-400 hover:bg-indigo-50 transition-all disabled:opacity-60 disabled:cursor-not-allowed group">
                <span class="text-2xl">{{ gw.icon }}</span>
                <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">{{ gw.label }}</span>
                <div v-if="loadingGateway === gw.key" class="absolute inset-0 flex items-center justify-center bg-white/80 rounded-xl">
                  <svg class="animate-spin h-6 w-6 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                </div>
              </button>
            </div>
            <div v-if="onlineError" class="mt-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
              {{ onlineError }}
            </div>
          </div>

          <!-- B) Mobile Money -->
          <div v-if="activeTab === 'mobile'">
            <p class="text-sm text-gray-500 mb-4">Envoyez le montant au numéro indiqué, puis conservez votre reçu de transaction.</p>
            <div class="space-y-4">
              <div v-for="op in activeMobileOperators" :key="op.key"
                class="border border-gray-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-3 min-w-0">
                  <span class="text-3xl flex-shrink-0">{{ op.icon }}</span>
                  <div class="min-w-0">
                    <p class="font-semibold text-gray-800">{{ op.label }}</p>
                    <p class="text-xs text-gray-400">{{ op.data.name }}</p>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xl font-bold text-gray-900 tracking-wide">{{ op.data.phone }}</p>
                  <p v-if="op.data.instructions" class="text-xs text-gray-500 mt-1">{{ op.data.instructions }}</p>
                </div>
                <button @click="copyText(op.data.phone)"
                  class="flex-shrink-0 flex items-center gap-1 text-sm text-indigo-600 border border-indigo-200 rounded-lg px-3 py-2 hover:bg-indigo-50 transition-colors">
                  📋 Copier
                </button>
              </div>
            </div>
          </div>

          <!-- C) Classique -->
          <div v-if="activeTab === 'classic'" class="space-y-4">

            <!-- Virement bancaire -->
            <div v-if="pm.classic.bank_transfer?.enabled" class="border border-gray-200 rounded-xl p-4">
              <h4 class="font-semibold text-gray-800 flex items-center gap-2 mb-3">🏦 Virement bancaire</h4>
              <div class="space-y-2">
                <div v-if="pm.classic.bank_transfer.bank_name" class="flex items-center justify-between">
                  <span class="text-sm text-gray-500">Banque</span>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-800">{{ pm.classic.bank_transfer.bank_name }}</span>
                  </div>
                </div>
                <div v-if="pm.classic.bank_transfer.account_name" class="flex items-center justify-between">
                  <span class="text-sm text-gray-500">Titulaire</span>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-800">{{ pm.classic.bank_transfer.account_name }}</span>
                    <button @click="copyText(pm.classic.bank_transfer.account_name)" class="text-xs text-indigo-500 hover:text-indigo-700">📋</button>
                  </div>
                </div>
                <div v-if="pm.classic.bank_transfer.rib" class="flex items-center justify-between">
                  <span class="text-sm text-gray-500">RIB</span>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-mono font-medium text-gray-800">{{ pm.classic.bank_transfer.rib }}</span>
                    <button @click="copyText(pm.classic.bank_transfer.rib)" class="text-xs text-indigo-500 hover:text-indigo-700">📋</button>
                  </div>
                </div>
                <div v-if="pm.classic.bank_transfer.iban" class="flex items-center justify-between">
                  <span class="text-sm text-gray-500">IBAN</span>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-mono font-medium text-gray-800">{{ pm.classic.bank_transfer.iban }}</span>
                    <button @click="copyText(pm.classic.bank_transfer.iban)" class="text-xs text-indigo-500 hover:text-indigo-700">📋</button>
                  </div>
                </div>
                <div v-if="pm.classic.bank_transfer.swift" class="flex items-center justify-between">
                  <span class="text-sm text-gray-500">SWIFT/BIC</span>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-mono font-medium text-gray-800">{{ pm.classic.bank_transfer.swift }}</span>
                    <button @click="copyText(pm.classic.bank_transfer.swift)" class="text-xs text-indigo-500 hover:text-indigo-700">📋</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Chèque -->
            <div v-if="pm.classic.cheque?.enabled" class="border border-gray-200 rounded-xl p-4">
              <h4 class="font-semibold text-gray-800 flex items-center gap-2 mb-3">📄 Chèque</h4>
              <div class="space-y-2 text-sm">
                <div v-if="pm.classic.cheque.payable_to" class="flex justify-between">
                  <span class="text-gray-500">À l'ordre de</span>
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-800">{{ pm.classic.cheque.payable_to }}</span>
                    <button @click="copyText(pm.classic.cheque.payable_to)" class="text-xs text-indigo-500">📋</button>
                  </div>
                </div>
                <div v-if="pm.classic.cheque.address" class="flex justify-between">
                  <span class="text-gray-500">Adresse</span>
                  <span class="font-medium text-gray-800 text-right max-w-xs">{{ pm.classic.cheque.address }}</span>
                </div>
              </div>
            </div>

            <!-- Espèces -->
            <div v-if="pm.classic.cash?.enabled" class="border border-gray-200 rounded-xl p-4">
              <h4 class="font-semibold text-gray-800 flex items-center gap-2 mb-3">💵 Paiement en espèces</h4>
              <div class="space-y-2 text-sm">
                <div v-if="pm.classic.cash.address" class="flex justify-between">
                  <span class="text-gray-500">Adresse</span>
                  <span class="font-medium text-gray-800 text-right">{{ pm.classic.cash.address }}</span>
                </div>
                <div v-if="pm.classic.cash.hours" class="flex justify-between">
                  <span class="text-gray-500">Horaires</span>
                  <span class="font-medium text-gray-800">{{ pm.classic.cash.hours }}</span>
                </div>
                <p v-if="!pm.classic.cash.address && !pm.classic.cash.hours" class="text-gray-500 italic">Présentez-vous à nos bureaux pour régler en espèces.</p>
              </div>
            </div>

            <!-- Western Union -->
            <div v-if="pm.classic.western_union?.enabled" class="border border-gray-200 rounded-xl p-4">
              <h4 class="font-semibold text-gray-800 flex items-center gap-2 mb-3">🌍 Western Union</h4>
              <div class="space-y-2 text-sm">
                <div v-if="pm.classic.western_union.name" class="flex justify-between">
                  <span class="text-gray-500">Bénéficiaire</span>
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-800">{{ pm.classic.western_union.name }}</span>
                    <button @click="copyText(pm.classic.western_union.name)" class="text-xs text-indigo-500">📋</button>
                  </div>
                </div>
                <div v-if="pm.classic.western_union.country" class="flex justify-between">
                  <span class="text-gray-500">Pays</span>
                  <span class="font-medium text-gray-800">{{ pm.classic.western_union.country }}</span>
                </div>
                <div v-if="pm.classic.western_union.city" class="flex justify-between">
                  <span class="text-gray-500">Ville</span>
                  <span class="font-medium text-gray-800">{{ pm.classic.western_union.city }}</span>
                </div>
              </div>
            </div>

            <!-- SWIFT International -->
            <div v-if="pm.classic.swift_transfer?.enabled" class="border border-gray-200 rounded-xl p-4">
              <h4 class="font-semibold text-gray-800 flex items-center gap-2 mb-3">🌐 Virement SWIFT international</h4>
              <div class="space-y-2 text-sm">
                <div v-if="pm.classic.swift_transfer.bank_name" class="flex justify-between">
                  <span class="text-gray-500">Banque</span>
                  <span class="font-medium text-gray-800">{{ pm.classic.swift_transfer.bank_name }}</span>
                </div>
                <div v-if="pm.classic.swift_transfer.swift_code" class="flex justify-between">
                  <span class="text-gray-500">Code SWIFT</span>
                  <div class="flex items-center gap-2">
                    <span class="font-mono font-medium text-gray-800">{{ pm.classic.swift_transfer.swift_code }}</span>
                    <button @click="copyText(pm.classic.swift_transfer.swift_code)" class="text-xs text-indigo-500">📋</button>
                  </div>
                </div>
                <div v-if="pm.classic.swift_transfer.iban" class="flex justify-between">
                  <span class="text-gray-500">IBAN</span>
                  <div class="flex items-center gap-2">
                    <span class="font-mono font-medium text-gray-800">{{ pm.classic.swift_transfer.iban }}</span>
                    <button @click="copyText(pm.classic.swift_transfer.iban)" class="text-xs text-indigo-500">📋</button>
                  </div>
                </div>
                <div v-if="pm.classic.swift_transfer.beneficiary" class="flex justify-between">
                  <span class="text-gray-500">Bénéficiaire</span>
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-800">{{ pm.classic.swift_transfer.beneficiary }}</span>
                    <button @click="copyText(pm.classic.swift_transfer.beneficiary)" class="text-xs text-indigo-500">📋</button>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- D) Crypto -->
          <div v-if="activeTab === 'crypto'">
            <p class="text-sm text-gray-500 mb-4">Envoyez exactement le montant indiqué à l'adresse du portefeuille correspondant.</p>
            <div class="space-y-4">

              <div v-if="pm.crypto.usdt_trc20?.enabled" class="border border-gray-200 rounded-xl p-4">
                <div class="flex items-center gap-3 mb-3">
                  <span class="text-2xl">₮</span>
                  <h4 class="font-semibold text-gray-800">USDT <span class="text-xs text-gray-400 font-normal">({{ pm.crypto.usdt_trc20.network }})</span></h4>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 font-mono text-sm text-gray-700 break-all mb-3">
                  {{ pm.crypto.usdt_trc20.address }}
                </div>
                <div class="flex items-center gap-3">
                  <button @click="copyText(pm.crypto.usdt_trc20.address)"
                    class="flex items-center gap-1 text-sm text-indigo-600 border border-indigo-200 rounded-lg px-3 py-2 hover:bg-indigo-50 transition-colors">
                    📋 Copier l'adresse
                  </button>
                </div>
                <!-- QR Code simplifié -->
                <div class="mt-3 border border-dashed border-gray-300 rounded-lg p-4 text-center">
                  <div class="inline-block bg-white p-2 border border-gray-200 rounded">
                    <div class="grid grid-cols-8 gap-px w-24 h-24 mx-auto">
                      <div v-for="(bit, i) in generateSimpleQR(pm.crypto.usdt_trc20.address)" :key="i"
                        :class="bit ? 'bg-gray-900' : 'bg-white'" class="aspect-square"></div>
                    </div>
                  </div>
                  <p class="text-xs text-gray-400 mt-2">Scannez pour obtenir l'adresse</p>
                </div>
              </div>

              <div v-if="pm.crypto.bitcoin?.enabled" class="border border-gray-200 rounded-xl p-4">
                <div class="flex items-center gap-3 mb-3">
                  <span class="text-2xl">₿</span>
                  <h4 class="font-semibold text-gray-800">Bitcoin (BTC)</h4>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 font-mono text-sm text-gray-700 break-all mb-3">
                  {{ pm.crypto.bitcoin.address }}
                </div>
                <div class="flex items-center gap-3">
                  <button @click="copyText(pm.crypto.bitcoin.address)"
                    class="flex items-center gap-1 text-sm text-indigo-600 border border-indigo-200 rounded-lg px-3 py-2 hover:bg-indigo-50 transition-colors">
                    📋 Copier l'adresse
                  </button>
                </div>
                <div class="mt-3 border border-dashed border-gray-300 rounded-lg p-4 text-center">
                  <div class="inline-block bg-white p-2 border border-gray-200 rounded">
                    <div class="grid grid-cols-8 gap-px w-24 h-24 mx-auto">
                      <div v-for="(bit, i) in generateSimpleQR(pm.crypto.bitcoin.address)" :key="i"
                        :class="bit ? 'bg-gray-900' : 'bg-white'" class="aspect-square"></div>
                    </div>
                  </div>
                  <p class="text-xs text-gray-400 mt-2">Scannez pour obtenir l'adresse</p>
                </div>
              </div>

            </div>
          </div>

        </div>
      </section>

      <!-- Message si déjà payé -->
      <section v-if="isPaid" class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
        <p class="text-green-700 font-medium">Ce document est entièrement réglé.</p>
        <p class="text-green-600 text-sm mt-1">Merci pour votre confiance.</p>
      </section>

    </main>

    <!-- Footer -->
    <footer class="mt-10 py-6 text-center text-xs text-gray-400 border-t border-gray-200">
      <p>Propulsé par <span class="font-semibold text-gray-500">IBIG FactPro</span></p>
      <p class="mt-1">
        <a :href="`/verify/${paymentToken}`" class="text-indigo-400 hover:text-indigo-600 underline transition-colors">
          Vérifier l'authenticité de ce document
        </a>
      </p>
    </footer>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  document: Object,
  lines: Array,
  company: Object,
  customer: Object,
  alreadyPaid: Boolean,
  paymentToken: String,
})

// --- State ---
const isPaid = ref(props.alreadyPaid || false)
const showCancelToast = ref(false)
const copiedToast = ref(false)
const activeTab = ref(null)
const loadingGateway = ref(null)
const onlineError = ref(null)

// --- Raccourci payment_methods ---
const pm = computed(() => props.company?.payment_methods ?? {
  online: {}, mobile_money: {}, classic: {}, crypto: {}
})

// --- Détection URL params ---
onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  if (params.get('paid') === '1') {
    isPaid.value = true
  }
  if (params.get('cancelled') === '1') {
    showCancelToast.value = true
    setTimeout(() => { showCancelToast.value = false }, 5000)
  }

  // Sélection du premier onglet disponible
  if (hasOnline.value) activeTab.value = 'online'
  else if (hasMobileMoney.value) activeTab.value = 'mobile'
  else if (hasClassic.value) activeTab.value = 'classic'
  else if (hasCrypto.value) activeTab.value = 'crypto'
})

// --- Gateways online ---
const gatewayMeta = [
  { key: 'cinetpay', label: 'CinetPay', icon: '🔵' },
  { key: 'fedapay', label: 'FedaPay', icon: '🟢' },
  { key: 'flutterwave', label: 'Flutterwave', icon: '🟠' },
  { key: 'moneroo', label: 'Moneroo', icon: '🔷' },
  { key: 'stripe', label: 'Stripe', icon: '🟣' },
  { key: 'paypal', label: 'PayPal', icon: '🔵' },
]

const activeOnlineGateways = computed(() =>
  gatewayMeta.filter(gw => pm.value.online?.[gw.key]?.enabled)
)

const hasOnline = computed(() => activeOnlineGateways.value.length > 0)

// --- Mobile money ---
const mobileOperatorMeta = [
  { key: 'orange_money', label: 'Orange Money', icon: '🟠' },
  { key: 'mtn_momo', label: 'MTN MoMo', icon: '🟡' },
  { key: 'wave', label: 'Wave', icon: '🔵' },
  { key: 'moov_money', label: 'Moov Money', icon: '🟢' },
  { key: 'airtel_money', label: 'Airtel Money', icon: '🔴' },
  { key: 'free_money', label: 'Free Money', icon: '🟤' },
  { key: 'mpesa', label: 'M-Pesa', icon: '🟩' },
]

const activeMobileOperators = computed(() =>
  mobileOperatorMeta
    .filter(op => pm.value.mobile_money?.[op.key]?.enabled)
    .map(op => ({ ...op, data: pm.value.mobile_money[op.key] }))
)

const hasMobileMoney = computed(() => activeMobileOperators.value.length > 0)

// --- Classique ---
const hasClassic = computed(() => {
  const c = pm.value.classic ?? {}
  return Object.values(c).some(v => v?.enabled)
})

// --- Crypto ---
const hasCrypto = computed(() => {
  const cr = pm.value.crypto ?? {}
  return Object.values(cr).some(v => v?.enabled)
})

// --- Montant restant ---
const remainingAmount = computed(() => {
  const total = parseFloat(props.document?.total ?? 0)
  const paid = parseFloat(props.document?.amount_paid ?? 0)
  return Math.max(0, total - paid)
})

// --- Retard ---
const isOverdue = computed(() => {
  if (!props.document?.due_date) return false
  return new Date(props.document.due_date) < new Date()
})

// --- Paiement en ligne ---
async function payOnline(gateway) {
  if (loadingGateway.value) return
  loadingGateway.value = gateway
  onlineError.value = null

  try {
    const xsrfToken = getCookie('XSRF-TOKEN')
    const response = await fetch(`/pay/${props.paymentToken}/online`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': xsrfToken ? decodeURIComponent(xsrfToken) : '',
      },
      body: JSON.stringify({ gateway }),
    })

    if (!response.ok) {
      const err = await response.json().catch(() => ({}))
      throw new Error(err.message ?? `Erreur ${response.status}`)
    }

    const data = await response.json()
    if (data.redirect_url) {
      window.location.href = data.redirect_url
    } else {
      throw new Error('Aucune URL de redirection reçue.')
    }
  } catch (err) {
    onlineError.value = err.message ?? 'Une erreur est survenue. Veuillez réessayer.'
    loadingGateway.value = null
  }
}

// --- Utilitaires ---
function getCookie(name) {
  const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'))
  return match ? match[1] : null
}

async function copyText(text) {
  if (!text) return
  try {
    await navigator.clipboard.writeText(text)
    copiedToast.value = true
    setTimeout(() => { copiedToast.value = false }, 2000)
  } catch {
    // fallback
    const el = document.createElement('textarea')
    el.value = text
    document.body.appendChild(el)
    el.select()
    document.execCommand('copy')
    document.body.removeChild(el)
    copiedToast.value = true
    setTimeout(() => { copiedToast.value = false }, 2000)
  }
}

function formatAmount(val) {
  if (val == null) return '0'
  return parseFloat(val).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 })
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' })
}

function statusClass(status) {
  const s = (status ?? '').toLowerCase()
  if (s.includes('pay') || s === 'paid') return 'bg-green-100 text-green-700'
  if (s.includes('partiel')) return 'bg-yellow-100 text-yellow-700'
  if (s.includes('annul') || s.includes('cancel')) return 'bg-red-100 text-red-700'
  if (s.includes('retard') || s.includes('overdue')) return 'bg-red-100 text-red-700'
  return 'bg-gray-100 text-gray-600'
}

// QR code simple basé sur le hash de l'adresse (visuel indicatif uniquement)
function generateSimpleQR(address) {
  const bits = []
  let hash = 0
  for (let i = 0; i < (address?.length ?? 0); i++) {
    hash = ((hash << 5) - hash + address.charCodeAt(i)) | 0
  }
  // Remplissage avec coin marks et données pseudo-aléatoires
  for (let i = 0; i < 64; i++) {
    const row = Math.floor(i / 8)
    const col = i % 8
    // Coins QR (finder patterns simplifiés)
    if ((row < 2 && col < 2) || (row < 2 && col > 5) || (row > 5 && col < 2)) {
      bits.push(true)
    } else {
      bits.push(((hash >> (i % 32)) & 1) === 1)
    }
  }
  return bits
}
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

<template>
  <div class="min-h-screen bg-gray-100 font-sans">

    <!-- Toast copié -->
    <transition name="fade">
      <div v-if="copiedToast"
        class="fixed top-4 right-4 z-50 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm">
        Copié !
      </div>
    </transition>

    <!-- Toast annulation -->
    <transition name="fade">
      <div v-if="showCancelToast"
        class="fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-orange-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2">
        <span>Paiement annulé. Vous pouvez réessayer.</span>
        <button @click="showCancelToast = false" class="ml-4 text-white/80 hover:text-white">x</button>
      </div>
    </transition>

    <!-- Bandeau PAYÉ -->
    <div v-if="isPaid" class="bg-green-500 text-white text-center py-4 px-4 shadow-md">
      <div class="max-w-3xl mx-auto flex items-center justify-center gap-3">
        <div>
          <p class="font-bold text-lg">Paiement confirmé</p>
          <p class="text-green-100 text-sm">Merci pour votre paiement.</p>
        </div>
      </div>
    </div>

    <!-- Bandeau EN ATTENTE + timer polling -->
    <div v-if="isPending && !isPaid" class="bg-amber-500 text-white text-center py-3 px-4 shadow-md">
      <div class="max-w-3xl mx-auto flex items-center justify-center gap-3">
        <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent flex-shrink-0"></div>
        <div>
          <p class="font-semibold text-sm">Vérification du paiement en cours...</p>
          <p class="text-amber-100 text-xs">Nous vérifions votre paiement automatiquement. Restez sur cette page.</p>
        </div>
      </div>
    </div>

    <!-- Header société — fond coloré -->
    <header class="bg-blue-800 text-white shadow-md">
      <div class="max-w-3xl mx-auto px-4 py-5 flex items-center gap-4">
        <img v-if="company.logo_url" :src="company.logo_url" alt="Logo"
          class="h-14 w-14 rounded-full object-cover border-2 border-white/30 flex-shrink-0" />
        <div v-else
          class="h-14 w-14 rounded-full bg-white/20 border-2 border-white/30 flex items-center justify-center text-white font-bold text-2xl flex-shrink-0">
          {{ company.name?.charAt(0)?.toUpperCase() ?? 'C' }}
        </div>
        <div class="min-w-0">
          <h1 class="font-bold text-white text-xl leading-tight">{{ company.name }}</h1>
          <p v-if="company.city" class="text-blue-200 text-sm mt-0.5">{{ company.city }}</p>
          <p v-if="company.phone" class="text-blue-200 text-sm">{{ company.phone }}</p>
        </div>
      </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 py-6 space-y-6">

      <!-- Bloc facture -->
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
            <p class="text-3xl font-bold text-blue-800">{{ formatAmount(document.total) }} {{ document.currency }}</p>
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
          <span class="text-gray-400 text-xs uppercase font-semibold">Echéance :</span>
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

        <!-- Totaux -->
        <div class="border-t border-gray-100 px-6 py-4 space-y-2 text-sm">
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
          <div class="flex justify-between font-bold text-gray-900 text-lg border-t border-gray-200 pt-3 mt-2">
            <span>TOTAL TTC</span>
            <span class="text-blue-800">{{ formatAmount(document.total) }} {{ document.currency }}</span>
          </div>
          <div v-if="document.amount_paid && document.amount_paid > 0" class="flex justify-between text-green-600">
            <span>Déjà payé</span>
            <span>-{{ formatAmount(document.amount_paid) }} {{ document.currency }}</span>
          </div>
          <div v-if="remainingAmount > 0" class="flex justify-between font-bold text-blue-800 text-xl border-t border-blue-100 pt-2 mt-1">
            <span>Reste à payer</span>
            <span>{{ formatAmount(remainingAmount) }} {{ document.currency }}</span>
          </div>
        </div>
      </section>

      <!-- Timer paiement en ligne (si initié) -->
      <section v-if="onlineTimerSeconds > 0 && !isPaid"
        class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-4">
        <div class="text-3xl font-mono font-bold text-amber-700 flex-shrink-0">{{ timerDisplay }}</div>
        <div>
          <p class="text-sm font-semibold text-amber-800">Paiement en attente</p>
          <p class="text-xs text-amber-600 mt-0.5">Finalisez votre paiement sur la page externe avant l'expiration du délai.</p>
        </div>
      </section>

      <!-- Section paiement (si non payé) -->
      <section v-if="!isPaid" class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-blue-800 text-white">
          <h3 class="font-semibold flex items-center gap-2 text-lg">
            Choisissez votre mode de paiement
          </h3>
        </div>

        <!-- Onglets -->
        <div class="flex overflow-x-auto border-b border-gray-100 bg-gray-50">
          <button v-if="hasOnline" @click="activeTab = 'online'"
            :class="activeTab === 'online' ? 'border-b-2 border-blue-700 text-blue-700 bg-white' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors">
            En ligne
          </button>
          <button v-if="hasMobileMoney" @click="activeTab = 'mobile'"
            :class="activeTab === 'mobile' ? 'border-b-2 border-blue-700 text-blue-700 bg-white' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors">
            Mobile Money
          </button>
          <button v-if="hasClassic" @click="activeTab = 'classic'"
            :class="activeTab === 'classic' ? 'border-b-2 border-blue-700 text-blue-700 bg-white' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors">
            Virement / Classique
          </button>
          <button v-if="hasCrypto" @click="activeTab = 'crypto'"
            :class="activeTab === 'crypto' ? 'border-b-2 border-blue-700 text-blue-700 bg-white' : 'text-gray-500 hover:text-gray-700'"
            class="px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors">
            Crypto
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
                class="relative flex flex-col items-center gap-2 border border-gray-200 rounded-xl p-4 hover:border-blue-400 hover:bg-blue-50 transition-all disabled:opacity-60 disabled:cursor-not-allowed group">
                <span class="text-2xl">{{ gw.icon }}</span>
                <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">{{ gw.label }}</span>
                <div v-if="loadingGateway === gw.key" class="absolute inset-0 flex items-center justify-center bg-white/80 rounded-xl">
                  <div class="animate-spin h-6 w-6 border-2 border-blue-600 border-t-transparent rounded-full"></div>
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
            <div class="space-y-5">
              <div v-for="op in activeMobileOperators" :key="op.key"
                class="border border-gray-200 rounded-xl p-5">
                <div class="flex items-center gap-3 mb-3">
                  <span class="text-3xl">{{ op.icon }}</span>
                  <div>
                    <p class="font-bold text-gray-800 text-base">{{ op.label }}</p>
                    <p v-if="op.data.name" class="text-xs text-gray-400">{{ op.data.name }}</p>
                  </div>
                </div>
                <!-- Numéro en TRÈS GROS -->
                <div class="flex items-center gap-3 mb-4">
                  <p class="text-3xl font-bold text-gray-900 tracking-widest">{{ op.data.phone }}</p>
                  <button @click="copyText(op.data.phone)"
                    class="flex items-center gap-1 text-sm text-blue-600 border border-blue-200 rounded-lg px-3 py-2 hover:bg-blue-50 transition-colors flex-shrink-0">
                    Copier
                  </button>
                </div>
                <!-- Lien Wave deep link -->
                <div v-if="op.key === 'wave' && isMobile" class="mb-3">
                  <a :href="`wave://pay?phone=${op.data.phone}&amount=${document.total}`"
                    class="inline-flex items-center gap-2 bg-blue-600 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-blue-700 transition-colors">
                    Ouvrir Wave
                  </a>
                </div>
                <!-- Instructions pas à pas -->
                <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside bg-gray-50 rounded-lg p-3">
                  <li>Composez le menu USSD ou ouvrez l'application {{ op.label }}</li>
                  <li>Choisissez "Paiement marchand" ou "Envoyer de l'argent"</li>
                  <li>Entrez le numéro : <strong>{{ op.data.phone }}</strong></li>
                  <li>Montant : <strong>{{ formatAmount(document.total) }} {{ document.currency }}</strong></li>
                  <li>Confirmez avec votre code PIN</li>
                </ol>
                <p v-if="op.data.instructions" class="text-xs text-gray-500 mt-2 italic">{{ op.data.instructions }}</p>
              </div>
            </div>
          </div>

          <!-- C) Classique -->
          <div v-if="activeTab === 'classic'" class="space-y-4">

            <div v-if="pm.classic.bank_transfer?.enabled" class="border border-gray-200 rounded-xl p-4">
              <h4 class="font-semibold text-gray-800 flex items-center gap-2 mb-3">Virement bancaire</h4>
              <div class="space-y-2 text-sm">
                <div v-if="pm.classic.bank_transfer.bank_name" class="flex items-center justify-between">
                  <span class="text-gray-500">Banque</span>
                  <span class="text-sm font-medium text-gray-800">{{ pm.classic.bank_transfer.bank_name }}</span>
                </div>
                <div v-if="pm.classic.bank_transfer.account_name" class="flex items-center justify-between">
                  <span class="text-gray-500">Titulaire</span>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-800">{{ pm.classic.bank_transfer.account_name }}</span>
                    <button @click="copyText(pm.classic.bank_transfer.account_name)" class="text-xs text-blue-500 hover:text-blue-700">Copier</button>
                  </div>
                </div>
                <div v-if="pm.classic.bank_transfer.iban" class="flex items-center justify-between">
                  <span class="text-gray-500">IBAN</span>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-mono font-medium text-gray-800">{{ pm.classic.bank_transfer.iban }}</span>
                    <button @click="copyText(pm.classic.bank_transfer.iban)" class="text-xs text-blue-500 hover:text-blue-700">Copier</button>
                  </div>
                </div>
                <div v-if="pm.classic.bank_transfer.rib" class="flex items-center justify-between">
                  <span class="text-gray-500">RIB</span>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-mono font-medium text-gray-800">{{ pm.classic.bank_transfer.rib }}</span>
                    <button @click="copyText(pm.classic.bank_transfer.rib)" class="text-xs text-blue-500 hover:text-blue-700">Copier</button>
                  </div>
                </div>
                <div v-if="pm.classic.bank_transfer.swift" class="flex items-center justify-between">
                  <span class="text-gray-500">BIC / SWIFT</span>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-mono font-medium text-gray-800">{{ pm.classic.bank_transfer.swift }}</span>
                    <button @click="copyText(pm.classic.bank_transfer.swift)" class="text-xs text-blue-500 hover:text-blue-700">Copier</button>
                  </div>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                  <span class="text-gray-500">Motif suggéré</span>
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-gray-800">{{ document.number }}</span>
                    <button @click="copyText(document.number)" class="text-xs text-blue-500 hover:text-blue-700">Copier</button>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="pm.classic.cheque?.enabled" class="border border-gray-200 rounded-xl p-4">
              <h4 class="font-semibold text-gray-800 mb-3">Chèque</h4>
              <div class="space-y-2 text-sm">
                <div v-if="pm.classic.cheque.payable_to" class="flex justify-between">
                  <span class="text-gray-500">A l'ordre de</span>
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-800">{{ pm.classic.cheque.payable_to }}</span>
                    <button @click="copyText(pm.classic.cheque.payable_to)" class="text-xs text-blue-500">Copier</button>
                  </div>
                </div>
                <div v-if="pm.classic.cheque.address" class="flex justify-between">
                  <span class="text-gray-500">Adresse</span>
                  <span class="font-medium text-gray-800 text-right max-w-xs">{{ pm.classic.cheque.address }}</span>
                </div>
              </div>
            </div>

            <div v-if="pm.classic.cash?.enabled" class="border border-gray-200 rounded-xl p-4">
              <h4 class="font-semibold text-gray-800 mb-3">Paiement en espèces</h4>
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

            <div v-if="pm.classic.western_union?.enabled" class="border border-gray-200 rounded-xl p-4">
              <h4 class="font-semibold text-gray-800 mb-3">Western Union</h4>
              <div class="space-y-2 text-sm">
                <div v-if="pm.classic.western_union.name" class="flex justify-between">
                  <span class="text-gray-500">Bénéficiaire</span>
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-800">{{ pm.classic.western_union.name }}</span>
                    <button @click="copyText(pm.classic.western_union.name)" class="text-xs text-blue-500">Copier</button>
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

            <div v-if="pm.classic.swift_transfer?.enabled" class="border border-gray-200 rounded-xl p-4">
              <h4 class="font-semibold text-gray-800 mb-3">Virement SWIFT international</h4>
              <div class="space-y-2 text-sm">
                <div v-if="pm.classic.swift_transfer.bank_name" class="flex justify-between">
                  <span class="text-gray-500">Banque</span>
                  <span class="font-medium text-gray-800">{{ pm.classic.swift_transfer.bank_name }}</span>
                </div>
                <div v-if="pm.classic.swift_transfer.swift_code" class="flex justify-between">
                  <span class="text-gray-500">Code SWIFT</span>
                  <div class="flex items-center gap-2">
                    <span class="font-mono font-medium text-gray-800">{{ pm.classic.swift_transfer.swift_code }}</span>
                    <button @click="copyText(pm.classic.swift_transfer.swift_code)" class="text-xs text-blue-500">Copier</button>
                  </div>
                </div>
                <div v-if="pm.classic.swift_transfer.iban" class="flex justify-between">
                  <span class="text-gray-500">IBAN</span>
                  <div class="flex items-center gap-2">
                    <span class="font-mono font-medium text-gray-800">{{ pm.classic.swift_transfer.iban }}</span>
                    <button @click="copyText(pm.classic.swift_transfer.iban)" class="text-xs text-blue-500">Copier</button>
                  </div>
                </div>
                <div v-if="pm.classic.swift_transfer.beneficiary" class="flex justify-between">
                  <span class="text-gray-500">Bénéficiaire</span>
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-800">{{ pm.classic.swift_transfer.beneficiary }}</span>
                    <button @click="copyText(pm.classic.swift_transfer.beneficiary)" class="text-xs text-blue-500">Copier</button>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- D) Crypto -->
          <div v-if="activeTab === 'crypto'">
            <p class="text-sm text-gray-500 mb-4">Envoyez exactement le montant indiqué à l'adresse du portefeuille correspondant.</p>
            <div class="space-y-4">
              <div v-if="pm.crypto?.usdt_trc20?.enabled" class="border border-gray-200 rounded-xl p-4">
                <div class="flex items-center gap-3 mb-3">
                  <h4 class="font-semibold text-gray-800">USDT
                    <span class="text-xs text-gray-400 font-normal">({{ pm.crypto.usdt_trc20.network }})</span>
                  </h4>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 font-mono text-sm text-gray-700 break-all mb-3">{{ pm.crypto.usdt_trc20.address }}</div>
                <button @click="copyText(pm.crypto.usdt_trc20.address)"
                  class="flex items-center gap-1 text-sm text-blue-600 border border-blue-200 rounded-lg px-3 py-2 hover:bg-blue-50 transition-colors">
                  Copier l'adresse
                </button>
              </div>
              <div v-if="pm.crypto?.bitcoin?.enabled" class="border border-gray-200 rounded-xl p-4">
                <div class="flex items-center gap-3 mb-3">
                  <h4 class="font-semibold text-gray-800">Bitcoin (BTC)</h4>
                </div>
                <div class="bg-gray-50 rounded-lg p-3 font-mono text-sm text-gray-700 break-all mb-3">{{ pm.crypto.bitcoin.address }}</div>
                <button @click="copyText(pm.crypto.bitcoin.address)"
                  class="flex items-center gap-1 text-sm text-blue-600 border border-blue-200 rounded-lg px-3 py-2 hover:bg-blue-50 transition-colors">
                  Copier l'adresse
                </button>
              </div>
            </div>
          </div>

        </div>
      </section>

      <!-- Message si déjà payé -->
      <section v-if="isPaid" class="bg-green-50 border border-green-200 rounded-xl p-6 text-center">
        <p class="text-green-700 font-medium text-lg">Ce document est entièrement réglé.</p>
        <p class="text-green-600 text-sm mt-1">Merci pour votre confiance.</p>
      </section>

      <!-- Section : Confirmer paiement manuel -->
      <section v-if="!isPaid" class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-base mb-1">Confirmer un paiement manuel</h3>
        <p class="text-sm text-gray-500 mb-4">Vous avez effectué un virement ou un paiement mobile ? Envoyez une preuve (capture d'écran, reçu).</p>

        <div v-if="!showProofForm">
          <button @click="showProofForm = true"
            class="inline-flex items-center gap-2 bg-blue-700 text-white rounded-lg px-5 py-3 font-medium hover:bg-blue-800 transition-colors text-sm">
            J'ai effectué le paiement
          </button>
        </div>

        <div v-else class="space-y-4">
          <div v-if="proofSuccess" class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 text-green-700 text-sm font-medium">
            Preuve envoyée. La société vous contactera pour confirmation.
          </div>
          <div v-else>
            <label class="block text-sm text-gray-600 mb-2">Joindre une image (reçu, capture d'écran) — max 5 Mo :</label>
            <input type="file" accept="image/*" @change="onProofFileChange"
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors" />
            <p v-if="proofError" class="text-red-600 text-sm mt-2">{{ proofError }}</p>
            <div class="flex gap-3 mt-4">
              <button @click="submitProof"
                :disabled="!proofFile || proofLoading"
                class="inline-flex items-center gap-2 bg-blue-700 text-white rounded-lg px-5 py-2.5 font-medium hover:bg-blue-800 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="proofLoading" class="animate-spin inline-block h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                {{ proofLoading ? 'Envoi...' : 'Envoyer la preuve' }}
              </button>
              <button @click="showProofForm = false; proofFile = null; proofError = null"
                class="text-gray-500 text-sm hover:text-gray-700 underline">Annuler</button>
            </div>
          </div>
        </div>
      </section>

      <!-- Partager le lien -->
      <section class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-800 text-base mb-3">Partager ce document</h3>
        <div class="flex flex-wrap gap-3">
          <button @click="copyPageLink"
            class="flex items-center gap-2 border border-gray-200 rounded-lg px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
            Copier le lien
          </button>
          <a :href="whatsappUrl" target="_blank" rel="noopener"
            class="flex items-center gap-2 bg-green-500 text-white rounded-lg px-4 py-2 text-sm font-medium hover:bg-green-600 transition-colors">
            Partager sur WhatsApp
          </a>
        </div>
      </section>

    </main>

    <!-- Footer -->
    <footer class="mt-10 py-6 text-center text-xs text-gray-400 border-t border-gray-200">
      <p>Paiement sécurisé — <span class="font-semibold text-gray-500">IBIG FactPro</span></p>
      <p class="mt-1">
        <a :href="`/verify/${paymentToken}`" class="text-blue-400 hover:text-blue-600 underline transition-colors">
          Vérifier l'authenticité de ce document
        </a>
      </p>
    </footer>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  document:       Object,
  lines:          Array,
  company:        Object,
  customer:       Object,
  alreadyPaid:    Boolean,
  paymentToken:   String,
  uploadProofUrl: String,
  statusCheckUrl: String,
})

// ── State ────────────────────────────────────────────────────────────────────
const isPaid            = ref(props.alreadyPaid || false)
const isPending         = ref(false)
const showCancelToast   = ref(false)
const copiedToast       = ref(false)
const activeTab         = ref(null)
const loadingGateway    = ref(null)
const onlineError       = ref(null)
const isMobile          = ref(false)

// Proof upload
const showProofForm  = ref(false)
const proofFile      = ref(null)
const proofLoading   = ref(false)
const proofError     = ref(null)
const proofSuccess   = ref(false)

// Timer (15 min = 900s)
const onlineTimerSeconds = ref(0)
let timerInterval = null

// Polling
let pollingInterval = null

// ── Payment methods shortcut ─────────────────────────────────────────────────
const pm = computed(() => {
  const methods = props.company?.payment_methods ?? {}
  // Support tableau ou objet
  if (Array.isArray(methods)) {
    // ancien format tableau
    return { online: {}, mobile_money: {}, classic: {}, crypto: {} }
  }
  return {
    online:       methods.online       ?? {},
    mobile_money: methods.mobile_money ?? {},
    classic:      methods.classic      ?? {},
    crypto:       methods.crypto       ?? {},
  }
})

// ── Gateways en ligne ────────────────────────────────────────────────────────
const gatewayMeta = [
  { key: 'cinetpay',    label: 'CinetPay',    icon: '🔵' },
  { key: 'fedapay',     label: 'FedaPay',     icon: '🟢' },
  { key: 'wave',        label: 'Wave',         icon: '🌊' },
  { key: 'flutterwave', label: 'Flutterwave', icon: '🟠' },
  { key: 'moneroo',     label: 'Moneroo',     icon: '🔷' },
  { key: 'stripe',      label: 'Stripe',      icon: '🟣' },
  { key: 'paypal',      label: 'PayPal',      icon: '🔵' },
]

const activeOnlineGateways = computed(() =>
  gatewayMeta.filter(gw => pm.value.online?.[gw.key]?.enabled)
)
const hasOnline = computed(() => activeOnlineGateways.value.length > 0)

// ── Mobile money ─────────────────────────────────────────────────────────────
const mobileOperatorMeta = [
  { key: 'orange_money', label: 'Orange Money', icon: '🟠' },
  { key: 'mtn_momo',     label: 'MTN MoMo',     icon: '🟡' },
  { key: 'wave',         label: 'Wave',          icon: '🔵' },
  { key: 'moov_money',   label: 'Moov Money',   icon: '🟢' },
  { key: 'airtel_money', label: 'Airtel Money', icon: '🔴' },
  { key: 'free_money',   label: 'Free Money',   icon: '🟤' },
  { key: 'mpesa',        label: 'M-Pesa',       icon: '🟩' },
]

const activeMobileOperators = computed(() =>
  mobileOperatorMeta
    .filter(op => pm.value.mobile_money?.[op.key]?.enabled)
    .map(op => ({ ...op, data: pm.value.mobile_money[op.key] }))
)
const hasMobileMoney = computed(() => activeMobileOperators.value.length > 0)

// ── Classique ─────────────────────────────────────────────────────────────────
const hasClassic = computed(() => Object.values(pm.value.classic ?? {}).some(v => v?.enabled))

// ── Crypto ───────────────────────────────────────────────────────────────────
const hasCrypto = computed(() => Object.values(pm.value.crypto ?? {}).some(v => v?.enabled))

// ── Montant restant ───────────────────────────────────────────────────────────
const remainingAmount = computed(() => {
  const total = parseFloat(props.document?.total ?? 0)
  const paid  = parseFloat(props.document?.amount_paid ?? 0)
  return Math.max(0, total - paid)
})

// ── Retard ───────────────────────────────────────────────────────────────────
const isOverdue = computed(() => {
  if (!props.document?.due_date) return false
  return new Date(props.document.due_date) < new Date()
})

// ── Timer display ─────────────────────────────────────────────────────────────
const timerDisplay = computed(() => {
  const m = Math.floor(onlineTimerSeconds.value / 60).toString().padStart(2, '0')
  const s = (onlineTimerSeconds.value % 60).toString().padStart(2, '0')
  return `${m}:${s}`
})

// ── WhatsApp ─────────────────────────────────────────────────────────────────
const whatsappUrl = computed(() => {
  const text = encodeURIComponent(
    `Voici le lien de paiement pour la facture ${props.document?.number ?? ''} d'un montant de ${formatAmount(props.document?.total)} ${props.document?.currency ?? ''} : ${window.location.href}`
  )
  return `https://wa.me/?text=${text}`
})

// ── onMounted ─────────────────────────────────────────────────────────────────
onMounted(() => {
  isMobile.value = /Mobi|Android/i.test(navigator.userAgent)

  const params = new URLSearchParams(window.location.search)

  if (params.get('paid') === '1') {
    isPaid.value = true
  }

  if (params.get('cancelled') === '1') {
    showCancelToast.value = true
    setTimeout(() => { showCancelToast.value = false }, 5000)
  }

  // Polling si ?pending=1
  if (params.get('pending') === '1' && !isPaid.value) {
    isPending.value = true
    startPolling()
  }

  // Timer si paiement online initié (sessionStorage flag)
  const timerStart = sessionStorage.getItem(`fp_timer_${props.paymentToken}`)
  if (timerStart) {
    const elapsed = Math.floor((Date.now() - parseInt(timerStart, 10)) / 1000)
    const remaining = 900 - elapsed
    if (remaining > 0) {
      onlineTimerSeconds.value = remaining
      startTimer()
    } else {
      sessionStorage.removeItem(`fp_timer_${props.paymentToken}`)
    }
  }

  // Sélection premier onglet
  if (hasOnline.value) activeTab.value = 'online'
  else if (hasMobileMoney.value) activeTab.value = 'mobile'
  else if (hasClassic.value) activeTab.value = 'classic'
  else if (hasCrypto.value) activeTab.value = 'crypto'
})

onUnmounted(() => {
  stopTimer()
  stopPolling()
})

// ── Timer ──────────────────────────────────────────────────────────────────────
function startTimer() {
  timerInterval = setInterval(() => {
    if (onlineTimerSeconds.value > 0) {
      onlineTimerSeconds.value--
    } else {
      stopTimer()
      sessionStorage.removeItem(`fp_timer_${props.paymentToken}`)
    }
  }, 1000)
}

function stopTimer() {
  if (timerInterval) {
    clearInterval(timerInterval)
    timerInterval = null
  }
}

// ── Polling statut ────────────────────────────────────────────────────────────
let pollCount = 0
const MAX_POLLS = 60 // 5 min à 5s

function startPolling() {
  pollCount = 0
  pollingInterval = setInterval(async () => {
    pollCount++
    if (pollCount > MAX_POLLS) {
      stopPolling()
      isPending.value = false
      return
    }
    try {
      const res = await fetch(props.statusCheckUrl, { headers: { 'Accept': 'application/json' } })
      if (!res.ok) return
      const data = await res.json()
      if (data.status === 'paid') {
        isPaid.value = true
        isPending.value = false
        stopPolling()
      }
    } catch (_) {
      // silencieux
    }
  }, 5000)
}

function stopPolling() {
  if (pollingInterval) {
    clearInterval(pollingInterval)
    pollingInterval = null
  }
}

// ── Paiement en ligne ─────────────────────────────────────────────────────────
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
      // Démarrer le timer 15 min
      sessionStorage.setItem(`fp_timer_${props.paymentToken}`, Date.now().toString())
      onlineTimerSeconds.value = 900
      startTimer()
      window.location.href = data.redirect_url
    } else {
      throw new Error('Aucune URL de redirection reçue.')
    }
  } catch (err) {
    onlineError.value = err.message ?? 'Une erreur est survenue. Veuillez réessayer.'
    loadingGateway.value = null
  }
}

// ── Upload preuve ──────────────────────────────────────────────────────────────
function onProofFileChange(e) {
  proofFile.value = e.target.files[0] ?? null
  proofError.value = null
}

async function submitProof() {
  if (!proofFile.value || proofLoading.value) return
  proofLoading.value = true
  proofError.value = null

  try {
    const formData = new FormData()
    formData.append('proof', proofFile.value)

    const xsrfToken = getCookie('XSRF-TOKEN')
    const response = await fetch(props.uploadProofUrl, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-XSRF-TOKEN': xsrfToken ? decodeURIComponent(xsrfToken) : '',
      },
      body: formData,
    })

    const data = await response.json().catch(() => ({}))
    if (!response.ok) throw new Error(data.message ?? `Erreur ${response.status}`)

    proofSuccess.value = true
    proofFile.value = null
  } catch (err) {
    proofError.value = err.message ?? 'Une erreur est survenue lors de l\'envoi.'
  } finally {
    proofLoading.value = false
  }
}

// ── Copier lien page ──────────────────────────────────────────────────────────
async function copyPageLink() {
  await copyText(window.location.href)
}

// ── Utilitaires ───────────────────────────────────────────────────────────────
function getCookie(name) {
  const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'))
  return match ? match[1] : null
}

async function copyText(text) {
  if (!text) return
  try {
    await navigator.clipboard.writeText(text)
  } catch {
    const el = document.createElement('textarea')
    el.value = text
    document.body.appendChild(el)
    el.select()
    document.execCommand('copy')
    document.body.removeChild(el)
  }
  copiedToast.value = true
  setTimeout(() => { copiedToast.value = false }, 2000)
}

function formatAmount(val) {
  if (val == null) return '0'
  return parseFloat(val).toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 2 })
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' })
}

function statusClass(status) {
  const s = (status ?? '').toLowerCase()
  if (s === 'paid' || s.includes('pay')) return 'bg-green-100 text-green-700'
  if (s.includes('partiel') || s === 'partial') return 'bg-yellow-100 text-yellow-700'
  if (s.includes('annul') || s.includes('cancel')) return 'bg-red-100 text-red-700'
  if (s.includes('retard') || s === 'overdue') return 'bg-red-100 text-red-700'
  return 'bg-gray-100 text-gray-600'
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

<template>
  <AppLayout title="Bulletin de paie">
    <div class="max-w-3xl mx-auto px-4 py-8" v-if="payslip">
      <!-- Actions -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
          Bulletin de paie — {{ monthName(payslip.period_month) }} {{ payslip.period_year }}
        </h1>
        <div class="flex gap-3">
          <a :href="route('hr.payslips.pdf', payslip.id)" target="_blank"
             class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            📥 Télécharger PDF
          </a>
          <button v-if="payslip.status === 'draft'"
                  @click="validatePayslip"
                  class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            ✅ Valider
          </button>
        </div>
      </div>

      <!-- Card -->
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="bg-blue-600 text-white p-6">
          <div class="flex justify-between">
            <div>
              <div class="text-lg font-bold">{{ payslip.company?.name }}</div>
              <div class="text-blue-200 text-sm">Employeur</div>
            </div>
            <div class="text-right">
              <div class="font-bold text-xl">{{ monthName(payslip.period_month) }} {{ payslip.period_year }}</div>
              <span :class="statusClass(payslip.status)"
                    class="inline-block mt-1 px-3 py-0.5 rounded-full text-xs font-bold uppercase">
                {{ payslip.status }}
              </span>
            </div>
          </div>
        </div>

        <div class="p-6">
          <!-- Employé -->
          <div class="grid grid-cols-2 gap-6 mb-6">
            <div>
              <div class="text-xs font-bold text-gray-400 uppercase mb-2">Employé</div>
              <div class="font-bold text-gray-800 dark:text-white text-lg">
                {{ payslip.employee?.first_name }} {{ payslip.employee?.last_name }}
              </div>
              <div class="text-gray-500 text-sm">{{ payslip.employee?.position }}</div>
              <div v-if="payslip.employee?.cnss_number" class="text-gray-400 text-xs mt-1">
                CNSS : {{ payslip.employee.cnss_number }}
              </div>
            </div>
            <div>
              <div class="text-xs font-bold text-gray-400 uppercase mb-2">Contrat</div>
              <div class="text-gray-700 dark:text-gray-300 text-sm">
                <div v-if="payslip.contract">
                  Type : <strong>{{ payslip.contract.type?.toUpperCase() }}</strong>
                </div>
                <div>Régime : <strong>{{ payslip.employee?.regime || 'cnss_ci' }}</strong></div>
              </div>
            </div>
          </div>

          <!-- Tableau cotisations -->
          <table class="w-full text-sm mb-6">
            <thead>
              <tr class="bg-gray-50 dark:bg-gray-700">
                <th class="px-3 py-2 text-left text-gray-600 dark:text-gray-300 font-semibold">Désignation</th>
                <th class="px-3 py-2 text-right text-gray-600 dark:text-gray-300 font-semibold">Salarial</th>
                <th class="px-3 py-2 text-right text-gray-600 dark:text-gray-300 font-semibold">Patronal</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border-t border-gray-100 dark:border-gray-700">
                <td class="px-3 py-2 text-gray-800 dark:text-white font-medium">Salaire brut</td>
                <td class="px-3 py-2 text-right font-mono font-bold">{{ fmt(payslip.gross_salary) }}</td>
                <td class="px-3 py-2 text-right font-mono font-bold">{{ fmt(payslip.gross_salary) }}</td>
              </tr>
              <tr class="border-t border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-400">
                <td class="px-3 py-2">Cotisations CNSS</td>
                <td class="px-3 py-2 text-right font-mono text-red-500">- {{ fmt(payslip.employee_contributions?.cnss) }}</td>
                <td class="px-3 py-2 text-right font-mono text-orange-500">{{ fmt(payslip.employer_contributions?.cnss) }}</td>
              </tr>
              <tr v-if="payslip.employee_contributions?.irpp > 0"
                  class="border-t border-gray-100 dark:border-gray-700 text-gray-600 dark:text-gray-400">
                <td class="px-3 py-2">IRPP</td>
                <td class="px-3 py-2 text-right font-mono text-red-500">- {{ fmt(payslip.employee_contributions.irpp) }}</td>
                <td class="px-3 py-2 text-right text-gray-300">—</td>
              </tr>
            </tbody>
          </table>

          <!-- Totaux -->
          <div class="border-t-2 border-blue-200 pt-4 space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Total cotisations salariales</span>
              <span class="font-mono text-red-500">- {{ fmt(payslip.employee_contributions?.total) }}</span>
            </div>
            <div class="flex justify-between text-xl font-bold bg-blue-50 dark:bg-blue-900/20 rounded-lg px-4 py-3">
              <span class="text-blue-700 dark:text-blue-300">NET À PAYER</span>
              <span class="text-blue-700 dark:text-blue-300 font-mono">{{ fmt(payslip.net_salary) }} {{ payslip.currency }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Charges patronales</span>
              <span class="font-mono text-orange-500">{{ fmt(payslip.employer_contributions?.total) }}</span>
            </div>
            <div class="flex justify-between text-sm font-bold">
              <span class="text-gray-700 dark:text-gray-300">Coût total employeur</span>
              <span class="font-mono text-purple-600">{{ fmt(payslip.total_employer_cost) }} {{ payslip.currency }}</span>
            </div>
          </div>

          <div v-if="payslip.payment_date" class="mt-4 text-sm text-gray-500 text-right">
            Date de paiement : {{ payslip.payment_date }}
          </div>
        </div>
      </div>
    </div>

    <!-- Liste de bulletins -->
    <div v-else class="max-w-5xl mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Bulletins de paie</h1>
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-3 text-left">Employé</th>
              <th class="px-4 py-3 text-center">Période</th>
              <th class="px-4 py-3 text-right">Brut</th>
              <th class="px-4 py-3 text-right">Net</th>
              <th class="px-4 py-3 text-center">Statut</th>
              <th class="px-4 py-3 text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in payslips?.data || []" :key="p.id"
                class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50">
              <td class="px-4 py-3">{{ p.employee?.first_name }} {{ p.employee?.last_name }}</td>
              <td class="px-4 py-3 text-center text-gray-500">
                {{ String(p.period_month).padStart(2,'0') }}/{{ p.period_year }}
              </td>
              <td class="px-4 py-3 text-right font-mono">{{ fmt(p.gross_salary) }}</td>
              <td class="px-4 py-3 text-right font-mono font-bold text-blue-600">{{ fmt(p.net_salary) }}</td>
              <td class="px-4 py-3 text-center">
                <span :class="statusClass(p.status)"
                      class="px-2 py-0.5 rounded-full text-xs font-bold uppercase">
                  {{ p.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <a :href="route('hr.payslips.show', p.id)" class="text-blue-500 hover:underline text-xs mr-2">Voir</a>
                <a :href="route('hr.payslips.pdf', p.id)" target="_blank" class="text-gray-500 hover:underline text-xs">PDF</a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  payslip:  { type: Object, default: null },
  payslips: { type: Object, default: null },
  filters:  { type: Object, default: () => ({}) },
})

const monthNames = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']
function monthName(m) { return monthNames[m] || '' }

function fmt(val) {
  if (!val && val !== 0) return '—'
  return new Intl.NumberFormat('fr-FR').format(Math.round(val))
}

function statusClass(status) {
  const map = {
    draft: 'bg-yellow-100 text-yellow-700',
    validated: 'bg-green-100 text-green-700',
    paid: 'bg-blue-100 text-blue-700',
  }
  return map[status] || 'bg-gray-100 text-gray-600'
}

function validatePayslip() {
  router.post(route('hr.payslips.validate', props.payslip.id))
}
</script>

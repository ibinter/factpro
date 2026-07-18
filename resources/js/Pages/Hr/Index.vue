<template>
  <AppLayout title="RH & Paie">
    <!-- Gate ENTERPRISE -->
    <div v-if="!hasAccess" class="max-w-2xl mx-auto mt-20 text-center">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-10">
        <div class="text-5xl mb-4">👥</div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Module RH & Paie</h2>
        <p class="text-gray-500 dark:text-gray-400 mb-6">
          Ce module est réservé au plan <strong class="text-blue-600">ENTERPRISE</strong>.
          Gérez vos employés, contrats et bulletins de paie en toute conformité.
        </p>
        <a href="/billing/plans"
           class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-xl transition">
          Passer à ENTERPRISE
        </a>
      </div>
    </div>

    <!-- Module principal -->
    <div v-else class="max-w-7xl mx-auto px-4 py-6">
      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
          <div class="text-sm text-gray-500">Employés actifs</div>
          <div class="text-2xl font-bold text-blue-600">{{ stats.active_employees }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
          <div class="text-sm text-gray-500">Masse salariale</div>
          <div class="text-2xl font-bold text-green-600">{{ formatAmount(stats.masse_salariale) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
          <div class="text-sm text-gray-500">Charges patronales</div>
          <div class="text-2xl font-bold text-orange-500">{{ formatAmount(stats.charges_patronales) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
          <div class="text-sm text-gray-500">Coût total employeur</div>
          <div class="text-2xl font-bold text-purple-600">{{ formatAmount(stats.total_cost) }}</div>
        </div>
      </div>

      <!-- Onglets -->
      <div class="flex gap-2 mb-6 border-b border-gray-200 dark:border-gray-700">
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          :class="[
            'px-4 py-2 font-medium text-sm rounded-t-lg transition',
            activeTab === tab.key
              ? 'bg-blue-600 text-white'
              : 'text-gray-500 hover:text-blue-600'
          ]"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Onglet Employés -->
      <div v-if="activeTab === 'employees'">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Liste des employés</h2>
          <button @click="showAddEmployee = true"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            + Ajouter un employé
          </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Nom</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Poste</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Département</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Salaire brut</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Contrat</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Statut</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="emp in employees" :key="emp.id"
                  class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750">
                <td class="px-4 py-3 font-medium text-gray-800 dark:text-white">{{ emp.full_name }}</td>
                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ emp.position }}</td>
                <td class="px-4 py-3 text-gray-500">{{ emp.department || '—' }}</td>
                <td class="px-4 py-3 text-right font-mono text-gray-800 dark:text-white">
                  {{ emp.gross_salary ? formatAmount(emp.gross_salary) : '—' }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span v-if="emp.contract_type"
                        class="px-2 py-0.5 rounded-full text-xs font-bold uppercase bg-blue-100 text-blue-700">
                    {{ emp.contract_type }}
                  </span>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span :class="statusClass(emp.status)"
                        class="px-2 py-0.5 rounded-full text-xs font-bold">
                    {{ emp.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="!employees.length">
                <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                  Aucun employé enregistré.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Onglet Bulletins de paie -->
      <div v-if="activeTab === 'payslips'">
        <!-- Génération de la paie -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-4 mb-6 flex flex-wrap items-center gap-4">
          <span class="font-semibold text-blue-800 dark:text-blue-300">Générer la paie du mois :</span>
          <select v-model="payrollMonth"
                  class="border rounded-lg px-3 py-1.5 text-sm dark:bg-gray-700 dark:border-gray-600">
            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
          </select>
          <input v-model="payrollYear" type="number" min="2020" max="2099"
                 class="border rounded-lg px-3 py-1.5 w-24 text-sm dark:bg-gray-700 dark:border-gray-600" />
          <button @click="generatePayroll"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition">
            ▶ Générer
          </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-4 py-3 text-left">Employé</th>
                <th class="px-4 py-3 text-center">Période</th>
                <th class="px-4 py-3 text-right">Brut</th>
                <th class="px-4 py-3 text-right">Net à payer</th>
                <th class="px-4 py-3 text-center">Statut</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in payslips" :key="p.id"
                  class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50">
                <td class="px-4 py-3">
                  {{ p.employee?.first_name }} {{ p.employee?.last_name }}
                </td>
                <td class="px-4 py-3 text-center text-gray-500">
                  {{ String(p.period_month).padStart(2,'0') }}/{{ p.period_year }}
                </td>
                <td class="px-4 py-3 text-right font-mono">{{ formatAmount(p.gross_salary) }}</td>
                <td class="px-4 py-3 text-right font-mono font-bold text-blue-600">
                  {{ formatAmount(p.net_salary) }}
                </td>
                <td class="px-4 py-3 text-center">
                  <span :class="payslipStatusClass(p.status)"
                        class="px-2 py-0.5 rounded-full text-xs font-bold uppercase">
                    {{ p.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="!payslips.length">
                <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                  Aucun bulletin de paie.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Onglet Masse salariale -->
      <div v-if="activeTab === 'mass_salariale'">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Synthèse masse salariale</h3>
          <div class="space-y-3">
            <div class="flex justify-between py-2 border-b border-gray-100">
              <span class="text-gray-600">Salaire brut total</span>
              <span class="font-bold">{{ formatAmount(stats.masse_salariale) }} XOF</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-100">
              <span class="text-gray-600">Charges patronales</span>
              <span class="font-bold text-orange-500">{{ formatAmount(stats.charges_patronales) }} XOF</span>
            </div>
            <div class="flex justify-between py-2 text-lg">
              <span class="font-bold text-gray-800 dark:text-white">Coût total employeur</span>
              <span class="font-bold text-purple-600">{{ formatAmount(stats.total_cost) }} XOF</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  hasAccess: Boolean,
  employees: { type: Array, default: () => [] },
  payslips:  { type: Array, default: () => [] },
  stats:     { type: Object, default: () => ({}) },
})

const tabs = [
  { key: 'employees', label: '👥 Employés' },
  { key: 'payslips',  label: '📄 Bulletins de paie' },
  { key: 'mass_salariale', label: '📊 Masse salariale' },
]
const activeTab = ref('employees')
const showAddEmployee = ref(false)

const now = new Date()
const payrollMonth = ref(now.getMonth() + 1)
const payrollYear  = ref(now.getFullYear())

const monthNames = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']
const months = monthNames.map((label, i) => ({ value: i + 1, label }))

function formatAmount(val) {
  if (!val && val !== 0) return '—'
  return new Intl.NumberFormat('fr-FR').format(Math.round(val))
}

function statusClass(status) {
  const map = {
    active: 'bg-green-100 text-green-700',
    suspended: 'bg-yellow-100 text-yellow-700',
    terminated: 'bg-red-100 text-red-700',
  }
  return map[status] || 'bg-gray-100 text-gray-600'
}

function payslipStatusClass(status) {
  const map = {
    draft: 'bg-yellow-100 text-yellow-700',
    validated: 'bg-green-100 text-green-700',
    paid: 'bg-blue-100 text-blue-700',
  }
  return map[status] || 'bg-gray-100 text-gray-600'
}

function generatePayroll() {
  router.post(route('hr.payroll.generate'), {
    month: payrollMonth.value,
    year:  payrollYear.value,
  })
}
</script>

<script setup>
import { ref } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  customers: Array,
})

const form = useForm({
  customer_id: '',
  device_type: 'smartphone',
  brand: '',
  model_name: '',
  serial_number: '',
  issue_description: '',
  priority: 'normal',
  deposit_amount: '',
  promised_at: '',
  customer_notes: '',
  technician_name: '',
})

function submit() {
  form.post(route('sav.repairs.store'))
}
</script>

<template>
  <Head title="Nouvelle réparation" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Nouvelle réparation</h2>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">

          <form @submit.prevent="submit" class="space-y-6">

            <!-- Client -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client</label>
              <select
                v-model="form.customer_id"
                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              >
                <option value="">— Client non enregistré —</option>
                <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <p v-if="form.errors.customer_id" class="mt-1 text-xs text-red-600">{{ form.errors.customer_id }}</p>
            </div>

            <!-- Appareil -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type d'appareil *</label>
                <select
                  v-model="form.device_type"
                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  <option value="smartphone">Smartphone</option>
                  <option value="tablet">Tablette</option>
                  <option value="computer">Ordinateur</option>
                  <option value="printer">Imprimante</option>
                  <option value="tv">Téléviseur</option>
                  <option value="other">Autre</option>
                </select>
                <p v-if="form.errors.device_type" class="mt-1 text-xs text-red-600">{{ form.errors.device_type }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priorité *</label>
                <select
                  v-model="form.priority"
                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                >
                  <option value="low">Basse</option>
                  <option value="normal">Normale</option>
                  <option value="high">Haute</option>
                  <option value="urgent">Urgente</option>
                </select>
                <p v-if="form.errors.priority" class="mt-1 text-xs text-red-600">{{ form.errors.priority }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Marque</label>
                <input v-model="form.brand" type="text" placeholder="Samsung, Apple…"
                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Modèle</label>
                <input v-model="form.model_name" type="text" placeholder="Galaxy S21…"
                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">N° de série</label>
                <input v-model="form.serial_number" type="text"
                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
            </div>

            <!-- Description panne -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description de la panne *</label>
              <textarea
                v-model="form.issue_description"
                rows="4"
                placeholder="Décrivez le problème signalé par le client…"
                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              ></textarea>
              <p v-if="form.errors.issue_description" class="mt-1 text-xs text-red-600">{{ form.errors.issue_description }}</p>
            </div>

            <!-- Technicien + dates + acompte -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Technicien</label>
                <input v-model="form.technician_name" type="text"
                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date promise</label>
                <input v-model="form.promised_at" type="date"
                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Acompte versé</label>
                <input v-model="form.deposit_amount" type="number" min="0" step="0.01"
                  class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              </div>
            </div>

            <!-- Notes client -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes client (visible)</label>
              <textarea
                v-model="form.customer_notes"
                rows="2"
                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              ></textarea>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-2">
              <Link :href="route('sav.repairs.index')" class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                Annuler
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-medium rounded-md shadow-sm transition"
              >
                Créer la réparation
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

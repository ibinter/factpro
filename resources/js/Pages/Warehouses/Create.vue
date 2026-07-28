<script setup>
import { useForm } from '@inertiajs/vue3'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const form = useForm({
    name: '',
    code: '',
    address: '',
    city: '',
    manager_name: '',
    phone: '',
    is_default: false,
    is_active: true,
})

function submit() {
    form.post(route('warehouses.store'))
}
</script>

<template>
    <Head title="Nouvel entrepôt" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('warehouses.index')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Nouvel entrepôt</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Ajouter un site de stockage</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 space-y-5">

                    <!-- Nom -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nom de l'entrepôt <span class="text-red-500">*</span></label>
                        <input v-model="form.name" type="text" placeholder="Entrepôt principal"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                            :class="{ 'border-red-500': form.errors.name }" />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Code -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code unique <span class="text-red-500">*</span></label>
                        <input v-model="form.code" type="text" placeholder="ENT-01" maxlength="20"
                            class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-mono uppercase"
                            :class="{ 'border-red-500': form.errors.code }"
                            @input="form.code = form.code.toUpperCase()" />
                        <p v-if="form.errors.code" class="mt-1 text-xs text-red-600">{{ form.errors.code }}</p>
                    </div>

                    <!-- Adresse + Ville -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adresse</label>
                            <input v-model="form.address" type="text" placeholder="Rue, quartier..."
                                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ville</label>
                            <input v-model="form.city" type="text" placeholder="Abidjan"
                                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                        </div>
                    </div>

                    <!-- Responsable + Téléphone -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Responsable</label>
                            <input v-model="form.manager_name" type="text" placeholder="Nom du gestionnaire"
                                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Téléphone</label>
                            <input v-model="form.phone" type="tel" placeholder="+225 07 00 00 00"
                                class="w-full px-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <input v-model="form.is_default" type="checkbox" class="h-4 w-4 rounded text-brand-600 border-gray-300 focus:ring-brand-500" />
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">Entrepôt par défaut</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Utilisé en priorité pour les documents</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <input v-model="form.is_active" type="checkbox" class="h-4 w-4 rounded text-brand-600 border-gray-300 focus:ring-brand-500" />
                            <div>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-100">Entrepôt actif</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Désactiver pour le mettre hors service</p>
                            </div>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <Link :href="route('warehouses.index')"
                            class="px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Annuler
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 disabled:opacity-60 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm flex items-center gap-2">
                            <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            {{ form.processing ? 'Création...' : 'Créer l\'entrepôt' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({
    visits: Object,
    stats: Object,
    customers: Array,
})

const showForm = ref(false)
const form = useForm({
    customer_id: '',
    customer_name: '',
    visit_type: 'commercial',
    planned_at: '',
    objective: '',
    address_visited: '',
})

function submit() {
    form.post(route('visits.store'), { onSuccess: () => { showForm.value = false; form.reset() } })
}

function checkin(visit) {
    if (!navigator.geolocation) return alert('GPS non disponible')
    navigator.geolocation.getCurrentPosition(pos => {
        router.post(route('visits.checkin', visit.id), {
            lat: pos.coords.latitude,
            lng: pos.coords.longitude,
        }, { preserveScroll: true })
    }, () => alert('Impossible d\'obtenir la position GPS'))
}

function checkout(visit) {
    const report = prompt('Compte-rendu rapide (optionnel) :') ?? ''
    const outcome = prompt('Résultat ? (positif / neutre / negatif / relance)') ?? 'neutre'
    if (!navigator.geolocation) {
        router.post(route('visits.checkout', visit.id), { report, outcome }, { preserveScroll: true })
        return
    }
    navigator.geolocation.getCurrentPosition(pos => {
        router.post(route('visits.checkout', visit.id), {
            lat: pos.coords.latitude, lng: pos.coords.longitude, report, outcome,
        }, { preserveScroll: true })
    }, () => {
        router.post(route('visits.checkout', visit.id), { report, outcome }, { preserveScroll: true })
    })
}

const typeColors = { commercial: 'blue', livraison: 'green', sav: 'orange', prospection: 'purple' }
const statusColors = { planned: 'gray', in_progress: 'yellow', completed: 'green', cancelled: 'red' }
const outcomeColors = { positif: 'green', neutre: 'gray', negatif: 'red', relance: 'orange' }
</script>

<template>
    <Head title="Visites terrain" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">Visites terrain</h2>
                <button @click="showForm = true"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">
                    + Nouvelle visite
                </button>
            </div>
        </template>

        <div class="max-w-5xl mx-auto py-6 px-4 space-y-6">

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-for="(item, key) in [
                    { label: 'Total visites', value: stats.total, icon: '📋' },
                    { label: 'Cette semaine', value: stats.this_week, icon: '📅' },
                    { label: 'Complétées', value: stats.completed, icon: '✅' },
                    { label: 'Issues positives', value: stats.positif, icon: '🎯' },
                ]" :key="key"
                    class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow text-center">
                    <div class="text-2xl mb-1">{{ item.icon }}</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white">{{ item.value }}</div>
                    <div class="text-xs text-gray-500">{{ item.label }}</div>
                </div>
            </div>

            <!-- Liste visites -->
            <div class="space-y-3">
                <div v-for="v in visits.data" :key="v.id"
                    class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-gray-800 dark:text-white truncate">{{ v.customer }}</span>
                                <span :class="`bg-${typeColors[v.visit_type] || 'blue'}-100 text-${typeColors[v.visit_type] || 'blue'}-700 text-xs px-2 py-0.5 rounded-full`">
                                    {{ v.visit_type }}
                                </span>
                                <span :class="`bg-${statusColors[v.status] || 'gray'}-100 text-${statusColors[v.status] || 'gray'}-700 text-xs px-2 py-0.5 rounded-full`">
                                    {{ v.status }}
                                </span>
                                <span v-if="v.outcome" :class="`bg-${outcomeColors[v.outcome] || 'gray'}-100 text-${outcomeColors[v.outcome] || 'gray'}-700 text-xs px-2 py-0.5 rounded-full`">
                                    {{ v.outcome }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-500 mt-1">
                                👤 {{ v.user }} · 📅 {{ v.planned_at || '—' }}
                                <span v-if="v.duration"> · ⏱ {{ v.duration }}</span>
                                <span v-if="v.has_report" class="ml-1 text-green-600">📝 CR</span>
                            </div>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <button v-if="v.status === 'planned'" @click="checkin(v)"
                                class="bg-green-500 text-white text-xs px-3 py-1.5 rounded-lg font-medium hover:bg-green-600">
                                📍 Check-in
                            </button>
                            <button v-if="v.status === 'in_progress'" @click="checkout(v)"
                                class="bg-orange-500 text-white text-xs px-3 py-1.5 rounded-lg font-medium hover:bg-orange-600">
                                🏁 Check-out
                            </button>
                            <button @click="router.delete(route('visits.destroy', v.id), { preserveScroll: true })"
                                class="text-red-400 text-xs px-2 py-1.5 rounded hover:text-red-600">
                                🗑
                            </button>
                        </div>
                    </div>
                </div>
                <p v-if="!visits.data.length" class="text-center text-gray-400 py-8">Aucune visite planifiée.</p>
            </div>

            <!-- Pagination -->
            <div v-if="visits.last_page > 1" class="flex justify-center gap-2">
                <a v-for="l in visits.links" :key="l.label" :href="l.url"
                    v-html="l.label"
                    :class="['px-3 py-1 rounded text-sm', l.active ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100']" />
            </div>
        </div>

        <!-- Slide-over nouvelle visite -->
        <div v-if="showForm" class="fixed inset-0 z-50 flex items-end md:items-center justify-center bg-black/50" @click.self="showForm = false">
            <div class="bg-white dark:bg-gray-800 rounded-t-2xl md:rounded-2xl w-full max-w-lg p-6 space-y-4">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Nouvelle visite</h3>

                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Client</label>
                        <select v-model="form.customer_id" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
                            <option value="">— Prospect / Autre —</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div v-if="!form.customer_id">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Nom prospect</label>
                        <input v-model="form.customer_name" type="text" placeholder="Nom du prospect"
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Type de visite</label>
                        <select v-model="form.visit_type" class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
                            <option value="commercial">Commercial</option>
                            <option value="livraison">Livraison</option>
                            <option value="sav">SAV</option>
                            <option value="prospection">Prospection</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Date prévue</label>
                        <input v-model="form.planned_at" type="datetime-local"
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Adresse</label>
                        <input v-model="form.address_visited" type="text" placeholder="Adresse de la visite"
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Objectif</label>
                        <textarea v-model="form.objective" rows="2" placeholder="Objectif de la visite..."
                            class="mt-1 block w-full rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm"></textarea>
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button @click="showForm = false" class="flex-1 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 py-2 rounded-lg text-sm">Annuler</button>
                    <button @click="submit" :disabled="form.processing"
                        class="flex-1 bg-blue-600 text-white py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50">
                        Planifier
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

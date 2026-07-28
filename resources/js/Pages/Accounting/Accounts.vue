<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    accountsByType: Object,
    currency: String,
})

const TYPE_LABELS = {
    asset:     'Actif',
    liability: 'Passif',
    equity:    'Capitaux propres',
    revenue:   'Produits',
    expense:   'Charges',
}

const TYPE_COLORS = {
    asset:     'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    liability: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    equity:    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    revenue:   'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    expense:   'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
}

const activeTab = ref(Object.keys(props.accountsByType)[0] ?? 'asset')

const totalAccounts = computed(() =>
    Object.values(props.accountsByType).reduce((s, list) => s + list.length, 0)
)

const hasAccounts = computed(() => totalAccounts.value > 0)

// Modal nouveau compte
const showModal = ref(false)

const form = useForm({
    code:      '',
    name:      '',
    type:      'asset',
    category:  '',
    parent_id: null,
})

function openModal() {
    form.reset()
    showModal.value = true
}

function submitAccount() {
    form.post(route('accounting.accounts.store'), {
        onSuccess: () => { showModal.value = false },
    })
}

function importOhada() {
    router.post(route('accounting.import-plan'))
}

function fmtAmount(val) {
    return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val ?? 0)
}
</script>

<template>
    <Head title="Plan comptable" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Plan comptable
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Barre d'actions -->
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ totalAccounts }} compte{{ totalAccounts !== 1 ? 's' : '' }}
                    </p>
                    <div class="flex gap-2">
                        <button
                            v-if="!hasAccounts"
                            @click="importOhada"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition"
                        >
                            Importer plan OHADA
                        </button>
                        <button
                            @click="openModal"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition"
                        >
                            + Nouveau compte
                        </button>
                    </div>
                </div>

                <!-- Aucun compte -->
                <div v-if="!hasAccounts" class="bg-white dark:bg-gray-800 rounded-xl shadow p-12 text-center">
                    <p class="text-gray-400 dark:text-gray-500 text-lg mb-4">Aucun compte dans le plan comptable.</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500">
                        Commencez par importer le plan OHADA ou créez vos propres comptes.
                    </p>
                </div>

                <!-- Onglets par type -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                    <div class="flex border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                        <button
                            v-for="(accounts, type) in accountsByType"
                            :key="type"
                            @click="activeTab = type"
                            class="px-5 py-3 text-sm font-medium whitespace-nowrap transition border-b-2"
                            :class="activeTab === type
                                ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                        >
                            <span class="inline-flex items-center gap-2">
                                {{ TYPE_LABELS[type] ?? type }}
                                <span class="text-xs px-1.5 py-0.5 rounded-full" :class="TYPE_COLORS[type]">
                                    {{ accounts.length }}
                                </span>
                            </span>
                        </button>
                    </div>

                    <div v-for="(accounts, type) in accountsByType" :key="type" v-show="activeTab === type">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-28">Code</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nom</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Catégorie</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-36">Solde</th>
                                    <th class="px-4 py-3 w-8"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr
                                    v-for="account in accounts"
                                    :key="account.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                >
                                    <td class="px-4 py-3 font-mono text-sm font-semibold text-indigo-700 dark:text-indigo-300"
                                        :style="account.parent_id ? 'padding-left: 2rem' : ''">
                                        {{ account.code }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-gray-200"
                                        :style="account.parent_id ? 'padding-left: 2.5rem' : ''">
                                        {{ account.name }}
                                        <span v-if="account.is_system" class="ml-1 text-xs text-gray-400">(système)</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                        {{ account.category ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-mono"
                                        :class="account.balance >= 0 ? 'text-gray-800 dark:text-gray-200' : 'text-red-600 dark:text-red-400'">
                                        {{ fmtAmount(account.balance) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span v-if="!account.is_active" class="inline-block w-2 h-2 rounded-full bg-gray-300 dark:bg-gray-600" title="Inactif"></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal nouveau compte -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nouveau compte</h3>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-2xl leading-none">&times;</button>
                    </div>
                    <form @submit.prevent="submitAccount" class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code *</label>
                                <input v-model="form.code" type="text" maxlength="20" required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                                <p v-if="form.errors.code" class="mt-1 text-xs text-red-500">{{ form.errors.code }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type *</label>
                                <select v-model="form.type" required
                                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option v-for="(label, val) in TYPE_LABELS" :key="val" :value="val">{{ label }}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Intitulé *</label>
                            <input v-model="form.name" type="text" required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Catégorie</label>
                            <input v-model="form.category" type="text"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showModal = false"
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Annuler
                            </button>
                            <button type="submit" :disabled="form.processing"
                                class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50">
                                Créer le compte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

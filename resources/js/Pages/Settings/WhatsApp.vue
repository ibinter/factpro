<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    settings: Object,
})

const form = useForm({
    wa_enabled:             props.settings.wa_enabled ?? false,
    wa_phone_number_id:     props.settings.wa_phone_number_id ?? '',
    wa_access_token:        '',
    wa_business_account_id: props.settings.wa_business_account_id ?? '',
})

const showToken = ref(false)
const testLoading = ref(false)
const testResult = ref(null)
const testError = ref(null)

const webhookUrl = computed(() => `${window.location.origin}/webhooks/whatsapp`)

function submit() {
    form.put(route('settings.whatsapp.update'), {
        preserveScroll: true,
    })
}

async function testConnection() {
    testLoading.value = true
    testResult.value = null
    testError.value = null

    try {
        const response = await fetch(route('whatsapp.test'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
        const data = await response.json()
        if (data.success) {
            testResult.value = data.message
        } else {
            testError.value = data.message
        }
    } catch (e) {
        testError.value = 'Erreur reseau : ' + e.message
    } finally {
        testLoading.value = false
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text)
}
</script>

<template>
    <Head title="Parametres WhatsApp" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Parametres WhatsApp Business
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Card Configuration -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            <path d="M12 0C5.373 0 0 5.373 0 12c0 2.136.561 4.14 1.535 5.877L0 24l6.335-1.507A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.804 9.804 0 01-5.031-1.383l-.36-.214-3.762.895.952-3.657-.235-.376A9.799 9.799 0 012.182 12C2.182 6.57 6.57 2.182 12 2.182S21.818 6.57 21.818 12 17.43 21.818 12 21.818z"/>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Configuration API</h3>
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Toggle Activer -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-700 dark:text-gray-300">Activer WhatsApp Business</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Permet l'envoi de messages via l'API Cloud</p>
                            </div>
                            <button
                                type="button"
                                @click="form.wa_enabled = !form.wa_enabled"
                                :class="[
                                    'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2',
                                    form.wa_enabled ? 'bg-green-600' : 'bg-gray-300 dark:bg-gray-600'
                                ]"
                            >
                                <span
                                    :class="[
                                        'inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform',
                                        form.wa_enabled ? 'translate-x-6' : 'translate-x-1'
                                    ]"
                                />
                            </button>
                        </div>

                        <!-- Phone Number ID -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Phone Number ID
                            </label>
                            <input
                                v-model="form.wa_phone_number_id"
                                type="text"
                                placeholder="Ex: 123456789012345"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                            />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Trouve dans le tableau de bord Meta -> WhatsApp -> API Setup</p>
                        </div>

                        <!-- Access Token -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Access Token
                            </label>
                            <div class="relative">
                                <input
                                    v-model="form.wa_access_token"
                                    :type="showToken ? 'text' : 'password'"
                                    :placeholder="settings.wa_access_token_masked ? settings.wa_access_token_masked : 'Coller votre token ici'"
                                    class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 pr-10 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                                />
                                <button
                                    type="button"
                                    @click="showToken = !showToken"
                                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                >
                                    <svg v-if="!showToken" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Laisser vide pour conserver le token actuel</p>
                        </div>

                        <!-- Business Account ID -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Business Account ID
                            </label>
                            <input
                                v-model="form.wa_business_account_id"
                                type="text"
                                placeholder="Ex: 987654321098765"
                                class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                            />
                        </div>

                        <!-- Boutons -->
                        <div class="flex items-center gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md shadow-sm disabled:opacity-50 transition-colors"
                            >
                                {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
                            </button>
                            <button
                                type="button"
                                @click="testConnection"
                                :disabled="testLoading"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm disabled:opacity-50 transition-colors"
                            >
                                {{ testLoading ? 'Test en cours...' : 'Tester la connexion' }}
                            </button>
                        </div>

                        <!-- Resultats -->
                        <div v-if="testResult" class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
                            <p class="text-sm text-green-700 dark:text-green-400">{{ testResult }}</p>
                        </div>
                        <div v-if="testError" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                            <p class="text-sm text-red-700 dark:text-red-400">{{ testError }}</p>
                        </div>
                        <div v-if="$page.props.flash?.success" class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
                            <p class="text-sm text-green-700 dark:text-green-400">{{ $page.props.flash.success }}</p>
                        </div>
                    </form>
                </div>

                <!-- Webhook Section -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Configuration Webhook</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL du Webhook</label>
                            <div class="flex items-center gap-2">
                                <input
                                    :value="webhookUrl"
                                    readonly
                                    class="flex-1 rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-2 text-sm font-mono"
                                />
                                <button
                                    type="button"
                                    @click="copyToClipboard(webhookUrl)"
                                    class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-md border border-gray-300 dark:border-gray-600 transition-colors"
                                >
                                    Copier
                                </button>
                            </div>
                        </div>
                        <div v-if="settings.wa_verify_token">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Verify Token</label>
                            <div class="flex items-center gap-2">
                                <input
                                    :value="settings.wa_verify_token"
                                    readonly
                                    class="flex-1 rounded-md border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-3 py-2 text-sm font-mono"
                                />
                                <button
                                    type="button"
                                    @click="copyToClipboard(settings.wa_verify_token)"
                                    class="px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-md border border-gray-300 dark:border-gray-600 transition-colors"
                                >
                                    Copier
                                </button>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-500 dark:text-gray-400">Le verify token sera genere lors de la premiere sauvegarde.</p>
                    </div>
                </div>

                <!-- Guide -->
                <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Guide de configuration</h3>
                    <ol class="list-decimal list-inside space-y-3 text-sm text-gray-700 dark:text-gray-300">
                        <li>
                            <span class="font-medium">Creer une application Meta</span> —
                            <a href="https://developers.facebook.com/docs/whatsapp/cloud-api/get-started" target="_blank" rel="noopener" class="text-blue-600 dark:text-blue-400 hover:underline">
                                developers.facebook.com
                            </a>
                            et creez une app de type "Business".
                        </li>
                        <li>
                            <span class="font-medium">Ajouter WhatsApp</span> — Dans votre app, ajoutez le produit "WhatsApp" et configurez votre numero de telephone business.
                        </li>
                        <li>
                            <span class="font-medium">Recuperer les cles</span> — Copiez le Phone Number ID, l'Access Token et le WhatsApp Business Account ID depuis l'onglet API Setup.
                        </li>
                        <li>
                            <span class="font-medium">Configurer le Webhook</span> — Dans la section Webhook de votre app Meta, collez l'URL et le Verify Token ci-dessus. Abonnez-vous aux evenements messages et message_status_updates.
                        </li>
                    </ol>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

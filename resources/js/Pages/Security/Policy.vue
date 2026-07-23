<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    policy: Object,
    stats: Object,
});

const form = reactive({ ...props.policy });

const saving = ref(false);
const flashSuccess = ref('');
const flashError = ref('');
const sessions = ref([]);
const showSessions = ref(false);
const loadingSessions = ref(false);
const ipInput = ref('');

const allowedIps = ref(Array.isArray(form.allowed_ips) ? [...form.allowed_ips] : []);

function addIp() {
    const ip = ipInput.value.trim();
    if (ip && !allowedIps.value.includes(ip)) {
        allowedIps.value.push(ip);
    }
    ipInput.value = '';
}

function removeIp(ip) {
    allowedIps.value = allowedIps.value.filter(i => i !== ip);
}

function save() {
    saving.value = true;
    flashSuccess.value = '';
    flashError.value = '';

    router.put(route('security.policy.update'), {
        ...form,
        allowed_ips: allowedIps.value,
    }, {
        onSuccess: () => {
            flashSuccess.value = 'Politique mise à jour avec succès.';
        },
        onError: (errors) => {
            flashError.value = Object.values(errors).flat().join(' ');
        },
        onFinish: () => {
            saving.value = false;
        },
    });
}

async function loadSessions() {
    loadingSessions.value = true;
    showSessions.value = true;
    try {
        const res = await axios.get(route('security.sessions'));
        sessions.value = res.data;
    } finally {
        loadingSessions.value = false;
    }
}

function killSession(sessionId) {
    if (!confirm('Terminer cette session ?')) return;
    router.delete(route('security.sessions.kill', sessionId), {
        onSuccess: () => loadSessions(),
    });
}

function killAllSessions() {
    if (!confirm('Terminer toutes les autres sessions ?')) return;
    router.delete(route('security.sessions.kill-all'), {
        onSuccess: () => loadSessions(),
    });
}

const actionLabels = {
    login: 'Connexion',
    logout: 'Déconnexion',
    login_failed: 'Échec connexion',
    password_changed: 'MDP changé',
    '2fa_enabled': '2FA activé',
    gdpr_export: 'Export RGPD',
    gdpr_report_generated: 'Rapport RGPD',
    page_access: 'Accès page',
};
</script>

<template>
    <Head title="Politique de sécurité" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Politique de sécurité</h2>
            </div>
        </template>

        <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <!-- Flash messages -->
            <div v-if="flashSuccess" class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 flex items-center gap-2">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ flashSuccess }}
            </div>
            <div v-if="flashError" class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3">
                {{ flashError }}
            </div>

            <!-- Stat rapide -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
                    <div class="text-2xl font-bold text-indigo-600">{{ stats.failed_attempts }}</div>
                    <div class="text-xs text-gray-500 mt-1">Échecs connexion (7j)</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
                    <div class="text-2xl font-bold text-green-600">{{ stats.last_logins?.length ?? 0 }}</div>
                    <div class="text-xs text-gray-500 mt-1">Dernières connexions</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 text-center">
                    <div class="text-2xl font-bold text-gray-700 dark:text-gray-200">{{ stats.recent_logs?.length ?? 0 }}</div>
                    <div class="text-xs text-gray-500 mt-1">Entrées journal</div>
                </div>
            </div>

            <form @submit.prevent="save" class="space-y-6">

                <!-- Section 1 : Politique mot de passe -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-base mb-5 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Politique de mot de passe
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Longueur minimale : <strong>{{ form.password_min_length }}</strong> caractères
                            </label>
                            <input type="range" v-model.number="form.password_min_length" min="6" max="32"
                                class="w-full accent-indigo-600" />
                            <div class="flex justify-between text-xs text-gray-400 mt-0.5"><span>6</span><span>32</span></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Expiration (jours) : <strong>{{ form.password_expiry_days === 0 ? 'Jamais' : form.password_expiry_days + 'j' }}</strong>
                            </label>
                            <input type="range" v-model.number="form.password_expiry_days" min="0" max="365" step="30"
                                class="w-full accent-indigo-600" />
                            <div class="flex justify-between text-xs text-gray-400 mt-0.5"><span>Jamais</span><span>365j</span></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Historique refusé : <strong>{{ form.password_history_count }} dernier(s)</strong>
                            </label>
                            <input type="range" v-model.number="form.password_history_count" min="0" max="12"
                                class="w-full accent-indigo-600" />
                        </div>

                        <div class="space-y-3 pt-1">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" v-model="form.password_require_uppercase"
                                    class="w-4 h-4 text-indigo-600 rounded border-gray-300" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">Exiger une majuscule</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" v-model="form.password_require_number"
                                    class="w-4 h-4 text-indigo-600 rounded border-gray-300" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">Exiger un chiffre</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" v-model="form.password_require_symbol"
                                    class="w-4 h-4 text-indigo-600 rounded border-gray-300" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">Exiger un symbole (!@#…)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Section 2 : Sessions & accès -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-base mb-5 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Sessions & contrôle d'accès
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Durée session : <strong>{{ form.session_lifetime_minutes }} min</strong>
                            </label>
                            <input type="range" v-model.number="form.session_lifetime_minutes" min="5" max="10080" step="5"
                                class="w-full accent-indigo-600" />
                            <div class="flex justify-between text-xs text-gray-400 mt-0.5"><span>5 min</span><span>7 jours</span></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Tentatives max : <strong>{{ form.max_login_attempts }}</strong>
                            </label>
                            <input type="range" v-model.number="form.max_login_attempts" min="1" max="20"
                                class="w-full accent-indigo-600" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Blocage : <strong>{{ form.lockout_minutes }} min</strong>
                            </label>
                            <input type="range" v-model.number="form.lockout_minutes" min="1" max="1440" step="5"
                                class="w-full accent-indigo-600" />
                        </div>

                        <div class="space-y-3 pt-1">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" v-model="form.single_session"
                                    class="w-4 h-4 text-indigo-600 rounded border-gray-300" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">Session unique (une seule connexion active)</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" v-model="form.require_2fa"
                                    class="w-4 h-4 text-indigo-600 rounded border-gray-300" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">Exiger 2FA pour tous les utilisateurs</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" v-model="form.log_all_access"
                                    class="w-4 h-4 text-indigo-600 rounded border-gray-300" />
                                <span class="text-sm text-gray-700 dark:text-gray-300">Journaliser tous les accès</span>
                            </label>
                        </div>
                    </div>

                    <!-- IP Whitelist -->
                    <div class="mt-6 pt-5 border-t border-gray-100 dark:border-gray-700">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Liste blanche IP (laisser vide pour autoriser toutes les IP)
                        </label>
                        <div class="flex gap-2 mb-3">
                            <input v-model="ipInput" @keydown.enter.prevent="addIp" type="text" placeholder="Ex: 192.168.1.1"
                                class="flex-1 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 focus:ring-2 focus:ring-indigo-500 outline-none" />
                            <button type="button" @click="addIp"
                                class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg text-sm font-medium hover:bg-indigo-200 transition">
                                Ajouter
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="ip in allowedIps" :key="ip"
                                class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 px-2 py-1 rounded text-xs">
                                {{ ip }}
                                <button type="button" @click="removeIp(ip)" class="ml-1 text-red-400 hover:text-red-600">×</button>
                            </span>
                            <span v-if="!allowedIps.length" class="text-xs text-gray-400 italic">Toutes les IP autorisées</span>
                        </div>
                    </div>
                </div>

                <!-- Bouton save -->
                <div class="flex justify-end">
                    <button type="submit" :disabled="saving"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-medium text-sm transition disabled:opacity-60 flex items-center gap-2">
                        <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ saving ? 'Enregistrement…' : 'Sauvegarder la politique' }}
                    </button>
                </div>
            </form>

            <!-- Sessions actives -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-base">Sessions actives</h3>
                    <div class="flex gap-2">
                        <button @click="loadSessions" type="button"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            Actualiser
                        </button>
                        <button v-if="sessions.length > 1" @click="killAllSessions" type="button"
                            class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                            Terminer toutes les autres
                        </button>
                    </div>
                </div>

                <div v-if="loadingSessions" class="py-6 text-center text-gray-400 text-sm">Chargement…</div>

                <div v-else-if="sessions.length === 0 && showSessions" class="py-6 text-center text-gray-400 text-sm">
                    Cliquez "Actualiser" pour charger les sessions.
                </div>
                <div v-else-if="!showSessions" class="py-4 text-center text-gray-400 text-sm">
                    <button @click="loadSessions" type="button" class="text-indigo-600 hover:underline">Afficher les sessions actives</button>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b dark:border-gray-700">
                                <th class="pb-2 font-medium">IP</th>
                                <th class="pb-2 font-medium">Navigateur</th>
                                <th class="pb-2 font-medium">Dernière activité</th>
                                <th class="pb-2 font-medium">Statut</th>
                                <th class="pb-2 font-medium"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="s in sessions" :key="s.id" class="border-b dark:border-gray-700 last:border-0">
                                <td class="py-2 font-mono text-xs">{{ s.ip_address }}</td>
                                <td class="py-2 text-gray-600 dark:text-gray-400 text-xs max-w-xs truncate">{{ s.user_agent }}</td>
                                <td class="py-2 text-gray-600 dark:text-gray-400">{{ s.last_activity }}</td>
                                <td class="py-2">
                                    <span v-if="s.is_current" class="inline-flex items-center gap-1 bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                        Courante
                                    </span>
                                    <span v-else class="text-xs text-gray-400">Active</span>
                                </td>
                                <td class="py-2">
                                    <button v-if="!s.is_current" @click="killSession(s.id)" type="button"
                                        class="text-xs text-red-500 hover:text-red-700 font-medium">
                                        Terminer
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Historique des accès -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white text-base mb-4">Historique des accès (50 derniers)</h3>
                <div v-if="!stats.recent_logs?.length" class="py-6 text-center text-gray-400 text-sm">
                    Aucun accès enregistré. Activez "Journaliser tous les accès" pour commencer.
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-xs text-gray-500 border-b dark:border-gray-700">
                                <th class="pb-2 font-medium">Utilisateur</th>
                                <th class="pb-2 font-medium">Action</th>
                                <th class="pb-2 font-medium">IP</th>
                                <th class="pb-2 font-medium">Date</th>
                                <th class="pb-2 font-medium text-center">Résultat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in stats.recent_logs" :key="log.id" class="border-b dark:border-gray-700 last:border-0">
                                <td class="py-2 text-gray-700 dark:text-gray-300">{{ log.user?.name ?? '—' }}</td>
                                <td class="py-2">
                                    <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-xs font-mono">
                                        {{ actionLabels[log.action] ?? log.action }}
                                    </span>
                                </td>
                                <td class="py-2 font-mono text-xs text-gray-500">{{ log.ip_address }}</td>
                                <td class="py-2 text-xs text-gray-500">{{ log.created_at }}</td>
                                <td class="py-2 text-center">
                                    <span v-if="log.success">✅</span>
                                    <span v-else>❌</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>

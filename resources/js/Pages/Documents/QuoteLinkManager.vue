<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    document: { type: Object, required: true },
});

const links     = ref([]);
const loading   = ref(false);
const generating = ref(false);
const error     = ref('');
const showPanel = ref(false);

// Options de génération
const expiresOption    = ref('7');   // '7', '30', 'none'
const usePassword      = ref(false);
const passwordVal      = ref('');
const allowComments    = ref(true);
const allowDecline     = ref(true);
const requireSignature = ref(true);

const latestActiveLink = computed(() => links.value.find(l => l.is_active));
const copied = ref(false);

function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

const fetchLinks = async () => {
    loading.value = true;
    error.value = '';
    try {
        const res = await fetch(`/documents/${props.document.id}/quote-links`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) {
            error.value = `Erreur serveur (${res.status}) — vérifiez que les migrations ont été exécutées.`;
            return;
        }
        links.value = await res.json();
    } catch {
        error.value = 'Impossible de charger les liens (erreur réseau).';
    } finally {
        loading.value = false;
    }
};

const openPanel = () => {
    showPanel.value = true;
    fetchLinks();
};

const generate = async () => {
    generating.value = true;
    error.value = '';
    try {
        const body = {
            expires_in_days:   expiresOption.value !== 'none' ? parseInt(expiresOption.value) : null,
            password:          usePassword.value ? passwordVal.value : null,
            allow_comments:    allowComments.value,
            allow_decline:     allowDecline.value,
            require_signature: requireSignature.value,
        };
        const res = await fetch(`/documents/${props.document.id}/quote-link`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf(), 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(body),
        });
        if (!res.ok) {
            const data = await res.json();
            error.value = data.message ?? 'Erreur lors de la génération.';
            return;
        }
        await fetchLinks();
        passwordVal.value = '';
        usePassword.value = false;
    } catch {
        error.value = 'Erreur réseau.';
    } finally {
        generating.value = false;
    }
};

const copyLink = async (url) => {
    await navigator.clipboard.writeText(url).catch(() => {});
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const revokeLink = async (link) => {
    if (!confirm('Révoquer ce lien ? Il ne sera plus accessible.')) return;
    await fetch(`/quote-links/${link.id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': getCsrf(), 'X-Requested-With': 'XMLHttpRequest' },
    });
    await fetchLinks();
};

const fmtDate = (d) => d ? new Date(d).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' }) : null;
</script>

<template>
    <div class="mt-2">
        <button
            v-if="!showPanel"
            @click="openPanel"
            class="rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50"
        >
            🔗 Lien de partage
        </button>

        <div v-else class="rounded-lg border border-indigo-100 bg-indigo-50/40 p-4 shadow-inner">
            <div class="mb-3 flex items-center justify-between">
                <h4 class="font-semibold text-indigo-800">🔗 Lien de partage du devis</h4>
                <button @click="showPanel = false" class="text-xs text-gray-400 hover:text-gray-600">✕ Fermer</button>
            </div>

            <p v-if="error" class="mb-2 text-sm text-red-600">{{ error }}</p>
            <p v-if="loading" class="text-sm text-gray-400">Chargement…</p>

            <!-- Lien actif existant -->
            <div v-if="latestActiveLink" class="mb-4 space-y-2">
                <div class="flex items-center gap-2 rounded-lg border bg-white p-3 shadow-sm">
                    <input
                        :value="latestActiveLink.url"
                        readonly
                        class="min-w-0 flex-1 rounded border-0 bg-transparent text-xs text-gray-700 outline-none"
                    />
                    <button
                        @click="copyLink(latestActiveLink.url)"
                        class="shrink-0 rounded-md bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-700"
                    >
                        {{ copied ? '✓ Copié !' : 'Copier' }}
                    </button>
                </div>

                <!-- Statut -->
                <div class="text-xs text-gray-600 space-y-0.5">
                    <div v-if="latestActiveLink.views_count">
                        👁 Vu {{ latestActiveLink.views_count }}×
                        <span v-if="latestActiveLink.viewed_at">(première fois le {{ fmtDate(latestActiveLink.viewed_at) }})</span>
                    </div>
                    <div v-if="latestActiveLink.signed_at" class="text-green-700 font-semibold">
                        ✅ Signé le {{ fmtDate(latestActiveLink.signed_at) }} par {{ latestActiveLink.client_name }}
                    </div>
                    <div v-else-if="latestActiveLink.declined_at" class="text-red-700 font-semibold">
                        ❌ Refusé — {{ latestActiveLink.decline_reason }}
                    </div>
                    <div v-if="latestActiveLink.expires_at" class="text-gray-400">
                        Expire le {{ fmtDate(latestActiveLink.expires_at) }}
                    </div>
                </div>

                <button @click="revokeLink(latestActiveLink)"
                    class="text-xs text-red-500 underline hover:text-red-700">
                    Révoquer ce lien
                </button>
            </div>

            <!-- Formulaire génération -->
            <div v-if="!latestActiveLink" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600">Expiration</label>
                    <select v-model="expiresOption"
                        class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="7">7 jours</option>
                        <option value="30">30 jours</option>
                        <option value="none">Sans limite</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <input v-model="usePassword" type="checkbox" id="ql-pwd" class="rounded" />
                    <label for="ql-pwd" class="text-xs text-gray-600">Protéger par mot de passe</label>
                </div>
                <input v-if="usePassword" v-model="passwordVal" type="text" placeholder="Mot de passe"
                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />

                <div class="flex flex-wrap gap-3 text-xs text-gray-600">
                    <label class="flex items-center gap-1">
                        <input v-model="allowComments" type="checkbox" class="rounded" /> Commentaires
                    </label>
                    <label class="flex items-center gap-1">
                        <input v-model="allowDecline" type="checkbox" class="rounded" /> Autoriser refus
                    </label>
                    <label class="flex items-center gap-1">
                        <input v-model="requireSignature" type="checkbox" class="rounded" /> Signature obligatoire
                    </label>
                </div>

                <button
                    @click="generate"
                    :disabled="generating"
                    class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ generating ? 'Génération…' : '🔗 Générer un lien de partage' }}
                </button>
            </div>
        </div>
    </div>
</template>

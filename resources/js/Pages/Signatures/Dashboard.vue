<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    signatures: Object,
    stats:      Object,
    filters:    Object,
});

// ─── Filtres ─────────────────────────────────────────────────────────────────
const currentFilter = ref(props.filters?.status ?? '');

function applyFilter(status) {
    currentFilter.value = status;
    router.get(route('signatures.dashboard'), { status: status || undefined }, { preserveState: true });
}

// ─── Badge couleur par statut ─────────────────────────────────────────────────
function badgeClass(status) {
    const map = {
        pending:  'bg-yellow-100 text-yellow-800',
        signed:   'bg-green-100 text-green-800',
        refused:  'bg-red-100 text-red-800',
        expired:  'bg-gray-100 text-gray-500',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
}

function badgeLabel(status) {
    const map = {
        pending: 'En attente',
        signed:  'Signé',
        refused: 'Refusé',
        expired: 'Expiré',
    };
    return map[status] ?? status;
}

// ─── Modal Nouvelle demande ───────────────────────────────────────────────────
const showModal    = ref(false);
const submitError  = ref('');
const submitting   = ref(false);

const form = reactive({
    signable_type: 'App\\Models\\Document',
    signable_id:   '',
    level:         'advanced',
    expires_days:  7,
    signers: [{ name: '', email: '', role: '' }],
});

function addSigner()    { form.signers.push({ name: '', email: '', role: '' }); }
function removeSigner(i){ if (form.signers.length > 1) form.signers.splice(i, 1); }

async function submitInvite() {
    submitError.value = '';
    submitting.value  = true;

    try {
        const res = await fetch(route('signatures.invite'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ ...form }),
        });

        const data = await res.json();

        if (!res.ok) {
            const msgs = Object.values(data.errors ?? {}).flat();
            submitError.value = msgs.join(' ') || data.message || 'Erreur lors de l\'envoi.';
            return;
        }

        showModal.value = false;
        router.reload({ only: ['signatures', 'stats'] });
    } catch (e) {
        submitError.value = 'Erreur réseau. Veuillez réessayer.';
    } finally {
        submitting.value = false;
    }
}

// ─── Téléchargement ──────────────────────────────────────────────────────────
function downloadSigned(id) {
    window.open(route('signatures.download', id), '_blank');
}

// ─── Formatage date ──────────────────────────────────────────────────────────
function fmtDate(d) {
    return d ? new Date(d).toLocaleDateString('fr-FR') : '—';
}
</script>

<template>
    <Head title="Signatures qualifiées" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold text-gray-900">Signatures qualifiées eIDAS</h1>
                <button
                    @click="showModal = true"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700"
                >
                    + Nouvelle demande
                </button>
            </div>
        </template>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-6">

            <!-- Statistiques -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div
                    v-for="(label, key) in { pending: 'En attente', signed: 'Signés', refused: 'Refusés', expired: 'Expirés' }"
                    :key="key"
                    @click="applyFilter(currentFilter === key ? '' : key)"
                    class="cursor-pointer rounded-xl border p-4 text-center transition hover:shadow-md"
                    :class="currentFilter === key ? 'border-blue-400 bg-blue-50' : 'border-gray-200 bg-white'"
                >
                    <div class="text-3xl font-bold text-gray-800">{{ stats[key] }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ label }}</div>
                </div>
            </div>

            <!-- Tableau -->
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Document</th>
                            <th class="px-4 py-3 text-left">Signataire</th>
                            <th class="px-4 py-3 text-left">Rôle</th>
                            <th class="px-4 py-3 text-left">Niveau</th>
                            <th class="px-4 py-3 text-left">Statut</th>
                            <th class="px-4 py-3 text-left">Invité le</th>
                            <th class="px-4 py-3 text-left">Signé le</th>
                            <th class="px-4 py-3 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="!signatures.data.length">
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400 italic">
                                Aucune demande de signature.
                            </td>
                        </tr>
                        <tr v-for="sig in signatures.data" :key="sig.id" class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ sig.signable?.title ?? sig.signable?.number ?? `#${sig.signable_id}` }}
                                <div class="text-xs text-gray-400">{{ sig.signable_type?.split('\\').pop() }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-700">{{ sig.signer_name }}</div>
                                <div class="text-xs text-gray-400">{{ sig.signer_email }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ sig.signer_role || '—' }}</td>
                            <td class="px-4 py-3 capitalize text-blue-700 font-medium">{{ sig.signature_level }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="badgeClass(sig.status)">
                                    {{ badgeLabel(sig.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ fmtDate(sig.invited_at) }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ fmtDate(sig.signed_at) }}</td>
                            <td class="px-4 py-3">
                                <button
                                    v-if="sig.status === 'signed'"
                                    @click="downloadSigned(sig.id)"
                                    class="rounded-lg border border-green-300 bg-green-50 px-3 py-1 text-xs font-semibold text-green-700 hover:bg-green-100"
                                >
                                    Télécharger
                                </button>
                                <a
                                    v-if="sig.status === 'pending'"
                                    :href="`/sign/${sig.token}`"
                                    target="_blank"
                                    class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                                >
                                    Voir le portail
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="signatures.last_page > 1" class="flex justify-center gap-1 border-t border-gray-100 py-3">
                    <a
                        v-for="link in signatures.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        class="rounded px-3 py-1 text-sm"
                        :class="link.active ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-100'"
                    ></a>
                </div>
            </div>
        </div>

        <!-- Modal Nouvelle demande -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/40 p-4 pt-10">
                <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-800">Nouvelle demande de signature</h2>
                        <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">×</button>
                    </div>

                    <div v-if="submitError" class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ submitError }}
                    </div>

                    <div class="space-y-4">
                        <!-- Type de document -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Type de document</label>
                            <select v-model="form.signable_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                                <option value="App\Models\Document">Facture / Devis / Bon de commande</option>
                                <option value="App\Models\CommercialContract">Contrat commercial</option>
                                <option value="App\Models\GedDocument">Document GED</option>
                            </select>
                        </div>

                        <!-- ID du document -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">ID du document</label>
                            <input v-model="form.signable_id" type="number" placeholder="ex: 42" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                        </div>

                        <!-- Niveau + Expiration -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Niveau</label>
                                <select v-model="form.level" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
                                    <option value="simple">Simple</option>
                                    <option value="advanced">Avancé</option>
                                    <option value="qualified">Qualifié</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Expire dans (jours)</label>
                                <input v-model.number="form.expires_days" type="number" min="1" max="90" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none" />
                            </div>
                        </div>

                        <!-- Signataires -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-semibold text-gray-500 uppercase">Signataires</label>
                                <button @click="addSigner" class="text-xs text-blue-600 hover:underline">+ Ajouter</button>
                            </div>
                            <div v-for="(signer, i) in form.signers" :key="i" class="mb-3 rounded-lg border border-gray-200 p-3 space-y-2">
                                <div class="flex gap-2">
                                    <input v-model="signer.name" type="text" placeholder="Nom complet" class="flex-1 rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-blue-500 focus:outline-none" />
                                    <button v-if="form.signers.length > 1" @click="removeSigner(i)" class="text-gray-300 hover:text-red-500 font-bold text-lg leading-none">×</button>
                                </div>
                                <input v-model="signer.email" type="email" placeholder="Email" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-blue-500 focus:outline-none" />
                                <input v-model="signer.role" type="text" placeholder="Rôle (acheteur, directeur…)" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-blue-500 focus:outline-none" />
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-5">
                        <button @click="showModal = false" class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600">
                            Annuler
                        </button>
                        <button
                            @click="submitInvite"
                            :disabled="submitting"
                            class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ submitting ? 'Envoi en cours…' : 'Envoyer les invitations' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

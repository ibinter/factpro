<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    requiresPassword: { type: Boolean, default: false },
    token: { type: String, required: true },
    expired: { type: Boolean, default: false },
    link: { type: Object, default: null },
    company: { type: Object, default: null },
    document: { type: Object, default: null },
});

/* -------------------------------------------------------------------------
 * Mot de passe
 * ---------------------------------------------------------------------- */
const passwordInput = ref('');
const passwordError = ref('');
const passwordLoading = ref(false);

const submitPassword = async () => {
    passwordError.value = '';
    passwordLoading.value = true;
    try {
        const res = await fetch(`/q/${props.token}/password`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
            body: JSON.stringify({ password: passwordInput.value }),
        });
        const data = await res.json();
        if (data.success) {
            router.reload();
        } else {
            passwordError.value = data.message ?? 'Mot de passe incorrect.';
        }
    } catch {
        passwordError.value = 'Erreur réseau, veuillez réessayer.';
    } finally {
        passwordLoading.value = false;
    }
};

/* -------------------------------------------------------------------------
 * Signature canvas
 * ---------------------------------------------------------------------- */
const canvasRef = ref(null);
const isDrawing = ref(false);
let lastX = 0, lastY = 0;

const startDraw = (e) => {
    isDrawing.value = true;
    const pos = getPos(e);
    lastX = pos.x;
    lastY = pos.y;
};
const draw = (e) => {
    if (!isDrawing.value) return;
    const ctx = canvasRef.value?.getContext('2d');
    if (!ctx) return;
    const pos = getPos(e);
    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(pos.x, pos.y);
    ctx.strokeStyle = '#1e293b';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.stroke();
    lastX = pos.x;
    lastY = pos.y;
    e.preventDefault();
};
const stopDraw = () => { isDrawing.value = false; };
const clearCanvas = () => {
    const canvas = canvasRef.value;
    if (!canvas) return;
    canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
};
const getPos = (e) => {
    const rect = canvasRef.value?.getBoundingClientRect();
    const src = e.touches?.[0] ?? e;
    return { x: src.clientX - (rect?.left ?? 0), y: src.clientY - (rect?.top ?? 0) };
};
const getSignatureData = () => canvasRef.value?.toDataURL('image/png') ?? null;
const isCanvasEmpty = () => {
    const canvas = canvasRef.value;
    if (!canvas) return true;
    const ctx = canvas.getContext('2d');
    const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
    return !data.some(v => v !== 0);
};

/* -------------------------------------------------------------------------
 * Formulaire d'acceptation
 * ---------------------------------------------------------------------- */
const clientName  = ref('');
const clientEmail = ref('');
const comment     = ref('');
const formErrors  = ref({});
const formLoading = ref(false);
const showConfirmModal = ref(false);
const successMessage = ref('');

const openConfirm = () => {
    formErrors.value = {};
    const errs = {};
    if (!clientName.value.trim()) errs.client_name = 'Votre nom est requis.';
    if (props.link?.allow_comments && !clientEmail.value.trim()) errs.client_email = 'Votre email est requis.';
    if (props.link?.require_signature && isCanvasEmpty()) errs.signature = 'Veuillez signer avant de valider.';
    if (Object.keys(errs).length) { formErrors.value = errs; return; }
    showConfirmModal.value = true;
};

const submitSign = async () => {
    formLoading.value = true;
    showConfirmModal.value = false;
    try {
        const res = await fetch(`/q/${props.token}/sign`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
            body: JSON.stringify({
                client_name:    clientName.value,
                client_email:   clientEmail.value || null,
                client_comment: comment.value || null,
                signature_data: props.link?.require_signature ? getSignatureData() : null,
            }),
        });
        const data = await res.json();
        if (data.success) {
            successMessage.value = data.message;
        } else {
            formErrors.value = data.errors ?? { general: data.message };
        }
    } catch {
        formErrors.value = { general: 'Erreur réseau.' };
    } finally {
        formLoading.value = false;
    }
};

/* -------------------------------------------------------------------------
 * Formulaire de refus
 * ---------------------------------------------------------------------- */
const showDeclineModal = ref(false);
const declineName   = ref('');
const declineReason = ref('');
const declineErrors = ref({});
const declineLoading = ref(false);
const declineSuccess = ref(false);

const submitDecline = async () => {
    declineErrors.value = {};
    const errs = {};
    if (!declineName.value.trim())   errs.client_name = 'Votre nom est requis.';
    if (!declineReason.value.trim()) errs.decline_reason = 'Veuillez préciser la raison.';
    if (Object.keys(errs).length) { declineErrors.value = errs; return; }

    declineLoading.value = true;
    try {
        const res = await fetch(`/q/${props.token}/decline`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrf() },
            body: JSON.stringify({ client_name: declineName.value, decline_reason: declineReason.value }),
        });
        const data = await res.json();
        if (data.success) {
            declineSuccess.value = true;
            showDeclineModal.value = false;
        } else {
            declineErrors.value = { general: data.message };
        }
    } catch {
        declineErrors.value = { general: 'Erreur réseau.' };
    } finally {
        declineLoading.value = false;
    }
};

/* -------------------------------------------------------------------------
 * Helpers
 * ---------------------------------------------------------------------- */
function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);
const fmtDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '';

const alreadySigned  = computed(() => !!props.link?.signed_at);
const alreadyDeclined = computed(() => !!props.link?.declined_at);
const isResolved = computed(() => alreadySigned.value || alreadyDeclined.value || successMessage.value || declineSuccess.value);
</script>

<template>
    <Head :title="document ? ('Devis ' + document.number) : 'Devis partagé'" />

    <!-- Layout standalone sans authentification -->
    <div class="min-h-screen bg-gray-50">

        <!-- Header -->
        <header class="border-b bg-white shadow-sm">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-4">
                <div class="flex items-center gap-3">
                    <span class="text-xl font-bold text-indigo-700">IBIG FactPro</span>
                </div>
                <div v-if="company" class="text-right text-sm text-gray-600">
                    <div class="font-semibold">{{ company.name }}</div>
                    <div v-if="company.email" class="text-xs text-gray-400">{{ company.email }}</div>
                </div>
            </div>
        </header>

        <!-- Lien expiré -->
        <div v-if="expired" class="mx-auto mt-16 max-w-md text-center">
            <div class="rounded-xl bg-white p-10 shadow-lg">
                <div class="mb-4 text-5xl">⏰</div>
                <h2 class="text-xl font-bold text-gray-800">Ce lien a expiré</h2>
                <p class="mt-2 text-sm text-gray-500">Contactez l'émetteur du devis pour obtenir un nouveau lien.</p>
            </div>
        </div>

        <!-- Mot de passe requis -->
        <div v-else-if="requiresPassword" class="mx-auto mt-16 max-w-sm">
            <div class="rounded-xl bg-white p-8 shadow-lg">
                <h2 class="mb-1 text-lg font-bold text-gray-800">Accès protégé</h2>
                <p class="mb-4 text-sm text-gray-500">Ce lien est protégé par un mot de passe.</p>
                <input
                    v-model="passwordInput"
                    type="password"
                    placeholder="Mot de passe"
                    class="w-full rounded-lg border-gray-300 px-4 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    @keyup.enter="submitPassword"
                />
                <p v-if="passwordError" class="mt-1 text-sm text-red-600">{{ passwordError }}</p>
                <button
                    @click="submitPassword"
                    :disabled="passwordLoading"
                    class="mt-4 w-full rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{ passwordLoading ? 'Vérification…' : 'Accéder au devis' }}
                </button>
            </div>
        </div>

        <!-- Contenu principal du devis -->
        <main v-else-if="document" class="mx-auto max-w-4xl space-y-6 px-4 py-8">

            <!-- Bandeau statut -->
            <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <span class="font-bold text-indigo-800">{{ document.type_label }} {{ document.number }}</span>
                        <span v-if="company"> — <span class="text-indigo-700">{{ company.name }}</span></span>
                    </div>
                    <div v-if="link?.expires_at" class="text-sm text-indigo-600">
                        Expire le {{ fmtDate(link.expires_at) }}
                    </div>
                </div>
            </div>

            <!-- État final : signé -->
            <div v-if="successMessage || alreadySigned" class="rounded-xl border border-green-200 bg-green-50 p-6 text-center shadow">
                <div class="mb-2 text-4xl">✅</div>
                <h2 class="text-lg font-bold text-green-800">
                    {{ successMessage || ('Devis accepté le ' + fmtDate(link?.signed_at) + ' par ' + (link?.client_name ?? '')) }}
                </h2>
            </div>

            <!-- État final : refusé -->
            <div v-else-if="declineSuccess || alreadyDeclined" class="rounded-xl border border-red-200 bg-red-50 p-6 text-center shadow">
                <div class="mb-2 text-4xl">❌</div>
                <h2 class="text-lg font-bold text-red-800">Vous avez décliné ce devis.</h2>
            </div>

            <template v-else>
                <!-- Infos client + document -->
                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="rounded-xl bg-white p-6 shadow">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Client</h3>
                        <div v-if="document.customer">
                            <div class="font-semibold text-gray-800">{{ document.customer.name }}</div>
                            <div class="mt-1 text-sm text-gray-500">
                                <div v-if="document.customer.address">{{ document.customer.address }}</div>
                                <div v-if="document.customer.city">{{ document.customer.city }}</div>
                                <div v-if="document.customer.email">{{ document.customer.email }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-xl bg-white p-6 shadow">
                        <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Informations</h3>
                        <dl class="space-y-1 text-sm">
                            <div class="flex justify-between"><dt class="text-gray-500">Émis le</dt><dd>{{ fmtDate(document.issue_date) }}</dd></div>
                            <div v-if="document.due_date" class="flex justify-between"><dt class="text-gray-500">Valide jusqu'au</dt><dd>{{ fmtDate(document.due_date) }}</dd></div>
                            <div class="flex justify-between"><dt class="text-gray-500">Devise</dt><dd>{{ document.currency }}</dd></div>
                        </dl>
                    </div>
                </div>

                <!-- Lignes du devis -->
                <div class="overflow-hidden rounded-xl bg-white shadow">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-indigo-700 text-left text-xs uppercase tracking-wide text-white">
                                <tr>
                                    <th class="px-6 py-3">Désignation</th>
                                    <th class="px-6 py-3 text-right">Qté</th>
                                    <th class="px-6 py-3 text-right">P.U. HT</th>
                                    <th class="px-6 py-3 text-right">Total HT</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="line in document.lines" :key="line.id">
                                    <td class="px-6 py-3">{{ line.description }}</td>
                                    <td class="px-6 py-3 text-right">{{ line.quantity }}</td>
                                    <td class="px-6 py-3 text-right">{{ fmt(line.unit_price) }}</td>
                                    <td class="px-6 py-3 text-right font-semibold">{{ fmt(line.line_total) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50 text-sm">
                                <tr>
                                    <td colspan="3" class="px-6 py-2 text-right text-gray-500">Sous-total HT</td>
                                    <td class="px-6 py-2 text-right font-semibold">{{ fmt(document.subtotal) }} {{ document.currency }}</td>
                                </tr>
                                <tr v-if="Number(document.discount_amount) > 0">
                                    <td colspan="3" class="px-6 py-2 text-right text-gray-500">Remise</td>
                                    <td class="px-6 py-2 text-right text-red-600">−{{ fmt(document.discount_amount) }} {{ document.currency }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-6 py-2 text-right text-gray-500">TVA</td>
                                    <td class="px-6 py-2 text-right font-semibold">{{ fmt(document.tax_amount) }} {{ document.currency }}</td>
                                </tr>
                                <tr class="border-t-2 border-indigo-700">
                                    <td colspan="3" class="px-6 py-3 text-right font-bold text-indigo-800">TOTAL TTC</td>
                                    <td class="px-6 py-3 text-right text-lg font-bold text-indigo-800">{{ fmt(document.total) }} {{ document.currency }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Section formulaire client -->
                <div class="rounded-xl bg-white p-6 shadow">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-400">Votre réponse</h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Votre nom *</label>
                            <input v-model="clientName" type="text" placeholder="Jean Dupont"
                                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <p v-if="formErrors.client_name" class="mt-1 text-sm text-red-600">{{ formErrors.client_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Votre email <span v-if="link?.allow_comments">*</span>
                            </label>
                            <input v-model="clientEmail" type="email" placeholder="jean@exemple.com"
                                class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <p v-if="formErrors.client_email" class="mt-1 text-sm text-red-600">{{ formErrors.client_email }}</p>
                        </div>
                    </div>

                    <!-- Commentaire optionnel -->
                    <div v-if="link?.allow_comments" class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Votre message (optionnel)</label>
                        <textarea v-model="comment" rows="3" placeholder="Questions, remarques…"
                            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>

                    <!-- Canvas de signature -->
                    <div v-if="link?.require_signature" class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Votre signature *</label>
                        <p class="mb-2 text-xs text-gray-400">Tracez votre signature dans le cadre ci-dessous.</p>
                        <div class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50">
                            <canvas
                                ref="canvasRef"
                                width="600"
                                height="150"
                                class="w-full touch-none rounded-lg"
                                @mousedown="startDraw"
                                @mousemove="draw"
                                @mouseup="stopDraw"
                                @mouseleave="stopDraw"
                                @touchstart.prevent="startDraw"
                                @touchmove.prevent="draw"
                                @touchend="stopDraw"
                            ></canvas>
                        </div>
                        <button @click="clearCanvas" class="mt-1 text-xs text-gray-400 underline hover:text-gray-600">
                            Effacer
                        </button>
                        <p v-if="formErrors.signature" class="mt-1 text-sm text-red-600">{{ formErrors.signature }}</p>
                    </div>

                    <p v-if="formErrors.general" class="mt-2 text-sm text-red-600">{{ formErrors.general }}</p>

                    <!-- Boutons d'action -->
                    <div class="mt-6 flex flex-wrap justify-end gap-3">
                        <button v-if="link?.allow_decline"
                            @click="showDeclineModal = true"
                            class="rounded-lg border border-red-300 bg-white px-5 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">
                            ❌ Je décline
                        </button>
                        <button
                            @click="openConfirm"
                            :disabled="formLoading"
                            class="rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50">
                            {{ formLoading ? 'Enregistrement…' : "✅ J'accepte ce devis" }}
                        </button>
                    </div>
                </div>
            </template>
        </main>

        <!-- Modal confirmation acceptation -->
        <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-2xl">
                <h3 class="mb-2 text-lg font-bold text-gray-800">Confirmer l'acceptation</h3>
                <p class="mb-4 text-sm text-gray-600">
                    En cliquant sur « Confirmer », vous acceptez formellement le devis
                    <strong>{{ document?.number }}</strong>.
                </p>
                <div class="flex justify-end gap-3">
                    <button @click="showConfirmModal = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button @click="submitSign" :disabled="formLoading"
                        class="rounded-lg bg-green-600 px-5 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50">
                        ✅ Confirmer
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal refus -->
        <div v-if="showDeclineModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-2xl">
                <h3 class="mb-2 text-lg font-bold text-gray-800">Décliner ce devis</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Votre nom *</label>
                        <input v-model="declineName" type="text"
                            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500" />
                        <p v-if="declineErrors.client_name" class="mt-1 text-xs text-red-600">{{ declineErrors.client_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Raison du refus *</label>
                        <textarea v-model="declineReason" rows="3"
                            class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                        <p v-if="declineErrors.decline_reason" class="mt-1 text-xs text-red-600">{{ declineErrors.decline_reason }}</p>
                    </div>
                    <p v-if="declineErrors.general" class="text-xs text-red-600">{{ declineErrors.general }}</p>
                </div>
                <div class="mt-4 flex justify-end gap-3">
                    <button @click="showDeclineModal = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button @click="submitDecline" :disabled="declineLoading"
                        class="rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50">
                        {{ declineLoading ? 'Envoi…' : '❌ Confirmer le refus' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

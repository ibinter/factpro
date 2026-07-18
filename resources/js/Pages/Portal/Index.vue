<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';

const props = defineProps({
    token: String,
    company: Object,
    customer: Object,
    documents: Array,
    stats: Object,
});

const page = usePage();
const flash = computed(() => page.props.flash ?? {});
const flashVisible = ref(false);
watch(
    () => [flash.value.success, flash.value.error],
    ([success, error]) => {
        if (success || error) {
            flashVisible.value = true;
            setTimeout(() => (flashVisible.value = false), 6000);
        }
    },
    { immediate: true },
);

const money = (amount, currency) =>
    new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: currency || 'XOF',
        maximumFractionDigits: currency === 'XOF' ? 0 : 2,
    }).format(amount ?? 0);

const formatDate = (value) =>
    value ? new Date(value).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' }) : '—';

const STATUS_LABELS = {
    draft: 'Brouillon',
    sent: 'Envoyé',
    viewed: 'Consulté',
    accepted: 'Accepté',
    rejected: 'Refusé',
    partial: 'Partiellement payé',
    paid: 'Payé',
    overdue: 'En retard',
    cancelled: 'Annulé',
    converted: 'Converti',
};

const STATUS_CLASSES = {
    sent: 'bg-blue-100 text-blue-700',
    viewed: 'bg-indigo-100 text-indigo-700',
    accepted: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
    partial: 'bg-amber-100 text-amber-700',
    paid: 'bg-green-100 text-green-700',
    overdue: 'bg-red-100 text-red-700',
    cancelled: 'bg-gray-100 text-gray-500',
    converted: 'bg-gray-100 text-gray-600',
};

const statusLabel = (s) => STATUS_LABELS[s] ?? s;
const statusClass = (s) => STATUS_CLASSES[s] ?? 'bg-gray-100 text-gray-600';

const pdfUrl = (doc) => `/portal/${props.token}/documents/${doc.uuid}/pdf`;

const processing = ref(false);
const rejecting = ref(null); // document en cours de refus (mini-modale)
const rejectComment = ref('');

const sendDecision = (doc, decision, comment = null, extra = {}) => {
    processing.value = true;
    router.post(
        `/portal/${props.token}/documents/${doc.uuid}/decision`,
        { decision, comment, ...extra },
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false;
                rejecting.value = null;
                rejectComment.value = '';
                accepting.value = null;
                signerName.value = '';
            },
        },
    );
};

const confirmReject = () => sendDecision(rejecting.value, 'reject', rejectComment.value || null);

/* ─── Acceptation + signature électronique (cahier §22.1) ─── */
const accepting = ref(null); // devis en cours d'acceptation
const signerName = ref('');
const canvasEl = ref(null);
const hasDrawn = ref(false);
let ctx = null;
let drawing = false;

const openAccept = (doc) => {
    accepting.value = doc;
    signerName.value = '';
    hasDrawn.value = false;
    nextTick(initCanvas);
};

const initCanvas = () => {
    const c = canvasEl.value;
    if (!c) return;
    const rect = c.getBoundingClientRect();
    c.width = rect.width;
    c.height = rect.height;
    ctx = c.getContext('2d');
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, c.width, c.height);
    ctx.lineWidth = 2.2;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#1a2332';
};

const pointFrom = (e) => {
    const rect = canvasEl.value.getBoundingClientRect();
    return { x: e.clientX - rect.left, y: e.clientY - rect.top };
};

const startDraw = (e) => {
    if (!ctx) return;
    drawing = true;
    const p = pointFrom(e);
    ctx.beginPath();
    ctx.moveTo(p.x, p.y);
    canvasEl.value.setPointerCapture?.(e.pointerId);
};

const moveDraw = (e) => {
    if (!drawing || !ctx) return;
    const p = pointFrom(e);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
    hasDrawn.value = true;
};

const endDraw = () => {
    drawing = false;
};

const clearSignature = () => {
    if (!ctx || !canvasEl.value) return;
    ctx.fillStyle = '#ffffff';
    ctx.fillRect(0, 0, canvasEl.value.width, canvasEl.value.height);
    hasDrawn.value = false;
};

const canSign = computed(() => hasDrawn.value && signerName.value.trim().length > 0);

const confirmAcceptSigned = () => {
    if (!canSign.value) return;
    sendDecision(accepting.value, 'accept', null, {
        signature: canvasEl.value.toDataURL('image/png'),
        signer_name: signerName.value.trim(),
    });
};

const acceptWithoutSignature = () => sendDecision(accepting.value, 'accept');
</script>

<template>
    <Head :title="`Espace client — ${company.name}`" />

    <div class="min-h-screen bg-gray-100">
        <!-- Bandeau société -->
        <header class="bg-gradient-to-r from-brand-950 to-brand-800 px-4 py-8 text-white sm:px-8">
            <div class="mx-auto flex max-w-5xl flex-col items-center gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <img
                        v-if="company.logo_path"
                        :src="'/storage/' + company.logo_path"
                        :alt="company.name"
                        class="h-14 w-auto max-w-[160px] rounded bg-white/90 object-contain p-1"
                    />
                    <div>
                        <h1 class="text-2xl font-extrabold tracking-tight">{{ company.name }}</h1>
                        <p class="mt-0.5 text-xs text-white/60">Espace client sécurisé</p>
                    </div>
                </div>
                <div class="text-center text-xs text-white/70 sm:text-right">
                    <p v-if="company.address || company.city">{{ [company.address, company.city].filter(Boolean).join(', ') }}</p>
                    <p v-if="company.phone">📞 {{ company.phone }}</p>
                    <p v-if="company.email">✉️ {{ company.email }}</p>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8 sm:px-8">
            <!-- Flash -->
            <transition
                enter-active-class="transition duration-300"
                enter-from-class="-translate-y-2 opacity-0"
                leave-active-class="transition duration-300"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="flashVisible && (flash.success || flash.error)"
                    class="mb-6 rounded-lg px-4 py-3 text-sm font-semibold shadow"
                    :class="flash.success ? 'bg-green-50 text-green-700 ring-1 ring-green-200' : 'bg-red-50 text-red-700 ring-1 ring-red-200'"
                >
                    {{ flash.success || flash.error }}
                </div>
            </transition>

            <!-- Bienvenue -->
            <div class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-gray-800">Bonjour {{ customer.name }} 👋</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Retrouvez ici vos documents émis par <b>{{ company.name }}</b> : consultez-les,
                    téléchargez les PDF et répondez aux devis en attente.
                </p>
            </div>

            <!-- Tuiles stats -->
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total facturé</p>
                    <p class="mt-2 text-2xl font-extrabold text-brand-900">{{ money(stats.invoiced, stats.currency) }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total payé</p>
                    <p class="mt-2 text-2xl font-extrabold text-green-600">{{ money(stats.paid, stats.currency) }}</p>
                </div>
                <div class="rounded-2xl bg-white p-5 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Restant dû</p>
                    <p class="mt-2 text-2xl font-extrabold" :class="stats.due > 0 ? 'text-red-600' : 'text-gray-700'">
                        {{ money(stats.due, stats.currency) }}
                    </p>
                </div>
            </div>

            <!-- Documents -->
            <div class="mt-6 overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="border-b px-6 py-4">
                    <h3 class="font-bold text-gray-800">Vos documents</h3>
                </div>

                <div v-if="!documents.length" class="px-6 py-12 text-center text-sm text-gray-400">
                    Aucun document disponible pour le moment.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Document</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Échéance</th>
                                <th class="px-6 py-3 text-right">Total</th>
                                <th class="px-6 py-3 text-center">Statut</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="doc in documents" :key="doc.uuid" class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <div class="font-semibold text-gray-800">{{ doc.type_label }}</div>
                                    <div class="text-xs text-gray-400">{{ doc.number }}</div>
                                    <span
                                        v-if="doc.signed"
                                        class="mt-1 inline-block rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-700"
                                        :title="doc.signed_by_name ? `Signé par ${doc.signed_by_name}` : 'Signé'"
                                    >
                                        ✍ Signé
                                    </span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">{{ formatDate(doc.issue_date) }}</td>
                                <td class="px-6 py-3 whitespace-nowrap">{{ formatDate(doc.due_date) }}</td>
                                <td class="px-6 py-3 text-right">
                                    <div class="font-bold text-gray-800">{{ money(doc.total, doc.currency) }}</div>
                                    <div v-if="doc.balance_due > 0 && doc.amount_paid > 0" class="text-xs text-red-500">
                                        Reste {{ money(doc.balance_due, doc.currency) }}
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusClass(doc.status)">
                                        {{ statusLabel(doc.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a
                                            :href="pdfUrl(doc)"
                                            target="_blank"
                                            class="rounded-md bg-brand-50 px-3 py-1.5 text-xs font-semibold text-brand-700 hover:bg-brand-100"
                                        >
                                            📄 PDF
                                        </a>
                                        <template v-if="doc.canDecide">
                                            <button
                                                :disabled="processing"
                                                class="rounded-md bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700 disabled:opacity-50"
                                                @click="openAccept(doc)"
                                            >
                                                ✓ Accepter
                                            </button>
                                            <button
                                                :disabled="processing"
                                                class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                                                @click="rejecting = doc"
                                            >
                                                ✗ Refuser
                                            </button>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <footer class="mt-8 text-center text-xs text-gray-400">
                Propulsé par <b>IBIG FactPro</b> — documents vérifiables par QR code
            </footer>
        </main>

        <!-- Mini-modale refus -->
        <div
            v-if="rejecting"
            class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-4 sm:items-center"
            @click.self="rejecting = null"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-gray-800">Refuser le devis {{ rejecting.number }} ?</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Vous pouvez préciser la raison de votre refus (optionnel) — elle sera transmise à {{ company.name }}.
                </p>
                <textarea
                    v-model="rejectComment"
                    rows="3"
                    maxlength="500"
                    placeholder="Commentaire (optionnel)…"
                    class="mt-4 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                ></textarea>
                <div class="mt-5 flex justify-end gap-3">
                    <button
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        @click="rejecting = null"
                    >
                        Annuler
                    </button>
                    <button
                        :disabled="processing"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                        @click="confirmReject"
                    >
                        Confirmer le refus
                    </button>
                </div>
            </div>
        </div>

        <!-- Modale acceptation + signature électronique -->
        <div
            v-if="accepting"
            class="fixed inset-0 z-50 flex items-end justify-center bg-black/50 p-4 sm:items-center"
            @click.self="accepting = null"
        >
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
                <h3 class="text-lg font-bold text-gray-800">Accepter le devis {{ accepting.number }}</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Signez ci-dessous pour valider votre accord, puis confirmez.
                </p>

                <label class="mt-4 block text-xs font-semibold text-gray-600">Nom complet *</label>
                <input
                    v-model="signerName"
                    type="text"
                    maxlength="100"
                    placeholder="Votre nom et prénom"
                    class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                />

                <div class="mt-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-semibold text-gray-600">Signature</label>
                        <button
                            type="button"
                            class="text-xs font-semibold text-brand-600 hover:underline"
                            @click="clearSignature"
                        >
                            Effacer
                        </button>
                    </div>
                    <canvas
                        ref="canvasEl"
                        class="mt-1 block h-[160px] w-full touch-none rounded-md border-2 border-dashed border-gray-300 bg-white"
                        @pointerdown="startDraw"
                        @pointermove="moveDraw"
                        @pointerup="endDraw"
                        @pointerleave="endDraw"
                        @pointercancel="endDraw"
                    ></canvas>
                </div>

                <div class="mt-5 flex flex-col gap-3">
                    <button
                        :disabled="processing || !canSign"
                        class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50"
                        @click="confirmAcceptSigned"
                    >
                        ✍ Accepter et signer
                    </button>
                    <div class="flex items-center justify-between">
                        <button
                            class="text-xs text-gray-400 hover:text-gray-600 hover:underline"
                            @click="acceptWithoutSignature"
                        >
                            Accepter sans signer
                        </button>
                        <button
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            @click="accepting = null"
                        >
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

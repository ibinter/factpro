<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    signature: Object,
    documentName: String,
    token: String,
});

// ─── État des étapes ─────────────────────────────────────────────────────────
// step: 'info' | 'otp' | 'draw' | 'done' | 'refused' | 'expired' | 'already'
const step  = ref(
    props.signature.status === 'signed'   ? 'already' :
    props.signature.status === 'refused'  ? 'refused'  :
    props.signature.status === 'expired'  ? 'expired'  :
    'info'
);

const loading  = ref(false);
const errorMsg = ref('');

// ─── Étape 1 : Présentation → envoyer OTP ────────────────────────────────────
async function sendOtp() {
    loading.value  = true;
    errorMsg.value = '';
    try {
        await fetch(`/sign/${props.token}/otp`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content, 'Accept': 'application/json' },
        });
        step.value = 'otp';
    } catch (e) {
        errorMsg.value = 'Erreur lors de l\'envoi du code. Veuillez réessayer.';
    } finally {
        loading.value = false;
    }
}

// ─── Étape 2 : Vérification OTP ──────────────────────────────────────────────
const otpCode = ref('');

function verifyOtp() {
    if (otpCode.value.length !== 6) {
        errorMsg.value = 'Veuillez saisir le code à 6 chiffres.';
        return;
    }
    errorMsg.value = '';
    step.value = 'draw';
}

// ─── Étape 3 : Signature manuscrite sur canvas ───────────────────────────────
const canvasRef   = ref(null);
let isDrawing     = false;
let lastX = 0, lastY = 0;

function getPos(e) {
    const rect = canvasRef.value.getBoundingClientRect();
    const src  = e.touches ? e.touches[0] : e;
    return { x: src.clientX - rect.left, y: src.clientY - rect.top };
}

function startDraw(e) {
    e.preventDefault();
    isDrawing = true;
    const { x, y } = getPos(e);
    lastX = x; lastY = y;
}

function draw(e) {
    if (!isDrawing) return;
    e.preventDefault();
    const ctx = canvasRef.value.getContext('2d');
    const { x, y } = getPos(e);
    ctx.strokeStyle = '#1e3a5f';
    ctx.lineWidth   = 2.5;
    ctx.lineCap     = 'round';
    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
    ctx.lineTo(x, y);
    ctx.stroke();
    lastX = x; lastY = y;
}

function stopDraw() { isDrawing = false; }

function clearCanvas() {
    const canvas = canvasRef.value;
    const ctx    = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

// ─── Étape 3 → signer ────────────────────────────────────────────────────────
const signResult = ref(null);

async function confirmSignature() {
    const canvas = canvasRef.value;
    const blank  = document.createElement('canvas');
    blank.width  = canvas.width;
    blank.height = canvas.height;
    if (canvas.toDataURL() === blank.toDataURL()) {
        errorMsg.value = 'Veuillez signer dans la zone prévue à cet effet.';
        return;
    }

    loading.value  = true;
    errorMsg.value = '';

    try {
        const res = await fetch(`/sign/${props.token}/sign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                otp:            otpCode.value,
                signature_data: canvas.toDataURL('image/png'),
            }),
        });

        const data = await res.json();

        if (!res.ok) {
            errorMsg.value = data.error ?? 'Une erreur est survenue.';
            if (data.error?.includes('OTP')) step.value = 'otp';
            return;
        }

        signResult.value = data;
        step.value = 'done';
    } catch (e) {
        errorMsg.value = 'Erreur réseau. Veuillez réessayer.';
    } finally {
        loading.value = false;
    }
}

// ─── Refus ───────────────────────────────────────────────────────────────────
const refuseReason = ref('');
const showRefuseModal = ref(false);

async function submitRefuse() {
    loading.value = true;
    try {
        await fetch(`/sign/${props.token}/refuse`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ reason: refuseReason.value }),
        });
        step.value = 'refused';
    } finally {
        loading.value = false;
        showRefuseModal.value = false;
    }
}
</script>

<template>
    <GuestLayout>
        <div class="space-y-4">

            <!-- En-tête commun -->
            <div class="text-center">
                <h1 class="text-xl font-bold text-gray-800">Signature de document</h1>
                <p class="mt-1 text-sm text-gray-500">{{ documentName }}</p>
            </div>

            <!-- Erreur globale -->
            <div v-if="errorMsg" class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ errorMsg }}
            </div>

            <!-- ── Étape : Déjà signé ── -->
            <div v-if="step === 'already'" class="rounded-lg border border-green-300 bg-green-50 px-5 py-6 text-center">
                <div class="text-3xl mb-2">✅</div>
                <p class="font-semibold text-green-800">Ce document a déjà été signé.</p>
                <p class="text-sm text-green-600 mt-1">Signé le {{ new Date(signature.signed_at).toLocaleString('fr-FR') }}</p>
            </div>

            <!-- ── Étape : Refusé ── -->
            <div v-else-if="step === 'refused'" class="rounded-lg border border-orange-300 bg-orange-50 px-5 py-6 text-center">
                <div class="text-3xl mb-2">🚫</div>
                <p class="font-semibold text-orange-800">Vous avez refusé de signer ce document.</p>
            </div>

            <!-- ── Étape : Expiré ── -->
            <div v-else-if="step === 'expired'" class="rounded-lg border border-gray-300 bg-gray-50 px-5 py-6 text-center">
                <div class="text-3xl mb-2">⏰</div>
                <p class="font-semibold text-gray-700">Ce lien de signature a expiré.</p>
                <p class="text-sm text-gray-500 mt-1">Demandez un nouveau lien à l'émetteur.</p>
            </div>

            <!-- ── Étape 1 : Présentation ── -->
            <div v-else-if="step === 'info'" class="space-y-4">
                <div class="rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Document</span>
                        <span class="font-medium text-gray-800">{{ documentName }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Signataire</span>
                        <span class="font-medium text-gray-800">{{ signature.signer_name }}</span>
                    </div>
                    <div v-if="signature.signer_role" class="flex justify-between">
                        <span class="text-gray-500">Rôle</span>
                        <span class="font-medium text-gray-800">{{ signature.signer_role }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Niveau</span>
                        <span class="font-medium capitalize text-blue-700">{{ signature.signature_level }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Expire le</span>
                        <span class="font-medium text-gray-800">{{ new Date(signature.expires_at).toLocaleDateString('fr-FR') }}</span>
                    </div>
                </div>

                <p class="text-xs text-gray-500">
                    Un code de vérification sera envoyé à l'adresse <strong>{{ signature.signer_email }}</strong> pour valider votre identité.
                </p>

                <button
                    @click="sendOtp"
                    :disabled="loading"
                    class="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ loading ? 'Envoi en cours…' : 'Envoyer le code de vérification' }}
                </button>

                <button
                    @click="showRefuseModal = true"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-500 hover:bg-gray-50"
                >
                    Refuser de signer
                </button>
            </div>

            <!-- ── Étape 2 : Saisie OTP ── -->
            <div v-else-if="step === 'otp'" class="space-y-4">
                <p class="text-sm text-gray-600 text-center">
                    Un code à 6 chiffres a été envoyé à <strong>{{ signature.signer_email }}</strong>.
                </p>

                <input
                    v-model="otpCode"
                    type="text"
                    maxlength="6"
                    inputmode="numeric"
                    placeholder="_ _ _ _ _ _"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-center text-2xl font-mono tracking-[0.5em] focus:border-blue-500 focus:outline-none"
                    @input="otpCode = otpCode.replace(/\D/g, '')"
                />

                <button
                    @click="verifyOtp"
                    :disabled="otpCode.length !== 6"
                    class="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-50"
                >
                    Vérifier le code
                </button>

                <button @click="step = 'info'" class="w-full text-xs text-gray-400 hover:underline">
                    ← Renvoyer le code
                </button>
            </div>

            <!-- ── Étape 3 : Dessin signature ── -->
            <div v-else-if="step === 'draw'" class="space-y-3">
                <p class="text-sm text-gray-600 text-center">Signez dans la zone ci-dessous :</p>

                <div class="relative rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 overflow-hidden">
                    <canvas
                        ref="canvasRef"
                        width="440"
                        height="160"
                        class="w-full touch-none cursor-crosshair"
                        @mousedown="startDraw"
                        @mousemove="draw"
                        @mouseup="stopDraw"
                        @mouseleave="stopDraw"
                        @touchstart="startDraw"
                        @touchmove="draw"
                        @touchend="stopDraw"
                    ></canvas>
                    <p class="absolute bottom-1 right-2 text-[10px] text-gray-300 pointer-events-none">Signez ici</p>
                </div>

                <div class="flex gap-2">
                    <button
                        @click="clearCanvas"
                        class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50"
                    >
                        Effacer
                    </button>
                    <button
                        @click="confirmSignature"
                        :disabled="loading"
                        class="flex-1 rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50"
                    >
                        {{ loading ? 'Envoi…' : 'Confirmer la signature' }}
                    </button>
                </div>
            </div>

            <!-- ── Étape finale : Confirmation ── -->
            <div v-else-if="step === 'done'" class="rounded-lg border border-green-300 bg-green-50 p-5 text-center space-y-3">
                <div class="text-4xl">✅</div>
                <h2 class="text-lg font-bold text-green-800">Document signé avec succès !</h2>
                <p class="text-sm text-green-700">Votre signature a été enregistrée et le document est maintenant certifié.</p>

                <div v-if="signResult" class="mt-3 rounded-lg border border-green-200 bg-white p-3 text-left text-xs text-gray-600 space-y-1 font-mono">
                    <div><span class="text-gray-400">Date :</span> {{ new Date(signResult.signed_at).toLocaleString('fr-FR') }}</div>
                    <div><span class="text-gray-400">IP :</span> {{ signResult.ip_address }}</div>
                    <div class="break-all"><span class="text-gray-400">Hash :</span> {{ signResult.document_hash }}</div>
                </div>

                <p class="text-xs text-gray-400 mt-2">
                    Signature eIDAS niveau avancé — Règlement UE n° 910/2014
                </p>
            </div>

        </div>

        <!-- Modal refus -->
        <Teleport to="body">
            <div v-if="showRefuseModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                    <h3 class="font-bold text-gray-800 mb-3">Refuser la signature</h3>
                    <textarea
                        v-model="refuseReason"
                        rows="3"
                        placeholder="Motif du refus (optionnel)"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-orange-500 focus:outline-none"
                    ></textarea>
                    <div class="flex gap-2 mt-4">
                        <button @click="showRefuseModal = false" class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-600">
                            Annuler
                        </button>
                        <button @click="submitRefuse" :disabled="loading" class="flex-1 rounded-lg bg-orange-600 px-3 py-2 text-sm font-semibold text-white hover:bg-orange-700 disabled:opacity-50">
                            Confirmer le refus
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </GuestLayout>
</template>

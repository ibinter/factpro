<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

const props = defineProps({ document: Object, company: Object })

const canvas = ref(null)
const signedBy = ref(props.document.customer?.name ?? '')
let ctx = null
let drawing = false
let lastX = 0, lastY = 0

onMounted(() => {
    ctx = canvas.value.getContext('2d')
    ctx.strokeStyle = '#1a1a2e'
    ctx.lineWidth = 2.5
    ctx.lineCap = 'round'
    ctx.lineJoin = 'round'
    resizeCanvas()
    window.addEventListener('resize', resizeCanvas)
})

function resizeCanvas() {
    const rect = canvas.value.parentElement.getBoundingClientRect()
    canvas.value.width = rect.width
    canvas.value.height = Math.min(rect.height, 280)
}

function getPos(e) {
    const rect = canvas.value.getBoundingClientRect()
    const src = e.touches ? e.touches[0] : e
    return { x: src.clientX - rect.left, y: src.clientY - rect.top }
}

function startDraw(e) {
    e.preventDefault()
    drawing = true
    const { x, y } = getPos(e)
    lastX = x; lastY = y
    ctx.beginPath()
    ctx.moveTo(x, y)
}

function draw(e) {
    if (!drawing) return
    e.preventDefault()
    const { x, y } = getPos(e)
    ctx.lineTo(x, y)
    ctx.stroke()
    lastX = x; lastY = y
}

function stopDraw() { drawing = false }

function clear() {
    ctx.clearRect(0, 0, canvas.value.width, canvas.value.height)
}

function isEmpty() {
    const d = ctx.getImageData(0, 0, canvas.value.width, canvas.value.height).data
    return !d.some(v => v !== 0)
}

const form = useForm({ signature: '', signed_by: '' })

function save() {
    if (isEmpty()) return alert('Veuillez signer avant de valider.')
    form.signature = canvas.value.toDataURL('image/png')
    form.signed_by = signedBy.value
    form.post(route('documents.signature.store', props.document.id), {
        onSuccess: () => router.visit(route('documents.show', props.document.id)),
    })
}
</script>

<template>
    <Head title="Signature client" />

    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Header -->
        <div class="bg-white border-b px-4 py-3 flex items-center justify-between">
            <div>
                <div class="font-bold text-gray-800">{{ company.name }}</div>
                <div class="text-sm text-gray-500">{{ document.type_label ?? 'Document' }} · {{ document.number }}</div>
            </div>
            <button @click="router.visit(route('documents.show', document.id))"
                class="text-gray-400 hover:text-gray-600 text-sm">✕ Annuler</button>
        </div>

        <div class="flex-1 flex flex-col max-w-2xl mx-auto w-full px-4 py-6 space-y-6">

            <!-- Infos document -->
            <div class="bg-white rounded-xl shadow p-4 text-sm space-y-2">
                <div class="flex justify-between"><span class="text-gray-500">Document</span><span class="font-medium">{{ document.number }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Montant</span><span class="font-bold text-lg text-blue-700">{{ new Intl.NumberFormat('fr-FR').format(document.total_ttc) }} {{ document.currency }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Client</span><span>{{ document.customer?.name ?? '—' }}</span></div>
            </div>

            <!-- Nom signataire -->
            <div>
                <label class="text-sm font-medium text-gray-700">Nom du signataire</label>
                <input v-model="signedBy" type="text" placeholder="Nom complet"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
            </div>

            <!-- Canvas signature -->
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="bg-gray-50 border-b px-4 py-2 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700">✍ Signature du client</span>
                    <button @click="clear" class="text-sm text-red-500 hover:text-red-700">Effacer</button>
                </div>
                <div class="relative" style="height:280px;">
                    <canvas ref="canvas"
                        class="block w-full touch-none cursor-crosshair"
                        @mousedown="startDraw" @mousemove="draw" @mouseup="stopDraw" @mouseleave="stopDraw"
                        @touchstart="startDraw" @touchmove="draw" @touchend="stopDraw">
                    </canvas>
                    <div class="absolute bottom-4 left-4 right-4 border-t border-dashed border-gray-300 pointer-events-none">
                        <span class="text-xs text-gray-400">Signez ici</span>
                    </div>
                </div>
            </div>

            <!-- Mention légale -->
            <p class="text-xs text-gray-400 text-center">
                En signant, le client reconnaît avoir pris connaissance du document et en accepte les termes.
                Signature horodatée le {{ new Date().toLocaleDateString('fr-FR') }}.
            </p>

            <!-- Bouton valider -->
            <button @click="save" :disabled="form.processing"
                class="w-full bg-green-600 text-white py-4 rounded-xl text-base font-bold hover:bg-green-700 disabled:opacity-50 shadow-lg">
                ✅ Valider la signature
            </button>
        </div>
    </div>
</template>

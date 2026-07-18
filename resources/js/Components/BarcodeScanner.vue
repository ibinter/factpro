<template>
  <div class="barcode-scanner fixed inset-0 z-50 bg-black flex flex-col">
    <!-- Contrôles caméra -->
    <div class="camera-controls flex items-center justify-between px-4 py-3 bg-gray-900">
      <button
        @click="switchCamera"
        class="flex items-center gap-2 text-white text-sm bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded-lg"
      >
        🔄 Changer caméra
      </button>
      <button
        v-if="hasFlash"
        @click="toggleFlash"
        class="flex items-center gap-2 text-white text-sm bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded-lg"
      >
        🔦 Flash
      </button>
      <button
        @click="stop"
        class="flex items-center gap-2 text-white text-sm bg-red-700 hover:bg-red-600 px-3 py-2 rounded-lg"
      >
        ✕ Fermer
      </button>
    </div>

    <!-- Viewfinder -->
    <div class="viewfinder relative flex-1 flex items-center justify-center overflow-hidden">
      <video
        ref="videoRef"
        autoplay
        playsinline
        muted
        class="w-full h-full object-cover"
      ></video>

      <!-- Cadre de ciblage -->
      <div class="targeting-frame absolute inset-0 flex items-center justify-center pointer-events-none">
        <div class="relative w-64 h-48">
          <!-- Coins -->
          <div class="corner tl absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-green-400 rounded-tl-sm"></div>
          <div class="corner tr absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-green-400 rounded-tr-sm"></div>
          <div class="corner bl absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-green-400 rounded-bl-sm"></div>
          <div class="corner br absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-green-400 rounded-br-sm"></div>

          <!-- Ligne de scan animée -->
          <div class="scan-line absolute left-0 right-0 h-0.5 bg-green-400 opacity-80" :style="scanLineStyle"></div>
        </div>
      </div>

      <!-- Hint -->
      <p class="hint absolute bottom-8 left-0 right-0 text-center text-white text-sm opacity-80 px-4">
        Pointez vers un code-barres ou QR code
      </p>

      <!-- Erreur accès caméra -->
      <div v-if="cameraError" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-80">
        <div class="text-center text-white px-6">
          <div class="text-4xl mb-4">📷</div>
          <p class="text-lg font-semibold mb-2">{{ cameraError }}</p>
          <button
            @click="start"
            class="mt-4 bg-green-600 hover:bg-green-500 text-white px-6 py-2 rounded-lg"
          >
            Réessayer
          </button>
        </div>
      </div>
    </div>

    <!-- Résultat détecté -->
    <div v-if="lastResult" class="result flex items-center gap-3 px-4 py-3 bg-green-900 text-green-200 text-sm">
      <span class="text-lg">✅</span>
      <span class="font-mono font-semibold">{{ lastResult }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'

const props = defineProps({
  autoStart: { type: Boolean, default: true },
})

const emit = defineEmits(['detected', 'error', 'close'])

const videoRef = ref(null)
const lastResult = ref(null)
const cameraError = ref(null)
const hasFlash = ref(false)
const scanLineY = ref(0)
const scanLineDirection = ref(1)

let codeReader = null
let currentDeviceIndex = ref(0)
let videoDevices = ref([])
let scanDebounceTimer = null
let scanLineInterval = null
let stream = null
let track = null
let isScanning = ref(false)

// Animation de la ligne de scan
const scanLineStyle = computed(() => ({
  top: `${scanLineY.value}%`,
  transition: 'top 0.05s linear',
}))

function animateScanLine() {
  scanLineInterval = setInterval(() => {
    scanLineY.value += scanLineDirection.value * 2
    if (scanLineY.value >= 95) scanLineDirection.value = -1
    if (scanLineY.value <= 5) scanLineDirection.value = 1
  }, 30)
}

async function initZXing() {
  // Chargement dynamique de ZXing pour éviter les problèmes SSR
  try {
    const { BrowserMultiFormatReader } = await import('@zxing/library')
    codeReader = new BrowserMultiFormatReader()
    return true
  } catch (e) {
    cameraError.value = 'Bibliothèque de scan non disponible'
    emit('error', e)
    return false
  }
}

async function enumerateDevices() {
  try {
    const devices = await navigator.mediaDevices.enumerateDevices()
    videoDevices.value = devices.filter(d => d.kind === 'videoinput')
    // Préférer la caméra arrière (environment)
    const backIndex = videoDevices.value.findIndex(d =>
      d.label.toLowerCase().includes('back') ||
      d.label.toLowerCase().includes('rear') ||
      d.label.toLowerCase().includes('environment')
    )
    if (backIndex !== -1) currentDeviceIndex.value = backIndex
  } catch (e) {
    // Continuer sans énumération
  }
}

async function start() {
  cameraError.value = null
  if (!codeReader) {
    const ok = await initZXing()
    if (!ok) return
  }

  await enumerateDevices()

  const deviceId = videoDevices.value[currentDeviceIndex.value]?.deviceId

  try {
    // Arrêter le stream existant
    if (stream) {
      stream.getTracks().forEach(t => t.stop())
    }

    // Ouvrir caméra avec préférence rear
    const constraints = {
      video: deviceId
        ? { deviceId: { exact: deviceId } }
        : { facingMode: { ideal: 'environment' } }
    }
    stream = await navigator.mediaDevices.getUserMedia(constraints)
    if (videoRef.value) {
      videoRef.value.srcObject = stream
    }

    // Détecter flash disponible
    track = stream.getVideoTracks()[0]
    const capabilities = track?.getCapabilities?.() ?? {}
    hasFlash.value = !!capabilities.torch

    // Lancer le scan
    isScanning.value = true
    scanWithStream()
    animateScanLine()
  } catch (e) {
    if (e.name === 'NotAllowedError' || e.name === 'PermissionDeniedError') {
      cameraError.value = 'Accès refusé — veuillez autoriser la caméra'
    } else if (e.name === 'NotFoundError') {
      cameraError.value = 'Aucune caméra détectée sur ce device'
    } else {
      cameraError.value = `Erreur caméra : ${e.message}`
    }
    emit('error', e)
  }
}

async function scanWithStream() {
  if (!codeReader || !videoRef.value || !isScanning.value) return

  try {
    const deviceId = videoDevices.value[currentDeviceIndex.value]?.deviceId ?? undefined
    await codeReader.decodeFromVideoDevice(
      deviceId,
      videoRef.value,
      (result, err) => {
        if (!result) return
        const text = result.getText()
        if (text === lastResult.value && scanDebounceTimer) return

        lastResult.value = text

        // Vibration courte de confirmation
        if (navigator.vibrate) {
          navigator.vibrate(100)
        }

        emit('detected', text)

        // Debounce 1.5s pour éviter détections multiples
        clearTimeout(scanDebounceTimer)
        scanDebounceTimer = setTimeout(() => {
          lastResult.value = null
          scanDebounceTimer = null
        }, 1500)
      }
    )
  } catch (e) {
    if (e.name !== 'NotFoundException') {
      // NotFoundException = pas de code trouvé dans le frame, c'est normal
      cameraError.value = `Erreur de lecture : ${e.message}`
      emit('error', e)
    }
  }
}

async function switchCamera() {
  if (videoDevices.value.length <= 1) return
  currentDeviceIndex.value = (currentDeviceIndex.value + 1) % videoDevices.value.length
  codeReader?.reset()
  await start()
}

async function toggleFlash() {
  if (!track || !hasFlash.value) return
  try {
    const capabilities = track.getCapabilities?.() ?? {}
    const settings = track.getSettings?.() ?? {}
    await track.applyConstraints({ advanced: [{ torch: !settings.torch }] })
  } catch (e) {
    // Flash non supporté sur ce device
  }
}

function stop() {
  isScanning.value = false
  codeReader?.reset()
  if (stream) {
    stream.getTracks().forEach(t => t.stop())
    stream = null
  }
  if (scanLineInterval) clearInterval(scanLineInterval)
  if (scanDebounceTimer) clearTimeout(scanDebounceTimer)
  emit('close')
}

onMounted(async () => {
  if (props.autoStart) {
    await start()
  }
})

onUnmounted(() => {
  stop()
})

// Exposer pour usage parent
defineExpose({ start, stop, switchCamera })
</script>

<style scoped>
.scan-line {
  box-shadow: 0 0 8px 2px rgba(74, 222, 128, 0.6);
}
</style>

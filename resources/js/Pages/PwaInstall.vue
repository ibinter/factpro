<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'

const deferredPrompt = ref(null)
const isInstalled = ref(false)
const isIos = ref(false)
const isAndroid = ref(false)
const installed = ref(false)

onMounted(() => {
    isIos.value = /iphone|ipad|ipod/i.test(navigator.userAgent)
    isAndroid.value = /android/i.test(navigator.userAgent)

    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
        isInstalled.value = true
    }

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault()
        deferredPrompt.value = e
    })

    window.addEventListener('appinstalled', () => {
        installed.value = true
        deferredPrompt.value = null
    })
})

async function installApp() {
    if (!deferredPrompt.value) return
    deferredPrompt.value.prompt()
    const { outcome } = await deferredPrompt.value.userChoice
    if (outcome === 'accepted') {
        installed.value = true
    }
    deferredPrompt.value = null
}
</script>

<template>
    <Head title="Installer FactPro — Application mobile" />

    <div class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 flex flex-col items-center justify-center px-4 py-12">

        <!-- Logo -->
        <div class="mb-8 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-2xl mb-4">
                <span class="text-4xl">🧾</span>
            </div>
            <h1 class="text-3xl font-bold text-white">IBIG FactPro</h1>
            <p class="text-blue-200 mt-1">La facturation simple pour les PME africaines</p>
        </div>

        <!-- Déjà installée -->
        <div v-if="isInstalled" class="bg-green-500/20 border border-green-400/30 rounded-2xl p-8 max-w-md w-full text-center">
            <div class="text-5xl mb-4">✅</div>
            <h2 class="text-xl font-bold text-white mb-2">Application déjà installée !</h2>
            <p class="text-green-200 mb-6">FactPro est installé sur votre appareil. Ouvrez-le depuis votre écran d'accueil.</p>
            <a href="/dashboard" class="inline-block bg-white text-blue-900 font-bold px-6 py-3 rounded-xl hover:bg-blue-50 transition">
                Aller au tableau de bord →
            </a>
        </div>

        <!-- Installé avec succès -->
        <div v-else-if="installed" class="bg-green-500/20 border border-green-400/30 rounded-2xl p-8 max-w-md w-full text-center">
            <div class="text-5xl mb-4">🎉</div>
            <h2 class="text-xl font-bold text-white mb-2">Installation réussie !</h2>
            <p class="text-green-200 mb-6">FactPro est maintenant disponible sur votre écran d'accueil.</p>
            <a href="/dashboard" class="inline-block bg-white text-blue-900 font-bold px-6 py-3 rounded-xl hover:bg-blue-50 transition">
                Ouvrir l'application →
            </a>
        </div>

        <!-- Prompt Android/Chrome -->
        <div v-else-if="deferredPrompt" class="bg-white/10 backdrop-blur border border-white/20 rounded-2xl p-8 max-w-md w-full text-center">
            <div class="text-5xl mb-4">📱</div>
            <h2 class="text-xl font-bold text-white mb-2">Installer sur votre appareil</h2>
            <p class="text-blue-100 mb-6">
                Installez FactPro comme une application native — accès rapide, mode hors-ligne, aucun Play Store requis.
            </p>
            <ul class="text-left text-blue-100 text-sm mb-6 space-y-2">
                <li>✓ Icône sur votre écran d'accueil</li>
                <li>✓ Fonctionne sans connexion internet</li>
                <li>✓ Notifications de rappel de paiements</li>
                <li>✓ Chargement ultra-rapide</li>
            </ul>
            <button
                @click="installApp"
                class="w-full bg-yellow-400 hover:bg-yellow-300 text-blue-900 font-bold px-6 py-4 rounded-xl text-lg transition shadow-lg"
            >
                📲 Installer FactPro
            </button>
            <a href="/dashboard" class="block mt-4 text-blue-300 hover:text-white text-sm transition">
                Continuer sans installer →
            </a>
        </div>

        <!-- iOS — instructions manuelles -->
        <div v-else-if="isIos" class="bg-white/10 backdrop-blur border border-white/20 rounded-2xl p-8 max-w-md w-full">
            <div class="text-center mb-6">
                <div class="text-5xl mb-3">🍎</div>
                <h2 class="text-xl font-bold text-white">Installer sur iPhone / iPad</h2>
                <p class="text-blue-200 text-sm mt-1">Suivez ces 3 étapes dans Safari</p>
            </div>
            <ol class="space-y-4">
                <li class="flex gap-4 items-start">
                    <span class="flex-shrink-0 w-8 h-8 bg-yellow-400 text-blue-900 font-bold rounded-full flex items-center justify-center text-sm">1</span>
                    <div>
                        <p class="text-white font-medium">Appuyez sur le bouton Partager</p>
                        <p class="text-blue-200 text-sm">Icône <strong>⬆</strong> en bas de l'écran Safari</p>
                    </div>
                </li>
                <li class="flex gap-4 items-start">
                    <span class="flex-shrink-0 w-8 h-8 bg-yellow-400 text-blue-900 font-bold rounded-full flex items-center justify-center text-sm">2</span>
                    <div>
                        <p class="text-white font-medium">Faites défiler et appuyez sur</p>
                        <p class="text-blue-200 text-sm"><strong>"Sur l'écran d'accueil"</strong></p>
                    </div>
                </li>
                <li class="flex gap-4 items-start">
                    <span class="flex-shrink-0 w-8 h-8 bg-yellow-400 text-blue-900 font-bold rounded-full flex items-center justify-center text-sm">3</span>
                    <div>
                        <p class="text-white font-medium">Appuyez sur "Ajouter"</p>
                        <p class="text-blue-200 text-sm">FactPro apparaît sur votre écran d'accueil</p>
                    </div>
                </li>
            </ol>
            <a href="/dashboard" class="block mt-6 text-center text-blue-300 hover:text-white text-sm transition">
                Continuer dans le navigateur →
            </a>
        </div>

        <!-- Navigateur non compatible ou desktop -->
        <div v-else class="bg-white/10 backdrop-blur border border-white/20 rounded-2xl p-8 max-w-md w-full text-center">
            <div class="text-5xl mb-4">💻</div>
            <h2 class="text-xl font-bold text-white mb-2">Installation PWA</h2>
            <p class="text-blue-100 mb-6">
                Pour installer FactPro sur <strong>Android</strong>, ouvrez cette page dans <strong>Chrome</strong> et appuyez sur "Installer" dans la barre d'adresse.
            </p>
            <p class="text-blue-100 mb-6">
                Sur <strong>iPhone</strong>, ouvrez dans <strong>Safari</strong> → Partager → Sur l'écran d'accueil.
            </p>
            <div class="border-t border-white/20 pt-6">
                <p class="text-blue-200 text-sm mb-4">Sur ordinateur, cliquez sur l'icône d'installation dans la barre d'adresse Chrome :</p>
                <div class="flex justify-center gap-2 text-2xl mb-4">⬇️ 📥</div>
            </div>
            <a href="/dashboard" class="inline-block bg-white text-blue-900 font-bold px-6 py-3 rounded-xl hover:bg-blue-50 transition">
                Accéder à FactPro →
            </a>
        </div>

        <!-- Footer -->
        <p class="mt-8 text-blue-300 text-sm">
            © 2026 IBIG SARL — <a href="https://factpro.ibigsoft.com" class="hover:text-white transition">factpro.ibigsoft.com</a>
        </p>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const show = ref(false);
const showCustom = ref(false);
const prefs = ref({ analytics: false, marketing: false });

onMounted(() => {
    if (!localStorage.getItem('factpro_cookies')) {
        setTimeout(() => { show.value = true; }, 1200);
    }
});

function acceptAll() {
    localStorage.setItem('factpro_cookies', JSON.stringify({ analytics: true, marketing: true, ts: Date.now() }));
    show.value = false;
}
function rejectAll() {
    localStorage.setItem('factpro_cookies', JSON.stringify({ analytics: false, marketing: false, ts: Date.now() }));
    show.value = false;
}
function saveCustom() {
    localStorage.setItem('factpro_cookies', JSON.stringify({ ...prefs.value, ts: Date.now() }));
    show.value = false;
    showCustom.value = false;
}
</script>

<template>
    <Transition name="cookie-slide">
        <div v-if="show"
             class="fixed bottom-0 inset-x-0 z-50 px-4 pb-4 sm:px-6"
             role="dialog" aria-label="Gestion des cookies">
            <div class="mx-auto max-w-4xl rounded-2xl shadow-2xl p-5 sm:p-6"
                 style="background:#001d3d;border:1px solid rgba(255,255,255,.1)">

                <div v-if="!showCustom">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 text-3xl">🍪</div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-white mb-1">Nous utilisons des cookies</p>
                            <p class="text-xs text-white/60 leading-relaxed">
                                IBIG FactPro utilise des cookies nécessaires au fonctionnement du service et, avec votre accord, des cookies analytiques pour améliorer l'expérience. Aucun cookie non essentiel n'est déposé sans votre consentement.
                                <a href="/legal/cookies" class="underline text-blue-300 hover:text-blue-200 ml-1">En savoir plus</a>
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-3 sm:justify-end">
                        <button @click="showCustom = true"
                                class="rounded-lg border px-4 py-2 text-xs font-semibold text-white/70 transition hover:text-white"
                                style="border-color:rgba(255,255,255,.2)">
                            Personnaliser
                        </button>
                        <button @click="rejectAll"
                                class="rounded-lg border px-4 py-2 text-xs font-semibold text-white/70 transition hover:text-white"
                                style="border-color:rgba(255,255,255,.2)">
                            Refuser les non essentiels
                        </button>
                        <button @click="acceptAll"
                                class="rounded-lg px-5 py-2 text-xs font-bold transition hover:scale-105"
                                style="background:#F0C040;color:#001d3d">
                            Tout accepter
                        </button>
                    </div>
                </div>

                <div v-else>
                    <p class="text-sm font-bold text-white mb-4">Personnaliser mes préférences</p>
                    <div class="space-y-3 mb-5">
                        <label class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-sm font-semibold text-white">Cookies nécessaires</div>
                                <div class="text-xs text-white/50">Authentification, session, sécurité. Toujours actifs.</div>
                            </div>
                            <div class="rounded-full px-3 py-1 text-xs font-bold" style="background:rgba(255,255,255,.1);color:rgba(255,255,255,.5)">Obligatoire</div>
                        </label>
                        <label class="flex items-center justify-between gap-4 cursor-pointer">
                            <div>
                                <div class="text-sm font-semibold text-white">Cookies analytiques</div>
                                <div class="text-xs text-white/50">Amélioration de l'expérience utilisateur.</div>
                            </div>
                            <button @click="prefs.analytics = !prefs.analytics"
                                    class="w-10 h-6 rounded-full transition-colors duration-200 relative flex-shrink-0"
                                    :style="prefs.analytics ? 'background:#0062CC' : 'background:rgba(255,255,255,.2)'">
                                <span class="absolute top-1 w-4 h-4 rounded-full bg-white shadow transition-all duration-200"
                                      :style="prefs.analytics ? 'left:22px' : 'left:2px'"></span>
                            </button>
                        </label>
                        <label class="flex items-center justify-between gap-4 cursor-pointer">
                            <div>
                                <div class="text-sm font-semibold text-white">Cookies marketing</div>
                                <div class="text-xs text-white/50">Publicités personnalisées et campagnes.</div>
                            </div>
                            <button @click="prefs.marketing = !prefs.marketing"
                                    class="w-10 h-6 rounded-full transition-colors duration-200 relative flex-shrink-0"
                                    :style="prefs.marketing ? 'background:#0062CC' : 'background:rgba(255,255,255,.2)'">
                                <span class="absolute top-1 w-4 h-4 rounded-full bg-white shadow transition-all duration-200"
                                      :style="prefs.marketing ? 'left:22px' : 'left:2px'"></span>
                            </button>
                        </label>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button @click="showCustom = false"
                                class="rounded-lg border px-4 py-2 text-xs font-semibold text-white/70"
                                style="border-color:rgba(255,255,255,.2)">
                            Retour
                        </button>
                        <button @click="saveCustom"
                                class="rounded-lg px-5 py-2 text-xs font-bold"
                                style="background:#F0C040;color:#001d3d">
                            Enregistrer mes choix
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.cookie-slide-enter-active,.cookie-slide-leave-active{transition:all .35s cubic-bezier(.4,0,.2,1)}
.cookie-slide-enter-from,.cookie-slide-leave-to{transform:translateY(100%);opacity:0}
</style>

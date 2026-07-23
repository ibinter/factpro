<script setup>
import { ref, computed, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({ mode: { type: String, default: 'public' } });

const open = ref(false);
const minimized = ref(false);
const input = ref('');
const loading = ref(false);
const inputError = ref('');
const unreadCount = ref(0);
const messagesEl = ref(null);

const welcomeMessage = computed(() =>
    props.mode === 'internal'
        ? "Bonjour ! Je suis SARA, votre assistante IA. Je peux vous guider dans FactPro, expliquer les fonctionnalités et vous aider à créer des documents."
        : "Bonjour ! Je suis SARA, l'assistante intelligente de IBIG FactPro. Posez-moi toutes vos questions sur le logiciel, les tarifs et l'essai gratuit."
);

const suggestions = computed(() =>
    props.mode === 'internal'
        ? ["Comment créer une facture ?", "Expliquer les KPIs du tableau de bord", "Comment ajouter un client ?", "Comment générer un devis ?"]
        : ["C'est quoi l'essai gratuit ?", "Quels sont les tarifs ?", "Est-ce compatible mobile ?", "Comment créer une facture ?"]
);

const messages = ref([
    { role: 'assistant', content: welcomeMessage.value }
]);

const BLOCKED_KEYWORDS = ["mot de passe", "password", "carte bancaire", "numéro de carte", "code secret", "pin", "bdf", "hack", "exploit", "injection", "xss", "sql"];
const REFUSAL_MSG = "Je suis désolée, je ne peux pas vous aider avec ce type de demande. Pour la sécurité, contactez notre support à support@ibigsoft.com";

function isBlocked(text) {
    const lower = text.toLowerCase();
    return BLOCKED_KEYWORDS.some(k => lower.includes(k));
}

function openChat() {
    open.value = true;
    minimized.value = false;
    unreadCount.value = 0;
}

function toggleChat() {
    if (open.value && !minimized.value) {
        open.value = false;
    } else {
        openChat();
    }
}

function minimize() {
    minimized.value = true;
    open.value = false;
}

function resetConversation() {
    messages.value = [{ role: 'assistant', content: welcomeMessage.value }];
    input.value = '';
    inputError.value = '';
}

async function send(text) {
    const msg = (text || input.value).trim();
    if (!msg || loading.value) return;

    inputError.value = '';

    if (msg.length > 500) {
        inputError.value = "Message trop long (max 500 caractères)";
        return;
    }

    if (isBlocked(msg)) {
        input.value = '';
        messages.value.push({ role: 'user', content: msg });
        messages.value.push({ role: 'assistant', content: REFUSAL_MSG });
        if (!open.value) unreadCount.value++;
        await scrollBottom();
        return;
    }

    input.value = '';
    messages.value.push({ role: 'user', content: msg });
    loading.value = true;
    await scrollBottom();

    try {
        const history = messages.value.slice(-10).map(m => ({ role: m.role, content: m.content }));
        const { data } = await axios.post('/api/sara/chat', { messages: history, mode: props.mode });
        messages.value.push({ role: 'assistant', content: data.reply });
        if (!open.value) unreadCount.value++;
    } catch (e) {
        const err = e.response?.data?.error ?? e.response?.data?.message ?? null;
        messages.value.push({ role: 'assistant', content: err ?? 'Désolée, problème de connexion. Réessayez.' });
        if (!open.value) unreadCount.value++;
    } finally {
        loading.value = false;
        await scrollBottom();
    }
}

async function scrollBottom() {
    await nextTick();
    if (messagesEl.value) messagesEl.value.scrollTop = messagesEl.value.scrollHeight;
}

function onKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
}
</script>

<template>
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3">
        <!-- Chat window -->
        <Transition name="sara-slide">
            <div v-if="open" class="mb-2 flex h-[520px] w-[360px] flex-col overflow-hidden rounded-2xl shadow-2xl" style="background:#fff; border:1px solid #e5e7eb;">
                <!-- Header -->
                <div class="flex items-center gap-3 px-4 py-3" style="background:linear-gradient(135deg,#0062CC,#002D5B)">
                    <div class="relative">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-xl font-bold text-white">S</div>
                        <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white bg-green-400"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-white">SARA</p>
                        <p class="text-xs text-white/70 truncate">{{ mode === 'internal' ? 'Assistante IA · FactPro' : 'Assistante IA FactPro · En ligne' }}</p>
                    </div>
                    <button @click="resetConversation" class="text-[10px] text-white/60 hover:text-white border border-white/30 rounded px-1.5 py-0.5 shrink-0" title="Nouvelle conversation">
                        Nouvelle conversation
                    </button>
                    <button @click="minimize" class="text-white/60 hover:text-white ml-1 text-lg font-bold leading-none" title="Réduire">–</button>
                    <button @click="open = false" class="text-white/60 hover:text-white ml-1" title="Fermer">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Messages -->
                <div ref="messagesEl" class="flex-1 space-y-3 overflow-y-auto p-4">
                    <div v-for="(m, i) in messages" :key="i" :class="m.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div
                            class="max-w-[80%] rounded-2xl px-4 py-2.5 text-sm leading-relaxed"
                            :class="m.role === 'user'
                                ? 'rounded-br-sm text-white'
                                : 'rounded-bl-sm bg-gray-100 text-gray-800'"
                            :style="m.role === 'user' ? 'background:#0062CC' : ''"
                        >{{ m.content }}</div>
                    </div>

                    <!-- Typing indicator -->
                    <div v-if="loading" class="flex justify-start">
                        <div class="flex items-center gap-1 rounded-2xl rounded-bl-sm bg-gray-100 px-4 py-3">
                            <span v-for="n in 3" :key="n" class="h-2 w-2 rounded-full bg-gray-400" :style="`animation:sara-dot 1.2s ${(n-1)*0.2}s infinite`"></span>
                        </div>
                    </div>

                    <!-- Suggestions (first message only) -->
                    <div v-if="messages.length === 1 && !loading" class="flex flex-wrap gap-2 pt-1">
                        <button
                            v-for="s in suggestions" :key="s"
                            @click="send(s)"
                            class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 transition hover:bg-blue-100"
                        >{{ s }}</button>
                    </div>
                </div>

                <!-- Input -->
                <div class="border-t border-gray-100 p-3">
                    <div class="flex items-end gap-2 rounded-xl border bg-gray-50 px-3 py-2 focus-within:border-blue-400" :class="inputError ? 'border-red-300' : 'border-gray-200'">
                        <textarea
                            v-model="input"
                            @keydown="onKey"
                            @input="inputError = ''"
                            rows="1"
                            placeholder="Posez votre question…"
                            class="flex-1 resize-none bg-transparent text-sm text-gray-800 outline-none placeholder:text-gray-400"
                            style="max-height:80px"
                        ></textarea>
                        <button
                            @click="send()"
                            :disabled="!input.trim() || loading"
                            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg transition disabled:opacity-40"
                            style="background:#0062CC"
                        >
                            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m-7 7l7-7 7 7"/></svg>
                        </button>
                    </div>
                    <div class="mt-1 flex items-center justify-between px-0.5">
                        <p v-if="inputError" class="text-[10px] text-red-500">{{ inputError }}</p>
                        <p v-else class="text-[10px] text-gray-400">{{ input.length }}/500</p>
                        <p class="text-[10px] text-gray-400">SARA · IA de IBIG FactPro</p>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Toggle button -->
        <button
            @click="toggleChat"
            class="relative flex h-14 w-14 items-center justify-center rounded-full shadow-xl transition hover:scale-105 active:scale-95"
            style="background:linear-gradient(135deg,#0062CC,#002D5B)"
            aria-label="Ouvrir SARA"
        >
            <Transition name="sara-icon" mode="out-in">
                <svg v-if="!open" key="chat" class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <svg v-else key="close" class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </Transition>
            <!-- Unread badge -->
            <span
                v-if="unreadCount > 0 && !open"
                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white"
            >{{ unreadCount }}</span>
        </button>
    </div>
</template>

<style scoped>
.sara-slide-enter-active,.sara-slide-leave-active{transition:all .3s cubic-bezier(.34,1.56,.64,1)}
.sara-slide-enter-from,.sara-slide-leave-to{opacity:0;transform:translateY(20px) scale(.95)}
.sara-icon-enter-active,.sara-icon-leave-active{transition:all .2s}
.sara-icon-enter-from,.sara-icon-leave-to{opacity:0;transform:rotate(90deg) scale(.5)}
@keyframes sara-dot{0%,80%,100%{transform:scale(0);opacity:.4}40%{transform:scale(1);opacity:1}}
</style>

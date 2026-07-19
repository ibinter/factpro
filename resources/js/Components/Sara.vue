<script setup>
import { ref, nextTick } from 'vue';
import axios from 'axios';

const open = ref(false);
const input = ref('');
const loading = ref(false);
const messagesEl = ref(null);
const messages = ref([
    { role: 'assistant', content: 'Bonjour ! Je suis SARA, votre assistante IA FactPro. Comment puis-je vous aider aujourd\'hui ?' }
]);

const suggestions = [
    'C\'est quoi l\'essai gratuit ?',
    'Quels sont les tarifs ?',
    'Est-ce compatible mobile ?',
    'Comment fonctionne le QR ?',
];

async function send(text) {
    const msg = (text || input.value).trim();
    if (!msg || loading.value) return;
    input.value = '';
    messages.value.push({ role: 'user', content: msg });
    loading.value = true;
    await scrollBottom();
    try {
        const { data } = await axios.post('/api/sara/chat', {
            messages: messages.value.filter(m => m.role !== 'system').slice(-10),
        });
        messages.value.push({ role: 'assistant', content: data.reply });
    } catch {
        messages.value.push({ role: 'assistant', content: 'Désolée, je rencontre un problème technique. Réessayez dans un instant.' });
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
    <!-- Bubble trigger -->
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
                    <div>
                        <p class="text-sm font-bold text-white">SARA</p>
                        <p class="text-xs text-white/70">Assistante IA FactPro · En ligne</p>
                    </div>
                    <button @click="open = false" class="ml-auto text-white/60 hover:text-white">
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
                            class="rounded-full border border-brand-200 bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700 transition hover:bg-brand-100"
                        >{{ s }}</button>
                    </div>
                </div>

                <!-- Input -->
                <div class="border-t border-gray-100 p-3">
                    <div class="flex items-end gap-2 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 focus-within:border-brand-400">
                        <textarea
                            v-model="input"
                            @keydown="onKey"
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
                    <p class="mt-1 text-center text-[10px] text-gray-400">SARA · IA de IBIG FactPro</p>
                </div>
            </div>
        </Transition>

        <!-- Toggle button -->
        <button
            @click="open = !open"
            class="flex h-14 w-14 items-center justify-center rounded-full shadow-xl transition hover:scale-105 active:scale-95"
            style="background:linear-gradient(135deg,#0062CC,#002D5B)"
            aria-label="Ouvrir SARA"
        >
            <Transition name="sara-icon" mode="out-in">
                <svg v-if="!open" key="chat" class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <svg v-else key="close" class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </Transition>
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

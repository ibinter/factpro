<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    document: { type: Object, required: true },
    comments: { type: Array, default: () => [] },
});

const open = ref(props.comments.length > 0);

const form = useForm({ body: '' });

const submit = () => {
    form.post(route('documents.comments.store', props.document.id), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const destroy = (commentId) => {
    router.delete(route('documents.comments.destroy', [props.document.id, commentId]), {
        preserveScroll: true,
    });
};

const initials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
};

const avatarColor = (name) => {
    const colors = [
        'bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-amber-500',
        'bg-rose-500', 'bg-teal-500', 'bg-indigo-500', 'bg-orange-500',
    ];
    if (!name) return colors[0];
    let hash = 0;
    for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
    return colors[Math.abs(hash) % colors.length];
};
</script>

<template>
    <div class="rounded-lg bg-white shadow">
        <!-- En-tête pliable -->
        <button
            type="button"
            @click="open = !open"
            class="flex w-full items-center justify-between px-6 py-4 text-left"
        >
            <h3 class="font-semibold text-gray-800">
                💬 Commentaires internes
                <span v-if="comments.length" class="ml-2 rounded-full bg-brand-100 px-2 py-0.5 text-xs font-semibold text-brand-700">
                    {{ comments.length }}
                </span>
            </h3>
            <svg
                :class="open ? 'rotate-180' : ''"
                class="h-5 w-5 text-gray-400 transition-transform"
                fill="none" viewBox="0 0 24 24" stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div v-if="open" class="border-t px-6 pb-6 pt-4">
            <!-- Liste des commentaires -->
            <div v-if="comments.length" class="mb-5 space-y-4">
                <div
                    v-for="comment in comments"
                    :key="comment.id"
                    class="flex items-start gap-3"
                >
                    <!-- Avatar initiales -->
                    <div
                        :class="avatarColor(comment.user?.name)"
                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold text-white"
                    >
                        {{ initials(comment.user?.name) }}
                    </div>

                    <!-- Contenu -->
                    <div class="flex-1 rounded-lg bg-gray-50 px-4 py-3 text-sm">
                        <div class="mb-1 flex items-center justify-between gap-2">
                            <span class="font-semibold text-gray-800">{{ comment.user?.name ?? 'Inconnu' }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400">
                                    {{ new Date(comment.created_at).toLocaleString('fr-FR') }}
                                </span>
                                <button
                                    type="button"
                                    @click="destroy(comment.id)"
                                    class="text-xs text-red-400 hover:text-red-600"
                                    title="Supprimer ce commentaire"
                                >
                                    ✕
                                </button>
                            </div>
                        </div>
                        <p class="whitespace-pre-line text-gray-700">{{ comment.body }}</p>
                    </div>
                </div>
            </div>

            <p v-else class="mb-4 text-sm text-gray-400 italic">Aucun commentaire pour l'instant.</p>

            <!-- Formulaire ajout -->
            <form @submit.prevent="submit" class="space-y-3">
                <textarea
                    v-model="form.body"
                    rows="3"
                    placeholder="Ajouter un commentaire interne…"
                    class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                ></textarea>
                <InputError :message="form.errors.body" />
                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing || !form.body.trim()"
                        class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Envoi…' : '+ Ajouter' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    ticket: Object,
});

const priorityColors = {
    urgent: 'bg-red-100 text-red-700',
    high:   'bg-orange-100 text-orange-700',
    normal: 'bg-blue-100 text-blue-700',
    low:    'bg-gray-100 text-gray-500',
};
const statusColors = {
    open:         'bg-green-100 text-green-700',
    in_progress:  'bg-blue-100 text-blue-700',
    waiting_user: 'bg-amber-100 text-amber-700',
    resolved:     'bg-gray-100 text-gray-500',
    closed:       'bg-gray-200 text-gray-400',
};
const statusLabels = {
    open: 'Ouvert', in_progress: 'En cours', waiting_user: 'En attente',
    resolved: 'Résolu', closed: 'Fermé',
};
const priorityLabels = { low: 'Basse', normal: 'Normale', high: 'Haute', urgent: 'Urgent' };
const categoryLabels = {
    general: 'Général', billing: 'Facturation', technical: 'Technique',
    feature: 'Fonctionnalité', other: 'Autre',
};

const replyForm = useForm({ message: '' });
const submit = () => {
    replyForm.post(route('support.reply', props.ticket.id), {
        onSuccess: () => replyForm.reset(),
    });
};
</script>

<template>
    <Head :title="`Ticket #${ticket.ticket_number}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <a :href="route('support.index')" class="text-sm text-gray-400 hover:text-gray-600">← Mes tickets</a>
                <span class="text-gray-300">/</span>
                <h2 class="text-xl font-semibold text-gray-800">Ticket #{{ ticket.ticket_number }}</h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Infos ticket -->
                <div class="rounded-2xl bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-800">{{ ticket.subject }}</h3>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600">
                            {{ categoryLabels[ticket.category] ?? ticket.category }}
                        </span>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="priorityColors[ticket.priority]">
                            {{ priorityLabels[ticket.priority] ?? ticket.priority }}
                        </span>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusColors[ticket.status]">
                            {{ statusLabels[ticket.status] ?? ticket.status }}
                        </span>
                        <span class="text-xs text-gray-400 self-center">Créé le {{ ticket.created_at }}</span>
                    </div>
                </div>

                <!-- Fil de conversation -->
                <div class="space-y-4">

                    <!-- Premier message -->
                    <div class="rounded-xl border-l-4 border-brand-600 bg-gray-50 p-5">
                        <div class="mb-2 text-xs font-semibold text-gray-400 uppercase tracking-wide">Message initial</div>
                        <p class="whitespace-pre-wrap text-sm text-gray-700">{{ ticket.first_message }}</p>
                    </div>

                    <!-- Réponses -->
                    <div v-for="reply in ticket.replies" :key="reply.id" class="flex" :class="reply.is_staff ? 'justify-start' : 'justify-end'">
                        <div
                            class="max-w-[85%] rounded-2xl p-4 shadow-sm"
                            :class="reply.is_staff ? 'bg-brand-600 text-white' : 'bg-white border border-gray-200 text-gray-800'"
                        >
                            <div class="mb-1 flex items-center gap-2">
                                <span class="text-xs font-semibold" :class="reply.is_staff ? 'text-brand-100' : 'text-gray-500'">
                                    {{ reply.is_staff ? '🎧 Support IBIG' : reply.user }}
                                </span>
                                <span class="text-xs opacity-60">{{ reply.created_at }}</span>
                            </div>
                            <p class="whitespace-pre-wrap text-sm">{{ reply.message }}</p>
                        </div>
                    </div>

                </div>

                <!-- Formulaire réponse -->
                <div v-if="ticket.status !== 'closed'" class="rounded-2xl bg-white p-6 shadow">
                    <h4 class="mb-3 text-sm font-semibold text-gray-700">Ajouter une réponse</h4>
                    <form @submit.prevent="submit" class="space-y-3">
                        <textarea
                            v-model="replyForm.message"
                            rows="4"
                            placeholder="Votre message..."
                            maxlength="5000"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-400 focus:outline-none focus:ring-1 focus:ring-brand-400"
                        ></textarea>
                        <p v-if="replyForm.errors.message" class="text-xs text-red-600">{{ replyForm.errors.message }}</p>
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="replyForm.processing"
                                class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-brand-700 transition disabled:opacity-50"
                            >
                                {{ replyForm.processing ? 'Envoi...' : 'Répondre' }}
                            </button>
                        </div>
                    </form>
                </div>

                <div v-else class="rounded-xl border border-gray-200 bg-gray-50 px-5 py-4 text-center text-sm text-gray-500">
                    Ce ticket est fermé. <a :href="route('support.create')" class="text-brand-600 underline">Ouvrir un nouveau ticket</a>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

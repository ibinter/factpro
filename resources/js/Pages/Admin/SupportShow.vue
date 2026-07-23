<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
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

const replyForm = useForm({
    message: '',
    status: props.ticket.status,
});

const submit = () => {
    replyForm.post(route('admin.support.reply', props.ticket.id), {
        onSuccess: () => replyForm.reset('message'),
    });
};
</script>

<template>
    <Head :title="`[Admin] Ticket #${ticket.ticket_number}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <a :href="route('admin.support.index')" class="text-sm text-gray-400 hover:text-gray-600">← Support</a>
                    <span class="text-gray-300">/</span>
                    <h2 class="text-xl font-semibold text-gray-800">Ticket #{{ ticket.ticket_number }}</h2>
                </div>
                <AdminTabs />
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Infos ticket -->
                <div class="rounded-2xl bg-white p-6 shadow">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">{{ ticket.subject }}</h3>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600">
                                    {{ categoryLabels[ticket.category] ?? ticket.category }}
                                </span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="priorityColors[ticket.priority]">
                                    {{ priorityLabels[ticket.priority] ?? ticket.priority }}
                                </span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusColors[ticket.status]">
                                    {{ statusLabels[ticket.status] ?? ticket.status }}
                                </span>
                            </div>
                        </div>
                        <div class="text-right text-sm">
                            <div class="font-semibold text-gray-700">{{ ticket.user }}</div>
                            <div class="text-gray-400">{{ ticket.email }}</div>
                            <div class="mt-1 text-xs text-gray-400">{{ ticket.created_at }}</div>
                        </div>
                    </div>
                </div>

                <!-- Fil de conversation -->
                <div class="space-y-4">

                    <!-- Premier message -->
                    <div class="rounded-xl border-l-4 border-brand-600 bg-gray-50 p-5">
                        <div class="mb-2 flex items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-400">Message initial</span>
                            <span class="text-xs text-gray-400">— {{ ticket.user }}</span>
                        </div>
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
                                    {{ reply.is_staff ? '🎧 Staff — ' + reply.user : reply.user }}
                                </span>
                                <span class="text-xs opacity-60">{{ reply.created_at }}</span>
                            </div>
                            <p class="whitespace-pre-wrap text-sm">{{ reply.message }}</p>
                        </div>
                    </div>

                </div>

                <!-- Formulaire réponse staff -->
                <div class="rounded-2xl bg-white p-6 shadow">
                    <h4 class="mb-4 text-sm font-semibold text-gray-700">Répondre au client</h4>
                    <form @submit.prevent="submit" class="space-y-4">
                        <textarea
                            v-model="replyForm.message"
                            rows="5"
                            placeholder="Votre réponse..."
                            maxlength="5000"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-400 focus:outline-none focus:ring-1 focus:ring-brand-400"
                        ></textarea>
                        <p v-if="replyForm.errors.message" class="text-xs text-red-600">{{ replyForm.errors.message }}</p>

                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <label class="mb-1 block text-xs font-medium text-gray-600">Changer le statut</label>
                                <select v-model="replyForm.status" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-brand-400 focus:outline-none">
                                    <option value="open">Ouvert</option>
                                    <option value="in_progress">En cours</option>
                                    <option value="waiting_user">En attente du client</option>
                                    <option value="resolved">Résolu</option>
                                    <option value="closed">Fermé</option>
                                </select>
                                <p v-if="replyForm.errors.status" class="mt-1 text-xs text-red-600">{{ replyForm.errors.status }}</p>
                            </div>
                            <button
                                type="submit"
                                :disabled="replyForm.processing"
                                class="self-end rounded-lg bg-brand-600 px-6 py-2 text-sm font-semibold text-white shadow hover:bg-brand-700 transition disabled:opacity-50"
                            >
                                {{ replyForm.processing ? 'Envoi...' : 'Envoyer au client' }}
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>

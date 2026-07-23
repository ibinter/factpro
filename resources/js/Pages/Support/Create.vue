<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const form = useForm({
    subject: '',
    category: 'general',
    priority: 'normal',
    first_message: '',
});

const msgLength = computed(() => form.first_message.length);

const submit = () => {
    form.post(route('support.store'));
};
</script>

<template>
    <Head title="Nouveau ticket de support" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800">Nouveau ticket de support</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl bg-white p-8 shadow">
                    <p class="mb-6 text-sm text-gray-500">Décrivez votre problème et notre équipe vous répondra dans les meilleurs délais.</p>

                    <form @submit.prevent="submit" class="space-y-5">

                        <!-- Sujet -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Sujet *</label>
                            <input
                                v-model="form.subject"
                                type="text"
                                maxlength="150"
                                placeholder="Résumez votre demande en une phrase"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-400 focus:outline-none focus:ring-1 focus:ring-brand-400"
                            />
                            <p v-if="form.errors.subject" class="mt-1 text-xs text-red-600">{{ form.errors.subject }}</p>
                        </div>

                        <!-- Catégorie + Priorité côte à côte -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Catégorie *</label>
                                <select v-model="form.category" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-400 focus:outline-none focus:ring-1 focus:ring-brand-400">
                                    <option value="general">Général</option>
                                    <option value="billing">Facturation</option>
                                    <option value="technical">Technique</option>
                                    <option value="feature">Fonctionnalité</option>
                                    <option value="other">Autre</option>
                                </select>
                                <p v-if="form.errors.category" class="mt-1 text-xs text-red-600">{{ form.errors.category }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Priorité *</label>
                                <select v-model="form.priority" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-400 focus:outline-none focus:ring-1 focus:ring-brand-400">
                                    <option value="low">Basse</option>
                                    <option value="normal">Normale</option>
                                    <option value="high">Haute</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                                <p v-if="form.errors.priority" class="mt-1 text-xs text-red-600">{{ form.errors.priority }}</p>
                            </div>
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Description *</label>
                            <textarea
                                v-model="form.first_message"
                                rows="7"
                                maxlength="5000"
                                placeholder="Décrivez votre problème en détail (minimum 20 caractères). Plus vous êtes précis, plus nous pourrons vous aider rapidement."
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-brand-400 focus:outline-none focus:ring-1 focus:ring-brand-400"
                            ></textarea>
                            <div class="mt-1 flex justify-between">
                                <p v-if="form.errors.first_message" class="text-xs text-red-600">{{ form.errors.first_message }}</p>
                                <span v-else class="text-xs text-gray-400">{{ msgLength }} / 5000 caractères (min. 20)</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <a :href="route('support.index')" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-gold-500 px-6 py-2.5 text-sm font-semibold text-white shadow hover:bg-gold-600 transition disabled:opacity-50"
                            >
                                {{ form.processing ? 'Envoi...' : 'Envoyer le ticket' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

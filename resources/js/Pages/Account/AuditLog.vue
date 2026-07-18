<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    logs: Object,
});
</script>

<template>
    <Head title="Journal d'audit" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Journal d'audit de la société
                </h2>
                <Link :href="route('gdpr.index')" class="text-sm text-brand-600 hover:underline">
                    &larr; Mes données & RGPD
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Utilisateur</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Document</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-600">Événement</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!logs.data.length">
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400">Aucune entrée dans le journal.</td>
                            </tr>
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-4 py-2 text-gray-500">{{ log.created_at }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ log.user?.name ?? '—' }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ log.document?.number ?? '—' }}</td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex rounded-full bg-brand-50 px-2 py-0.5 text-xs font-medium text-brand-700">
                                        {{ log.event }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="logs.last_page > 1" class="mt-4 flex justify-center gap-2 text-sm">
                    <Link
                        v-for="link in logs.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        :class="[
                            'rounded px-3 py-1 border',
                            link.active ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50',
                            !link.url ? 'pointer-events-none opacity-40' : '',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

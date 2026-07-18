<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    notifications: Object,
    filter: { type: String, default: 'all' },
    unreadCount: { type: Number, default: 0 },
});

const currentFilter = ref(props.filter);

const setFilter = (f) => {
    currentFilter.value = f;
    router.get(route('notifications.index'), { filter: f }, { preserveState: true });
};

const markRead = (id) => {
    router.post(route('notifications.read', id), {}, { preserveScroll: true });
};

const markAllRead = () => {
    router.post(route('notifications.read-all'), {}, { preserveScroll: true });
};

const deleteNotif = (id) => {
    router.delete(route('notifications.destroy', id), { preserveScroll: true });
};

const clearAllRead = () => {
    router.delete(route('notifications.clear'), { preserveScroll: true });
};

const timeAgo = (dateStr) => {
    const date = new Date(dateStr);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);
    if (diff < 60) return 'à l\'instant';
    if (diff < 3600) return `il y a ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `il y a ${Math.floor(diff / 3600)} h`;
    return `il y a ${Math.floor(diff / 86400)} j`;
};

const filters = [
    { key: 'all', label: 'Toutes' },
    { key: 'unread', label: 'Non lues' },
    { key: 'read', label: 'Lues' },
];
</script>

<template>
    <Head title="Notifications" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Centre de notifications</h2>
                <div class="flex gap-2">
                    <button
                        v-if="unreadCount > 0"
                        type="button"
                        class="rounded-md bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700"
                        @click="markAllRead"
                    >
                        Tout marquer lu
                    </button>
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        @click="clearAllRead"
                    >
                        Effacer les lues
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <!-- Filtres -->
                <div class="mb-4 flex gap-2">
                    <button
                        v-for="f in filters"
                        :key="f.key"
                        type="button"
                        class="rounded-full px-4 py-1.5 text-sm font-medium transition"
                        :class="currentFilter === f.key
                            ? 'bg-brand-600 text-white'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        @click="setFilter(f.key)"
                    >
                        {{ f.label }}
                    </button>
                </div>

                <!-- Liste -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                    <div v-if="notifications.data.length === 0" class="px-6 py-10 text-center text-sm text-gray-500">
                        Aucune notification à afficher.
                    </div>

                    <ul v-else class="divide-y divide-gray-100">
                        <li
                            v-for="notif in notifications.data"
                            :key="notif.id"
                            class="flex items-start gap-4 px-6 py-4 transition hover:bg-gray-50"
                            :class="{ 'bg-blue-50/40': !notif.read_at }"
                        >
                            <!-- Icône -->
                            <span class="mt-0.5 text-2xl">{{ notif.data?.icon ?? '🔔' }}</span>

                            <!-- Contenu -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            {{ notif.data?.title ?? 'Notification' }}
                                            <span v-if="!notif.read_at" class="ml-2 rounded-full bg-blue-500 px-2 py-0.5 text-xs font-bold text-white">
                                                Nouveau
                                            </span>
                                        </p>
                                        <p class="mt-0.5 text-sm text-gray-600">{{ notif.data?.message }}</p>
                                    </div>
                                    <span class="shrink-0 text-xs text-gray-400">{{ timeAgo(notif.created_at) }}</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex shrink-0 items-center gap-2">
                                <button
                                    v-if="!notif.read_at"
                                    type="button"
                                    class="rounded text-xs text-brand-600 hover:underline"
                                    @click="markRead(notif.id)"
                                >
                                    Marquer lu
                                </button>
                                <button
                                    type="button"
                                    class="rounded text-xs text-red-500 hover:underline"
                                    @click="deleteNotif(notif.id)"
                                >
                                    Supprimer
                                </button>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Pagination -->
                <div v-if="notifications.last_page > 1" class="mt-4 flex justify-center gap-2">
                    <Link
                        v-for="link in notifications.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        class="rounded border px-3 py-1 text-sm"
                        :class="link.active
                            ? 'border-brand-500 bg-brand-600 text-white'
                            : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

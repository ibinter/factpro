<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const unreadCount = ref(0);
const notifications = ref([]);
const open = ref(false);
let pollInterval = null;

const fetchUnreadCount = async () => {
    try {
        const res = await fetch(route('notifications.unread-count'), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (res.ok) {
            const data = await res.json();
            unreadCount.value = data.count;
        }
    } catch (e) {
        // silencieux
    }
};

const fetchRecent = async () => {
    try {
        const res = await fetch(route('notifications.recent'), {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (res.ok) {
            const data = await res.json();
            notifications.value = data.data ?? [];
        }
    } catch (e) {
        // silencieux
    }
};

const toggleDropdown = () => {
    open.value = !open.value;
    if (open.value) {
        fetchRecent();
    }
};

const closeDropdown = (e) => {
    if (!e.target.closest('[data-notification-bell]')) {
        open.value = false;
    }
};

const markAllRead = () => {
    router.post(route('notifications.read-all'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            unreadCount.value = 0;
            notifications.value = notifications.value.map(n => ({ ...n, read_at: new Date().toISOString() }));
        },
    });
};

const typeIcon = (notif) => {
    const type = notif.data?.type ?? notif.type ?? '';
    if (notif.data?.icon) return notif.data.icon;
    if (type.includes('document') || type.includes('invoice') || type.includes('facture')) return '📄';
    if (type.includes('payment') || type.includes('paiement')) return '💰';
    if (type.includes('alert') || type.includes('warning')) return '⚠️';
    if (type.includes('team') || type.includes('user')) return '👥';
    return '🔔';
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

onMounted(() => {
    fetchUnreadCount();
    pollInterval = setInterval(fetchUnreadCount, 30000);
    document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
    document.removeEventListener('click', closeDropdown);
});
</script>

<template>
    <div class="relative" data-notification-bell>
        <!-- Bouton cloche -->
        <button
            type="button"
            class="relative inline-flex items-center rounded-md p-2 text-gray-500 hover:text-gray-700 focus:outline-none"
            @click.stop="toggleDropdown"
            aria-label="Notifications"
        >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <!-- Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute -right-0.5 -top-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown -->
        <div
            v-if="open"
            class="absolute right-0 z-50 mt-2 w-80 rounded-lg border border-gray-200 bg-white shadow-lg"
            @click.stop
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
                <button
                    v-if="unreadCount > 0"
                    type="button"
                    class="text-xs text-brand-600 hover:underline"
                    @click="markAllRead"
                >
                    Tout marquer lu
                </button>
            </div>

            <!-- Corps : liste des 5 dernières notifications -->
            <div class="max-h-72 overflow-y-auto">
                <div v-if="notifications.length === 0" class="px-4 py-5 text-center text-sm text-gray-400">
                    {{ unreadCount > 0 ? `${unreadCount} notification(s) non lue(s)` : 'Aucune notification' }}
                </div>
                <ul v-else class="divide-y divide-gray-50">
                    <li
                        v-for="notif in notifications"
                        :key="notif.id"
                        class="flex items-start gap-3 px-4 py-3 transition hover:bg-gray-50"
                        :class="{ 'border-l-2 border-blue-500 bg-blue-50/40': !notif.read_at }"
                    >
                        <span class="mt-0.5 shrink-0 text-lg">{{ typeIcon(notif) }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900">
                                {{ notif.data?.title ?? 'Notification' }}
                            </p>
                            <p class="mt-0.5 line-clamp-1 text-xs text-gray-500">{{ notif.data?.message }}</p>
                        </div>
                        <span class="shrink-0 text-[10px] text-gray-400">{{ timeAgo(notif.created_at) }}</span>
                    </li>
                </ul>
            </div>

            <!-- Footer -->
            <div class="border-t border-gray-100 px-4 py-2">
                <Link
                    :href="route('notifications.index')"
                    class="block text-center text-sm font-medium text-brand-600 hover:underline"
                    @click="open = false"
                >
                    Voir toutes les notifications →
                </Link>
            </div>
        </div>
    </div>
</template>

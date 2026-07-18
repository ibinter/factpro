<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    members: Array,
    invitations: Array,
    seatLimit: Number, // null = illimité
    seatsUsed: Number,
    canInvite: Boolean,
    isManager: Boolean,
    roles: Array,
});

const roleLabels = {
    owner: 'Propriétaire',
    admin: 'Administrateur',
    member: 'Membre',
    cashier: 'Caissier',
};

const roleHints = {
    admin: 'Admin : tout sauf la facturation du compte.',
    member: 'Membre : documents et clients.',
    cashier: 'Caissier : caisse POS.',
};

const roleBadgeClass = (role) => ({
    owner: 'bg-gold-400 text-brand-900',
    admin: 'bg-brand-50 text-brand-700',
    member: 'bg-gray-100 text-gray-700',
    cashier: 'bg-emerald-50 text-emerald-700',
}[role] ?? 'bg-gray-100 text-gray-700');

const nf = new Intl.NumberFormat('fr-FR');

const seatLabel = computed(() =>
    props.seatLimit === null ? '∞' : nf.format(props.seatLimit),
);

const seatPercent = computed(() => {
    if (props.seatLimit === null || props.seatLimit === 0) return props.seatLimit === null ? 15 : 100;
    return Math.min(100, Math.round((props.seatsUsed / props.seatLimit) * 100));
});

const formatDate = (iso) =>
    new Date(iso).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });

// --- Inviter ---
const showModal = ref(false);
const form = useForm({ email: '', role: 'member' });

const openInvite = () => {
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const submitInvite = () => {
    form.post(route('team.invite'), {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        },
    });
};

// --- Rôle inline ---
const changeRole = (member, event) => {
    router.put(route('team.members.role', member.id), { role: event.target.value }, {
        preserveScroll: true,
    });
};

// --- Retirer ---
const removeMember = (member) => {
    if (!confirm(`Retirer ${member.name} de l'équipe ?`)) return;
    router.delete(route('team.members.remove', member.id), { preserveScroll: true });
};

// --- Annuler invitation ---
const cancelInvite = (invitation) => {
    if (!confirm(`Annuler l'invitation de ${invitation.email} ?`)) return;
    router.delete(route('team.invite.cancel', invitation.id), { preserveScroll: true });
};
</script>

<template>
    <Head title="Équipe" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">Équipe & rôles</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        Invitez des collaborateurs et attribuez-leur un rôle dans la société courante.
                    </p>
                </div>
                <PrimaryButton v-if="isManager" :disabled="!canInvite" @click="openInvite">
                    + Inviter un collaborateur
                </PrimaryButton>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- Carte sièges -->
                <div class="rounded-lg bg-white p-5 shadow">
                    <div class="flex items-end justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Sièges utilisés</div>
                            <div class="mt-1 text-2xl font-bold text-brand-900">
                                {{ nf.format(seatsUsed) }} <span class="text-base font-normal text-gray-400">/ {{ seatLabel }}</span>
                            </div>
                        </div>
                        <Link
                            v-if="seatLimit !== null && seatsUsed >= seatLimit"
                            :href="route('billing.plans')"
                            class="text-sm font-semibold text-brand-600 hover:underline"
                        >
                            Passer au forfait supérieur
                        </Link>
                    </div>
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                        <div
                            class="h-full rounded-full transition-all"
                            :class="seatLimit !== null && seatsUsed >= seatLimit ? 'bg-red-500' : 'bg-brand-600'"
                            :style="{ width: seatPercent + '%' }"
                        ></div>
                    </div>
                    <p v-if="!canInvite && isManager" class="mt-3 text-sm text-red-600">
                        Limite d'utilisateurs atteinte pour votre forfait. Passez au forfait supérieur pour inviter davantage de collaborateurs.
                    </p>
                </div>

                <!-- Membres -->
                <div class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h3 class="font-semibold text-gray-800">Membres de l'équipe</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-5 py-3">Nom</th>
                                    <th class="px-5 py-3">Email</th>
                                    <th class="px-5 py-3">Rôle</th>
                                    <th class="px-5 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="member in members" :key="member.id">
                                    <td class="px-5 py-3 font-medium text-gray-800">{{ member.name }}</td>
                                    <td class="px-5 py-3 text-gray-500">{{ member.email }}</td>
                                    <td class="px-5 py-3">
                                        <span
                                            v-if="member.is_owner || !isManager"
                                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                            :class="roleBadgeClass(member.role)"
                                        >
                                            {{ roleLabels[member.role] ?? member.role }}
                                        </span>
                                        <select
                                            v-else
                                            :value="member.role"
                                            class="rounded-md border-gray-300 py-1 text-sm focus:border-brand-500 focus:ring-brand-500"
                                            @change="changeRole(member, $event)"
                                        >
                                            <option v-for="r in roles" :key="r" :value="r">{{ roleLabels[r] ?? r }}</option>
                                        </select>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <button
                                            v-if="isManager && !member.is_owner"
                                            type="button"
                                            class="text-sm font-semibold text-red-600 hover:underline"
                                            @click="removeMember(member)"
                                        >
                                            Retirer
                                        </button>
                                        <span v-else class="text-xs text-gray-400">—</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Invitations en attente -->
                <div v-if="invitations.length" class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-100 px-5 py-4">
                        <h3 class="font-semibold text-gray-800">Invitations en attente</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-5 py-3">Email</th>
                                    <th class="px-5 py-3">Rôle</th>
                                    <th class="px-5 py-3">Expire le</th>
                                    <th class="px-5 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="invitation in invitations" :key="invitation.id">
                                    <td class="px-5 py-3 font-medium text-gray-800">{{ invitation.email }}</td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="roleBadgeClass(invitation.role)">
                                            {{ roleLabels[invitation.role] ?? invitation.role }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-500">{{ formatDate(invitation.expires_at) }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <button
                                            v-if="isManager"
                                            type="button"
                                            class="text-sm font-semibold text-red-600 hover:underline"
                                            @click="cancelInvite(invitation)"
                                        >
                                            Annuler
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rappel des rôles -->
                <div class="rounded-lg bg-brand-50/60 p-5 text-sm text-brand-900">
                    <div class="mb-2 font-semibold">Rôles disponibles</div>
                    <ul class="space-y-1 text-brand-800">
                        <li v-for="(hint, role) in roleHints" :key="role">• {{ hint }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Modale invitation -->
        <Modal :show="showModal" @close="showModal = false" max-width="lg">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Inviter un collaborateur</h3>

                <div class="space-y-4">
                    <div>
                        <InputLabel value="Adresse email *" />
                        <TextInput v-model="form.email" type="email" class="mt-1 block w-full" required autofocus />
                        <InputError :message="form.errors.email" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Rôle *" />
                        <select
                            v-model="form.role"
                            class="mt-1 block w-full rounded-md border-gray-300 focus:border-brand-500 focus:ring-brand-500"
                        >
                            <option v-for="r in roles" :key="r" :value="r">{{ roleLabels[r] ?? r }}</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">{{ roleHints[form.role] }}</p>
                        <InputError :message="form.errors.role" class="mt-1" />
                    </div>
                </div>

                <p class="mt-4 text-xs text-gray-400">
                    Un lien d'invitation valable 7 jours sera envoyé par email.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing" @click="submitInvite">Envoyer l'invitation</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

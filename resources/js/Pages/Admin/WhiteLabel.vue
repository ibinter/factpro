<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    configs: Array,
});

const editing = ref(null);
const showModal = ref(false);

const form = useForm({
    subdomain: '',
    app_name: 'IBIG FactPro',
    primary_color: '#0062CC',
    secondary_color: '#002D5B',
    accent_color: '#F0C040',
    footer_text: '',
    support_email: '',
    is_active: true,
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.clearErrors();
    form.app_name = 'IBIG FactPro';
    form.primary_color = '#0062CC';
    form.secondary_color = '#002D5B';
    form.accent_color = '#F0C040';
    form.is_active = true;
    showModal.value = true;
};

const openEdit = (c) => {
    editing.value = c;
    form.clearErrors();
    form.subdomain = c.subdomain ?? '';
    form.app_name = c.app_name;
    form.primary_color = c.primary_color;
    form.secondary_color = c.secondary_color;
    form.accent_color = c.accent_color;
    form.footer_text = c.footer_text ?? '';
    form.support_email = c.support_email ?? '';
    form.is_active = c.is_active;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editing.value = null;
    form.reset();
};

const submit = () => {
    if (editing.value) {
        form.put(route('admin.white-label.update', editing.value.id), {
            onSuccess: closeModal,
        });
    } else {
        form.post(route('admin.white-label.store'), {
            onSuccess: closeModal,
        });
    }
};

const destroy = (c) => {
    if (!confirm(`Supprimer la config « ${c.app_name} » ?`)) return;
    router.delete(route('admin.white-label.destroy', c.id));
};

// Prévisualisation live du header
const previewStyle = computed(() => ({
    backgroundColor: form.primary_color,
}));

const previewTextStyle = computed(() => ({
    color: '#ffffff',
}));

const previewAccentStyle = computed(() => ({
    backgroundColor: form.accent_color,
    color: form.secondary_color,
}));
</script>

<template>
    <Head title="White-label" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Console Admin — White-label
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- Onglets admin -->
                <AdminTabs />

                <!-- En-tête + bouton créer -->
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-700">Configurations White-label</h3>
                    <PrimaryButton @click="openCreate">+ Nouvelle config</PrimaryButton>
                </div>

                <!-- Note CNAME -->
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-700">
                    <strong>Note :</strong> Le sous-domaine <code>{subdomain}.ibigfactpro.com</code> doit pointer vers ce serveur via un enregistrement <strong>CNAME</strong>.
                </div>

                <!-- Liste des configs -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Sous-domaine</th>
                                <th class="px-4 py-3 text-left">Nom app</th>
                                <th class="px-4 py-3 text-left">Couleurs</th>
                                <th class="px-4 py-3 text-left">Email support</th>
                                <th class="px-4 py-3 text-center">Actif</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="configs.length === 0">
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucune configuration white-label.</td>
                            </tr>
                            <tr v-for="c in configs" :key="c.id" class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                    {{ c.subdomain ? `${c.subdomain}.ibigfactpro.com` : '—' }}
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ c.app_name }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-1">
                                        <span
                                            class="inline-block h-5 w-5 rounded border border-gray-200"
                                            :style="{ backgroundColor: c.primary_color }"
                                            :title="`Primaire: ${c.primary_color}`"
                                        />
                                        <span
                                            class="inline-block h-5 w-5 rounded border border-gray-200"
                                            :style="{ backgroundColor: c.secondary_color }"
                                            :title="`Secondaire: ${c.secondary_color}`"
                                        />
                                        <span
                                            class="inline-block h-5 w-5 rounded border border-gray-200"
                                            :style="{ backgroundColor: c.accent_color }"
                                            :title="`Accent: ${c.accent_color}`"
                                        />
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ c.support_email ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                        :class="c.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                    >
                                        {{ c.is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="openEdit(c)" class="mr-2 text-brand-600 hover:underline text-xs">Éditer</button>
                                    <button @click="destroy(c)" class="text-red-500 hover:underline text-xs">Supprimer</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal création/édition -->
        <Modal :show="showModal" max-width="2xl" @close="closeModal">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-bold text-gray-800">
                    {{ editing ? 'Modifier la config' : 'Nouvelle config white-label' }}
                </h3>

                <!-- Prévisualisation du header -->
                <div class="mb-5 overflow-hidden rounded-lg border border-gray-200 shadow-sm">
                    <div class="flex items-center gap-3 px-4 py-3" :style="previewStyle">
                        <span class="text-lg font-bold" :style="previewTextStyle">{{ form.app_name || 'Nom app' }}</span>
                        <span class="ml-auto rounded px-2 py-0.5 text-xs font-semibold" :style="previewAccentStyle">Accent</span>
                    </div>
                    <p class="px-4 py-2 text-xs text-gray-400 bg-gray-50">Prévisualisation du header</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Sous-domaine -->
                        <div>
                            <InputLabel for="subdomain" value="Sous-domaine" />
                            <div class="mt-1 flex items-center rounded-md border border-gray-300 focus-within:ring-2 focus-within:ring-brand-500">
                                <input
                                    id="subdomain"
                                    v-model="form.subdomain"
                                    type="text"
                                    placeholder="clientX"
                                    class="flex-1 rounded-l-md border-0 px-3 py-2 text-sm focus:outline-none focus:ring-0"
                                />
                                <span class="rounded-r-md bg-gray-50 px-3 py-2 text-xs text-gray-500 border-l">.ibigfactpro.com</span>
                            </div>
                            <InputError :message="form.errors.subdomain" class="mt-1" />
                        </div>

                        <!-- Nom app -->
                        <div>
                            <InputLabel for="app_name" value="Nom de l'application *" />
                            <TextInput id="app_name" v-model="form.app_name" class="mt-1 block w-full" required />
                            <InputError :message="form.errors.app_name" class="mt-1" />
                        </div>
                    </div>

                    <!-- Palette couleurs -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <InputLabel for="primary_color" value="Couleur primaire *" />
                            <div class="mt-1 flex items-center gap-2">
                                <input id="primary_color" v-model="form.primary_color" type="color" class="h-9 w-12 cursor-pointer rounded border border-gray-300 p-0.5" />
                                <TextInput v-model="form.primary_color" class="flex-1 font-mono text-sm" placeholder="#0062CC" />
                            </div>
                            <InputError :message="form.errors.primary_color" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="secondary_color" value="Couleur secondaire *" />
                            <div class="mt-1 flex items-center gap-2">
                                <input id="secondary_color" v-model="form.secondary_color" type="color" class="h-9 w-12 cursor-pointer rounded border border-gray-300 p-0.5" />
                                <TextInput v-model="form.secondary_color" class="flex-1 font-mono text-sm" placeholder="#002D5B" />
                            </div>
                            <InputError :message="form.errors.secondary_color" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel for="accent_color" value="Couleur accent *" />
                            <div class="mt-1 flex items-center gap-2">
                                <input id="accent_color" v-model="form.accent_color" type="color" class="h-9 w-12 cursor-pointer rounded border border-gray-300 p-0.5" />
                                <TextInput v-model="form.accent_color" class="flex-1 font-mono text-sm" placeholder="#F0C040" />
                            </div>
                            <InputError :message="form.errors.accent_color" class="mt-1" />
                        </div>
                    </div>

                    <!-- Footer + email support -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="support_email" value="Email support" />
                            <TextInput id="support_email" v-model="form.support_email" type="email" class="mt-1 block w-full" placeholder="support@example.com" />
                            <InputError :message="form.errors.support_email" class="mt-1" />
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input id="is_active" v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-brand-600" />
                            <InputLabel for="is_active" value="Config active" class="!mb-0 cursor-pointer" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="footer_text" value="Texte de pied de page" />
                        <textarea
                            id="footer_text"
                            v-model="form.footer_text"
                            rows="2"
                            class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
                            placeholder="© 2026 Mon Entreprise. Tous droits réservés."
                        />
                        <InputError :message="form.errors.footer_text" class="mt-1" />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <SecondaryButton type="button" @click="closeModal">Annuler</SecondaryButton>
                        <PrimaryButton :disabled="form.processing">
                            {{ editing ? 'Enregistrer' : 'Créer' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

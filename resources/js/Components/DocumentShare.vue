<script setup>
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    document: { type: Object, required: true },
    typeLabel: { type: String, default: 'Document' },
});

const showDropdown = ref(false);
const showEmailModal = ref(false);
const copied = ref(false);
const dropdownRef = ref(null);

const shareForm = useForm({
    to: props.document.customer?.email ?? '',
    subject: `${props.typeLabel} ${props.document.number}`,
    body: `Bonjour,\n\nVeuillez trouver ci-joint le ${props.typeLabel} n° ${props.document.number}.\n\nCordialement.`,
});

const documentUrl = () => window.location.href;

const shareWhatsApp = () => {
    const text = encodeURIComponent(
        `${props.typeLabel} ${props.document.number}\n${documentUrl()}`
    );
    window.open(`https://wa.me/?text=${text}`, '_blank');
    showDropdown.value = false;
};

const copyLink = async () => {
    try {
        await navigator.clipboard.writeText(documentUrl());
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch {
        // fallback
        const el = document.createElement('textarea');
        el.value = documentUrl();
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    }
    showDropdown.value = false;
};

const openEmailModal = () => {
    showDropdown.value = false;
    showEmailModal.value = true;
};

const submitEmail = () => {
    shareForm.post(route('documents.share', props.document.id), {
        onSuccess: () => {
            showEmailModal.value = false;
            shareForm.reset('body');
        },
    });
};

const closeOnOutside = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        showDropdown.value = false;
    }
};

onMounted(() => document.addEventListener('mousedown', closeOnOutside));
onBeforeUnmount(() => document.removeEventListener('mousedown', closeOnOutside));
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <!-- Bouton principal -->
        <button
            @click="showDropdown = !showDropdown"
            class="rounded-md border border-teal-300 bg-teal-50 px-4 py-2 text-sm font-semibold text-teal-700 hover:bg-teal-100"
            title="Partager ce document"
        >
            🔗 Partager
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="showDropdown"
                class="absolute right-0 z-50 mt-1 w-52 origin-top-right rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
            >
                <button
                    @click="shareWhatsApp"
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700"
                >
                    <span class="text-lg">💬</span>
                    <span>WhatsApp</span>
                </button>
                <button
                    @click="copyLink"
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700"
                >
                    <span class="text-lg">{{ copied ? '✅' : '📋' }}</span>
                    <span>{{ copied ? 'Lien copié !' : 'Copier le lien' }}</span>
                </button>
                <button
                    @click="openEmailModal"
                    class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700"
                >
                    <span class="text-lg">✉️</span>
                    <span>Envoyer par email</span>
                </button>
            </div>
        </Transition>
    </div>

    <!-- Modal email -->
    <Modal :show="showEmailModal" @close="showEmailModal = false">
        <div class="p-6">
            <h3 class="mb-1 text-lg font-semibold text-gray-800">
                Partager {{ typeLabel }} {{ document.number }}
            </h3>
            <p class="mb-4 text-sm text-gray-500">
                Un lien vers ce document sera inclus dans le message.
            </p>
            <div class="space-y-4">
                <div>
                    <InputLabel value="Destinataire *" />
                    <TextInput
                        v-model="shareForm.to"
                        type="email"
                        class="mt-1 block w-full"
                        placeholder="destinataire@exemple.com"
                    />
                    <InputError :message="shareForm.errors.to" class="mt-1" />
                </div>
                <div>
                    <InputLabel value="Sujet *" />
                    <TextInput
                        v-model="shareForm.subject"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="shareForm.errors.subject" class="mt-1" />
                </div>
                <div>
                    <InputLabel value="Message" />
                    <textarea
                        v-model="shareForm.body"
                        rows="5"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500 text-sm"
                    ></textarea>
                    <InputError :message="shareForm.errors.body" class="mt-1" />
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="showEmailModal = false">Annuler</SecondaryButton>
                <PrimaryButton :disabled="shareForm.processing" @click="submitEmail">
                    {{ shareForm.processing ? 'Envoi…' : '✉️ Envoyer' }}
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>

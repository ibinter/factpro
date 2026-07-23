<script setup>
import { Head } from '@inertiajs/vue3';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';
import Analytics from '@/Components/Analytics.vue';

defineProps({
    canLogin:    { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const page = usePage();
const success = computed(() => page.props.flash?.contact_success);

const form = useForm({
    name:    '',
    email:   '',
    phone:   '',
    subject: '',
    message: '',
    rgpd:    false,
});

const subjects = [
    'Question générale',
    'Demande de démo',
    'Support technique',
    'Partenariat',
    'Facturation/Paiement',
    'Autre',
];

function submit() {
    form.post(route('contact.store'), { preserveScroll: true });
}
</script>

<template>
    <Head title="Contactez-nous — IBIG FactPro">
        <meta name="description" content="Contactez l'équipe IBIG Soft pour une démo, du support ou un partenariat. Réponse sous 24h.">
        <meta property="og:title" content="Contact IBIG FactPro — Support &amp; Démo">
    </Head>

    <PublicNav :can-login="canLogin" :can-register="canRegister" />

    <!-- Hero -->
    <section style="background:linear-gradient(135deg,#001d3d 0%,#002D5B 100%)" class="py-16 px-6 text-center text-white">
        <div class="mx-auto max-w-2xl">
            <h1 class="text-4xl font-extrabold mb-3">Contactez-nous</h1>
            <p class="text-white/70 text-lg">Notre équipe répond en moins de 24h</p>
        </div>
    </section>

    <!-- Corps -->
    <section class="bg-gray-50 py-16 px-6">
        <div class="mx-auto max-w-6xl">
            <div class="grid gap-12 lg:grid-cols-2">

                <!-- Formulaire -->
                <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Envoyer un message</h2>

                    <!-- Message succès -->
                    <div v-if="success" class="mb-6 rounded-xl p-4 text-sm font-semibold" style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0">
                        ✅ Votre message a été envoyé ! Nous vous répondrons sous 24h.
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">

                        <!-- Nom -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                placeholder="Koné Ibrahim"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="vous@exemple.com"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <!-- Téléphone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone <span class="text-gray-400 text-xs">(optionnel)</span></label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                placeholder="+225 07 00 00 00 00"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
                        </div>

                        <!-- Sujet -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sujet <span class="text-red-500">*</span></label>
                            <select
                                v-model="form.subject"
                                required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white"
                            >
                                <option value="" disabled>Choisir un sujet…</option>
                                <option v-for="s in subjects" :key="s" :value="s">{{ s }}</option>
                            </select>
                            <p v-if="form.errors.subject" class="mt-1 text-xs text-red-600">{{ form.errors.subject }}</p>
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                            <textarea
                                v-model="form.message"
                                required
                                rows="5"
                                placeholder="Décrivez votre demande en détail (minimum 20 caractères)…"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 resize-none"
                            ></textarea>
                            <p v-if="form.errors.message" class="mt-1 text-xs text-red-600">{{ form.errors.message }}</p>
                        </div>

                        <!-- RGPD -->
                        <div class="flex items-start gap-3">
                            <input
                                id="rgpd"
                                v-model="form.rgpd"
                                type="checkbox"
                                required
                                class="mt-0.5 h-4 w-4 rounded border-gray-300 accent-blue-600"
                            />
                            <label for="rgpd" class="text-sm text-gray-600 cursor-pointer">
                                J'accepte que mes données soient utilisées pour traiter ma demande
                            </label>
                        </div>
                        <p v-if="form.errors.rgpd" class="mt-1 text-xs text-red-600">{{ form.errors.rgpd }}</p>

                        <!-- Submit -->
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full rounded-xl py-3 text-sm font-bold transition hover:opacity-90 disabled:opacity-60"
                            style="background:#F0C040;color:#001d3d"
                        >
                            {{ form.processing ? 'Envoi en cours…' : 'Envoyer le message →' }}
                        </button>
                    </form>
                </div>

                <!-- Infos contact -->
                <div class="space-y-5">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Nos coordonnées</h2>

                    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 flex gap-4">
                        <span class="text-2xl">📧</span>
                        <div>
                            <div class="text-sm font-bold text-gray-900 mb-1">Email</div>
                            <a href="mailto:support@ibigsoft.com" class="block text-sm text-blue-600 hover:underline">support@ibigsoft.com</a>
                            <a href="mailto:commercial@ibigsoft.com" class="block text-sm text-blue-600 hover:underline">commercial@ibigsoft.com</a>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 flex gap-4">
                        <span class="text-2xl">📱</span>
                        <div>
                            <div class="text-sm font-bold text-gray-900 mb-1">WhatsApp</div>
                            <a href="https://wa.me/2250700000000" target="_blank" rel="noopener" class="text-sm text-blue-600 hover:underline">
                                +225 07 XX XX XX XX
                            </a>
                            <div class="text-xs text-gray-400 mt-0.5">Réponse rapide via WhatsApp Business</div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 flex gap-4">
                        <span class="text-2xl">📍</span>
                        <div>
                            <div class="text-sm font-bold text-gray-900 mb-1">Adresse</div>
                            <div class="text-sm text-gray-600">Cocody, Abidjan, Côte d'Ivoire</div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 flex gap-4">
                        <span class="text-2xl">🕐</span>
                        <div>
                            <div class="text-sm font-bold text-gray-900 mb-1">Horaires</div>
                            <div class="text-sm text-gray-600">Lun–Ven 8h–18h (UTC+0)</div>
                            <div class="text-sm font-semibold" style="color:#001d3d">Urgences 24h/7j</div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-white border border-gray-100 shadow-sm p-6 flex gap-4">
                        <span class="text-2xl">🌍</span>
                        <div>
                            <div class="text-sm font-bold text-gray-900 mb-1">Zones couvertes</div>
                            <div class="text-sm text-gray-600">Afrique de l'Ouest · Afrique Centrale · Maghreb</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <PublicFooter />
    <Analytics />
</template>

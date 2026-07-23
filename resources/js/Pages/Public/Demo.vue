<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';

defineProps({
    canLogin:    { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const page = usePage();
const success = computed(() => page.props.flash?.demo_success);

const form = useForm({
    name:         '',
    email:        '',
    phone:        '',
    company:      '',
    country:      '',
    company_size: '',
    sector:       '',
    slot:         'flexible',
    message:      '',
});

function submit() {
    form.post(route('demo.store'), { preserveScroll: true });
}

const slots = [
    { value: 'morning',   label: 'Matin',        sub: '9h–12h',   icon: '🌅' },
    { value: 'afternoon', label: 'Après-midi',   sub: '14h–17h',  icon: '☀️' },
    { value: 'evening',   label: 'Soir',          sub: '17h–19h',  icon: '🌆' },
    { value: 'flexible',  label: 'Flexible',      sub: 'n\'importe quand', icon: '🔄' },
];

const companySizes = ['1-5', '6-20', '21-50', '51-200', '200+'];

const demoFeatures = [
    'Tableau de bord en temps réel',
    'Création de facture en direct',
    'Module POS (point de vente)',
    'Gestion des stocks',
    'Rapports & KPIs avancés',
    'Intégrations paiement mobile',
];
</script>

<template>
    <Head title="Demander une démo — IBIG FactPro">
        <meta name="description" content="Réservez une démonstration personnalisée de FactPro. Notre équipe vous contacte sous 24h. Sans engagement.">
        <meta property="og:title" content="Démo FactPro — Voyez le logiciel en action">
    </Head>

    <PublicNav :can-login="canLogin" :can-register="canRegister" />

    <!-- Hero -->
    <section style="background: linear-gradient(135deg, #001d3d 0%, #002D5B 100%)" class="py-16 px-6 text-center text-white">
        <div class="mx-auto max-w-3xl">
            <span class="mb-4 inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#C9A84C;color:#002D5B;">
                ⚡ Démo gratuite · 30 minutes
            </span>
            <h1 class="mt-4 text-4xl font-extrabold leading-tight md:text-5xl">
                Voyez FactPro en action
            </h1>
            <p class="mt-4 text-lg text-white/75">
                Une démonstration personnalisée avec notre équipe, adaptée à votre secteur d'activité
            </p>
            <ul class="mt-6 flex flex-wrap justify-center gap-4 text-sm font-semibold text-white/90">
                <li class="flex items-center gap-1"><span style="color:#C9A84C">✓</span> Démo personnalisée selon votre secteur</li>
                <li class="flex items-center gap-1"><span style="color:#C9A84C">✓</span> Sans engagement</li>
                <li class="flex items-center gap-1"><span style="color:#C9A84C">✓</span> Réponse sous 24h</li>
            </ul>
        </div>
    </section>

    <!-- Corps principal -->
    <section class="bg-gray-50 py-16 px-6">
        <div class="mx-auto max-w-6xl">
            <div class="grid gap-10 lg:grid-cols-5">

                <!-- Formulaire (60%) -->
                <div class="lg:col-span-3">
                    <!-- Message succès -->
                    <div
                        v-if="success"
                        class="mb-6 rounded-xl border border-green-200 bg-green-50 p-6 text-center"
                    >
                        <div class="mb-2 text-3xl">✓</div>
                        <p class="text-lg font-bold text-green-800">Demande envoyée !</p>
                        <p class="mt-1 text-sm text-green-700">
                            Notre équipe vous contacte sous 24h pour planifier votre démo personnalisée.
                        </p>
                    </div>

                    <div class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-gray-100">
                        <h2 class="mb-6 text-2xl font-extrabold text-gray-900">Réserver ma démo</h2>

                        <form class="space-y-5" @submit.prevent="submit">
                            <!-- Nom + Email -->
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Votre nom complet <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Jean Dupont"
                                        required
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    >
                                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Email professionnel <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        placeholder="jean@société.com"
                                        required
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    >
                                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                                </div>
                            </div>

                            <!-- Téléphone + Société -->
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Téléphone</label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        placeholder="+225 07 00 00 00 00"
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    >
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Nom de votre société <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.company"
                                        type="text"
                                        placeholder="ACME SARL"
                                        required
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    >
                                    <p v-if="form.errors.company" class="mt-1 text-xs text-red-600">{{ form.errors.company }}</p>
                                </div>
                            </div>

                            <!-- Pays + Taille -->
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Pays <span class="text-red-500">*</span></label>
                                    <input
                                        v-model="form.country"
                                        type="text"
                                        placeholder="Côte d'Ivoire"
                                        required
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    >
                                    <p v-if="form.errors.country" class="mt-1 text-xs text-red-600">{{ form.errors.country }}</p>
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-gray-700">Taille de l'entreprise <span class="text-red-500">*</span></label>
                                    <select
                                        v-model="form.company_size"
                                        required
                                        class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    >
                                        <option value="" disabled>Sélectionner...</option>
                                        <option v-for="s in companySizes" :key="s" :value="s">{{ s }} employés</option>
                                    </select>
                                    <p v-if="form.errors.company_size" class="mt-1 text-xs text-red-600">{{ form.errors.company_size }}</p>
                                </div>
                            </div>

                            <!-- Secteur -->
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-700">Secteur d'activité</label>
                                <input
                                    v-model="form.sector"
                                    type="text"
                                    placeholder="Commerce, BTP, Santé, Cabinet comptable..."
                                    class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                >
                            </div>

                            <!-- Créneau préféré -->
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-gray-700">Créneau préféré <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    <label
                                        v-for="s in slots"
                                        :key="s.value"
                                        :class="[
                                            'flex cursor-pointer flex-col items-center rounded-xl border-2 px-3 py-3 text-center text-xs font-semibold transition',
                                            form.slot === s.value
                                                ? 'border-blue-500 bg-blue-50 text-blue-700'
                                                : 'border-gray-200 text-gray-600 hover:border-gray-300'
                                        ]"
                                    >
                                        <input type="radio" :value="s.value" v-model="form.slot" class="sr-only">
                                        <span class="mb-1 text-xl">{{ s.icon }}</span>
                                        <span>{{ s.label }}</span>
                                        <span class="font-normal text-gray-400">{{ s.sub }}</span>
                                    </label>
                                </div>
                                <p v-if="form.errors.slot" class="mt-1 text-xs text-red-600">{{ form.errors.slot }}</p>
                            </div>

                            <!-- Message -->
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-gray-700">Message (optionnel)</label>
                                <textarea
                                    v-model="form.message"
                                    rows="3"
                                    placeholder="Précisez vos besoins spécifiques, vos questions..."
                                    class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                ></textarea>
                            </div>

                            <!-- Bouton -->
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full rounded-xl py-4 text-base font-extrabold tracking-wide text-white shadow-lg transition active:scale-95 disabled:opacity-60"
                                style="background: linear-gradient(135deg, #C9A84C 0%, #b8963f 100%);"
                            >
                                <span v-if="form.processing">Envoi en cours...</span>
                                <span v-else>Demander ma démo gratuite →</span>
                            </button>

                            <p class="text-center text-xs text-gray-400">
                                Sans engagement · Réponse garantie sous 24h · Vos données restent confidentielles
                            </p>
                        </form>
                    </div>
                </div>

                <!-- Sidebar (40%) -->
                <div class="flex flex-col gap-6 lg:col-span-2">

                    <!-- Ce que vous verrez -->
                    <div class="rounded-2xl p-6 text-white" style="background: linear-gradient(135deg, #001d3d 0%, #002D5B 100%);">
                        <h3 class="mb-4 text-lg font-extrabold">Ce que vous verrez pendant la démo</h3>
                        <ul class="space-y-3">
                            <li
                                v-for="f in demoFeatures"
                                :key="f"
                                class="flex items-center gap-3 text-sm text-white/85"
                            >
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-xs font-bold" style="background:#C9A84C;color:#002D5B;">✓</span>
                                {{ f }}
                            </li>
                        </ul>
                    </div>

                    <!-- Témoignage -->
                    <div class="rounded-2xl p-6" style="background: #FDF8EC; border: 1px solid #e9d99a;">
                        <p class="text-sm italic text-gray-700">
                            "FactPro nous a fait gagner 2h par jour sur la facturation et le suivi des paiements. Une révolution pour notre cabinet."
                        </p>
                        <p class="mt-3 text-xs font-bold text-gray-600">— Amara Koné, Cabinet Koné &amp; Associés</p>
                        <div class="mt-2 flex gap-0.5 text-yellow-400">
                            <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                        </div>
                    </div>

                    <!-- Ou démarrez maintenant -->
                    <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-100">
                        <p class="text-sm font-semibold text-gray-700">Pas le temps d'attendre ?</p>
                        <p class="mt-1 text-xs text-gray-500">Accédez à FactPro dès maintenant, sans carte bancaire.</p>
                        <a
                            href="/register"
                            class="mt-4 block rounded-xl border-2 px-4 py-3 text-center text-sm font-bold transition hover:opacity-90"
                            style="border-color:#002D5B;color:#002D5B;"
                        >
                            Essai gratuit 14 jours →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Section confiance -->
            <div class="mt-14 rounded-2xl bg-white px-8 py-6 shadow-sm ring-1 ring-gray-100">
                <ul class="flex flex-wrap items-center justify-center gap-8 text-sm font-bold text-gray-600">
                    <li class="flex items-center gap-2">
                        <span class="text-xl" style="color:#C9A84C;">🏢</span>
                        <span>500+ clients</span>
                    </li>
                    <li class="text-gray-200">|</li>
                    <li class="flex items-center gap-2">
                        <span class="text-xl" style="color:#C9A84C;">🌍</span>
                        <span>18 pays</span>
                    </li>
                    <li class="text-gray-200">|</li>
                    <li class="flex items-center gap-2">
                        <span class="text-yellow-400 text-xl">★</span>
                        <span>4.8/5 étoiles</span>
                    </li>
                    <li class="text-gray-200">|</li>
                    <li class="flex items-center gap-2">
                        <span class="text-xl" style="color:#C9A84C;">💬</span>
                        <span>Support 24/7</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <PublicFooter />
</template>

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
const success = computed(() => page.props.flash?.partner_success);

const form = useForm({
    name:          '',
    email:         '',
    company:       '',
    country:       '',
    type:          '',
    clients_count: '',
    message:       '',
});

function scrollToForm() {
    document.getElementById('candidature')?.scrollIntoView({ behavior: 'smooth' });
}

function submit() {
    form.post(route('partners.apply'), { preserveScroll: true });
}
</script>

<template>
    <Head title="Partenaires & Revendeurs — IBIG FactPro">
        <meta name="description" content="Rejoignez le programme partenaires IBIG FactPro. Jusqu'à 30% de commission récurrente. Intégrateurs, revendeurs, consultants et agences digitales bienvenus.">
        <meta property="og:title" content="Programme Partenaires IBIG FactPro — Devenez partenaire">
    </Head>

    <PublicNav :can-login="canLogin" :can-register="canRegister" />

    <!-- ── Hero ── -->
    <section style="background:linear-gradient(135deg,#001d3d 0%,#002D5B 100%)" class="py-20 px-6 text-center text-white">
        <div class="mx-auto max-w-3xl">
            <div class="mb-4 inline-block rounded-full px-4 py-1.5 text-xs font-bold uppercase tracking-widest" style="background:rgba(240,192,64,.15);color:#F0C040;border:1px solid rgba(240,192,64,.3)">
                Programme Partenaires
            </div>
            <h1 class="mb-5 text-4xl font-extrabold leading-tight sm:text-5xl">
                Devenez Partenaire<br><span style="color:#F0C040">IBIG FactPro</span>
            </h1>
            <p class="mb-8 text-lg text-white/75 leading-relaxed">
                Développez votre activité en proposant la solution de facturation <strong class="text-white">#1 en Afrique francophone</strong>.<br>
                Commissions récurrentes, outils marketing, support dédié.
            </p>
            <button
                @click="scrollToForm"
                class="rounded-xl px-8 py-4 text-base font-bold shadow-lg transition hover:scale-105 hover:shadow-xl"
                style="background:#F0C040;color:#001d3d"
            >
                Candidater maintenant →
            </button>
        </div>
        <!-- Bande stats -->
        <div class="mx-auto mt-12 max-w-4xl rounded-2xl px-6 py-4" style="background:rgba(240,192,64,.12);border:1px solid rgba(240,192,64,.25)">
            <div class="flex flex-wrap items-center justify-center gap-6 text-sm font-semibold" style="color:#F0C040">
                <span>500+ clients</span>
                <span class="opacity-40">·</span>
                <span>18 pays</span>
                <span class="opacity-40">·</span>
                <span>30% commission récurrente</span>
                <span class="opacity-40">·</span>
                <span>Support dédié</span>
            </div>
        </div>
    </section>

    <!-- ── Types de partenaires ── -->
    <section class="py-16 px-6 bg-white">
        <div class="mx-auto max-w-6xl">
            <h2 class="mb-3 text-center text-3xl font-extrabold text-gray-900">Types de partenaires</h2>
            <p class="mb-10 text-center text-gray-500">Quel que soit votre profil, il existe un programme adapté.</p>
            <div class="grid gap-6 sm:grid-cols-2">

                <!-- Intégrateur technique -->
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-6 hover:shadow-md transition">
                    <div class="mb-3 text-3xl">🔧</div>
                    <h3 class="mb-1 text-lg font-bold text-gray-900">Intégrateur technique</h3>
                    <p class="mb-3 text-sm font-semibold" style="color:#002D5B">Pour qui : Sociétés IT, développeurs, ESN</p>
                    <ul class="mb-4 space-y-1 text-sm text-gray-600">
                        <li>✓ Accès API complète</li>
                        <li>✓ Co-branding et white-label</li>
                        <li>✓ Support technique prioritaire</li>
                    </ul>
                    <div class="inline-block rounded-full px-4 py-1.5 text-xs font-bold" style="background:#002D5B;color:#F0C040">
                        25% par abonnement souscrit
                    </div>
                </div>

                <!-- Revendeur -->
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-6 hover:shadow-md transition">
                    <div class="mb-3 text-3xl">🤝</div>
                    <h3 class="mb-1 text-lg font-bold text-gray-900">Revendeur</h3>
                    <p class="mb-3 text-sm font-semibold" style="color:#002D5B">Pour qui : Distributeurs, agents commerciaux</p>
                    <ul class="mb-4 space-y-1 text-sm text-gray-600">
                        <li>✓ Prix revendeur -30%</li>
                        <li>✓ Marque blanche possible</li>
                        <li>✓ Formation incluse</li>
                    </ul>
                    <div class="inline-block rounded-full px-4 py-1.5 text-xs font-bold" style="background:#002D5B;color:#F0C040">
                        30% par abonnement
                    </div>
                </div>

                <!-- Consultant / Expert-comptable -->
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-6 hover:shadow-md transition">
                    <div class="mb-3 text-3xl">📊</div>
                    <h3 class="mb-1 text-lg font-bold text-gray-900">Consultant / Expert-comptable</h3>
                    <p class="mb-3 text-sm font-semibold" style="color:#002D5B">Pour qui : Cabinets comptables, consultants en gestion</p>
                    <ul class="mb-4 space-y-1 text-sm text-gray-600">
                        <li>✓ Tableau de bord multi-clients</li>
                        <li>✓ Facturation groupée</li>
                        <li>✓ Certification FactPro</li>
                    </ul>
                    <div class="inline-block rounded-full px-4 py-1.5 text-xs font-bold" style="background:#002D5B;color:#F0C040">
                        20% récurrent + prime fidélisation
                    </div>
                </div>

                <!-- Agence digitale -->
                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-6 hover:shadow-md transition">
                    <div class="mb-3 text-3xl">🎨</div>
                    <h3 class="mb-1 text-lg font-bold text-gray-900">Agence digitale</h3>
                    <p class="mb-3 text-sm font-semibold" style="color:#002D5B">Pour qui : Agences web, digitales, marketing</p>
                    <ul class="mb-4 space-y-1 text-sm text-gray-600">
                        <li>✓ Package "FactPro inclus" dans vos offres</li>
                        <li>✓ Templates personnalisés</li>
                        <li>✓ Co-branding complet</li>
                    </ul>
                    <div class="inline-block rounded-full px-4 py-1.5 text-xs font-bold" style="background:#002D5B;color:#F0C040">
                        25% + bonus volume
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ── Avantages programme ── -->
    <section class="py-16 px-6" style="background:#f8fafc">
        <div class="mx-auto max-w-6xl">
            <h2 class="mb-3 text-center text-3xl font-extrabold text-gray-900">Les avantages du programme</h2>
            <p class="mb-10 text-center text-gray-500">Tout ce dont vous avez besoin pour réussir avec FactPro.</p>
            <div class="grid gap-6 sm:grid-cols-3">

                <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100 text-center">
                    <div class="mb-3 text-4xl">💰</div>
                    <h3 class="mb-2 text-lg font-bold text-gray-900">Commission récurrente</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Gagnez jusqu'à <strong>30%</strong> sur chaque abonnement, tant que le client reste actif. Sans plafond sur le nombre de clients référés.
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100 text-center">
                    <div class="mb-3 text-4xl">🎯</div>
                    <h3 class="mb-2 text-lg font-bold text-gray-900">Support dédié</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Un account manager dédié, support prioritaire 24h, et accès bêta aux nouvelles fonctionnalités avant tout le monde.
                    </p>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100 text-center">
                    <div class="mb-3 text-4xl">📢</div>
                    <h3 class="mb-2 text-lg font-bold text-gray-900">Outils marketing</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Kit marketing complet, landing pages personnalisées, co-branding et formations certifiantes incluses.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ── Comment ça marche ── -->
    <section class="py-16 px-6 bg-white">
        <div class="mx-auto max-w-5xl">
            <h2 class="mb-3 text-center text-3xl font-extrabold text-gray-900">Comment ça marche ?</h2>
            <p class="mb-12 text-center text-gray-500">Du formulaire à la première commission en quelques jours.</p>
            <div class="grid gap-6 sm:grid-cols-4">

                <div class="text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full text-lg font-extrabold text-white" style="background:#002D5B">1</div>
                    <h3 class="mb-1 font-bold text-gray-900">Candidatez</h3>
                    <p class="text-sm text-gray-500">Remplissez le formulaire ci-dessous (2 min)</p>
                </div>

                <div class="text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full text-lg font-extrabold text-white" style="background:#002D5B">2</div>
                    <h3 class="mb-1 font-bold text-gray-900">Évaluation</h3>
                    <p class="text-sm text-gray-500">Notre équipe vous contacte sous 48h</p>
                </div>

                <div class="text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full text-lg font-extrabold text-white" style="background:#002D5B">3</div>
                    <h3 class="mb-1 font-bold text-gray-900">Formation</h3>
                    <p class="text-sm text-gray-500">Formation gratuite de 2h sur FactPro</p>
                </div>

                <div class="text-center">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full text-lg font-extrabold" style="background:#F0C040;color:#001d3d">4</div>
                    <h3 class="mb-1 font-bold text-gray-900">Go !</h3>
                    <p class="text-sm text-gray-500">Commencez à référer et à gagner</p>
                </div>

            </div>
        </div>
    </section>

    <!-- ── Témoignages partenaires ── -->
    <section class="py-16 px-6" style="background:#f8fafc">
        <div class="mx-auto max-w-4xl">
            <h2 class="mb-3 text-center text-3xl font-extrabold text-gray-900">Ils sont déjà partenaires</h2>
            <p class="mb-10 text-center text-gray-500">Ce que disent nos partenaires actifs.</p>
            <div class="grid gap-6 sm:grid-cols-2">

                <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
                    <div class="mb-4 flex gap-1">
                        <span v-for="i in 5" :key="i" class="text-yellow-400">★</span>
                    </div>
                    <p class="mb-4 text-sm text-gray-700 leading-relaxed italic">
                        "En 6 mois de partenariat avec IBIG, nous avons intégré FactPro chez 23 clients PME. Les commissions récurrentes représentent maintenant 40% de notre CA mensuel."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full font-bold text-white text-sm" style="background:#002D5B">AK</div>
                        <div>
                            <div class="text-sm font-bold text-gray-900">Ange Kouassi</div>
                            <div class="text-xs text-gray-500">Directeur — Kouassi Informatique, Abidjan</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
                    <div class="mb-4 flex gap-1">
                        <span v-for="i in 5" :key="i" class="text-yellow-400">★</span>
                    </div>
                    <p class="mb-4 text-sm text-gray-700 leading-relaxed italic">
                        "Le programme partenaires d'IBIG est le plus structuré du marché africain. Formation de qualité, tools marketing prêts à l'emploi, et un support réactif."
                    </p>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full font-bold text-white text-sm" style="background:#002D5B">MS</div>
                        <div>
                            <div class="text-sm font-bold text-gray-900">Mamadou Sow</div>
                            <div class="text-xs text-gray-500">Expert-comptable — EasyCompta, Dakar</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ── Formulaire de candidature ── -->
    <section id="candidature" class="py-16 px-6 bg-gray-50">
        <div class="mx-auto max-w-2xl">
            <div class="rounded-2xl bg-white p-8 shadow-sm border border-gray-100">
                <h2 class="mb-2 text-2xl font-extrabold text-gray-900 text-center">Candidater au programme partenaires</h2>
                <p class="mb-8 text-center text-sm text-gray-500">Notre équipe vous répond sous 48h ouvrables.</p>

                <!-- Message de succès -->
                <div v-if="success" class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 flex items-start gap-3">
                    <span class="mt-0.5 text-green-600 font-bold text-lg">✓</span>
                    <div>
                        <div class="font-bold text-green-800">Candidature envoyée !</div>
                        <div class="text-sm text-green-700">Notre équipe partenaires vous contacte sous 48h.</div>
                    </div>
                </div>

                <form v-else @submit.prevent="submit" class="space-y-5">

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Votre nom complet <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Ex : Ange Kouassi"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            :class="{ 'border-red-400': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Email professionnel <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="vous@société.com"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            :class="{ 'border-red-400': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Nom de votre société <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.company"
                            type="text"
                            required
                            placeholder="Ex : Kouassi Informatique"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            :class="{ 'border-red-400': form.errors.company }"
                        />
                        <p v-if="form.errors.company" class="mt-1 text-xs text-red-600">{{ form.errors.company }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Pays <span class="text-red-500">*</span></label>
                        <input
                            v-model="form.country"
                            type="text"
                            required
                            placeholder="Ex : Côte d'Ivoire"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            :class="{ 'border-red-400': form.errors.country }"
                        />
                        <p v-if="form.errors.country" class="mt-1 text-xs text-red-600">{{ form.errors.country }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Type de partenariat <span class="text-red-500">*</span></label>
                        <select
                            v-model="form.type"
                            required
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            :class="{ 'border-red-400': form.errors.type }"
                        >
                            <option value="" disabled>Choisir un type...</option>
                            <option value="integrator">Intégrateur technique</option>
                            <option value="reseller">Revendeur</option>
                            <option value="consultant">Consultant / Expert-comptable</option>
                            <option value="agency">Agence digitale</option>
                        </select>
                        <p v-if="form.errors.type" class="mt-1 text-xs text-red-600">{{ form.errors.type }}</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Nombre de clients potentiels <span class="text-gray-400 font-normal">(optionnel)</span></label>
                        <input
                            v-model="form.clients_count"
                            type="number"
                            min="0"
                            placeholder="Ex : 15"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Message / Présentation <span class="text-gray-400 font-normal">(optionnel)</span></label>
                        <textarea
                            v-model="form.message"
                            rows="4"
                            placeholder="Décrivez votre activité, vos objectifs, vos clients cibles..."
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 resize-none"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full rounded-xl px-6 py-4 text-base font-bold shadow-lg transition hover:scale-[1.01] hover:shadow-xl disabled:opacity-60"
                        style="background:#F0C040;color:#001d3d"
                    >
                        <span v-if="form.processing">Envoi en cours...</span>
                        <span v-else>Envoyer ma candidature →</span>
                    </button>

                </form>
            </div>
        </div>
    </section>

    <PublicFooter />
</template>

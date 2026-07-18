<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    plans: Array,
    currentPlanCode: String,
    isTrial: Boolean,
});

const months = ref(1);
const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const form = useForm({ plan_code: null, months: 1 });

const subscribe = (plan) => {
    form.plan_code = plan.code;
    form.months = months.value;
    form.post(route('billing.subscribe'));
};

const priceFor = (plan) => (months.value === 12 ? plan.price_yearly : plan.price_monthly * months.value);

const highlight = (plan) => plan.code === 'pro';
</script>

<template>
    <Head title="Forfaits" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Choisissez votre forfait</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Durée -->
                <div class="flex justify-center">
                    <div class="inline-flex rounded-lg bg-white p-1 shadow">
                        <button
                            v-for="option in [{ v: 1, l: 'Mensuel' }, { v: 3, l: '3 mois' }, { v: 6, l: '6 mois' }, { v: 12, l: 'Annuel −20%' }]"
                            :key="option.v"
                            @click="months = option.v"
                            class="rounded-md px-4 py-2 text-sm font-semibold transition"
                            :class="months === option.v ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-gray-50'"
                        >
                            {{ option.l }}
                        </button>
                    </div>
                </div>

                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="plan in plans"
                        :key="plan.code"
                        class="relative flex flex-col rounded-xl bg-white p-6 shadow transition hover:shadow-lg"
                        :class="highlight(plan) ? 'ring-2 ring-gold-400' : ''"
                    >
                        <div
                            v-if="highlight(plan)"
                            class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-gold-400 px-3 py-0.5 text-xs font-bold text-brand-900"
                        >
                            LE PLUS POPULAIRE
                        </div>

                        <h3 class="text-lg font-bold text-brand-900">{{ plan.name }}</h3>
                        <p class="mt-1 min-h-[2.5rem] text-xs text-gray-500">{{ plan.short_description }}</p>

                        <div class="mt-4">
                            <span class="text-3xl font-extrabold text-gray-900">{{ fmt(priceFor(plan)) }}</span>
                            <span class="text-sm text-gray-500"> FCFA</span>
                            <div class="text-xs text-gray-400">
                                {{ months === 1 ? '/mois' : `pour ${months} mois` }}
                                · ≈ {{ plan.price_eur }} € · {{ plan.price_usd }} $ /mois
                            </div>
                        </div>

                        <ul class="mt-5 flex-1 space-y-2 text-sm">
                            <li v-for="feature in plan.features" :key="feature" class="flex items-start gap-2">
                                <span class="mt-0.5 text-green-500">✓</span>
                                <span class="text-gray-600">{{ feature }}</span>
                            </li>
                        </ul>

                        <button
                            @click="subscribe(plan)"
                            :disabled="form.processing || (plan.code === currentPlanCode && !isTrial)"
                            class="mt-6 w-full rounded-lg py-2.5 text-sm font-bold transition"
                            :class="[
                                plan.code === currentPlanCode && !isTrial
                                    ? 'cursor-default bg-green-100 text-green-700'
                                    : highlight(plan)
                                        ? 'bg-gold-400 text-brand-900 hover:bg-gold-300'
                                        : 'bg-brand-600 text-white hover:bg-brand-700',
                            ]"
                        >
                            {{ plan.code === currentPlanCode && !isTrial ? 'Forfait actuel ✓' : 'Choisir ' + plan.name }}
                        </button>
                    </div>
                </div>

                <p class="text-center text-xs text-gray-400">
                    7 jours d'essai gratuit sur tous les forfaits — sans carte bancaire ·
                    Paiement Mobile Money (Orange, MTN, Wave, Moov), carte bancaire et virement ·
                    Abonnement annuel : payez 10 mois, bénéficiez de 12.
                </p>

                <p class="text-center text-xs text-gray-400">
                    🎟 Un code promo ? Vous pourrez l'appliquer à l'étape suivante (paiement).
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
    products: Array,
    customers: Array,
    session: Object,
    currency: String,
    lastTicket: Object,
});

const page = usePage();
const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Math.round(Number(n ?? 0)));

/* ------------------------------------------------------------------ */
/* Ouverture de session                                                */
/* ------------------------------------------------------------------ */
const openForm = useForm({ opening_float: 0 });
const submitOpen = () => openForm.post(route('pos.session.open'), { preserveScroll: true });

/* ------------------------------------------------------------------ */
/* Recherche produits + scan code-barres                               */
/* ------------------------------------------------------------------ */
const search = ref('');
const searchInput = ref(null);

const filteredProducts = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.products;
    return props.products.filter((p) =>
        (p.name ?? '').toLowerCase().includes(q) ||
        (p.sku ?? '').toLowerCase().includes(q) ||
        (p.barcode ?? '').toLowerCase().includes(q)
    );
});

// Scan code-barres USB : correspondance EXACTE → ajout direct au panier
watch(search, (value) => {
    const code = value.trim();
    if (!code) return;
    const match = props.products.find((p) => p.barcode && p.barcode === code);
    if (match) {
        addToCart(match);
        search.value = '';
    }
});

/* ------------------------------------------------------------------ */
/* Panier                                                              */
/* ------------------------------------------------------------------ */
const cart = ref([]);
const customerId = ref(null);
const globalDiscount = ref(0);

const addToCart = (product) => {
    const existing = cart.value.find((i) => i.product_id === product.id);
    if (existing) {
        existing.quantity += 1;
    } else {
        cart.value.push({
            product_id: product.id,
            name: product.name,
            unit: product.unit || 'unité',
            unit_price: Number(product.price),
            tax_rate: Number(product.tax_rate ?? 0),
            quantity: 1,
        });
    }
};

const increment = (item) => { item.quantity += 1; };
const decrement = (item) => {
    if (item.quantity > 1) item.quantity -= 1;
    else removeItem(item);
};
const removeItem = (item) => {
    cart.value = cart.value.filter((i) => i !== item);
};
const clearCart = () => {
    cart.value = [];
    customerId.value = null;
    globalDiscount.value = 0;
};

const discountFactor = computed(() => {
    const d = Math.min(100, Math.max(0, Number(globalDiscount.value) || 0));
    return 1 - d / 100;
});

const lineTotal = (item) => Math.round(item.quantity * item.unit_price * discountFactor.value * 100) / 100;

const subtotal = computed(() => cart.value.reduce((sum, i) => sum + lineTotal(i), 0));
const taxTotal = computed(() =>
    Math.round(cart.value.reduce((sum, i) => sum + lineTotal(i) * (i.tax_rate / 100), 0) * 100) / 100
);
const grandTotal = computed(() => Math.round((subtotal.value + taxTotal.value) * 100) / 100);

/* ------------------------------------------------------------------ */
/* Encaissement                                                        */
/* ------------------------------------------------------------------ */
const showPayModal = ref(false);
const payMethod = ref('cash');
const received = ref(null);
const submitting = ref(false);

const methods = [
    { value: 'cash', label: 'Espèces', icon: '💵' },
    { value: 'mobile_money', label: 'Mobile Money', icon: '📱' },
    { value: 'card', label: 'Carte', icon: '💳' },
];

const openPayModal = () => {
    payMethod.value = 'cash';
    received.value = null;
    showPayModal.value = true;
};

const setExact = () => { received.value = grandTotal.value; };
const addBill = (amount) => { received.value = (Number(received.value) || 0) + amount; };

const change = computed(() => {
    if (payMethod.value !== 'cash' || received.value === null || received.value === '') return null;
    return Math.round((Number(received.value) - grandTotal.value) * 100) / 100;
});

const canValidate = computed(() => {
    if (!cart.value.length || submitting.value) return false;
    if (payMethod.value === 'cash') return Number(received.value) >= grandTotal.value;
    return true;
});

const submitCheckout = () => {
    if (!canValidate.value) return;
    submitting.value = true;
    router.post(route('pos.checkout'), {
        customer_id: customerId.value,
        lines: cart.value.map((i) => ({
            product_id: i.product_id,
            description: i.name,
            quantity: i.quantity,
            unit: i.unit,
            unit_price: i.unit_price,
            discount_percent: Math.min(100, Math.max(0, Number(globalDiscount.value) || 0)),
            tax_rate: i.tax_rate,
        })),
        payments: [{ method: payMethod.value, amount: grandTotal.value }],
        received: payMethod.value === 'cash' ? Number(received.value) : null,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showPayModal.value = false;
            clearCart();
            if (page.props.lastTicket) {
                ticket.value = page.props.lastTicket;
                showTicketModal.value = true;
            }
        },
        onFinish: () => { submitting.value = false; },
    });
};

/* ------------------------------------------------------------------ */
/* Confirmation ticket + impression                                    */
/* ------------------------------------------------------------------ */
const showTicketModal = ref(false);
const ticket = ref(null);

onMounted(() => {
    if (props.lastTicket) {
        ticket.value = props.lastTicket;
        showTicketModal.value = true;
    }
});

const printTicket = () => {
    if (!ticket.value) return;
    window.open(route('documents.thermal', { document: ticket.value.id }) + '?width=80', '_blank');
};

const newSale = () => {
    showTicketModal.value = false;
    ticket.value = null;
    clearCart();
    window.history.replaceState({}, '', route('pos.index'));
    searchInput.value?.focus();
};

/* ------------------------------------------------------------------ */
/* Clôture de caisse                                                   */
/* ------------------------------------------------------------------ */
const showCloseModal = ref(false);
const closeForm = useForm({ counted_cash: null, notes: '' });
const submitClose = () => closeForm.post(route('pos.session.close'));

const openedSince = computed(() => {
    if (!props.session?.opened_at) return '';
    return new Date(props.session.opened_at).toLocaleString('fr-FR', {
        day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit',
    });
});
</script>

<template>
    <Head title="Caisse" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Point de Vente — Caisse</h2>
                <div v-if="session" class="flex flex-wrap items-center gap-x-5 gap-y-1 text-sm">
                    <span class="text-gray-500">
                        Caissier : <strong class="text-gray-800">{{ page.props.auth.user.name }}</strong>
                    </span>
                    <span class="text-gray-500">Ouvert : <strong class="text-gray-800">{{ openedSince }}</strong></span>
                    <span class="text-gray-500">Tickets : <strong class="text-gray-800">{{ session.tickets_count }}</strong></span>
                    <span class="text-gray-500">
                        CA session : <strong class="text-brand-700">{{ fmt(session.total_sales) }} {{ currency }}</strong>
                    </span>
                    <button
                        class="rounded-lg bg-brand-900 px-4 py-2 font-semibold text-white hover:bg-brand-800"
                        @click="showCloseModal = true"
                    >
                        Clôturer la caisse
                    </button>
                </div>
            </div>
        </template>

        <!-- ============ PAS DE SESSION : ouverture de caisse ============ -->
        <div v-if="!session" class="flex min-h-[60vh] items-center justify-center px-4 py-10">
            <div class="w-full max-w-lg rounded-2xl bg-white p-10 text-center shadow-lg">
                <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-4xl">🧾</div>
                <h3 class="text-2xl font-bold text-brand-900">Ouvrir la caisse</h3>
                <p class="mt-2 text-gray-500">Indiquez le fonds de caisse initial pour démarrer la session.</p>

                <form class="mt-8 space-y-4" @submit.prevent="submitOpen">
                    <div class="text-left">
                        <InputLabel :value="`Fonds de caisse (${currency})`" />
                        <TextInput
                            v-model="openForm.opening_float"
                            type="number" min="0" step="1"
                            class="mt-1 block w-full text-center text-2xl font-bold"
                            required autofocus
                        />
                        <InputError :message="openForm.errors.opening_float" class="mt-1" />
                    </div>
                    <button
                        type="submit"
                        :disabled="openForm.processing"
                        class="h-16 w-full rounded-xl bg-brand-600 text-xl font-bold text-white shadow hover:bg-brand-700 disabled:opacity-50"
                    >
                        🔓 Ouvrir la caisse
                    </button>
                </form>
            </div>
        </div>

        <!-- ============ SESSION OUVERTE : interface de vente ============ -->
        <div v-else class="px-3 py-4 sm:px-4 lg:px-6">
            <div class="grid gap-4 lg:grid-cols-3">
                <!-- GAUCHE : recherche + grille produits -->
                <div class="lg:col-span-2">
                    <input
                        ref="searchInput"
                        v-model="search"
                        type="search"
                        autofocus
                        placeholder="🔍 Rechercher ou scanner (nom, SKU, code-barres)…"
                        class="h-14 w-full rounded-xl border-gray-300 text-lg shadow-sm focus:border-brand-500 focus:ring-brand-500"
                    />

                    <div class="mt-4 grid max-h-[70vh] grid-cols-2 content-start gap-3 overflow-y-auto pb-4 sm:grid-cols-3 xl:grid-cols-4">
                        <button
                            v-for="product in filteredProducts"
                            :key="product.id"
                            class="flex min-h-[7rem] flex-col justify-between rounded-xl border-2 border-gray-200 bg-white p-3 text-left shadow-sm transition hover:border-brand-400 hover:shadow-md active:scale-95"
                            @click="addToCart(product)"
                        >
                            <div class="line-clamp-2 font-semibold text-gray-800">{{ product.name }}</div>
                            <div class="mt-2 flex items-end justify-between gap-1">
                                <span class="text-lg font-bold text-brand-700">{{ fmt(product.price) }}</span>
                                <span
                                    v-if="product.track_stock"
                                    class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                    :class="Number(product.stock_quantity) > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
                                >
                                    {{ Number(product.stock_quantity) }}
                                </span>
                            </div>
                        </button>

                        <div v-if="!filteredProducts.length" class="col-span-full py-12 text-center text-gray-400">
                            Aucun produit ne correspond à « {{ search }} ».
                        </div>
                    </div>
                </div>

                <!-- DROITE : panier -->
                <div class="flex flex-col rounded-2xl bg-white shadow-lg">
                    <div class="border-b px-4 py-3">
                        <h3 class="text-lg font-bold text-brand-900">🛒 Panier ({{ cart.length }})</h3>
                    </div>

                    <div class="max-h-[38vh] flex-1 overflow-y-auto px-4 py-2">
                        <div v-if="!cart.length" class="py-10 text-center text-gray-400">
                            Panier vide — touchez un produit pour l'ajouter.
                        </div>
                        <div
                            v-for="item in cart"
                            :key="item.product_id"
                            class="flex items-center gap-2 border-b py-2 last:border-b-0"
                        >
                            <div class="min-w-0 flex-1">
                                <div class="truncate text-sm font-semibold text-gray-800">{{ item.name }}</div>
                                <div class="text-xs text-gray-400">{{ fmt(item.unit_price) }} × {{ item.quantity }}</div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    class="h-9 w-9 rounded-lg bg-gray-100 text-lg font-bold text-gray-700 hover:bg-gray-200 active:scale-95"
                                    @click="decrement(item)"
                                >−</button>
                                <span class="w-8 text-center font-bold">{{ item.quantity }}</span>
                                <button
                                    class="h-9 w-9 rounded-lg bg-gray-100 text-lg font-bold text-gray-700 hover:bg-gray-200 active:scale-95"
                                    @click="increment(item)"
                                >+</button>
                            </div>
                            <div class="w-20 text-right text-sm font-bold text-gray-800">{{ fmt(lineTotal(item)) }}</div>
                            <button class="text-lg text-red-400 hover:text-red-600" @click="removeItem(item)">✕</button>
                        </div>
                    </div>

                    <div class="space-y-3 border-t px-4 py-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs font-semibold uppercase text-gray-400">Client</label>
                                <select
                                    v-model="customerId"
                                    class="mt-0.5 block w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500"
                                >
                                    <option :value="null">Client de passage</option>
                                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase text-gray-400">Remise %</label>
                                <input
                                    v-model.number="globalDiscount"
                                    type="number" min="0" max="100" step="1"
                                    class="mt-0.5 block w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500"
                                />
                            </div>
                        </div>

                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between text-gray-500">
                                <span>Sous-total HT</span><span>{{ fmt(subtotal) }} {{ currency }}</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>TVA</span><span>{{ fmt(taxTotal) }} {{ currency }}</span>
                            </div>
                            <div class="flex items-baseline justify-between pt-1 text-brand-900">
                                <span class="text-lg font-bold">TOTAL TTC</span>
                                <span class="text-3xl font-extrabold">{{ fmt(grandTotal) }} {{ currency }}</span>
                            </div>
                        </div>

                        <button
                            :disabled="!cart.length"
                            class="h-16 w-full rounded-xl bg-green-600 text-xl font-bold text-white shadow-lg transition hover:bg-green-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="openPayModal"
                        >
                            ENCAISSER {{ fmt(grandTotal) }} {{ currency }}
                        </button>
                        <button
                            v-if="cart.length"
                            class="w-full py-1 text-sm font-semibold text-red-500 hover:underline"
                            @click="clearCart"
                        >
                            Vider le panier
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ MODALE ENCAISSEMENT ============ -->
        <Modal :show="showPayModal" max-width="lg" @close="showPayModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-brand-900">Encaissement</h3>
                <div class="mt-2 rounded-xl bg-brand-50 py-4 text-center">
                    <div class="text-sm font-semibold uppercase text-brand-700">Total à payer</div>
                    <div class="text-4xl font-extrabold text-brand-900">{{ fmt(grandTotal) }} {{ currency }}</div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <button
                        v-for="m in methods"
                        :key="m.value"
                        class="flex h-20 flex-col items-center justify-center rounded-xl border-2 text-sm font-bold transition active:scale-95"
                        :class="payMethod === m.value
                            ? 'border-brand-600 bg-brand-50 text-brand-800'
                            : 'border-gray-200 bg-white text-gray-600 hover:border-brand-300'"
                        @click="payMethod = m.value"
                    >
                        <span class="text-2xl">{{ m.icon }}</span>
                        {{ m.label }}
                    </button>
                </div>

                <div v-if="payMethod === 'cash'" class="mt-4 space-y-3">
                    <div>
                        <InputLabel :value="`Montant reçu (${currency})`" />
                        <TextInput
                            v-model.number="received"
                            type="number" min="0" step="1"
                            class="mt-1 block w-full text-center text-2xl font-bold"
                        />
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button
                            class="h-12 flex-1 rounded-lg bg-gold-400 px-3 font-bold text-brand-900 hover:bg-gold-500 active:scale-95"
                            @click="setExact"
                        >Montant exact</button>
                        <button
                            v-for="bill in [1000, 2000, 5000, 10000]"
                            :key="bill"
                            class="h-12 rounded-lg bg-gray-100 px-4 font-bold text-gray-700 hover:bg-gray-200 active:scale-95"
                            @click="addBill(bill)"
                        >+{{ fmt(bill) }}</button>
                    </div>
                    <div
                        v-if="change !== null"
                        class="rounded-xl py-3 text-center"
                        :class="change >= 0 ? 'bg-green-50' : 'bg-red-50'"
                    >
                        <div class="text-sm font-semibold uppercase" :class="change >= 0 ? 'text-green-700' : 'text-red-600'">
                            {{ change >= 0 ? 'Rendu monnaie' : 'Montant insuffisant' }}
                        </div>
                        <div class="text-3xl font-extrabold" :class="change >= 0 ? 'text-green-700' : 'text-red-600'">
                            {{ fmt(Math.abs(change)) }} {{ currency }}
                        </div>
                    </div>
                </div>

                <InputError :message="page.props.errors?.payments" class="mt-2" />
                <InputError :message="page.props.errors?.session" class="mt-2" />

                <div class="mt-6 flex gap-3">
                    <SecondaryButton class="h-14 flex-1 justify-center" @click="showPayModal = false">Annuler</SecondaryButton>
                    <button
                        :disabled="!canValidate"
                        class="h-14 flex-[2] rounded-lg bg-green-600 text-lg font-bold text-white shadow hover:bg-green-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                        @click="submitCheckout"
                    >
                        {{ submitting ? 'Encaissement…' : '✓ Valider la vente' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- ============ MODALE CONFIRMATION TICKET ============ -->
        <Modal :show="showTicketModal" max-width="md" @close="newSale">
            <div class="p-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-4xl text-green-600">✓</div>
                <h3 class="mt-4 text-xl font-bold text-gray-800">Vente enregistrée</h3>
                <p class="mt-1 text-gray-500">Ticket <strong class="text-brand-800">{{ ticket?.number }}</strong> — {{ fmt(ticket?.total) }} {{ currency }}</p>

                <div class="mt-6 space-y-3">
                    <button
                        class="h-14 w-full rounded-xl bg-brand-600 text-lg font-bold text-white hover:bg-brand-700 active:scale-95"
                        @click="printTicket"
                    >
                        🖨 Imprimer ticket 80mm
                    </button>
                    <button
                        class="h-14 w-full rounded-xl bg-gray-100 text-lg font-bold text-gray-700 hover:bg-gray-200 active:scale-95"
                        @click="newSale"
                    >
                        Nouvelle vente
                    </button>
                </div>
            </div>
        </Modal>

        <!-- ============ MODALE CLÔTURE ============ -->
        <Modal :show="showCloseModal" max-width="md" @close="showCloseModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-brand-900">Clôturer la caisse</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Comptez les espèces présentes dans le tiroir (fonds de caisse inclus) puis validez pour générer le rapport Z.
                </p>

                <form class="mt-5 space-y-4" @submit.prevent="submitClose">
                    <div>
                        <InputLabel :value="`Espèces comptées (${currency})`" />
                        <TextInput
                            v-model="closeForm.counted_cash"
                            type="number" min="0" step="1"
                            class="mt-1 block w-full text-center text-2xl font-bold"
                            required
                        />
                        <InputError :message="closeForm.errors.counted_cash" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Notes (optionnel)" />
                        <textarea
                            v-model="closeForm.notes"
                            rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                        ></textarea>
                    </div>
                    <div class="flex gap-3">
                        <SecondaryButton type="button" class="h-14 flex-1 justify-center" @click="showCloseModal = false">
                            Annuler
                        </SecondaryButton>
                        <button
                            type="submit"
                            :disabled="closeForm.processing"
                            class="h-14 flex-[2] rounded-lg bg-brand-900 text-lg font-bold text-white hover:bg-brand-800 active:scale-95 disabled:opacity-50"
                        >
                            Clôturer et générer le rapport Z
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

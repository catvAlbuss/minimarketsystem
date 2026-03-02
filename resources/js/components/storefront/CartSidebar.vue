<script setup lang="ts">
import { computed } from 'vue';

type Product = {
    id: number;
    name: string;
    final_price: number;
    image: string | null;
};

type CartEntry = {
    product: Product;
    quantity: number;
};

const props = defineProps<{
    cartItems: CartEntry[];
    cartTotal: number;
    cartCount: number;
    checkoutLoading: boolean;
    checkoutMessage: string;
    checkoutError: string;
    paymentMethod: 'cash' | 'card' | 'yape' | 'plin';
    voucher: 'ticket' | 'invoice';
    document: string;
    open: boolean;
}>();

const emit = defineEmits<{
    add: [product: Product];
    decrease: [productId: number];
    remove: [productId: number];
    checkout: [];
    'update:paymentMethod': [v: 'cash' | 'card' | 'yape' | 'plin'];
    'update:voucher': [v: 'ticket' | 'invoice'];
    'update:document': [v: string];
    close: [];
}>();

const paymentLabels: Record<string, string> = {
    cash: '💵 Efectivo',
    card: '💳 Tarjeta',
    yape: '📱 Yape',
    plin: '📱 Plin',
};

const isEmpty = computed(() => props.cartItems.length === 0);
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop -->
        <Transition name="fade">
            <div v-if="open" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm" @click="emit('close')" />
        </Transition>

        <!-- Drawer panel -->
        <Transition name="slide">
            <div v-if="open" class="fixed right-0 top-0 z-50 flex h-full w-full max-w-sm flex-col bg-white shadow-2xl">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🛒</span>
                        <h2 class="text-base font-bold text-slate-900">Tu carrito</h2>
                        <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-600">
                            {{ cartCount }} item(s)
                        </span>
                    </div>
                    <button type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                        @click="emit('close')">
                        ✕
                    </button>
                </div>

                <!-- Items (scrollable) -->
                <div class="flex-1 overflow-y-auto px-5 py-4">
                    <!-- Empty state -->
                    <div v-if="isEmpty" class="flex flex-col items-center gap-3 py-16 text-center text-slate-400">
                        <span class="text-5xl">🛍️</span>
                        <p class="text-sm font-medium">Tu carrito está vacío</p>
                        <p class="text-xs">Agrega productos desde el catálogo</p>
                    </div>

                    <!-- Product rows -->
                    <div v-else class="space-y-3">
                        <article v-for="row in cartItems" :key="row.product.id"
                            class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3">
                            <!-- Thumbnail -->
                            <img v-if="row.product.image" :src="row.product.image" :alt="row.product.name"
                                class="h-12 w-12 shrink-0 rounded-lg object-cover" />
                            <div v-else
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xl">
                                📦
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="line-clamp-1 text-sm font-semibold text-slate-900">{{ row.product.name }}</p>
                                <p class="text-xs text-slate-400">S/ {{ Number(row.product.final_price).toFixed(2) }}
                                    c/u</p>

                                <!-- Quantity controls -->
                                <div class="mt-2 flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <button type="button"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-white border border-slate-200 text-sm font-bold transition hover:bg-slate-100"
                                            @click="emit('decrease', row.product.id)">−</button>
                                        <span class="min-w-[1.5rem] text-center text-sm font-bold text-slate-900">{{
                                            row.quantity }}</span>
                                        <button type="button"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white transition hover:bg-blue-700"
                                            @click="emit('add', row.product)">+</button>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-bold text-blue-700">
                                            S/ {{ (row.quantity * Number(row.product.final_price)).toFixed(2) }}
                                        </span>
                                        <button type="button" class="text-xs text-rose-400 hover:text-rose-600"
                                            @click="emit('remove', row.product.id)">✕</button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>

                <!-- Footer: payment + total + checkout -->
                <div class="border-t border-slate-100 px-5 py-4 space-y-3">
                    <!-- Payment method -->
                    <div>
                        <p class="mb-1.5 text-xs font-semibold uppercase tracking-widest text-slate-400">Método de pago
                        </p>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button v-for="method in (['cash', 'card', 'yape', 'plin'] as const)" :key="method"
                                type="button" class="rounded-lg border py-2 text-xs font-semibold transition" :class="paymentMethod === method
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-slate-200 text-slate-500 hover:border-blue-200 hover:bg-blue-50/60'"
                                @click="emit('update:paymentMethod', method)">
                                {{ paymentLabels[method] }}
                            </button>
                        </div>
                    </div>

                    <!-- Voucher -->
                    <div>
                        <p class="mb-1.5 text-xs font-semibold uppercase tracking-widest text-slate-400">Comprobante</p>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button v-for="v in (['ticket', 'invoice'] as const)" :key="v" type="button"
                                class="rounded-lg border py-2 text-xs font-semibold transition" :class="voucher === v
                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                    : 'border-slate-200 text-slate-500 hover:border-blue-200 hover:bg-blue-50/60'"
                                @click="emit('update:voucher', v)">
                                {{ v === 'ticket' ? '🧾 Ticket' : '📄 Factura' }}
                            </button>
                        </div>
                    </div>

                    <!-- Document (optional) -->
                    <input :value="document" type="text"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        placeholder="RUC / DNI (opcional)"
                        @input="emit('update:document', ($event.target as HTMLInputElement).value)" />

                    <!-- Total -->
                    <div class="flex items-center justify-between rounded-xl bg-slate-900 px-4 py-3">
                        <p class="text-sm font-semibold text-slate-400">Total</p>
                        <p class="text-xl font-bold text-white">S/ {{ cartTotal.toFixed(2) }}</p>
                    </div>

                    <!-- Feedback messages -->
                    <p v-if="checkoutError" class="rounded-lg bg-rose-50 px-3 py-2 text-xs text-rose-600">⚠️ {{
                        checkoutError }}</p>
                    <p v-if="checkoutMessage" class="rounded-lg bg-blue-50 px-3 py-2 text-xs text-blue-700">✅ {{
                        checkoutMessage }}</p>

                    <!-- Confirm button -->
                    <button type="button"
                        class="w-full rounded-xl bg-blue-600 py-3 text-sm font-bold text-white transition hover:bg-blue-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="checkoutLoading || isEmpty" @click="emit('checkout')">
                        {{ checkoutLoading ? 'Procesando…' : 'Confirmar compra ✓' }}
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity .22s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform .28s cubic-bezier(.4, 0, .2, 1);
}

.slide-enter-from,
.slide-leave-to {
    transform: translateX(100%);
}
</style>

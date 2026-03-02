<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import CartSidebar from '@/components/storefront/CartSidebar.vue';
import CategoriesFilter from '@/components/storefront/CategoriesFilter.vue';
import ProductsGrid from '@/components/storefront/ProductsGrid.vue';
import PromotionsGrid from '@/components/storefront/PromotionsGrid.vue';
import { useStorefront } from '@/composables/useStorefront';
import { dashboard, login, register } from '@/routes';

// ── Props ──────────────────────────────────────────────────────────────────
const props = withDefaults(defineProps<{ canRegister: boolean }>(), { canRegister: true });

// ── Storefront state ───────────────────────────────────────────────────────
const {
    branches, filteredCategories,
    products, promotions,
    productsMeta,
    selectedBranchId, selectedCategoryId,
    search,
    loadingBranches, loadingCatalog, loadingPromotions,
    checkoutLoading, checkoutMessage, checkoutError,
    cartItems, cartTotal, cartCount,
    paymentMethod, voucher, document,
    setBranch, setCategory, setSearch, setPage,
    addToCart, decreaseFromCart, removeFromCart,
    checkout,
} = useStorefront();

// ── Cart modal ─────────────────────────────────────────────────────────────
const cartOpen = ref(false);

// ── Navbar search autocomplete ────────────────────────────────────────────
const navSearchFocused = ref(false);
const navSearchInput = ref<HTMLInputElement | null>(null);

/** Unique product-name suggestions matching current query */
const suggestions = computed<string[]>(() => {
    const q = search.value.trim().toLowerCase();
    if (q.length < 2) return [];
    const seen = new Set<string>();
    const out: string[] = [];
    for (const p of products.value) {
        if (p.name.toLowerCase().includes(q) && !seen.has(p.name)) {
            seen.add(p.name);
            out.push(p.name);
            if (out.length >= 6) break;
        }
    }
    return out;
});

const applySuggestion = (s: string) => {
    setSearch(s);
    navSearchFocused.value = false;
    navSearchInput.value?.blur();
};

// ── Add to cart + open drawer ─────────────────────────────────────────────
const addAndOpen = (p: Parameters<typeof addToCart>[0]) => {
    addToCart(p);
    cartOpen.value = true;
};

// ── Pagination ─────────────────────────────────────────────────────────────
const hideSearchDropdown = () => { window.setTimeout(() => { navSearchFocused.value = false; }, 150); };

const handlePageChange = (page: number) => {
    setPage(page);
    document.getElementById?.('catalog-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};
</script>

<template>

    <Head title="Mini Market">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400..700;1,14..32,400..700&family=Plus+Jakarta+Sans:wght@700;800&display=swap"
            rel="stylesheet" />
    </Head>

    <div class="min-h-screen bg-slate-50 text-slate-900" style="font-family:'Inter',sans-serif;">

        <!-- ════════════════════════════════ NAVBAR ════════════════════════════════ -->
        <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/96 backdrop-blur">
            <div class="mx-auto flex max-w-screen-2xl items-center gap-3 px-4 py-3 sm:px-6">

                <!-- Logo -->
                <a href="/" class="flex shrink-0 items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 shadow-sm">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h12M7 13L5.4 5M10 21a1 1 0 1 0 2 0 1 1 0 0 0-2 0Zm6 0a1 1 0 1 0 2 0 1 1 0 0 0-2 0Z" />
                        </svg>
                    </div>
                    <span class="hidden text-lg font-bold text-slate-900 sm:block"
                        style="font-family:'Plus Jakarta Sans',sans-serif;">
                        MiniMarket
                    </span>
                </a>

                <!-- Search (grows, with dropdown) -->
                <div class="relative flex-1">
                    <div class="flex items-center gap-2 rounded-xl border bg-slate-50 px-3 py-2 transition" :class="navSearchFocused
                        ? 'border-blue-400 bg-white ring-2 ring-blue-100'
                        : 'border-slate-200'">
                        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                        </svg>
                        <input ref="navSearchInput" :value="search" type="text" autocomplete="off"
                            placeholder="Buscar productos, categorías…"
                            class="flex-1 bg-transparent text-sm outline-none placeholder:text-slate-400"
                            @input="setSearch(($event.target as HTMLInputElement).value)"
                            @focus="navSearchFocused = true"
                            @blur="window.setTimeout(() => { navSearchFocused = false }, 150)" />
                        <button v-if="search" type="button" class="shrink-0 text-slate-300 hover:text-slate-500"
                            @click="setSearch('')">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Autocomplete dropdown -->
                    <Transition name="dropdown">
                        <ul v-if="navSearchFocused && suggestions.length > 0"
                            class="absolute left-0 right-0 top-full z-40 mt-1 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                            <li v-for="s in suggestions" :key="s"
                                class="flex cursor-pointer items-center gap-2 px-4 py-2.5 text-sm text-slate-700 transition hover:bg-blue-50 hover:text-blue-700"
                                @mousedown.prevent="applySuggestion(s)">
                                <svg class="h-3.5 w-3.5 shrink-0 text-slate-300" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                                </svg>
                                {{ s }}
                            </li>
                        </ul>
                    </Transition>
                </div>

                <!-- Auth + Cart -->
                <div class="flex shrink-0 items-center gap-2">
                    <Link v-if="$page.props.auth.user" :href="dashboard()"
                        class="hidden rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:block">
                        Dashboard</Link>
                    <template v-else>
                        <Link :href="login()"
                            class="hidden rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 sm:block">
                            Iniciar sesión</Link>
                        <Link v-if="props.canRegister" :href="register()"
                            class="hidden rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 sm:block">
                            Registrarse</Link>
                    </template>

                    <!-- Cart button -->
                    <button type="button"
                        class="relative flex h-10 w-10 items-center justify-center rounded-xl border transition"
                        :class="cartCount > 0
                            ? 'border-blue-400 bg-blue-50 text-blue-600'
                            : 'border-slate-200 text-slate-500 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600'" @click="cartOpen = true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h12M7 13L5.4 5M10 21a1 1 0 1 0 2 0 1 1 0 0 0-2 0Zm6 0a1 1 0 1 0 2 0 1 1 0 0 0-2 0Z" />
                        </svg>
                        <span v-if="cartCount > 0"
                            class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white ring-2 ring-white">{{
                                cartCount > 99 ? '99+' : cartCount }}</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- ════════════════════════════════ HERO ════════════════════════════════ -->
        <div class="relative overflow-hidden bg-gradient-to-br from-blue-700 via-blue-600 to-sky-500">
            <!-- Decorative blobs -->
            <div class="pointer-events-none absolute -right-24 -top-24 h-80 w-80 rounded-full bg-white/5"></div>
            <div class="pointer-events-none absolute -bottom-16 -left-16 h-64 w-64 rounded-full bg-white/5"></div>

            <div class="mx-auto max-w-screen-2xl px-4 py-8 sm:px-6">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <!-- Copy -->
                    <div class="hero-fade text-white">
                        <p class="mb-1 text-xs font-semibold uppercase tracking-[.3em] text-blue-200">minimarket system
                        </p>
                        <h1 class="mb-2 text-3xl font-extrabold leading-tight sm:text-4xl"
                            style="font-family:'Plus Jakarta Sans',sans-serif;">Tu mercado,<br
                                class="hidden sm:block" /> tu
                            sucursal.</h1>
                        <p class="max-w-md text-sm text-blue-100">
                            Explora promociones, filtra por categoría y agrega al carrito. Pago inmediato.
                        </p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="#catalog-section"
                                class="rounded-xl bg-white px-5 py-2 text-sm font-bold text-blue-700 shadow transition hover:bg-blue-50">Ver
                                catálogo ↓</a>
                            <button type="button"
                                class="flex items-center gap-2 rounded-xl border border-white/30 px-5 py-2 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/10"
                                @click="cartOpen = true">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h12M7 13L5.4 5M10 21a1 1 0 1 0 2 0 1 1 0 0 0-2 0Zm6 0a1 1 0 1 0 2 0 1 1 0 0 0-2 0Z" />
                                </svg>
                                Mi carrito
                                <span v-if="cartCount > 0"
                                    class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-xs font-bold text-blue-700">{{
                                        cartCount }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-4 gap-2 sm:grid-cols-4">
                        <div v-for="({ label, value }) in [
                            { label: 'Productos', value: productsMeta.total },
                            { label: 'Categorías', value: filteredCategories.length },
                            { label: 'Combos', value: promotions.length },
                            { label: 'Sucursales', value: branches.length },
                        ]" :key="label" class="rounded-xl bg-white/10 px-3 py-3 text-center backdrop-blur">
                            <p class="text-xl font-bold text-white">{{ value }}</p>
                            <p class="mt-0.5 text-[10px] font-medium text-blue-200">{{ label }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ════════════════════════════════ CONTENT ════════════════════════════════ -->
        <main id="catalog-section" class="mx-auto max-w-screen-2xl space-y-5 px-4 py-6 sm:px-6">

            <!-- 1. Promotions -->
            <PromotionsGrid :promotions="promotions" :loading="loadingPromotions" />

            <!-- 2. Filters (branch + category + search) -->
            <CategoriesFilter :branches="branches" :categories="filteredCategories"
                :selected-branch-id="selectedBranchId" :selected-category-id="selectedCategoryId" :search="search"
                :loading-branches="loadingBranches" @update:branch="setBranch" @update:category="setCategory"
                @update:search="setSearch" />

            <!-- 3. Full catalogue
                 (products with discount show a -N% badge automatically;
                  no separate offers section = no duplicate entries) -->
            <ProductsGrid title="Catálogo de productos" :products="products" :meta="productsMeta"
                :loading="loadingCatalog" empty-text="No se encontraron productos. Prueba con otro filtro o búsqueda."
                @add="addAndOpen" @page-change="handlePageChange" />
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white py-5 text-center text-xs text-slate-400">
            MiniMarket System — {{ new Date().getFullYear() }}
        </footer>

        <!-- ════════════ CART MODAL (single Teleport instance) ════════════ -->
        <CartSidebar :open="cartOpen" :cart-items="cartItems" :cart-total="cartTotal" :cart-count="cartCount"
            :checkout-loading="checkoutLoading" :checkout-message="checkoutMessage" :checkout-error="checkoutError"
            v-model:payment-method="paymentMethod" v-model:voucher="voucher" v-model:document="document"
            @add="addToCart" @decrease="decreaseFromCart" @remove="removeFromCart" @checkout="checkout"
            @close="cartOpen = false" />

        <!-- FAB — only visible when cart has items -->
        <Transition name="fab">
            <button v-if="cartCount > 0" type="button"
                class="fixed bottom-6 right-6 z-40 flex items-center gap-2 rounded-full bg-blue-600 px-4 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-blue-700 active:scale-95"
                @click="cartOpen = true">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h12M7 13L5.4 5M10 21a1 1 0 1 0 2 0 1 1 0 0 0-2 0Zm6 0a1 1 0 1 0 2 0 1 1 0 0 0-2 0Z" />
                </svg>
                Ver carrito
                <span
                    class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-xs font-bold text-blue-700">
                    {{ cartCount }}
                </span>
            </button>
        </Transition>
    </div>
</template>

<style scoped>
* {
    font-family: 'Inter', sans-serif;
}

.hero-fade {
    animation: fadeUp .45s ease both;
}

@keyframes fadeUp {
    from {
        opacity: 0;
        transform: translateY(12px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-enter-active,
.dropdown-leave-active {
    transition: opacity .15s ease, transform .15s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

.fab-enter-active,
.fab-leave-active {
    transition: transform .2s ease, opacity .2s ease;
}

.fab-enter-from,
.fab-leave-to {
    transform: scale(.75) translateY(6px);
    opacity: 0;
}
</style>

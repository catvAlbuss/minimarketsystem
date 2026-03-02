<script setup lang="ts">
// ── Types ──────────────────────────────────────────────────────────────────
type ProductsMeta = {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
};
type ProductCategory = { id: number; name: string };
type ProductBranch = { id: number; name: string };
export type Product = {
    id: number;
    code: string;
    name: string;
    description: string;
    unit_price: number;
    promotion_discount: number;
    final_price: number;
    stock: number;
    image: string | null;
    category: ProductCategory | null;
    branch: ProductBranch | null;
};

// ── Props / Emits ──────────────────────────────────────────────────────────
const props = defineProps<{
    title: string;
    products: Product[];
    loading?: boolean;
    meta?: ProductsMeta | null;
    emptyText?: string;
}>();

const emit = defineEmits<{
    add: [product: Product];
    'page-change': [page: number];
}>();

// ── Helpers ────────────────────────────────────────────────────────────────
const fmt = (n: number) => Number(n).toFixed(2);

/** Smart pagination: 1 … 4 5 6 … 12 */
const pages = (): (number | '…')[] => {
    if (!props.meta) return [];
    const { last_page: total, current_page: cur } = props.meta;
    const out: (number | '…')[] = [];
    for (let i = 1; i <= total; i++) {
        if (i === 1 || i === total || (i >= cur - 1 && i <= cur + 1)) {
            out.push(i);
        } else if (out[out.length - 1] !== '…') {
            out.push('…');
        }
    }
    return out;
};

const imageError = (e: Event) => {
    (e.target as HTMLImageElement).src =
        'https://placehold.co/300x300/e0f2fe/0369a1?text=Sin+imagen';
};
</script>

<template>
    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
        <!-- ── Section header ── -->
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <h2 class="text-base font-bold text-slate-900">{{ title }}</h2>
            <span v-if="loading" class="text-xs text-slate-400">Cargando…</span>
            <span v-else-if="meta" class="text-xs font-medium text-slate-400">
                {{ meta.total.toLocaleString() }} producto(s)
            </span>
        </div>

        <div class="p-5">
            <!-- ── Skeleton ── -->
            <div v-if="loading" class="grid gap-3 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5">
                <div v-for="n in 10" :key="n" class="animate-pulse overflow-hidden rounded-xl border border-slate-100">
                    <div class="h-36 bg-slate-100"></div>
                    <div class="space-y-2 p-3">
                        <div class="h-3 w-3/4 rounded bg-slate-100"></div>
                        <div class="h-3 w-1/2 rounded bg-slate-100"></div>
                        <div class="h-7 w-full rounded bg-slate-100"></div>
                    </div>
                </div>
            </div>

            <!-- ── Empty state ── -->
            <div v-else-if="products.length === 0" class="flex flex-col items-center gap-3 py-14 text-center">
                <span class="text-4xl">📦</span>
                <p class="text-sm text-slate-500">{{ emptyText ?? 'No hay productos disponibles.' }}</p>
            </div>

            <!-- ── Product grid: 2 → 3 → 4 → 5 columns ── -->
            <div v-else class="grid gap-3 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5">
                <article v-for="product in products" :key="product.id"
                    class="group relative flex flex-col overflow-hidden rounded-xl border border-slate-100 bg-white transition hover:-translate-y-0.5 hover:shadow-md hover:border-blue-200">
                    <!-- ── Image wrapper ── -->
                    <div class="relative aspect-square overflow-hidden bg-slate-50">
                        <img :src="product.image ?? 'https://placehold.co/300x300/e0f2fe/0369a1?text=Producto'"
                            :alt="product.name"
                            class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                            @error="imageError" />

                        <!-- Discount badge (top-left) -->
                        <span v-if="product.promotion_discount > 0"
                            class="absolute left-2 top-2 flex items-center gap-0.5 rounded-full bg-red-500 px-2 py-0.5 text-xs font-extrabold text-white shadow">
                            -{{ product.promotion_discount }}%
                        </span>

                        <!-- Low-stock badge (top-right) -->
                        <span v-if="product.stock > 0 && product.stock <= 5"
                            class="absolute right-2 top-2 rounded-full bg-amber-400 px-2 py-0.5 text-xs font-bold text-white shadow">
                            Últimas {{ product.stock }}
                        </span>
                    </div>

                    <!-- ── Card body ── -->
                    <div class="flex flex-1 flex-col p-3">
                        <!-- Category label -->
                        <p class="mb-0.5 truncate text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                            {{ product.category?.name ?? '—' }}
                        </p>

                        <!-- Product name -->
                        <h3 class="mb-2 line-clamp-2 flex-1 text-xs font-semibold leading-snug text-slate-800">
                            {{ product.name }}
                        </h3>

                        <!-- Price block -->
                        <div class="mb-2">
                            <p v-if="product.promotion_discount > 0" class="text-[10px] text-slate-400 line-through">S/
                                {{ fmt(product.unit_price) }}</p>
                            <p class="text-base font-extrabold text-blue-700 leading-none">
                                S/ {{ fmt(product.final_price) }}
                            </p>
                        </div>

                        <!-- Add to cart button -->
                        <button type="button"
                            class="w-full rounded-lg bg-blue-600 py-1.5 text-xs font-bold text-white transition hover:bg-blue-700 active:scale-95"
                            @click="emit('add', product)">
                            + Agregar
                        </button>
                    </div>
                </article>
            </div>

            <!-- ── Pagination ── -->
            <div v-if="!loading && meta && meta.last_page > 1" class="mt-6 flex items-center justify-center gap-1">
                <button type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-sm text-slate-500 transition hover:bg-blue-50 hover:text-blue-600 disabled:opacity-40"
                    :disabled="meta.current_page <= 1" @click="emit('page-change', meta!.current_page - 1)">‹</button>

                <template v-for="(p, i) in pages()" :key="i">
                    <span v-if="p === '…'" class="px-1 text-slate-400 text-sm">…</span>
                    <button v-else type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-lg border text-sm font-medium transition"
                        :class="p === meta.current_page
                            ? 'border-blue-500 bg-blue-600 text-white'
                            : 'border-slate-200 text-slate-600 hover:bg-blue-50 hover:text-blue-600'"
                        @click="emit('page-change', p as number)">{{ p }}</button>
                </template>

                <button type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-sm text-slate-500 transition hover:bg-blue-50 hover:text-blue-600 disabled:opacity-40"
                    :disabled="meta.current_page >= meta.last_page"
                    @click="emit('page-change', meta!.current_page + 1)">›</button>
            </div>
        </div>
    </section>
</template>

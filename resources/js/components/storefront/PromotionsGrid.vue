<script setup lang="ts">
import { ref } from 'vue';

// ── Types ──────────────────────────────────────────────────────────────────
type PromotionItem = { id: number; name: string; image: string | null };
export type Promotion = {
    name_promotion: string;
    price: number;
    image: string | null;
    items_count: number;
    items: PromotionItem[];
};

defineProps<{
    promotions: Promotion[];
    loading?: boolean;
}>();

// ── State ──────────────────────────────────────────────────────────────────
const expanded = ref<Record<string, boolean>>({});
const toggle = (name: string) => { expanded.value[name] = !expanded.value[name]; };

const imageError = (e: Event) => {
    (e.target as HTMLImageElement).src =
        'https://placehold.co/400x200/dbeafe/1d4ed8?text=Promo';
};
</script>

<template>
    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
        <!-- ── Header ── -->
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">🏷️ Promociones</h2>
                <p class="text-xs text-slate-400">Combos y paquetes especiales</p>
            </div>
            <span v-if="loading" class="text-xs text-slate-400">Cargando…</span>
            <span v-else class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-600">
                {{ promotions.length }} combo(s)
            </span>
        </div>

        <div class="p-5">
            <!-- ── Skeleton ── -->
            <div v-if="loading" class="grid gap-3 grid-cols-2 lg:grid-cols-4">
                <div v-for="n in 4" :key="n" class="animate-pulse overflow-hidden rounded-xl border border-slate-100">
                    <div class="h-28 bg-slate-100"></div>
                    <div class="space-y-2 p-3">
                        <div class="h-3 w-2/3 rounded bg-slate-100"></div>
                        <div class="h-3 w-1/3 rounded bg-slate-100"></div>
                    </div>
                </div>
            </div>

            <!-- ── Empty ── -->
            <div v-else-if="promotions.length === 0" class="flex flex-col items-center gap-2 py-10 text-center">
                <span class="text-3xl">🏷️</span>
                <p class="text-sm text-slate-500">Sin promociones activas por el momento.</p>
            </div>

            <!-- ── 2 → 4 column grid ── -->
            <div v-else class="grid gap-3 grid-cols-2 lg:grid-cols-4">
                <article v-for="promo in promotions" :key="promo.name_promotion"
                    class="flex flex-col overflow-hidden rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50 to-sky-50 transition hover:shadow-md">
                    <!-- Promo image -->
                    <div class="relative h-28 overflow-hidden bg-blue-100">
                        <img v-if="promo.image" :src="promo.image" :alt="promo.name_promotion"
                            class="h-full w-full object-cover" @error="imageError" />
                        <div v-else class="flex h-full items-center justify-center">
                            <span class="text-4xl opacity-20">🏷️</span>
                        </div>
                        <span
                            class="absolute left-2 top-2 rounded-full bg-blue-700 px-2 py-0.5 text-[10px] font-bold text-white">
                            COMBO
                        </span>
                    </div>

                    <!-- Body -->
                    <div class="flex flex-1 flex-col p-3">
                        <h3 class="mb-1 line-clamp-1 text-sm font-bold text-slate-900">
                            {{ promo.name_promotion }}
                        </h3>

                        <!-- Items (collapsed) -->
                        <div v-if="!expanded[promo.name_promotion]" class="mb-2 flex flex-wrap gap-1">
                            <span v-for="item in promo.items.slice(0, 2)" :key="item.id"
                                class="rounded-full bg-white/80 px-2 py-0.5 text-[10px] text-slate-600 ring-1 ring-blue-100">{{
                                item.name }}</span>
                            <span v-if="promo.items.length > 2"
                                class="rounded-full bg-white/80 px-2 py-0.5 text-[10px] text-slate-400 ring-1 ring-blue-100">+{{
                                promo.items.length - 2 }} más</span>
                        </div>

                        <!-- Items (expanded) -->
                        <div v-else class="mb-2 space-y-1 rounded-lg bg-white/70 p-2 ring-1 ring-blue-100">
                            <p class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">Incluye
                            </p>
                            <p v-for="item in promo.items" :key="item.id"
                                class="flex items-center gap-1.5 text-xs text-slate-700">
                                <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-blue-400"></span>
                                {{ item.name }}
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="mt-auto flex items-center justify-between gap-1">
                            <p class="text-base font-extrabold text-blue-700">
                                S/ {{ Number(promo.price).toFixed(2) }}
                            </p>
                            <button type="button"
                                class="rounded-lg border border-blue-200 bg-white px-2 py-1 text-[10px] font-semibold text-blue-600 transition hover:bg-blue-600 hover:text-white active:scale-95"
                                @click="toggle(promo.name_promotion)">
                                {{ expanded[promo.name_promotion] ? '▲ Cerrar' : `▼ Ver (${promo.items_count})` }}
                            </button>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>

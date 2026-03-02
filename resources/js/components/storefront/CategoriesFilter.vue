<script setup lang="ts">
// ── Types ──────────────────────────────────────────────────────────────────
type Branch = { id: number; name: string };
type Category = { id: number; name: string; description: string; branch: Branch | null };

// ── Props / Emits ──────────────────────────────────────────────────────────
const props = defineProps<{
    branches: Branch[];
    categories: Category[];
    selectedBranchId: number | null;
    selectedCategoryId: number | null;
    search: string;
    loadingBranches?: boolean;
}>();

const emit = defineEmits<{
    'update:branch': [id: number | null];
    'update:category': [id: number | null];
    'update:search': [q: string];
}>();
</script>

<template>
    <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
        <div class="border-b border-slate-100 px-5 py-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Filtros</h2>
                <span class="text-xs font-medium uppercase tracking-widest text-slate-400">catálogo</span>
            </div>
        </div>

        <div class="space-y-5 p-5">

            <!-- ── Search bar ──────────────────────────────────────────── -->
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-slate-400">Búsqueda</p>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                        </svg>
                    </span>
                    <input :value="props.search" type="text" autocomplete="off"
                        placeholder="Nombre, código o descripción…"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-9 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100"
                        @input="emit('update:search', ($event.target as HTMLInputElement).value)" />
                    <button v-if="props.search" type="button"
                        class="absolute inset-y-0 right-3 flex items-center text-slate-300 hover:text-slate-500"
                        @click="emit('update:search', '')">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- ── Branch chips ────────────────────────────────────────── -->
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-slate-400">Sucursales</p>

                <div v-if="props.loadingBranches" class="flex items-center gap-2 text-xs text-slate-400">
                    <span
                        class="inline-block h-3 w-3 animate-spin rounded-full border-2 border-blue-400 border-t-transparent"></span>
                    Cargando…
                </div>

                <div v-else class="flex flex-wrap gap-2">
                    <button type="button" class="rounded-full px-4 py-1.5 text-sm font-medium transition" :class="props.selectedBranchId === null
                        ? 'bg-blue-600 text-white shadow-sm'
                        : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-600'"
                        @click="emit('update:branch', null)">Todas</button>

                    <button v-for="branch in props.branches" :key="branch.id" type="button"
                        class="rounded-full px-4 py-1.5 text-sm font-medium transition" :class="props.selectedBranchId === branch.id
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'bg-slate-100 text-slate-600 hover:bg-blue-50 hover:text-blue-600'"
                        @click="emit('update:branch', branch.id)">{{ branch.name }}</button>
                </div>
            </div>

            <!-- ── Category chips ──────────────────────────────────────── -->
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-slate-400">Categorías</p>

                <p v-if="props.categories.length === 0 && props.selectedBranchId !== null"
                    class="text-xs text-slate-400">Sin categorías para esta sucursal.</p>

                <div v-else class="flex flex-wrap gap-2">
                    <button type="button" class="rounded-full px-4 py-1.5 text-sm font-medium transition" :class="props.selectedCategoryId === null
                        ? 'bg-sky-500 text-white shadow-sm'
                        : 'bg-slate-100 text-slate-600 hover:bg-sky-50 hover:text-sky-600'"
                        @click="emit('update:category', null)">Todas</button>

                    <button v-for="cat in props.categories" :key="cat.id" type="button"
                        class="rounded-full px-4 py-1.5 text-sm font-medium transition" :class="props.selectedCategoryId === cat.id
                            ? 'bg-sky-500 text-white shadow-sm'
                            : 'bg-slate-100 text-slate-600 hover:bg-sky-50 hover:text-sky-600'"
                        @click="emit('update:category', cat.id)">{{ cat.name }}</button>
                </div>
            </div>

        </div>
    </section>
</template>

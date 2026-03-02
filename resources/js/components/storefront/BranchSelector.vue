<script setup lang="ts">
type Branch = {
    id: number;
    name: string;
    address: string;
    opening_time: string;
    closing_time: string;
};

defineProps<{
    branches: Branch[];
    selectedBranchId: number | null;
    loading: boolean;
}>();

const emit = defineEmits<{
    'update:branch': [id: number | null];
}>();
</script>

<template>
    <section class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Elige tu sucursal</h2>
                <p class="text-xs text-slate-400">Filtra el catálogo por tienda</p>
            </div>
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-lg">🏪</span>
        </div>

        <div v-if="loading" class="flex items-center gap-2 text-sm text-slate-400">
            <span
                class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-blue-400 border-t-transparent"></span>
            Cargando sucursales...
        </div>

        <div v-else class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
            <!-- All branches button -->
            <button type="button"
                class="group flex flex-col items-center justify-center gap-1 rounded-xl border-2 py-3 text-sm font-semibold transition-all"
                :class="selectedBranchId === null
                    ? 'border-blue-500 bg-blue-50 text-blue-700 shadow-sm'
                    : 'border-slate-200 text-slate-500 hover:border-blue-300 hover:bg-blue-50/50 hover:text-blue-600'"
                @click="emit('update:branch', null)">
                <span class="text-lg">🌐</span>
                <span>Todas</span>
            </button>

            <!-- Per branch -->
            <button v-for="branch in branches" :key="branch.id" type="button"
                class="group flex flex-col rounded-xl border-2 p-3 text-left transition-all" :class="selectedBranchId === branch.id
                    ? 'border-blue-500 bg-blue-50 shadow-sm'
                    : 'border-slate-200 hover:border-blue-300 hover:bg-blue-50/50'"
                @click="emit('update:branch', branch.id)">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-semibold transition"
                        :class="selectedBranchId === branch.id ? 'text-blue-700' : 'text-slate-800 group-hover:text-blue-700'">
                        {{ branch.name }}
                    </p>
                    <span v-if="selectedBranchId === branch.id"
                        class="mt-1 h-2 w-2 flex-shrink-0 rounded-full bg-blue-500"></span>
                </div>
                <p class="mt-1 text-xs text-slate-400 line-clamp-1">📍 {{ branch.address }}</p>
                <p class="mt-0.5 text-xs text-slate-300">
                    🕐 {{ branch.opening_time?.slice(0, 5) }} – {{ branch.closing_time?.slice(0, 5) }}
                </p>
            </button>
        </div>
    </section>
</template>

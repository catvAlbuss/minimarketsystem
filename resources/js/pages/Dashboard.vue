<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

// ── Types ──────────────────────────────────────────────────────────────────
type Branch = {
    id: number; name: string; address: string; state: string;
    opening_time: string; closing_time: string;
    categories_count: number; users_count: number;
};
type Stats = {
    today_sales: number; today_revenue: number;
    total_revenue: number; low_stock: number;
    total_products: number; with_discount: number;
};

// ── Props ──────────────────────────────────────────────────────────────────
const props = defineProps<{
    stats: Stats;
    branches: Branch[];
    userBranch: { id: number; name: string } | null;
    isGlobal: boolean;
    roleLabel: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: dashboard().url }];

const activeBranches = computed(() => props.branches.filter(b => b.state === 'active'));

// ── Quick-access modules (shown as link cards) ─────────────────────────────
type QuickLink = { label: string; desc: string; href: string; icon: string; color: string };
const quickLinks: QuickLink[] = [
    { label: 'Productos', desc: 'Gestión de inventario', href: '/products', icon: '📦', color: 'blue' },
    { label: 'Categorías', desc: 'Organización por área', href: '/categories', icon: '🗂️', color: 'sky' },
    { label: 'Ventas', desc: 'Registro y seguimiento', href: '/sales', icon: '🛒', color: 'violet' },
    { label: 'Clientes', desc: 'Base de clientes', href: '/customers', icon: '👥', color: 'emerald' },
    { label: 'Compras', desc: 'Gestión de compras', href: '/buys', icon: '🛍️', color: 'amber' },
    { label: 'Proveedores', desc: 'Directorio de proveedores', href: '/providers', icon: '🏭', color: 'rose' },
];

const globalLinks: QuickLink[] = [
    { label: 'Sucursales', desc: 'Red de sucursales', href: '/branches', icon: '🏪', color: 'blue' },
    { label: 'Usuarios', desc: 'Gestión de accesos', href: '/users', icon: '🔑', color: 'slate' },
    { label: 'Promociones', desc: 'Combos y descuentos', href: '/promotions', icon: '🏷️', color: 'sky' },
];

const colorMap: Record<string, string> = {
    blue: 'bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400',
    sky: 'bg-sky-50 text-sky-600 dark:bg-sky-950 dark:text-sky-400',
    violet: 'bg-violet-50 text-violet-600 dark:bg-violet-950 dark:text-violet-400',
    emerald: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400',
    amber: 'bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400',
    rose: 'bg-rose-50 text-rose-600 dark:bg-rose-950 dark:text-rose-400',
    slate: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
};
</script>

<template>

    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">

            <!-- ── Page header ─────────────────────────────────────────── -->
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                        {{ isGlobal ? 'Panel General' : (userBranch?.name ?? 'Mi Sucursal') }}
                    </h1>
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                        {{ isGlobal ? 'Vista global · todas las sucursales' : 'Datos de tu sucursal asignada' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold capitalize"
                        :class="isGlobal
                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                            : 'bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300'">
                        <span class="h-1.5 w-1.5 rounded-full" :class="isGlobal ? 'bg-blue-500' : 'bg-sky-500'"></span>
                        {{ isGlobal ? '🌐 Global' : '📍 ' + (userBranch?.name ?? 'Sucursal') }}
                    </span>
                    <span
                        class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                        {{ roleLabel }}
                    </span>
                </div>
            </div>

            <!-- ── KPI cards ───────────────────────────────────────────── -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <!-- Today Sales -->
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-950">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.5 6h12M7 13L5.4 5" />
                        </svg>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ventas hoy</p>
                    <p class="mt-0.5 text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ stats.today_sales }}
                    </p>
                </div>

                <!-- Today Revenue -->
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 dark:bg-sky-950">
                        <svg class="h-5 w-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ingresos hoy</p>
                    <p class="mt-0.5 text-xl font-extrabold text-slate-900 dark:text-slate-100">S/ {{
                        stats.today_revenue.toFixed(2) }}</p>
                </div>

                <!-- Total Revenue -->
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div
                        class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950">
                        <svg class="h-5 w-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Ingresos totales</p>
                    <p class="mt-0.5 text-xl font-extrabold text-slate-900 dark:text-slate-100">S/ {{
                        stats.total_revenue.toFixed(2) }}</p>
                </div>

                <!-- Total Products -->
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-950">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Productos activos</p>
                    <p class="mt-0.5 text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ stats.total_products
                        }}</p>
                </div>

                <!-- Low stock alert -->
                <div class="rounded-2xl border p-4 shadow-sm" :class="stats.low_stock > 0
                    ? 'border-rose-200 bg-rose-50 dark:border-rose-900 dark:bg-rose-950/50'
                    : 'border-slate-100 bg-white dark:border-slate-800 dark:bg-slate-900'">
                    <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 dark:bg-rose-950">
                        <svg class="h-5 w-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <p class="text-xs"
                        :class="stats.low_stock > 0 ? 'text-rose-500' : 'text-slate-500 dark:text-slate-400'">Stock bajo
                        (≤5)</p>
                    <p class="mt-0.5 text-2xl font-extrabold"
                        :class="stats.low_stock > 0 ? 'text-rose-700 dark:text-rose-400' : 'text-slate-900 dark:text-slate-100'">
                        {{ stats.low_stock }}
                    </p>
                </div>

                <!-- With discount -->
                <div
                    class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-950">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">En descuento</p>
                    <p class="mt-0.5 text-2xl font-extrabold text-slate-900 dark:text-slate-100">{{ stats.with_discount
                        }}</p>
                </div>
            </div>

            <!-- ── Quick access modules ─────────────────────────────────── -->
            <div>
                <h2 class="mb-3 text-sm font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                    Accesos rápidos</h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                    <Link v-for="m in [...quickLinks, ...(isGlobal ? globalLinks : [])]" :key="m.label" :href="m.href"
                        class="flex flex-col rounded-2xl border border-slate-100 bg-white p-4 transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl text-xl"
                            :class="colorMap[m.color]">
                            {{ m.icon }}
                        </div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ m.label }}</p>
                        <p class="mt-0.5 text-xs text-slate-400 dark:text-slate-500">{{ m.desc }}</p>
                    </Link>
                </div>
            </div>

            <!-- ── Branches table (global only) ───────────────────────── -->
            <div v-if="isGlobal && branches.length > 0"
                class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div
                    class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800">
                    <div>
                        <h2 class="font-semibold text-slate-900 dark:text-slate-100">Sucursales</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400">{{ activeBranches.length }} activa(s)</p>
                    </div>
                    <span
                        class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600 dark:bg-blue-950 dark:text-blue-300">
                        {{ branches.length }} total
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <th
                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    Sucursal</th>
                                <th
                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    Dirección</th>
                                <th
                                    class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    Horario</th>
                                <th
                                    class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    Categorías</th>
                                <th
                                    class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    Personal</th>
                                <th
                                    class="px-5 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="branch in branches" :key="branch.id"
                                class="border-b border-slate-50 transition hover:bg-slate-50 dark:border-slate-800/60 dark:hover:bg-slate-800/50">
                                <td class="px-5 py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ branch.name
                                    }}</td>
                                <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400">{{ branch.address }}</td>
                                <td class="px-5 py-3.5 text-center text-slate-500 dark:text-slate-400">
                                    {{ branch.opening_time?.slice(0, 5) }} – {{ branch.closing_time?.slice(0, 5) }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span
                                        class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                        {{ branch.categories_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span
                                        class="rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-semibold text-sky-700 dark:bg-sky-950 dark:text-sky-300">
                                        {{ branch.users_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="branch.state === 'active'
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'">{{
                                                branch.state === 'active' ? '● Activa' : '○ Inactiva' }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── Branch card (scoped user) ──────────────────────────── -->
            <div v-else-if="!isGlobal && userBranch"
                class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-2xl dark:bg-blue-950">
                    🏪</div>
                <div>
                    <p class="text-base font-bold text-slate-900 dark:text-slate-100">{{ userBranch.name }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Tu sucursal asignada</p>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

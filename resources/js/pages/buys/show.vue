<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import buyDetailsRoutes from '@/routes/buy_details';
import { type BreadcrumbItem } from '@/types';
import type { BuyDetail } from '@/types/buys';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Detalle de compras',
        href: buyDetailsRoutes.index.url(),
    },
];

const props = defineProps<{
    buy_details: BuyDetail[];
}>();

const search = ref('');

const buyDetails = computed(() => props.buy_details ?? []);

const filteredDetails = computed(() => {
    const term = search.value.trim().toLowerCase();
    if (!term) return buyDetails.value;

    return buyDetails.value.filter((detail) => {
        const voucher = detail.buy?.voucher_number?.toLowerCase() ?? '';
        const provider = detail.buy?.provider?.company_name?.toLowerCase() ?? '';
        const product = detail.product?.name?.toLowerCase() ?? '';
        const code = detail.product?.code?.toLowerCase() ?? '';
        return voucher.includes(term) || provider.includes(term) || product.includes(term) || code.includes(term);
    });
});

const totalItems = computed(() => filteredDetails.value.reduce((sum, detail) => sum + Number(detail.quantity ?? 0), 0));
const totalAmount = computed(() => filteredDetails.value.reduce((sum, detail) => sum + Number(detail.sub_total ?? 0), 0));

const formatCurrency = (value: number): string =>
    new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(value || 0);

const formatDate = (value?: string): string => {
    if (!value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleDateString('es-PE');
};
</script>

<template>
    <Head title="Reporte de detalle de compras" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <main class="min-h-screen bg-slate-50 p-4 text-slate-900 transition-colors md:p-6 dark:bg-slate-950 dark:text-slate-100">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Reporte de detalle de compras</h1>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Vista solo lectura para seguimiento de compras y productos solicitados.
                        </p>
                    </div>

                    <div class="grid w-full max-w-xl gap-3 md:grid-cols-3">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Registros</p>
                            <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ filteredDetails.length }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Unidades</p>
                            <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ totalItems }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800">
                            <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Subtotal acumulado</p>
                            <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency(totalAmount) }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por comprobante, proveedor o producto..."
                        class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none transition focus:border-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-slate-500"
                    />
                </div>
            </section>

            <section class="mt-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px] text-sm">
                        <thead class="border-b border-slate-200 text-left dark:border-slate-700">
                            <tr class="text-slate-700 dark:text-slate-300">
                                <th class="px-2 py-3">Comprobante</th>
                                <th class="px-2 py-3">Fecha</th>
                                <th class="px-2 py-3">Proveedor</th>
                                <th class="px-2 py-3">Producto</th>
                                <th class="px-2 py-3">Cantidad</th>
                                <th class="px-2 py-3">P. Unitario</th>
                                <th class="px-2 py-3">Subtotal</th>
                                <th class="px-2 py-3">Estado</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-if="filteredDetails.length === 0">
                                <td colspan="8" class="px-2 py-5 text-center text-slate-500 dark:text-slate-400">
                                    No hay datos para mostrar.
                                </td>
                            </tr>

                            <tr v-for="detail in filteredDetails" :key="detail.id" class="border-b border-slate-100 dark:border-slate-800">
                                <td class="px-2 py-3 font-medium text-slate-900 dark:text-slate-100">
                                    {{ detail.buy?.voucher_number ?? `#${detail.id_buys}` }}
                                </td>
                                <td class="px-2 py-3 text-slate-700 dark:text-slate-300">
                                    {{ formatDate(detail.buy?.date_time || detail.buy?.created_at) }}
                                </td>
                                <td class="px-2 py-3 text-slate-700 dark:text-slate-300">
                                    {{ detail.buy?.provider?.company_name ?? '-' }}
                                </td>
                                <td class="px-2 py-3 text-slate-700 dark:text-slate-300">
                                    {{ detail.product?.code ? `${detail.product.code} - ` : '' }}{{ detail.product?.name ?? `#${detail.id_products}` }}
                                </td>
                                <td class="px-2 py-3 text-slate-700 dark:text-slate-300">{{ detail.quantity }}</td>
                                <td class="px-2 py-3 text-slate-700 dark:text-slate-300">{{ formatCurrency(detail.unit_price) }}</td>
                                <td class="px-2 py-3 font-semibold text-slate-900 dark:text-slate-100">{{ formatCurrency(detail.sub_total) }}</td>
                                <td class="px-2 py-3">
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                        :class="
                                            detail.buy?.payment_status === 'delivered'
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300'
                                                : detail.buy?.payment_status === 'pending'
                                                  ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300'
                                                  : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
                                        "
                                    >
                                        {{ detail.buy?.payment_status ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </AppLayout>
</template>

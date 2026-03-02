<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import SaleController from '@/actions/App/Http/Controllers/SaleController';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Venta',
        href: './sales',
    },
];

type Customer = {
    id: number;
    dni: string;
    name: string;
    last_name: string;
    birthday: string;
    email: string;
    phone: string;
    address: string;
    score: number;
    state: string;
};

type User = {
    id: number,
    name: string,
    lastname: string,
    dni: number,
    phone: number,
    address: string,
    email: string,
    children: number,
    affiliate: string,
    insured: string,
    work_modality: string,
    entry_date: string,
    retention: string,
    entry_to_payroll: string,
    role: string,
    state: string,
};

type Sale = {
    id: number,
    id_customers: number,
    id_users: number,
    voucher_number: string,
    igv: number,
    total: number,
    payment_method: 'cash' | 'card' | 'yape' | 'plin',
    voucher: 'ticket' | 'invoice',
    document: string,
    date_time: string,
};

type Props = {
    sales: Sale[];
    customers: Customer[];
    users: User[]
};

const props = defineProps<Props>();
const sales = computed(() => props.sales);
const customers = computed(() => props.customers);
const users = computed(() => props.users);

const editingId = ref<number | null>(null);

const form = useForm({
    id_customers: props.customers?.[0]?.id ?? '',
    id_users: props.users?.[0]?.id ?? '',
    voucher_number: '',
    igv: 0.18,
    total: 0,
    payment_method: '',
    voucher: '',
    document: '',
    date_time: '',
});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);

const isEditing = computed(() => editingId.value !== null);

const resetForm = (): void => {
    editingId.value=null;
    form.reset();
    form.clearErrors();
    form.id_customers = props.customers?.[0]?.id ?? '';
    form.id_users = props.users?.[0]?.id ?? '';
};

const startEdit = (sales: Sale): void => {
    editingId.value = sales.id;
    form.clearErrors();
    form.id_customers = sales.id_customers ?? props.customers?.[0]?.id ?? '';
    form.id_users = sales.id_users ?? props.users?.[0]?.id ?? '';
    form.voucher_number = sales.voucher_number;
    form.igv = sales.igv;
    form.total = sales.total;
    form.payment_method = sales.payment_method;
    form.voucher = sales.voucher;
    form.document = sales.document;
    form.date_time = sales.date_time;
};

const submit = (): void => {
    const options = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };
    if (isEditing.value && editingId.value !== null) {
        form.put(SaleController.update.url(editingId.value), options);
        return;
    }
    form.post(SaleController.store.url(), options);
};

const remove = (sales: Sale): void => {
    if (!confirm(`Eliminar venta "${sales.voucher_number}"?`)) {
        return;
    }

    deleteForm.delete(SaleController.destroy.url(sales.id), {
        preserveScroll: true,
    });
};

// KPIs computados
const totalVentas = computed(() => sales.value.length);
const totalIngresos = computed(() => 
    sales.value.reduce((sum, sale) => sum + Number(sale.total), 0).toFixed(2)
);
const metodoMasUsado = computed(() => {
    const counts: Record<string, number> = {};
    sales.value.forEach(s => {
        counts[s.payment_method] = (counts[s.payment_method] || 0) + 1;
    });
    const entries = Object.entries(counts);
    if (entries.length === 0) return 'N/A';
    return entries.reduce((a, b) => a[1] > b[1] ? a : b)[0];
});
</script>

<template>
    <Head title="Ventas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        
        <!-- Main Content -->
        <main class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
            
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestión de Ventas</h1>
                </div>
            </div>

            <!-- KPIs Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Ventas -->
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Ventas</p>
                            <h3 class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ totalVentas }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Ingresos -->
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ingresos Totales</p>
                            <h3 class="mt-1 text-3xl font-bold text-green-600 dark:text-green-400">S/ {{ totalIngresos }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-50 dark:bg-green-900/30">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Método más usado -->
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Método más usado</p>
                            <h3 class="mt-1 text-3xl font-bold text-purple-600 dark:text-purple-400 capitalize">{{ metodoMasUsado }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50 dark:bg-purple-900/30">
                            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Ticket promedio -->
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Ticket Promedio</p>
                            <h3 class="mt-1 text-3xl font-bold text-orange-600 dark:text-orange-400">
                                S/ {{ totalVentas ? (Number(totalIngresos) / totalVentas).toFixed(2) : '0.00' }}
                            </h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-50 dark:bg-orange-900/30">
                            <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Venta -->
            <section class="mb-6 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ isEditing ? 'Editar venta' : 'Nueva venta' }}
                        </h2>
                    </div>
                </div>

                <form class="grid gap-5 md:grid-cols-2 lg:grid-cols-3" @submit.prevent="submit">
                    
                    <!-- Cliente -->
                    <div class="space-y-2">
                        <Label for="id_customers" class="text-sm font-medium text-gray-700 dark:text-gray-300">Cliente</Label>
                        <select 
                            id="id_customers" 
                            v-model="form.id_customers" 
                            required
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-white transition-all focus:border-blue-500 dark:focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20"
                        >
                            <option value="" disabled>Seleccione</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} {{ c.last_name }}</option>
                        </select>
                        <InputError :message="form.errors.id_customers" />
                    </div>

                    <!-- Usuario -->
                    <div class="space-y-2">
                        <Label for="id_users" class="text-sm font-medium text-gray-700 dark:text-gray-300">Usuario</Label>
                        <select 
                            id="id_users" 
                            v-model="form.id_users" 
                            required
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-white transition-all focus:border-blue-500 dark:focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20"
                        >
                            <option value="" disabled>Seleccione</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} {{ u.lastname }}</option>
                        </select>
                        <InputError :message="form.errors.id_users" />
                    </div>

                    <!-- Número de Comprobante -->
                    <div class="space-y-2">
                        <Label for="voucher_number" class="text-sm font-medium text-gray-700 dark:text-gray-300">N° Comprobante</Label>
                        <input 
                            id="voucher_number" 
                            v-model="form.voucher_number" 
                            type="text" 
                            placeholder="Ej: F001-000123"
                            required
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all focus:border-blue-500 dark:focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20"
                        />
                        <InputError :message="form.errors.voucher_number" />
                    </div>

                    <!-- IGV -->
                    <div class="space-y-2">
                        <Label for="igv" class="text-sm font-medium text-gray-700 dark:text-gray-300">IGV</Label>
                        <input 
                            id="igv" 
                            v-model="form.igv" 
                            type="number" 
                            step="0.01"
                            placeholder="0.18"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all focus:border-blue-500 dark:focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20"
                        />
                        <InputError :message="form.errors.igv" />
                    </div>

                    <!-- Total -->
                    <div class="space-y-2">
                        <Label for="total" class="text-sm font-medium text-gray-700 dark:text-gray-300">Total</Label>
                        <input 
                            id="total" 
                            v-model="form.total" 
                            type="number" 
                            step="0.01"
                            placeholder="0.00"
                            required
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all focus:border-blue-500 dark:focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20"
                        />
                        <InputError :message="form.errors.total" />
                    </div>

                    <!-- Método de Pago -->
                    <div class="space-y-2">
                        <Label for="payment_method" class="text-sm font-medium text-gray-700 dark:text-gray-300">Método de Pago</Label>
                        <select 
                            id="payment_method" 
                            v-model="form.payment_method" 
                            required
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-white transition-all focus:border-blue-500 dark:focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20"
                        >
                            <option value="" disabled>Seleccione</option>
                            <option value="cash">💵 Efectivo</option>
                            <option value="card">💳 Tarjeta</option>
                            <option value="yape">🟢 Yape</option>
                            <option value="plin">🔵 Plin</option>
                        </select>
                        <InputError :message="form.errors.payment_method" />
                    </div>

                    <!-- Tipo de Comprobante -->
                    <div class="space-y-2">
                        <Label for="voucher" class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de Comprobante</Label>
                        <select 
                            id="voucher" 
                            v-model="form.voucher" 
                            required
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-white transition-all focus:border-blue-500 dark:focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20"
                        >
                            <option value="" disabled>Seleccione</option>
                            <option value="ticket">🧾 Boleta</option>
                            <option value="invoice">📄 Factura</option>
                        </select>
                        <InputError :message="form.errors.voucher" />
                    </div>

                    <!-- Documento (ruta de imagen) -->
                    <div class="space-y-2">
                        <Label for="document" class="text-sm font-medium text-gray-700 dark:text-gray-300">Documento (URL)</Label>
                        <input 
                            id="document" 
                            v-model="form.document" 
                            type="text" 
                            placeholder="Ej: /storage/ventas/imagen.jpg"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 transition-all focus:border-blue-500 dark:focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20"
                        />
                        <InputError :message="form.errors.document" />
                    </div>

                    <!-- Fecha -->
                    <div class="space-y-2">
                        <Label for="date_time" class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha y Hora</Label>
                        <input 
                            id="date_time" 
                            v-model="form.date_time" 
                            type="datetime-local"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm text-gray-900 dark:text-white transition-all focus:border-blue-500 dark:focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:focus:ring-blue-400/20"
                        />
                        <InputError :message="form.errors.date_time" />
                    </div>

                    <!-- Botones -->
                    <div class="col-span-full flex gap-3 pt-2">
                        <button 
                            type="submit" 
                            :disabled="form.processing || deleteForm.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 dark:bg-blue-500 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/30 dark:shadow-blue-500/30 transition-all hover:bg-blue-700 dark:hover:bg-blue-600 hover:scale-105 hover:shadow-blue-600/50 dark:hover:shadow-blue-500/50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                        >
                            <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            {{ isEditing ? 'Actualizar' : 'Registrar Venta' }}
                        </button>
                        <button 
                            v-if="isEditing" 
                            type="button" 
                            :disabled="form.processing || deleteForm.processing" 
                            @click="resetForm"
                            class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-6 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 transition-all hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </section>

            <!-- Tabla de Ventas -->
            <section class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
                <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
                                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Listado de Ventas</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ sales.length }} ventas registradas</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-full bg-blue-50 dark:bg-blue-900/30 px-4 py-2">
                            <span class="text-sm font-semibold text-blue-700 dark:text-blue-400">{{ sales.length }}</span>
                            <span class="text-sm text-blue-600 dark:text-blue-400">ventas</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">N° Venta</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Cliente</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Vendedor</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Comprobante</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Total</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Método</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Fecha</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="sales.length === 0">
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <p class="text-gray-500 dark:text-gray-400">No hay ventas registradas</p>
                                    </div>
                                </td>
                            </tr>
                            <tr 
                                v-for="s in sales" 
                                :key="s.id" 
                                class="border-t border-gray-100 dark:border-gray-700 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                            >
                                <td class="px-4 py-3">
                                    <span class="rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-1 text-xs font-medium text-gray-700 dark:text-gray-300">{{ s.voucher_number }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ customers.find(c => c.id === s.id_customers)?.name || 'N/A' }} {{ customers.find(c => c.id === s.id_customers)?.last_name || '' }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                    {{ users.find(u => u.id === s.id_users)?.name || 'N/A' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span 
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium capitalize"
                                        :class="s.voucher === 'invoice' ? 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400'"
                                    >
                                        {{ s.voucher === 'invoice' ? '📄 Factura' : '🧾 Boleta' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900 dark:text-white">S/ {{ Number(s.total).toFixed(2) }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">IGV: S/ {{ (Number(s.total) * 0.18).toFixed(2) }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span 
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium capitalize"
                                        :class="{
                                            'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-400': s.payment_method === 'cash',
                                            'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400': s.payment_method === 'card',
                                            'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-400': s.payment_method === 'yape',
                                            'bg-pink-100 dark:bg-pink-900/50 text-pink-700 dark:text-pink-400': s.payment_method === 'plin',
                                        }"
                                    >
                                        {{ { cash: '💵 Efectivo', card: '💳 Tarjeta', yape: '🟢 Yape', plin: '🔵 Plin' }[s.payment_method] || s.payment_method }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                    {{ new Date(s.date_time).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button 
                                            type="button" 
                                            :disabled="form.processing || deleteForm.processing"
                                            @click="startEdit(s)"
                                            class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 transition-all hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50"
                                        >
                                            Editar
                                        </button>
                                        <button 
                                            type="button" 
                                            :disabled="form.processing || deleteForm.processing" 
                                            @click="remove(s)"
                                            class="rounded-lg bg-red-50 dark:bg-red-900/30 px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 transition-all hover:bg-red-100 dark:hover:bg-red-900/50 disabled:opacity-50"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <InputError :message="deleteError" class="mt-3 px-6 pb-4" />
            </section>

        </main>
    </AppLayout>
</template>
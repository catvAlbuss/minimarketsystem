<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import SaleDetailsController from '@/actions/App/Http/Controllers/SaleDetailController';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Detalle de ventas',
        href: './sale_details',
    },
];

type Product = {
    id: number;
    id_categories: number | null;
    code: string;
    name: string;
    description: string;
    unit_price: number;
    higher_price: number | null;
    stock: number;
    expiration_date: string;
    promotion_discount: number;
    state: 'active' | 'inactive';
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

type SaleDetails = {
    id: number,
    id_sales: number,
    id_products: number,
    quantity: number,
    discount: number,
    sub_total: number,
}

type Props = {
    sales: Sale[];
    products: Product[];
    sale_details: SaleDetails[];
};

const props = defineProps<Props>();
const sales = computed(() => props.sales);
const products = computed(() => props.products);
const sale_details = computed(() => props.sale_details);

const editingId = ref<number | null>(null);

const form = useForm({
    id_sales: props.sales?.[0]?.id ?? '',
    id_products: props.products?.[0]?.id ?? '',
    quantity: 1,
    discount: 0,
    sub_total: 0,
});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);

const isEditing = computed(() => editingId.value !== null);

const resetForm = (): void => {
    editingId.value=null;
    form.reset();
    form.clearErrors();
    form.id_sales = props.sales?.[0]?.id ?? '';
    form.id_products = props.products?.[0]?.id ?? '';
};

const startEdit = (sale_details: SaleDetails): void => {
    editingId.value = sale_details.id;
    form.clearErrors();
    form.id_sales = sale_details.id_sales ?? props.sales?.[0]?.id ?? '';
    form.id_products = sale_details.id_products ?? props.products?.[0]?.id ?? '';
    form.quantity = sale_details.quantity;
    form.discount = sale_details.discount;
    form.sub_total = sale_details.sub_total;
};

const submit = (): void => {
    const options = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };
    if (isEditing.value && editingId.value !== null) {
        form.put(SaleDetailsController.update.url(editingId.value), options);
        return;
    }
    form.post(SaleDetailsController.store.url(), options);
};

const remove = (sale_details: SaleDetails): void => {
    if (!confirm(`Eliminar detalle de venta "${sale_details.id}"?`)) {
        return;
    }

    deleteForm.delete(SaleDetailsController.destroy.url(sale_details.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Detalle de Ventas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        
        <!-- Main Content -->
        <main class="min-h-screen bg-gray-50 p-6">
            
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Detalle de Ventas</h1>
                </div>
            </div>

            <!-- KPIs Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Detalles</p>
                            <h3 class="mt-1 text-3xl font-bold text-gray-900">{{ sale_details.length }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Productos Únicos</p>
                            <h3 class="mt-1 text-3xl font-bold text-purple-600">{{ new Set(sale_details.map(d => d.id_products)).size }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Vendido</p>
                            <h3 class="mt-1 text-3xl font-bold text-green-600">{{ sale_details.reduce((sum, d) => sum + d.quantity, 0) }} unid.</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-50">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Acumulado</p>
                            <h3 class="mt-1 text-3xl font-bold text-emerald-600">S/ {{ sale_details.reduce((sum, d) => sum + Number(d.sub_total), 0).toFixed(2) }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Detalle -->
            <section class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            {{ isEditing ? 'Editar detalle' : 'Nuevo detalle' }}
                        </h2>
                    </div>
                </div>

                <form class="grid gap-5 md:grid-cols-2 lg:grid-cols-3" @submit.prevent="submit">
                    
                    <!-- Venta -->
                    <div class="space-y-2">
                        <Label for="id_sales" class="text-sm font-medium text-gray-700">Venta </Label>
                        <select 
                            id="id_sales" 
                            v-model="form.id_sales" 
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <option value="" disabled>Seleccione</option>
                            <option v-for="s in sales" :key="s.id" :value="s.id">{{ s.voucher_number }}</option>
                        </select>
                        <InputError :message="form.errors.id_sales" />
                    </div>

                    <!-- Producto -->
                    <div class="space-y-2">
                        <Label for="id_products" class="text-sm font-medium text-gray-700">Producto </Label>
                        <select 
                            id="id_products" 
                            v-model="form.id_products" 
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <option value="" disabled>Seleccione</option>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <InputError :message="form.errors.id_products" />
                    </div>

                    <!-- Cantidad -->
                    <div class="space-y-2">
                        <Label for="quantity" class="text-sm font-medium text-gray-700">Cantidad </Label>
                        <Input 
                            id="quantity" 
                            v-model="form.quantity" 
                            type="number" 
                            min="1"
                            placeholder="1"
                            required
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.quantity" />
                    </div>

                    <!-- Descuento -->
                    <div class="space-y-2">
                        <Label for="discount" class="text-sm font-medium text-gray-700">Descuento </Label>
                        <Input 
                            id="discount" 
                            v-model="form.discount" 
                            type="number" 
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.discount" />
                    </div>

                    <!-- Subtotal -->
                    <div class="space-y-2">
                        <Label for="sub_total" class="text-sm font-medium text-gray-700">Total </Label>
                        <Input 
                            id="sub_total" 
                            v-model="form.sub_total" 
                            type="number" 
                            step="0.01"
                            placeholder="0.00"
                            required
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.sub_total" />
                    </div>

                    <!-- Botones -->
                    <div class="col-span-full flex gap-3 pt-2">
                        <Button 
                            type="submit" 
                            :disabled="form.processing || deleteForm.processing"
                            :class="['inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/30 transition-all hover:bg-blue-700 hover:scale-105 hover:shadow-blue-600/50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100']"
                        >
                            <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            {{ isEditing ? 'Actualizar' : 'Agregar Detalle' }}
                        </Button>
                        <Button 
                            v-if="isEditing" 
                            type="button" 
                            variant="secondary"
                            :disabled="form.processing || deleteForm.processing" 
                            @click="resetForm"
                            :class="['rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 transition-all hover:bg-gray-50 disabled:opacity-50']"
                        >
                            Cancelar
                        </Button>
                    </div>
                </form>
            </section>

            <!-- Tabla de Detalles -->
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Listado de Detalles</h2>
                                <p class="text-sm text-gray-500">{{ sale_details.length }} detalles registrados</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2">
                            <span class="text-sm font-semibold text-blue-700">{{ sale_details.length }}</span>
                            <span class="text-sm text-blue-600">detalles</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Venta</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Producto</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Cantidad</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Descuento</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Total</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="sale_details.length === 0">
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <p class="text-gray-500">No hay detalles registrados</p>
                                    </div>
                                </td>
                            </tr>
                            <tr 
                                v-for="d in sale_details" 
                                :key="d.id" 
                                class="border-t border-gray-100 transition-colors hover:bg-gray-50"
                            >
                                <td class="px-4 py-3">
                                    <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">#{{ d.id }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-md bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">
                                        {{ sales.find(s => s.id === d.id_sales)?.voucher_number || 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900">{{ products.find(p => p.id === d.id_products)?.name || 'N/A' }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                                        {{ d.quantity }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    <span v-if="Number(d.discount) > 0" class="text-green-600 font-medium">- S/ {{ Number(d.discount).toFixed(2) }}</span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900">S/ {{ Number(d.sub_total).toFixed(2) }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Button 
                                            type="button" 
                                            variant="destructive" 
                                            size="sm"
                                            :disabled="form.processing || deleteForm.processing" 
                                            @click="remove(d)"
                                            :class="['rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition-all hover:bg-red-100 disabled:opacity-50']"
                                        >
                                            Eliminar
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </AppLayout>
</template>


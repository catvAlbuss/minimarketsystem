<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import SaleController from '@/actions/App/Http/Controllers/SaleController';

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
    id: number;
    name: string;
    lastname: string;
    dni: number;
    phone: number;
    address: string;
    email: string;
    children: number;
    affiliate: string;
    insured: string;
    work_modality: string;
    entry_date: string;
    retention: string;
    entry_to_payroll: string;
    role: string;
    state: string;
};

type Sale = {
    id: number;
    id_customers: number;
    id_users: number;
    voucher_number: string;
    igv: number;
    total: number;
    payment_method: 'cash' | 'card' | 'yape' | 'plin';
    voucher: 'ticket' | 'invoice';
    document: string;
    date_time: string;
};

type Products = {
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
    image: string | null;
};

type SaleItem = {
    id: number;
    name: string;
    quantity: number;
    price_unit: number;
    total: number;
    method: string;
    status: string;
    max_stock: number;
};

type PaginatedProducts = {
    data: Products[];
};

type Props = {
    sales: Sale[];
    customers: Customer[];
    users: User[];
    products: Products[] | PaginatedProducts;
};

const props = defineProps<Props>();
const sales = computed(() => props.sales);
const customers = computed(() => props.customers);
const products = computed<Products[]>(() =>
    Array.isArray(props.products)
        ? props.products
        : (props.products?.data ?? []),
);
const SaleItems = ref<SaleItem[]>([]);

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
const deleteError = computed(
    () => (deleteForm.errors as Record<string, string | undefined>).delete,
);

const isEditing = computed(() => editingId.value !== null);

const resetForm = (): void => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.id_customers = props.customers?.[0]?.id ?? '';
    form.id_users = props.users?.[0]?.id ?? '';
};

const remove = (sales: Sale): void => {
    if (!confirm(`Eliminar venta "${sales.voucher_number}"?`)) {
        return;
    }

    deleteForm.delete(SaleController.destroy.url(sales.id), {
        preserveScroll: true,
    });
};

const subTotal = computed(() => {
    return (SaleItems.value.reduce((suma, item) => suma + item.total, 0).toFixed(2));
});

const igv = computed(() => {
    return (Number(subTotal.value) * 0.18).toFixed(2);
});

const total = computed(() => {
    return Number(subTotal.value) + Number(igv.value);
});

const addToSale = (product: Products) => {
    const stockDisponible = product.stock;

    const existItem = SaleItems.value.find((item) => item.id == product.id);

    //si existe el item suma la cantidad
    if (existItem) {
        const newQuanti = existItem.quantity + 1;

        //valida si la nueva cantidad no es mayor al stockDisponible
        if (newQuanti > stockDisponible) {
            alert(`Stock insuficiente para ${product.name}. 
                   Disponible: ${stockDisponible} | Solicitado: ${newQuanti}`);
            return;
        }
        existItem.quantity = newQuanti;
        existItem.total = existItem.price_unit * existItem.quantity;
    } else {
        const newItem: SaleItem = {
            id: product.id,
            name: product.name,
            quantity: 1,
            price_unit: product.unit_price,
            total: product.unit_price * 1,
            method: 'Efectico',
            status: product.state,
            max_stock: stockDisponible,
        };
        SaleItems.value.push(newItem);
        // console.log('Producto agregado:', newItem);
    }
};

//Elimina un producto de la ventana de ventas
const removeItemSale = (index: number) => {
    SaleItems.value.splice(index, 1);
};

const procesSale = async () => {
    //condicionamos si hay algo en las ventas
    if (SaleItems.value.length === 0) {
        alert('Selecciona un producto');
        return;
    }

    if (!confirm('¿Confirmar la venta?')) {
        return;
    }

    //prepara los datos
    const data = {
        items: SaleItems.value.map(item => ({
            id: item.id,
            name: item.name,
            quantity: item.quantity,
            status: item.status,
            method: item.method,
            total: item.total,
            state: item.status,
            payment: item.method
        })),
        total: total.value,
        // state: state.value,
        // payment: paymentMethod.value,
    };

    console.log(data);
    const box = JSON.stringify(data);
    console.log(box);
    //Enviar con Inertia

    router.post('/sales', data);
    // await(BoxController.store, box);
}
</script>

<template>
    <Head title="Ventas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Main Content -->
        <main class="min-h-screen bg-gray-50 p-6 dark:bg-gray-900">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                        Gestión de Ventas
                    </h1>
                </div>
            </div>

            <!-- Historial de Venta -->
            <section class="mb-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                                    Listado de Ventas
                                </h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ sales.length }} ventas registradas
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 dark:bg-blue-900/30">
                            <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">{{ sales.length }}</span>
                            <span class="text-sm text-blue-600 dark:text-blue-400">ventas</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    N° Venta
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    Cliente
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    Vendedor
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    Comprobante
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    Total
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    Método
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    Fecha
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="sales.length === 0">
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <p class="text-gray-500 dark:text-gray-400">
                                            No hay ventas registradas
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="s in sales" :key="s.id"
                                class="border-t border-gray-100 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                <td class="px-4 py-3">
                                    <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{
                                        s.voucher_number }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{
                                            customers.find(
                                                (c) =>
                                                    c.id ===
                                                    s.id_customers,
                                            )?.name || 'N/A'
                                        }}
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                    {{
                                        users.find(
                                            (u) => u.id === s.id_users,
                                        )?.name || 'N/A'
                                    }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium capitalize"
                                        :class="s.voucher === 'invoice'
                                            ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300'
                                            : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                            ">
                                        {{
                                            s.voucher === 'invoice'
                                                ? '📄 Factura'
                                                : '🧾 Boleta'
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        S/
                                        {{ Number(s.total).toFixed(2) }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        IGV: S/
                                        {{
                                            (
                                                Number(s.total) * 0.18
                                            ).toFixed(2)
                                        }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium capitalize"
                                        :class="{
                                            'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300':
                                                s.payment_method ===
                                                'cash',
                                            'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300':
                                                s.payment_method ===
                                                'card',
                                            'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300':
                                                s.payment_method ===
                                                'yape',
                                            'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300':
                                                s.payment_method ===
                                                'plin',
                                        }">
                                        {{
                                            {
                                                cash: '💵 Efectivo',
                                                card: '💳 Tarjeta',
                                                yape: '🟢 Yape',
                                                plin: '🔵 Plin',
                                            }[s.payment_method] ||
                                            s.payment_method
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                    {{
                                        new Date(
                                            s.date_time,
                                        ).toLocaleDateString('es-PE', {
                                            day: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                        })
                                    }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Button type="button" variant="destructive" size="sm" :disabled="form.processing ||
                                            deleteForm.processing
                                            " @click="remove(s)" :class="[
                                                        'rounded-lg px-3 py-1.5 text-xs font-medium transition-all disabled:opacity-50',
                                                        'bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30'
                                                    ]">
                                            Eliminar
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Tabla de Ventas -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                                        Listado de Productos
                                    </h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ products.length }} productos registrados
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 dark:bg-blue-900/30">
                                <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">{{ products.length }}</span>
                                <span class="text-sm text-blue-600 dark:text-blue-400">productos</span>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        Producto
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        Código
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        Precio
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        Stock
                                    </th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="products.length === 0">
                                    <td colspan="9" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <svg class="h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                            <p class="text-gray-500 dark:text-gray-400">
                                                No hay productos registrados
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-for="p in products" :key="p.id"
                                    class="border-t border-gray-100 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white">
                                                {{ p.name }}
                                            </p>
                                            <p class="line-clamp-1 text-xs text-gray-500 dark:text-gray-400">
                                                {{ p.description }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">{{
                                                p.code }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="font-semibold text-gray-900 dark:text-white">
                                                S/
                                                {{
                                                    Number(
                                                        p.unit_price,
                                                    ).toFixed(2)
                                                }}
                                            </p>
                                            <p v-if="p.higher_price" class="text-xs text-gray-500 dark:text-gray-400">
                                                Mayor: S/
                                                {{
                                                    Number(
                                                        p.higher_price,
                                                    ).toFixed(2)
                                                }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium"
                                            :class="p.stock < 10
                                                ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                                : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                ">
                                            <span :class="p.stock < 10
                                                ? 'bg-red-600 dark:bg-red-400'
                                                : 'bg-green-600 dark:bg-green-400'
                                                " class="h-1.5 w-1.5 rounded-full"></span>
                                            {{ p.stock }} unid.
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <Button type="button" @click.stop="addToSale(p)"
                                                class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white shadow-lg shadow-blue-600/30 transition-all hover:scale-105 hover:bg-blue-700 hover:shadow-blue-600/50 disabled:bg-gray-300 disabled:text-gray-500 disabled:shadow-none disabled:hover:scale-100 dark:bg-blue-600 dark:hover:bg-blue-700 dark:disabled:bg-gray-600 dark:disabled:text-gray-400">
                                                Agregar
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                                        Listado de Productos
                                    </h2>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ SaleItems.length }} productos registrados
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 dark:bg-blue-900/30">
                                <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">{{ SaleItems.length }}</span>
                                <span class="text-sm text-blue-600 dark:text-blue-400">productos</span>
                            </div>
                        </div>
                    </div>

                    <!-- Productos agregados -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-2 py-2 text-gray-700 dark:text-gray-200">Producto</th>
                                    <th class="px-2 py-2 text-gray-700 dark:text-gray-200">Cantidad</th>
                                    <th class="px-2 py-2 text-gray-700 dark:text-gray-200">P. unitario</th>
                                    <th class="px-2 py-2 text-gray-700 dark:text-gray-200">Total</th>
                                    <th class="px-2 py-2 text-gray-700 dark:text-gray-200"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in SaleItems" :key="index" class="border-b border-gray-100 dark:border-gray-700">
                                    <td class="px-2 py-3">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ item.name }}
                                        </p>
                                    </td>
                                    <td class="px-2 py-2">
                                        <!-- Input de cantidad -->
                                        <Input type="number" :value="item.quantity" 
                                            class="h-8 w-16 text-center border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                            placeholder="1" />
                                    </td>

                                    <td class="px-2 py-2 text-gray-600 dark:text-gray-300">
                                        S/{{ item.price_unit }}
                                    </td>
                                    <td class="px-2 py-2 font-semibold text-gray-900 dark:text-white">
                                        S/{{ item.total.toFixed(2) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <Button type="button" variant="destructive" @click.stop="removeItemSale(index)"
                                            class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition-all hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30">
                                            Eliminar
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!--Apartado de la venta de producto -->
                    <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                        <div class="overflow-x-auto">
                            <div class="flex space-x-2 justify-center">
                                <Button @click="procesSale" type="submit" 
                                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700">
                                    Generar Pago
                                    <i class="fas fa-plus"></i>
                                </Button>
                                <div class="flex gap-3 space-y-2 border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <div class="flex justify-between text-gray-700 dark:text-gray-300">
                                        sub total: {{ subTotal }}
                                    </div>
                                    <div class="flex justify-between text-gray-700 dark:text-gray-300">
                                        IGV: {{ igv }}
                                    </div>
                                    <div class="flex justify-between font-bold text-gray-900 dark:text-white">
                                        TOTAL: {{ total.toFixed(2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </AppLayout>
</template>

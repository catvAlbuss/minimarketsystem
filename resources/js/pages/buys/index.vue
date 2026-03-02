<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { computed, reactive, ref } from 'vue';
import BuyController from '@/actions/App/Http/Controllers/BuyController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import buysRoutes from '@/routes/buys';
import { type BreadcrumbItem } from '@/types';
import type { Provider, User, Buy, Product } from '@/types/buys';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Compras',
        href: buysRoutes.index.url(),
    },
];

type Props = {
    buys: Buy[];
    providers: Provider[];
    users: User[];
    low_stock_products?: Product[];
};

const props = defineProps<Props>();

const buys = computed(() => props.buys ?? []);
const providers = computed(() => props.providers ?? []);
const users = computed(() => props.users ?? []);
const lowStockProducts = computed(() => props.low_stock_products ?? []);

const outOfStockProducts = computed(() => lowStockProducts.value.filter((product) => Number(product.stock) === 0));
const lowStockWarningProducts = computed(() =>
    lowStockProducts.value.filter((product) => Number(product.stock) > 0 && Number(product.stock) <= 5),
);

const targetRestockTo = ref(20);
const selectedProductIds = ref<number[]>([]);
const restockByProduct = reactive<Record<number, number>>({});

const form = useForm({
    id_provider: props.providers?.[0]?.id ?? '',
    id_users: props.users?.[0]?.id ?? '',
    voucher_number: '',
    total: 0,
    payment_method: '',
    payment_status: '',
    date_time: '',
});

const selectedProviderName = computed(() => {
    const provider = providers.value.find((item) => Number(item.id) === Number(form.id_provider));
    return provider?.company_name ?? '';
});

const orderForm = useForm<{ id_provider: number | string; payment_method: string; items: { id_products: number; quantity: number }[] }>({
    id_provider: props.providers?.[0]?.id ?? '',
    payment_method: 'cash',
    items: [],
});

const deleteForm = useForm({});
const isDarkMode = (): boolean => document.documentElement.classList.contains('dark');

const sweetAlertTheme = computed(() => ({
    background: isDarkMode() ? '#0f172a' : '#ffffff',
    color: isDarkMode() ? '#e2e8f0' : '#0f172a',
    confirmButtonColor: '#2563eb',
    cancelButtonColor: isDarkMode() ? '#475569' : '#64748b',
    customClass: {
        popup: 'rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xl',
        title: 'text-slate-900 dark:text-slate-100',
        htmlContainer: 'text-slate-700 dark:text-slate-300',
    },
}));

const normalizeQuantity = (product: Product): number => {
    const suggested = Math.max(1, Number(targetRestockTo.value) - Number(product.stock || 0));
    const existing = Number(restockByProduct[product.id] ?? suggested);
    const finalValue = Number.isFinite(existing) && existing > 0 ? Math.floor(existing) : suggested;
    restockByProduct[product.id] = finalValue;
    return finalValue;
};

const getQuantityForProduct = (product: Product): number => {
    const fallback = Math.max(1, Number(targetRestockTo.value) - Number(product.stock || 0));
    const rawValue = Number(restockByProduct[product.id] ?? fallback);
    const sanitized = Number.isFinite(rawValue) && rawValue > 0 ? Math.floor(rawValue) : fallback;
    restockByProduct[product.id] = sanitized;
    return sanitized;
};

const isSelected = (id: number): boolean => selectedProductIds.value.includes(id);

const toggleSelection = (id: number): void => {
    if (isSelected(id)) {
        selectedProductIds.value = selectedProductIds.value.filter((value) => value !== id);
        return;
    }
    selectedProductIds.value = [...selectedProductIds.value, id];
};

const selectAllFromList = (products: Product[]): void => {
    const ids = products.map((product) => product.id);
    selectedProductIds.value = Array.from(new Set([...selectedProductIds.value, ...ids]));
};

const clearSelectionFromList = (products: Product[]): void => {
    const ids = new Set(products.map((product) => product.id));
    selectedProductIds.value = selectedProductIds.value.filter((id) => !ids.has(id));
};

const selectedProducts = computed(() =>
    selectedProductIds.value
        .map((id) => lowStockProducts.value.find((product) => product.id === id))
        .filter((product): product is Product => Boolean(product)),
);
const selectedItemsCount = computed(() => selectedProducts.value.length);
const selectedTotalUnits = computed(() =>
    selectedProducts.value.reduce((sum, product) => sum + getQuantityForProduct(product), 0),
);

const formatCurrency = (value: number): string =>
    new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(value || 0);

const submit = (): void => {
    form.post(BuyController.store.url(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.id_provider = props.providers?.[0]?.id ?? '';
            form.id_users = props.users?.[0]?.id ?? '';
            Swal.fire({
                title: 'Compra registrada',
                text: 'La compra se guardo correctamente.',
                icon: 'success',
                ...sweetAlertTheme.value,
            });
        },
    });
};

const createOrder = async (): Promise<void> => {
    if (!form.id_provider) {
        await Swal.fire({
            title: 'Proveedor requerido',
            text: 'Selecciona un proveedor en el formulario de compras para generar el pedido.',
            icon: 'warning',
            ...sweetAlertTheme.value,
        });
        return;
    }

    if (selectedProductIds.value.length === 0) {
        await Swal.fire({
            title: 'Productos requeridos',
            text: 'Selecciona al menos un producto para crear el pedido.',
            icon: 'warning',
            ...sweetAlertTheme.value,
        });
        return;
    }

    const invalidProducts = selectedProducts.value.filter((product) => getQuantityForProduct(product) < 1);
    if (invalidProducts.length > 0) {
        await Swal.fire({
            title: 'Cantidad invalida',
            html: `Corrige la cantidad de <strong>${invalidProducts.length}</strong> producto(s). Todas deben ser mayores o iguales a 1.`,
            icon: 'warning',
            ...sweetAlertTheme.value,
        });
        return;
    }

    orderForm.id_provider = form.id_provider;
    orderForm.items = selectedProducts.value.map((product) => ({
        id_products: product.id,
        quantity: getQuantityForProduct(product),
    }));

    const totalUnits = orderForm.items.reduce((sum, item) => sum + Number(item.quantity), 0);
    const confirmation = await Swal.fire({
        title: 'Confirmar solicitud al proveedor',
        html: `Proveedor: <strong>${selectedProviderName.value || 'No definido'}</strong><br>Items: <strong>${orderForm.items.length}</strong><br>Unidades: <strong>${totalUnits}</strong><br>Modo: <strong>${isDarkMode() ? 'Oscuro' : 'Claro'}</strong>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Si, solicitar pedido',
        cancelButtonText: 'Cancelar',
        ...sweetAlertTheme.value,
    });

    if (!confirmation.isConfirmed) {
        return;
    }

    orderForm.post(buysRoutes.order.url(), {
        preserveScroll: true,
        onSuccess: () => {
            selectedProductIds.value = [];
            Object.keys(restockByProduct).forEach((key) => delete restockByProduct[Number(key)]);
            Swal.fire({
                title: 'Pedido creado',
                html: `La solicitud al proveedor se registro correctamente.<br>Items: <strong>${orderForm.items.length}</strong><br>Unidades: <strong>${totalUnits}</strong>`,
                icon: 'success',
                ...sweetAlertTheme.value,
            });
        },
    });
};

const deleteBuy = async (buy: Buy): Promise<void> => {
    const detailsCount = Number(buy.buy_details_count ?? 0);
    const confirmation = await Swal.fire({
        title: 'Eliminar compra',
        html: `Comprobante: <strong>${buy.voucher_number}</strong><br>Proveedor: <strong>${buy.provider?.company_name ?? `#${buy.id_provider}`}</strong><br>Se eliminaran ${detailsCount} detalle(s) relacionados.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar',
        ...sweetAlertTheme.value,
        confirmButtonColor: '#dc2626',
    });

    if (!confirmation.isConfirmed) {
        return;
    }

    deleteForm.delete(buysRoutes.destroy.url(buy.id), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                title: 'Compra eliminada',
                text: 'La compra y sus detalles relacionados fueron eliminados.',
                icon: 'success',
                ...sweetAlertTheme.value,
            });
        },
    });
};
</script>

<template>
    <Head title="Compras" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <main class="min-h-screen bg-slate-50 p-4 text-slate-900 transition-colors md:p-6 dark:bg-slate-950 dark:text-slate-100">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">Gestion de compras</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Un solo formulario para registrar compras y una zona de alertas para preparar pedidos al proveedor.
                </p>

                <form class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="id_provider">Proveedor</Label>
                        <select
                            id="id_provider"
                            v-model="form.id_provider"
                            required
                            class="h-9 rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none focus:border-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-slate-500"
                        >
                            <option value="">Seleccione</option>
                            <option v-for="provider in providers" :key="provider.id" :value="provider.id">
                                {{ provider.company_name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.id_provider" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="id_users">Usuario</Label>
                        <select
                            id="id_users"
                            v-model="form.id_users"
                            required
                            class="h-9 rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none focus:border-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-slate-500"
                        >
                            <option value="">Seleccione</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">
                                {{ user.name }} {{ user.lastname }}
                            </option>
                        </select>
                        <InputError :message="form.errors.id_users" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="voucher_number">Numero de comprobante</Label>
                        <Input id="voucher_number" v-model="form.voucher_number" type="text" placeholder="Ej: FAC-001245" required />
                        <InputError :message="form.errors.voucher_number" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="total">Total</Label>
                        <Input id="total" v-model="form.total" type="number" min="0" step="0.01" placeholder="Ej: 150.30" required />
                        <InputError :message="form.errors.total" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="payment_method">Metodo de pago</Label>
                        <select
                            id="payment_method"
                            v-model="form.payment_method"
                            required
                            class="h-9 rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none focus:border-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-slate-500"
                        >
                            <option value="">Seleccione</option>
                            <option value="cash">Efectivo</option>
                            <option value="card">Tarjeta</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                        </select>
                        <InputError :message="form.errors.payment_method" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="payment_status">Estado de pago</Label>
                        <select
                            id="payment_status"
                            v-model="form.payment_status"
                            required
                            class="h-9 rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-900 outline-none focus:border-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-slate-500"
                        >
                            <option value="">Seleccione</option>
                            <option value="cancelled">Cancelado</option>
                            <option value="pending">Pendiente</option>
                            <option value="delivered">Entregado</option>
                        </select>
                        <InputError :message="form.errors.payment_status" />
                    </div>

                    <div class="col-span-full">
                        <Button type="submit" :disabled="form.processing || orderForm.processing">Registrar compra</Button>
                    </div>
                </form>
            </section>

            <section
                class="mt-5 rounded-xl border border-red-200 bg-gradient-to-br from-white to-red-50 p-5 shadow-sm transition-colors dark:border-red-900/60 dark:from-slate-900 dark:to-slate-900"
            >
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-red-700 dark:text-red-400">Alerta critica: productos agotados</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Stock en 0. Estos productos deben incluirse primero en el pedido.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Button type="button" variant="secondary" @click="selectAllFromList(outOfStockProducts)">
                            Seleccionar todos
                        </Button>
                        <Button type="button" variant="secondary" @click="clearSelectionFromList(outOfStockProducts)">
                            Limpiar seccion
                        </Button>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[760px] text-sm">
                        <thead class="border-b border-slate-200 text-left dark:border-slate-700">
                            <tr>
                                <th class="px-2 py-2">Sel.</th>
                                <th class="px-2 py-2">Codigo</th>
                                <th class="px-2 py-2">Producto</th>
                                <th class="px-2 py-2">Stock</th>
                                <th class="px-2 py-2">Cantidad a pedir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="outOfStockProducts.length === 0">
                                <td colspan="5" class="px-2 py-4 text-center text-slate-500 dark:text-slate-400">No hay productos agotados.</td>
                            </tr>
                            <tr v-for="product in outOfStockProducts" :key="product.id" class="border-b border-slate-100 dark:border-slate-800">
                                <td class="px-2 py-2">
                                    <input
                                        type="checkbox"
                                        :checked="isSelected(product.id)"
                                        @change="toggleSelection(product.id)"
                                    />
                                </td>
                                <td class="px-2 py-2">{{ product.code }}</td>
                                <td class="px-2 py-2">{{ product.name }}</td>
                                <td class="px-2 py-2 font-semibold text-red-700 dark:text-red-400">{{ product.stock }}</td>
                                <td class="px-2 py-2">
                                    <input
                                        v-model.number="restockByProduct[product.id]"
                                        type="number"
                                        min="1"
                                        class="h-9 w-28 rounded-md border border-slate-300 bg-white px-2 text-slate-900 outline-none focus:border-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-slate-500"
                                        @focus="normalizeQuantity(product)"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section
                class="mt-5 rounded-xl border border-amber-200 bg-gradient-to-br from-white to-amber-50 p-5 shadow-sm transition-colors dark:border-amber-900/60 dark:from-slate-900 dark:to-slate-900"
            >
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-amber-700 dark:text-amber-400">Alerta preventiva: stock bajo</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-400">Productos con stock entre 1 y 5. Usalos para pedidos de reposicion.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Button type="button" variant="secondary" @click="selectAllFromList(lowStockWarningProducts)">
                            Seleccionar todos
                        </Button>
                        <Button type="button" variant="secondary" @click="clearSelectionFromList(lowStockWarningProducts)">
                            Limpiar seccion
                        </Button>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[760px] text-sm">
                        <thead class="border-b border-slate-200 text-left dark:border-slate-700">
                            <tr>
                                <th class="px-2 py-2">Sel.</th>
                                <th class="px-2 py-2">Codigo</th>
                                <th class="px-2 py-2">Producto</th>
                                <th class="px-2 py-2">Stock</th>
                                <th class="px-2 py-2">Cantidad a pedir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="lowStockWarningProducts.length === 0">
                                <td colspan="5" class="px-2 py-4 text-center text-slate-500 dark:text-slate-400">No hay productos con stock bajo.</td>
                            </tr>
                            <tr v-for="product in lowStockWarningProducts" :key="product.id" class="border-b border-slate-100 dark:border-slate-800">
                                <td class="px-2 py-2">
                                    <input
                                        type="checkbox"
                                        :checked="isSelected(product.id)"
                                        @change="toggleSelection(product.id)"
                                    />
                                </td>
                                <td class="px-2 py-2">{{ product.code }}</td>
                                <td class="px-2 py-2">{{ product.name }}</td>
                                <td class="px-2 py-2 font-semibold text-amber-700 dark:text-amber-400">{{ product.stock }}</td>
                                <td class="px-2 py-2">
                                    <input
                                        v-model.number="restockByProduct[product.id]"
                                        type="number"
                                        min="1"
                                        class="h-9 w-28 rounded-md border border-slate-300 bg-white px-2 text-slate-900 outline-none focus:border-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-slate-500"
                                        @focus="normalizeQuantity(product)"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/70 md:grid-cols-2 xl:grid-cols-4">
                    <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-medium text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                        Proveedor vinculado: {{ selectedProviderName || 'Sin seleccionar' }}
                    </span>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                        Productos seleccionados: {{ selectedItemsCount }}
                    </span>
                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                        Unidades por pedir: {{ selectedTotalUnits }}
                    </span>
                    <label class="text-sm text-slate-700 dark:text-slate-300">
                        Objetivo de stock:
                        <input
                            v-model.number="targetRestockTo"
                            type="number"
                            min="1"
                            class="ml-2 h-9 w-24 rounded-md border border-slate-300 bg-white px-2 text-slate-900 outline-none focus:border-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-slate-500"
                        />
                    </label>
                    <label class="text-sm text-slate-700 dark:text-slate-300">
                        Pago pedido:
                        <select
                            v-model="orderForm.payment_method"
                            class="ml-2 h-9 rounded-md border border-slate-300 bg-white px-3 text-slate-900 outline-none focus:border-slate-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-slate-500"
                        >
                            <option value="cash">Efectivo</option>
                            <option value="card">Tarjeta</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                        </select>
                    </label>
                    <Button type="button" :disabled="orderForm.processing || !form.id_provider" @click="createOrder">
                        Crear pedido al proveedor ({{ selectedItemsCount }})
                    </Button>
                </div>
            </section>

            <section class="mt-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Historial de compras</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[980px] text-sm">
                        <thead class="border-b border-slate-200 text-left dark:border-slate-700">
                            <tr>
                                <th class="px-2 py-2">Comprobante</th>
                                <th class="px-2 py-2">Proveedor</th>
                                <th class="px-2 py-2">Usuario</th>
                                <th class="px-2 py-2">Total</th>
                                <th class="px-2 py-2">Metodo</th>
                                <th class="px-2 py-2">Estado</th>
                                <th class="px-2 py-2">Detalles</th>
                                <th class="px-2 py-2 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="buys.length === 0">
                                <td colspan="8" class="px-2 py-4 text-center text-slate-500 dark:text-slate-400">
                                    No hay compras registradas.
                                </td>
                            </tr>
                            <tr v-for="buy in buys" :key="buy.id" class="border-b border-slate-100 dark:border-slate-800">
                                <td class="px-2 py-2 font-medium text-slate-900 dark:text-slate-100">{{ buy.voucher_number }}</td>
                                <td class="px-2 py-2 text-slate-700 dark:text-slate-300">{{ buy.provider?.company_name ?? `#${buy.id_provider}` }}</td>
                                <td class="px-2 py-2 text-slate-700 dark:text-slate-300">
                                    {{ buy.user ? `${buy.user.name} ${buy.user.lastname}` : `#${buy.id_users}` }}
                                </td>
                                <td class="px-2 py-2 text-slate-700 dark:text-slate-300">{{ formatCurrency(Number(buy.total)) }}</td>
                                <td class="px-2 py-2 text-slate-700 dark:text-slate-300">{{ buy.payment_method }}</td>
                                <td class="px-2 py-2">
                                    <span
                                        class="rounded-full px-2 py-1 text-xs font-medium"
                                        :class="
                                            buy.payment_status === 'delivered'
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300'
                                                : buy.payment_status === 'pending'
                                                  ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300'
                                                  : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
                                        "
                                    >
                                        {{ buy.payment_status }}
                                    </span>
                                </td>
                                <td class="px-2 py-2 text-slate-700 dark:text-slate-300">{{ buy.buy_details_count ?? 0 }}</td>
                                <td class="px-2 py-2 text-right">
                                    <Button type="button" variant="destructive" size="sm" :disabled="deleteForm.processing" @click="deleteBuy(buy)">
                                        Eliminar
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </AppLayout>
</template>

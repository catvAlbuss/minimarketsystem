<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import InputError from '@/components/InputError.vue';

type Promotion = {
    id: number;
    id_products: number;
    name_promotion: string;
    price: number;
    state: string;
};

type AddProducts = {
    id: number;
    name: string;
    unit_price: number;
    promotion_discount: number;
    price_discount: number;
};

type Products = {
    id: number;
    name: string;
    unit_price: number;
    promotion_discount: number;
};

type Props = {
    promotions: Promotion[];
    products: Products[];
};

const AddProduct = ref<AddProducts[]>([]);

const form = useForm({
    name_promotion: '',
    state: ''
});

const props = defineProps<Props>();
const promotion = computed(() => props.promotions);
const product = computed(() => props.products);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Promociones',
        href: '/promotions',
    },
];

const editingId = ref<number | null>(null);
const isEditing = computed(() => editingId.value !== null);

const deleteForm = useForm({});
const deleteError = computed(
    () => (deleteForm.errors as Record<string, string | undefined>).delete,
);

// KPIs Computados - VERSIÓN SIMPLIFICADA Y SEGURA
const totalPromociones = computed(() => promotion.value.length);

const promocionesActivas = computed(() => 
    promotion.value.filter(p => p.state === 'active').length
);

const totalProductosEnPromo = computed(() => {
    return promotion.value.reduce((sum, p) => sum + (p.id_products ? 1 : 0), 0);
});

// ⚠️ IMPORTANTE: Esta es la versión CORREGIDA
const valorTotalPromos = computed(() => {
    if (!promotion.value || promotion.value.length === 0) return 0;
    return promotion.value.reduce((total, prom) => {
        return total + (Number(prom.price) || 0);
    }, 0);
});

const total = computed(() => {
    return AddProduct.value.reduce(
        (suma, item) => suma + (item.unit_price - item.price_discount),
        0,
    );
});

const addProduct = (product: Products) => {
    const calculateDiscount =
        product.unit_price * (product.promotion_discount / 100);

    const newItem: AddProducts = {
        id: product.id,
        name: product.name,
        unit_price: product.unit_price,
        promotion_discount: product.promotion_discount,
        price_discount: Number(calculateDiscount.toFixed(2)),
    };
    AddProduct.value.push(newItem);
};

const removeProduct = (index: number) => {
    AddProduct.value.splice(index, 1);
};

const selectedIds = computed(() => {
    return new Set(AddProduct.value.map((item) => item.id));
});

const resetData = () => {
    editingId.value = null;
    form.reset();
    AddProduct.value = [];
};

const crearPromotion = async () => {
    if (AddProduct.value.length === 0) {
        alert('Selecciona un producto');
        return;
    }
    if (!form.name_promotion || !form.state) {
        alert('Por favor complete el nombre y el estado');
        return;
    }

    const data = {
        item: AddProduct.value.map((item) => ({
            id: item.id,
        })),
        name_promotion: form.name_promotion,
        price: total.value,
        state: form.state,
    };

    if (isEditing.value && editingId.value !== null) {
        router.put(`/promotions/${editingId.value}`, data, {
            onSuccess: () => {
                resetData();
                alert('Promoción Actualizada');
            },
            onError: (errors) => form.setError(errors),
        });
    } else {
        router.post('/promotions', data, {
            preserveScroll: true,
            onSuccess: () => {
                resetData();
                alert('Promoción creada.');
            },
            onError: (errors) => form.setError(errors),
        });
    }
};

const startEdit = (prom: Promotion): void => {
    editingId.value = prom.id;
    form.name_promotion = prom.name_promotion;
    form.state = prom.state;

    AddProduct.value = [];

    const productInCombo = props.promotions.filter(
        (p) => p.name_promotion === prom.name_promotion,
    );

    productInCombo.forEach((item) => {
        const prodData = props.products.find((p) => p.id === item.id_products);
        if (prodData) {
            addProduct(prodData);
        }
    });
};

const remove = (prom: Promotion): void => {
    if (!confirm(`Eliminar promoción "${prom.name_promotion}"?`)) {
        return;
    }

    router.delete(`/promotions/${prom.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            if (editingId.value === prom.id) {
                resetData();
            }
        },
    });
};
</script>

<template>
    <Head title="Promociones" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Main Content -->
        <main class="min-h-screen bg-gray-50 p-6">
            <div class="mx-auto max-w-7xl">
                
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Gestión de Promociones</h1>
                       
                    </div>
                </div>

                <!-- KPIs Cards -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Promociones -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Promociones</p>
                                <h3 class="mt-1 text-3xl font-bold text-gray-900">{{ totalPromociones }}</h3>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Promociones Activas -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Promociones Activas</p>
                                <h3 class="mt-1 text-3xl font-bold text-emerald-600">{{ promocionesActivas }}</h3>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50">
                                <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Productos en Promo -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Productos en Promo</p>
                                <h3 class="mt-1 text-3xl font-bold text-purple-600">{{ totalProductosEnPromo }}</h3>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Valor Total -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Valor Total</p>
                                <h3 class="mt-1 text-3xl font-bold text-orange-600">
                                    S/ {{ valorTotalPromos.toFixed(2) }}
                                </h3>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-50">
                                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Promociones Existentes -->
                <section class="mb-6 rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-gray-900">Promociones Registradas</h2>
                                    <p class="text-sm text-gray-500">{{ totalPromociones }} promociones en total</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2">
                                <span class="text-sm font-semibold text-blue-700">{{ totalPromociones }}</span>
                                <span class="text-sm text-blue-600">promociones</span>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">ID Producto</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Nombre Promoción</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Precio</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Estado</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="promotion.length === 0">
                                    <td colspan="5" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                            <p class="text-gray-500">No hay promociones registradas</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr 
                                    v-for="prom in promotion" 
                                    :key="prom.id" 
                                    class="border-t border-gray-100 transition-colors hover:bg-gray-50"
                                >
                                    <td class="px-4 py-3">
                                        <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">#{{ prom.id_products }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100">
                                                <span class="text-sm font-bold text-blue-700">{{ prom.name_promotion.charAt(0).toUpperCase() }}</span>
                                            </div>
                                            <p class="font-semibold text-gray-900">{{ prom.name_promotion }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-emerald-600">S/ {{ prom.price }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span 
                                            class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                                            :class="prom.state === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700'"
                                        >
                                            {{ prom.state === 'active' ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <Button 
                                                type="button" 
                                                variant="secondary" 
                                                size="sm"
                                                :disabled="form.processing || deleteForm.processing"
                                                @click="startEdit(prom)"
                                                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition-all hover:bg-gray-50 disabled:opacity-50"
                                            >
                                                Editar
                                            </Button>
                                            <Button 
                                                type="button" 
                                                variant="destructive" 
                                                size="sm"
                                                :disabled="form.processing || deleteForm.processing" 
                                                @click="remove(prom)"
                                                class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition-all hover:bg-red-100 disabled:opacity-50"
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

                <!-- Grid de 2 columnas para Productos y Creación de Combos -->
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    
                    <!-- Tarjeta: Listado de Productos -->
                    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="mb-5 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <h2 class="text-lg font-bold text-gray-900">Listado de Productos</h2>
                        </div>
                        
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Producto</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">P/U</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Dto. %</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-if="product.length === 0">
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                            No hay productos registrados
                                        </td>
                                    </tr>
                                    <tr v-for="prod in product" :key="prod.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">#{{ prod.id }}</span>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ prod.name }}</td>
                                        <td class="px-4 py-3 text-gray-700">S/ {{ prod.unit_price }}</td>
                                        <td class="px-4 py-3">
                                            <span class="font-medium text-orange-600">{{ prod.promotion_discount }}%</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <Button
                                                type="button"
                                                @click.stop="addProduct(prod)"
                                                :disabled="selectedIds.has(prod.id)"
                                                class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white shadow-lg shadow-blue-600/30 transition-all hover:bg-blue-700 hover:scale-105 hover:shadow-blue-600/50 disabled:bg-gray-300 disabled:text-gray-500 disabled:hover:scale-100 disabled:shadow-none"
                                            >
                                                Agregar
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <InputError :message="deleteError" class="mt-4" />
                    </section>

                    <!-- Tarjeta: Creación de Combos -->
                    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <div class="mb-5 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <h2 class="text-lg font-bold text-gray-900">
                                {{ isEditing ? 'Editar Combo' : 'Crear Nuevo Combo' }}
                            </h2>
                        </div>
                        
                        <!-- Tabla de Productos Seleccionados -->
                        <div class="mb-6 max-h-64 overflow-y-auto rounded-lg border border-gray-200">
                            <table class="w-full text-sm">
                                <thead class="sticky top-0 bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Producto</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">P/U</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Dto.</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">P. Dto.</th>
                                        <th class="px-4 py-3 text-left font-semibold text-gray-700">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-if="AddProduct.length === 0">
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            No hay productos en el combo
                                        </td>
                                    </tr>
                                    <tr v-for="(item, index) in AddProduct" :key="index" class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">#{{ item.id }}</span>
                                        </td>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ item.name }}</td>
                                        <td class="px-4 py-3 text-gray-700">S/ {{ item.unit_price }}</td>
                                        <td class="px-4 py-3">
                                            <span class="font-medium text-orange-600">{{ item.promotion_discount }}%</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-medium text-emerald-600">S/ {{ item.price_discount }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <Button
                                                type="button"
                                                @click.stop="removeProduct(index)"
                                                class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition-all hover:bg-red-100"
                                            >
                                                Eliminar
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Formulario -->
                        <div class="space-y-4">
                            <div>
                                <Label for="name_promotion" class="text-sm font-medium text-gray-700">
                                    Nombre de la Promoción
                                </Label>
                                <Input
                                    id="name_promotion"
                                    v-model="form.name_promotion"
                                    type="text"
                                    placeholder="Ej: Promo Verano, 2x1..."
                                    class="mt-1 w-full rounded-lg border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
                                />
                                <InputError :message="form.errors.name_promotion" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label for="total" class="text-sm font-medium text-gray-700">Total del Combo</Label>
                                    <Input
                                        id="total"
                                        :model-value="'S/ ' + total.toFixed(2)"
                                        disabled
                                        type="text"
                                        class="mt-1 w-full bg-gray-50 text-gray-900 font-semibold"
                                    />
                                </div>

                                <div>
                                    <Label for="state" class="text-sm font-medium text-gray-700">Estado</Label>
                                    <select
                                        id="state"
                                        v-model="form.state"
                                        class="mt-1 w-full rounded-lg border-gray-300 px-3 py-2 text-gray-700 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
                                    >
                                        <option value="" disabled selected>Seleccione estado</option>
                                        <option value="active">Activo</option>
                                        <option value="inactive">Inactivo</option>
                                    </select>
                                    <InputError :message="form.errors.state" />
                                </div>
                            </div>

                            <div class="flex items-center gap-3 pt-2">
                                <Button 
                                    @click="crearPromotion" 
                                    :disabled="form.processing"
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/30 transition-all hover:bg-blue-700 hover:scale-105 hover:shadow-blue-600/50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                                >
                                   
                                    {{ isEditing ? 'Actualizar Promoción' : 'Crear Promoción' }}
                                </Button>
                                <Button 
                                    v-if="isEditing" 
                                    type="button" 
                                    variant="secondary"
                                    :disabled="form.processing || deleteForm.processing" 
                                    @click="resetData"
                                    class="rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 transition-all hover:bg-gray-50 disabled:opacity-50"
                                >
                                    Cancelar
                                </Button>
                            </div>
                        </div>  
                    </section>
                </div>
            </div>
        </main>
    </AppLayout>
</template>
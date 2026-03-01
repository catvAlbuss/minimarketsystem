<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';
import ProductsController from '@/actions/App/Http/Controllers/ProductsController';
import InputError from '@/components/InputError.vue';
import { index as productsIndex } from '@/routes/products';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Productos',
        href: productsIndex().url,
    },
];

type Category = {
    id: number;
    name: string;
}

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
};

type props = {
    products: Products[];
    categories: Category[];
};

const props = defineProps<props>();
const products = computed(() => props.products);
const categories = computed(() => props.categories)

const editingId = ref<number | null>(null);

const form = useForm({
    id_categories: props.categories?.[0]?.id ?? '',
    code: '',
    name: '',
    description: '',
    unit_price: 0,
    higher_price: 0,
    stock: 0,
    expiration_date: '',
    promotion_discount: 0,
    state: 'active',
});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);

const isEditing = computed(() => editingId.value !== null);

const resetForm = (): void => {
    editingId.value=null;
    form.reset();
    form.clearErrors();
    form.id_categories = props.categories?.[0]?.id ?? '';
};

const startEdit = (products: Products): void => {
    editingId.value = products.id;
    form.clearErrors();
    form.id_categories = products.id_categories ?? props.categories?.[0]?.id ?? '';
    form.code = products.code;
    form.name = products.name;
    form.description = products.description;
    form.unit_price = products.unit_price;
    form.higher_price = products.higher_price ?? 0;
    form.stock = products.stock;
    form.expiration_date = products.expiration_date;
    form.promotion_discount = products.promotion_discount;
    form.state = products.state;
};

const submit = (): void => {
    const options = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };
    if (isEditing.value && editingId.value !== null) {
        form.put(ProductsController.update.url(editingId.value), options);
        return;
    }
    form.post(ProductsController.store.url(), options);
};

const remove = (products: Products): void => {
    if (!confirm(`Eliminar producto "${products.name}"?`)) {
        return;
    }

    deleteForm.delete(ProductsController.destroy.url(products.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Productos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        
        <!-- Main Content -->
        <main class="min-h-screen bg-gray-50 p-6">
            
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Gestión de Productos</h1>
                </div>
            </div>

            <!-- KPIs Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Productos</p>
                            <h3 class="mt-1 text-3xl font-bold text-gray-900">{{ products.length }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Stock Bajo</p>
                            <h3 class="mt-1 text-3xl font-bold text-orange-600">{{ products.filter(p => p.stock < 10).length }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-50">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Por Vencer</p>
                            <h3 class="mt-1 text-3xl font-bold text-red-600">{{ products.filter(p => new Date(p.expiration_date) < new Date(Date.now() + 7*24*60*60*1000)).length }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-50">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Valor Inventario</p>
                            <h3 class="mt-1 text-3xl font-bold text-green-600">S/ {{ products.reduce((sum, p) => sum + (Number(p.unit_price) * p.stock), 0).toFixed(2) }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-50">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Producto -->
            <section class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            {{ isEditing ? ' Editar producto' : ' Nuevo producto' }}
                        </h2>
                    </div>
                </div>

                <form class="grid gap-5 md:grid-cols-2 lg:grid-cols-3" @submit.prevent="submit">
                    
                    <!-- Categoría -->
                    <div class="space-y-2">
                        <Label for="id_categories" class="text-sm font-medium text-gray-700">Categoría </Label>
                        <select 
                            id="id_categories" 
                            v-model="form.id_categories" 
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <option value="" disabled>Seleccione</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <InputError :message="form.errors.id_categories" />
                    </div>

                    <!-- Código -->
                    <div class="space-y-2">
                        <Label for="code" class="text-sm font-medium text-gray-700">Código </Label>
                        <Input 
                            id="code" 
                            v-model="form.code" 
                            type="text" 
                            placeholder="Ej: PROD-001"
                            required
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.code" />
                    </div>

                    <!-- Nombre -->
                    <div class="space-y-2">
                        <Label for="name" class="text-sm font-medium text-gray-700">Nombre </Label>
                        <Input 
                            id="name" 
                            v-model="form.name" 
                            type="text" 
                            placeholder="Ej: Yogurt Natural"
                            required
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Descripción -->
                    <div class="space-y-2 md:col-span-2">
                        <Label for="description" class="text-sm font-medium text-gray-700">Descripción </Label>
                        <Input 
                            id="description" 
                            v-model="form.description" 
                            type="text" 
                            placeholder="Descripción del producto"
                            required
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <!-- Precio Unitario -->
                    <div class="space-y-2">
                        <Label for="unit_price" class="text-sm font-medium text-gray-700">Precio Unitario </Label>
                        <Input 
                            id="unit_price" 
                            v-model="form.unit_price" 
                            type="number" 
                            step="0.01"
                            placeholder="0.00"
                            required
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.unit_price" />
                    </div>

                    <!-- Precio Mayor -->
                    <div class="space-y-2">
                        <Label for="higher_price" class="text-sm font-medium text-gray-700">Precio al Mayor</Label>
                        <Input 
                            id="higher_price" 
                            v-model="form.higher_price" 
                            type="number" 
                            step="0.01"
                            placeholder="0.00"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.higher_price" />
                    </div>

                    <!-- Stock -->
                    <div class="space-y-2">
                        <Label for="stock" class="text-sm font-medium text-gray-700">Stock </Label>
                        <Input 
                            id="stock" 
                            v-model="form.stock" 
                            type="number" 
                            placeholder="0"
                            required
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.stock" />
                    </div>

                    <!-- Fecha Vencimiento -->
                    <div class="space-y-2">
                        <Label for="expiration_date" class="text-sm font-medium text-gray-700">Fecha de Vencimiento </Label>
                        <Input 
                            id="expiration_date" 
                            v-model="form.expiration_date" 
                            type="date"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.expiration_date" />
                    </div>

                    <!-- Descuento -->
                    <div class="space-y-2">
                        <Label for="promotion_discount" class="text-sm font-medium text-gray-700">Descuento %</Label>
                        <Input 
                            id="promotion_discount" 
                            v-model="form.promotion_discount" 
                            type="number" 
                            min="0"
                            max="100"
                            placeholder="0"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.promotion_discount" />
                    </div>

                    <!-- Estado -->
                    <div class="space-y-2">
                        <Label for="state" class="text-sm font-medium text-gray-700">Estado </Label>
                        <select 
                            id="state" 
                            v-model="form.state" 
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <option value="active">🟢 Activo</option>
                            <option value="inactive">🔴 Inactivo</option>
                        </select>
                        <InputError :message="form.errors.state" />
                    </div>

                    <!-- Botones -->
                    <div class="col-span-full flex gap-3 pt-2">
                        <Button 
                            type="submit" 
                            :disabled="form.processing || deleteForm.processing"
                            :class="['inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/30 transition-all hover:bg-blue-700 hover:scale-105 hover:shadow-blue-600/50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100']"
                        >
                            <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            {{ isEditing ? ' Actualizar' : ' Crear Producto' }}
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

            <!-- Tabla de Productos -->
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
                                <h2 class="text-lg font-bold text-gray-900">Listado de Productos</h2>
                                <p class="text-sm text-gray-500">{{ products.length }} productos registrados</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2">
                            <span class="text-sm font-semibold text-blue-700">{{ products.length }}</span>
                            <span class="text-sm text-blue-600">productos</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Producto</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Código</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Categoría</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Precio</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Stock</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Vencimiento</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Estado</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="products.length === 0">
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        <p class="text-gray-500">No hay productos registrados</p>
                                        <button 
                                            @click="editingId = null"
                                            class="mt-2 inline-flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors"
                                        >
                                            + Agregar primer producto
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr 
                                v-for="p in products" 
                                :key="p.id" 
                                class="border-t border-gray-100 transition-colors hover:bg-gray-50"
                            >
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ p.name }}</p>
                                        <p class="text-xs text-gray-500 line-clamp-1">{{ p.description }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{ p.code }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ categories.find(c => c.id === p.id_categories)?.name || 'Sin categoría' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-semibold text-gray-900">S/ {{ Number(p.unit_price).toFixed(2) }}</p>
                                        <p v-if="p.higher_price" class="text-xs text-gray-500">Mayor: S/ {{ Number(p.higher_price).toFixed(2) }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span 
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium"
                                        :class="p.stock < 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
                                    >
                                        <span :class="p.stock < 10 ? 'bg-red-600' : 'bg-green-600'" class="h-1.5 w-1.5 rounded-full"></span>
                                        {{ p.stock }} unid.
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ new Date(p.expiration_date).toLocaleDateString('es-PE') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span 
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium"
                                        :class="p.state === 'active' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'"
                                    >
                                        <span :class="p.state === 'active' ? 'bg-blue-600' : 'bg-gray-600'" class="h-1.5 w-1.5 rounded-full"></span>
                                        {{ p.state === 'active' ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Button 
                                            type="button" 
                                            variant="secondary" 
                                            size="sm"
                                            :disabled="form.processing || deleteForm.processing"
                                            @click="startEdit(p)"
                                            :class="['rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition-all hover:bg-gray-50 disabled:opacity-50']"
                                        >
                                            Editar
                                        </Button>
                                        <Button 
                                            type="button" 
                                            variant="destructive" 
                                            size="sm"
                                            :disabled="form.processing || deleteForm.processing" 
                                            @click="remove(p)"
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


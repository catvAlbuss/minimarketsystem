<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';
import CategoryController from '@/actions/App/Http/Controllers/CategoryController';
import InputError from '@/components/InputError.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Categorías',
        href: './categories',
    },
];

type Category = {
    id: number;
    name: string;
    description: string;
};

type props = {
    categories: Category[];
};

const props = defineProps<props>();
const categories = computed(() => props.categories);

const editingId = ref<number | null>(null);

const form = useForm({
    name:'',
    description:'',
});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);

const isEditing = computed(() => editingId.value !== null);

const resetForm = (): void => {
    form.reset();
    deleteForm.reset();
};

const startEdit = (categories: Category): void => {
    editingId.value = categories.id;
    form.clearErrors();
    form.name =  categories.name;
    form.description = categories.description;
};

const submit = (): void => {
    const data = JSON.stringify(form);
    console.log('Submitting form with data:', data);
    const options = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };
    if (isEditing.value && editingId.value !== null) {
        form.put(CategoryController.update.url(editingId.value), options);
        return;
    }
    form.post(CategoryController.store.url(), options);
};

const remove = (categories: Category): void => {
    if (!confirm(`Eliminar categoría "${categories.name}"?`)) {
        return;
    }

    deleteForm.delete(CategoryController.destroy.url(categories.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Categorías" />

    <AppLayout :breadcrumbs="breadcrumbs">
        
        <!-- Main Content -->
        <main class="min-h-screen bg-gray-50 p-6">
            
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Gestión de Categorías</h1>
                </div>
            </div>

            <!-- KPIs Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Categorías</p>
                            <h3 class="mt-1 text-3xl font-bold text-gray-900">{{ categories.length }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Productos Asociados</p>
                            <h3 class="mt-1 text-3xl font-bold text-emerald-600">{{ categories.reduce((sum, c) => sum + (c.description ? 1 : 0), 0) }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Con Descripción</p>
                            <h3 class="mt-1 text-3xl font-bold text-purple-600">{{ categories.filter(c => c.description && c.description.length > 10).length }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Última Actualización</p>
                            <h3 class="mt-1 text-3xl font-bold text-orange-600">Hoy</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-50">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Categoría -->
            <section class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            {{ isEditing ? ' Editar categoría' : ' Nueva Categoría' }}
                        </h2>
                    </div>
                </div>

                <form class="grid gap-5 md:grid-cols-2" @submit.prevent="submit">
                    <!-- Nombre -->
                    <div class="space-y-2">
                        <Label for="name" class="text-sm font-medium text-gray-700">Nombre </Label>
                        <Input 
                            id="name" 
                            v-model="form.name" 
                            type="text" 
                            placeholder="Ej: Lácteos"
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
                            placeholder="Ej: Productos lácteos: leche, queso, yogurt, etc."
                            required
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <!-- Botones -->
                    <div class="col-span-full flex gap-3 pt-2">
                        <Button 
                            type="submit" 
                            :disabled="form.processing || deleteForm.processing"
                            :class="['inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/30 transition-all hover:bg-blue-700 hover:scale-105 hover:shadow-blue-600/50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100']"
                        >
                            <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            {{ isEditing ? ' Actualizar' : ' Crear Categoría' }}
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

            <!-- Tabla de Categorías -->
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Listado de Categorías</h2>
                                <p class="text-sm text-gray-500">{{ categories.length }} categorías registradas</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2">
                            <span class="text-sm font-semibold text-blue-700">{{ categories.length }}</span>
                            <span class="text-sm text-blue-600">categorías</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Nombre</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Descripción</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-if="categories.length === 0">
                                <td colspan="4" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                        <p class="text-gray-500">No hay categorías registradas</p>
                                    </div>
                                </td>
                            </tr>
                            <tr 
                                v-for="category in categories" 
                                :key="category.id" 
                                class="border-t border-gray-100 transition-colors hover:bg-gray-50"
                            >
                                <td class="px-4 py-3">
                                    <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">#{{ category.id }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100">
                                            <span class="text-sm font-bold text-blue-700">{{ category.name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <p class="font-semibold text-gray-900">{{ category.name }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    <p class="line-clamp-2">{{ category.description }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Button 
                                            type="button" 
                                            variant="secondary" 
                                            size="sm"
                                            :disabled="form.processing || deleteForm.processing"
                                            @click="startEdit(category)"
                                            :class="['rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition-all hover:bg-gray-50 disabled:opacity-50']"
                                        >
                                            Editar
                                        </Button>
                                        <Button 
                                            type="button" 
                                            variant="destructive" 
                                            size="sm"
                                            :disabled="form.processing || deleteForm.processing" 
                                            @click="remove(category)"
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


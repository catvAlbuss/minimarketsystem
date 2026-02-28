<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { index as productsIndex } from '@/routes/products';
import BranchController from '@/actions/App/Http/Controllers/BranchController';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sucursales',
        href: '/branches',
    },
];

type Users = {
    id: number;
    name: string;
}

type Branches = {
    id: number;
    id_users: number | null;
    name: string;
    address: string;
    opening_time: string;
    closing_time: string;
    state: string;
};

type Props = {
    branches: Branches[];
    users: Users[];
};

const props = defineProps<Props>();
const branches = computed(() => props.branches);
const users = computed(() => props.users);

const editingId = ref<number | null>(null);

const form = useForm({
    id_users: props.users?.[0]?.id ?? '',
    address: '',
    name: '',
    opening_time: '',
    closing_time: '',
    state: '',
});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);

const isEditing = computed(() => editingId.value !== null);

// KPIs Computados
const totalSucursales = computed(() => branches.value.length);
const sucursalesActivas = computed(() => branches.value.filter(b => b.state === 'active').length);
const usuariosAsignados = computed(() => new Set(branches.value.map(b => b.id_users).filter(id => id !== null)).size);

const resetForm = (): void => {
    editingId.value = null;
    form.reset();
    form.clearErrors();
    form.id_users = props.users?.[0]?.id ?? '';
};

const startEdit = (branch: Branches): void => {
    editingId.value = branch.id;
    form.clearErrors();
    form.id_users = branch.id_users ?? props.users?.[0]?.id ?? '';
    form.name = branch.name;
    form.address = branch.address;
    form.opening_time = branch.opening_time;
    form.closing_time = branch.closing_time;
    form.state = branch.state;
};

const submit = (): void => {
    const options = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };
    if (isEditing.value && editingId.value !== null) {
        form.put(BranchController.update.url(editingId.value), options);
        return;
    }
    form.post(BranchController.store.url(), options);
};

const remove = (branch: Branches): void => {
    if (!confirm(`Eliminar sucursal "${branch.name}"?`)) {
        return;
    }

    deleteForm.delete(BranchController.destroy.url(branch.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Sucursales" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Main Content -->
        <main class="min-h-screen bg-gray-50 p-6">
            <div class="mx-auto max-w-7xl">
                
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Gestión de Sucursales</h1>
                        
                    </div>
                </div>

                <!-- KPIs Cards -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Sucursales -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Sucursales</p>
                                <h3 class="mt-1 text-3xl font-bold text-gray-900">{{ totalSucursales }}</h3>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Sucursales Activas -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Sucursales Activas</p>
                                <h3 class="mt-1 text-3xl font-bold text-emerald-600">{{ sucursalesActivas }}</h3>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50">
                                <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Usuarios Asignados -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Usuarios Asignados</p>
                                <h3 class="mt-1 text-3xl font-bold text-purple-600">{{ usuariosAsignados }}</h3>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Horario Promedio -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Horario Promedio</p>
                                <h3 class="mt-1 text-3xl font-bold text-orange-600">8h</h3>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-50">
                                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de Sucursal -->
                <section class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <h2 class="text-lg font-bold text-gray-900">
                            {{ isEditing ? 'Editar Sucursal' : 'Nueva Sucursal' }}
                        </h2>
                    </div>

                    <form class="grid gap-5 md:grid-cols-2" @submit.prevent="submit">
                        <!-- Usuario - SELECT -->
                        <div class="space-y-2">
                            <Label for="id_users" class="text-sm font-medium text-gray-700">Usuario</Label>
                            <select 
                                id="id_users" 
                                v-model="form.id_users" 
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none"
                            >
                                <option value="" disabled>Seleccione un usuario</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.id_users" />
                        </div>

                        <!-- Nombre -->
                        <div class="space-y-2">
                            <Label for="name" class="text-sm font-medium text-gray-700">Nombre</Label>
                            <input 
                                id="name" 
                                v-model="form.name" 
                                type="text" 
                                placeholder="Ej: Sucursal Centro" 
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none"
                            />
                            <InputError :message="form.errors.name" />
                        </div>

                        <!-- Dirección (ocupa las 2 columnas) -->
                        <div class="space-y-2 md:col-span-2">
                            <Label for="address" class="text-sm font-medium text-gray-700">Dirección</Label>
                            <input 
                                id="address" 
                                v-model="form.address" 
                                type="text" 
                                placeholder="Ej: Av. Principal 123" 
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none"
                            />
                            <InputError :message="form.errors.address" />
                        </div>

                        <!-- Hora apertura -->
                        <div class="space-y-2">
                            <Label for="opening_time" class="text-sm font-medium text-gray-700">Hora Apertura</Label>
                            <input 
                                id="opening_time" 
                                v-model="form.opening_time" 
                                type="time" 
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none"
                            />
                            <InputError :message="form.errors.opening_time" />
                        </div>

                        <!-- Hora cierre -->
                        <div class="space-y-2">
                            <Label for="closing_time" class="text-sm font-medium text-gray-700">Hora Cierre</Label>
                            <input 
                                id="closing_time" 
                                v-model="form.closing_time" 
                                type="time" 
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none"
                            />
                            <InputError :message="form.errors.closing_time" />
                        </div>

                        <!-- Estado -->
                        <div class="space-y-2">
                            <Label for="state" class="text-sm font-medium text-gray-700">Estado</Label>
                            <select 
                                id="state" 
                                v-model="form.state" 
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20 focus:outline-none"
                            >
                                <option value="" disabled>Seleccione estado</option>
                                <option value="active">Activo</option>
                                <option value="inactive">Inactivo</option>
                            </select>
                            <InputError :message="form.errors.state" />
                        </div>

                        <!-- Botones (ocupan las 2 columnas) -->
                        <div class="col-span-full flex gap-3 pt-4">
                            <button 
                                type="submit" 
                                :disabled="form.processing || deleteForm.processing"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/30 transition-all hover:bg-blue-700 hover:scale-105 hover:shadow-blue-600/50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                            >
                                <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                {{ isEditing ? 'Actualizar Sucursal' : 'Crear Sucursal' }}
                            </button>
                            <button 
                                v-if="isEditing" 
                                type="button" 
                                variant="secondary"
                                :disabled="form.processing || deleteForm.processing" 
                                @click="resetForm"
                                class="rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-medium text-gray-700 transition-all hover:bg-gray-50 disabled:opacity-50"
                            >
                                Cancelar
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Tabla de Sucursales -->
                <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-gray-900">Listado de Sucursales</h2>
                                    <p class="text-sm text-gray-500">{{ totalSucursales }} sucursales registradas</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2">
                                <span class="text-sm font-semibold text-blue-700">{{ totalSucursales }}</span>
                                <span class="text-sm text-blue-600">sucursales</span>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">ID</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Usuario</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Nombre</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Dirección</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Apertura</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Cierre</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Estado</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-700">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-if="branches.length === 0">
                                    <td colspan="8" class="px-4 py-12 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            <p class="text-gray-500">No hay sucursales registradas</p>
                                        </div>
                                    </td>
                                </tr>
                                <tr 
                                    v-for="p in branches" 
                                    :key="p.id" 
                                    class="transition-colors hover:bg-gray-50"
                                >
                                    <td class="px-4 py-3">
                                        <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">#{{ p.id }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-100">
                                                <span class="text-xs font-bold text-blue-700">{{ users.find(u => u.id === p.id_users)?.name?.charAt(0).toUpperCase() || '?' }}</span>
                                            </div>
                                            <span class="text-gray-600">{{ users.find(u => u.id === p.id_users)?.name || 'Sin asignar' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ p.name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ p.address }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ p.opening_time }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ p.closing_time }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span 
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                            :class="p.state === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700'"
                                        >
                                            {{ p.state === 'active' ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <button 
                                                type="button" 
                                                @click="startEdit(p)"
                                                :disabled="form.processing || deleteForm.processing"
                                                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition-all hover:bg-gray-50 disabled:opacity-50"
                                            >
                                                Editar
                                            </button>
                                            <button 
                                                type="button" 
                                                @click="remove(p)"
                                                :disabled="form.processing || deleteForm.processing" 
                                                class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-medium text-red-600 transition-all hover:bg-red-100 disabled:opacity-50"
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
            </div>
        </main>
    </AppLayout>
</template>
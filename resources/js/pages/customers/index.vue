<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';
import CustomerController from '@/actions/App/Http/Controllers/CustomerController';
import InputError from '@/components/InputError.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clientes',
        href: './customers',
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

type props = {
    customers: Customer[];
};

const props = defineProps<props>();
const customer = computed(() => props.customers);

const editingId = ref<number | null>(null);

const form = useForm({
    dni: '',
    name: '',
    last_name: '',
    birthday: '',
    email: '',
    phone: '',
    address: '',
    score: 0,
    state: 'active',
});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);

const isEditing = computed(() => editingId.value !== null);

const resetForm = (): void => {
    editingId.value=null;
    form.reset();
    form.clearErrors();
};

const startEdit = (customer: Customer): void => {
    editingId.value = customer.id;
    form.clearErrors();
    form.dni = customer.dni;
    form.name = customer.name;
    form.last_name = customer.last_name;
    form.birthday = customer.birthday;
    form.email = customer.email;
    form.phone = customer.phone;
    form.address = customer.address;
    form.score = customer.score;
    form.state = customer.state;
};

const submit = (): void => {
    const data = JSON.stringify(form);
    console.log('Submitting form with data:', data);
    const options = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };
    if (isEditing.value && editingId.value !== null) {
        form.put(CustomerController.update.url(editingId.value), options);
        return;
    }
    form.post(CustomerController.store.url(), options);
};

const remove = (customer: Customer): void => {
    if (!confirm(`Eliminar cliente "${customer.name}"?`)) {
        return;
    }

    deleteForm.delete(CustomerController.destroy.url(customer.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Clientes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        
        <!-- Main Content -->
        <main class="min-h-screen bg-gray-50 p-6">
            
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Gestión de Clientes</h1>
                </div>
            </div>

            <!-- KPIs Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Clientes</p>
                            <h3 class="mt-1 text-3xl font-bold text-gray-900">{{ customer.length }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Clientes Activos</p>
                            <h3 class="mt-1 text-3xl font-bold text-green-600">{{ customer.filter(c => c.state === 'active').length }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-50">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Puntos Totales</p>
                            <h3 class="mt-1 text-3xl font-bold text-amber-600">{{ customer.reduce((sum, c) => sum + (c.score || 0), 0) }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-50">
                            <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Cumpleaños este Mes</p>
                            <h3 class="mt-1 text-3xl font-bold text-pink-600">{{ customer.filter(c => new Date(c.birthday).getMonth() === new Date().getMonth()).length }}</h3>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-pink-50">
                            <svg class="h-6 w-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Cliente -->
            <section class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            {{ isEditing ? ' Editar cliente' : ' Nuevo cliente' }}
                        </h2>
                    </div>
                </div>

                <form class="grid gap-5 md:grid-cols-2 lg:grid-cols-3" @submit.prevent="submit">
                    
                    <!-- Nombre -->
                    <div class="space-y-2">
                        <Label for="name" class="text-sm font-medium text-gray-700">Nombre </Label>
                        <Input 
                            id="name" 
                            v-model="form.name" 
                            type="text" 
                            placeholder="Ej: Juan"
                            required
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <!-- Apellido -->
                    <div class="space-y-2">
                        <Label for="last_name" class="text-sm font-medium text-gray-700">Apellido </Label>
                        <Input 
                            id="last_name" 
                            v-model="form.last_name" 
                            type="text" 
                            placeholder="Ej: Pérez"
                            required
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.last_name" />
                    </div>

                    <!-- DNI -->
                    <div class="space-y-2">
                        <Label for="dni" class="text-sm font-medium text-gray-700">DNI </Label>
                        <Input 
                            id="dni" 
                            v-model="form.dni" 
                            type="text" 
                            placeholder="Ej: 12345678"
                            required
                            :readonly="isEditing"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:opacity-60']"
                        />
                        <InputError :message="form.errors.dni" />
                    </div>

                    <!-- Celular -->
                    <div class="space-y-2">
                        <Label for="phone" class="text-sm font-medium text-gray-700">Celular </Label>
                        <Input 
                            id="phone" 
                            v-model="form.phone" 
                            type="tel" 
                            placeholder="Ej: 987654321"
                            required
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.phone" />
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <Label for="email" class="text-sm font-medium text-gray-700">Email </Label>
                        <Input 
                            id="email" 
                            v-model="form.email" 
                            type="email" 
                            placeholder="Ej: juan@email.com"
                            required
                            :readonly="isEditing"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:opacity-60']"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <!-- Dirección -->
                    <div class="space-y-2 md:col-span-2">
                        <Label for="address" class="text-sm font-medium text-gray-700">Dirección </Label>
                        <Input 
                            id="address" 
                            v-model="form.address" 
                            type="text" 
                            placeholder="Ej: Av. Principal 123, Distrito"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.address" />
                    </div>

                    <!-- Cumpleaños -->
                    <div class="space-y-2">
                        <Label for="birthday" class="text-sm font-medium text-gray-700">Cumpleaños </Label>
                        <Input 
                            id="birthday" 
                            v-model="form.birthday" 
                            type="date"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.birthday" />
                    </div>

                    <!-- Puntos/Score -->
                    <div class="space-y-2">
                        <Label for="score" class="text-sm font-medium text-gray-700">Puntos </Label>
                        <Input 
                            id="score" 
                            v-model="form.score" 
                            type="number" 
                            min="0"
                            placeholder="0"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.score" />
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
                            {{ isEditing ? ' Actualizar' : ' Crear Cliente' }}
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

            <!-- Tabla de Clientes -->
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Listado de Clientes</h2>
                                <p class="text-sm text-gray-500">{{ customer.length }} clientes registrados</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2">
                            <span class="text-sm font-semibold text-blue-700">{{ customer.length }}</span>
                            <span class="text-sm text-blue-600">clientes</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Cliente</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">DNI</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Contacto</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Cumpleaños</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Puntos</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Estado</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="customer.length === 0">
                                <td colspan="7" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <p class="text-gray-500">No hay clientes registrados</p>
                                    </div>
                                </td>
                            </tr>
                            <tr 
                                v-for="c in customer" 
                                :key="c.id" 
                                class="border-t border-gray-100 transition-colors hover:bg-gray-50"
                            >
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100">
                                            <span class="text-sm font-bold text-blue-700">{{ c.name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900">{{ c.name }} {{ c.last_name }}</p>
                                            <p class="text-xs text-gray-500">{{ c.address?.substring(0, 30) }}{{ c.address?.length > 30 ? '...' : '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{ c.dni }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="space-y-1">
                                        <p class="text-gray-600"> {{ c.phone || '-' }}</p>
                                        <p class="text-gray-600"> {{ c.email || '-' }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ c.birthday ? new Date(c.birthday).toLocaleDateString('es-PE') : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">
                                        ⭐ {{ c.score || 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span 
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium"
                                        :class="c.state === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                    >
                                        <span :class="c.state === 'active' ? 'bg-green-600' : 'bg-red-600'" class="h-1.5 w-1.5 rounded-full"></span>
                                        {{ c.state === 'active' ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Button 
                                            type="button" 
                                            variant="secondary" 
                                            size="sm"
                                            :disabled="form.processing || deleteForm.processing"
                                            @click="startEdit(c)"
                                            :class="['rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition-all hover:bg-gray-50 disabled:opacity-50']"
                                        >
                                            Editar
                                        </Button>
                                        <Button 
                                            type="button" 
                                            variant="destructive" 
                                            size="sm"
                                            :disabled="form.processing || deleteForm.processing" 
                                            @click="remove(c)"
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
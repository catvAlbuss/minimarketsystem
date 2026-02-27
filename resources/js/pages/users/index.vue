<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref, computed } from 'vue';
import UserController from '@/actions/App/Http/Controllers/UserController';


import InputError from '@/components/InputError.vue';
 

type Users = {
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

type Props = {
    users: Users[];
};

const props = defineProps<Props>();
const users = computed(() => props.users);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Usuarios', href: '/' },
];

// ── Estado UI ──
const editingId    = ref<number | null>(null);
const showModal    = ref(false);
const searchQuery  = ref('');
const isEditing    = computed(() => editingId.value !== null);

const usuariosFiltrados = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return users.value;
    return users.value.filter(u =>
        u.name.toLowerCase().includes(q) ||
        u.lastname.toLowerCase().includes(q) ||
        u.email.toLowerCase().includes(q) ||
        String(u.dni).includes(q)
    );
});

// ── KPIs ──
const totalUsuarios  = computed(() => users.value.length);
const usuariosActivos = computed(() => users.value.filter(u => u.state === 'active').length);
const admins         = computed(() => users.value.filter(u => ['root','managment','administrator_general','administrator'].includes(u.role)).length);

// ── Formulario ──
const form = useForm({
    name: '',
    lastname: '',
    dni: '' as number | '',
    phone: '' as number | '',
    address: '',
    children: '' as number | '',
    affiliate: '',
    insured: '',
    work_modality: '',
    entry_date: '',
    retention: '',
    entry_to_payroll: '',
    role: '',
    state: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({});

const resetForm = (): void => {
    editingId.value = null;
    form.reset();
    showModal.value = false;
};

const submit = (): void => {
    const option = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };
    if (isEditing.value && editingId.value !== null) {
        form.put(UserController.update.url(editingId.value), option);
        return;
    }
    form.post(UserController.store.url(), option);
};

const prepararNuevo = (): void => {
    editingId.value = null;
    form.reset();
    showModal.value = true;
};

const startEdit = (user: Users): void => {
    editingId.value = user.id;
    form.clearErrors();
    form.name               = user.name;
    form.lastname           = user.lastname;
    form.dni                = user.dni || '';
    form.phone              = user.phone || '';
    form.address            = user.address;
    form.email              = user.email;
    form.password           = '';
    form.password_confirmation = '';
    form.children           = user.children ?? '';
    form.affiliate          = user.affiliate;
    form.insured            = user.insured;
    form.work_modality      = user.work_modality;
    form.entry_date         = user.entry_date;
    form.retention          = user.retention;
    form.entry_to_payroll   = user.entry_to_payroll;
    form.role               = user.role;
    form.state              = user.state;
    showModal.value = true;
};

const remove = (user: Users): void => {
    if (!confirm(`¿Eliminar al usuario "${user.name} ${user.lastname}"?`)) return;
    deleteForm.delete(UserController.destroy.url(user.id), { preserveScroll: true });
};

const roleLabel = (role: string) => ({
    root: 'Root',
    managment: 'Gerencia',
    administrator_general: 'Admin. General',
    logistic_general: 'Logística General',
    administrator: 'Administrador',
    logistic: 'Logística',
    cashier: 'Cajero',
    asistente: 'Asistente',
}[role] ?? role);

const roleBadgeClass = (role: string) => {
    const map: Record<string, string> = {
        root: 'bg-purple-100 text-purple-700',
        managment: 'bg-blue-100 text-blue-700',
        administrator_general: 'bg-indigo-100 text-indigo-700',
        logistic_general: 'bg-cyan-100 text-cyan-700',
        administrator: 'bg-sky-100 text-sky-700',
        logistic: 'bg-teal-100 text-teal-700',
        cashier: 'bg-green-100 text-green-700',
        asistente: 'bg-gray-100 text-gray-600',
    };
    return map[role] ?? 'bg-gray-100 text-gray-600';
};

const inputClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2b4485] focus:border-[#2b4485] transition-colors';
const selectClass = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#2b4485] focus:border-[#2b4485] transition-colors';
const disabledClass = 'w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-400 cursor-not-allowed';
</script>

<template>
    <Head title="Usuarios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 md:p-8 bg-gray-50 min-h-screen space-y-6">
        <div class="space-y-6 p-4">
            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
                <h1 class="text-xl font-semibold">
                    {{ isEditing ? 'Editar usuario' : 'Nuevo usuario' }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Gestiona usuarios, rol y personal desde esta misma vista.
                </p>
                <form class="mt-4 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="name">Nombre</Label>
                        <Input id="name" class="text-white" v-model="form.name" type="text" required placeholder="Ej: Jorge"/>
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="lastname">Apellido</Label>
                        <Input id="lastname" v-model="form.lastname" type="text" required placeholder="Ej: Almeida" />
 
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="dni">DNI</Label>
                        <Input id="dni" v-model="form.dni" type="text" required/>
                        <InputError :message="form.errors.name" />

                        <InputError :message="form.errors.lastname" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="dni">DNI</Label>
                        <Input :disabled="isEditing" id="dni" v-model="form.dni" type="text" required/>
                        <InputError :message="form.errors.dni" />
 
                    </div>
                    <div class="grid gap-2">
                        <Label for="phone">Celular</Label>
                        <Input id="phone" v-model="form.phone" type="text" required />
 
                        <InputError :message="form.errors.name" />

                        <InputError :message="form.errors.phone" />
 
                    </div>
                    <div class="grid gap-2">
                        <Label for="address">Direccion</Label>
                        <Input id="address" v-model="form.address" type="text" required placeholder="Ej: Jr. 28 Julio"/>
                        <InputError :message="form.errors.address" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="email">Correo</Label>
 
                        <Input id="email" v-model="form.email" type="email" required placeholder="Ej: example@gmail.com"/>

                        <Input :disabled="isEditing" id="email" v-model="form.email" type="email" required placeholder="Ej: example@gmail.com"/>
 
                        <InputError :message="form.errors.email" />
                    </div>

            <!-- ── Header ── -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-5">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Gestión de Usuarios</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Administra roles, accesos y personal del sistema</p>
                </div>
                <button
                    @click="prepararNuevo"
                    class="flex items-center gap-2 bg-[#2b4485] hover:bg-[#1a3366] text-white px-5 py-2.5 rounded-lg font-semibold text-sm transition-colors shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Nuevo Usuario
                </button>
            </div>

            <!-- ── KPIs ── -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-[#2b4485]/10 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-[#2b4485]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Usuarios</p>
                        <p class="text-3xl font-bold text-gray-800 mt-0.5">{{ totalUsuarios }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Activos</p>
                        <p class="text-3xl font-bold text-green-600 mt-0.5">{{ usuariosActivos }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Administradores</p>
                        <p class="text-3xl font-bold text-indigo-600 mt-0.5">{{ admins }}</p>
                    </div>
                </div>
            </div>

            <!-- ── Búsqueda + Tabla ── -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

                <!-- Barra de búsqueda -->
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="relative max-w-sm">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z"/>
                        </svg>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Buscar por nombre, email o DNI..."
                            class="w-full pl-9 pr-8 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#2b4485] focus:border-[#2b4485] focus:bg-white transition-all"
                        />
                        <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-400">
                        <template v-if="searchQuery">
                            <span class="text-[#2b4485] font-medium">{{ usuariosFiltrados.length }}</span> resultado{{ usuariosFiltrados.length !== 1 ? 's' : '' }} para "<span class="font-medium text-gray-600">{{ searchQuery }}</span>"
                        </template>
                        <template v-else>
                            {{ users.length }} usuario{{ users.length !== 1 ? 's' : '' }} registrado{{ users.length !== 1 ? 's' : '' }}
                        </template>
                    </p>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Usuario</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Correo</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">DNI</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Teléfono</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Rol</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-if="usuariosFiltrados.length === 0">
                                <td colspan="7" class="px-5 py-10 text-center text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                                    </svg>
                                    <p class="font-medium text-sm">No hay usuarios registrados</p>
                                </td>
                            </tr>
                            <tr v-for="user in usuariosFiltrados" :key="user.id" class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-[#2b4485] text-white flex items-center justify-center text-xs font-bold shrink-0">
                                            {{ user.name.charAt(0).toUpperCase() }}{{ user.lastname.charAt(0).toUpperCase() }}
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-800 text-sm leading-tight">{{ user.name }} {{ user.lastname }}</p>
                                            <p class="text-xs text-gray-400 mt-0.5">ID #{{ user.id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-gray-600 text-sm">{{ user.email }}</td>
                                <td class="px-5 py-3.5 text-gray-600 font-mono text-sm">{{ user.dni }}</td>
                                <td class="px-5 py-3.5 text-gray-600 text-sm">{{ user.phone }}</td>
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold" :class="roleBadgeClass(user.role)">
                                        {{ roleLabel(user.role) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                        :class="user.state === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full" :class="user.state === 'active' ? 'bg-green-500' : 'bg-gray-400'"></span>
                                        {{ user.state === 'active' ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2 opacity-70 group-hover:opacity-100 transition-opacity">
                                        <button
                                            @click="startEdit(user)"
                                            :disabled="form.processing || deleteForm.processing"
                                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-[#2b4485] border border-[#2b4485]/30 rounded-lg hover:bg-[#2b4485] hover:text-white transition-all disabled:opacity-50"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                                            </svg>
                                            Editar
                                        </button>
                                        <button
                                            @click="remove(user)"
                                            :disabled="form.processing || deleteForm.processing"
                                            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-500 border border-red-200 rounded-lg hover:bg-red-500 hover:text-white transition-all disabled:opacity-50"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                            </svg>
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- ══════════════════════════════════════════════════
         MODAL: Nuevo / Editar Usuario
    ══════════════════════════════════════════════════ -->
    <Teleport to="body">
        <div v-if="showModal" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="resetForm"></div>

            <!-- Panel -->
            <div class="relative z-10 bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[92vh] flex flex-col">

                <!-- Header -->
                <div class="px-7 py-5 border-b border-gray-100 flex justify-between items-center shrink-0">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">
                            {{ isEditing ? 'Editar Usuario' : 'Registrar Nuevo Usuario' }}
                        </h3>
                        <p class="text-sm text-gray-400 mt-0.5">
                            {{ isEditing ? 'Modifica los datos del usuario seleccionado' : 'Completa los campos para crear un nuevo acceso al sistema' }}
                        </p>
                    </div>
                    <button @click="resetForm" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <form @submit.prevent="submit" class="px-7 py-6 overflow-y-auto space-y-6">

                    <!-- Sección: Datos Personales -->
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-1 h-5 bg-[#2b4485] rounded-full"></div>
                            <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Datos Personales</h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre *</label>
                                <input v-model="form.name" type="text" required placeholder="Ej: Jorge" :class="inputClass" />
                                <InputError :message="form.errors.name" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Apellido *</label>
                                <input v-model="form.lastname" type="text" required placeholder="Ej: Almeida" :class="inputClass" />
                                <InputError :message="form.errors.lastname" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    DNI *
                                    <span v-if="isEditing" class="text-xs text-gray-400 font-normal ml-1">(no editable)</span>
                                </label>
                                <input v-model="form.dni" type="text" :disabled="isEditing" required
                                    :class="isEditing ? disabledClass : inputClass" placeholder="12345678" />
                                <InputError :message="form.errors.dni" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Celular *</label>
                                <input v-model="form.phone" type="text" required placeholder="987654321" :class="inputClass" />
                                <InputError :message="form.errors.phone" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Dirección *</label>
                                <input v-model="form.address" type="text" required placeholder="Ej: Jr. 28 de Julio 123" :class="inputClass" />
                                <InputError :message="form.errors.address" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">N° de Hijos</label>
                                <input v-model="form.children" type="number" min="0" placeholder="0" :class="inputClass" />
                                <InputError :message="form.errors.children" />
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <hr class="border-gray-100" />

                    <!-- Sección: Datos Laborales -->
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-1 h-5 bg-indigo-400 rounded-full"></div>
                            <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Datos Laborales</h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Afiliado *</label>
                                <select v-model="form.affiliate" required :class="selectClass">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option value="ONP">ONP</option>
                                    <option value="AFP">AFP</option>
                                </select>
                                <InputError :message="form.errors.affiliate" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Asegurado *</label>
                                <select v-model="form.insured" required :class="selectClass">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option value="EsSalud">EsSalud</option>
                                    <option value="SIS">SIS</option>
                                </select>
                                <InputError :message="form.errors.insured" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Modalidad de Trabajo *</label>
                                <select v-model="form.work_modality" required :class="selectClass">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option value="fullTime">Full Time</option>
                                    <option value="partTime">Part Time</option>
                                </select>
                                <InputError :message="form.errors.work_modality" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Fecha de Ingreso
                                    <span v-if="isEditing" class="text-xs text-gray-400 font-normal ml-1">(opcional)</span>
                                </label>
                                <input v-model="form.entry_date" type="date" :required="!isEditing" :class="inputClass" />
                                <InputError :message="form.errors.entry_date" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Retención *</label>
                                <select v-model="form.retention" required :class="selectClass">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option value="yes">Sí</option>
                                    <option value="no">No</option>
                                </select>
                                <InputError :message="form.errors.retention" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Ingreso a Planilla *</label>
                                <select v-model="form.entry_to_payroll" required :class="selectClass">
                                    <option value="" disabled>Seleccionar...</option>
                                    <option value="yes">Sí</option>
                                    <option value="no">No</option>
                                </select>
                                <InputError :message="form.errors.entry_to_payroll" />
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <hr class="border-gray-100" />

                    <!-- Sección: Acceso al Sistema -->
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-1 h-5 bg-green-400 rounded-full"></div>
                            <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Acceso al Sistema</h4>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Correo Electrónico *
                                    <span v-if="isEditing" class="text-xs text-gray-400 font-normal ml-1">(no editable)</span>
                                </label>
                                <input v-model="form.email" type="email" :disabled="isEditing" required
                                    placeholder="Ej: usuario@empresa.com"
                                    :class="isEditing ? disabledClass : inputClass" />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Rol *</label>
                                <select v-model="form.role" required :class="selectClass">
                                    <option value="" disabled>Seleccionar rol...</option>
                                    <option value="root">Root</option>
                                    <option value="managment">Gerencia</option>
                                    <option value="administrator_general">Administrador General</option>
                                    <option value="logistic_general">Logística General</option>
                                    <option value="administrator">Administrador</option>
                                    <option value="logistic">Logística</option>
                                    <option value="cashier">Cajero</option>
                                    <option value="asistente">Asistente</option>
                                </select>
                                <InputError :message="form.errors.role" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Estado *</label>
                                <select v-model="form.state" required :class="selectClass">
                                    <option value="" disabled>Seleccionar estado...</option>
                                    <option value="active">Activo</option>
                                    <option value="inactive">Inactivo</option>
                                </select>
                                <InputError :message="form.errors.state" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Contraseña
                                    <span v-if="isEditing" class="text-xs text-gray-400 font-normal ml-1">(opcional en edición)</span>
                                    <span v-else class="text-red-500 ml-0.5">*</span>
                                </label>
                                <input v-model="form.password" type="password" :required="!isEditing"
                                    placeholder="Mínimo 8 caracteres" :class="inputClass" />
                                <InputError :message="form.errors.password" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                    Confirmar Contraseña
                                    <span v-if="!isEditing" class="text-red-500 ml-0.5">*</span>
                                </label>
                                <input v-model="form.password_confirmation" type="password" :required="!isEditing"
                                    placeholder="Repite la contraseña" :class="inputClass" />
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Footer -->
                <div class="px-7 py-5 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row-reverse gap-3 shrink-0 rounded-b-2xl">
                    <button
                        type="button" @click="submit"
                        :disabled="form.processing || deleteForm.processing"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 bg-[#2b4485] hover:bg-[#1a3366] disabled:opacity-50 text-white rounded-lg font-semibold text-sm transition-colors"
                    >
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ isEditing ? 'Actualizar Usuario' : 'Crear Usuario' }}
                    </button>
                    <button
                        type="button" @click="resetForm"
                        class="w-full sm:w-auto px-6 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-semibold text-sm transition-colors"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
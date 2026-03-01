<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
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
</script>

<template>
    <Head title="Ventas" />

    <AppLayout :breadcrumbs="breadcrumbs">
        
        <!-- Main Content -->
        <main class="min-h-screen bg-gray-50 p-6">
            
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Gestión de Ventas</h1>
                </div>
            </div>

            <!-- Formulario de Venta -->
            <section class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50">
                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">
                            {{ isEditing ? 'Editar venta' : 'Nueva venta' }}
                        </h2>
                    </div>
                </div>

                <form class="grid gap-5 md:grid-cols-2 lg:grid-cols-3" @submit.prevent="submit">
                    
                    <!-- Cliente -->
                    <div class="space-y-2">
                        <Label for="id_customers" class="text-sm font-medium text-gray-700">Cliente </Label>
                        <select 
                            id="id_customers" 
                            v-model="form.id_customers" 
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <option value="" disabled>Seleccione</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                        <InputError :message="form.errors.id_customers" />
                    </div>

                    <!-- Usuario -->
                    <div class="space-y-2">
                        <Label for="id_users" class="text-sm font-medium text-gray-700">Usuario *</Label>
                        <select 
                            id="id_users" 
                            v-model="form.id_users" 
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <option value="" disabled>Seleccione</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                        <InputError :message="form.errors.id_users" />
                    </div>

                    <!-- Número de Comprobante -->
                    <div class="space-y-2">
                        <Label for="voucher_number" class="text-sm font-medium text-gray-700">N° Comprobante </Label>
                        <Input 
                            id="voucher_number" 
                            v-model="form.voucher_number" 
                            type="text" 
                            placeholder="Ej: F001-000123"
                            required
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.voucher_number" />
                    </div>

                    <!-- IGV -->
                    <div class="space-y-2">
                        <Label for="igv" class="text-sm font-medium text-gray-700">IGV</Label>
                        <Input 
                            id="igv" 
                            v-model="form.igv" 
                            type="number" 
                            step="0.01"
                            placeholder="0.18"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.igv" />
                    </div>

                    <!-- Total -->
                    <div class="space-y-2">
                        <Label for="total" class="text-sm font-medium text-gray-700">Total </Label>
                        <Input 
                            id="total" 
                            v-model="form.total" 
                            type="number" 
                            step="0.01"
                            placeholder="0.00"
                            required
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.total" />
                    </div>

                    <!-- Método de Pago -->
                    <div class="space-y-2">
                        <Label for="payment_method" class="text-sm font-medium text-gray-700">Método de Pago </Label>
                        <select 
                            id="payment_method" 
                            v-model="form.payment_method" 
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <option value="" disabled selected>Seleccione</option>
                            <option value="cash">💵 Efectivo</option>
                            <option value="card">💳 Tarjeta</option>
                            <option value="yape">🟢 Yape</option>
                            <option value="plin">🔵 Plin</option>
                        </select>
                        <InputError :message="form.errors.payment_method" />
                    </div>

                    <!-- Comprobante -->
                    <div class="space-y-2">
                        <Label for="voucher" class="text-sm font-medium text-gray-700">Tipo de Comprobante </Label>
                        <select 
                            id="voucher" 
                            v-model="form.voucher" 
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                        >
                            <option value="" disabled>Seleccione</option>
                            <option value="ticket">🧾 Boleta</option>
                            <option value="invoice">📄 Factura</option>
                        </select>
                        <InputError :message="form.errors.voucher" />
                    </div>

                    <!-- Imagen/Documento -->
                    <div class="space-y-2">
                        <Label for="document" class="text-sm font-medium text-gray-700">Comprobante (IMG)</Label>
                        <Input 
                            id="document" 
                            v-model="form.document" 
                            type="file"
                            accept="image/*"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-blue-700 hover:file:bg-blue-100']"
                        />
                        <InputError :message="form.errors.document" />
                    </div>

                    <!-- Fecha -->
                    <div class="space-y-2">
                        <Label for="date_time" class="text-sm font-medium text-gray-700">Fecha</Label>
                        <Input 
                            id="date_time" 
                            v-model="form.date_time" 
                            type="datetime-local"
                            :class="['w-full rounded-lg border border-gray-300 !bg-white px-4 py-2.5 text-sm text-gray-900 transition-all focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20']"
                        />
                        <InputError :message="form.errors.date_time" />
                    </div>

                    <!-- Botones -->
                    <div class="col-span-full flex gap-3 pt-2">
                        <Button 
                            type="submit" 
                            :disabled="form.processing || deleteForm.processing"
                            :class="['inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/30 transition-all hover:bg-blue-700 hover:scale-105 hover:shadow-blue-600/50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100']"
                        >
                            <span v-if="form.processing" class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                            {{ isEditing ? ' Actualizar' : ' Registrar Venta' }}
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

            <!-- Tabla de Ventas -->
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
                                <h2 class="text-lg font-bold text-gray-900">Listado de Ventas</h2>
                                <p class="text-sm text-gray-500">{{ sales.length }} ventas registradas</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2">
                            <span class="text-sm font-semibold text-blue-700">{{ sales.length }}</span>
                            <span class="text-sm text-blue-600">ventas</span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">N° Venta</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Cliente</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Vendedor</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Comprobante</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Total</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Método</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Fecha</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="sales.length === 0">
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        <p class="text-gray-500">No hay ventas registradas</p>
                                    </div>
                                </td>
                            </tr>
                            <tr 
                                v-for="s in sales" 
                                :key="s.id" 
                                class="border-t border-gray-100 transition-colors hover:bg-gray-50"
                            >
                                <td class="px-4 py-3">
                                    <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{ s.voucher_number }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900">{{ customers.find(c => c.id === s.id_customers)?.name || 'N/A' }}</p>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ users.find(u => u.id === s.id_users)?.name || 'N/A' }}
                                </td>
                                <td class="px-4 py-3">
                                    <span 
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium capitalize"
                                        :class="s.voucher === 'invoice' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700'"
                                    >
                                        {{ s.voucher === 'invoice' ? '📄 Factura' : '🧾 Boleta' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-900">S/ {{ Number(s.total).toFixed(2) }}</p>
                                    <p class="text-xs text-gray-500">IGV: S/ {{ (Number(s.total) * 0.18).toFixed(2) }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span 
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium capitalize"
                                        :class="{
                                            'bg-green-100 text-green-700': s.payment_method === 'cash',
                                            'bg-blue-100 text-blue-700': s.payment_method === 'card',
                                            'bg-purple-100 text-purple-700': s.payment_method === 'yape',
                                            'bg-pink-100 text-pink-700': s.payment_method === 'plin',
                                        }"
                                    >
                                        {{ { cash: '💵 Efectivo', card: '💳 Tarjeta', yape: '🟢 Yape', plin: '🔵 Plin' }[s.payment_method] || s.payment_method }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ new Date(s.date_time).toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <Button 
                                            type="button" 
                                            variant="secondary" 
                                            size="sm"
                                            :disabled="form.processing || deleteForm.processing"
                                            @click="startEdit(s)"
                                            :class="['rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 transition-all hover:bg-gray-50 disabled:opacity-50']"
                                        >
                                            Editar
                                        </Button>
                                        <Button 
                                            type="button" 
                                            variant="destructive" 
                                            size="sm"
                                            :disabled="form.processing || deleteForm.processing" 
                                            @click="remove(s)"
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


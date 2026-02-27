<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import ProviderController from '@/actions/App/Http/Controllers/ProviderController';
import InputError from '@/components/InputError.vue';

type Providers = {
    id: number;
    id_products: number;
    ruc: string;
    company_name: string;
    contact_person: string;
    phone: number;
    address: string;
    email: string;
    category: string;
    description_products: string;
    status: string;
};

type Products = {
    id: number;
    code: string;
};

type Props = {
    providers: Providers[];
    products: Products[];
};

const props = defineProps<Props>();
const provider = computed(() => props.providers);
const product  = computed(() => props.products);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Proveedores', href: '/' },
];

// ── Estado UI ──
const editingId             = ref<number | null>(null);
const showModal             = ref(false);
const showViewModal         = ref(false);
const showDeleteModal       = ref(false);
const showPedidoModal       = ref(false);
const searchQuery           = ref('');
const proveedorSeleccionado = ref<Providers | null>(null);
const proveedorAEliminar    = ref<Providers | null>(null);
const pedidoProveedor       = ref<Providers | null>(null);
const pedidoProducto        = ref('');
const pedidoCantidad        = ref(1);
const pedidoNotas           = ref('');

const isEditing = computed(() => editingId.value !== null);

// ── KPIs ──
const totalProveedores   = computed(() => provider.value.length);
const proveedoresActivos = computed(() => provider.value.filter(p => p.status === 'active').length);
const productosAsociados = computed(() => new Set(provider.value.map(p => p.id_products)).size);

// ── Filtro búsqueda ──
const proveedoresFiltrados = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return provider.value;
    return provider.value.filter(p =>
        p.company_name.toLowerCase().includes(q) ||
        p.ruc.toLowerCase().includes(q) ||
        String(p.phone).includes(q)
    );
});

// ── Formulario ──
const form = useForm({
    id_products: '' as number | '',
    ruc: '',
    company_name: '',
    contact_person: '',
    phone: '' as number | '',
    address: '',
    email: '',
    category: '',
    description_products: '',
    status: '',
});

const deleteForm = useForm({});

const resetForm = (): void => {
    editingId.value = null;
    form.reset();
    showModal.value = false;
};

const submit = (): void => {
    const option = { preserveScroll: true, onSuccess: () => resetForm() };
    if (isEditing.value && editingId.value !== null) {
        form.put(ProviderController.update.url(editingId.value), option);
        return;
    }
    form.post(ProviderController.store.url(), option);
};

const prepararModalNuevo = (): void => {
    editingId.value = null;
    form.reset();
    showModal.value = true;
};

const verDetalle = (prov: Providers): void => {
    proveedorSeleccionado.value = prov;
    showViewModal.value = true;
};

const editarDesdeVista = (): void => {
    if (!proveedorSeleccionado.value) return;
    showViewModal.value = false;
    startEdit(proveedorSeleccionado.value);
};

const startEdit = (prov: Providers): void => {
    editingId.value = prov.id;
    form.clearErrors();
    form.company_name         = prov.company_name;
    form.contact_person       = prov.contact_person;
    form.ruc                  = prov.ruc;
    form.phone                = prov.phone || '';
    form.address              = prov.address;
    form.email                = prov.email;
    form.category             = prov.category;
    form.id_products          = prov.id_products || '';
    form.description_products = prov.description_products;
    form.status               = prov.status;
    showModal.value = true;
};

const abrirEliminar = (prov: Providers): void => {
    proveedorAEliminar.value = prov;
    showDeleteModal.value = true;
};

const confirmarEliminar = (): void => {
    if (!proveedorAEliminar.value) return;
    deleteForm.delete(ProviderController.destroy.url(proveedorAEliminar.value.id), {
        preserveScroll: true,
        onSuccess: () => { showDeleteModal.value = false; },
    });
};

const abrirPedido = (prov: Providers, producto: string): void => {
    pedidoProveedor.value = prov;
    pedidoProducto.value  = producto;
    pedidoCantidad.value  = 1;
    pedidoNotas.value     = '';
    showPedidoModal.value = true;
};

const confirmarPedido = (): void => {
    alert(`Pedido enviado a ${pedidoProveedor.value?.company_name}: ${pedidoCantidad.value} unidades de ${pedidoProducto.value}`);
    showPedidoModal.value = false;
};

const autocompletDescription = (): void => {
    const sel = product.value.find(p => p.id === form.id_products);
    form.description_products = sel ? sel.code : '';
};

const categoryLabel = (cat: string) => ({
    wholesaler:   'Mayorista',
    retailer:     'Minorista',
    distributor:  'Distribuidor',
    manufacturer: 'Fabricante',
}[cat] ?? cat);
</script>

<template>
    <Head title="Proveedores" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 md:p-8 bg-gray-50 min-h-screen">

            <!-- ── Botón Nuevo Proveedor ── -->
            <div class="flex justify-end mb-6">
                <button
                    @click="prepararModalNuevo"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold flex items-center gap-2 transition-all shadow-md"
                >
                    <i class="ph ph-plus-circle text-xl"></i>
                    Nuevo Proveedor
                </button>
            </div>

            <!-- ── KPIs ── -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
                <div class="bg-white rounded-xl p-4 md:p-6 shadow-sm border border-gray-100">
                    <p class="text-xs md:text-sm text-gray-500 mb-1">Total Proveedores</p>
                    <p class="text-2xl md:text-3xl font-bold text-gray-800">{{ totalProveedores }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 md:p-6 shadow-sm border border-gray-100">
                    <p class="text-xs md:text-sm text-gray-500 mb-1">Proveedores Activos</p>
                    <p class="text-2xl md:text-3xl font-bold text-green-600">{{ proveedoresActivos }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 md:p-6 shadow-sm border border-gray-100">
                    <p class="text-xs md:text-sm text-gray-500 mb-1">Productos Asociados</p>
                    <p class="text-2xl md:text-3xl font-bold text-blue-600">{{ productosAsociados }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 md:p-6 shadow-sm border border-gray-100">
                    <p class="text-xs md:text-sm text-gray-500 mb-1">Pedidos Pendientes</p>
                    <p class="text-2xl md:text-3xl font-bold text-orange-600">3</p>
                </div>
            </div>

            <!-- ── Búsqueda ── -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 mb-6">
                <div class="relative">
                    <!-- Lupa SVG nativa, siempre visible -->
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z"/>
                    </svg>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Buscar por nombre o RUC..."
                        class="w-full pl-9 pr-10 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <!-- X para limpiar -->
                    <button
                        v-if="searchQuery"
                        @click="searchQuery = ''"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <!-- Indicador siempre visible -->
                <p class="mt-2 text-xs">
                    <template v-if="searchQuery">
                        <span class="text-blue-600 font-medium">{{ proveedoresFiltrados.length }} resultado{{ proveedoresFiltrados.length !== 1 ? 's' : '' }}</span>
                        <span class="text-gray-400"> para </span>
                        <span class="text-gray-700 font-semibold">"{{ searchQuery }}"</span>
                    </template>
                    <template v-else>
                        <span class="text-gray-400">{{ provider.length }} proveedor{{ provider.length !== 1 ? 'es' : '' }} en total</span>
                    </template>
                </p>
            </div>
            <!-- ── Tarjetas de Proveedores ── -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6">
                <div
                    v-for="prov in proveedoresFiltrados"
                    :key="prov.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow"
                >
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h5 class="text-base font-bold text-gray-800 leading-tight">{{ prov.company_name }}</h5>
                            <p class="text-xs text-gray-400 mt-0.5">RUC: {{ prov.ruc }}</p>
                        </div>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                            :class="prov.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                        >
                            {{ prov.status === 'active' ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <div class="space-y-1.5 text-sm text-gray-600 mb-4">
                        <p class="flex items-center gap-2">
                            <i class="ph ph-user text-gray-400 shrink-0"></i>
                            {{ prov.contact_person || '—' }}
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="ph ph-phone text-gray-400 shrink-0"></i>
                            <a :href="`https://wa.me/51${prov.phone}`" class="text-green-600 hover:underline">{{ prov.phone }}</a>
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="ph ph-tag text-gray-400 shrink-0"></i>
                            {{ categoryLabel(prov.category) }}
                        </p>
                    </div>

                    <div class="flex gap-2 pt-3 border-t border-gray-100">
                        <button
                            @click="verDetalle(prov)"
                            class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <i class="ph ph-eye"></i> Ver
                        </button>
                        <button
                            @click="startEdit(prov)"
                            class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors"
                        >
                            <i class="ph ph-pencil-simple"></i> Editar
                        </button>
                        <button
                            @click="abrirPedido(prov, prov.description_products)"
                            class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            <i class="ph ph-truck"></i> Pedir
                        </button>
                        <button
                            @click="abrirEliminar(prov)"
                            class="px-3 py-2 text-xs font-medium text-red-500 border border-red-200 rounded-lg hover:bg-red-50 transition-colors"
                        >
                            <i class="ph ph-trash"></i>
                        </button>
                    </div>
                </div>

                <div v-if="proveedoresFiltrados.length === 0" class="col-span-full text-center py-12 text-gray-400">
                    <i class="ph ph-truck text-5xl mb-3 block"></i>
                    <p class="font-medium">No se encontraron proveedores</p>
                </div>
            </div>

            <!-- ── Tabla Productos y Stock ── -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h5 class="font-semibold text-gray-900 flex items-center gap-2">
                        <i class="ph ph-package text-blue-600"></i>
                        Productos y Stock por Proveedor
                    </h5>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Proveedor</th>
                                <th class="px-6 py-3 font-semibold">RUC</th>
                                <th class="px-6 py-3 font-semibold">Categoría</th>
                                <th class="px-6 py-3 font-semibold">Producto</th>
                                <th class="px-6 py-3 font-semibold">Estado</th>
                                <th class="px-6 py-3 font-semibold">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="provider.length === 0">
                                <td colspan="6" class="px-6 py-6 text-center text-gray-400">No hay proveedores registrados.</td>
                            </tr>
                            <tr v-for="prov in provider" :key="prov.id" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 font-medium text-gray-800">{{ prov.company_name }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ prov.ruc }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        {{ categoryLabel(prov.category) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-600">{{ prov.description_products }}</td>
                                <td class="px-6 py-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                        :class="prov.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                    >
                                        {{ prov.status === 'active' ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    <button
                                        @click="abrirPedido(prov, prov.description_products)"
                                        class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1"
                                    >
                                        <i class="ph ph-truck"></i> Pedir
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>

    <!--
         MODAL: Ver Detalle (solo lectura — datos como texto)
    -->
    <Teleport to="body">
        <div v-if="showViewModal && proveedorSeleccionado" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm" @click="showViewModal = false"></div>

            <div class="relative z-10 bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                <!-- Header con avatar -->
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-lg shrink-0">
                            {{ proveedorSeleccionado.company_name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 leading-tight">
                                {{ proveedorSeleccionado.company_name }}
                            </h3>
                            <p class="text-xs text-gray-400 mt-0.5">RUC: {{ proveedorSeleccionado.ruc }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                            :class="proveedorSeleccionado.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                        >
                            <span
                                class="w-1.5 h-1.5 rounded-full"
                                :class="proveedorSeleccionado.status === 'active' ? 'bg-green-500 animate-pulse' : 'bg-gray-400'"
                            ></span>
                            {{ proveedorSeleccionado.status === 'active' ? 'Activo' : 'Inactivo' }}
                        </span>
                        <button
                            @click="showViewModal = false"
                            class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            <i class="ph ph-x text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Datos como texto estilizado -->
                <div class="px-6 py-5 overflow-y-auto space-y-4">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Razón Social -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1.5 flex items-center gap-1.5">
                                <i class="ph ph-buildings"></i> Razón Social
                            </p>
                            <p class="text-sm font-semibold text-gray-800">{{ proveedorSeleccionado.company_name }}</p>
                        </div>
                        <!-- RUC -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1.5 flex items-center gap-1.5">
                                <i class="ph ph-identification-card"></i> RUC
                            </p>
                            <p class="text-sm font-semibold text-gray-800">{{ proveedorSeleccionado.ruc }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Contacto -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1.5 flex items-center gap-1.5">
                                <i class="ph ph-user"></i> Persona de Contacto
                            </p>
                            <p class="text-sm font-semibold text-gray-800">{{ proveedorSeleccionado.contact_person || '—' }}</p>
                        </div>
                        <!-- Teléfono -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1.5 flex items-center gap-1.5">
                                <i class="ph ph-phone"></i> Teléfono
                            </p>
                            <a
                                :href="`https://wa.me/51${proveedorSeleccionado.phone}`"
                                class="text-sm font-semibold text-green-600 hover:underline"
                            >{{ proveedorSeleccionado.phone }}</a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Email -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1.5 flex items-center gap-1.5">
                                <i class="ph ph-envelope"></i> Correo Electrónico
                            </p>
                            <p class="text-sm font-semibold text-gray-800 break-all">{{ proveedorSeleccionado.email || '—' }}</p>
                        </div>
                        <!-- Dirección -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1.5 flex items-center gap-1.5">
                                <i class="ph ph-map-pin"></i> Dirección
                            </p>
                            <p class="text-sm font-semibold text-gray-800">{{ proveedorSeleccionado.address || '—' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Categoría -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1.5 flex items-center gap-1.5">
                                <i class="ph ph-tag"></i> Categoría
                            </p>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-100 text-blue-700">
                                {{ categoryLabel(proveedorSeleccionado.category) }}
                            </span>
                        </div>
                        <!-- Producto -->
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1.5 flex items-center gap-1.5">
                                <i class="ph ph-package"></i> Producto / Descripción
                            </p>
                            <p class="text-sm font-semibold text-gray-800">{{ proveedorSeleccionado.description_products || '—' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row-reverse gap-3 shrink-0 rounded-b-xl">
                    <button
                        type="button" @click="editarDesdeVista"
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                    >
                        <i class="ph ph-pencil-simple"></i> Editar Proveedor
                    </button>
                    <button
                        type="button"
                        @click="() => { abrirPedido(proveedorSeleccionado!, proveedorSeleccionado!.description_products); showViewModal = false; }"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                    >
                        <i class="ph ph-truck"></i> Solicitar Pedido
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- 
         MODAL: Nuevo / Editar Proveedor (con inputs)
    -->
    <Teleport to="body">
        <div v-if="showModal" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm" @click="resetForm"></div>

            <div class="relative z-10 bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center shrink-0">
                    <h3 class="text-xl font-semibold text-gray-900">
                        {{ isEditing ? 'Editar Proveedor' : 'Registrar Nuevo Proveedor' }}
                    </h3>
                    <button @click="resetForm" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <form @submit.prevent="submit" class="px-6 py-5 overflow-y-auto space-y-4 bg-white">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Razón Social *</label>
                            <input v-model="form.company_name" type="text" required placeholder="Ej: Distribuidora Central SAC"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
                            <InputError :message="form.errors.company_name" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">RUC *</label>
                            <input v-model="form.ruc" type="text" required placeholder="20123456789"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
                            <InputError :message="form.errors.ruc" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
                            <input v-model="form.phone" type="text" required placeholder="929252113"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
                            <InputError :message="form.errors.phone" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                            <input v-model="form.email" type="email" :disabled="isEditing" placeholder="ventas@central.com"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed" />
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <input v-model="form.address" type="text" placeholder="Av. Principal 123, Distrito"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
                        <InputError :message="form.errors.address" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Persona de Contacto</label>
                            <input v-model="form.contact_person" type="text" placeholder="Juan Pérez"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
                            <InputError :message="form.errors.contact_person" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                            <select v-model="form.category" required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                <option value="" disabled class="text-gray-400">Tipo de categoría</option>
                                <option value="wholesaler" class="text-gray-900">Mayorista</option>
                                <option value="retailer" class="text-gray-900">Minorista</option>
                                <option value="distributor" class="text-gray-900">Distribuidor</option>
                                <option value="manufacturer" class="text-gray-900">Fabricante</option>
                            </select>
                            <InputError :message="form.errors.category" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Selecciona un Producto</label>
                            <select v-model="form.id_products" required @change="autocompletDescription"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                :class="!form.id_products ? 'text-gray-400' : 'text-gray-900'">
                                <option value="" disabled class="text-gray-400">— Selecciona un producto —</option>
                                <option v-for="prod in product" :key="prod.id" :value="prod.id" class="text-gray-900">
                                    {{ prod.code || prod.id }}
                                </option>
                            </select>
                            <p v-if="!form.id_products && form.isDirty" class="mt-1 text-xs text-red-500">Debes seleccionar un producto</p>
                            <InputError :message="form.errors.id_products" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Descripción del Producto</label>
                            <input v-model="form.description_products" type="text" :disabled="isEditing" placeholder="Descripción producto"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed" />
                            <InputError :message="form.errors.description_products" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select v-model="form.status" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                            <option value="" disabled class="text-gray-400">Tipo de estado</option>
                            <option value="active" class="text-gray-900">Activo</option>
                            <option value="inactive" class="text-gray-900">Inactivo</option>
                        </select>
                        <InputError :message="form.errors.status" />
                    </div>
                </form>

                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row-reverse gap-3 shrink-0 rounded-b-xl">
                    <button type="button" @click="submit" :disabled="form.processing || deleteForm.processing"
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white rounded-lg font-medium transition-colors">
                        {{ isEditing ? 'Actualizar Proveedor' : 'Guardar Proveedor' }}
                    </button>
                    <button type="button" @click="resetForm"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-medium transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!--
         MODAL: Confirmar Eliminación
     -->
    <Teleport to="body">
        <div v-if="showDeleteModal" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm" @click="showDeleteModal = false"></div>
            <div class="relative z-10 bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="bg-red-600 px-6 py-4 rounded-t-xl">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <i class="ph ph-warning-circle text-xl"></i> Confirmar Eliminación
                    </h3>
                </div>
                <div class="px-6 py-5">
                    <p class="text-gray-700 mb-2">
                        ¿Estás seguro de que deseas eliminar al proveedor
                        <strong class="font-semibold">{{ proveedorAEliminar?.company_name }}</strong>?
                    </p>
                    <p class="text-sm text-gray-500">Esta acción no se puede deshacer.</p>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3 border-t border-gray-200 rounded-b-xl">
                    <button type="button" @click="confirmarEliminar" :disabled="deleteForm.processing"
                        class="w-full sm:w-auto px-5 py-2.5 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white rounded-lg font-medium transition-colors">
                        Eliminar
                    </button>
                    <button type="button" @click="showDeleteModal = false"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-medium transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!--
         MODAL: Solicitar Pedido
     -->
    <Teleport to="body">
        <div v-if="showPedidoModal" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm" @click="showPedidoModal = false"></div>
            <div class="relative z-10 bg-white rounded-xl shadow-xl w-full max-w-lg">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Solicitar Pedido a Proveedor</h3>
                    <button @click="showPedidoModal = false" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <!-- Info del proveedor como texto -->
                    <div class="bg-blue-50 rounded-xl p-4 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-200 flex items-center justify-center text-blue-700 font-bold text-base shrink-0">
                            {{ pedidoProveedor?.company_name.charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ pedidoProveedor?.company_name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Producto: {{ pedidoProducto || '—' }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad Solicitada *</label>
                        <input v-model.number="pedidoCantidad" type="number" min="1" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas Adicionales</label>
                        <textarea v-model="pedidoNotas" rows="2" placeholder="Especificaciones adicionales..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200"></textarea>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3 border-t border-gray-200 rounded-b-xl">
                    <button type="button" @click="confirmarPedido"
                        class="w-full sm:w-auto px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                        Enviar Solicitud
                    </button>
                    <button type="button" @click="showPedidoModal = false"
                        class="w-full sm:w-auto px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg font-medium transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
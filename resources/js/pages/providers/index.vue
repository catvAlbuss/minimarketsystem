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
    description: string;
};

type Props = {
    providers: Providers[];
    products: Products[];
};

const props = defineProps<Props>();
const provider = computed(() => props.providers);
const product = computed(() => props.products);

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Proveedores', href: '/' }];

// ── Estado UI ──
const editingId = ref<number | null>(null);
const showModal = ref(false);
const showViewModal = ref(false);
const showDeleteModal = ref(false);
const showPedidoModal = ref(false);
const searchQuery = ref('');
const proveedorSeleccionado = ref<Providers | null>(null);
const proveedorAEliminar = ref<Providers | null>(null);
const pedidoProveedor = ref<Providers | null>(null);
const pedidoProducto = ref('');
const pedidoCantidad = ref(1);
const pedidoNotas = ref('');

const isEditing = computed(() => editingId.value !== null);

// ── KPIs ──
const totalProveedores = computed(() => provider.value.length);
const proveedoresActivos = computed(
    () => provider.value.filter((p) => p.status === 'active').length,
);
const productosAsociados = computed(
    () => new Set(provider.value.map((p) => p.id_products)).size,
);

// ── Filtro búsqueda ──
const proveedoresFiltrados = computed(() => {
    const q = searchQuery.value.toLowerCase();
    if (!q) return provider.value;
    return provider.value.filter(
        (p) =>
            p.company_name.toLowerCase().includes(q) ||
            p.ruc.toLowerCase().includes(q) ||
            String(p.phone).includes(q),
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
const deleteError = computed(
    () => (deleteForm.errors as Record<string, string | undefined>).delete,
);

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
    form.company_name = prov.company_name;
    form.contact_person = prov.contact_person;
    form.ruc = prov.ruc;
    form.phone = prov.phone || '';
    form.address = prov.address;
    form.email = prov.email;
    form.category = prov.category;
    form.id_products = prov.id_products || '';
    form.description_products = prov.description_products;
    form.status = prov.status;
    showModal.value = true;
};

const abrirEliminar = (prov: Providers): void => {
    proveedorAEliminar.value = prov;
    showDeleteModal.value = true;
};

const confirmarEliminar = (): void => {
    if (!proveedorAEliminar.value) return;
    deleteForm.delete(
        ProviderController.destroy.url(proveedorAEliminar.value.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteModal.value = false;
            },
        },
    );
};

const abrirPedido = (prov: Providers, producto: string): void => {
    pedidoProveedor.value = prov;
    pedidoProducto.value = producto;
    pedidoCantidad.value = 1;
    pedidoNotas.value = '';
    showPedidoModal.value = true;
};

const confirmarPedido = (): void => {
    alert(
        `Pedido enviado a ${pedidoProveedor.value?.company_name}: ${pedidoCantidad.value} unidades de ${pedidoProducto.value}`,
    );
    showPedidoModal.value = false;
};

const autocompletDescription = (): void => {
    const sel = product.value.find((p) => p.id === form.id_products);
    form.description_products = sel ? sel.description : '';
};

const categoryLabel = (cat: string) =>
    ({
        wholesaler: 'Mayorista',
        retailer: 'Minorista',
        distributor: 'Distribuidor',
        manufacturer: 'Fabricante',
    })[cat] ?? cat;
</script>

<template>
    <Head title="Proveedores" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gray-50 p-6 md:p-8">
            <!-- ── Botón Nuevo Proveedor ── -->
            <div class="mb-6 flex justify-end">
                <button
                    @click="prepararModalNuevo"
                    class="flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-2.5 font-semibold text-white shadow-md transition-all hover:bg-blue-700"
                >
                    <i class="ph ph-plus-circle text-xl"></i>
                    Nuevo Proveedor
                </button>
            </div>

            <!-- ── KPIs ── -->
            <div
                class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 lg:grid-cols-4"
            >
                <div
                    class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm md:p-6"
                >
                    <p class="mb-1 text-xs text-gray-500 md:text-sm">
                        Total Proveedores
                    </p>
                    <p class="text-2xl font-bold text-gray-800 md:text-3xl">
                        {{ totalProveedores }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm md:p-6"
                >
                    <p class="mb-1 text-xs text-gray-500 md:text-sm">
                        Proveedores Activos
                    </p>
                    <p class="text-2xl font-bold text-green-600 md:text-3xl">
                        {{ proveedoresActivos }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm md:p-6"
                >
                    <p class="mb-1 text-xs text-gray-500 md:text-sm">
                        Productos Asociados
                    </p>
                    <p class="text-2xl font-bold text-blue-600 md:text-3xl">
                        {{ productosAsociados }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm md:p-6"
                >
                    <p class="mb-1 text-xs text-gray-500 md:text-sm">
                        Pedidos Pendientes
                    </p>
                    <p class="text-2xl font-bold text-orange-600 md:text-3xl">
                        3
                    </p>
                </div>
            </div>

            <!-- ── Búsqueda ── -->
            <div
                class="mb-6 rounded-xl border border-gray-100 bg-white p-4 shadow-sm"
            >
                <div class="relative">
                    <!-- Lupa SVG nativa, siempre visible -->
                    <svg
                        class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m21 21-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0Z"
                        />
                    </svg>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Buscar por nombre o RUC..."
                        class="w-full rounded-lg border border-gray-300 bg-white py-2.5 pr-10 pl-9 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    />
                    <!-- X para limpiar -->
                    <button
                        v-if="searchQuery"
                        @click="searchQuery = ''"
                        class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                    >
                        <svg
                            class="h-4 w-4"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18 18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                </div>
                <!-- Indicador siempre visible -->
                <p class="mt-2 text-xs">
                    <template v-if="searchQuery">
                        <span class="font-medium text-blue-600"
                            >{{ proveedoresFiltrados.length }} resultado{{
                                proveedoresFiltrados.length !== 1 ? 's' : ''
                            }}</span
                        >
                        <span class="text-gray-400"> para </span>
                        <span class="font-semibold text-gray-700"
                            >"{{ searchQuery }}"</span
                        >
                    </template>
                    <template v-else>
                        <span class="text-gray-400"
                            >{{ provider.length }} proveedor{{
                                provider.length !== 1 ? 'es' : ''
                            }}
                            en total</span
                        >
                    </template>
                </p>
            </div>
            <!-- ── Tarjetas de Proveedores ── -->
            <div
                class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 lg:grid-cols-3"
            >
                <div
                    v-for="prov in proveedoresFiltrados"
                    :key="prov.id"
                    class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow hover:shadow-md"
                >
                    <div class="mb-3 flex items-start justify-between">
                        <div>
                            <h5
                                class="text-base leading-tight font-bold text-gray-800"
                            >
                                {{ prov.company_name }}
                            </h5>
                            <p class="mt-0.5 text-xs text-gray-400">
                                RUC: {{ prov.ruc }}
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="
                                prov.status === 'active'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-500'
                            "
                        >
                            {{
                                prov.status === 'active' ? 'Activo' : 'Inactivo'
                            }}
                        </span>
                    </div>

                    <div class="mb-4 space-y-1.5 text-sm text-gray-600">
                        <p class="flex items-center gap-2">
                            <i class="ph ph-user shrink-0 text-gray-400"></i>
                            {{ prov.contact_person || '—' }}
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="ph ph-phone shrink-0 text-gray-400"></i>
                            <a
                                :href="`https://wa.me/51${prov.phone}`"
                                class="text-green-600 hover:underline"
                                >{{ prov.phone }}</a
                            >
                        </p>
                        <p class="flex items-center gap-2">
                            <i class="ph ph-tag shrink-0 text-gray-400"></i>
                            {{ categoryLabel(prov.category) }}
                        </p>
                    </div>

                    <div class="flex gap-2 border-t border-gray-100 pt-3">
                        <button
                            @click="verDetalle(prov)"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-600 transition-colors hover:bg-gray-50"
                        >
                            <i class="ph ph-eye"></i> Ver
                        </button>
                        <button
                            @click="startEdit(prov)"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-blue-200 px-3 py-2 text-xs font-medium text-blue-600 transition-colors hover:bg-blue-50"
                        >
                            <i class="ph ph-pencil-simple"></i> Editar
                        </button>
                        <button
                            @click="
                                abrirPedido(prov, prov.description_products)
                            "
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-xs font-medium text-white transition-colors hover:bg-blue-700"
                        >
                            <i class="ph ph-truck"></i> Pedir
                        </button>
                        <button
                            @click="abrirEliminar(prov)"
                            class="rounded-lg border border-red-200 px-3 py-2 text-xs font-medium text-red-500 transition-colors hover:bg-red-50"
                        >
                            <i class="ph ph-trash"></i>
                        </button>
                    </div>
                </div>

                <div
                    v-if="proveedoresFiltrados.length === 0"
                    class="col-span-full py-12 text-center text-gray-400"
                >
                    <i class="ph ph-truck mb-3 block text-5xl"></i>
                    <p class="font-medium">No se encontraron proveedores</p>
                </div>
            </div>

            <!-- ── Tabla Productos y Stock ── -->

            <div
                class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
            >
                <section>
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h5
                            class="flex items-center gap-2 font-semibold text-gray-900"
                        >
                            <i class="ph ph-package text-blue-600"></i>
                            Productos y Stock por Proveedor
                        </h5>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead
                                class="bg-gray-50 text-xs text-gray-600 uppercase"
                            >
                                <tr>
                                    <th class="px-6 py-3 font-semibold">
                                        Proveedor
                                    </th>
                                    <th class="px-6 py-3 font-semibold">RUC</th>
                                    <th class="px-6 py-3 font-semibold">
                                        Categoría
                                    </th>
                                    <th class="px-6 py-3 font-semibold">
                                        Producto
                                    </th>
                                    <th class="px-6 py-3 font-semibold">
                                        Estado
                                    </th>
                                    <th class="px-6 py-3 font-semibold">
                                        Acción
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-if="provider.length === 0">
                                    <td
                                        colspan="6"
                                        class="px-6 py-6 text-center text-gray-400"
                                    >
                                        No hay proveedores registrados.
                                    </td>
                                </tr>
                                <tr
                                    v-for="prov in provider"
                                    :key="prov.id"
                                    class="transition-colors hover:bg-gray-50"
                                >
                                    <td
                                        class="px-6 py-3 font-medium text-gray-800"
                                    >
                                        {{ prov.company_name }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-600">
                                        {{ prov.ruc }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700"
                                        >
                                            {{ categoryLabel(prov.category) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-600">
                                        {{ prov.description_products }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                            :class="
                                                prov.status === 'active'
                                                    ? 'bg-green-100 text-green-700'
                                                    : 'bg-gray-100 text-gray-500'
                                            "
                                        >
                                            {{
                                                prov.status === 'active'
                                                    ? 'Activo'
                                                    : 'Inactivo'
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <button
                                            @click="
                                                abrirPedido(
                                                    prov,
                                                    prov.description_products,
                                                )
                                            "
                                            class="flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs text-white transition-colors hover:bg-blue-700"
                                        >
                                            <i class="ph ph-truck"></i> Pedir
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <InputError :message="deleteError" class="mt-3" />
                </section>
            </div>
        </div>
    </AppLayout>

    <!--
         MODAL: Ver Detalle (solo lectura — datos como texto)
    -->
    <Teleport to="body">
        <div
            v-if="showViewModal && proveedorSeleccionado"
            class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        >
            <div
                class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm"
                @click="showViewModal = false"
            ></div>

            <div
                class="relative z-10 flex max-h-[90vh] w-full max-w-2xl flex-col rounded-xl bg-white shadow-xl"
            >
                <!-- Header con avatar -->
                <div
                    class="flex shrink-0 items-center justify-between border-b border-gray-100 px-6 py-5"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-blue-100 text-lg font-bold text-blue-600"
                        >
                            {{
                                proveedorSeleccionado.company_name
                                    .charAt(0)
                                    .toUpperCase()
                            }}
                        </div>
                        <div>
                            <h3
                                class="text-xl leading-tight font-semibold text-gray-900"
                            >
                                {{ proveedorSeleccionado.company_name }}
                            </h3>
                            <p class="mt-0.5 text-xs text-gray-400">
                                RUC: {{ proveedorSeleccionado.ruc }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                            :class="
                                proveedorSeleccionado.status === 'active'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-500'
                            "
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="
                                    proveedorSeleccionado.status === 'active'
                                        ? 'animate-pulse bg-green-500'
                                        : 'bg-gray-400'
                                "
                            ></span>
                            {{
                                proveedorSeleccionado.status === 'active'
                                    ? 'Activo'
                                    : 'Inactivo'
                            }}
                        </span>
                        <button
                            @click="showViewModal = false"
                            class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
                        >
                            <i class="ph ph-x text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Datos como texto estilizado -->
                <div class="space-y-4 overflow-y-auto px-6 py-5">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Razón Social -->
                        <div class="rounded-xl bg-gray-50 p-4">
                            <p
                                class="mb-1.5 flex items-center gap-1.5 text-xs tracking-wide text-gray-400 uppercase"
                            >
                                <i class="ph ph-buildings"></i> Razón Social
                            </p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ proveedorSeleccionado.company_name }}
                            </p>
                        </div>
                        <!-- RUC -->
                        <div class="rounded-xl bg-gray-50 p-4">
                            <p
                                class="mb-1.5 flex items-center gap-1.5 text-xs tracking-wide text-gray-400 uppercase"
                            >
                                <i class="ph ph-identification-card"></i> RUC
                            </p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ proveedorSeleccionado.ruc }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Contacto -->
                        <div class="rounded-xl bg-gray-50 p-4">
                            <p
                                class="mb-1.5 flex items-center gap-1.5 text-xs tracking-wide text-gray-400 uppercase"
                            >
                                <i class="ph ph-user"></i> Persona de Contacto
                            </p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{
                                    proveedorSeleccionado.contact_person || '—'
                                }}
                            </p>
                        </div>
                        <!-- Teléfono -->
                        <div class="rounded-xl bg-gray-50 p-4">
                            <p
                                class="mb-1.5 flex items-center gap-1.5 text-xs tracking-wide text-gray-400 uppercase"
                            >
                                <i class="ph ph-phone"></i> Teléfono
                            </p>
                            <a
                                :href="`https://wa.me/51${proveedorSeleccionado.phone}`"
                                class="text-sm font-semibold text-green-600 hover:underline"
                                >{{ proveedorSeleccionado.phone }}</a
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Email -->
                        <div class="rounded-xl bg-gray-50 p-4">
                            <p
                                class="mb-1.5 flex items-center gap-1.5 text-xs tracking-wide text-gray-400 uppercase"
                            >
                                <i class="ph ph-envelope"></i> Correo
                                Electrónico
                            </p>
                            <p
                                class="text-sm font-semibold break-all text-gray-800"
                            >
                                {{ proveedorSeleccionado.email || '—' }}
                            </p>
                        </div>
                        <!-- Dirección -->
                        <div class="rounded-xl bg-gray-50 p-4">
                            <p
                                class="mb-1.5 flex items-center gap-1.5 text-xs tracking-wide text-gray-400 uppercase"
                            >
                                <i class="ph ph-map-pin"></i> Dirección
                            </p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ proveedorSeleccionado.address || '—' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Categoría -->
                        <div class="rounded-xl bg-gray-50 p-4">
                            <p
                                class="mb-1.5 flex items-center gap-1.5 text-xs tracking-wide text-gray-400 uppercase"
                            >
                                <i class="ph ph-tag"></i> Categoría
                            </p>
                            <span
                                class="inline-flex items-center rounded-lg bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700"
                            >
                                {{
                                    categoryLabel(
                                        proveedorSeleccionado.category,
                                    )
                                }}
                            </span>
                        </div>
                        <!-- Producto -->
                        <div class="rounded-xl bg-gray-50 p-4">
                            <p
                                class="mb-1.5 flex items-center gap-1.5 text-xs tracking-wide text-gray-400 uppercase"
                            >
                                <i class="ph ph-package"></i> Producto /
                                Descripción
                            </p>
                            <p class="text-sm font-semibold text-gray-800">
                                {{
                                    proveedorSeleccionado.description_products ||
                                    '—'
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div
                    class="flex shrink-0 flex-col gap-3 rounded-b-xl border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row-reverse"
                >
                    <button
                        type="button"
                        @click="editarDesdeVista"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 font-medium text-white transition-colors hover:bg-blue-700 sm:w-auto"
                    >
                        <i class="ph ph-pencil-simple"></i> Editar Proveedor
                    </button>
                    <button
                        type="button"
                        @click="
                            () => {
                                abrirPedido(
                                    proveedorSeleccionado!,
                                    proveedorSeleccionado!.description_products,
                                );
                                showViewModal = false;
                            }
                        "
                        class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 font-medium text-gray-700 transition-colors hover:bg-gray-50 sm:w-auto"
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
        <div
            v-if="showModal"
            class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        >
            <div
                class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm"
                @click="resetForm"
            ></div>

            <div
                class="relative z-10 flex max-h-[90vh] w-full max-w-3xl flex-col rounded-xl bg-white shadow-xl"
            >
                <div
                    class="flex shrink-0 items-center justify-between border-b border-gray-100 px-6 py-5"
                >
                    <h3 class="text-xl font-semibold text-gray-900">
                        {{
                            isEditing
                                ? 'Editar Proveedor'
                                : 'Registrar Nuevo Proveedor'
                        }}
                    </h3>
                    <button
                        @click="resetForm"
                        class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
                    >
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>

                <form
                    @submit.prevent="submit"
                    class="space-y-4 overflow-y-auto bg-white px-6 py-5"
                >
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >Razón Social *</label
                            >
                            <input
                                v-model="form.company_name"
                                type="text"
                                required
                                placeholder="Ej: Distribuidora Central SAC"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                            />
                            <InputError :message="form.errors.company_name" />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >RUC *</label
                            >
                            <input
                                v-model="form.ruc"
                                type="text"
                                required
                                placeholder="20123456789"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                            />
                            <InputError :message="form.errors.ruc" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >Teléfono *</label
                            >
                            <input
                                v-model="form.phone"
                                type="text"
                                required
                                placeholder="929252113"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                            />
                            <InputError :message="form.errors.phone" />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >Correo Electrónico</label
                            >
                            <input
                                v-model="form.email"
                                type="email"
                                :disabled="isEditing"
                                placeholder="ventas@central.com"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400"
                            />
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Dirección</label
                        >
                        <input
                            v-model="form.address"
                            type="text"
                            placeholder="Av. Principal 123, Distrito"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        />
                        <InputError :message="form.errors.address" />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >Persona de Contacto</label
                            >
                            <input
                                v-model="form.contact_person"
                                type="text"
                                placeholder="Juan Pérez"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                            />
                            <InputError :message="form.errors.contact_person" />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >Categoría</label
                            >
                            <select
                                v-model="form.category"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                            >
                                <option value="" disabled class="text-gray-400">
                                    Tipo de categoría
                                </option>
                                <option
                                    value="wholesaler"
                                    class="text-gray-900"
                                >
                                    Mayorista
                                </option>
                                <option value="retailer" class="text-gray-900">
                                    Minorista
                                </option>
                                <option
                                    value="distributor"
                                    class="text-gray-900"
                                >
                                    Distribuidor
                                </option>
                                <option
                                    value="manufacturer"
                                    class="text-gray-900"
                                >
                                    Fabricante
                                </option>
                            </select>
                            <InputError :message="form.errors.category" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >Selecciona un Producto</label
                            >
                            <select
                                v-model="form.id_products"
                                required
                                @change="autocompletDescription"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                                :class="
                                    !form.id_products
                                        ? 'text-gray-400'
                                        : 'text-gray-900'
                                "
                            >
                                <option value="" disabled class="text-gray-400">
                                    — Selecciona un producto —
                                </option>
                                <option
                                    v-for="prod in product"
                                    :key="prod.id"
                                    :value="prod.id"
                                    class="text-gray-900"
                                >
                                    {{ prod.code || prod.id }}
                                </option>
                            </select>
                            <p
                                v-if="!form.id_products && form.isDirty"
                                class="mt-1 text-xs text-red-500"
                            >
                                Debes seleccionar un producto
                            </p>
                            <InputError :message="form.errors.id_products" />
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >Descripción del Producto</label
                            >
                            <input
                                v-model="form.description_products"
                                type="text"
                                :disabled="isEditing"
                                placeholder="Descripción producto"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none disabled:cursor-not-allowed disabled:bg-gray-100 disabled:text-gray-400"
                            />
                            <InputError
                                :message="form.errors.description_products"
                            />
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Estado</label
                        >
                        <select
                            v-model="form.status"
                            required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        >
                            <option value="" disabled class="text-gray-400">
                                Tipo de estado
                            </option>
                            <option value="active" class="text-gray-900">
                                Activo
                            </option>
                            <option value="inactive" class="text-gray-900">
                                Inactivo
                            </option>
                        </select>
                        <InputError :message="form.errors.status" />
                    </div>
                </form>

                <div
                    class="flex shrink-0 flex-col gap-3 rounded-b-xl border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row-reverse"
                >
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing || deleteForm.processing"
                        class="w-full rounded-lg bg-blue-600 px-5 py-2.5 font-medium text-white transition-colors hover:bg-blue-700 disabled:opacity-50 sm:w-auto"
                    >
                        {{
                            isEditing
                                ? 'Actualizar Proveedor'
                                : 'Guardar Proveedor'
                        }}
                    </button>
                    <button
                        type="button"
                        @click="resetForm"
                        class="w-full rounded-lg border border-gray-300 bg-white px-5 py-2.5 font-medium text-gray-700 transition-colors hover:bg-gray-50 sm:w-auto"
                    >
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
        <div
            v-if="showDeleteModal"
            class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        >
            <div
                class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm"
                @click="showDeleteModal = false"
            ></div>
            <div
                class="relative z-10 w-full max-w-md rounded-xl bg-white shadow-xl"
            >
                <div class="rounded-t-xl bg-red-600 px-6 py-4">
                    <h3
                        class="flex items-center gap-2 text-lg font-semibold text-white"
                    >
                        <i class="ph ph-warning-circle text-xl"></i> Confirmar
                        Eliminación
                    </h3>
                </div>
                <div class="px-6 py-5">
                    <p class="mb-2 text-gray-700">
                        ¿Estás seguro de que deseas eliminar al proveedor
                        <strong class="font-semibold">{{
                            proveedorAEliminar?.company_name
                        }}</strong
                        >?
                    </p>
                    <p class="text-sm text-gray-500">
                        Esta acción no se puede deshacer.
                    </p>
                </div>
                <div
                    class="flex flex-col gap-3 rounded-b-xl border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row-reverse"
                >
                    <button
                        type="button"
                        @click="confirmarEliminar"
                        :disabled="deleteForm.processing"
                        class="w-full rounded-lg bg-red-600 px-5 py-2.5 font-medium text-white transition-colors hover:bg-red-700 disabled:opacity-50 sm:w-auto"
                    >
                        Eliminar
                    </button>
                    <button
                        type="button"
                        @click="showDeleteModal = false"
                        class="w-full rounded-lg border border-gray-300 bg-white px-5 py-2.5 font-medium text-gray-700 transition-colors hover:bg-gray-50 sm:w-auto"
                    >
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
        <div
            v-if="showPedidoModal"
            class="fixed inset-0 z-[70] flex items-center justify-center p-4"
        >
            <div
                class="absolute inset-0 bg-gray-900/75 backdrop-blur-sm"
                @click="showPedidoModal = false"
            ></div>
            <div
                class="relative z-10 w-full max-w-lg rounded-xl bg-white shadow-xl"
            >
                <div
                    class="flex items-center justify-between border-b border-gray-100 px-6 py-5"
                >
                    <h3 class="text-lg font-semibold text-gray-900">
                        Solicitar Pedido a Proveedor
                    </h3>
                    <button
                        @click="showPedidoModal = false"
                        class="rounded-lg p-1.5 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
                    >
                        <i class="ph ph-x text-xl"></i>
                    </button>
                </div>
                <div class="space-y-4 px-6 py-5">
                    <!-- Info del proveedor como texto -->
                    <div
                        class="flex items-center gap-3 rounded-xl bg-blue-50 p-4"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-200 text-base font-bold text-blue-700"
                        >
                            {{
                                pedidoProveedor?.company_name
                                    .charAt(0)
                                    .toUpperCase()
                            }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ pedidoProveedor?.company_name }}
                            </p>
                            <p class="mt-0.5 text-xs text-gray-500">
                                Producto: {{ pedidoProducto || '—' }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Cantidad Solicitada *</label
                        >
                        <input
                            v-model.number="pedidoCantidad"
                            type="number"
                            min="1"
                            required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Notas Adicionales</label
                        >
                        <textarea
                            v-model="pedidoNotas"
                            rows="2"
                            placeholder="Especificaciones adicionales..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none"
                        ></textarea>
                    </div>
                </div>
                <div
                    class="flex flex-col gap-3 rounded-b-xl border-t border-gray-200 bg-gray-50 px-6 py-4 sm:flex-row-reverse"
                >
                    <button
                        type="button"
                        @click="confirmarPedido"
                        class="w-full rounded-lg bg-blue-600 px-5 py-2.5 font-medium text-white transition-colors hover:bg-blue-700 sm:w-auto"
                    >
                        Enviar Solicitud
                    </button>
                    <button
                        type="button"
                        @click="showPedidoModal = false"
                        class="w-full rounded-lg border border-gray-300 bg-white px-5 py-2.5 font-medium text-gray-700 transition-colors hover:bg-gray-50 sm:w-auto"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
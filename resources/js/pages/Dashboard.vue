<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import { ref, onMounted } from 'vue';

// Datos de ejemplo (estos vendrían de tu backend)
const stats = ref({
    sucursales: { total: 3, activas: 3 },
    productos: { total: 156, stockBajo: 12, valorInventario: 45280.50 },
    clientes: { total: 89, activos: 76, puntosTotales: 2340 },
    proveedores: { total: 24, activos: 22 },
    ventas: { hoy: 3280.50, mes: 45280.75, pendientes: 8 },
    promociones: { activas: 5, total: 8 }
});

// Datos para gráficas
const ventasSemana = ref([2850, 3200, 2980, 4100, 3850, 5200, 4900]);
const productosTop = ref([
    { nombre: 'Leche Gloria', ventas: 145 },
    { nombre: 'Arroz Costeño', ventas: 132 },
    { nombre: 'Aceite Primor', ventas: 98 },
    { nombre: 'Azúcar Rubia', ventas: 87 },
    { nombre: 'Fideos Don Vittorio', ventas: 76 }
]);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// Formatear moneda
const formatMoney = (value: number) => {
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        minimumFractionDigits: 2
    }).format(value);
};
</script>

<template>
    <Head title="Dashboard - NetMarket" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gray-50 p-6">
            <!-- Header del Dashboard -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800 md:text-3xl">Dashboard NetMarket</h1>
                    
                </div>
                <div class="flex items-center gap-2 rounded-lg bg-white px-4 py-2 shadow-sm">
                    <span class="text-sm text-gray-600">{{ new Date().toLocaleDateString('es-PE', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
                </div>
            </div>

            <!-- KPIs Principales - Grid 4 columnas -->
            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Ventas Hoy -->
                <div class="rounded-xl bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Ventas Hoy</p>
                            <h3 class="mt-1 text-2xl font-bold text-gray-800">{{ formatMoney(stats.ventas.hoy) }}</h3>
                            <p class="mt-1 text-xs text-green-600">+12.5% vs ayer</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-50">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Productos -->
                <div class="rounded-xl bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Productos</p>
                            <h3 class="mt-1 text-2xl font-bold text-gray-800">{{ stats.productos.total }}</h3>
                            <p class="mt-1 text-xs text-orange-600">{{ stats.productos.stockBajo }} con stock bajo</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-50">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Clientes -->
                <div class="rounded-xl bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Clientes</p>
                            <h3 class="mt-1 text-2xl font-bold text-gray-800">{{ stats.clientes.total }}</h3>
                            <p class="mt-1 text-xs text-purple-600">{{ stats.clientes.activos }} activos</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-50">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Proveedores -->
                <div class="rounded-xl bg-white p-5 shadow-sm transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Proveedores</p>
                            <h3 class="mt-1 text-2xl font-bold text-gray-800">{{ stats.proveedores.total }}</h3>
                            <p class="mt-1 text-xs text-blue-600">{{ stats.proveedores.activos }} activos</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-50">
                            <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid Principal: Gráficas y Resumen -->
            <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Gráfica de Ventas Semanales (ocupa 2 columnas) -->
                <div class="col-span-1 lg:col-span-2 rounded-xl bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-800">Ventas de la Semana</h2>
                        <select class="rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-600 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
                            <option>Últimos 7 días</option>
                            <option>Últimos 30 días</option>
                            <option>Este mes</option>
                        </select>
                    </div>
                    
                    <!-- Gráfica de barras simple con Tailwind -->
                    <div class="flex h-48 items-end justify-between gap-2">
                        <div v-for="(venta, index) in ventasSemana" :key="index" class="group relative flex flex-1 flex-col items-center">
                            <div class="w-full rounded-t-lg bg-blue-500 transition-all group-hover:bg-blue-600" 
                                 :style="{ height: (venta / 6000) * 150 + 'px' }"></div>
                            <span class="mt-2 text-xs text-gray-500">D{{ index + 1 }}</span>
                            <span class="absolute -top-8 hidden rounded bg-gray-800 px-2 py-1 text-xs text-white group-hover:block">
                                {{ formatMoney(venta) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Alertas y Notificaciones -->
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-gray-800">Alertas</h2>
                    <div class="space-y-3">
                        <!-- Stock Bajo -->
                        <div class="flex items-center gap-3 rounded-lg bg-orange-50 p-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-100">
                                <svg class="h-4 w-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800">{{ stats.productos.stockBajo }} productos con stock bajo</p>
                                <p class="text-xs text-gray-500">Revisar inventario</p>
                            </div>
                            <span class="text-xs font-medium text-orange-600">Urgente</span>
                        </div>

                        <!-- Pedidos Pendientes -->
                        <div class="flex items-center gap-3 rounded-lg bg-blue-50 p-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100">
                                <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800">{{ stats.ventas.pendientes }} pedidos por entregar</p>
                                <p class="text-xs text-gray-500">Clientes esperando</p>
                            </div>
                            <span class="text-xs font-medium text-blue-600">Pendiente</span>
                        </div>

                        <!-- Productos por Vencer -->
                        <div class="flex items-center gap-3 rounded-lg bg-red-50 p-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-red-100">
                                <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-800">8 productos por vencer</p>
                                <p class="text-xs text-gray-500">Próximos 7 días</p>
                            </div>
                            <span class="text-xs font-medium text-red-600">Crítico</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segunda Fila: Resumen por Módulos -->
            <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Resumen Sucursales -->
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">Sucursales</h3>
                        <span class="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700">{{ stats.sucursales.activas }}/{{ stats.sucursales.total }} activas</span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Sucursal Centro</span>
                            <span class="font-medium text-green-600">● Activo</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Sucursal Norte</span>
                            <span class="font-medium text-green-600">● Activo</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Sucursal Sur</span>
                            <span class="font-medium text-green-600">● Activo</span>
                        </div>
                    </div>
                </div>

                <!-- Resumen Productos Destacados -->
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <h3 class="mb-3 font-semibold text-gray-800">Productos más vendidos</h3>
                    <div class="space-y-2">
                        <div v-for="(producto, index) in productosTop.slice(0, 3)" :key="index" class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">{{ producto.nombre }}</span>
                            <span class="font-medium text-gray-800">{{ producto.ventas }} uds.</span>
                        </div>
                    </div>
                </div>

                <!-- Resumen Proveedores -->
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <h3 class="mb-3 font-semibold text-gray-800">Próximos pedidos</h3>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Distribuidora Central</span>
                            <span class="text-xs text-gray-500">Mañana</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Lácteos Perú</span>
                            <span class="text-xs text-gray-500">En 3 días</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Abras del Norte</span>
                            <span class="text-xs text-gray-500">En 5 días</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Últimos Movimientos -->
            <div class="rounded-xl bg-white shadow-sm">
                <div class="border-b border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-800">Últimas Ventas</h2>
                        <button class="text-sm text-blue-600 hover:text-blue-800">Ver todas</button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Cliente</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Producto</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Monto</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Estado</th>
                                <th class="px-5 py-3 text-left font-semibold text-gray-700">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-xs font-bold text-blue-700">M</span>
                                        </div>
                                        <span class="font-medium text-gray-800">María López</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-gray-600">Leche Gloria x2</td>
                                <td class="px-5 py-3 font-medium text-gray-800">S/ 18.50</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Completado</span>
                                </td>
                                <td class="px-5 py-3 text-gray-500">Hace 2h</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center">
                                            <span class="text-xs font-bold text-purple-700">J</span>
                                        </div>
                                        <span class="font-medium text-gray-800">Juan Pérez</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-gray-600">Arroz Costeño 5kg</td>
                                <td class="px-5 py-3 font-medium text-gray-800">S/ 24.90</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700">Pendiente</span>
                                </td>
                                <td class="px-5 py-3 text-gray-500">Hace 4h</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center">
                                            <span class="text-xs font-bold text-emerald-700">C</span>
                                        </div>
                                        <span class="font-medium text-gray-800">Carlos Ruiz</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-gray-600">Aceite Primor x3</td>
                                <td class="px-5 py-3 font-medium text-gray-800">S/ 32.70</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Completado</span>
                                </td>
                                <td class="px-5 py-3 text-gray-500">Hace 5h</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
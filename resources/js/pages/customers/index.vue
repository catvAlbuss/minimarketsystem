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
    // isEditing.value = null;
    form.reset();
    deleteForm.reset();
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

<template class="bg-gray-100" style="font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;color:#1f2937;">

    <Head title="Customers" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div> -->

        <!-- Main Content -->
        <main class="main-tw min-h-screen bg-gray-100 transition-all duration-300">
            <!-- Top Bar -->
            <header class="bg-white flex justify-between items-center sticky top-0 z-40"
                style="padding:15px 30px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div class="flex items-center gap-5">
                    <button class="btn-menu-tw bg-transparent border-none cursor-pointer text-gray-700" id="menuToggle"
                        style="font-size:1.5em;padding:8px;display:none;"><i class="bi bi-list"></i></button>
                    <h1 class="text-2xl font-semibold text-gray-800">Gestion de Clientes</h1>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2.5 bg-gray-100 rounded-full" style="padding:8px 15px;">
                        <i class="bi bi-person-circle text-2xl" style="color:#2b4485;"></i>
                        <span class="font-medium text-gray-800" id="userName">Clientes</span>
                    </div>
                </div>
            </header>

            <!-- Clientes Section -->
            <section style="padding:30px;">

                <!-- KPIs Clientes -->
                <div class="kpi-grid mb-4">
                    <div class="kpi-card">
                        <p class="kpi-label">Total Clientes</p>
                        <h3 class="kpi-value" id="kpiTotalClientes">0</h3>
                    </div>
                    <div class="kpi-card">
                        <p class="kpi-label">Clientes Activos</p>
                        <h3 class="kpi-value" id="kpiClientesActivos">0</h3>
                    </div>
                    <div class="kpi-card">
                        <p class="kpi-label">Nuevos este Mes</p>
                        <h3 class="kpi-value" id="kpiNuevosMes">0</h3>
                    </div>
                    <div class="kpi-card">
                        <p class="kpi-label">Puntos por Canjear</p>
                        <h3 class="kpi-value" id="kpiPuntosCanjear">0</h3>
                    </div>
                </div>

                <!-- Barra de Búsqueda y Filtros -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- FILTRAR CLIENTES -->
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control text-black" id="buscarCliente"
                                        placeholder="Buscar por nombre, DNI o WhatsApp..." onkeyup="filtrarClientes()">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select text-black" id="filtroPreferencia"
                                    onchange="filtrarClientes()">
                                    <option value="">Todas las Preferencias</option>
                                    <option value="Bebidas / Licores">Bebidas / Licores</option>
                                    <option value="Limpieza / Hogar">Limpieza / Hogar</option>
                                    <option value="Abarrotes">Abarrotes</option>
                                    <option value="Mascotas">Mascotas</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select text-black" id="filtroEstado" onchange="filtrarClientes()">
                                    <option value="">Todos los Estados</option>
                                    <option value="Frecuente">Frecuente</option>
                                    <option value="Normal">Normal</option>
                                    <option value="VIP">VIP</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted" id="resultadoBusqueda">Mostrando todos los clientes</small>
                        </div>
                    </div>
                </div>

            </section>

            <!-- CREAR CLIENTES -->
            <section class="border border-sidebar-border/70 bg-background p-4">
                <h1 class="text-xl font-semibold">
                    {{ isEditing ? 'Editar cliente' : 'Nuevo cliente' }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Gestiona clientes desde esta misma vista.
                </p>

                <form class="mt-4 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                    <!-- CAMPO NOMBRE -->
                    <div class="grid gap-2">
                        <Label for="nombre">Nombre</Label>
                        <Input id="nombre" v-model="form.name" type="text" placeholder="Ej: Juan Pérez" required />
                        <InputError :message="form.errors.name" />
                    </div>
                    <!-- CAMPO APELLIDO -->
                    <div class="grid gap-2">
                        <Label for="apellido">Apellido</Label>
                        <Input id="apellido" v-model="form.last_name" type="text" placeholder="Ej: Pérez" required />
                        <InputError :message="form.errors.last_name" />
                    </div>
                    <!-- CAMPO DNI -->
                    <div class="grid gap-2">
                        <Label for="dni">DNI</Label>
                        <Input id="dni" v-model="form.dni" type="text" placeholder="Ej: 12345678" required
                            :readonly="isEditing" />
                        <InputError :message="form.errors.dni" />
                    </div>
                    <!-- CAMPO CELULAR -->
                    <div class="grid gap-2">
                        <Label for="celular">Celular</Label>
                        <Input id="celular" v-model="form.phone" type="text" placeholder="Ej: 987654321" required />
                        <InputError :message="form.errors.phone" />
                    </div>
                    <!-- CAMPO EMAIL -->
                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input id="email" v-model="form.email" type="email" placeholder="Ej: juan@ejemplo.com" required :readonly="isEditing"/>
                        <InputError :message="form.errors.email" />
                    </div>
                    <!-- CAMPO DIRECCION -->
                    <div class="grid gap-2">
                        <Label for="direccion">Dirección</Label>
                        <Input id="direccion" v-model="form.address" type="text" placeholder="Ej: Av. Principal 123" />
                        <InputError :message="form.errors.address" />
                    </div>
                    <!-- CAMPO CUMPLEAÑOS -->
                    <div class="grid gap-2">
                        <Label for="cumpleanos">Cumpleaños</Label>
                        <Input class="[color-scheme:dark]" id="cumpleanos" v-model="form.birthday" type="date"
                            placeholder="Ej: 1990-01-01" />
                        <InputError :message="form.errors.birthday" />
                    </div>
                    <!-- CAMPO SCORE -->
                    <div class="grid gap-2">
                        <Label for="puntos">Puntos</Label>
                        <Input class="[color-scheme:dark]" id="puntos" v-model="form.score" type="number"
                            placeholder="Ej: 100" />
                        <InputError :message="form.errors.score" />
                    </div>
                    <!-- CAMPO DE ESTADO: ACTIVO O INACTIVO -->
                    <div class="grid gap-2">
                        <Label for="estado">Estado</Label>
                        <select id="estado" v-model="form.state" required placeholder="Ej: Activo o Inactivo"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <!-- <option v-for="estado in form.state" :key="estado" :value="estado">
                                {{ estado }}
                            </option> -->
                            <option value="active">Activo</option>
                            <option value="inactive">Inactivo</option>
                        </select>
                        <InputError :message="form.errors.state" />
                    </div>

                    <!-- <div class="grid gap-2">
                        <Label for="password">
                            Password
                            <span v-if="isEditing" class="text-xs text-muted-foreground">(opcional en edicion)</span>
                        </Label>
                        <Input id="password" v-model="form.password" type="password" :required="!isEditing" />
                        <InputError :message="form.errors.password" />
                    </div> -->

                    <!-- <div class="grid gap-2">
                        <Label for="password_confirmation">Confirmar password</Label>
                        <Input id="password_confirmation" v-model="form.password_confirmation" type="password"
                            :required="!isEditing" />
                    </div> -->

                    <div class="col-span-full flex gap-2">
                        <Button type="submit" :disabled="form.processing || deleteForm.processing">
                            {{ isEditing ? 'Actualizar' : 'Crear' }}
                        </Button>
                        <Button v-if="isEditing" type="button" variant="secondary"
                            :disabled="form.processing || deleteForm.processing" @click="resetForm">
                            Cancelar
                        </Button>
                    </div>

                </form>
            </section>

            <!-- MOSTRAR CLIENTES -->
            <section class="border border-sidebar-border/70 bg-background p-4">
                <h2 class="text-lg font-semibold">Listado de clientes</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-2 py-2">ID</th>
                                <th class="px-2 py-2">Dni</th>
                                <th class="px-2 py-2">Nombre</th>
                                <th class="px-2 py-2">Apellido</th>
                                <th class="px-2 py-2">Whatsapp</th>
                                <th class="px-2 py-2">Correo</th>
                                <th class="px-2 py-2">Direccion</th>
                                <th class="px-2 py-2">Cumpleaños</th>
                                <th class="px-2 py-2">Puntos</th>
                                <th class="px-2 py-2">Estado</th>
                                <th class="px-2 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="customer.length === 0">
                                <td colspan="6" class="px-2 py-4 text-center text-muted-foreground">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                            <tr v-for="customers in customer" :key="customers.id" class="border-b">
                                <td class="px-2 py-2">{{ customers.id }}</td>
                                <td class="px-2 py-2">{{ customers.dni }}</td>
                                <td class="px-2 py-2">{{ customers.name }}</td>
                                <td class="px-2 py-2">{{ customers.last_name }}</td>
                                <td class="px-2 py-2">{{ customers.phone ?? '-' }}</td>
                                <td class="px-2 py-2">{{ customers.email ?? '-' }}</td>
                                <td class="px-2 py-2">{{ customers.address ?? '-' }}</td>
                                <td class="px-2 py-2">{{ customers.birthday }}</td>
                                <td class="px-2 py-2">{{ customers.score ?? '-' }}</td>
                                <td class="px-2 py-2">{{ customers.state ?? '-' }}</td>
                                <td class="px-2 py-2">
                                    <div class="flex gap-2">
                                        <Button type="button" variant="secondary" size="sm"
                                            :disabled="form.processing || deleteForm.processing"
                                            @click="startEdit(customers)">
                                            Editar
                                        </Button>
                                        <Button type="button" variant="destructive" size="sm"
                                            :disabled="form.processing || deleteForm.processing"
                                            @click="remove(customers)">
                                            Eliminar
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <InputError :message="deleteError" class="mt-3" />
            </section>

        </main>

    </AppLayout>

</template>

<style>
.nav-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 14px 25px;
    color: #374151;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.nav-item i {
    font-size: 1.2em;
    width: 24px;
    text-align: center;
}

.nav-item:hover {
    background: #f9fafb;
    color: #2b4485;
    border-left-color: #2b4485;
}

.nav-item.active {
    background: rgba(43, 68, 133, 0.1);
    color: #2b4485;
    border-left-color: #2b4485;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

@media(max-width:1200px) {
    .kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media(max-width:768px) {
    .kpi-grid {
        grid-template-columns: 1fr;
    }
}

.kpi-card {
    background: white;
    padding: 24px 20px;
    border-radius: 16px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    min-height: 110px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.kpi-label {
    font-size: 0.85em;
    color: #6b7280;
    margin-bottom: 8px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.kpi-value {
    font-size: 1.75em;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.btn-logout:hover {
    background: #ef4444 !important;
    color: white !important;
}

.cliente-card {
    background: white;
    border-radius: 12px;
    padding: 15px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 3px solid #2b4485;
    height: 100%;
}

.cliente-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    border-color: #1a3366;
}

.cliente-card.inactivo {
    opacity: 0.7;
    background: #f8f9fa;
    border-color: #9ca3af;
}

.cliente-header {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e7eb;
}

.cliente-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2em;
    font-weight: bold;
    margin-right: 12px;
    flex-shrink: 0;
}

.cliente-info h5 {
    font-size: 0.95em;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 2px;
}

.cliente-detalles {
    margin: 10px 0;
}

.cliente-detalles p {
    margin-bottom: 6px;
    font-size: 0.8em;
    color: #4b5563;
    display: flex;
    align-items: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.cliente-detalles i {
    width: 16px;
    color: #2b4485;
    margin-right: 6px;
    font-size: 0.9em;
}

.cliente-preferencia {
    display: inline-block;
    padding: 3px 8px;
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
    border-radius: 12px;
    font-size: 0.7em;
    font-weight: 600;
    margin-bottom: 8px;
}

.cliente-info-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 10px;
    background: #f9fafb;
    border-radius: 8px;
    margin: 10px 0;
    font-size: 0.8em;
}

.cliente-info-bar .info-item {
    display: flex;
    align-items: center;
    gap: 5px;
    white-space: nowrap;
}

.cliente-info-bar .cumpleanos {
    color: #e74c3c;
}

.cliente-info-bar .puntos {
    color: #f39c12;
    font-weight: 600;
}

.cliente-info-bar .whatsapp {
    color: #25D366;
}

.cliente-estado {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.7em;
    font-weight: 600;
    margin-bottom: 8px;
}

.estado-frecuente {
    background: rgba(46, 204, 113, 0.15);
    color: #2ecc71;
}

.estado-normal {
    background: rgba(52, 152, 219, 0.15);
    color: #3498db;
}

.estado-vip {
    background: rgba(155, 89, 182, 0.15);
    color: #9b59b6;
}

.estado-inactivo {
    background: rgba(107, 114, 128, 0.15);
    color: #6b7280;
}

.btn-whatsapp-cliente {
    width: 100%;
    padding: 8px;
    background: #25D366;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9em;
}

.btn-whatsapp-cliente:hover {
    background: #128C7E;
    transform: translateY(-2px);
}

.btn-acciones-cliente {
    display: flex;
    gap: 4px;
    margin-top: 8px;
}

.btn-acciones-cliente .btn {
    flex: 1;
    padding: 6px;
    font-size: 0.8em;
}

.proveedor-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 2px solid transparent;
    height: 100%;
}

.proveedor-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    border-color: #2b4485;
}

.proveedor-card.inactivo {
    opacity: 0.7;
    background: #f8f9fa;
}

.proveedor-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 15px;
}

.proveedor-icon {
    width: 50px;
    height: 50px;
    background: #2b4485;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5em;
    margin-right: 15px;
}

.proveedor-info h5 {
    font-size: 1.1em;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 5px;
}

.proveedor-contacto {
    margin: 15px 0;
}

.proveedor-contacto p {
    margin-bottom: 8px;
    font-size: 0.9em;
    color: #4b5563;
}

.proveedor-contacto i {
    width: 20px;
    color: #2b4485;
    margin-right: 8px;
}

.proveedor-categoria {
    display: inline-block;
    padding: 4px 12px;
    background: #f9fafb;
    border-radius: 20px;
    font-size: 0.8em;
    font-weight: 600;
    color: #374151;
    margin-bottom: 15px;
}

.btn-whatsapp {
    width: 100%;
    padding: 10px;
    background: #25D366;
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-whatsapp:hover {
    background: #128C7E;
    transform: translateY(-2px);
}

.btn-acciones-proveedor {
    display: flex;
    gap: 5px;
    margin-top: 10px;
}

.btn-acciones-proveedor .btn {
    flex: 1;
    padding: 8px;
    font-size: 0.85em;
}

.badge-activo {
    background: rgba(16, 185, 129, 0.15);
    color: #10b981;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8em;
    font-weight: 600;
}

.badge-inactivo {
    background: rgba(107, 114, 128, 0.15);
    color: #6b7280;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.8em;
    font-weight: 600;
}

.card.sucursal-card {
    cursor: pointer !important;
    transition: all 0.3s ease !important;
    border: 2px solid transparent !important;
    background: white !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08) !important;
    position: relative !important;
    z-index: 1 !important;
}

.card.sucursal-card:hover {
    transform: translateY(-8px) !important;
    border-color: #2b4485 !important;
    box-shadow: 0 12px 35px rgba(43, 68, 133, 0.2) !important;
    background-color: #f8fafc !important;
    z-index: 10 !important;
}

.card.sucursal-card.active {
    border-color: #2b4485 !important;
    background-color: #f0f7ff !important;
    box-shadow: 0 8px 25px rgba(43, 68, 133, 0.15) !important;
}

.card.sucursal-card:active {
    transform: translateY(-4px) !important;
}

.kpi-mini {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.card.sucursal-card:hover .kpi-mini {
    background: white;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.kpi-mini-label {
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 5px;
    font-weight: 500;
}

.kpi-mini-value {
    font-size: 1.3rem;
    font-weight: 700;
    color: #111827;
}

.alerta-stock {
    animation: pulse 2s infinite;
}

@keyframes pulse {

    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }

    50% {
        opacity: 0.7;
        transform: scale(1.05);
    }
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
    width: 100%;
}

.chart-card {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    height: 420px;
    display: flex;
    flex-direction: column;
}

.chart-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.chart-header {
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e5e7eb;
    flex-shrink: 0;
}

.chart-header h4 {
    font-size: 1em;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.chart-container {
    height: 320px;
    position: relative;
    flex: 1;
}

@media(max-width:992px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

.tabla-venta-item {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }

    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.btn-cantidad {
    width: 30px;
    height: 30px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.total-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-top: 20px;
}

.total-section .total-label {
    font-size: 1.1em;
    opacity: 0.9;
}

.total-section .total-amount {
    font-size: 2em;
    font-weight: bold;
}

.producto-row {
    cursor: pointer;
    transition: all 0.2s;
}

.producto-row:hover {
    background-color: #f8f9fa;
}

.promo-card {
    border: 2px dashed #2b4485;
    border-radius: 16px;
    padding: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    height: 100%;
    background: linear-gradient(135deg, rgba(43, 68, 133, 0.05) 0%, rgba(43, 68, 133, 0.02) 100%);
}

.promo-card:hover {
    background: linear-gradient(135deg, rgba(43, 68, 133, 0.1) 0%, rgba(43, 68, 133, 0.05) 100%);
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.promo-card .promo-title {
    font-weight: 700;
    color: #2b4485;
    margin-bottom: 12px;
    font-size: 1.1em;
}

.promo-card .promo-products {
    font-size: 0.9em;
    color: #4b5563;
    margin-bottom: 15px;
}

.promo-card .promo-products ul {
    margin: 0;
    padding-left: 20px;
}

.promo-card .promo-products li {
    margin-bottom: 5px;
}

.promo-card .promo-price {
    font-size: 1.4em;
    font-weight: bold;
    color: #10b981;
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.promo-card .promo-price .precio-original {
    font-size: 0.7em;
    color: #6b7280;
    text-decoration: line-through;
}

.promo-card .promo-price .badge {
    font-size: 0.6em;
    padding: 5px 10px;
}

#contenedorPromociones {
    display: flex;
    gap: 20px;
    width: 100%;
}

#contenedorPromociones .col-md-6 {
    flex: 1;
    min-width: 0;
}

@media(max-width:768px) {
    #contenedorPromociones {
        flex-direction: column;
    }
}

.btn-action {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    margin-right: 5px;
}

@media(max-width:992px) {
    .sidebar-tw {
        transform: translateX(-100%);
    }

    .sidebar-tw.show {
        transform: translateX(0);
    }

    .main-tw {
        margin-left: 0 !important;
    }

    .btn-menu-tw {
        display: block !important;
    }
}

@media(min-width:993px) {
    .btn-menu-tw {
        display: none !important;
    }
}
</style>
<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
// import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import BuyController from '@/actions/App/Http/Controllers/BuyController';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Compras',
        href: './buys',
    },
];
 //
// type Category = {
//     id: number;
//     name: string;
//     description: string;
// }

// type Product = {
//     id: number;
//     id_categories: number | null;
//     code: string;
//     name: string;
//     description: string;
//     unit_price: number;
//     higher_price: number | null;
//     stock: number;
//     expiration_date: string;
//     promotion_discount: number;
//     state: 'active' | 'inactive';
// };

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

type Buy = {
    id: number,
    id_provider: number,
    id_users: number,
    voucher_number: string,
    total: number,
    payment_method: 'cash' | 'card' | 'yape' | 'plin',
    payment_status: 'cancelled'|'pending'|'delivered',
    date_time: string,
};

type Props = {
    buys: Buy[];
    // products: Product[];
    // categories: Category[];
    providers: Providers[];
    users: User[]
};

const props = defineProps<Props>();
const buys = computed(() => props.buys);
const providers = computed(() => props.providers);
// const products = computed(() => props.products);
// const categories = computed(() => props.categories);
const users = computed(() => props.users);

const editingId = ref<number | null>(null);

const form = useForm({
    id_provider: props.providers?.[0]?.id ?? '',
    id_users: props.users?.[0]?.id ?? '',
    voucher_number: '',
    total: 0,
    payment_method: '',
    payment_status: '',
    date_time: '',
});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);

const isEditing = computed(() => editingId.value !== null);

const resetForm = (): void => {
    editingId.value=null;
    form.reset();
    form.clearErrors();
    form.id_provider = props.providers?.[0]?.id ?? '';
    form.id_users = props.users?.[0]?.id ?? '';
};

const startEdit = (buys: Buy): void => {
    editingId.value = buys.id;
    form.clearErrors();
    form.id_provider = buys.id_provider ?? props.providers?.[0]?.id ?? '';
    form.id_users = buys.id_users ?? props.users?.[0]?.id ?? '';
    form.voucher_number = buys.voucher_number;
    form.total = buys.total;
    form.payment_method = buys.payment_method;
    form.payment_status = buys.payment_status;
    form.date_time = buys.date_time;
};

// const SaleItems = ref<Sale[]>([]); //Array reactivo que almacena los productos agregados

// const searchQuery = ref<string>('');
// const paymentMethod = ref<string>('cash');
// const state = ref<string>('paid');

    //ERROR
//Agrega a las ventas los productos seleccionados
// const addToSale = (product: Product) => {
//     const stockDisponible = parseInt(product.stock);

//     const existItem = SaleItems.value.find(item => item.id == product.id);

//     //si existe el item suma la cantidad
//     if (existItem) {
//         const newQuanti = existItem.quantity + 1;

//         //valida si la nueva cantidad no es mayor al stockDisponible
//         if (newQuanti > stockDisponible) {
//             alert(`Stock insuficiente para ${product.name}. 
//                    Disponible: ${stockDisponible} | Solicitado: ${newQuanti}`);
//             return;
//         }
//         existItem.quantity = newQuanti;
//         existItem.total = existItem.price_unit * existItem.quantity;
//     } else {
//         const newItem: Sale = {
//             id: product.id,
//             name: product.name,
//             quantity: 1,
//             price_unit: product.price_unit,
//             total: product.price_unit * 1,
//             method: paymentMethod.value,
//             status: state.value,
//             max_stock: stockDisponible
//         };
//         SaleItems.value.push(newItem);
//         // console.log('Producto agregado:', newItem);
//     }
// };

//Actualiza la cantidad de los productos agregados a la ventana de venta
// const updateQuantity = (index: number, newQuantity: number | string) => {
//     let quantity = typeof newQuantity === 'string' ? parseInt(newQuantity) : newQuantity; //convierte a numero para asegurar la validez

//     // Si no es un número válido, establecer a 1
//     if (isNaN(quantity) || quantity < 1) {
//         quantity = 1;
//     }

//     const item = SaleItems.value[index];
//     const product = products.value.find(p => p.id === item.id);

// }

//Elimina un producto de la ventana de ventas
// const removeItemSale = (index: number) => {
//     SaleItems.value.splice(index, 1);
// }

//estado
// const currentPage = ref(1);
// const itemsPage = 8;

//Productos de la paginacian actual
// const paginatedProducts = computed(() => {
//     const start = (currentPage.value - 1) * itemsPage;
//     const end = start + itemsPage;
//     return products.value.slice(start, end);
// });

//total de paginas
// const totalPages = computed(() => {
//     return Math.ceil(products.value.length / itemsPage);
// });

//funcion para camibar de pagina
// const changePage = (page: number) => {
//     if (page >= 1 && page <= totalPages.value) {
//         currentPage.value = page;
//     }
// };

//

// const subTotal = computed(() => {
//     return SaleItems.value.reduce((suma, item) => suma + item.total, 0);
// });

// const iva = computed(() => {
//     return (Number(subTotal.value) * 0.18).toFixed(2);
// });

// const total = computed(() => {
//     return Number(subTotal.value) + Number(iva.value);
// });

const submit = (): void => {
    // const data = JSON.stringify(form);
    // console.log('Submitting form with data:', data);
    const options = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };
    if (isEditing.value && editingId.value !== null) {
        form.put(BuyController.update.url(editingId.value), options);
        return;
    }
    form.post(BuyController.store.url(), options);
};

const remove = (buys: Buy): void => {
    if (!confirm(`Eliminar venta "${buys.voucher_number}"?`)) {
        return;
    }

    deleteForm.delete(BuyController.destroy.url(buys.id), {
        preserveScroll: true,
    });
};


// const procesSale = async () => {
//     if (SaleItems.value.length === 0) {
//         alert('Selecciona un producto');
//         return;
//     }

//     if (!confirm('¿Confirmar la venta?')) {
//         return;
//     }

    
//     const data = {
//         items: SaleItems.value.map(item => ({
//             id: item.id,
//             name: item.name,
//             quantity: item.quantity,
//             status: item.status,
//             method: item.method,
//             total: item.total
//         })),
//         total: total.value,
//         state: state.value,
//         payment: paymentMethod.value,
//     };

//     console.log(data);
//     const box = JSON.stringify(data);
//     console.log(box);

//     router.post('/box', data);
// }

</script>

<template class="bg-gray-100" style="font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;color:#1f2937;">

    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- Main Content -->
        <main class="main-tw min-h-screen bg-gray-100 transition-all duration-300">
            <!-- Top Bar -->
            <header class="bg-white flex justify-between items-center sticky top-0 z-40"
                style="padding:15px 30px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div class="flex items-center gap-5">
                    <button class="btn-menu-tw bg-transparent border-none cursor-pointer text-gray-700" id="menuToggle"
                        style="font-size:1.5em;padding:8px;display:none;"><i class="bi bi-list"></i></button>
                    <h1 class="text-2xl font-semibold text-gray-800">Gestion de Compras</h1>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2.5 bg-gray-100 rounded-full" style="padding:8px 15px;">
                        <i class="bi bi-person-circle text-2xl" style="color:#2b4485;"></i>
                        <span class="font-medium text-gray-800" id="userName">Compras</span>
                    </div>
                </div>
            </header>

            <!-- CREAR CLIENTES -->
            <section class="border border-sidebar-border/70 bg-background p-4">
                <h1 class="text-xl font-semibold">
                    {{ isEditing ? 'Editar producto' : 'Nuevo producto' }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Gestiona los productos desde esta misma vista.
                </p>

                <form class="mt-4 grid gap-4 md:grid-cols-2" @submit.prevent="submit">

                    <!-- CAMPO ID_PROVEEDOR -->
                    <div class="grid gap-2">
                        <Label for="id_provider">Proveedor</Label>
                        <select id="id_provider" v-model="form.id_provider" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" class="text-sm">Seleccione</option>
                            <option v-for="p in providers" :key="p.id" :value="p.id">
                                {{ p.company_name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.id_provider" />
                    </div>
                    <!-- CAMPO ID_USUARIO -->
                    <div class="grid gap-2">
                        <Label for="id_users">Usuario</Label>
                        <select id="id_users" v-model="form.id_users" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" disabled>Seleccione</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">
                                {{ u.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.id_users" />
                    </div>
                    <!-- CAMPO NÚMERO DE COMPROBANTE -->
                    <div class="grid gap-2">
                        <Label for="voucher_number">Número de comprobante </Label>
                        <Input id="voucher_number" v-model="form.voucher_number" type="text" placeholder="Ej: ajd12312asd" required />
                        <InputError :message="form.errors.voucher_number" />
                    </div>
                    <!-- CAMPO TOTAL -->
                    <div class="grid gap-2">
                        <Label for="total">Total</Label>
                        <Input id="total" v-model="form.total" type="text" placeholder="Ej: Descripción"
                            required />
                        <InputError :message="form.errors.total" />
                    </div>
                    <!-- CAMPO METODO DE PAGO -->
                    <div class="grid gap-2">
                        <Label for="payment_method">Método de pago</Label>
                        <select id="payment_method" v-model="form.payment_method" required placeholder="Ej: Seleccione"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" disabled selected>Seleccione</option>
                            <option value="cash">Efectivo</option>
                            <option value="card">Tarjeta</option>
                            <option value="yape">Yape</option>
                            <option value="plin">Plin</option>
                        </select>
                        <InputError :message="form.errors.payment_method" />
                    </div>
                    <!-- CAMPO ESTADO DE PAGO -->
                    <div class="grid gap-2">
                        <Label for="payment_status">Estado de pago</Label>
                        <select id="payment_status" v-model="form.payment_status" required placeholder="Ej: Seleccione"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" disabled selected>Seleccione</option>
                            <option value="cancelled">Cancelado</option>
                            <option value="pending">Pendiente</option>
                            <option value="delivered">Entregado</option>
                        </select>
                        <InputError :message="form.errors.payment_status" />
                    </div>
                    <!-- CAMPO FECHA -->
                    <!-- <div class="grid gap-2">
                        <Label for="date_time">Fecha</Label>
                        <Input class="[color-scheme:dark]" id="date_time" v-model="form.date_time" type="date" placeholder="Ej: Descripción"
                            />
                        <InputError :message="form.errors.date_time" />
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
                <h2 class="text-lg font-semibold">Listado de Ventas</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-2 py-2">ID</th>
                                <th class="px-2 py-2">ID Proveedor</th>
                                <th class="px-2 py-2">ID Usuario</th>
                                <th class="px-2 py-2">Comprobante de pago</th>
                                <th class="px-2 py-2">Total</th>
                                <th class="px-2 py-2">Método de pago</th>
                                <th class="px-2 py-2">Estado de pago</th>
                                <th class="px-2 py-2">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="buys.length === 0">
                                <td colspan="6" class="px-2 py-4 text-center text-muted-foreground">
                                    No hay productos registrados.
                                </td>
                            </tr>
                            <tr v-for="b in buys" :key="b.id" class="border-b">
                                <td class="px-2 py-2">{{ b.id }}</td>
                                <td class="px-2 py-2">{{ b.id_provider }}</td>
                                <td class="px-2 py-2">{{ b.id_users }}</td>
                                <td class="px-2 py-2">{{ b.voucher_number }}</td>
                                <td class="px-2 py-2">{{ b.total }}</td>
                                <td class="px-2 py-2">{{ b.payment_method }}</td>
                                <td class="px-2 py-2">{{ b.payment_status }}</td>
                                <td class="px-2 py-2">{{ b.date_time }}</td>
                                <td class="px-2 py-2">
                                    <!-- <div class="flex gap-2">
                                        <Button type="button" variant="secondary" size="sm"
                                            :disabled="form.processing || deleteForm.processing" @click="startEdit(s)">
                                            Editar
                                        </Button>
                                        <Button type="button" variant="destructive" size="sm"
                                            :disabled="form.processing || deleteForm.processing" @click="remove(s)">
                                            Eliminar
                                        </Button>
                                    </div> -->
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
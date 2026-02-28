<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import SaleDetailsController from '@/actions/App/Http/Controllers/SaleDetailController';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Detalle de ventas',
        href: './sale_details',
    },
];

// type Category = {
//     id: number;
//     name: string;
//     description: string;
// }

type Product = {
    id: number;
    id_categories: number | null;
    code: string;
    name: string;
    description: string;
    unit_price: number;
    higher_price: number | null;
    stock: number;
    expiration_date: string;
    promotion_discount: number;
    state: 'active' | 'inactive';
};

// type Customer = {
//     id: number;
//     dni: string;
//     name: string;
//     last_name: string;
//     birthday: string;
//     email: string;
//     phone: string;
//     address: string;
//     score: number;
//     state: string;
// };

// type User = {
//     id: number,
//     name: string,
//     lastname: string,
//     dni: number,
//     phone: number,
//     address: string,
//     email: string,
//     children: number,
//     affiliate: string,
//     insured: string,
//     work_modality: string,
//     entry_date: string,
//     retention: string,
//     entry_to_payroll: string,
//     role: string,
//     state: string,
// };

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

type SaleDetails= {
    id: number,
    id_sales: number,
    id_products: number,
    quantity: number,
    discount: number,
    sub_total: number,
}

type Props = {
    sales: Sale[];
    products: Product[];
    // categories: Category[];
    sale_details: SaleDetails[];
    // customers: Customer[];
    // users: User[]
};

const props = defineProps<Props>();
const sales = computed(() => props.sales);
const products = computed(() => props.products);
// const categories = computed(() => props.categories);
const sale_details = computed(() => props.sale_details);

const editingId = ref<number | null>(null);

const form = useForm({
    id_sales: props.sales?.[0]?.id ?? '',
    id_products: props.products?.[0]?.id ?? '',
    quantity: 1,
    discount: 0,
    sub_total: 0,
});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);

const isEditing = computed(() => editingId.value !== null);

const resetForm = (): void => {
    editingId.value=null;
    form.reset();
    form.clearErrors();
    form.id_sales = props.sales?.[0]?.id ?? '';
    form.id_products = props.products?.[0]?.id ?? '';
};

const startEdit = (sale_details: SaleDetails): void => {
    editingId.value = sale_details.id;
    form.clearErrors();
    form.id_sales = sale_details.id_sales ?? props.sales?.[0]?.id ?? '';
    form.id_products = sale_details.id_products ?? props.products?.[0]?.id ?? '';
    form.quantity = sale_details.quantity;
    form.discount = sale_details.discount;
    form.sub_total = sale_details.sub_total;
    // form.payment_method = sales.payment_method;
    // form.voucher = sales.voucher;
    // form.document = sales.document;
    // form.date_time = sales.date_time;
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
        form.put(SaleDetailsController.update.url(editingId.value), options);
        return;
    }
    form.post(SaleDetailsController.store.url(), options);
};

const remove = (sale_details: SaleDetails): void => {
    if (!confirm(`Eliminar detalle de venta "${sale_details.id}"?`)) {
        return;
    }

    deleteForm.delete(SaleDetailsController.destroy.url(sale_details.id), {
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

    <Head title="sale_details" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <!-- Main Content -->
        <main class="main-tw min-h-screen bg-gray-100 transition-all duration-300">
            <!-- Top Bar -->
            <header class="bg-white flex justify-between items-center sticky top-0 z-40"
                style="padding:15px 30px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div class="flex items-center gap-5">
                    <button class="btn-menu-tw bg-transparent border-none cursor-pointer text-gray-700" id="menuToggle"
                        style="font-size:1.5em;padding:8px;display:none;"><i class="bi bi-list"></i></button>
                    <h1 class="text-2xl font-semibold text-gray-800">Gestion de Detalle de ventas</h1>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2.5 bg-gray-100 rounded-full" style="padding:8px 15px;">
                        <i class="bi bi-person-circle text-2xl" style="color:#2b4485;"></i>
                        <span class="font-medium text-gray-800" id="userName">Productos</span>
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

                    <!-- CAMPO VENTA -->
                    <div class="grid gap-2">
                        <Label for="id_sales">Venta</Label>
                        <select id="id_sales" v-model="form.id_sales" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" class="text-sm" disabled>Seleccione</option>
                            <option v-for="s in sales" :key="s.id" :value="s.id">
                                {{ s.voucher_number }}
                            </option>
                        </select>
                        <InputError :message="form.errors.id_sales" />
                    </div>
                    <!-- CAMPO PRODUCTO -->
                    <div class="grid gap-2">
                        <Label for="id_products">Producto</Label>
                        <select id="id_products" v-model="form.id_products" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" disabled>Seleccione</option>
                            <option v-for="p in products" :key="p.id" :value="p.id">
                                {{ p.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.id_products" />
                    </div>
                    <!-- CAMPO CANTIDAD -->
                    <div class="grid gap-2">
                        <Label for="quantity">Cantidad</Label>
                        <Input id="quantity" v-model="form.quantity" type="text" placeholder="Ej: ajd12312asd" required />
                        <InputError :message="form.errors.quantity" />
                    </div>
                    <!-- CAMPO DESCUENTO -->
                    <div class="grid gap-2">
                        <Label for="discount">Descuento</Label>
                        <Input id="discount" v-model="form.discount" type="text" placeholder="Ej: Yogurt" required/>
                        <InputError :message="form.errors.discount" />
                    </div>
                    <!-- CAMPO SUBTOTAL -->
                    <div class="grid gap-2">
                        <Label for="sub_total">SubTotal</Label>
                        <Input id="sub_total" v-model="form.sub_total" type="text" placeholder="Ej: Descripción"
                            required />
                        <InputError :message="form.errors.sub_total" />
                    </div>
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
                <h2 class="text-lg font-semibold">Listado de Detalle de ventas</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-2 py-2">ID</th>
                                <th class="px-2 py-2">ID Venta</th>
                                <th class="px-2 py-2">ID Producto</th>
                                <th class="px-2 py-2">Cantidad</th>
                                <th class="px-2 py-2">Descuento</th>
                                <th class="px-2 py-2">SubTotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="sale_details.length === 0">
                                <td colspan="6" class="px-2 py-4 text-center text-muted-foreground">
                                    No hay productos registrados.
                                </td>
                            </tr>
                            <tr v-for="s in sale_details" :key="s.id" class="border-b">
                                <td class="px-2 py-2">{{ s.id }}</td>
                                <td class="px-2 py-2">{{ s.id_sales }}</td>
                                <td class="px-2 py-2">{{ s.id_products }}</td>
                                <td class="px-2 py-2">{{ s.quantity }}</td>
                                <td class="px-2 py-2">{{ s.discount }}</td>
                                <td class="px-2 py-2">{{ s.sub_total }}</td>
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
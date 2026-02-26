<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { provide, ref } from 'vue';
import { computed } from 'vue';
import ProviderController from '@/actions/App/Http/Controllers/ProviderController';
import InputError from '@/components/InputError.vue';

type Promotion = {
    id: number;
    id_products: number;
    name_promotion: string;
    price: number;
    state: string;
};

type AddProducts ={
    id: number,
    name: string,
    unit_price: number,
    promotion_discount: number,
    price_discount: number
};

type Products = {
    id: number,
    name: string,
    unit_price: number,
    promotion_discount: number,
};

type Props = {
    promotions: Promotion[];
    products: Products[];
};

const AddProduct = ref<AddProducts[]>([]);
// const price = ref<number>();
const price = computed(()=>{
    return AddProduct.value.reduce((price,item) => price + item.unit_price, 0);
});

const props = defineProps<Props>();
const promotion = computed(() => props.promotions);
const product = computed(()=> props.products);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Promociones',
        href: '/',
    },
];

const editingId = ref<number | null>(null);
const isEditing = computed(() => editingId.value !== null);

const form = useForm({
    id_products: 0,
    name_promotion: '',
    price: 0,
    state: ''
});

const deleteForm = useForm({});
const deleteError = computed(
    () => (deleteForm.errors as Record<string, string | undefined>).delete,
);


const resetForm = (): void => {
    editingId.value = null;
    form.reset();
};

const submit = (): void => {
    const option = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };
    const box = JSON.stringify(form);
    console.log('Desde la condicion: ', box);
    if (isEditing.value && editingId.value !== null) {
        // console.log(form)
        const box = JSON.stringify(form);
        console.log('Desde la condicion: ', box);
        form.put(ProviderController.update.url(editingId.value), option);
        return;
    }

    form.post(ProviderController.store.url(), option);
};

const startEdit = (provider: Promotion): void => {
    editingId.value = provider.id;
    form.clearErrors();
    form.name_promotion = provider.name_promotion;
    form.price = provider.price;
    form.state = provider.state;
};

const remove = (promotion: Promotion): void => {
    if (!confirm(`Eliminar usuario "${promotion.name_promotion}"?`)) {
        return;
    }

    deleteForm.delete(ProviderController.destroy.url(promotion.id), {
        preserveScroll: true,
    });
};

const addProduct = (product: Products) =>{
    const existItem = AddProduct.value.find(item=>item.id = product.id)

    const newItem: AddProducts ={
        id: product.id,  
        name: product.name,
        unit_price: 2,
        promotion_discount: 2,
        price_discount: 3
    };
    AddProduct.value.push(newItem);
};

// const autocompletDescription=()=> {
//     const selectProd =  product.value.find(p =>p.id === form.id_products);
//     if (selectProd) {
//         form.description_products = selectProd.code;
//     }else{
//         form.description_products = '';
//     }
// };
</script>

<template>

    <Head title="Promociones" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
                <h1 class="text-xl font-semibold">
                    {{ isEditing ? 'Editar Promocion' : 'Nuevo Promocion' }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Gestiona todas las promociones de esta vista.
                </p>
                <table class="w-full min-w-[720px] text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-2 py-2">ID</th>
                                <th class="px-2 py-2">Nombre de Promocion</th>
                                <th class="px-2 py-2">Precio</th>
                                <th class="px-2 py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="product.length === 0">
                                <td colspan="6" class="px-2 py-4 text-center text-muted-foreground">
                                    No hay promociones registrados.
                                </td>
                            </tr>
                            <tr v-for="prom in promotion" :key="prom.id" class="border-b">
                                <td class="px-2 py-2">{{ prom.id }}</td>
                                <td class="px-2 py-2">
                                    {{ prom.name_promotion }}
                                </td>
                                <td class="px-2 py-2">{{ prom.price }}</td>
                                <td class="px-2 py-2">{{ prom.state }}</td>
                                <td class="px-2 py-2">
                                    <div class="flex gap-2">
                                        <!-- <Button type="button" variant="secondary" size="sm" :disabled="form.processing ||
                                            deleteForm.processing
                                            " @click="startEdit(prov)">
                                            Editar
                                        </Button> -->
                                        <!-- <Button type="button" variant="destructive" size="sm" :disabled="form.processing ||
                                            deleteForm.processing
                                            " @click="remove(prov)">
                                            Eliminar
                                        </Button> -->
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                <!-- <form class="mt-4 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="name_promotion">Nombre de la promocion</Label>
                        <Input id="name_promotion" class="text-white" v-model="form.name_promotion" type="text" required
                            placeholder="Ej: Jorge" />
                        <InputError :message="form.errors.name_promotion" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="price">precio</Label>
                        <Input id="price" v-model="form.price" type="text" required placeholder="Ej: Almeida" />
                        <InputError :message="form.errors.price" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="id_products">Selecciona un Producto</Label>
                        <select id="id_products" v-model="form.id_products" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            @change="autocompletDescription">
                            <option value="" disabled selected>
                                Tipo de producto
                            </option>
                            <option v-for="prod in product" :key="prod.id" :value="prod.id">{{ prod.id }}</option>
                        </select>
                        <InputError :message="form.errors.id_products" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Descripcion</Label>
                        <Input :disabled="isEditing" id="description_products" v-model="form.description_products" type="text" required
                            placeholder="Descripcion Producto" />
                        <InputError :message="form.errors.description_products" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="category">Categoria</Label>
                        <select id="category" v-model="form.category" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" disabled selected>
                                Tipo de categoria
                            </option>
                            <option :value="'wholesaler'">Mayorista</option>
                            <option :value="'retailer'">Minorista</option>
                            <option :value="'distributor'">Distribuidor</option>
                            <option :value="'manufacturer'">Fabricante</option>
                        </select>
                        <InputError :message="form.errors.category" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Estado</Label>
                        <select id="status" v-model="form.status" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" disabled selected>
                                tipo de estado
                            </option>
                            <option :value="'active'">Activo</option>
                            <option :value="'inactive'">Inactivo</option>
                        </select>
                        <InputError :message="form.errors.status" />
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
                </form> -->
            </section>
            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
                <h2 class="text-lg font-semibold">Listado de Productos</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-2 py-2">ID</th>
                                <th class="px-2 py-2">Nombre Producto</th>
                                <th class="px-2 py-2">P/U</th>
                                <th class="px-2 py-2">Descuento %</th>
                                <th class="px-2 py-2">Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="product.length === 0">
                                <td colspan="6" class="px-2 py-4 text-center text-muted-foreground">
                                    No hay productos registrados.
                                </td>
                            </tr>
                            <tr v-for="prod in product" :key="prod.id" class="border-b">
                                <td class="px-2 py-2">{{ prod.id }}</td>
                                <td class="px-2 py-2">
                                    {{ prod.name }}
                                </td>
                                <td class="px-2 py-2">{{ prod.unit_price }}</td>
                                <td class="px-2 py-2">{{ prod.promotion_discount }}</td>
                                <td class="px-2 py-2">
                                    <div class="flex gap-2">

                                        <Button type="button" variant="secondary" @click.stop="addProduct(prod)">
                                                Agregar
                                            </Button>
                                        <!-- <Button type="button" variant="secondary" size="sm" :disabled="form.processing ||
                                            deleteForm.processing
                                            " @click="startEdit(prov)">
                                            Editar
                                        </Button> -->
                                        <!-- <Button type="button" variant="destructive" size="sm" :disabled="form.processing ||
                                            deleteForm.processing
                                            " @click="remove(prov)">
                                            Eliminar
                                        </Button> -->
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- <InputError :message="deleteError" class="mt-3" /> -->
            </section>
        </div>
    </AppLayout>
</template>

<style></style>

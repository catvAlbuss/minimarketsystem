<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import { computed } from 'vue';
// import ProviderController from '@/actions/App/Http/Controllers/ProviderController';
import InputError from '@/components/InputError.vue';
// import promotions from '@/routes/promotions';

type Promotion = {
    id: number;
    id_products: number;
    name_promotion: string;
    price: number;
    state: string;
};

type AddProducts = {
    id: number;
    name: string;
    unit_price: number;
    promotion_discount: number;
    price_discount: number;
};

type Products = {
    id: number;
    name: string;
    unit_price: number;
    promotion_discount: number;
};

type Props = {
    promotions: Promotion[];
    products: Products[];
};

const AddProduct = ref<AddProducts[]>([]);
// const promotionName = ref('');
// const state = ref('');

const form = useForm({
    name_promotion: '',
    state: ''
});

const props = defineProps<Props>();
const promotion = computed(() => props.promotions);
const product = computed(() => props.products);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Promociones',
        href: '/',
    },
];

const editingId = ref<number | null>(null);
const isEditing = computed(() => editingId.value !== null);

const deleteForm = useForm({});
const deleteError = computed(
    () => (deleteForm.errors as Record<string, string | undefined>).delete,
);

const total = computed(() => {
    return AddProduct.value.reduce(
        (suma, item) => (suma = suma + (item.unit_price - item.price_discount)),
        0,
    );
});

const addProduct = (product: Products) => {
    const calculateDiscount =
        product.unit_price * (product.promotion_discount / 100);

    const newItem: AddProducts = {
        id: product.id,
        name: product.name,
        unit_price: product.unit_price,
        promotion_discount: product.promotion_discount,
        price_discount: Number(calculateDiscount.toFixed(2)),
    };
    AddProduct.value.push(newItem);
};

//removemos un producto de la const AddProduct
const removeProduct = (index: number) => {
    AddProduct.value.splice(index, 1);
};

//Crea un conjunto con todos los IDs que ya están en AddProduct
const selectedIds = computed(() => {
    return new Set(AddProduct.value.map((item) => item.id));
});

const resetData = () => {
    editingId.value = null;
    form.reset();
    AddProduct.value = [];
};

const crearPromotion = async () => {
    // const option = {
    //     preserveScroll: true,
    //     onSuccess: () => resetData(),
    // };

    if (AddProduct.value.length === 0) {
        alert('Selecciona un producto');
        return;
    }
    if (!form.name_promotion || !form.state) {
        alert('Por favor complete el nombre y el estado');
        return;
    }

    const data = {
        item: AddProduct.value.map((item) => ({
            id: item.id,
        })),
        name_promotion: form.name_promotion,
        price: total.value,
        state: form.state,
    };

    if (isEditing.value && editingId.value !== null) {
        // pasa los datos al controllador en el metodo update
        router.put(`/promotions/${editingId.value}`, data, {
            onSuccess: () => {
                resetData();
                alert('Promocion Actualizada');
            },onError: (errors) => form.setError(errors),
        });
    } else {
        const box = JSON.stringify(data);
        console.log(box);

        router.post('/promotions', data, {
            preserveScroll: true,
            onSuccess: ()=>{
                resetData();
                alert('Promocion creada.')
            },
            onError: (errors) => form.setError(errors),
        });
    }

};

const startEdit = (prom: Promotion): void => {
    editingId.value = prom.id;
    form.name_promotion = prom.name_promotion;
    form.state = prom.state;

    AddProduct.value = []; //limpia el arreglo de AddProduct

    const productInCombo = props.promotions.filter(
        (p) => p.name_promotion === prom.name_promotion,
    );

    productInCombo.forEach((item) => {
        const prodData = props.products.find((p) => p.id === item.id_products);
        if (prodData) {
            addProduct(prodData);
        }
    });
};

const remove = (prom: Promotion): void =>{
    if(!confirm(`Eliminar usuario "${prom.name_promotion}"?`)){
        return;
    }

    router.delete(`/promotions/${prom.id}`,{
        preserveScroll: true,
        onSuccess: ()=>{
            if (editingId.value === prom.id) {
                resetData();
            }
        }
    });
}
</script>

<template>
    <Head title="Promociones" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <section
                class="rounded-xl border border-sidebar-border/70 bg-background p-4"
            >
                <h1 class="text-xl font-semibold">
                    {{ isEditing ? 'Editar Promocion' : 'Nuevo Promocion' }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Gestiona todas las promociones de esta vista.
                </p>
                <table class="w-full min-w-[720px] text-sm">
                    <thead class="border-b text-left">
                        <tr>
                            <th class="px-2 py-2">Id Producto</th>
                            <th class="px-2 py-2">Nombre de Promocion</th>
                            <th class="px-2 py-2">Precio</th>
                            <th class="px-2 py-2">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="product.length === 0">
                            <td
                                colspan="6"
                                class="px-2 py-4 text-center text-muted-foreground"
                            >
                                No hay promociones registrados.
                            </td>
                        </tr>
                        <tr
                            v-for="prom in promotion"
                            :key="prom.id"
                            class="border-b"
                        >
                            <td class="px-2 py-2">{{ prom.id_products }}</td>
                            <td class="px-2 py-2">
                                {{ prom.name_promotion }}
                            </td>
                            <td class="px-2 py-2">{{ prom.price }}</td>
                            <td class="px-2 py-2">{{ prom.state }}</td>
                            <td class="px-2 py-2">
                                <div class="flex gap-2">
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        size="sm"
                                        @click="startEdit(prom)"
                                    >
                                        Editar
                                    </Button>
                                    <!-- :disabled="form.processing ||
                                            deleteForm.processing
                                            " -->
                                    <Button
                                        type="button"
                                        variant="destructive"
                                        size="sm"
                                        @click="remove(prom)"
                                    >
                                        Eliminar
                                    </Button>
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
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                <section
                    class="rounded-xl border border-sidebar-border/70 bg-background p-4"
                >
                    <h2 class="text-lg font-semibold">Listado de Productos</h2>

                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-auto text-sm">
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
                                    <td
                                        colspan="6"
                                        class="px-2 py-4 text-center text-muted-foreground"
                                    >
                                        No hay productos registrados.
                                    </td>
                                </tr>
                                <tr
                                    v-for="prod in product"
                                    :key="prod.id"
                                    class="border-b"
                                >
                                    <td class="px-2 py-2">{{ prod.id }}</td>
                                    <td class="px-2 py-2">
                                        {{ prod.name }}
                                    </td>
                                    <td class="px-2 py-2">
                                        {{ prod.unit_price }}
                                    </td>
                                    <td class="px-2 py-2">
                                        {{ prod.promotion_discount }}
                                    </td>
                                    <td class="px-2 py-2">
                                        <div class="flex gap-2">
                                            <Button
                                                type="button"
                                                variant="secondary"
                                                @click.stop="addProduct(prod)"
                                                :disabled="
                                                    selectedIds.has(prod.id)
                                                "
                                            >
                                                Agregar
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <InputError :message="deleteError" class="mt-3" />
                </section>

                <!-- Apartado de creacion de Combos -->
                <section
                    class="rounded-xl border border-sidebar-border/70 bg-background p-4"
                >
                    <h2 class="text-lg font-semibold">Crea tus combos</h2>

                    <div class="mb-4 flex-1 overflow-y-auto">
                        <table class="w-full min-w-auto text-sm">
                            <thead class="border-b text-left">
                                <tr>
                                    <th class="px-2 py-2">ID</th>
                                    <th class="px-2 py-2">Nombre Producto</th>
                                    <th class="px-2 py-2">P/U</th>
                                    <th class="px-2 py-2">Descuento</th>
                                    <th class="px-2 py-2">Desc/Precio</th>
                                    <th class="px-2 py-2">Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="product.length === 0">
                                    <td
                                        colspan="6"
                                        class="px-2 py-4 text-center text-muted-foreground"
                                    >
                                        No hay productos registrados.
                                    </td>
                                </tr>
                                <tr
                                    v-for="(item, index) in AddProduct"
                                    :key="index"
                                    class="border-b"
                                >
                                    <td class="px-2 py-2">{{ item.id }}</td>
                                    <td class="px-2 py-2">
                                        {{ item.name }}
                                    </td>
                                    <td class="px-2 py-2">
                                        {{ item.unit_price }}
                                    </td>
                                    <td class="px-2 py-2">
                                        {{ item.promotion_discount }}%
                                    </td>
                                    <td class="px-2 py-2">
                                        {{ item.price_discount }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            @click.stop="removeProduct(index)"
                                        >
                                            Eliminar
                                        </Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mb-2 flex-1 overflow-y-auto">
                        <div class="mb-2 flex flex-col gap-2">
                            <label class="text-sm font-bold text-white"
                                >Nombre de la Promoción:</label
                            >
                            <Input
                                v-model="form.name_promotion"
                                type="text"
                                placeholder="Ej: Promo Verano, 2x1..."
                                class="rounded-lg border border-gray-300 p-2 outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            />
                            <InputError :message="form.errors.name_promotion" />
                        </div>
                    </div>
                    <div class="mb-4 flex gap-4 overflow-y-auto">
                        <div class="mb-4 flex flex-col gap-2">
                            <label class="text-md font-bold text-white"
                                >TOTAL</label
                            >
                            <input
                                v-model="total"
                                disabled
                                type="text"
                                placeholder="Ej: Promo Verano, 2x1..."
                                class="rounded-lg border border-gray-300 p-2 outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div class="grid gap-2">
                            <Label for="id_products">Estado</Label>
                            <select
                                id="id_products"
                                v-model="form.state"
                                required
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                            >
                                <option value="" disabled selected>
                                    Tipo de producto
                                </option>
                                <option :value="'active'">Activo</option>
                                <option :value="'inactive'">Inactivo</option>
                            </select>
                            <InputError :message="form.errors.state" />
                        </div>
                        <div class="item-center">
                            <Button @click="crearPromotion" :disabled="form.processing" type="submit"
                                >{{ isEditing ? 'Actualizar' : 'Crear' }}
                                <i class="fas fa-plus"></i>
                            </Button>
                            <Button v-if="isEditing" type="button" variant="secondary"
                            @click="resetData">
                            Cancelar
                        </Button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

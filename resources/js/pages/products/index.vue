<script setup lang="ts">
 
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type BreadcrumbItem } from '@/types';
import { computed, ref } from 'vue';
import ProductsController from '@/actions/App/Http/Controllers/ProductsController';
import InputError from '@/components/InputError.vue';
import { index as productsIndex } from '@/routes/products';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Productos',
        href: productsIndex().url,
    },
];

type Category = {
    id: number;
    name: string;
}

type Products = {
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

type props = {
    products: Products[];
    categories: Category[];
};

const props = defineProps<props>();
const products = computed(() => props.products);
const categories = computed(() => props.categories)

const editingId = ref<number | null>(null);

const form = useForm({
    id_categories: props.categories?.[0]?.id ?? '',
    code: '',
    name: '',
    description: '',
    unit_price: 0,
    higher_price: 0,
    stock: 0,
    expiration_date: '',
    promotion_discount: 0,
    state: 'active',
});


const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete);

const isEditing = computed(() => editingId.value !== null);

const resetForm = (): void => {
    // isEditing.value = null;
    form.reset();
    // deleteForm.reset();
    form.clearErrors();
    form.id_categories = props.categories?.[0]?.id ?? '';
};

const startEdit = (products: Products): void => {
    editingId.value = products.id;
    form.clearErrors();
    form.id_categories = products.id_categories ?? props.categories?.[0]?.id ?? '';
    form.code = products.code;
    form.name = products.name;
    form.description = products.description;
    form.unit_price = products.unit_price;
    form.higher_price = products.higher_price ?? 0;
    form.stock = products.stock;
    form.expiration_date = products.expiration_date;
    form.promotion_discount = products.promotion_discount;
    form.state = products.state;
};

const submit = (): void => {
    // const data = JSON.stringify(form);
    // console.log('Submitting form with data:', data);
    const options = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };
    if (isEditing.value && editingId.value !== null) {
        form.put(ProductsController.update.url(editingId.value), options);
        return;
    }
    form.post(ProductsController.store.url(), options);
};

const remove = (products: Products): void => {
    if (!confirm(`Eliminar producto "${products.name}"?`)) {
        return;
    }

    deleteForm.delete(ProductsController.destroy.url(products.id), {
        preserveScroll: true,
    });
};
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
                    <h1 class="text-2xl font-semibold text-gray-800">Gestion de Productos</h1>
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

                    <!-- CAMPO ID_CATEGORIA -->
                    <div class="grid gap-2">
                        <Label for="id_categories">Categoría</Label>
                        <select id="id_categories" v-model="form.id_categories" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" class="text-sm">Seleccione</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">
                                {{ c.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.id_categories" />
                    </div>
                    <!-- CAMPO CODIGO -->
                    <div class="grid gap-2">
                        <Label for="name">Código</Label>
                        <Input id="name" v-model="form.code" type="text" placeholder="Ej: ajd12312asd" required />
                        <InputError :message="form.errors.code" />
                    </div>
                    <!-- CAMPO NOMBRE -->
                    <div class="grid gap-2">
                        <Label for="name">Nombre</Label>
                        <Input id="name" v-model="form.name" type="text" placeholder="Ej: Yogurt" required />
                        <InputError :message="form.errors.name" />
                    </div>
                    <!-- CAMPO DESCRIPCIÓN -->
                    <div class="grid gap-2">
                        <Label for="description">Descripción</Label>
                        <Input id="description" v-model="form.description" type="text" placeholder="Ej: Descripción"
                            required />
                        <InputError :message="form.errors.description" />
                    </div>
                    <!-- CAMPO PRECIO UNITARIO -->
                    <div class="grid gap-2">
                        <Label for="unit_price">Precio unitario</Label>
                        <Input id="unit_price" v-model="form.unit_price" type="text" placeholder="Ej: 6.50"
                            required />
                        <InputError :message="form.errors.unit_price" />
                    </div>
                    <!-- CAMPO PRECIO AL MAYOR -->
                    <div class="grid gap-2">
                        <Label for="higher_price">Precio al mayor</Label>
                        <Input id="higher_price" v-model="form.higher_price" type="text" placeholder="Ej: 5.50"
                            required />
                        <InputError :message="form.errors.higher_price" />
                    </div>
                    <!-- CAMPO STOCK -->
                    <div class="grid gap-2">
                        <Label for="stock">Stock</Label>
                        <Input id="stock" v-model="form.stock" type="text" placeholder="Ej: Descripción"
                            required />
                        <InputError :message="form.errors.stock" />
                    </div>
                    <!-- CAMPO FECHA DE VENCIMIENTO -->
                    <div class="grid gap-2">
                        <Label for="expiration_date">Fecha de vencimiento</Label>
                        <Input class="[color-scheme:dark]" id="expiration_date" v-model="form.expiration_date" type="date"
                            placeholder="Ej: 1990-01-01" />
                        <InputError :message="form.errors.expiration_date" />
                    </div>
                    <!-- CAMPO DE DESCUENTO -->
                    <div class="grid gap-2">
                        <Label for="promotion_discount">Descuento %</Label>
                        <Input id="promotion_discount" v-model="form.promotion_discount" type="text" placeholder="Ej: Descripción"
                            required />
                        <InputError :message="form.errors.promotion_discount" />
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
                <h2 class="text-lg font-semibold">Listado de productos</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-2 py-2">ID</th>
                                <th class="px-2 py-2">ID_Categoría</th>
                                <th class="px-2 py-2">Código</th>
                                <th class="px-2 py-2">Nombre</th>
                                <th class="px-2 py-2">Precio unitario</th>
                                <th class="px-2 py-2">Precio al mayor</th>
                                <th class="px-2 py-2">Stock</th>
                                <th class="px-2 py-2">Fecha de vencimiento</th>
                                <th class="px-2 py-2">Descuento %</th>
                                <th class="px-2 py-2">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="products.length === 0">
                                <td colspan="6" class="px-2 py-4 text-center text-muted-foreground">
                                    No hay productos registrados.
                                </td>
                            </tr>
                            <tr v-for="p in products" :key="p.id" class="border-b">
                                <td class="px-2 py-2">{{ p.id }}</td>
                                <td class="px-2 py-2">{{ p.id_categories }}</td>
                                <td class="px-2 py-2">{{ p.code }}</td>
                                <td class="px-2 py-2">{{ p.name }}</td>
                                <td class="px-2 py-2">{{ p.unit_price }}</td>
                                <td class="px-2 py-2">{{ p.higher_price }}</td>
                                <td class="px-2 py-2">{{ p.stock }}</td>
                                <td class="px-2 py-2">{{ p.expiration_date }}</td>
                                <td class="px-2 py-2">{{ p.promotion_discount }}</td>
                                <td class="px-2 py-2">{{ p.state }}</td>
                                <td class="px-2 py-2">
                                    <div class="flex gap-2">
                                        <Button type="button" variant="secondary" size="sm"
                                            :disabled="form.processing || deleteForm.processing" @click="startEdit(p)">
                                            Editar
                                        </Button>
                                        <Button type="button" variant="destructive" size="sm"
                                            :disabled="form.processing || deleteForm.processing" @click="remove(p)">
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

    <!-- import { type BreadcrumbItem } from '@/types';

    const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'products',
        href: './products',
    },
];
</script>

<template>

    <Head title="products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
            </section>
                hola
            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
            </section>
        </div>
    </AppLayout>
</template>
>>>>>>> ca91998998c9ef5e8dea0e0e1d114c3b708910ba -->

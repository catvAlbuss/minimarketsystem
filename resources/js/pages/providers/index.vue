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
    id: number,
    code: string,
};

type Props = {
    providers: Providers[];
    products: Products[];
};

const props = defineProps<Props>();
const provider = computed(() => props.providers);
const product = computed(()=> props.products);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Proveedores',
        href: '/',
    },
];

const editingId = ref<number | null>(null);
const isEditing = computed(() => editingId.value !== null);

const form = useForm({
    id_products: 0,
    ruc: '',
    company_name: '',
    contact_person: '',
    phone: 0,
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

const startEdit = (provider: Providers): void => {
    editingId.value = provider.id;
    form.clearErrors();
    form.company_name = provider.company_name;
    form.contact_person = provider.contact_person;
    form.ruc = provider.ruc;
    form.phone = provider.phone;
    form.address = provider.address;
    form.email = provider.email;
    form.category = provider.category;
    form.id_products = provider.id_products;
    form.description_products = provider.description_products;
    form.status = provider.status;
};

const remove = (provider: Providers): void => {
    if (!confirm(`Eliminar usuario "${provider.company_name}"?`)) {
        return;
    }

    deleteForm.delete(ProviderController.destroy.url(provider.id), {
        preserveScroll: true,
    });
};

const autocompletDescription=()=> {
    const selectProd =  product.value.find(p =>p.id === form.id_products);
    if (selectProd) {
        form.description_products = selectProd.code;
    }else{
        form.description_products = '';
    }
};
</script>

<template>

    <Head title="Proveedores" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
                <h1 class="text-xl font-semibold">
                    {{ isEditing ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Gestiona a tus proveedores desde esta misma vista.
                </p>
                <form class="mt-4 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="company_name">Nombre de la compañia</Label>
                        <Input id="company_name" class="text-white" v-model="form.company_name" type="text" required
                            placeholder="Ej: Jorge" />
                        <InputError :message="form.errors.company_name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="ruc">RUC</Label>
                        <Input id="ruc" v-model="form.ruc" type="text" required placeholder="Ej: Almeida" />
                        <InputError :message="form.errors.ruc" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="contact_person">Contacto del Personal a Cargo</Label>
                        <Input id="contact_person" v-model="form.contact_person" type="text"
                            required />
                        <InputError :message="form.errors.contact_person" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="phone">Celular</Label>
                        <Input id="phone" v-model="form.phone" type="text" required />
                        <InputError :message="form.errors.phone" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="address">Direccion</Label>
                        <Input id="address" v-model="form.address" type="text" required
                            placeholder="Ej: Jr. 28 Julio" />
                        <InputError :message="form.errors.address" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="email">Correo</Label>
                        <Input :disabled="isEditing" id="email" v-model="form.email" type="email" required
                            placeholder="Ej: example@gmail.com" />
                        <InputError :message="form.errors.email" />
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

                    <!-- <div class="grid gap-2">
                        <Label for="entry_date">
                            Fecha Ingreso
                            <span v-if="isEditing" class="text-xs text-muted-foreground">(opcional en edicion)</span>
                        </Label>
                        <Input id="entry_date" v-model="form.entry_date" type="date" :required="!isEditing" />
                        <InputError :message="form.errors.password" />
                    </div> -->

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

                    <!-- <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="proveedorEstado" checked />
                        <label class="form-check-label" for="proveedorEstado">Proveedor Activo</label>
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
            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
                <h2 class="text-lg font-semibold">Listado de usuarios</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-2 py-2">ID</th>
                                <th class="px-2 py-2">Nombre de Compañia</th>
                                <th class="px-2 py-2">Correo</th>
                                <th class="px-2 py-2">Ruc</th>
                                <th class="px-2 py-2">Phone</th>
                                <th class="px-2 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="provider.length === 0">
                                <td colspan="6" class="px-2 py-4 text-center text-muted-foreground">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                            <tr v-for="prov in provider" :key="prov.id" class="border-b">
                                <td class="px-2 py-2">{{ prov.id }}</td>
                                <td class="px-2 py-2">
                                    {{ prov.company_name }}
                                </td>
                                <td class="px-2 py-2">{{ prov.email }}</td>
                                <td class="px-2 py-2">{{ prov.ruc }}</td>
                                <td class="px-2 py-2">{{ prov.phone }}</td>
                                <td class="px-2 py-2">
                                    <div class="flex gap-2">
                                        <Button type="button" variant="secondary" size="sm" :disabled="form.processing ||
                                            deleteForm.processing
                                            " @click="startEdit(prov)">
                                            Editar
                                        </Button>
                                        <Button type="button" variant="destructive" size="sm" :disabled="form.processing ||
                                            deleteForm.processing
                                            " @click="remove(prov)">
                                            Eliminar
                                        </Button>
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

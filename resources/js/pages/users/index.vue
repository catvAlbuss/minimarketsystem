<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';
import { computed } from 'vue';
import UserController from '@/actions/App/Http/Controllers/UserController';
import InputError from '@/components/InputError.vue';

type Users = {
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

type Props = {
    users: Users[];
};

const props = defineProps<Props>();
const users = computed(() => props.users);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Usuarios',
        href: '/'
    }
];

const editingId = ref<number | null>(null);
const isEditing = computed(() => editingId.value !== null);

const form = useForm({
    name: '',
    lastname: '',
    dni: 0,
    phone: 0,
    address: '',
    children: 0,
    affiliate: '',
    insured: '',
    work_modality: '',
    entry_date: '',
    retention: '',
    entry_to_payroll: '',
    role: '',
    state: '',
    email: '',
    password: '',
    password_confirmation: '',

});

const deleteForm = useForm({});
const deleteError = computed(() => (deleteForm.errors as Record<string, string | undefined>).delete)

const resetForm = (): void => {
    editingId.value = null;
    form.reset();
};

const submit = (): void => {
    const option = {
        preserveScroll: true,
        onSuccess: () => resetForm(),
    };

    if (isEditing.value && editingId.value !== null) {
        // console.log(form)
        const box = JSON.stringify(form);
        console.log('Desde la condicion: ',box);
        form.put(UserController.update.url(editingId.value), option);
        return;
    }

    form.post(UserController.store.url(), option);
};

const startEdit = (user: Users): void => {
    editingId.value = user.id;
    form.clearErrors();
    form.name = user.name;
    form.lastname = user.lastname;
    form.dni = user.dni;
    form.phone = user.phone;
    form.address = user.address;
    form.email = user.email;
    form.password = '';
    form.password_confirmation = '';
    form.children = user.children;
    form.affiliate = user.affiliate;
    form.insured = user.insured;
    form.work_modality = user.work_modality;
    form.entry_date = user.entry_date;
    form.retention = user.retention;
    form.entry_to_payroll = user.entry_to_payroll;
    form.role = user.role;
    form.state = user.state;
};

const remove = (user: Users): void => {
    if (!confirm(`Eliminar usuario "${user.name}"?`)) {
        return;
    }

    deleteForm.delete(UserController.destroy.url(user.id), {
        preserveScroll: true,
    });
};
</script>

<template>

    <Head title="Usuarios" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-4">
            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
                <h1 class="text-xl font-semibold">
                    {{ isEditing ? 'Editar usuario' : 'Nuevo usuario' }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Gestiona usuarios, rol y personal desde esta misma vista.
                </p>
                <form class="mt-4 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="name">Nombre</Label>
                        <Input id="name" class="text-white" v-model="form.name" type="text" required placeholder="Ej: Jorge"/>
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="lastname">Apellido</Label>
                        <Input id="lastname" v-model="form.lastname" type="text" required placeholder="Ej: Almeida" />
                        <InputError :message="form.errors.lastname" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="dni">DNI</Label>
                        <Input :disabled="isEditing" id="dni" v-model="form.dni" type="text" required/>
                        <InputError :message="form.errors.dni" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="phone">Celular</Label>
                        <Input id="phone" v-model="form.phone" type="text" required />
                        <InputError :message="form.errors.phone" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="address">Direccion</Label>
                        <Input id="address" v-model="form.address" type="text" required placeholder="Ej: Jr. 28 Julio"/>
                        <InputError :message="form.errors.address" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="email">Correo</Label>
                        <Input :disabled="isEditing" id="email" v-model="form.email" type="email" required placeholder="Ej: example@gmail.com"/>
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="children">Numero de hijos</Label>
                        <Input id="children" v-model="form.children" type="number" />
                        <InputError :message="form.errors.children" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="affiliate">Afiliado</Label>
                        <select id="affiliate" v-model="form.affiliate" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" disabled selected>Tipo de afiliado</option>
                            <option :value="'ONP'">ONP</option>
                            <option :value="'AFP'">AFP</option>
                        </select>
                        <InputError :message="form.errors.affiliate" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="insured">Asegurado</Label>
                        <select id="insured" v-model="form.insured" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" disabled selected>Tipo de seguro</option>
                            <option :value="'EsSalud'">EsSalud</option>
                            <option :value="'SIS'">SIS</option>
                        </select>
                        <InputError :message="form.errors.insured" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="work_modality">Modalidad de Trabajo</Label>
                        <select id="work_modality" v-model="form.work_modality" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" disabled selected>Tipo de modalidad</option>
                            <option :value="'fullTime'">Full Time</option>
                            <option :value="'partTime'">Part Time</option>
                        </select>
                        <InputError :message="form.errors.work_modality" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="entry_date">
                            Fecha Ingreso
                            <span v-if="isEditing" class="text-xs text-muted-foreground">(opcional en edicion)</span>
                        </Label>
                        <Input id="entry_date" v-model="form.entry_date" type="date" :required="!isEditing" />
                        <InputError :message="form.errors.password" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="retention">Retencion</Label>
                        <select id="retention" v-model="form.retention" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" disabled selected>Tipo de retencion</option>
                            <option :value="'yes'">Yes</option>
                            <option :value="'no'">No</option>
                        </select>
                        <InputError :message="form.errors.retention" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="entry_to_payroll">Ingreso a planilla</Label>
                        <select id="entry_to_payroll" v-model="form.entry_to_payroll" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" disabled selected>Estas en planilla</option>
                            <option :value="'yes'">Yes</option>
                            <option :value="'no'">No</option>
                        </select>
                        <InputError :message="form.errors.entry_to_payroll" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="role">Role</Label>
                        <select id="role" v-model="form.role" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" disabled selected>Tipo de rol</option>
                            <option :value="'root'">root</option>
                            <option :value="'managment'">managment</option>
                            <option :value="'administrator_general'">administrator_general</option>
                            <option :value="'logistic_general'">logistic_general</option>
                            <option :value="'administrator'">administrator</option>
                            <option :value="'logistic'">logistic</option>
                            <option :value="'cashier'">cashier</option>
                            <option :value="'asistente'">asistente</option>
                        </select>
                        <InputError :message="form.errors.role" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="state">Estado</Label>
                        <select id="state" v-model="form.state" required
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                            <option value="" disabled selected>tipo de estado</option>
                            <option :value="'active'">Activo</option>
                            <option :value="'inactive'">Inactivo</option>
                        </select>
                        <InputError :message="form.errors.entry_to_payroll" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="password">
                            Password
                            <span v-if="isEditing" class="text-xs text-muted-foreground">(opcional en edicion)</span>
                        </Label>
                        <Input id="password" v-model="form.password" type="password" :required="!isEditing" />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password_confirmation">Confirmar password</Label>
                        <Input id="password_confirmation" v-model="form.password_confirmation" type="password"
                            :required="!isEditing" />
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
            <section class="rounded-xl border border-sidebar-border/70 bg-background p-4">
                <h2 class="text-lg font-semibold">Listado de usuarios</h2>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="border-b text-left">
                            <tr>
                                <th class="px-2 py-2">ID</th>
                                <th class="px-2 py-2">Nombre</th>
                                <th class="px-2 py-2">Correo</th>
                                <th class="px-2 py-2">Dni</th>
                                <th class="px-2 py-2">Phone</th>
                                <th class="px-2 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="users.length === 0">
                                <td colspan="6" class="px-2 py-4 text-center text-muted-foreground">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                            <tr v-for="user in users" :key="user.id" class="border-b">
                                <td class="px-2 py-2">{{ user.id }}</td>
                                <td class="px-2 py-2">{{ user.name }}</td>
                                <td class="px-2 py-2">{{ user.email }}</td>
                                <td class="px-2 py-2">{{ user.dni }}</td>
                                <td class="px-2 py-2">{{ user.phone }}</td>
                                <td class="px-2 py-2">
                                    <div class="flex gap-2">
                                        <Button type="button" variant="secondary" size="sm"
                                            :disabled="form.processing || deleteForm.processing"
                                            @click="startEdit(user)">
                                            Editar
                                        </Button>
                                        <Button type="button" variant="destructive" size="sm"
                                            :disabled="form.processing || deleteForm.processing" @click="remove(user)">
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
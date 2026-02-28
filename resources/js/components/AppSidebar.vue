<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import { BookOpen, Folder, LayoutGrid, User } from 'lucide-vue-next';

import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';
import { dashboard } from '@/routes';

// IMPORTAR RUTAS
import products from '@/routes/products';
import users from '@/routes/users';
import customers from '@/routes/customers';
import categories from '@/routes/categories';
import sales from '@/routes/sales';
import sale_details from '@/routes/sale_details';
import providers from '@/routes/providers';
import promotions from '@/routes/promotions';
import buys from '@/routes/buys';



const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Users',
        href: users.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Productos',
        href: products.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Ventas',
        href: sales.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Detalle de Ventas',
        href: sale_details.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Compras',
        href: buys.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Categorías',
        href: categories.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Clientes',
        href: customers.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Proveedores',
        href: providers.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Promociones',
        href: promotions.index.url(),
        icon: LayoutGrid,

    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

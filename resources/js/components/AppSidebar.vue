<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
<<<<<<< HEAD
import { BookOpen, Folder, LayoutGrid } from 'lucide-vue-next';
=======
import { BookOpen, Folder, LayoutGrid, User } from 'lucide-vue-next';
>>>>>>> ca91998998c9ef5e8dea0e0e1d114c3b708910ba
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
<<<<<<< HEAD
// IMPORTAR RUTAS
import products from '@/routes/products';
import users from '@/routes/users';
import customers from '@/routes/customers';
import categories from '@/routes/categories';
import sales from '@/routes/sales';
=======
import products from '@/routes/products';
import users from '@/routes/users';
import customers from '@/routes/customers';
import providers from '@/routes/providers';
import promotions from '@/routes/promotions';
>>>>>>> ca91998998c9ef5e8dea0e0e1d114c3b708910ba

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
<<<<<<< HEAD
        title: 'Productos',
=======
        title: 'Products',
>>>>>>> ca91998998c9ef5e8dea0e0e1d114c3b708910ba
        href: products.index.url(),
        icon: LayoutGrid,
    },
    {
<<<<<<< HEAD
        title: 'Ventas',
        href: sales.index.url(),
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
=======
        title: 'Clientes',
        href: customers.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Proveedores',
        href: providers.index.url(),
        icon: User,
    },
    {
        title: 'Promociones',
        href: promotions.index.url(),
        icon: LayoutGrid,
>>>>>>> ca91998998c9ef5e8dea0e0e1d114c3b708910ba
    }
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

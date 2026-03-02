<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';

import {
    Store,
    TrendingUp,
    Package,
    Users,
    UserCog,
    Truck,
    Tag,
    ShoppingCart,
    Receipt,
    ListOrdered,
    FileText,
    LogOut,
    Home,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { computed } from 'vue';

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarGroupContent,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import branches from '@/routes/branches';

import buy_details from '@/routes/buy_details';
import buys from '@/routes/buys';
import categories from '@/routes/categories';
import customers from '@/routes/customers';
import products from '@/routes/products';
import promotions from '@/routes/promotions';
import providers from '@/routes/providers';
import sale_details from '@/routes/sale_details';
import sales from '@/routes/sales';
import users from '@/routes/users';
import { type NavItem } from '@/types';
import NavUser from './NavUser.vue';

const page = usePage();
const currentPath = computed(() => page.url);

const isActive = (href: string | { url: string } | (() => string)) => {
    let hrefStr: string;
    if (typeof href === 'function') {
        hrefStr = href();
    } else if (typeof href === 'string') {
        hrefStr = href;
    } else {
        hrefStr = href.url;
    }
    return currentPath.value === hrefStr || currentPath.value.startsWith(hrefStr);
};

const logout = async () => {
    const result = await Swal.fire({
        title: '¿Cerrar sesión?',
        text: 'Tu sesión actual se cerrará en este dispositivo.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
    });

    if (result.isConfirmed) {
        router.post('/logout');
    }
};

const inventoryGroup: NavItem[] = [
    { title: 'Categorías', href: categories.index.url(), icon: Tag },
    { title: 'Productos', href: products.index.url(), icon: Package },
    { title: 'Promociones', href: promotions.index.url(), icon: FileText },
];

const salesGroup: NavItem[] = [
    { title: 'Ventas', href: sales.index.url(), icon: TrendingUp },
    { title: 'Detalle de ventas', href: sale_details.index.url(), icon: Receipt },
];

const purchasesGroup: NavItem[] = [
    { title: 'Compras', href: buys.index.url(), icon: ShoppingCart },
    { title: 'Detalle de compras', href: buy_details.index.url(), icon: ListOrdered },
];

const peopleGroup: NavItem[] = [
    { title: 'Clientes', href: customers.index.url(), icon: Users },
    { title: 'Proveedores', href: providers.index.url(), icon: Truck },
    { title: 'Personal', href: users.index.url(), icon: UserCog },
];

const mainNavItems: NavItem[] = [
    { title: 'Inicio', href: dashboard(), icon: Home },
    { title: 'Sucursales', href: branches.index.url(), icon: Store },
];

const navGroups = [
    { label: 'Principal', items: mainNavItems },
    { label: 'Inventario', items: inventoryGroup },
    { label: 'Ventas', items: salesGroup },
    { label: 'Compras', items: purchasesGroup },
    { label: 'Personas', items: peopleGroup },
];
</script>

<template>
    <Sidebar collapsible="offcanvas" variant="inset"
        class="border-r border-blue-100/80 bg-white text-slate-900 transition-colors duration-300 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
        <SidebarHeader class="border-b border-blue-100/80 bg-white dark:border-slate-800 dark:bg-slate-950">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child
                        class="rounded-xl py-4 hover:bg-sky-50 dark:hover:bg-sky-950/40">
                        <Link :href="dashboard()" class="flex items-center gap-3 px-2">
                            <div
                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-700 to-sky-400 shadow-lg shadow-sky-200/80 dark:shadow-sky-900/40">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="2.2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                                </svg>
                            </div>

                            <div class="flex flex-col leading-tight">
                                <span class="text-base font-extrabold tracking-tight text-blue-700 dark:text-sky-300">
                                    NetMarket
                                </span>
                                <span
                                    class="text-[10px] font-semibold uppercase tracking-widest text-sky-500 dark:text-sky-500">
                                    Panel de control
                                </span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="sidebar-scroll bg-white dark:bg-slate-950">
            <SidebarGroup v-for="group in navGroups" :key="group.label">
                <SidebarGroupLabel
                    class="px-4 pb-1 pt-4 text-[10px] font-bold uppercase tracking-widest text-sky-600 dark:text-sky-500">
                    {{ group.label }}
                </SidebarGroupLabel>

                <SidebarGroupContent>
                    <nav class="space-y-0.5 px-2 pb-1">
                        <Link v-for="item in group.items" :key="item.title" :href="item.href"
                            class="group flex items-center gap-3 rounded-lg border-l-[3px] px-3 py-2.5 text-sm font-medium transition-all duration-200"
                            :class="isActive(item.href)
                                ? 'border-blue-600 bg-blue-50 text-blue-700 dark:border-sky-400 dark:bg-slate-900 dark:text-sky-300'
                                : 'border-transparent text-slate-600 hover:bg-sky-50 hover:text-blue-700 dark:text-slate-400 dark:hover:bg-slate-900 dark:hover:text-sky-300'
                                ">
                            <component :is="item.icon"
                                class="h-[18px] w-[18px] flex-shrink-0 transition-colors duration-200" :class="isActive(item.href)
                                    ? 'text-blue-600 dark:text-sky-400'
                                    : 'text-slate-400 group-hover:text-blue-600 dark:text-slate-500 dark:group-hover:text-sky-400'
                                    " />

                            <span class="truncate">{{ item.title }}</span>

                            <span v-if="isActive(item.href)"
                                class="ml-auto h-1.5 w-1.5 flex-shrink-0 rounded-full bg-blue-600 dark:bg-sky-400" />
                        </Link>
                    </nav>
                </SidebarGroupContent>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter class="border-t border-blue-100/80 bg-white p-2 dark:border-slate-800 dark:bg-slate-950">
            <SidebarMenu>
                <SidebarMenuItem>
                    <NavUser />
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>
    </Sidebar>
</template>

<style scoped>
.sidebar-scroll {
    scrollbar-width: thin;
    scrollbar-color: #38bdf8 transparent;
}

.sidebar-scroll::-webkit-scrollbar {
    width: 8px;
}

.sidebar-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #2563eb 0%, #38bdf8 100%);
    border-radius: 9999px;
    border: 2px solid transparent;
    background-clip: padding-box;
}

.dark .sidebar-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #38bdf8 0%, #0ea5e9 100%);
    background-clip: padding-box;
}
</style>

<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import { 
    LayoutGrid, 
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

import branches from '@/routes/branches';
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
import { type NavItem } from '@/types';
import { dashboard } from '@/routes';

import products from '@/routes/products';
import users from '@/routes/users';
import customers from '@/routes/customers';
import categories from '@/routes/categories';
import sales from '@/routes/sales';
import sale_details from '@/routes/sale_details';
import providers from '@/routes/providers';
import promotions from '@/routes/promotions';
import buys from '@/routes/buys';
import buy_details from '@/routes/buy_details';

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

const logout = () => {
    if (confirm('¿Estás seguro que deseas cerrar sesión?')) {
        router.post('/logout');
    }
};

const inventoryGroup: NavItem[] = [
    { title: 'Productos', href: products.index.url(), icon: Package },
    { title: 'Categorías', href: categories.index.url(), icon: Tag },
    { title: 'Promociones', href: promotions.index.url(), icon: FileText },
];

const salesGroup: NavItem[] = [
    { title: 'Ventas', href: sales.index.url(), icon: TrendingUp },
    { title: 'Detalle de Ventas', href: sale_details.index.url(), icon: Receipt },
];

const purchasesGroup: NavItem[] = [
    { title: 'Compras', href: buys.index.url(), icon: ShoppingCart },
    { title: 'Detalle de Compras', href: buy_details.index.url(), icon: ListOrdered },
];

const peopleGroup: NavItem[] = [
    { title: 'Clientes', href: customers.index.url(), icon: Users },
    { title: 'Proveedores', href: providers.index.url(), icon: Truck },
    { title: 'Personales', href: users.index.url(), icon: UserCog },
];

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: Home },
    { title: 'Sucursales', href: branches.index.url(), icon: Store },
];
</script>

<template>
    <Sidebar collapsible="offcanvas" variant="inset" class="bg-white border-r border-gray-200">
        
        <!-- Header con Logo -->
        <SidebarHeader class="border-b border-gray-200 bg-white">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child class="hover:bg-gray-50 py-4">
                        <Link :href="dashboard()" class="flex items-center gap-3 px-2">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                </svg>
                            </div>
                            <span class="text-base font-bold text-blue-600 truncate">NetMarket</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="bg-white">
            
            <!-- Sección: Principal -->
            <SidebarGroup>
                <SidebarGroupLabel class="text-xs font-semibold text-blue-600 uppercase tracking-wider px-3 py-2">
                    Principal
                </SidebarGroupLabel>
                <SidebarGroupContent>
                    <nav class="space-y-0.5 px-2 py-2">
                        <Link 
                            v-for="item in mainNavItems" 
                            :key="item.title"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200"
                            :class="isActive(item.href) 
                                ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' 
                                : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 border-l-4 border-transparent'"
                        >
                            <component 
                                :is="item.icon" 
                                class="h-5 w-5 flex-shrink-0" 
                                :class="isActive(item.href) ? 'text-blue-600' : 'text-gray-500'" 
                            />
                            <span class="truncate">{{ item.title }}</span>
                        </Link>
                    </nav>
                </SidebarGroupContent>
            </SidebarGroup>

            <!-- Sección: Inventario -->
            <SidebarGroup>
                <SidebarGroupLabel class="text-xs font-semibold text-blue-600 uppercase tracking-wider px-3 py-2">
                    Inventario
                </SidebarGroupLabel>
                <SidebarGroupContent>
                    <nav class="space-y-0.5 px-2 py-2">
                        <Link 
                            v-for="item in inventoryGroup" 
                            :key="item.title"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200"
                            :class="isActive(item.href) 
                                ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' 
                                : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 border-l-4 border-transparent'"
                        >
                            <component 
                                :is="item.icon" 
                                class="h-5 w-5 flex-shrink-0" 
                                :class="isActive(item.href) ? 'text-blue-600' : 'text-gray-500'" 
                            />
                            <span class="truncate">{{ item.title }}</span>
                        </Link>
                    </nav>
                </SidebarGroupContent>
            </SidebarGroup>

            <!-- Sección: Ventas -->
            <SidebarGroup>
                <SidebarGroupLabel class="text-xs font-semibold text-blue-600 uppercase tracking-wider px-3 py-2">
                    Ventas
                </SidebarGroupLabel>
                <SidebarGroupContent>
                    <nav class="space-y-0.5 px-2 py-2">
                        <Link 
                            v-for="item in salesGroup" 
                            :key="item.title"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200"
                            :class="isActive(item.href) 
                                ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' 
                                : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 border-l-4 border-transparent'"
                        >
                            <component 
                                :is="item.icon" 
                                class="h-5 w-5 flex-shrink-0" 
                                :class="isActive(item.href) ? 'text-blue-600' : 'text-gray-500'" 
                            />
                            <span class="truncate">{{ item.title }}</span>
                        </Link>
                    </nav>
                </SidebarGroupContent>
            </SidebarGroup>

            <!-- Sección: Compras -->
            <SidebarGroup>
                <SidebarGroupLabel class="text-xs font-semibold text-blue-600 uppercase tracking-wider px-3 py-2">
                    Compras
                </SidebarGroupLabel>
                <SidebarGroupContent>
                    <nav class="space-y-0.5 px-2 py-2">
                        <Link 
                            v-for="item in purchasesGroup" 
                            :key="item.title"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200"
                            :class="isActive(item.href) 
                                ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' 
                                : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 border-l-4 border-transparent'"
                        >
                            <component 
                                :is="item.icon" 
                                class="h-5 w-5 flex-shrink-0" 
                                :class="isActive(item.href) ? 'text-blue-600' : 'text-gray-500'" 
                            />
                            <span class="truncate">{{ item.title }}</span>
                        </Link>
                    </nav>
                </SidebarGroupContent>
            </SidebarGroup>

            <!-- Sección: Personas -->
            <SidebarGroup>
                <SidebarGroupLabel class="text-xs font-semibold text-blue-600 uppercase tracking-wider px-3 py-2">
                    Personas
                </SidebarGroupLabel>
                <SidebarGroupContent>
                    <nav class="space-y-0.5 px-2 py-2">
                        <Link 
                            v-for="item in peopleGroup" 
                            :key="item.title"
                            :href="item.href"
                            class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200"
                            :class="isActive(item.href) 
                                ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' 
                                : 'text-gray-600 hover:bg-blue-50 hover:text-blue-600 border-l-4 border-transparent'"
                        >
                            <component 
                                :is="item.icon" 
                                class="h-5 w-5 flex-shrink-0" 
                                :class="isActive(item.href) ? 'text-blue-600' : 'text-gray-500'" 
                            />
                            <span class="truncate">{{ item.title }}</span>
                        </Link>
                    </nav>
                </SidebarGroupContent>
            </SidebarGroup>

        </SidebarContent>

        <!-- Footer con Logout -->
        <SidebarFooter class="border-t border-gray-200 bg-blue">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton 
                        @click="logout" 
                        class=" flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 flex-shrink-0 w-full hover:bg-red-100 hover:text-red-600 text-blue-600 transition-all duration-200 cursor-pointer"
                        flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 flex-shrink-0
                        
                        as="button"
                    >   
                        <LogOut class="h-5 w-5 flex-shrink-0" />
                        <span class="truncate">Cerrar Sesión</span>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>
    </Sidebar>
</template>


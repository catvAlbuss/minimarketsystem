<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppSidebar from '@/components/AppSidebar.vue';
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItemType[];
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name ?? 'Administrador');
</script>

<template>
    <SidebarProvider>
        <div class="flex min-h-screen w-full bg-gray-50">
            <!-- Sidebar -->
            <AppSidebar />
            
            <!-- Main Content Area -->
            <div class="flex flex-1 flex-col">
                
                <!-- Header  -->
                <header class="sticky top-0 z-30 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-4.5"> 
                    
                    <!-- Botón de menú hamburguesa (para móvil) -->
                    <div class="flex items-center gap-3">
                        <SidebarTrigger class="md:hidden">
                            <button class="flex h-10 w-10 items-center justify-center rounded-lg hover:bg-gray-100 transition-colors md:hidden">
                                <svg class="h-6 w-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                            </button>
                        </SidebarTrigger>

                        <!-- Breadcrumbs -->
                        <Breadcrumb>
                            <BreadcrumbList>
                                <template v-for="(item, index) in breadcrumbs" :key="index">
                                    <BreadcrumbItem>
                                        <template v-if="index === breadcrumbs.length - 1">
                                            <BreadcrumbPage class="text-gray-900 font-medium">
                                                {{ item.title }}
                                            </BreadcrumbPage>
                                        </template>
                                        <template v-else>
                                            <BreadcrumbLink as-child>
                                                <Link :href="item.href" class="text-gray-600 hover:text-blue-900 transition-colors">
                                                    {{ item.title }}
                                                </Link>
                                            </BreadcrumbLink>
                                        </template>
                                    </BreadcrumbItem>
                                    <BreadcrumbSeparator v-if="index < breadcrumbs.length - 1" class="text-gray-400" />
                                </template>
                            </BreadcrumbList>
                        </Breadcrumb>
                    </div>
                    
                    <!-- User Info -->
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-sm font-medium text-blue-900">{{ userName }}</span>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 p-4 sm:p-6">
                    <slot />
                </main>
                
            </div>
        </div>
    </SidebarProvider>
</template>


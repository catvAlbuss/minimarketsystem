<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

import AppSidebar from '@/components/AppSidebar.vue';
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbPage, BreadcrumbSeparator } from '@/components/ui/breadcrumb';
import { SidebarProvider, SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem as BreadcrumbItemType } from '@/types';

type Props = { breadcrumbs?: BreadcrumbItemType[] };
const props = withDefaults(defineProps<Props>(), { breadcrumbs: () => [] });

const page = usePage();
const userName = computed(() => page.props.auth?.user?.name ?? 'Administrador');

// ── Dark mode ────────────────────────────────────────────────────────────
const isDark = ref(false);

const applyTheme = (dark: boolean) => {
    document.documentElement.classList.toggle('dark', dark);
    localStorage.setItem('theme', dark ? 'dark' : 'light');
};

const toggleDark = () => {
    isDark.value = !isDark.value;
    applyTheme(isDark.value);
};

onMounted(() => {
    const saved = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    isDark.value = saved ? saved === 'dark' : prefersDark;
    applyTheme(isDark.value);
});
</script>

<template>
    <SidebarProvider>
        <div class="flex min-h-screen w-full bg-slate-50 dark:bg-slate-950 transition-colors duration-200">
            <!-- Sidebar -->
            <AppSidebar />

            <!-- Main Content Area -->
            <div class="flex flex-1 flex-col">

                <!-- Header -->
                <header
                    class="sticky top-0 z-30 flex items-center justify-between border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95 px-6 py-3 transition-colors duration-200">

                    <!-- Left: hamburger + breadcrumbs -->
                    <div class="flex items-center gap-3">
                        <SidebarTrigger class="md:hidden">
                            <button
                                class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 md:hidden">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </SidebarTrigger>

                        <Breadcrumb>
                            <BreadcrumbList>
                                <template v-for="(item, index) in breadcrumbs" :key="index">
                                    <BreadcrumbItem>
                                        <template v-if="index === breadcrumbs.length - 1">
                                            <BreadcrumbPage class="font-medium text-slate-900 dark:text-slate-100">
                                                {{ item.title }}
                                            </BreadcrumbPage>
                                        </template>
                                        <template v-else>
                                            <BreadcrumbLink as-child>
                                                <Link :href="item.href"
                                                    class="text-slate-500 transition hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400">
                                                    {{ item.title }}
                                                </Link>
                                            </BreadcrumbLink>
                                        </template>
                                    </BreadcrumbItem>
                                    <BreadcrumbSeparator v-if="index < breadcrumbs.length - 1"
                                        class="text-slate-300 dark:text-slate-600" />
                                </template>
                            </BreadcrumbList>
                        </Breadcrumb>
                    </div>

                    <!-- Right: dark toggle + user -->
                    <div class="flex items-center gap-2">
                        <!-- Dark / Light toggle -->
                        <button type="button"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                            :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'" @click="toggleDark">
                            <!-- Sun icon (shown in dark mode → switch to light) -->
                            <svg v-if="isDark" class="h-4 w-4 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 2a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1Zm0 15a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm8-4a1 1 0 1 1 0 2h-1a1 1 0 1 1 0-2h1ZM5 13a1 1 0 1 1 0 2H4a1 1 0 1 1 0-2h1Zm14.07-7.07a1 1 0 0 1 0 1.41l-.71.71a1 1 0 0 1-1.41-1.41l.71-.71a1 1 0 0 1 1.41 0ZM6.64 17.36a1 1 0 0 1 0 1.41l-.71.71a1 1 0 0 1-1.41-1.41l.71-.71a1 1 0 0 1 1.41 0ZM21 12a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM12 20a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0v-1a1 1 0 0 1 1-1Zm-8-8a1 1 0 1 1 0 2H3a1 1 0 1 1 0-2h1Zm14.78 6.36a1 1 0 0 1-1.41 1.41l-.71-.71a1 1 0 0 1 1.41-1.41l.71.71ZM7.05 6.64a1 1 0 0 1-1.41 1.41l-.71-.71a1 1 0 0 1 1.41-1.41l.71.71Z" />
                            </svg>
                            <!-- Moon icon (shown in light mode → switch to dark) -->
                            <svg v-else class="h-4 w-4 text-slate-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
                            </svg>
                        </button>

                        <!-- User chip -->
                        <div
                            class="hidden sm:flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-4 py-1.5 dark:border-blue-900 dark:bg-blue-950">
                            <svg class="h-4 w-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="text-sm font-medium text-blue-900 dark:text-blue-200">{{ userName }}</span>
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

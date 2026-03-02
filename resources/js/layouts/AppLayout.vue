<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { useDark } from '@vueuse/core';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

// Esto activa la clase .dark en el <html> de forma global
useDark(); 
</script>

<template>
    <div class="min-h-screen bg-background text-foreground transition-colors duration-300">
        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="flex-1 bg-background">
                <slot />
            </div>
        </AppLayout>
    </div>
</template>

<style scoped>
/* Referencia para que Vite no dé error con Tailwind v4 */
@reference "../../css/app.css";

/* Forzamos que el fondo de la página sea el de la variable */
:deep(body) {
    background-color: var(--background);
}
</style>
<script setup lang="ts">
    import { type BreadcrumbItem } from '@/types';

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
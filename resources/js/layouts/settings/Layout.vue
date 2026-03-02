<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { toUrl } from '@/lib/utils';
import { edit as editAppearance } from '@/routes/appearance';
import { edit as editProfile } from '@/routes/profile';
import { show } from '@/routes/two-factor';
import { edit as editPassword } from '@/routes/user-password';
import { type NavItem } from '@/types';

const sidebarNavItems: NavItem[] = [
    {
        title: 'Perfil',
        href: editProfile(),
    },
    {
        title: 'Contraseña',
        href: editPassword(),
    },
    {
        title: 'Autenticación en dos pasos',
        href: show(),
    },
    {
        title: 'Apariencia',
        href: editAppearance(),
    },
];

const { isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <div class="rounded-2xl border border-blue-100 bg-white/90 px-4 py-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/80">
        <Heading
            title="Configuración"
            description="Administra tu perfil y la configuración de tu cuenta"
        />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav
                    class="flex flex-col space-y-1 space-x-0"
                    aria-label="Configuración"
                >
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="toUrl(item.href)"
                        variant="ghost"
                        :class="[
                            'w-full justify-start rounded-xl border px-3 text-left transition-colors',
                            isCurrentUrl(item.href)
                                ? 'border-blue-200 bg-blue-50 font-semibold text-blue-700 dark:border-sky-500/40 dark:bg-slate-900 dark:text-sky-300'
                                : 'border-transparent text-slate-600 hover:border-blue-100 hover:bg-sky-50 hover:text-blue-700 dark:text-slate-300 dark:hover:border-slate-700 dark:hover:bg-slate-900 dark:hover:text-sky-300',
                        ]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>

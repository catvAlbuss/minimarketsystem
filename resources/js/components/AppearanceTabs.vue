<script setup lang="ts">
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Claro' },
    { value: 'dark', Icon: Moon, label: 'Oscuro' },
    { value: 'system', Icon: Monitor, label: 'Sistema' },
] as const;
</script>

<template>
    <div class="inline-flex gap-1 rounded-xl border border-blue-100 bg-sky-50 p-1 dark:border-slate-700 dark:bg-slate-800">
        <button v-for="{ value, Icon, label } in tabs" :key="value" @click="updateAppearance(value)" :class="[
            'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
            appearance === value
                ? 'bg-white text-blue-700 shadow-xs dark:bg-slate-900 dark:text-sky-300'
                : 'text-slate-600 hover:bg-blue-100 hover:text-blue-700 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-sky-300',
        ]">
            <component :is="Icon" class="-ml-1 h-4 w-4" />
            <span class="ml-1.5 text-sm">{{ label }}</span>
        </button>
    </div>
</template>

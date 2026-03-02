<script setup lang="ts">
import { computed } from 'vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { User } from '@/types';

type Props = {
    user: User;
    showEmail?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

const showAvatar = computed(() => props.user.avatar && props.user.avatar !== '');
</script>

<template>
    <Avatar class="h-9 w-9 overflow-hidden rounded-xl border border-blue-100 bg-white dark:border-slate-700 dark:bg-slate-900">
        <AvatarImage v-if="showAvatar" :src="user.avatar!" :alt="`Foto de ${user.name}`" />
        <AvatarFallback
            class="rounded-xl bg-gradient-to-br from-blue-700 to-sky-400 font-semibold text-white"
        >
            {{ getInitials(user.name) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-semibold text-slate-900 dark:text-slate-100">{{ user.name }}</span>
        <span v-if="showEmail" class="truncate text-xs text-sky-700 dark:text-sky-400">{{ user.email }}</span>
    </div>
</template>

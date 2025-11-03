<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { cn } from '@/lib/utils';
import { HTMLAttributes } from 'vue';

interface Props {
    class?: HTMLAttributes['class'];
}

const props = defineProps<Props>();
</script>

<template>
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-transparent text-sidebar-primary-foreground">
        <AppLogoIcon :class="cn('size-8 fill-current text-white group-data-[collapsible=icon]:size-6 dark:text-black', props.class)" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-semibold">Noynay Medical Center</span>
    </div>
</template>

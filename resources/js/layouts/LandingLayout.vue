<script setup lang="ts">
import LandingLayout from '@/layouts/app/AppHeaderLayout.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <LandingLayout
        :breadcrumbs="breadcrumbs"
        class="relative"
    >
        <slot />
    </LandingLayout>
</template>
